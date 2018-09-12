<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
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
            $html .= '<ul class="sidebar '.$ulclass.'">';
            $first = false;
        }
        if ($liclass != $item->locationtypealias) {
			$html .= "<li><a data-marker-type='".$item->locationtype_id."' class='active markertoggles markers-".$item->locationtypealias."' href='#'>".$item->locationtype."</a></li>";
			$liclass = $item->locationtypealias;
        }
    }
	$html .="</ul></div></div>";
	$html .= $this->loadTemplate('legend_buttons');
    echo $html;
