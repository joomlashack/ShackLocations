<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2022 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\View\Admin\AbstractList;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();

abstract class FocalpointViewAdminList extends AbstractList
{
    /**
     * @param string $single
     * @param string $plural
     *
     * @return void
     */
    protected function addToolbar(string $single, string $plural)
    {
        $user = Factory::getUser();

        ToolbarHelper::title(Text::_('COM_FOCALPOINT_TITLE_' . $plural), $single);

        if ($user->authorise('core.create', 'com_focalpoint')) {
            ToolbarHelper::addNew($single . '.add');
        }

        if ($user->authorise('core.edit', 'com_focalpoint')) {
            ToolbarHelper::editList($single . '.edit');
        }

        if ($user->authorise('core.edit.state', 'com_focalpoint')) {
            ToolbarHelper::publishList($plural . '.publish');
            ToolbarHelper::unpublishList($plural . '.unpublish');
            ToolbarHelper::checkin($plural . '.checkin');
        }

        if ($user->authorise('core.delete', 'com_focalpoint')) {
            if ($this->state->get('filter.state') == -2) {
                ToolbarHelper::deleteList('', $plural . '.delete');

            } else {
                ToolbarHelper::trash($plural . '.trash');
            }
        }

        if ($user->authorise('core.admin', 'com_focalpoint')) {
            ToolbarHelper::preferences('com_focalpoint');
        }
    }
}
