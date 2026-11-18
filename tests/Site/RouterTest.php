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

use FocalpointRouter;
use Tests\Support\Fixtures\TestMenu;
use Tests\Support\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var int[]
     */
    protected $ids = [];

    /**
     * @var TestMenu
     */
    protected $menu = null;

    protected function setUp(): void
    {
        parent::setUp();

        require_once SLOC_SITE . '/router.php';

        $this->ids['downtown'] = $this->insert('focalpoint_maps', ['alias' => 'downtown']);
        $this->ids['uptown']   = $this->insert('focalpoint_maps', ['alias' => 'uptown']);

        $this->ids['espresso'] = $this->insert('focalpoint_locations', [
            'alias'  => 'espresso',
            'map_id' => $this->ids['downtown'],
        ]);
        $this->ids['featured'] = $this->insert('focalpoint_locations', [
            'alias'  => 'featured',
            'map_id' => $this->ids['downtown'],
        ]);
        $this->ids['diner']    = $this->insert('focalpoint_locations', [
            'alias'  => 'diner',
            'map_id' => $this->ids['uptown'],
        ]);

        $this->menu = new TestMenu([
            [
                'id'     => 101,
                'alias'  => 'downtown-map',
                'query'  => ['option' => 'com_focalpoint', 'view' => 'map'],
                'params' => ['item_id' => $this->ids['downtown']],
            ],
            [
                'id'     => 102,
                'alias'  => 'featured-place',
                'query'  => ['option' => 'com_focalpoint', 'view' => 'location'],
                'params' => ['item_id' => $this->ids['featured']],
            ],
            [
                'id'    => 103,
                'alias' => 'news',
                'query' => ['option' => 'com_content', 'view' => 'featured'],
            ],
        ]);
        $this->app->setMenu($this->menu);
    }

    protected function getRouter(?int $active = null): FocalpointRouter
    {
        $this->menu->setActive($active ?: 0);

        return new FocalpointRouter($this->app, $this->menu);
    }

    // ---- build ----

    public function testLocationOnMapMenuItemGetsAliasSegment()
    {
        $query = ['view' => 'location', 'id' => $this->ids['espresso'], 'Itemid' => 101];

        $segments = $this->getRouter()->build($query);

        $this->assertSame(['espresso'], $segments);
        $this->assertSame(['Itemid' => 101], $query);
    }

    public function testLocationWithOwnMenuItemRoutesToIt()
    {
        $query = ['view' => 'location', 'id' => $this->ids['featured'], 'Itemid' => 101];

        $segments = $this->getRouter()->build($query);

        $this->assertSame([], $segments);
        $this->assertSame(['Itemid' => 102], $query);
    }

    public function testMapWithOwnMenuItemRoutesToIt()
    {
        $query = ['view' => 'map', 'id' => $this->ids['downtown'], 'Itemid' => 103];

        $segments = $this->getRouter()->build($query);

        $this->assertSame([], $segments);
        $this->assertSame(['Itemid' => 101], $query);
    }

    public function testMapWithoutMenuItemGetsAliasSegment()
    {
        $query = ['view' => 'map', 'id' => $this->ids['uptown'], 'Itemid' => 102];

        $segments = $this->getRouter()->build($query);

        $this->assertSame(['uptown'], $segments);
    }

    public function testSameViewAsMenuItemKeepsId()
    {
        $query = ['view' => 'map', 'id' => $this->ids['uptown'], 'Itemid' => 101];

        $segments = $this->getRouter()->build($query);

        $this->assertSame([], $segments);
        $this->assertSame(['id' => $this->ids['uptown'], 'Itemid' => 101], $query);
    }

    public function testViewDefaultsToActiveMenuItem()
    {
        $query = ['id' => $this->ids['espresso']];

        $segments = $this->getRouter(102)->build($query);

        $this->assertSame([], $segments, 'Same view as the menu item, so nothing to add');
    }

    // ---- parse ----

    public function testAliasOnMapMenuItemParsesToLocation()
    {
        $segments = ['espresso'];

        $vars = $this->getRouter(101)->parse($segments);

        $this->assertSame(['view' => 'location', 'id' => $this->ids['espresso']], $vars);
        $this->assertSame([], $segments);
    }

    public function testAliasIsLimitedToTheMenuItemsMap()
    {
        $segments = ['diner'];

        $this->assertSame([], $this->getRouter(101)->parse($segments));
    }

    public function testAliasWithoutMapMenuItemSearchesAllMaps()
    {
        $segments = ['diner'];

        $vars = $this->getRouter(103)->parse($segments);

        $this->assertSame(['view' => 'location', 'id' => $this->ids['diner']], $vars);
    }

    public function testUnknownAliasParsesToNothing()
    {
        $segments = ['nowhere'];

        $this->assertSame([], $this->getRouter(101)->parse($segments));
    }

    public function testLocationMenuItemIgnoresSegments()
    {
        $segments = ['espresso'];

        $this->assertSame([], $this->getRouter(102)->parse($segments));
        $this->assertSame(['espresso'], $segments);
    }

    public function testNoSegmentsParsesToNothing()
    {
        $segments = [];

        $this->assertSame([], $this->getRouter(101)->parse($segments));
    }

    // ---- preprocess ----

    public function testPreprocessUsesItemsOwnMenuItem()
    {
        $query = $this->getRouter()->preprocess(['view' => 'location', 'id' => $this->ids['featured']]);

        $this->assertSame(['view' => 'location', 'Itemid' => 102], $query);
    }

    public function testPreprocessLeavesOtherItemsAlone()
    {
        $original = ['view' => 'location', 'id' => $this->ids['espresso']];

        $this->assertSame($original, $this->getRouter()->preprocess($original));
        $this->assertSame(['view' => 'map'], $this->getRouter()->preprocess(['view' => 'map']));
    }
}
