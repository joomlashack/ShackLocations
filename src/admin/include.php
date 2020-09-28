<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2020 Joomlashack.com. All rights reserved
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
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();

if (!defined('SHACKLOC_LOADED')) {
    define('SHACKLOC_LOADED', true);
    define('SHACKLOC_ADMIN', JPATH_ADMINISTRATOR . '/components/com_focalpoint');
    define('SHACKLOC_SITE', JPATH_SITE . '/components/com_focalpoint');
    define('SHACKLOC_LIBRARY', SHACKLOC_ADMIN . '/library');

    // Setup autoload libraries
    require_once SHACKLOC_LIBRARY . '/AutoLoader.php';
    AutoLoader::registerCamelBase('Focalpoint', SHACKLOC_LIBRARY . '/joomla');

    // Application specific loads
    switch (Factory::getApplication()->getName()) {
        case 'site':
            HTMLHelper::_('stylesheet', 'components/com_focalpoint/assets/css/focalpoint.css');
            break;

        case 'administrator':
            HTMLHelper::_('stylesheet', 'administrator/components/com_focalpoint/assets/css/focalpoint.css');
            JLoader::register('FocalpointHelper', __DIR__ . '/helpers/focalpoint.php');
            JLoader::register('mapsAPI', __DIR__ . '/helpers/maps.php');

            // Alledia Framework
            if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
                $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

                if (file_exists($allediaFrameworkPath)) {
                    require_once $allediaFrameworkPath;
                } else {
                    Factory::getApplication()
                        ->enqueueMessage(JText::_('COM_FOCALPOINT_ERROR_FRAMEWORK_NOT_FOUND'), 'error');
                }
            }
            break;
    }
}
