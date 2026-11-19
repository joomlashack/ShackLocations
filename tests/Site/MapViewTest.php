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

use FocalpointModelMap;
use FocalpointViewMap;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Tests\Support\TestCase;

class MapViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication('site', ['option' => 'com_focalpoint']);

        require_once SLOC_SITE . '/models/map.php';
        require_once SLOC_SITE . '/views/map/view.html.php';
    }

    /**
     * @param ?object $item What the model returns
     *
     * @return FocalpointViewMap
     */
    protected function getView(?object $item = null): FocalpointViewMap
    {
        $view = new FocalpointViewMap(['name' => 'map', 'base_path' => SLOC_SITE]);
        $view->setDocument($this->app->getDocument());
        $view->addTemplatePath(dirname(__DIR__) . '/Support/Data/tmpl/map');

        if ($item) {
            $model = new class (['dbo' => $this->db]) extends FocalpointModelMap {
                public $testItem = null;

                public function getData(?int $id = null): object
                {
                    return $this->testItem;
                }
            };
            $model->testItem = $item;
            $model->setState('list.offset', 0);

            $view->setModel($model, true);
        }

        return $view;
    }

    /**
     * @param array $properties
     *
     * @return object
     */
    protected function mapItem(array $properties = []): object
    {
        return (object)($properties + [
            'id'         => 5,
            'title'      => 'Downtown',
            'text'       => 'Intro',
            'params'     => new Registry(['zoom' => '14']),
            'metadata'   => new Registry(['metadesc' => 'All of downtown']),
            'tabsdata'   => (object)['tabs' => []],
            'markerdata' => [],
        ]);
    }

    /**
     * @param string $legend
     * @param string $type
     * @param string $subtitle
     *
     * @return object
     */
    protected function marker(string $legend, string $type, string $subtitle = ''): object
    {
        return (object)[
            'legend'            => ucfirst($legend),
            'legendalias'       => $legend,
            'legendsubtitle'    => $subtitle,
            'locationtypealias' => $type,
        ];
    }

    // ---- chunkLegends ----

    public function testLegendsBecomeColumnsOfUniqueTypes()
    {
        $markers = [
            $this->marker('food', 'cafe'),
            $this->marker('food', 'cafe'),
            $this->marker('food', 'bar'),
            $this->marker('fun', 'park', 'Outdoors'),
            $this->marker('fun', 'park', 'Outdoors'),
        ];

        $hasSubtitles = null;
        $legends      = $this->invoke($this->getView(), 'chunkLegends', [$markers, &$hasSubtitles]);

        $this->assertCount(2, $legends);
        $this->assertSame(['food', 'fun'], [$legends[0]->alias, $legends[1]->alias]);
        $this->assertSame(['Food', ''], [$legends[0]->title, $legends[0]->subtitle]);
        $this->assertSame(['Fun', 'Outdoors'], [$legends[1]->title, $legends[1]->subtitle]);
        $this->assertSame(
            ['cafe', 'bar'],
            array_map(function ($marker) {
                return $marker->locationtypealias;
            }, $legends[0]->markers)
        );
        $this->assertCount(1, $legends[1]->markers);
        $this->assertTrue($hasSubtitles);
    }

    public function testNoSubtitles()
    {
        $hasSubtitles = null;
        $this->invoke($this->getView(), 'chunkLegends', [[$this->marker('food', 'cafe')], &$hasSubtitles]);

        $this->assertFalse($hasSubtitles);
    }

    public function testNoMarkersNoLegends()
    {
        $this->assertSame([], $this->invoke($this->getView(), 'chunkLegends', [[]]));
    }

    // ---- display ----

    public function testDisplayRendersTheMap()
    {
        $view = $this->getView($this->mapItem());

        ob_start();
        $view->display();
        $output = ob_get_clean();

        $this->assertSame('Rendered: Downtown', $output);
        $this->assertSame('Downtown', $this->app->getDocument()->getTitle());
        $this->assertSame('All of downtown', $this->app->getDocument()->getDescription());
    }

    public function testDisplayEventsRunInOrder()
    {
        $item = $this->mapItem([
            'tabsdata' => (object)['tabs' => ['t1' => (object)['name' => 'About', 'content' => 'Tab content']]],
        ]);

        ob_start();
        $this->getView($item)->display();
        ob_get_clean();

        $events = array_map(function ($event) {
            return $event[0] . (isset($event[1][0]) && is_string($event[1][0]) ? ':' . $event[1][0] : '');
        }, $this->app->events);

        $this->assertSame(
            [
                'onSlocmapPrepareRender',
                'onContentPrepare:com_focalpoint.map',
                'onContentPrepare:com_focalpoint.map',
                'onSlocmapBeforeRender',
                'onSlocmapAfterRender',
            ],
            $events
        );
        $this->assertSame($item, $this->app->events[0][1][0], 'Plugins receive the map item');
    }

    public function testMapParamsAreMergedIntoViewParams()
    {
        $this->app->getParams()->set('showlegend', '1');
        $view = $this->getView($this->mapItem());

        ob_start();
        $view->display();
        ob_get_clean();

        $params = new \ReflectionProperty($view, 'params');
        $this->assertSame('14', $params->getValue($view)->get('zoom'));
        $this->assertSame('1', $params->getValue($view)->get('showlegend'));
    }

    public function testEmptyTabsAreDropped()
    {
        $item = $this->mapItem([
            'tabsdata' => (object)['tabs' => [
                'keep'      => (object)['name' => 'About', 'content' => 'Tab content'],
                'noname'    => (object)['name' => '', 'content' => 'Orphan content'],
                'nocontent' => (object)['name' => 'Empty', 'content' => ''],
            ]],
        ]);

        ob_start();
        $this->getView($item)->display();
        ob_get_clean();

        $this->assertSame(['keep'], array_keys($item->tabsdata->tabs));
        $this->assertSame('Tab content', $item->tabsdata->tabs['keep']->content);
        $this->assertFalse(
            property_exists($item->tabsdata->tabs['keep'], 'text'),
            'Temporary text property is removed'
        );
    }

    public function testFieldTokensInLocationDescriptionsAreReplaced()
    {
        $item = $this->mapItem([
            'markerdata' => [(object)[
                'description'  => 'Hours: {hours}',
                'customfields' => (object)[
                    'hours' => (object)['datatype' => 'textbox', 'label' => 'Hours', 'data' => '9 to 5'],
                ],
            ]],
        ]);

        ob_start();
        $this->getView($item)->display();
        ob_get_clean();

        $this->assertStringContainsString('9 to 5', $item->markerdata[0]->description);
        $this->assertStringNotContainsString('{hours}', $item->markerdata[0]->description);
    }

    public function testMissingMapIsNotFound()
    {
        $view = $this->getView((object)[]);

        try {
            $view->display();
            $this->fail('No exception');

        } catch (\Exception $error) {
            $this->assertSame(404, $error->getCode());
            $this->assertSame(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), $error->getMessage());
        }
    }

    public function testMapParamsDontChangeApplicationParams()
    {
        $this->app->getParams()->set('zoom', '6');

        ob_start();
        $this->getView($this->mapItem())->display();
        ob_get_clean();

        $this->assertSame('6', $this->app->getParams()->get('zoom'));
    }

    public function testModelErrorsStopDisplay()
    {
        $view  = $this->getView($this->mapItem());
        $model = $view->getModel();
        $model->setError('Something broke');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Something broke');

        $view->display();
    }
}
