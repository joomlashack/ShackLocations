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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

/**
 * View to edit
 */
class FocalpointViewLocation extends JViewLegacy
{
    /**
     * @var FocalpointModelLocation
     */
    protected $model = null;

    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var object
     */
    protected $item = null;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        try {
            $this->model = $this->getModel();
            $this->state = $this->model->getState();
            $this->item  = $this->model->getItem();
            $this->form  = $this->model->getForm();

            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                throw new Exception(implode("\n", $errors));
            }

            $this->addToolbar();

            parent::display($tpl);

        } catch (Throwable $e) {
            echo $e->getMessage();
            echo $e->getLine() . ': ' . $e->getCode();
        }
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user  = JFactory::getUser();
        $isNew = ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
            $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
        $canDo = FocalpointHelper::getActions();

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATION'), 'location.png');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {

            JToolBarHelper::apply('location.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('location.save', 'JTOOLBAR_SAVE');
        }
        if (!$checkedOut && ($canDo->get('core.create'))) {
            JToolBarHelper::custom('location.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW',
                false);
        }
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::custom('location.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY',
                false);
        }
        if (empty($this->item->id)) {
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
