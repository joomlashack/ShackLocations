<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

abstract class JhtmlSlocGoogle
{
    public static function map($id, $params, $center = null, $markerData = [])
    {
        if (!$params instanceof Registry) {
            $params = new Registry($params);
        }

        if (empty($center)) {
            $center = (object)[
                'lat' => FocalpointHelper::HOME_LAT,
                'lng' => FocalpointHelper::HOME_LNG
            ];

        } elseif (is_array($center)) {
            $center = (object)$center;

        }
        if (!is_object($center) || !isset($center->lat) || !isset($center->lng)) {
            Factory::getApplication()->enqueueMessage('Invalid Position', 'error');
            return;
        }

        if (!is_array($markerData)) {
            $markerData = [$markerData];
        }

        HTMLHelper::_('script', '//maps.googleapis.com/maps/api/js?key=' . $params->get('apikey'));
        HTMLHelper::_('script', 'com_focalpoint/infobox.js', ['relative' => true]);
        HTMLHelper::_('jquery.framework');
        HTMLHelper::_('script', 'com_focalpoint/googleMap.js', ['relative' => true]);

        $texts = [
            'COM_FOCALPOINT_BUTTTON_HIDE_ALL',
            'COM_FOCALPOINT_BUTTTON_SHOW_ALL',
            'COM_FOCALPOINT_ERROR_GEOCODE',
            'COM_FOCALPOINT_NO_LOCATION_TYPES_SELECTED',
            'COM_FOCALPOINT_SEARCH_ADDRESS_REQUIRED',
            'COM_FOCALPOINT_SEARCH_SHOWING',
            'COM_FOCALPOINT_SEARCH_SHOWING_1',
            'COM_FOCALPOINT_SEARCH_WITHIN',
            'COM_FOCALPOINT_SEARCH_WITHIN_1',
        ];
        foreach ($texts as $text) {
            Text::script($text);
        }

        $options = json_encode([
            'clusterOptions' => $params->get('clusterOptions', null),
            'fitBounds'      => (bool)$params->get('fitbounds'),
            'mapProperties'  => [
                'center'                   => [
                    'lat' => $center->lat,
                    'lng' => $center->lng
                ],
                'draggable'                => (int)(bool)$params->get('draggable'),
                'fullscreenControl'        => (int)(bool)$params->get('fullscreen'),
                'fullscreenControlOptions' => $params->get('fullscreenOptions', (object)[]),
                'mapTypeControl'           => (int)$params->get('mapTypeControl'),
                'mapTypeId'                => $params->get('mapTypeId'),
                'maxZoom'                  => $params->get('maxzoom') ?: null,
                'panControl'               => (int)(bool)$params->get('panControl'),
                'scrollwheel'              => (int)(bool)$params->get('scrollwheel'),
                'streetViewControl'        => (int)(bool)$params->get('streetViewControl'),
                'styles'                   => $params->get('mapstyle', []),
                'zoom'                     => (int)$params->get('zoom'),
                'zoomControl'              => (int)(bool)$params->get('zoomControl'),
            ],
            'markerData'     => static::createMarkers($markerData),
            'search'         => [
                'assist' => (string)$params->get('searchassist', ''),
                'radius' => (float)$params->get('resultradius', 15),
                'zoom'   => (int)$params->get('mapsearchzoom', 12)
            ],
            'show'           => [
                'clusters' => (bool)$params->get('markerclusters'),
                'listTab'  => (bool)$params->get('locationlist'),
                'markers'  => (bool)$params->get('showmarkers'),
                'search'   => (bool)$params->get('mapsearchenabled')
            ]
        ]);

        $init = <<<JSINIT
jQuery(document).ready(function ($) {
    window.slocMap  = window.slocMap || {};
    
    let map = new $.sloc.map.google;
    map.init({$options});
    
    window.slocMap['{$id}'] = map;
});
JSINIT;

        Factory::getDocument()->addScriptDeclaration($init);
    }

    public static function infoboxContent($marker)
    {
        //Assemble the infobox.
        $infoDescription = [];
        if ($marker->params->get('infoshowaddress') && $marker->address != '') {
            $infoDescription[] = '<p>' . Text::_($marker->address) . '</p>';
        }
        if ($marker->params->get('infoshowphone') && $marker->phone != '') {
            $infoDescription[] = '<p>' . Text::_($marker->phone) . '</p>';
        }
        if ($marker->params->get('infoshowintro') && $marker->description != '') {
            $infoDescription[] = '<p>' . Text::_($marker->description) . '</p>';
        }

        $boxText = sprintf(
            '<h4>%s</h4><div class="infoboxcontent">%s',
            $marker->title,
            join('', $infoDescription)
        );
        if (preg_match_all('/<img.*?src="(image[^"].*?)".*?>/', $boxText, $images)) {
            $fixed = [];
            foreach ($images[0] as $idx => $source) {
                $imageUri    = HTMLHelper::_('image', $images[1][$idx], null, null, false, 1);
                $fixed[$idx] = str_replace($images[1][$idx], $imageUri, $source);
            }
            $boxText = str_replace($images[0], $fixed, $boxText);
        }

        if (isset($marker->link)) {
            $boxText .= sprintf(
                '<p class="infoboxlink">%s</p>',
                HTMLHelper::_(
                    'link',
                    Route::_($marker->link),
                    Text::_('COM_FOCALPOINT_FIND_OUT_MORE'),
                    ['title' => $marker->title]
                )
            );
        }
        $boxText .= '<div class="infopointer"></div></div>';

        return str_replace(["\t", "\n", "\r",], [' ', ''], $boxText);
    }

    public static function createMarkers(array $markerData)
    {
        $markers = [];
        foreach ($markerData as $marker) {
            $markers[] = [
                'id'       => (int)$marker->id,
                'typeId'   => isset($marker->locationtype_id) ? (int)$marker->locationtype_id : null,
                'infoBox'  => [
                    'content' => static::infoboxContent($marker),
                ],
                'marker'   => $marker->marker,
                'position' => [
                    'lat' => $marker->latitude,
                    'lng' => $marker->longitude
                ]
            ];
        }

        return $markers;
    }
}
