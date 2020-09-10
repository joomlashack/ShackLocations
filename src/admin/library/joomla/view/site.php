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

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die();

class FocalpointViewSite extends FocalpointView
{
    public function __construct($config = [])
    {
        parent::__construct($config);

        $lang = Factory::getLanguage();
        $lang->load('com_focalpoint', JPATH_ADMINISTRATOR . '/components/com_focalpoint');
    }

    /**
     * Render the modules in a position
     *
     * @param string $position
     * @param mixed  $attribs
     *
     * @return string
     */
    public static function renderModule($position, $attribs = [])
    {
        $results = ModuleHelper::getModules($position);
        $content = '';

        ob_start();
        foreach ($results as $result) {
            $content .= ModuleHelper::renderModule($result, $attribs);
        }
        ob_end_clean();

        return $content;
    }
}
