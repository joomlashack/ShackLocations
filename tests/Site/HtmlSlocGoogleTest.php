<?php

/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2026 Joomlashack. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of ShackLocations.
 *
 * ShackLocations is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * ShackLocations is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ShackLocations.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Tests\Site;

use FocalpointHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use JhtmlSlocGoogle;
use Tests\Support\TestCase;

/**
 * HTMLHelper::_('slocgoogle.map'), the bridge between PHP and googleMap.js
 */
class HtmlSlocGoogleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication('site', ['option' => 'com_focalpoint']);

        require_once SLOC_LIBRARY . '/html/slocgoogle.php';
        $loaded = new \ReflectionProperty(JhtmlSlocGoogle::class, 'apiLoaded');
        $loaded->setValue(null, false);
    }

    protected function document(): HtmlDocument
    {
        return $this->app->getDocument();
    }

    /**
     * @return string[]
     */
    protected function scriptDeclarations(): array
    {
        $scripts = $this->document()->_script['text/javascript'] ?? [];

        return array_values(is_array($scripts) ? $scripts : [$scripts]);
    }

    /**
     * The options object passed to initMap() for the given map
     *
     * @param string $id
     *
     * @return array
     */
    protected function mapOptions(string $id): array
    {
        foreach ($this->scriptDeclarations() as $script) {
            if (preg_match('/^initMap\(\'' . preg_quote($id) . '\', (.*)\);$/s', $script, $match)) {
                return json_decode($match[1], true);
            }
        }

        $this->fail('No initMap() call for ' . $id);
    }

    /**
     * @param array $properties
     * @param array $params
     *
     * @return object
     */
    protected function marker(array $properties = [], array $params = []): object
    {
        return (object)($properties + [
            'id'              => '7',
            'locationtype_id' => '3',
            'title'           => 'Espresso Bar',
            'address'         => '1 Main St',
            'phone'           => '555-1234',
            'description'     => 'Great coffee',
            'link'            => 'index.php?option=com_focalpoint&view=location&id=7',
            'marker'          => 'http://www.example.com/images/markers/coffee.png',
            'latitude'        => '27.5',
            'longitude'       => '-82.25',
            'params'          => new Registry($params + ['infopopupevent' => 'click']),
        ]);
    }

    public function testMissingApiKeyWarnsAndRendersNothing()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', []);

        $this->assertSame(
            [['message' => Text::_('COM_FOCALPOINT_ERROR_MAPS_API_MISSING'), 'type' => 'warning']],
            $this->app->messages
        );
        $this->assertSame([], $this->scriptDeclarations());
        $this->assertSame([], $this->document()->_scripts);
    }

    public function testGoogleApiAndScriptsLoadOnce()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123']);
        HTMLHelper::_('slocgoogle.map', 'fp_2', ['apikey' => 'KEY123']);

        $scripts = array_keys($this->document()->_scripts);
        $google  = array_values(array_filter($scripts, function (string $url) {
            return strpos($url, 'maps.googleapis.com') !== false;
        }));

        $this->assertCount(1, $google);
        $this->assertStringContainsString('key=KEY123', $google[0]);
        $this->assertStringContainsString('loading=async', $google[0]);
        $this->assertStringContainsString('callback=initGoogle', $google[0]);

        foreach (['sloc.min.js', 'googleMap.min.js'] as $file) {
            $this->assertCount(
                1,
                array_filter($scripts, function (string $url) use ($file) {
                    return substr($url, -strlen($file) - 1) == '/' . $file;
                }),
                $file
            );
        }

        $declarations = $this->scriptDeclarations();
        $this->assertCount(1, preg_grep('/async function initGoogle\(\)/', $declarations));
        $this->assertCount(1, preg_grep('/^initMap\(\'fp_1\'/', $declarations));
        $this->assertCount(1, preg_grep('/^initMap\(\'fp_2\'/', $declarations));
    }

    public function testInfoboxScriptIsLoadedAfterGoogle()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123']);

        $init = implode("\n", preg_grep('/initGoogle/', $this->scriptDeclarations()));

        $this->assertMatchesRegularExpression('#\[".*?/infobox\.min\.js"\]#', stripslashes($init));
        $this->assertStringContainsString("importLibrary('maps')", $init);
        $this->assertStringContainsString("importLibrary('marker')", $init);
    }

    public function testJavascriptLanguageStringsAreRegistered()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123']);

        $strings = $this->document()->getScriptOptions('joomla.jtext');

        $keys = ['COM_FOCALPOINT_SEARCH_WITHIN', 'COM_FOCALPOINT_BUTTON_SHOW_ALL', 'COM_FOCALPOINT_ERROR_GEOCODE'];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $strings);
            $this->assertNotSame($key, $strings[$key], $key . ' is translated');
        }
    }

    public function testDefaultOptions()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123']);

        $options = $this->mapOptions('fp_1');

        $this->assertSame('fp_googleMap', $options['canvasId']);
        $this->assertSame(
            ['lat' => FocalpointHelper::HOME_LAT, 'lng' => FocalpointHelper::HOME_LNG],
            $options['mapProperties']['center']
        );
        $this->assertNull($options['mapProperties']['maxZoom'], 'No max zoom unless one is set');
        $this->assertSame(0, $options['mapProperties']['zoom']);
        $this->assertSame([], $options['mapProperties']['styles']);
        $this->assertSame(['assist' => '', 'radius' => 15, 'zoom' => 12], $options['search']);
        $this->assertSame(
            ['clusters' => false, 'legend' => false, 'listTab' => false, 'markers' => false, 'search' => false],
            $options['show']
        );
        $this->assertSame([], $options['markerData']);
        $this->assertFalse($options['fitBounds']);
    }

    public function testParametersMapToOptions()
    {
        HTMLHelper::_(
            'slocgoogle.map',
            'fp_9',
            [
                'apikey'           => 'KEY123',
                'canvasId'         => 'my_canvas',
                'zoom'             => '14',
                'maxzoom'          => '18',
                'mapTypeId'        => 'satellite',
                'mapTypeControl'   => '1',
                'gestureHandling'  => 'cooperative',
                'fullscreen'       => '1',
                'zoomControl'      => '1',
                'fitbounds'        => '1',
                'showmarkers'      => '1',
                'showlegend'       => '1',
                'locationlist'     => '1',
                'mapsearchenabled' => '1',
                'markerclusters'   => '1',
                'resultradius'     => '2.5',
                'mapsearchzoom'    => '9',
                'searchassist'     => ', Florida',
            ],
            ['lat' => '40.1', 'lng' => '-75.2']
        );

        $options    = $this->mapOptions('fp_9');
        $properties = $options['mapProperties'];

        $this->assertSame('my_canvas', $options['canvasId']);
        $this->assertSame(['lat' => '40.1', 'lng' => '-75.2'], $properties['center']);
        $this->assertSame(14, $properties['zoom']);
        $this->assertSame(18, $properties['maxZoom']);
        $this->assertSame('satellite', $properties['mapTypeId']);
        $this->assertSame(1, $properties['mapTypeControl']);
        $this->assertSame('cooperative', $properties['gestureHandling']);
        $this->assertTrue($properties['fullscreenControl']);
        $this->assertTrue($properties['zoomControl']);
        $this->assertFalse($properties['streetViewControl']);
        $this->assertTrue($options['fitBounds']);
        $this->assertSame(['assist' => ', Florida', 'radius' => 2.5, 'zoom' => 9], $options['search']);
        $this->assertSame(
            ['clusters' => true, 'legend' => true, 'listTab' => true, 'markers' => true, 'search' => true],
            $options['show']
        );
    }

    public function testMarkerData()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [$this->marker()]);

        $marker = $this->mapOptions('fp_1')['markerData'][0];

        $this->assertSame(7, $marker['id']);
        $this->assertSame(3, $marker['typeId']);
        $this->assertSame('http://www.example.com/images/markers/coffee.png', $marker['marker']);
        $this->assertSame(['lat' => '27.5', 'lng' => '-82.25'], $marker['position']);
        $this->assertSame('click', $marker['infoBox']['event']);
        $this->assertSame('', $marker['infoBox']['link'], 'On click the link is inside the content');
    }

    public function testSingleMarkerObjectIsAccepted()
    {
        $location = $this->marker();
        unset($location->locationtype_id);

        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, $location);

        $markers = $this->mapOptions('fp_1')['markerData'];
        $this->assertCount(1, $markers);
        $this->assertNull($markers[0]['typeId'], 'A location page has no type filter');
    }

    public function testInfoboxShowsOnlyEnabledDetails()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [
            $this->marker(['id' => 1], ['infoshowaddress' => 1, 'infoshowphone' => 0, 'infoshowintro' => 1]),
            $this->marker(['id' => 2], ['infoshowaddress' => 0, 'infoshowphone' => 1, 'infoshowintro' => 0]),
        ]);

        [$first, $second] = $this->mapOptions('fp_1')['markerData'];

        $this->assertStringContainsString('<h4>Espresso Bar</h4>', $first['infoBox']['content']);
        $this->assertStringContainsString('1 Main St', $first['infoBox']['content']);
        $this->assertStringNotContainsString('555-1234', $first['infoBox']['content']);
        $this->assertStringContainsString('Great coffee', $first['infoBox']['content']);

        $this->assertStringNotContainsString('1 Main St', $second['infoBox']['content']);
        $this->assertStringContainsString('555-1234', $second['infoBox']['content']);
        $this->assertStringNotContainsString('Great coffee', $second['infoBox']['content']);
    }

    public function testInfoboxContentIsOneLine()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [$this->marker()]);

        $content = $this->mapOptions('fp_1')['markerData'][0]['infoBox']['content'];

        $this->assertStringNotContainsString("\n", $content);
        $this->assertStringNotContainsString("\t", $content);
    }

    public function testClickInfoboxContainsTheLink()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [$this->marker()]);

        $infoBox = $this->mapOptions('fp_1')['markerData'][0]['infoBox'];

        $this->assertStringContainsString('class="infoboxlink"', $infoBox['content']);
        $this->assertStringContainsString('view=location', $infoBox['content']);
        $this->assertStringContainsString(Text::_('COM_FOCALPOINT_FIND_OUT_MORE'), $infoBox['content']);
    }

    public function testHoverInfoboxKeepsTheLinkSeparate()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [
            $this->marker([], ['infopopupevent' => 'hover']),
        ]);

        $infoBox = $this->mapOptions('fp_1')['markerData'][0]['infoBox'];

        $this->assertSame('hover', $infoBox['event']);
        $this->assertStringNotContainsString('infoboxlink', $infoBox['content']);
        $this->assertStringContainsString('class="infoboxlink"', $infoBox['link']);
    }

    public function testLocationWithoutLinkHasNoLink()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [$this->marker(['link' => null])]);

        $infoBox = $this->mapOptions('fp_1')['markerData'][0]['infoBox'];

        $this->assertStringNotContainsString('infoboxlink', $infoBox['content']);
    }

    public function testRelativeImagesInInfoboxBecomeSiteUrls()
    {
        // HTMLHelper::image() only resolves files that exist
        touch(JPATH_SITE . '/images/photo.jpg');

        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [
            $this->marker(['description' => '<img src="images/photo.jpg" alt="Photo">'], ['infoshowintro' => 1]),
        ]);

        $content = $this->mapOptions('fp_1')['markerData'][0]['infoBox']['content'];

        $this->assertStringNotContainsString('src="images/photo.jpg"', $content);
        $this->assertMatchesRegularExpression('#src="[^"]*/images/photo\.jpg"#', $content);
    }

    public function testMissingInfoboxImagesAreLeftAlone()
    {
        HTMLHelper::_('slocgoogle.map', 'fp_1', ['apikey' => 'KEY123'], null, [
            $this->marker(['description' => '<img src="images/missing.jpg" alt="Gone">'], ['infoshowintro' => 1]),
        ]);

        $content = $this->mapOptions('fp_1')['markerData'][0]['infoBox']['content'];

        $this->assertStringContainsString('src="images/missing.jpg"', $content);
    }
}
