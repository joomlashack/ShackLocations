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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class FocalpointViewLocationtype extends JViewLegacy
{
    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var CMSObject
     */
    protected $item;

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
        /** @var FocalpointModellocationtype $model */
        $model = $this->getModel();

        $this->state = $model->getState();
        $this->item  = $model->getItem();
        $this->form  = $model->getForm();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        parent::display($tpl);

        echo FocalpointHelper::renderAdminFooter();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user  = JFactory::getUser();
        $isNew = empty($this->item->id);

        $canDo = FocalpointHelper::getActions();

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATIONTYPE'), 'location');

        if ($canDo->get('core.edit') || ($canDo->get('core.create'))) {
            JToolBarHelper::apply('locationtype.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('locationtype.save', 'JTOOLBAR_SAVE');
        }

        if ($canDo->get('core.create')) {
            JToolBarHelper::custom(
                'locationtype.save2new',
                'save-new.png',
                'save-new_f2.png',
                'JTOOLBAR_SAVE_AND_NEW',
                false
            );
        }

        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::custom(
                'locationtype.save2copy',
                'save-copy.png',
                'save-copy_f2.png',
                'JTOOLBAR_SAVE_AS_COPY',
                false
            );
        }

        if (!($this->item->get('id'))) {
            JToolBarHelper::cancel('locationtype.cancel', 'JTOOLBAR_CANCEL');

        } else {
            JToolBarHelper::cancel('locationtype.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
