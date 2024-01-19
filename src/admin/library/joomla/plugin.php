<?php
/**
 * @package   ShackLocations-Pro
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2022-2024 Joomlashack. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of ShackLocations-Pro.
 *
 * ShackLocations-Pro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * ShackLocations-Pro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ShackLocations-Pro.  If not, see <https://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

abstract class FocalpointPlugin extends CMSPlugin
{
    /**
     * @inheritdoc
     */
    protected $autoloadLanguage = true;

    /**
     * @param int $mapId
     *
     * @return bool
     */
    protected function isMapExcluded(int $mapId): bool
    {
        $excludedMaps = array_map('intval', (array)$this->params->get('excludemaps'));

        return in_array($mapId, $excludedMaps);
    }
}
