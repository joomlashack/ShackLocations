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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();

class FocalpointViewLocationtype extends AbstractList
{
    /**
     * @var CMSObject
     */
    protected $item;

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

        $title = 'COM_FOCALPOINT_TITLE_LOCATIONTYPE_' . ($isNew ? 'ADD' : 'EDIT');
        ToolbarHelper::title(Text::_($title), 'location-type');

        if (
            $user->authorise('core.edit', 'com_focalpoint')
            || $user->authorise('core.create', 'com_focalpoint')
        ) {
            ToolbarHelper::apply('locationtype.apply');
            ToolbarHelper::save('locationtype.save');
        }

        if ($user->authorise('core.create', 'com_focalpoint')) {
            ToolbarHelper::save2new('locationtype.save2new');
        }

        if (!$isNew && $user->authorise('core.create', 'com_focalpoint')) {
            ToolbarHelper::save2copy('locationtype.save2copy');
        }

        if (!$this->item->get('id')) {
            ToolbarHelper::cancel('locationtype.cancel');

        } else {
            ToolbarHelper::cancel('locationtype.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
