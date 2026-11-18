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

use FocalpointViewMap;
use Joomla\Registry\Registry;
use Tests\Support\TestCase;

/**
 * Page helpers shared by every view (FocalpointView) and the site view helpers
 * (FocalpointViewSite)
 */
class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication('site', ['option' => 'com_focalpoint']);

        require_once SLOC_SITE . '/views/map/view.html.php';
    }

    /**
     * @param array $params Menu item / component params
     *
     * @return FocalpointViewMap
     */
    protected function getView(array $params = []): FocalpointViewMap
    {
        $this->app->setParams('com_focalpoint', new Registry($params));

        $view = new FocalpointViewMap(['name' => 'map', 'base_path' => SLOC_SITE]);

        // The controller does this when it creates the view
        $view->setDocument($this->app->getDocument());

        return $view;
    }

    public function testParamsComeFromTheApplication()
    {
        $view = $this->getView(['zoom' => '9']);

        $params = new \ReflectionProperty($view, 'params');
        $this->assertSame('9', $params->getValue($view)->get('zoom'));
    }

    public function testPageHeadingIsHiddenUnlessEnabled()
    {
        $view = $this->getView(['page_heading' => 'Custom heading']);

        $this->assertSame('', $this->invoke($view, 'getPageHeading', ['Map title']));
    }

    public function testPageHeadingPrefersMenuHeading()
    {
        $view = $this->getView(['show_page_heading' => 1, 'page_heading' => 'Custom heading']);

        $this->assertSame('Custom heading', $this->invoke($view, 'getPageHeading', ['Map title']));
    }

    public function testPageHeadingFallsBackToDefault()
    {
        $view = $this->getView(['show_page_heading' => 1]);

        $this->assertSame('Map title', $this->invoke($view, 'getPageHeading', ['Map title']));
    }

    public function testPageClassAddsSuffix()
    {
        $this->assertSame('fp-map-view', $this->invoke($this->getView(), 'getPageClass', ['fp-map-view']));
        $this->assertSame(
            'fp-map-view my-suffix',
            $this->invoke($this->getView(['pageclass_sfx' => 'my-suffix']), 'getPageClass', ['fp-map-view'])
        );
        $this->assertSame('my-suffix', $this->invoke($this->getView(['pageclass_sfx' => 'my-suffix']), 'getPageClass'));
    }

    public function testDocumentTitlePrefersMenuPageTitle()
    {
        $this->invoke($this->getView(), 'setDocumentTitle', ['Map title']);
        $this->assertSame('Map title', $this->app->getDocument()->getTitle());

        $this->invoke($this->getView(['page_title' => 'Menu title']), 'setDocumentTitle', ['Map title']);
        $this->assertSame('Menu title', $this->app->getDocument()->getTitle());
    }

    public function testItemMetadataIsUsedWhenMenuHasNone()
    {
        $view = $this->getView();

        $this->invoke($view, 'setDocumentMetadata', [[
            'metadesc' => 'Item description',
            'metakey'  => 'item, keywords',
            'robots'   => 'noindex, follow',
            'rights'   => 'Item rights',
            'author'   => 'Item author',
        ]]);

        $document = $this->app->getDocument();
        $this->assertSame('Item description', $document->getDescription());
        $this->assertSame('item, keywords', $document->getMetaData('keywords'));
        $this->assertSame('noindex, follow', $document->getMetaData('robots'));
        $this->assertSame('Item rights', $document->getMetaData('rights'));
        $this->assertSame('Item author', $document->getMetaData('author'));
    }

    public function testMenuMetadataOverridesItemDescriptionAndKeywords()
    {
        $view = $this->getView([
            'menu-meta_description' => 'Menu description',
            'menu-meta_keywords'    => 'menu, keywords',
            'robots'                => 'index, nofollow',
        ]);

        $this->invoke($view, 'setDocumentMetadata', [new Registry([
            'metadesc' => 'Item description',
            'metakey'  => 'item, keywords',
        ])]);

        $document = $this->app->getDocument();
        $this->assertSame('Menu description', $document->getDescription());
        $this->assertSame('menu, keywords', $document->getMetaData('keywords'));
        $this->assertSame(
            'index, nofollow',
            $document->getMetaData('robots'),
            'Menu robots apply when the item has none'
        );
    }

    public function testItemRobotsOverrideMenuRobots()
    {
        $view = $this->getView(['robots' => 'index, nofollow']);

        $this->invoke($view, 'setDocumentMetadata', [['robots' => 'noindex, nofollow']]);

        $this->assertSame('noindex, nofollow', $this->app->getDocument()->getMetaData('robots'));
    }

    public function testEmptyMetadataSetsNothing()
    {
        $this->invoke($this->getView(), 'setDocumentMetadata', [null]);

        $document = $this->app->getDocument();
        $this->assertSame('', (string)$document->getMetaData('keywords'));
        $this->assertSame('', (string)$document->getMetaData('robots'));
        $this->assertSame('', (string)$document->getMetaData('author'));
    }

    // ---- FocalpointViewSite ----

    public function testRenderFieldUsesTheLayoutForItsType()
    {
        $view = $this->getView();

        $html = $this->invoke($view, 'renderField', [(object)[
            'datatype' => 'textbox',
            'label'    => 'Hours',
            'data'     => '7am - 3pm',
        ]]);

        $this->assertSame(
            '<p class="fp_customfield fp_textbox"><span class="fp_label">Hours: </span>7am - 3pm</p>',
            trim($html)
        );
    }

    public function testRenderFieldWithoutTypeIsEmpty()
    {
        $field = (object)['label' => 'x', 'data' => 'y'];

        $this->assertSame('', $this->invoke($this->getView(), 'renderField', [$field]));
    }

    public function testFieldTokensAreReplacedWithRenderedFields()
    {
        $fields = (object)[
            'hours' => (object)['datatype' => 'textbox', 'label' => 'Hours', 'data' => '7am - 3pm'],
            'web'   => (object)['datatype' => 'link', 'label' => 'Web', 'data' => ['url' => 'https://example.com']],
        ];

        $html = $this->invoke($this->getView(), 'replaceFieldTokens', [
            'Open {hours} - see {web} - {unknown}',
            $fields,
        ]);

        $this->assertStringContainsString('<span class="fp_label">Hours: </span>7am - 3pm', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('{unknown}', $html, 'Tokens without a field are left alone');
        $this->assertStringNotContainsString('{hours}', $html);
    }

    public function testFieldTokensNeedCustomFields()
    {
        $html = $this->invoke($this->getView(), 'replaceFieldTokens', ['Open {hours}', null]);

        $this->assertSame('Open {hours}', $html);
    }
}
