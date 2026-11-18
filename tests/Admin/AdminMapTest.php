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

namespace Tests\Admin;

use FocalpointControllerMap;
use FocalpointModelmap;
use Joomla\CMS\Table\Table;
use Tests\Support\Fixtures\TestPlugin;
use Tests\Support\Fixtures\TestUser;
use Tests\Support\TestCase;

/**
 * The map model, map controller and the plugin events they fire
 */
class AdminMapTest extends TestCase
{
    /**
     * @var TestPlugin
     */
    protected $plugin = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication(
            'administrator',
            ['option' => 'com_focalpoint'],
            new TestUser(42, ['core.create:com_focalpoint', 'core.edit:com_focalpoint'])
        );
        $this->plugin = TestPlugin::create($this->app->getDispatcher());

        Table::addIncludePath(SLOC_ADMIN . '/tables');
        require_once SLOC_ADMIN . '/models/map.php';
        require_once SLOC_ADMIN . '/controllers/map.php';
    }

    protected function getModel(): FocalpointModelmap
    {
        return new FocalpointModelmap(['dbo' => $this->db, 'ignore_request' => true]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function mapData(array $data = []): array
    {
        return $data + [
            'id'        => 0,
            'title'     => 'Downtown',
            'alias'     => '',
            'text'      => '',
            'latitude'  => '27.5',
            'longitude' => '-82.25',
            'state'     => 1,
            'tabsdata'  => ['tabs' => ['t1' => ['name' => 'About', 'content' => 'Hello']]],
            'metadata'  => ['robots' => 'noindex'],
            'params'    => ['zoom' => '12'],
        ];
    }

    /**
     * @param int $id
     *
     * @return object
     */
    protected function row(int $id): object
    {
        return $this->db->setQuery(
            $this->db->getQuery(true)
                ->select('*')
                ->from('#__focalpoint_maps')
                ->where('id = ' . $id)
        )->loadObject();
    }

    public function testSaveStoresMapAsJson()
    {
        $model = $this->getModel();

        $this->assertTrue($model->save($this->mapData()), implode(', ', $model->getErrors()));

        $row = $this->row((int)$model->getState('map.id'));
        $this->assertSame('downtown', $row->alias);
        $this->assertSame(
            ['tabs' => ['t1' => ['name' => 'About', 'content' => 'Hello']]],
            json_decode($row->tabsdata, true)
        );
        $this->assertSame(['robots' => 'noindex'], json_decode($row->metadata, true));
        $this->assertSame(['zoom' => '12'], json_decode($row->params, true));
    }

    public function testPluginsCanChangeMapDataBeforeSave()
    {
        $model = $this->getModel();
        $model->save($this->mapData());

        $this->assertSame('onSlocmapBeforeSave', $this->plugin->calls[0][0]);
        $this->assertSame('Downtown', $this->plugin->calls[0][1][0]['title']);
        $this->assertSame('Added by plugin', $this->row((int)$model->getState('map.id'))->text);
    }

    public function testGetItemDecodesJson()
    {
        $id = $this->insert('focalpoint_maps', [
            'tabsdata' => json_encode(['tabs' => ['t1' => ['name' => 'About']]]),
            'metadata' => json_encode(['robots' => 'noindex']),
        ]);

        $item = $this->getModel()->getItem($id);

        $this->assertSame(['tabs' => ['t1' => ['name' => 'About']]], $item->tabsdata);
        $this->assertSame(['robots' => 'noindex'], $item->metadata);
    }

    public function testSaveAsCopyRenamesTheCopy()
    {
        $model = $this->getModel();
        $model->save($this->mapData(['alias' => 'downtown']));
        $originalId = (int)$model->getState('map.id');

        $this->createApplication(
            'administrator',
            ['option' => 'com_focalpoint', 'task' => 'save2copy', 'id' => $originalId],
            new TestUser(42, ['core.create:com_focalpoint'])
        );
        $model = $this->getModel();
        $model->save($this->mapData(['alias' => 'downtown']));

        $copy = $this->row((int)$model->getState('map.id'));
        $this->assertSame('Downtown (2)', $copy->title);
        $this->assertSame('downtown-2', $copy->alias);
        $this->assertEquals(0, $copy->state);
    }

    public function testControllerSendsSavedDataToPluginsAsOneArgument()
    {
        $controller = $this->instance(FocalpointControllerMap::class);
        $validData  = $this->mapData(['id' => 5]);

        $this->invoke($controller, 'postSaveHook', [$this->getModel(), $validData]);

        $this->assertSame([['onSlocmapAfterSave', [$validData]]], $this->plugin->calls);
    }

    // ---- FocalpointPlugin ----

    public function testPluginMapExclusions()
    {
        $plugin = TestPlugin::create($this->app->getDispatcher(), ['excludemaps' => ['3', '7']]);

        $this->assertTrue($plugin->isExcluded(3));
        $this->assertTrue($plugin->isExcluded(7));
        $this->assertFalse($plugin->isExcluded(5));
    }

    public function testPluginWithoutExclusionsExcludesNothing()
    {
        $this->assertFalse($this->plugin->isExcluded(3));
        $this->assertFalse(TestPlugin::create($this->app->getDispatcher(), ['excludemaps' => ''])->isExcluded(0));
    }
}
