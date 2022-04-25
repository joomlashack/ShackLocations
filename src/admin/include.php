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

use Alledia\Framework\AutoLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

try {
    $frameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';
    if (is_file($frameworkPath) == false || (include $frameworkPath) == false) {
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            $app->enqueueMessage('[ShackLocations] Joomlashack framework not found', 'error');
        }

        return false;
    }

    if (defined('ALLEDIA_FRAMEWORK_LOADED') && !defined('SLOC_LOADED')) {
        define('SLOC_ADMIN', JPATH_ADMINISTRATOR . '/components/com_focalpoint');
        define('SLOC_SITE', JPATH_SITE . '/components/com_focalpoint');
        define('SLOC_LIBRARY', SLOC_ADMIN . '/library');

        AutoLoader::registerCamelBase('Focalpoint', SLOC_LIBRARY . '/joomla');
        HTMLHelper::addIncludePath(SLOC_LIBRARY . '/html');
        PluginHelper::importPlugin('focalpoint');

        // Application specific loads
        switch (Factory::getApplication()->getName()) {
            case 'site':
                HTMLHelper::_('stylesheet', 'com_focalpoint/focalpoint.css', ['relative' => true]);

                Table::addIncludePath(SLOC_ADMIN . '/tables');
                BaseDatabaseModel::addIncludePath(SLOC_SITE . '/models');
                break;

            case 'administrator':
                HTMLHelper::_('alledia.fontawesome');
                HTMLHelper::_('stylesheet', 'com_focalpoint/admin.css', ['relative' => true]);
                JLoader::register('FocalpointHelper', SLOC_ADMIN . '/helpers/focalpoint.php');
                break;
        }

        define('SLOC_LOADED', 1);
    }

} catch (Throwable $error) {
    Factory::getApplication()
        ->enqueueMessage('[ShackLocations] Unable to initialize: ' . $error->getMessage(), 'error');

    return false;
}

return defined('ALLEDIA_FRAMEWORK_LOADED') && defined('SLOC_LOADED');
