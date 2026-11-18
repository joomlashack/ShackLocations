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

use Codeception\Attribute\DataProvider;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Tests\Support\TestCase;

class TablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Table::addIncludePath(SLOC_ADMIN . '/tables');
    }

    /**
     * @param string $name
     *
     * @return Table
     */
    protected function table(string $name): Table
    {
        return Table::getInstance($name, 'FocalpointTable', ['dbo' => $this->db]);
    }

    public static function tableProvider(): array
    {
        return [
            'map'          => ['Map', '#__focalpoint_maps'],
            'legend'       => ['Legend', '#__focalpoint_legends'],
            'locationtype' => ['Locationtype', '#__focalpoint_locationtypes'],
            'location'     => ['Location', '#__focalpoint_locations'],
        ];
    }

    #[DataProvider('tableProvider')]
    public function testTableMatchesSchema(string $name, string $tableName)
    {
        $table = $this->table($name);

        $this->assertSame($tableName, $table->getTableName());
        $this->assertSame('id', $table->getKeyName());
        $this->assertSame('state', $table->getColumnAlias('published'));
    }

    #[DataProvider('tableProvider')]
    public function testAliasIsMadeFromTitle(string $name)
    {
        $table = $this->table($name);

        $table->bind(['title' => 'Café & Bar Downtown', 'alias' => '']);

        $this->assertSame('cafe-bar-downtown', $table->alias);
    }

    #[DataProvider('tableProvider')]
    public function testGivenAliasIsMadeUrlSafe(string $name)
    {
        $table = $this->table($name);

        $table->bind(['title' => 'Anything', 'alias' => 'My Custom Alias!']);

        $this->assertSame('my-custom-alias', $table->alias);
    }

    public function testLocationTypeOrderingFilterUsesLocationTypeAsCategory()
    {
        $this->assertSame('type', $this->table('Location')->getColumnAlias('catid'));
    }

    public function testCustomFieldNamesAreMadeSafe()
    {
        $table = $this->table('Locationtype');
        $table->bind([
            'title'        => 'Cafe',
            'customfields' => [
                'h1' => ['type' => 'textbox', 'name' => 'Opening Hours!', 'label' => 'Hours'],
                'h2' => ['type' => 'email', 'name' => 'contact_email', 'label' => 'Email'],
            ],
        ]);

        $this->assertTrue($table->check());

        $this->assertSame(
            [
                'h1' => ['type' => 'textbox', 'name' => 'OpeningHours', 'label' => 'Hours'],
                'h2' => ['type' => 'email', 'name' => 'contact_email', 'label' => 'Email'],
            ],
            json_decode($table->customfields, true)
        );
    }

    public function testCustomFieldsAsJsonAreChecked()
    {
        $table = $this->table('Locationtype');
        $table->bind(['title' => 'Cafe']);
        $table->customfields = json_encode(['h1' => ['type' => 'textbox', 'name' => 'a b', 'label' => 'A']]);

        $table->check();

        $this->assertSame('ab', json_decode($table->customfields, true)['h1']['name']);
    }

    public function testInvalidCustomFieldsAreReported()
    {
        $table = $this->table('Locationtype');
        $table->bind(['title' => 'Cafe']);
        $table->customfields = 'not json';

        $table->check();

        $this->assertSame(Text::_('COM_FOCALPOINT_ERROR_CUSTOMFIELDS_INVALID'), $table->getError());
    }

    public function testLocationTypeWithoutCustomFieldsPassesCheck()
    {
        $table = $this->table('Locationtype');
        $table->bind(['title' => 'Cafe', 'customfields' => '']);

        $this->assertTrue($table->check());
        $this->assertSame('', $table->customfields);
    }
}
