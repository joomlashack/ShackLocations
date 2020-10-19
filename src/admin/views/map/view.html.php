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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

class FocalpointViewMap extends HtmlView
{
    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var CMSObject
     */
    protected $item = null;

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        try {
            /** @var FocalpointModelMap $model */
            $model = $this->getModel();

            $this->state = $model->getState();
            $this->item  = $model->getItem();
            $this->form  = $model->getForm();

            if (count($errors = $this->get('Errors'))) {
                throw new Exception(implode("\n", $errors));
            }

            $this->addToolbar();

            PluginHelper::importPlugin('focalpoint');
            $app->triggerEvent('onSlocmapBeforeLoad', [&$this->item]);

            parent::display($tpl);

            echo FocalpointHelper::renderAdminFooter();

        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

        } catch (Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }
    }

    /**
     * @throws Exception
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user  = Factory::getUser();
        $isNew = ($this->item->id == 0);

        $checkedOut = !empty($this->item->checked_out) && $this->item->checked_out != $user->get('id');

        $title = 'COM_FOCALPOINT_TITLE_MAP_' . ($isNew ? 'ADD' : 'EDIT');
        ToolbarHelper::title(Text::_($title), 'compass');

        if (!$checkedOut) {
            if ($user->authorise('core.edit', 'com_focalpoint')
                || $user->authorise('core.create', 'com_focalpoint')
            ) {
                ToolBarHelper::apply('map.apply');
                ToolBarHelper::save('map.save');
            }

            if ($user->authorise('core.create', 'com_focalpoint')) {
                ToolBarHelper::save2new('map.save2new');
            }
        }

        if (!$isNew && $user->authorise('core.create', 'com_focalpoint')) {
            ToolBarHelper::save2copy('map.save2copy');
        }

        ToolBarHelper::cancel('map.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
