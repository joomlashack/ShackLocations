<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2022 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Joomla\View\Admin\AbstractBase;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

defined('_JEXEC') or die();

class FocalpointViewGetstarted extends AbstractBase
{
    /**
     * @var string
     */
    protected $sidebar = null;

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();

        $this->state = $this->model->getState();

        $this->addToolbar();

        $view = Factory::getApplication()->input->getCmd('view');
        FocalpointHelper::addSubmenu($view);

        if (Version::MAJOR_VERSION < 4) {
            $this->sidebar = Sidebar::render();
        }

        parent::display($tpl);
    }

    /**
     * @return void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_FOCALPOINT_TITLE_GETSTARTED'), 'map');

        $user = Factory::getUser();

        if ($user->authorise('core.admin', 'com_focalpoint')) {
            ToolbarHelper::preferences('com_focalpoint');
        }
    }
}
