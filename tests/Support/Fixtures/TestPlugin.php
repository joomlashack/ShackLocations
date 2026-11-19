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

use FocalpointPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * A focalpoint group plugin written the way add-ons are, with on<Event> methods that
 * Joomla registers as legacy listeners
 */
class TestPlugin extends FocalpointPlugin
{
    /**
     * @var array[] Method name and the arguments it received
     */
    public $calls = [];

    /**
     * Skips CMSPlugin's constructor, whose signature changed between Joomla 4 and 5
     *
     * @param DispatcherInterface $dispatcher
     * @param array               $params
     *
     * @return static
     */
    public static function create(DispatcherInterface $dispatcher, array $params = []): self
    {
        $plugin = (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();

        $plugin->params = new Registry($params);
        $plugin->setDispatcher($dispatcher);
        $plugin->registerListeners();

        return $plugin;
    }

    public function isExcluded(int $mapId): bool
    {
        return $this->isMapExcluded($mapId);
    }

    public function onSlocmapBeforeSave(&$data)
    {
        $this->calls[] = ['onSlocmapBeforeSave', func_get_args()];

        $data['text'] = 'Added by plugin';
    }

    public function onSlocmapAfterSave()
    {
        $this->calls[] = ['onSlocmapAfterSave', func_get_args()];
    }
}
