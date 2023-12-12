<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2022-2023 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

abstract class FocalpointModelAdmin extends AdminModel
{
    /**
     * @inheritDoc
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if (empty($item->id) && property_exists($item, 'created_by')) {
            $item->created_by = Factory::getUser()->id;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    protected function prepareTable($table)
    {
        parent::prepareTable($table);

        $ordering = $table->getColumnAlias('ordering');
        if (property_exists($table, $ordering)) {
            if (empty($table->id)) {
                $conditions = $this->getReorderConditions($table);

                $table->reorder($conditions);
                if (empty($table->{$ordering})) {
                    $table->{$ordering} = $table->getNextOrder($conditions);
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function save($data)
    {
        if (parent::save($data)) {
            $this->garbageCollect();

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function delete(&$pks)
    {
        if (parent::delete($pks)) {
            $this->garbageCollect();

            return true;
        }

        return false;
    }

    /**
     * Clear any orhpaned table rows
     *
     * @return void
     * @throws Exception
     */
    protected function garbageCollect(): void
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->delete('#__focalpoint_location_type_xref')
            ->where([
                'location_id NOT IN (SELECT id FROM #__focalpoint_locations)',
                'locationtype_id NOT IN (SELECT id FROM #__focalpoint_locationtypes)',
            ], 'OR');

        $db->setQuery($query)->execute();
    }
}
