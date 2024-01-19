<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021-2024 Joomlashack. All rights reserved
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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var mixed[]    $displayData
 * @var string     $layoutOutput
 * @var string     $path
 */

extract($displayData);
/**
 * @var object   $marker
 * @var Registry $params
 */
?>
<p class="infoboxlink">
    <?php
    echo HTMLHelper::_(
        'link',
        Route::_($marker->link),
        Text::_('COM_FOCALPOINT_FIND_OUT_MORE'),
        ['title' => $marker->title]
    );
    ?>
</p>

