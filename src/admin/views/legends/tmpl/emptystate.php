<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2024 Joomlashack. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

use Alledia\Framework\Factory;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();

$displayData = [
    'textPrefix' => 'COM_FOCALPOINT_LEGENDS',
    'formURL'    => 'index.php?option=com_focalpoint&view=legends',
    'helpURL'    => 'https://www.joomlashack.com/docs/shack-locations/getting-started-with-shack-locations/',
    'icon'       => 'icon-legend',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_focalpoint')) {
    $displayData['createURL'] = 'index.php?option=com_focalpoint&task=legend.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
