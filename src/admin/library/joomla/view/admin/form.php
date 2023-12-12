<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2022-2023 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\View\Admin\AbstractForm;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

abstract class FocalpointViewAdminForm extends AbstractForm
{
    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var CMSObject
     */
    protected $item = null;

    /**
     * @inheritDoc
     */
    protected function setup()
    {
        parent::setup();

        $this->params = $this->extension->params;
    }

    /**
     * @param string $prefix
     *
     * @return void
     */
    protected function addToolbar(string $prefix): void
    {
        $this->app->input->set('hidemainmenu', true);

        $user  = Factory::getUser();
        $isNew = ($this->item->id == 0);

        $title = 'COM_FOCALPOINT_TITLE_' . $prefix . ($isNew ? '_ADD' : '_EDIT');
        ToolbarHelper::title(Text::_($title), $prefix);

        if (empty($this->item->checked_out) || $this->item->checked_out == $user->get('id')) {
            if (
                $user->authorise('core.edit', 'com_focalpoint')
                || $user->authorise('core.create', 'com_focalpoint')
            ) {
                ToolbarHelper::apply($prefix . '.apply');
                ToolbarHelper::save($prefix . '.save');
            }

            if ($user->authorise('core.create', 'com_focalpoint')) {
                ToolbarHelper::save2new($prefix . '.save2new');
            }
        }

        if ($isNew == false && $user->authorise('core.create', 'com_focalpoint')) {
            ToolbarHelper::save2copy($prefix . '.save2copy');
        }

        ToolbarHelper::cancel($prefix . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
