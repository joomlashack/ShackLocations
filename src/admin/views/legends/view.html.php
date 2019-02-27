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

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;

defined('_JEXEC') or die;

class FocalpointViewLegends extends JViewLegacy
{
    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var Pagination
     */
    protected $pagination = null;

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
        $app = JFactory::getApplication();

        try {
            /** @var FocalpointModellegends $model */
            $model = $this->getModel();

            $this->state         = $model->getState();
            $this->items         = $model->getItems();
            $this->pagination    = $model->getPagination();
            $this->filterForm    = $model->getFilterForm();
            $this->activeFilters = $model->getActiveFilters();

            // Check for errors.
            if ($errors = $model->getErrors()) {
                throw new Exception(implode("\n", $errors));
            }

            $this->addToolbar();

            FocalpointHelper::addSubmenu('legends');
            $this->sidebar = JHtmlSidebar::render();

            /*
             * This is part of the getting started walk through. If we've gotten this far then the
             * user has successfully saved their configuration and defined a map.
             * Check we have at least one legend defined
             */
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')->from('#__focalpoint_legends');

            if (!$db->setQuery($query)->loadResult()) {
                JFactory::getApplication()->input->set('task', 'showhelp');
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
        $user = JFactory::getUser();

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LEGENDS'), 'list-2');

        if ($user->authorise('core.create', 'com_focalpoint')) {
            JToolBarHelper::addNew('legend.add');
        }

        if ($user->authorise('core.edit', 'com_focalpoint')) {
            JToolBarHelper::editList('legend.edit');
        }

        if ($user->authorise('core.edit.state', 'com_focalpoint')) {
            JToolBarHelper::publishList('legends.publish');
            JToolBarHelper::unpublishList('legends.unpublish');
            JToolBarHelper::checkin('legends.checkin');
        }

        if ($this->state->get('filter.state') == -2 && $user->authorise('core.delete', 'com_focalpoint')) {
            JToolBarHelper::deleteList('', 'legends.delete');

        } elseif ($user->authorise('core.edit.state', 'com_focalpoint')) {
            JToolBarHelper::trash('legends.trash');
        }

        if ($user->authorise('core.admin', 'com_focalpoint')) {
            JToolBarHelper::preferences('com_focalpoint');
        }
    }
}
