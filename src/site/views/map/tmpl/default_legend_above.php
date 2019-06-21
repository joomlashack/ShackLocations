<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018 Joomlashack <https://www.joomlashack.com
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

defined('_JEXEC') or die();

$data    = $this->item->markerdata;
$ulclass = "";
$liclass = "";
$html    = "";
$first   = true;
$columns = 4;
$count   = 0;

$markers    = array();
$lastLegend = null;
$column     = 0;
$subtitles  = false;
foreach ($this->item->markerdata as $marker) {
    if ($lastLegend && $lastLegend != $marker->legendalias) {
        $column++;
    }
    if (!isset($markers[$column])) {
        $markers[$column] = (object)array(
            'alias'    => $marker->legendalias,
            'title'    => $marker->legend,
            'subtitle' => $marker->legendsubtitle,
            'markers'  => array()
        );

        $subtitles = $subtitles || (bool)$marker->legendsubtitle;
    }
    $markers[$column]->markers[] = $marker;

    $lastLegend = $marker->legendalias;
}

$subtitle = '';
$html     = array();
$column   = -1;
foreach ($markers as $legend) {
    $column = (++$column % $columns);
    if ($column == 0) {
        $html[] = '<div class="row-fluid">';
    }

    if ($subtitles) {
        $subtitle = sprintf('<small>%s</small>', $legend->subtitle ?: '&nbsp;');
    }
    $html[] = sprintf(
        '<div class="span%s %s"><h4>%s%s</h4>',
        (int)(12 / $columns),
        $legend->alias,
        $legend->title,
        $subtitle
    );
    $html[] = sprintf('<ul class="sidebar %s">', $legend->alias);

    $lastType = null;
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

    if (($column + 1) >= $columns) {
        $html[] = '</div>';
    }
}
if (($column + 1) < $columns) {
    $html[] = '</div>';
}

$html[] = $this->loadTemplate('legend_buttons');

echo join("\n", $html);
