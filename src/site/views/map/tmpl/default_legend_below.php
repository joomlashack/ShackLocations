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

defined('_JEXEC') or die('Restricted access');

    $data = $this->item->markerdata;
    $ulclass    = "";
    $liclass    = "";
    $html       = "";
    $first      = true;
	$columns = 4;
	$count = 0;

	$html .='<div class="row-fluid">';
    foreach ($data as $item) {
        if ($item->legendalias != $ulclass) {
            $ulclass = $item->legendalias;
            if (!$first) {
                $html .="</ul></div>";
				$count +=1 ;
				if ($count%$columns == 0) {
					$html .='</div>';
					$html .='<div class="row-fluid">';
				}
            }
            $html .= '<div class="span'.(12/$columns).' '.$ulclass.'"><h4>'.$item->legend.'<small>'.$item->legendsubtitle.'</small></h4>';
            $html .= '<ul class="sidebar">';
            $first = false;
        }
        if ($liclass != $item->locationtypealias) {
            $html .= "<li><a data-marker-type='".$item->locationtype_id."' class='active markertoggles markers-".$item->locationtypealias."' href='#'>".$item->locationtype."</a></li>";
            $liclass = $item->locationtypealias;
        }
    }
    $html .="</ul>";
	$html .="</div>";
	$html .="</div>";
	$html .= $this->loadTemplate('legend_buttons');
    echo $html;
?>
