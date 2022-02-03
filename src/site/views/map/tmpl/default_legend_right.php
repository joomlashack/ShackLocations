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

defined('_JEXEC') or die();

$markers = $this->chunkLegends($this->item->markerdata);

$html       = [];
$subtitle   = '';
$lastLegend = null;
foreach ($markers as $legend) {
    if ($lastLegend && $lastLegend != $legend->alias) {
        $html[] = '</ul>';
        $html[] = '</div>';
    }

    $subtitle = $legend->subtitle ? sprintf('<small>%s</small>', $legend->subtitle) : '';

    $html[] = sprintf(
        '<div class="%s"><h4>%s%s</h4>',
        $legend->alias,
        $legend->title,
        $subtitle
    );

    $html[] = '<ul class="sidebar">';
    foreach ($legend->markers as $marker) {
        $html[] = sprintf(
            '<li><a data-marker-type="%s" class="active markertoggles markers-%s" href="#">%s</a></li>',
            $marker->locationtype_id,
            $marker->locationtypealias,
            $marker->locationtype
        );
    }
    $html[] = '</ul>';
    $html[] = '</div>';
}

$html[] = $this->loadTemplate('legend_buttons');

echo join("\n", $html);
