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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class FocalpointViewMap extends AbstractForm
{
    /**
     * @var CMSObject
     */
    protected $item = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        try {
            $this->model  = $this->getModel();
            $this->state  = $this->model->getState();
            $this->item   = $this->model->getItem();
            $this->form   = $this->model->getForm();
            $this->params = $this->extension->params;

            $this->addToolbar();

            PluginHelper::importPlugin('focalpoint');
            $this->app->triggerEvent('onSlocmapBeforeLoad', [&$this->item]);

            parent::display($tpl);

        } catch (Throwable $error) {
            $this->app->enqueueMessage($error->getMessage(), 'error');
        }
    }

    /**
     * @return void
     */
    protected function addToolbar()
    {
        $this->app->input->set('hidemainmenu', true);

        $user  = Factory::getUser();
        $isNew = ($this->item->id == 0);

        $checkedOut = !empty($this->item->checked_out) && $this->item->checked_out != $user->get('id');

        $title = 'COM_FOCALPOINT_TITLE_MAP_' . ($isNew ? 'ADD' : 'EDIT');
        ToolbarHelper::title(Text::_($title), 'compass');

        if (!$checkedOut) {
            if (
                $user->authorise('core.edit', 'com_focalpoint')
                || $user->authorise('core.create', 'com_focalpoint')
            ) {
                ToolbarHelper::apply('map.apply');
                ToolbarHelper::save('map.save');
            }

            if ($user->authorise('core.create', 'com_focalpoint')) {
                ToolbarHelper::save2new('map.save2new');
            }
        }

        if (!$isNew && $user->authorise('core.create', 'com_focalpoint')) {
            ToolbarHelper::save2copy('map.save2copy');
        }

        ToolbarHelper::cancel('map.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
