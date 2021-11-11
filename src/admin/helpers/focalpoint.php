<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Joomla\Extension\Licensed;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die;

abstract class FocalpointHelper extends ContentHelper
{
    // Bradenton, FL
    public const HOME_LAT = '27.6648274';
    public const HOME_LNG = '-81.5157535';

    /**
     * @inheritDoc
     */
    public static function addSubmenu($vName)
    {
        Sidebar::addEntry(
            Text::_('COM_FOCALPOINT_TITLE_MAPS'),
            'index.php?option=com_focalpoint&view=maps',
            $vName == 'maps'
        );
        Sidebar::addEntry(
            Text::_('COM_FOCALPOINT_TITLE_LEGENDS'),
            'index.php?option=com_focalpoint&view=legends',
            $vName == 'legends'
        );
        Sidebar::addEntry(
            Text::_('COM_FOCALPOINT_TITLE_LOCATIONTYPES'),
            'index.php?option=com_focalpoint&view=locationtypes',
            $vName == 'locationtypes'
        );

        Sidebar::addEntry(
            Text::_('COM_FOCALPOINT_TITLE_LOCATIONS'),
            'index.php?option=com_focalpoint&view=locations',
            $vName == 'locations'
        );
    }

    /**
     * @inheritDoc
     */
    public static function getActions($component = '', $section = '', $id = 0)
    {
        $user   = Factory::getUser();
        $result = new CMSObject();

        $assetName = 'com_focalpoint';

        $actions = [
            'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
        ];

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }
}
