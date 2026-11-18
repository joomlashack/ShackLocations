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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Tests\Support\Fixtures\TestMenu;
use Tests\Support\TestCase;

class SiteMapModelTest extends TestCase
{
    /**
     * @var int[]
     */
    protected $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        require_once SLOC_SITE . '/models/map.php';
        \Joomla\CMS\Table\Table::addIncludePath(SLOC_ADMIN . '/tables');

        $this->app->getParams('com_focalpoint')->set('marker', 'images/markers/default.png');
    }

    /**
     * One map with two legends, three location types and four locations
     *
     * @return void
     */
    protected function createMap(array $mapProperties = []): void
    {
        $this->ids['map'] = $this->insert('focalpoint_maps', $mapProperties + [
            'title'    => 'Downtown',
            'alias'    => 'downtown',
            'tabsdata' => json_encode(['tabs' => ['t1' => ['name' => 'About', 'content' => 'Hello']]]),
            'metadata' => json_encode(['metadesc' => 'Map description']),
            'params'   => json_encode(['zoom' => '14']),
        ]);

        $legend = function (string $title, array $properties): int {
            $properties += ['title' => $title, 'alias' => strtolower($title)];

            return $this->insert('focalpoint_legends', $properties);
        };

        $this->ids['food']  = $legend('Food', ['ordering' => 1]);
        $this->ids['fun']   = $legend('Fun', ['ordering' => 2]);
        $this->ids['other'] = $legend('Hidden', ['state' => 0]);

        $this->ids['cafe'] = $this->insert('focalpoint_locationtypes', [
            'title'        => 'Cafe',
            'alias'        => 'cafe',
            'legend'       => $this->ids['food'],
            'marker'       => 'images/markers/coffee.png#joomlaImage://local-images/markers/coffee.png',
            'ordering'     => 1,
            'customfields' => json_encode([
                'h1' => ['type' => 'textbox', 'name' => 'hours', 'label' => 'Hours'],
                'h2' => ['type' => 'email', 'name' => 'contact', 'label' => 'Contact'],
            ]),
        ]);
        $this->ids['bar']    = $this->insert('focalpoint_locationtypes', [
            'title'    => 'Bar',
            'alias'    => 'bar',
            'legend'   => $this->ids['food'],
            'ordering' => 2,
        ]);
        $this->ids['park']   = $this->insert('focalpoint_locationtypes', [
            'title'    => 'Park',
            'alias'    => 'park',
            'legend'   => $this->ids['fun'],
            'marker'   => 'images/markers/park.png',
            'ordering' => 1,
        ]);
        $this->ids['closed'] = $this->insert('focalpoint_locationtypes', [
            'title'  => 'Closed',
            'alias'  => 'closed',
            'legend' => $this->ids['fun'],
            'state'  => 0,
        ]);
        $this->ids['hiddenType'] = $this->insert('focalpoint_locationtypes', [
            'title'  => 'In hidden legend',
            'alias'  => 'in-hidden',
            'legend' => $this->ids['other'],
        ]);
    }

    /**
     * @param string $key
     * @param int    $typeId
     * @param array  $properties
     * @param int[]  $otherTypes
     *
     * @return int
     */
    protected function createLocation(string $key, int $typeId, array $properties = [], array $otherTypes = []): int
    {
        $id = $this->insert('focalpoint_locations', $properties + [
            'title'  => ucfirst($key),
            'alias'  => $key,
            'map_id' => $this->ids['map'],
            'type'   => $typeId,
        ]);

        foreach (array_unique(array_merge([$typeId], $otherTypes)) as $type) {
            $this->insert('focalpoint_location_type_xref', ['location_id' => $id, 'locationtype_id' => $type]);
        }

        $this->ids[$key] = $id;

        return $id;
    }

    protected function getModel(): FocalpointModelMap
    {
        return new FocalpointModelMap(['dbo' => $this->db]);
    }

    /**
     * @param object[] $markers
     * @param string   $field
     *
     * @return string[]
     */
    protected function column(array $markers, string $field): array
    {
        return array_map(function ($marker) use ($field) {
            return $marker->{$field};
        }, $markers);
    }

    // ---- getData ----

    public function testMissingIdIsResourceNotFound()
    {
        $model = $this->getModel();

        $item = $model->getData();

        $this->assertEquals((object)[], $item);
        $this->assertContains(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), $model->getErrors());
    }

    public function testIdComesFromRequestOrMenuParameter()
    {
        $this->createMap();

        $this->createApplication('site', ['id' => $this->ids['map']]);
        $this->assertSame('Downtown', $this->getModel()->getData()->title);

        $this->createApplication('site');
        $this->app->getParams()->set('item_id', $this->ids['map']);
        $this->assertSame('Downtown', $this->getModel()->getData()->title);
    }

    public function testMapDataIsDecodedAndParamsMerged()
    {
        $this->createMap();
        $this->setComponentParams(['zoom' => '6', 'showlegend' => '1']);

        $item = $this->getModel()->getData($this->ids['map']);

        $this->assertSame('Downtown', $item->title);
        $this->assertSame(['t1'], array_keys($item->tabsdata->tabs));
        $this->assertSame('About', $item->tabsdata->tabs['t1']->name);
        $this->assertInstanceOf(Registry::class, $item->metadata);
        $this->assertSame('Map description', $item->metadata->get('metadesc'));
        $this->assertInstanceOf(Registry::class, $item->params);
        $this->assertSame('14', $item->params->get('zoom'), 'Map params override global options');
        $this->assertSame('1', $item->params->get('showlegend'), 'Global options fill in the rest');
        $this->assertSame([], $item->markerdata);
    }

    public function testMapParamsDontChangeGlobalOptions()
    {
        $this->createMap();
        $this->setComponentParams(['zoom' => '6']);

        $this->getModel()->getData($this->ids['map']);

        $this->assertSame('6', ComponentHelper::getParams('com_focalpoint')->get('zoom'));
    }

    public function testMapWithoutTabsGetsEmptyTabList()
    {
        $this->createMap(['tabsdata' => '']);

        $item = $this->getModel()->getData($this->ids['map']);

        $this->assertSame([], $item->tabsdata->tabs);
    }

    public function testPublishedFilterHidesUnpublishedMap()
    {
        $this->createMap(['state' => 0]);

        $model = $this->getModel();
        $model->setState('filter.published', 1);

        $this->assertEquals((object)[], $model->getData($this->ids['map']));
    }

    public function testDataIsLoadedOnce()
    {
        $this->createMap();
        $model = $this->getModel();

        $first = $model->getData($this->ids['map']);
        $this->db->setQuery('UPDATE #__focalpoint_maps SET title = ' . $this->db->quote('Changed'))->execute();

        $this->assertSame($first, $model->getData($this->ids['map']));
    }

    // ---- getMarkerData ----

    public function testOnlyPublishedLocationsTypesAndLegendsAreShown()
    {
        $this->createMap();
        $this->createLocation('espresso', $this->ids['cafe']);
        $this->createLocation('draft', $this->ids['cafe'], ['state' => 0]);
        $this->createLocation('shut', $this->ids['closed']);
        $this->createLocation('secret', $this->ids['hiddenType']);
        $this->createLocation('elsewhere', $this->ids['cafe'], ['map_id' => 999]);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;

        $this->assertSame(['espresso'], $this->column($markers, 'alias'));
    }

    public function testLocationAppearsOnceForEachOfItsTypes()
    {
        $this->createMap();
        $otherTypes = [$this->ids['cafe'], $this->ids['park'], $this->ids['closed']];
        $this->createLocation('pub', $this->ids['bar'], [], $otherTypes);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;

        $this->assertCount(3, $markers, 'The unpublished type is skipped');
        $types = $this->column($markers, 'locationtype_id');
        sort($types);
        $expected = [$this->ids['cafe'], $this->ids['bar'], $this->ids['park']];
        sort($expected);
        $this->assertEquals($expected, $types);
        $this->assertSame([$this->ids['pub']], array_values(array_unique($this->column($markers, 'id'))));
    }

    public function testMarkerRowsCarryLegendAndTypeDetails()
    {
        $this->createMap();
        $this->createLocation('espresso', $this->ids['cafe']);

        $marker = $this->getModel()->getData($this->ids['map'])->markerdata[0];

        $this->assertSame('Food', $marker->legend);
        $this->assertSame('food', $marker->legendalias);
        $this->assertSame('Cafe', $marker->locationtype);
        $this->assertSame('cafe', $marker->locationtypealias);
        $this->assertEquals($this->ids['cafe'], $marker->locationtype_id);
        $this->assertInstanceOf(Registry::class, $marker->params);
    }

    public function testLocationsAreOrderedByLocationOrdering()
    {
        $this->createMap();
        $this->createLocation('third', $this->ids['park'], ['ordering' => 3]);
        $this->createLocation('first', $this->ids['bar'], ['ordering' => 1]);
        $this->createLocation('second', $this->ids['cafe'], ['ordering' => 2]);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;
        $this->assertSame(['first', 'second', 'third'], $this->column($markers, 'alias'));
    }

    public function testLocationOrderingParametersApply()
    {
        $this->createMap(['params' => json_encode(['locationorder' => 'title', 'locationorderdir' => 'desc'])]);
        $this->createLocation('alpha', $this->ids['cafe'], ['title' => 'Alpha']);
        $this->createLocation('charlie', $this->ids['cafe'], ['title' => 'Charlie']);
        $this->createLocation('bravo', $this->ids['cafe'], ['title' => 'Bravo']);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;
        $this->assertSame(['charlie', 'bravo', 'alpha'], $this->column($markers, 'alias'));
    }

    public function testGroupedOrderingSortsByLegendThenTypeThenLocation()
    {
        $this->createMap(['params' => json_encode(['locationgroup' => '1'])]);
        $this->createLocation('swings', $this->ids['park'], ['ordering' => 1]);
        $this->createLocation('tavern', $this->ids['bar'], ['ordering' => 2]);
        $this->createLocation('latte', $this->ids['cafe'], ['ordering' => 3]);
        $this->createLocation('mocha', $this->ids['cafe'], ['ordering' => 1]);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;
        $this->assertSame(['mocha', 'latte', 'tavern', 'swings'], $this->column($markers, 'alias'));
    }

    public function testMarkerPriorityIsLocationThenTypeThenGlobal()
    {
        $this->createMap();

        $this->createLocation('own', $this->ids['cafe'], ['marker' => 'images/markers/own.png']);
        $this->createLocation('typed', $this->ids['cafe']);
        $this->createLocation('plain', $this->ids['bar']);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;
        $byAlias = array_combine($this->column($markers, 'alias'), $this->column($markers, 'marker'));

        $this->assertSame('http://www.example.com/images/markers/own.png', $byAlias['own']);
        $this->assertSame(
            'http://www.example.com/images/markers/coffee.png',
            $byAlias['typed'],
            'Media field suffix after # is stripped'
        );
        $this->assertSame('http://www.example.com/images/markers/default.png', $byAlias['plain']);
    }

    public function testNoMarkerAnywhereUsesGoogleDefault()
    {
        $this->createMap();
        $this->app->getParams('com_focalpoint')->set('marker', '');
        $this->createLocation('plain', $this->ids['bar']);

        $marker = $this->getModel()->getData($this->ids['map'])->markerdata[0];

        $this->assertNull($marker->marker);
    }

    public function testLinkTypes()
    {
        $this->createMap();
        $menu = new TestMenu([
            ['id' => 101, 'alias' => 'map-page', 'query' => ['option' => 'com_focalpoint', 'view' => 'map']],
            ['id' => 202, 'alias' => 'articles', 'query' => ['option' => 'com_content', 'view' => 'featured']],
        ]);
        $menu->setActive(101);
        $this->app->setMenu($menu);

        $this->createLocation('own', $this->ids['cafe'], ['linktype' => 0]);
        $this->createLocation('url', $this->ids['cafe'], ['linktype' => 1, 'altlink' => 'https://example.org/x']);
        $this->createLocation('nourl', $this->ids['cafe'], ['linktype' => 1, 'altlink' => '']);
        $this->createLocation('tomap', $this->ids['cafe'], ['linktype' => 2, 'maplinkid' => 7]);
        $this->createLocation('notomap', $this->ids['cafe'], ['linktype' => 2, 'maplinkid' => 0]);
        $this->createLocation('none', $this->ids['cafe'], ['linktype' => 3]);
        $this->createLocation('menu', $this->ids['cafe'], ['linktype' => 4, 'menulink' => 202]);
        $this->createLocation('nomenu', $this->ids['cafe'], ['linktype' => 4, 'menulink' => 999]);

        $markers = $this->getModel()->getData($this->ids['map'])->markerdata;
        $links   = array_combine($this->column($markers, 'alias'), $this->column($markers, 'link'));

        $location = function (string $key, ?int $itemId = null): string {
            $query = ['option' => 'com_focalpoint', 'view' => 'location', 'id' => $this->ids[$key]];
            if ($itemId) {
                $query['Itemid'] = $itemId;
            }

            return 'index.php?' . http_build_query($query);
        };

        $this->assertSame($location('own', 101), $links['own']);
        $this->assertSame('https://example.org/x', $links['url']);
        $this->assertSame($location('nourl', 101), $links['nourl']);
        $this->assertSame('index.php?option=com_focalpoint&view=map&id=7', $links['tomap']);
        $this->assertSame($location('notomap'), $links['notomap']);
        $this->assertNull($links['none']);
        $this->assertSame('index.php?option=com_content&Itemid=202', $links['menu']);
        $this->assertSame($location('nomenu'), $links['nomenu']);
    }

    public function testAddressSeparatorsBecomeLineBreaks()
    {
        $this->createMap();
        $this->createLocation('espresso', $this->ids['cafe'], ['address' => '1 Main St||Springfield||USA']);

        $marker = $this->getModel()->getData($this->ids['map'])->markerdata[0];

        $this->assertSame('1 Main St<br>Springfield<br>USA', $marker->address);
    }

    public function testLocationParamsOverrideGlobalOptions()
    {
        $this->createMap();
        $this->app->getParams('com_focalpoint')->set('infopopupevent', 'click');
        $this->app->getParams('com_focalpoint')->set('zoom', '10');
        $this->createLocation('espresso', $this->ids['cafe'], ['params' => json_encode(['infopopupevent' => 'hover'])]);

        $marker = $this->getModel()->getData($this->ids['map'])->markerdata[0];

        $this->assertSame('hover', $marker->params->get('infopopupevent'));
        $this->assertSame('10', $marker->params->get('zoom'));
        $this->assertSame(
            'click',
            $this->app->getParams('com_focalpoint')->get('infopopupevent'),
            'Global options are not changed'
        );
    }

    public function testCustomFieldsAreKeyedByName()
    {
        $this->createMap();
        $this->createLocation('espresso', $this->ids['cafe'], [
            'customfieldsdata' => json_encode([
                'h1'      => ['hours' => '7am - 3pm'],
                'h2'      => ['contact' => ''],
                'removed' => ['old' => 'Field no longer defined on the type'],
            ]),
        ]);

        $marker = $this->getModel()->getData($this->ids['map'])->markerdata[0];

        $this->assertEquals(
            (object)[
                'hours'   => (object)['datatype' => 'textbox', 'label' => 'Hours', 'data' => '7am - 3pm'],
                'contact' => (object)['datatype' => 'email', 'label' => 'Contact', 'data' => ''],
            ],
            $marker->customfields
        );
        $this->assertFalse(property_exists($marker, 'customfieldsdata'));
    }

    public function testLocationWithoutCustomFieldDataHasNone()
    {
        $this->createMap();
        $this->createLocation('espresso', $this->ids['cafe']);

        $marker = $this->getModel()->getData($this->ids['map'])->markerdata[0];

        $this->assertNull($marker->customfields);
    }
}
