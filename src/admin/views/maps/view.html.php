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

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();

class FocalpointViewMaps extends JViewLegacy
{
    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var Pagination
     */
    protected $pagination;

    /**
     * @var Form
     */
    public $filterForm = null;

    /**
     * @var mixed[]
     */
    public $activeFilters = null;

    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getApplication();

        try {
            /** @var FocalpointModelMaps $model */
            $model = $this->getModel();

            $this->state         = $model->getState();
            $this->items         = $model->getItems();
            $this->pagination    = $model->getPagination();
            $this->filterForm    = $model->getFilterForm();
            $this->activeFilters = $model->getActiveFilters();

            if ($errors = $model->getErrors()) {
                throw new Exception(implode('<br>', $errors));
            }

            FocalpointHelper::addSubmenu('maps');
            $this->sidebar = JHtmlSidebar::render();

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
                $app->input->set('task', 'showhelp');
            }

            parent::display($tpl);

            echo FocalpointHelper::renderAdminFooter();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

        } catch (Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Add the page title and toolbar.
     *
     * @since    1.6
     */
    protected function addToolbar()
    {
        $user = Factory::getUser();

        ToolbarHelper::title(JText::_('COM_FOCALPOINT_TITLE_MAPS'), 'compass');

        if ($user->authorise('core.create', 'com_focalpoint')) {
            ToolBarHelper::addNew('map.add');
        }

        if ($user->authorise('core.edit', 'com_focalpoint')) {
            ToolBarHelper::editList('map.edit');
        }

        if ($user->authorise('core.edit.state', 'com_focalpoint')) {
            ToolBarHelper::publishList('maps.publish');
            ToolBarHelper::unpublishList('maps.unpublish');
            ToolBarHelper::checkin('maps.checkin');
        }

        if ($user->authorise('core.delete', 'com_focalpoint')) {
            if ($this->state->get('filter.state') == -2) {
                ToolBarHelper::deleteList('', 'maps.delete');

            } else {
                ToolBarHelper::trash('maps.trash');
            }

            if ($user->authorise('core.admin', 'com_focalpoint')) {
                ToolBarHelper::preferences('com_focalpoint');
            }
        }
    }
}
