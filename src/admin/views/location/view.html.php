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

use Alledia\Framework\Joomla\View\Admin\AbstractForm;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();

class FocalpointViewLocation extends AbstractForm
{
    /**
     * @var object
     */
    protected $item = null;

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();
        $this->state = $this->model->getState();
        $this->item  = $this->model->getItem();
        $this->form  = $this->model->getForm();

        $this->addToolbar();

        parent::display($tpl);
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

        $title = 'COM_FOCALPOINT_TITLE_LOCATION_' . ($isNew ? 'ADD' : 'EDIT');
        ToolbarHelper::title(Text::_($title), 'location.png');

        if (!$checkedOut) {
            if ($canDo->get('core.edit') || ($canDo->get('core.create'))) {
                ToolbarHelper::apply('location.apply');
                ToolbarHelper::save('location.save');
            }

            if ($canDo->get('core.create')) {
                ToolbarHelper::save2new('location.save2new');
            }
        }

        if (!$isNew && $canDo->get('core.create')) {
            ToolbarHelper::save2copy('location.save2copy');
        }

        ToolbarHelper::cancel('location.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
