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

defined('_JEXEC') or die();

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

            echo FocalpointHelper::renderAdminFooter();

        } catch (Exception $e) {
            echo $e->getMessage();
            echo $e->getLine() . ': ' . $e->getCode();

        } catch (Throwable $e) {
            echo $e->getMessage();
            echo $e->getLine() . ': ' . $e->getCode();
        }
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
        if (isset($this->item->checked_out)) {
            $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
        $canDo = FocalpointHelper::getActions();

        ToolbarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATION'), 'location.png');

        if (!$checkedOut) {
            if ($canDo->get('core.edit') || ($canDo->get('core.create'))) {
                ToolBarHelper::apply('location.apply');
                ToolBarHelper::save('location.save');
            }

            if ($canDo->get('core.create')) {
                ToolBarHelper::save2new('location.save2new');
            }
        }

        if (!$isNew && $canDo->get('core.create')) {
            ToolBarHelper::save2copy('location.save2copy');
        }

        ToolBarHelper::cancel('location.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
