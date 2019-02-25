<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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

defined('_JEXEC') or die();

if (!defined('SHACKLOC_LOADED')) {
    define('SHACKLOC_LOADED', true);

    // Application specific loads
    switch (JFactory::getApplication()->getName()) {
        case 'site':
            JHtml::_('stylesheet', 'components/com_focalpoint/assets/css/focalpoint.css');
            break;

        case 'administrator':
            JHtml::_('stylesheet', 'administrator/components/com_focalpoint/assets/css/focalpoint.css');
            JLoader::register('FocalpointHelper', __DIR__ . '/helpers/focalpoint.php');
            JLoader::register('mapsAPI', __DIR__ . '/helpers/maps.php');

            // Alledia Framework
            if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
                $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

                if (file_exists($allediaFrameworkPath)) {
                    require_once $allediaFrameworkPath;
                } else {
                    JFactory::getApplication()
                        ->enqueueMessage(JText::_('COM_FOCALPOINT_ERROR_FRAMEWORK_NOT_FOUND'), 'error');
                }
            }
            break;
    }
}
