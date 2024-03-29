<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2024 Joomlashack. All rights reserved
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
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var array      $displayData
 * @var string     $layoutOutput
 * @var string     $path
 */

$showLabel = empty($displayData['showlabel']) ? true : $displayData['showlabel'];
$label     = empty($displayData['label']) ? null : $displayData['label'];
$value     = empty($displayData['data']) ? null : $displayData['data'];

if ($value) :
    if ($url = empty($value['url']) ? null : $value['url']) :
        $link = HTMLHelper::_(
            'link',
            $url,
            empty($value['linktext']) ? $url : $value['linktext'],
            empty($value['target']) ? null : 'target="_blank"'
        );

        echo '<p class="fp_customfield fp_link">';
        if ($showLabel && $label) :
            echo sprintf('<span class="fp_label">%s: </span>', $label);
        endif;
        echo $link;
        echo '</p>';
    endif;
endif;
