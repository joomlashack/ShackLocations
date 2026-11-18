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

use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use SimpleXMLElement;
use Tests\Support\InstallerLoader;
use Tests\Support\TestCase;

/**
 * The update migrations in install.focalpoint.php, run against a real database
 */
class InstallerScriptTest extends TestCase
{
    /**
     * @var object
     */
    protected $script = null;

    protected function setUp(): void
    {
        parent::setUp();

        InstallerLoader::load();

        $this->createApplication('administrator');
        $this->script = $this->instance(InstallerLoader::CLASS_NAME, [
            'dbo'   => $this->db,
            'app'   => $this->app,
            'debug' => false,
        ]);
    }

    /**
     * @param string $method
     *
     * @return void
     */
    protected function runMigration(string $method): void
    {
        $this->invoke($this->script, $method);
    }

    /**
     * @param string $table
     * @param int    $id
     * @param string $column
     *
     * @return mixed
     */
    protected function loadJson(string $table, int $id, string $column = 'params')
    {
        $key   = $table == 'extensions' ? 'extension_id' : 'id';
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName($column))
            ->from('#__' . $table)
            ->where($this->db->quoteName($key) . ' = ' . $id);

        return json_decode((string)$this->db->setQuery($query)->loadResult(), true);
    }

    /**
     * @param string $table
     * @param int    $id
     * @param string $column
     *
     * @return string
     */
    protected function loadRaw(string $table, int $id, string $column = 'params'): string
    {
        $key   = $table == 'extensions' ? 'extension_id' : 'id';
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName($column))
            ->from('#__' . $table)
            ->where($this->db->quoteName($key) . ' = ' . $id);

        return (string)$this->db->setQuery($query)->loadResult();
    }

    protected function errors(): array
    {
        return array_filter($this->app->messages, function (array $message) {
            return $message['type'] == 'error';
        });
    }

    // ---- customUpdate ----

    protected function setPreviousVersion(string $version): void
    {
        $manifest = new SimpleXMLElement('<extension><version>' . $version . '</version></extension>');

        $this->setProperty($this->script, 'previousManifest', $manifest);
    }

    public function testUpdateFromBefore12ShowsNotice()
    {
        $this->setPreviousVersion('1.1.5');

        $this->assertTrue($this->invoke($this->script, 'customUpdate', [$this->instance(ComponentAdapter::class)]));
        $this->assertSame('notice', $this->app->messages[0]['type'] ?? null);
    }

    public function testUpdateFrom12OrLaterIsSilent()
    {
        $this->setPreviousVersion('2.2.3');

        $this->assertTrue($this->invoke($this->script, 'customUpdate', [$this->instance(ComponentAdapter::class)]));
        $this->assertSame([], $this->app->messages);
    }

    // ---- customPostFlight ----

    public function testPostFlightRunsTheRightStepsForEachType()
    {
        $script = new class extends \com_focalpointInstallerScript {
            public $called = [];

            public function __construct()
            {
            }

            protected function moveMarkers()
            {
                $this->called[] = 'moveMarkers';
            }

            protected function fixMapParameters()
            {
                $this->called[] = 'fixMapParameters';
            }

            protected function updateTabsdata()
            {
                $this->called[] = 'updateTabsdata';
            }

            protected function updateCustomFields()
            {
                $this->called[] = 'updateCustomFields';
            }

            protected function updateCustomFieldsData()
            {
                $this->called[] = 'updateCustomFieldsData';
            }

            protected function removeObsoleteFiles()
            {
                $this->called[] = 'removeObsoleteFiles';
            }

            protected function checkParameters()
            {
                $this->called[] = 'checkParameters';
            }

            protected function fixGestureParameter()
            {
                $this->called[] = 'fixGestureParameter';
            }
        };

        foreach (['install', 'discover_install'] as $type) {
            $script->called = [];
            $this->invoke($script, 'customPostFlight', [$type, null]);
            $this->assertSame(['moveMarkers'], $script->called, $type);
        }

        $script->called = [];
        $this->invoke($script, 'customPostFlight', ['update', null]);
        $this->assertSame(
            [
                'fixMapParameters',
                'updateTabsdata',
                'updateCustomFields',
                'updateCustomFieldsData',
                'removeObsoleteFiles',
                'checkParameters',
                'fixGestureParameter',
            ],
            $script->called
        );

        $script->called = [];
        $this->invoke($script, 'customPostFlight', ['uninstall', null]);
        $this->assertSame([], $script->called);
    }

    // ---- fixGestureParameter ----

    public function testGestureMigrationConvertsComponentMapsAndLocations()
    {
        $component = $this->insert('extensions', [
            'params' => json_encode(['scrollwheel' => '1', 'draggable' => '1', 'zoom' => '10']),
        ]);
        $mapCustom  = $this->insert('focalpoint_maps', [
            'params' => json_encode(['scrollwheel' => '0', 'draggable' => '1']),
        ]);
        $mapDefault = $this->insert('focalpoint_maps', ['params' => json_encode(['zoom' => '5'])]);
        $location   = $this->insert('focalpoint_locations', [
            'params' => json_encode(['scrollwheel' => 'true', 'draggable' => 'false']),
        ]);

        $this->runMigration('fixGestureParameter');

        $this->assertSame([], $this->errors());
        $this->assertSame(['zoom' => '10', 'gestureHandling' => 'greedy'], $this->loadJson('extensions', $component));
        $this->assertSame(['gestureHandling' => 'cooperative'], $this->loadJson('focalpoint_maps', $mapCustom));
        $this->assertSame(['zoom' => '5', 'gestureHandling' => ''], $this->loadJson('focalpoint_maps', $mapDefault));
        $this->assertSame(['gestureHandling' => 'none'], $this->loadJson('focalpoint_locations', $location));
    }

    /**
     * Both old switches off maps to 'none'; only draggable on is cooperative
     */
    public function testGestureMigrationComponentCombinations()
    {
        $expected = [
            ['0', '0', 'none'],
            ['0', '1', 'cooperative'],
            ['1', '0', 'none'],
            ['1', '1', 'greedy'],
        ];

        foreach ($expected as [$scrollwheel, $draggable, $gesture]) {
            $this->db->setQuery('DELETE FROM #__extensions')->execute();
            $id = $this->insert('extensions', [
                'params' => json_encode(['scrollwheel' => $scrollwheel, 'draggable' => $draggable]),
            ]);

            $this->runMigration('fixGestureParameter');

            $this->assertSame(
                ['gestureHandling' => $gesture],
                $this->loadJson('extensions', $id),
                "scrollwheel=$scrollwheel draggable=$draggable"
            );
        }
    }

    public function testGestureMigrationDoesNothingWithoutOldParameters()
    {
        $component = $this->insert('extensions', ['params' => json_encode(['gestureHandling' => 'auto'])]);
        $map       = $this->insert('focalpoint_maps', ['params' => json_encode(['scrollwheel' => '1'])]);

        $this->runMigration('fixGestureParameter');

        $this->assertSame(['gestureHandling' => 'auto'], $this->loadJson('extensions', $component));
        $this->assertSame(
            ['scrollwheel' => '1'],
            $this->loadJson('focalpoint_maps', $map),
            'Maps are only converted with the component'
        );
    }

    public function testGestureMigrationSurvivesEmptyParams()
    {
        $this->insert('extensions', ['params' => json_encode(['scrollwheel' => '1', 'draggable' => '1'])]);
        $map = $this->insert('focalpoint_maps', ['params' => '']);

        $this->runMigration('fixGestureParameter');

        $this->assertSame([], $this->errors());
        $this->assertSame('', $this->loadRaw('focalpoint_maps', $map));
    }

    public function testGestureMigrationSurvivesEmptyComponentParams()
    {
        $component = $this->insert('extensions', ['params' => '']);

        $this->runMigration('fixGestureParameter');

        $this->assertSame([], $this->errors());
        $this->assertSame('', $this->loadRaw('extensions', $component));
    }

    // ---- fixMapParameters ----

    public function testMapParameterStringsBecomeNumericSwitches()
    {
        $this->insert('extensions', ['params' => '{}']);
        $map = $this->insert('focalpoint_maps', [
            'params' => json_encode([
                'a' => 'true',
                'b' => 'on',
                'c' => 'false',
                'd' => 'off',
                'e' => 'null',
                'f' => 'roadmap',
                'g' => '12',
            ]),
        ]);

        $this->runMigration('fixMapParameters');

        $this->assertSame(
            ['a' => '1', 'b' => '1', 'c' => '0', 'd' => '0', 'e' => '0', 'f' => 'roadmap', 'g' => '12'],
            $this->loadJson('focalpoint_maps', $map)
        );
    }

    public function testMapParametersWithoutOldValuesAreUntouched()
    {
        $this->insert('extensions', ['params' => '{}']);
        $params = '{"zoom":"5","showlegend":"1"}';
        $map    = $this->insert('focalpoint_maps', ['params' => $params]);

        $this->runMigration('fixMapParameters');

        $this->assertSame($params, $this->loadRaw('focalpoint_maps', $map));
    }

    public function testMapParameterMigrationSurvivesEmptyParams()
    {
        $this->insert('extensions', ['params' => '{}']);
        $map = $this->insert('focalpoint_maps', ['params' => '']);

        $this->runMigration('fixMapParameters');

        $this->assertSame('', $this->loadRaw('focalpoint_maps', $map));
    }

    public function testComponentParametersAreNormalized()
    {
        $component = $this->insert('extensions', [
            'params' => json_encode([
                'mapTypeControl'  => 'true',
                'maxzoom'         => 'null',
                'mapsearchprompt' => 'Suburb or Postal code',
                'showmarkers'     => 'off',
                'zoom'            => '8',
            ]),
        ]);

        $this->runMigration('fixMapParameters');

        $this->assertSame(
            [
                'mapTypeControl'  => '1',
                'maxzoom'         => '0',
                'mapsearchprompt' => '',
                'showmarkers'     => '0',
                'zoom'            => '8',
            ],
            $this->loadJson('extensions', $component)
        );
    }

    public function testCustomSearchPromptIsKept()
    {
        $component = $this->insert('extensions', [
            'params' => json_encode(['mapsearchprompt' => 'Town', 'mapTypeControl' => 'false', 'showmarkers' => 'on']),
        ]);

        $this->runMigration('fixMapParameters');

        $this->assertSame(
            ['mapsearchprompt' => 'Town', 'mapTypeControl' => '0', 'showmarkers' => '1'],
            $this->loadJson('extensions', $component)
        );
    }

    // ---- updateTabsdata ----

    public function testOldTabsdataIsNestedUnderTabs()
    {
        $map = $this->insert('focalpoint_maps', [
            'tabsdata' => json_encode([
                'abc'      => ['name' => 'One', 'content' => 'First'],
                'mapstyle' => '[{"featureType":"water"}]',
                'def'      => ['name' => 'Two', 'content' => 'Second'],
            ]),
        ]);

        $this->runMigration('updateTabsdata');

        $this->assertSame(
            [
                'tabs'     => [
                    'abc' => ['name' => 'One', 'content' => 'First'],
                    'def' => ['name' => 'Two', 'content' => 'Second'],
                ],
                'mapstyle' => '[{"featureType":"water"}]',
            ],
            $this->loadJson('focalpoint_maps', $map, 'tabsdata')
        );
    }

    public function testCurrentTabsdataIsUntouched()
    {
        $tabsdata = '{"tabs":{"abc":{"name":"One","content":"First"}}}';
        $map      = $this->insert('focalpoint_maps', ['tabsdata' => $tabsdata]);
        $empty    = $this->insert('focalpoint_maps', ['tabsdata' => '']);

        $this->runMigration('updateTabsdata');

        $this->assertSame($tabsdata, $this->loadRaw('focalpoint_maps', $map, 'tabsdata'));
        $this->assertSame('', $this->loadRaw('focalpoint_maps', $empty, 'tabsdata'));
    }

    // ---- updateCustomFields ----

    public function testOldCustomFieldKeysAreSplitIntoHashAndType()
    {
        $type = $this->insert('focalpoint_locationtypes', [
            'customfields' => json_encode([
                'textbox.h1'  => ['name' => 'phone', 'label' => 'Phone'],
                'textarea.h2' => ['name' => 'notes', 'label' => 'Notes', 'loadeditor' => '1'],
            ]),
        ]);

        $this->runMigration('updateCustomFields');

        $this->assertSame(
            [
                'h1' => ['name' => 'phone', 'label' => 'Phone', 'type' => 'textbox'],
                'h2' => ['name' => 'notes', 'label' => 'Notes', 'loadeditor' => '1', 'type' => 'textarea'],
            ],
            $this->loadJson('focalpoint_locationtypes', $type, 'customfields')
        );
    }

    public function testCurrentAndInvalidCustomFieldsAreUntouched()
    {
        $current = json_encode(['h1' => ['type' => 'textbox', 'name' => 'phone', 'label' => 'Phone']]);
        $typeId  = $this->insert('focalpoint_locationtypes', ['customfields' => $current]);
        $invalid = $this->insert('focalpoint_locationtypes', ['customfields' => 'not json']);
        $empty   = $this->insert('focalpoint_locationtypes', ['customfields' => '{}']);

        $this->runMigration('updateCustomFields');

        $this->assertSame($current, $this->loadRaw('focalpoint_locationtypes', $typeId, 'customfields'));
        $this->assertSame('not json', $this->loadRaw('focalpoint_locationtypes', $invalid, 'customfields'));
        $this->assertSame('{}', $this->loadRaw('focalpoint_locationtypes', $empty, 'customfields'));
    }

    // ---- updateCustomFieldsData ----

    public function testOldCustomFieldDataIsKeyedByHash()
    {
        $location = $this->insert('focalpoint_locations', [
            'customfieldsdata' => json_encode([
                'textbox.h1.phone'  => '555-1234',
                'textarea.h2.notes' => 'Open late',
            ]),
        ]);

        $this->runMigration('updateCustomFieldsData');

        $this->assertSame(
            [
                'h1' => ['phone' => '555-1234'],
                'h2' => ['notes' => 'Open late'],
            ],
            $this->loadJson('focalpoint_locations', $location, 'customfieldsdata')
        );
    }

    public function testCurrentCustomFieldDataIsUntouched()
    {
        $current  = json_encode(['h1' => ['phone' => '555-1234']]);
        $location = $this->insert('focalpoint_locations', ['customfieldsdata' => $current]);
        $empty    = $this->insert('focalpoint_locations', ['customfieldsdata' => '']);

        $this->runMigration('updateCustomFieldsData');

        $this->assertSame($current, $this->loadRaw('focalpoint_locations', $location, 'customfieldsdata'));
        $this->assertSame('', $this->loadRaw('focalpoint_locations', $empty, 'customfieldsdata'));
    }

    // ---- checkParameters ----

    public function testInfoPopupEventDefaultsToClick()
    {
        $component = $this->insert('extensions', ['params' => json_encode(['zoom' => '8'])]);

        $this->runMigration('checkParameters');

        $this->assertSame(['zoom' => '8', 'infopopupevent' => 'click'], $this->loadJson('extensions', $component));
    }

    public function testExistingInfoPopupEventIsKept()
    {
        $component = $this->insert('extensions', ['params' => json_encode(['infopopupevent' => 'hover'])]);

        $this->runMigration('checkParameters');

        $this->assertSame(['infopopupevent' => 'hover'], $this->loadJson('extensions', $component));
    }

    public function testEmptyComponentParamsGetDefaults()
    {
        $component = $this->insert('extensions', ['params' => '']);

        $this->runMigration('checkParameters');

        $this->assertSame(['infopopupevent' => 'click'], $this->loadJson('extensions', $component));
    }

    public function testMissingComponentRowIsIgnored()
    {
        $this->runMigration('checkParameters');

        $this->assertSame([], $this->errors());
    }
}
