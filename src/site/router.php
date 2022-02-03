<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2022 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Menu\MenuItem;

defined('_JEXEC') or die();

/*
 * Component Routes
 * @TODO: This is not entirely accurate. Routing needs to be reviewed/improved
 *
 *  http://root/{menu_alias}  <- if menu exists
 *  http://root/{menu_alias}/{location_alias} <- shows location view at menu id
 *  http://root/component/focalpoint/map/id <-- map view
 *  http://root/component/focalpoint/location/id <-- location view
 */

class FocalpointRouter extends JComponentRouterBase
{
    protected $menuItems = null;

    protected $alias = [];

    /**
     * @param array $query
     *
     * @return array
     */
    public function build(&$query)
    {
        $segments = [];

        $menuItem = empty($query['Itemid']) ? $this->menu->getActive() : $this->menu->getItem($query['Itemid']);

        if (!empty($query['view'])) {
            $view = $query['view'];
            unset($query['view']);

        } else {
            $view = $menuItem ? $menuItem->query['view'] : null;
        }

        if ($view != $menuItem->query['view']) {
            if (!empty($query['id'])) {
                $id = $query['id'];
                unset($query['id']);

                if ($targetMenu = $this->findMenu($view, $id)) {
                    $query['Itemid'] = $targetMenu->id;

                } else {
                    $segments[] = $this->getAlias($view, $id);
                }
            }
        }

        return $segments;
    }

    /**
     * @param array $segments
     *
     * @return array
     */
    public function parse(&$segments)
    {
        $vars = [];

        if ($segments) {
            $menuItem = $this->menu->getActive();

            if (!$menuItem || $menuItem->query['view'] !== 'location') {
                $locationAlias = array_pop($segments);

                $db = Factory::getDbo();

                $sqlQuery = $db->getQuery(true)
                    ->select('id, map_id')
                    ->from('#__focalpoint_locations')
                    ->where('alias = ' . $db->quote($locationAlias));

                if (!empty($menuItem)
                    && $menuItem->query['option'] == 'com_focalpoint'
                    && $menuItem->query['view'] == 'map'
                ) {
                    $sqlQuery->where('map_id = ' . (int)$menuItem->getParams()->get('item_id'));
                }

                if ($locationId = (int)$db->setQuery($sqlQuery)->loadResult()) {
                    $vars['view'] = 'location';
                    $vars['id']   = $locationId;
                }
            }
        }

        return $vars;
    }

    /**
     * @param string $view
     * @param string $id
     *
     * @return MenuItem
     */
    protected function findMenu($view, $id)
    {
        if ($this->menuItems === null) {
            $menuItems = $this->menu->getItems('component', 'com_focalpoint');

            $this->menuItems = [];
            foreach ($menuItems as $menuItem) {
                $menuView = $menuItem->query['view'];
                if (!isset($this->menuItems[$menuView])) {
                    $this->menuItems[$menuView] = [];
                }

                $parameterId = $menuItem->getParams()->get('item_id');

                $this->menuItems[$menuView][$parameterId] = $menuItem;
            }
        }

        if (!empty($this->menuItems[$view][$id])) {
            return $this->menuItems[$view][$id];
        }

        return null;
    }

    /**
     * @param string $view
     * @param string $id
     *
     * @return string
     */
    protected function getAlias($view, $id)
    {
        if (empty($this->alias[$view][$id])) {
            $tables = [
                'location' => 'locations',
                'map'      => 'maps'
            ];

            if (!empty($tables[$view])) {
                if (!isset($this->alias[$view])) {
                    $this->alias[$view] = [];
                }

                $db = Factory::getDbo();

                $sqlQuery = $db->getQuery(true)
                    ->select('alias')
                    ->from('#__focalpoint_' . $tables[$view])
                    ->where('id=' . (int)$id);

                $this->alias[$view][$id] = $db->setQuery($sqlQuery)->loadResult();
            }
        }

        if (!empty($this->alias[$view][$id])) {
            return $this->alias[$view][$id];
        }

        return null;
    }
}
