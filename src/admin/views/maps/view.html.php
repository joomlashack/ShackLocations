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

use Alledia\Framework\Joomla\View\Admin\AbstractList;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();

class FocalpointViewMaps extends AbstractList
{
    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        try {
            $this->model         = $this->getModel();
            $this->state         = $this->model->getState();
            $this->items         = $this->model->getItems();
            $this->pagination    = $this->model->getPagination();
            $this->filterForm    = $this->model->getFilterForm();
            $this->activeFilters = $this->model->getActiveFilters();

            FocalpointHelper::addSubmenu('maps');
            $this->sidebar = Sidebar::render();

            $this->addToolbar();

            /*
             * This is part of the getting started walk through. If we've gotten this far then the
             * user has successfully saved their configuration.
             * Check we have at least one map defined
             */
            $db = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__focalpoint_maps');

            $mapsExist = $db->setQuery($query)->loadResult();
            if (!$mapsExist) {
                $this->app->input->set('task', 'showhelp');
            }

            parent::display($tpl);

        } catch (Throwable $error) {
            $this->app->enqueueMessage($error->getMessage(), 'error');
        }
    }

    /**
     * @return void
     */
    protected function addToolbar()
    {
        $user = Factory::getUser();

        ToolbarHelper::title(Text::_('COM_FOCALPOINT_TITLE_MAPS'), 'compass');

        if ($user->authorise('core.create', 'com_focalpoint')) {
            ToolbarHelper::addNew('map.add');
        }

        if ($user->authorise('core.edit', 'com_focalpoint')) {
            ToolbarHelper::editList('map.edit');
        }

        if ($user->authorise('core.edit.state', 'com_focalpoint')) {
            ToolbarHelper::publishList('maps.publish');
            ToolbarHelper::unpublishList('maps.unpublish');
            ToolbarHelper::checkin('maps.checkin');
        }

        if ($user->authorise('core.delete', 'com_focalpoint')) {
            if ($this->state->get('filter.state') == -2) {
                ToolbarHelper::deleteList('', 'maps.delete');

            } else {
                ToolbarHelper::trash('maps.trash');
            }

            if ($user->authorise('core.admin', 'com_focalpoint')) {
                ToolbarHelper::preferences('com_focalpoint');
            }
        }
    }
}
