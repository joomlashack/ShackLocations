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

use FocalpointModellocation;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Tests\Support\Fixtures\TestUser;
use Tests\Support\TestCase;

class AdminLocationModelTest extends TestCase
{
    /**
     * @var int
     */
    protected $mapId = null;

    /**
     * @var int[]
     */
    protected $types = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication(
            'administrator',
            ['option' => 'com_focalpoint'],
            new TestUser(42, $this->permissions())
        );

        Table::addIncludePath(SLOC_ADMIN . '/tables');
        require_once SLOC_ADMIN . '/models/location.php';

        $this->mapId = $this->insert('focalpoint_maps', ['alias' => 'downtown']);
        foreach (['cafe', 'bar', 'park'] as $type) {
            $this->types[$type] = $this->insert('focalpoint_locationtypes', ['alias' => $type]);
        }
    }

    /**
     * @return string[]
     */
    protected function permissions(): array
    {
        return array_map(function (string $action) {
            return $action . ':com_focalpoint';
        }, ['core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete']);
    }

    protected function getModel(): FocalpointModellocation
    {
        // As FormController::getModel() creates it
        return new FocalpointModellocation(['dbo' => $this->db, 'ignore_request' => true]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function locationData(array $data = []): array
    {
        return $data + [
            'id'          => 0,
            'title'       => 'Espresso Bar',
            'alias'       => '',
            'map_id'      => $this->mapId,
            'type'        => $this->types['cafe'],
            'description' => 'Great coffee',
            'latitude'    => '27.5',
            'longitude'   => '-82.25',
            'linktype'    => 0,
            'state'       => 1,
            // Always posted by the edit form; the columns have no defaults
            'image'       => '',
            'marker'      => '',
            'metadata'    => [],
            'params'      => [],
        ];
    }

    /**
     * @param int $locationId
     *
     * @return int[]
     */
    protected function typesFor(int $locationId): array
    {
        $types = $this->db->setQuery(
            $this->db->getQuery(true)
                ->select('locationtype_id')
                ->from('#__focalpoint_location_type_xref')
                ->where('location_id = ' . $locationId)
                ->order('locationtype_id')
        )->loadColumn();

        return array_map('intval', $types);
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
                ->from('#__focalpoint_locations')
                ->where('id = ' . $id)
        )->loadObject();
    }

    // ---- save ----

    public function testSaveCreatesLocationWithAllItsTypes()
    {
        $model = $this->getModel();

        $this->assertTrue($model->save($this->locationData([
            'othertypes' => [$this->types['bar'], $this->types['cafe'], ''],
        ])), implode(', ', $model->getErrors()));

        $id = (int)$model->getState('location.id');
        $this->assertGreaterThan(0, $id);

        $types = [$this->types['cafe'], $this->types['bar']];
        sort($types);
        $this->assertSame($types, $this->typesFor($id), 'Primary type plus other types, without duplicates or blanks');

        $row = $this->row($id);
        $this->assertSame('espresso-bar', $row->alias, 'Alias is made from the title');
        $this->assertSame('{}', $row->customfieldsdata);
    }

    public function testSaveReplacesTypesOfExistingLocation()
    {
        $model = $this->getModel();
        $model->save($this->locationData(['othertypes' => [$this->types['bar']]]));
        $id = (int)$model->getState('location.id');

        $model = $this->getModel();
        $this->assertTrue($model->save($this->locationData([
            'id'         => $id,
            'type'       => $this->types['park'],
            'othertypes' => [],
        ])));

        $this->assertSame([$this->types['park']], $this->typesFor($id));
    }

    public function testSaveSplitsReadMore()
    {
        $model = $this->getModel();
        $model->save($this->locationData([
            'description' => '<p>Intro</p><hr id="system-readmore" /><p>The rest</p>',
        ]));

        $row = $this->row((int)$model->getState('location.id'));

        $this->assertSame('<p>Intro</p>', $row->description);
        $this->assertSame('<p>The rest</p>', $row->fulldescription);
    }

    public function testNewLocationsAreOrderedLastWithinTheirMap()
    {
        $this->insert('focalpoint_locations', ['map_id' => $this->mapId, 'ordering' => 1, 'alias' => 'a']);
        $this->insert('focalpoint_locations', ['map_id' => $this->mapId, 'ordering' => 2, 'alias' => 'b']);
        $this->insert('focalpoint_locations', ['map_id' => 999, 'ordering' => 10, 'alias' => 'c']);

        $model = $this->getModel();
        $model->save($this->locationData());

        $this->assertEquals(3, $this->row((int)$model->getState('location.id'))->ordering);
    }

    public function testSaveAsCopyMakesUnpublishedCopyWithNewAlias()
    {
        $model = $this->getModel();
        $model->save($this->locationData(['alias' => 'espresso-bar']));
        $originalId = (int)$model->getState('location.id');

        $this->createApplication(
            'administrator',
            ['option' => 'com_focalpoint', 'task' => 'save2copy', 'id' => $originalId],
            new TestUser(42, $this->permissions())
        );
        $model = $this->getModel();
        $this->assertTrue($model->save($this->locationData(['alias' => 'espresso-bar'])));

        $copy = $this->row((int)$model->getState('location.id'));
        $this->assertNotEquals($originalId, $copy->id);
        $this->assertSame('Espresso Bar (2)', $copy->title);
        $this->assertSame('espresso-bar-2', $copy->alias);
        $this->assertEquals(0, $copy->state);
    }

    public function testSaveAsCopyWithNewTitleClearsCopiedAlias()
    {
        $model = $this->getModel();
        $model->save($this->locationData(['alias' => 'espresso-bar']));
        $originalId = (int)$model->getState('location.id');

        $this->createApplication(
            'administrator',
            ['option' => 'com_focalpoint', 'task' => 'save2copy', 'id' => $originalId],
            new TestUser(42, $this->permissions())
        );
        $model = $this->getModel();
        $model->save($this->locationData(['title' => 'Espresso Bar North', 'alias' => 'espresso-bar']));

        $this->assertSame('espresso-bar-north', $this->row((int)$model->getState('location.id'))->alias);
    }

    // ---- delete ----

    public function testDeleteRemovesOrphanedTypeLinks()
    {
        $model = $this->getModel();
        $model->save($this->locationData(['othertypes' => [$this->types['bar']]]));
        $id = (int)$model->getState('location.id');

        $pks = [$id];
        $this->assertTrue($this->getModel()->delete($pks));

        $this->assertSame([], $this->typesFor($id));
    }

    // ---- getItem ----

    public function testGetItemJoinsReadMoreAndDecodesJson()
    {
        $id = $this->insert('focalpoint_locations', [
            'map_id'           => $this->mapId,
            'description'      => '<p>Intro</p>',
            'fulldescription'  => '<p>The rest</p>',
            'othertypes'       => json_encode([$this->types['bar']]),
            'customfieldsdata' => json_encode(['h1' => ['hours' => '9-5']]),
            'metadata'         => json_encode(['robots' => 'noindex']),
        ]);

        $item = $this->getModel()->getItem($id);

        $this->assertSame('<p>Intro</p><hr id="system-readmore" /><p>The rest</p>', $item->description);
        $this->assertEquals([$this->types['bar']], $item->othertypes);
        $this->assertSame(['h1' => ['hours' => '9-5']], $item->customfieldsdata);
        $this->assertSame(['robots' => 'noindex'], $item->metadata);
    }

    public function testNewItemIsCreatedByCurrentUser()
    {
        $item = $this->getModel()->getItem(0);

        $this->assertSame(42, (int)$item->created_by);
    }

    // ---- validate ----

    public function testMapLinkNeedsAMap()
    {
        $model = $this->getModel();
        $form  = new Form('location');
        $form->load('<form><field name="linktype"/><field name="maplinkid"/><field name="menulink"/></form>');

        $this->assertFalse($model->validate($form, ['linktype' => 2, 'maplinkid' => '']));
        $this->assertSame([Text::_('COM_FOCALPOINT_ERROR_LINKTYPE_MAPLINKID_REQUIRED')], $model->getErrors());

        $this->assertIsArray($this->getModel()->validate($form, ['linktype' => 2, 'maplinkid' => 3]));
    }

    public function testMenuLinkNeedsAMenuItem()
    {
        $model = $this->getModel();
        $form  = new Form('location');
        $form->load('<form><field name="linktype"/><field name="maplinkid"/><field name="menulink"/></form>');

        $this->assertFalse($model->validate($form, ['linktype' => 4, 'menulink' => '']));
        $this->assertSame([Text::_('COM_FOCALPOINT_ERROR_LINKTYPE_MENULINK_REQUIRED')], $model->getErrors());

        $this->assertIsArray($this->getModel()->validate($form, ['linktype' => 4, 'menulink' => 101]));
    }

    public function testOtherLinkTypesNeedNothingExtra()
    {
        $form = new Form('location');
        $form->load('<form><field name="linktype"/><field name="maplinkid"/><field name="menulink"/></form>');

        foreach ([0, 1, 3] as $linkType) {
            $this->assertIsArray($this->getModel()->validate($form, ['linktype' => $linkType]), (string)$linkType);
        }
    }
}
