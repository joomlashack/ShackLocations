<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2020 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user  = Factory::getUser();
        $isNew = empty($this->item->id);

        ToolbarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATIONTYPE'), 'location');

        if ($user->authorise('core.edit', 'com_focalpoint')
            || $user->authorise('core.create', 'com_focalpoint')
        ) {
            ToolBarHelper::apply('locationtype.apply');
            ToolBarHelper::save('locationtype.save');
        }

        if ($user->authorise('core.create', 'com_focalpoint')) {
            ToolBarHelper::save2new('locationtype.save2new');
        }

        if (!$isNew && $user->authorise('core.create', 'com_focalpoint')) {
            ToolBarHelper::save2copy('locationtype.save2copy');
        }

        if (!($this->item->get('id'))) {
            ToolBarHelper::cancel('locationtype.cancel');

        } else {
            ToolBarHelper::cancel('locationtype.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
