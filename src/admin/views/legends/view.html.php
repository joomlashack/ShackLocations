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
     * @var Registry
     */
    protected $state;

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
        $canDo = FocalpointHelper::getActions($this->state->get('core.admin'));

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LEGENDS'), 'list-2');

        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('legend.add', 'JTOOLBAR_NEW');
        }

        if ($canDo->get('core.edit')) {
            JToolBarHelper::editList('legend.edit', 'JTOOLBAR_EDIT');
        }

        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::custom('legends.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::custom(
                'legends.unpublish',
                'unpublish.png',
                'unpublish_f2.png',
                'JTOOLBAR_UNPUBLISH',
                true
            );

            JToolBarHelper::divider();
            JToolBarHelper::archiveList('legends.archive', 'JTOOLBAR_ARCHIVE');
            JToolBarHelper::custom('legends.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
        }

        if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'legends.delete', 'JTOOLBAR_EMPTY_TRASH');

        } elseif ($canDo->get('core.edit.state')) {
            JToolBarHelper::trash('legends.trash', 'JTOOLBAR_TRASH');
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_focalpoint');
        }
    }
}
