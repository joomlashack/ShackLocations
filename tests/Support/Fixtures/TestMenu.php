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

namespace Tests\Support\Fixtures;

use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;

/**
 * Site menu built from items the test supplies instead of the #__menu table
 */
class TestMenu extends AbstractMenu
{
    /**
     * @param array[] $items Each item's properties; 'query' and 'params' are arrays
     */
    public function __construct(array $items = [])
    {
        parent::__construct();

        foreach ($items as $item) {
            $item += [
                'menutype'  => 'mainmenu',
                'type'      => 'component',
                'published' => 1,
                'access'    => 1,
                'language'  => '*',
                'home'      => 0,
                'level'     => 1,
                'parent_id' => 1,
                'tree'      => [],
                'route'     => $item['alias'] ?? ('item-' . $item['id']),
            ];
            $item['component'] = $item['query']['option'] ?? '';
            $item['link']      = 'index.php?' . http_build_query($item['query'] ?? []);
            $item['params']    = json_encode($item['params'] ?? []);

            $this->items[$item['id']] = new MenuItem($item);
        }
    }

    /**
     * @inheritDoc
     */
    public function getMenu()
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function load()
    {
        return true;
    }
}
