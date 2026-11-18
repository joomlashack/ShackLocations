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

use FocalpointModelLocation;
use Joomla\Registry\Registry;
use Tests\Support\TestCase;

class SiteLocationModelTest extends TestCase
{
    /**
     * @var int
     */
    protected $mapId = null;

    /**
     * @var int
     */
    protected $typeId = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (defined('JPATH_COMPONENT_ADMINISTRATOR') == false) {
            define('JPATH_COMPONENT_ADMINISTRATOR', SLOC_ADMIN);
        }
        require_once SLOC_SITE . '/models/location.php';

        // HTMLHelper::image() only returns paths to files that exist
        if (is_dir(JPATH_SITE . '/images/markers') == false) {
            mkdir(JPATH_SITE . '/images/markers', 0777, true);
        }
        foreach (['own', 'type', 'default'] as $marker) {
            touch(JPATH_SITE . '/images/markers/' . $marker . '.png');
        }

        $this->mapId  = $this->insert('focalpoint_maps', ['title' => 'Downtown', 'alias' => 'downtown']);
        $this->typeId = $this->insert('focalpoint_locationtypes', [
            'marker'       => 'images/markers/type.png',
            'customfields' => json_encode(['h1' => ['type' => 'link', 'name' => 'website', 'label' => 'Website']]),
        ]);
    }

    protected function getModel(): FocalpointModelLocation
    {
        return new FocalpointModelLocation(['dbo' => $this->db]);
    }

    /**
     * @param array $properties
     *
     * @return int
     */
    protected function createLocation(array $properties = []): int
    {
        return $this->insert('focalpoint_locations', $properties + [
            'title'  => 'Espresso Bar',
            'alias'  => 'espresso-bar',
            'map_id' => $this->mapId,
            'type'   => $this->typeId,
        ]);
    }

    public function testLocationIsLoadedAndFormatted()
    {
        $this->setComponentParams(['zoom' => '6', 'infopopupevent' => 'click']);
        $id = $this->createLocation([
            'address'          => '1 Main St||Springfield',
            'metadata'         => json_encode(['robots' => 'noindex']),
            'params'           => json_encode(['zoom' => '16']),
            'customfieldsdata' => json_encode(['h1' => ['website' => 'https://example.com']]),
        ]);

        $item = $this->getModel()->getData($id);

        $this->assertSame('Espresso Bar', $item->title);
        $this->assertSame('1 Main St <br>Springfield', $item->address);
        $this->assertInstanceOf(Registry::class, $item->metadata);
        $this->assertSame('noindex', $item->metadata->get('robots'));
        $this->assertSame('16', $item->params->get('zoom'), 'Location params override global options');
        $this->assertSame('click', $item->params->get('infopopupevent'));
        $this->assertEquals(
            (object)[
                'website' => (object)['datatype' => 'link', 'label' => 'Website', 'data' => 'https://example.com'],
            ],
            $item->customfields
        );
    }

    public function testIdComesFromRequest()
    {
        $id = $this->createLocation();
        $this->createApplication('site', ['id' => $id]);

        $this->assertSame('Espresso Bar', $this->getModel()->getData()->title);
    }

    public function testIdFallsBackToMenuParameter()
    {
        $id = $this->createLocation();
        $this->app->getParams()->set('item_id', $id);

        $this->assertSame('Espresso Bar', $this->getModel()->getData()->title);
    }

    public function testPublishedFilterHidesUnpublishedLocation()
    {
        $id    = $this->createLocation(['state' => 0]);
        $model = $this->getModel();
        $model->setState('filter.published', 1);

        $this->assertNull($model->getData($id));
    }

    public function testOwnMarkerIsUsed()
    {
        $id = $this->createLocation(['marker' => 'images/markers/own.png']);

        $this->assertStringEndsWith('/images/markers/own.png', $this->getModel()->getData($id)->marker);
    }

    public function testMarkerFallsBackToTypeMarker()
    {
        $id = $this->createLocation();

        $this->assertStringEndsWith('/images/markers/type.png', $this->getModel()->getData($id)->marker);
    }

    public function testMarkerFallsBackToGlobalMarker()
    {
        $typeId = $this->insert('focalpoint_locationtypes', ['marker' => '']);
        $id     = $this->createLocation(['type' => $typeId]);
        $this->app->getParams('com_focalpoint')->set('marker', 'images/markers/default.png');

        $this->assertStringEndsWith('/images/markers/default.png', $this->getModel()->getData($id)->marker);
    }

    public function testNoMarkerAnywhereIsNull()
    {
        $typeId = $this->insert('focalpoint_locationtypes', ['marker' => '']);
        $id     = $this->createLocation(['type' => $typeId]);

        $this->assertNull($this->getModel()->getData($id)->marker);
    }

    public function testBackLinkPointsToTheMapMenuItem()
    {
        $this->insert('menu', [
            'link'   => 'index.php?option=com_focalpoint&view=map',
            'params' => json_encode(['item_id' => 999]),
        ]);
        $menuId = $this->insert('menu', [
            'link'   => 'index.php?option=com_focalpoint&view=map',
            'params' => json_encode(['item_id' => $this->mapId]),
        ]);
        $this->insert('menu', [
            'link'      => 'index.php?option=com_focalpoint&view=map',
            'published' => 0,
            'params'    => json_encode(['item_id' => $this->mapId]),
        ]);
        $id = $this->createLocation();

        $this->assertSame(
            'index.php?option=com_focalpoint&view=map&Itemid=' . $menuId,
            $this->getModel()->getData($id)->backlink
        );
    }

    public function testNoBackLinkWithoutMapMenuItem()
    {
        $id = $this->createLocation();

        $this->assertNull($this->getModel()->getData($id)->backlink);
    }
}
