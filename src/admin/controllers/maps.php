<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018 Joomlashack <https://www.joomlashack.com
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
 * along with ShackLocations.  If not, see <http://www.gnu.org/licenses/>.
 */

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

class FocalpointControllerMaps extends JControllerAdmin
{
    public function getModel($name = 'Map', $prefix = 'FocalpointModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function downgrade()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'tabsdata'
                )
            )
            ->from('#__focalpoint_maps');

        $maps = $db->setQuery($query)->loadObjectList();

        $fixed = 0;
        foreach ($maps as $map) {
            if ($tabsdata = json_decode($map->tabsdata)) {
                if (isset($tabsdata->tabs)) {
                    $newData = array();
                    foreach ($tabsdata as $key => $data) {
                        if ($key == 'tabs') {
                            foreach ($data as $hash => $tab) {
                                $newData[$hash] = $tab;
                            }
                        } else {
                            $newData[$key] = $data;
                        }
                    }
                    if ($newData) {
                        $map->tabsdata = json_encode($newData);
                        $fixed += $db->updateObject('#__focalpoint_maps', $map, 'id');
                    }
                }
            }
        }
        echo 'Fixed: ' . $fixed;
    }
}
