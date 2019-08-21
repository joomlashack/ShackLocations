<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Menu\MenuItem;

defined('_JEXEC') or die();

class FocalpointView extends JViewLegacy
{
    /** @var CMSApplication */
    protected $app = null;

    /**
     * @var MenuItem
     */
    protected $activeMenu = null;

    /**
     * FocalpointView constructor.
     *
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->app = JFactory::getApplication();

        if ($menu = $this->app->getMenu()) {
            $this->activeMenu = $menu->getActive();
        }
    }
}
