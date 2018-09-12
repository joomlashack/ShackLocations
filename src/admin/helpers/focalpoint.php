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

defined('_JEXEC') or die;

/**
 * Focalpoint helper.
 */
class FocalpointHelper extends JHelperContent
{
    public static $extension = 'com_focalpoint';

    /**
     * Quick and dirty debug.
     */
    public static function printNdie($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die();
    }


    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName)
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_FOCALPOINT_TITLE_MAPS'),
            'index.php?option=com_focalpoint&view=maps',
            $vName == 'maps'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_FOCALPOINT_TITLE_LEGENDS'),
            'index.php?option=com_focalpoint&view=legends',
            $vName == 'legends'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_FOCALPOINT_TITLE_LOCATIONTYPES'),
            'index.php?option=com_focalpoint&view=locationtypes',
            $vName == 'locationtypes'
        );

        JHtmlSidebar::addEntry(
            JText::_('COM_FOCALPOINT_TITLE_LOCATIONS'),
            'index.php?option=com_focalpoint&view=locations',
            $vName == 'locations'
        );

    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return    JObject
     * @since    1.6
     */
    public static function getActions($component = '', $section = '', $id = 0)
    {
        $user = JFactory::getUser();
        $result = new JObject;

        $assetName = 'com_focalpoint';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }
}
