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
use Joomla\CMS\Pagination\Pagination;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class FocalpointViewLocationtypes extends JViewLegacy
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
     * @var Registry
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
            /** @var FocalpointModellocationtypes $model */
            $model = $this->getModel();

            $this->state         = $model->getState();
            $this->items         = $model->getItems();
            $this->pagination    = $model->getPagination();
            $this->filterForm    = $model->getFilterForm();
            $this->activeFilters = $model->getActiveFilters();

            if ($errors = $model->getErrors()) {
                throw new Exception(implode("\n", $errors));
            }

            $this->addToolbar();

            FocalpointHelper::addSubmenu('locationtypes');
            $this->sidebar = JHtmlSidebar::render();

            /*
             * This is part of the getting started walk through. If we've gotten this far then the
             * user has successfully saved their configuration, added a map and defined a legend.
             * Check we have at least one location type defined
             */
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__focalpoint_locationtypes');

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
        require_once JPATH_COMPONENT . '/helpers/focalpoint.php';

        $state = $this->get('State');
        $canDo = FocalpointHelper::getActions($state->get('filter.category_id'));

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATIONTYPES'), 'location');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/locationtype';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
                JToolBarHelper::addNew('locationtype.add', 'JTOOLBAR_NEW');
            }

            if ($canDo->get('core.edit') && isset($this->items[0])) {
                JToolBarHelper::editList('locationtype.edit', 'JTOOLBAR_EDIT');
            }

        }

        if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('locationtypes.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH',
                    true);
                JToolBarHelper::custom('locationtypes.unpublish', 'unpublish.png', 'unpublish_f2.png',
                    'JTOOLBAR_UNPUBLISH', true);
            } else {
                if (isset($this->items[0])) {
                    //If this component does not use state then show a direct delete button as we can not trash
                    JToolBarHelper::deleteList('', 'locationtypes.delete', 'JTOOLBAR_DELETE');
                }
            }

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::archiveList('locationtypes.archive', 'JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
                JToolBarHelper::custom('locationtypes.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN',
                    true);
            }
        }

        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'locationtypes.delete', 'JTOOLBAR_EMPTY_TRASH');
                JToolBarHelper::divider();
            } else {
                if ($canDo->get('core.edit.state')) {
                    JToolBarHelper::trash('locationtypes.trash', 'JTOOLBAR_TRASH');
                    JToolBarHelper::divider();
                }
            }
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_focalpoint');
        }
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'a.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
            'a.state'      => JText::_('JSTATUS'),
            'a.title'      => JText::_('JGLOBAL_TITLE'),
            'legend_title' => JText::_('COM_FOCALPOINT_LOCATIONTYPES_LEGEND'),
            'a.created_by' => JText::_('JAUTHOR'),
            'a.id'         => JText::_('JGRID_HEADING_ID')
        );
    }
}
