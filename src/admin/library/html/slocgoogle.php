<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020-2023 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

abstract class JhtmlSlocGoogle
{
    /**
     * @param int|string      $id
     * @param mixed           $params
     * @param mixed           $center
     * @param object|object[] $markerData
     *
     * @return void
     * @throws Exception
     */
    public static function map($id, $params, $center = null, $markerData = [])
    {
        $params = new Registry($params);
        if (static::checkMootools($params)) {
            // Don't load when mootools is loaded
            return;
        }

        $app = Factory::getApplication();

        $apiKey = $params->get('apikey');
        if ($apiKey == false) {
            Factory::getLanguage()->load('com_focalpoint', SLOC_ADMIN);
            $app->enqueueMessage(Text::_('COM_FOCALPOINT_ERROR_MAPS_API_MISSING'), 'warning');

            return;
        }

        $center = new Registry($center);
        $center->def('lat', FocalpointHelper::HOME_LAT);
        $center->def('lng', FocalpointHelper::HOME_LNG);

        if (is_object($markerData)) {
            $markerData = [$markerData];
        }

        $texts = [
            'COM_FOCALPOINT_BUTTON_HIDE_ALL',
            'COM_FOCALPOINT_BUTTON_SHOW_ALL',
            'COM_FOCALPOINT_ERROR_GEOCODE',
            'COM_FOCALPOINT_ERROR_OVERLAY',
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
            'canvasId'       => $params->get('canvasId', 'fp_googleMap'),
            'clusterOptions' => $params->get('clusterOptions'),
            'fitBounds'      => (bool)$params->get('fitbounds'),
            'mapProperties'  => [
                'center'                   => [
                    'lat' => $center->get('lat'),
                    'lng' => $center->get('lng'),
                ],
                'draggable'                => (int)(bool)$params->get('draggable'),
                'fullscreenControl'        => (int)(bool)$params->get('fullscreen'),
                'fullscreenControlOptions' => $params->get('fullscreenOptions', (object)[]),
                'mapTypeControl'           => (int)$params->get('mapTypeControl'),
                'mapTypeId'                => $params->get('mapTypeId'),
                'maxZoom'                  => (int)$params->get('maxzoom') ?: null,
                'panControl'               => (int)(bool)$params->get('panControl'),
                'scrollwheel'              => (int)(bool)$params->get('scrollwheel'),
                'streetViewControl'        => (int)(bool)$params->get('streetViewControl'),
                'styles'                   => $params->get('mapstyle', []),
                'zoom'                     => (int)$params->get('zoom'),
                'zoomControl'              => (int)(bool)$params->get('zoomControl'),
            ],
            'overlay'        => (array)$params->get('overlay'),
            'markerData'     => static::createMarkers($markerData),
            'search'         => [
                'assist' => (string)$params->get('searchassist', ''),
                'radius' => (float)$params->get('resultradius', 15),
                'zoom'   => (int)$params->get('mapsearchzoom', 12),
            ],
            'show'           => [
                'clusters' => (bool)$params->get('markerclusters'),
                'legend'   => (bool)$params->get('showlegend'),
                'listTab'  => (bool)$params->get('locationlist'),
                'markers'  => (bool)$params->get('showmarkers'),
                'search'   => (bool)$params->get('mapsearchenabled'),
            ],
        ]);


        $googleVars = [
            'key'      => $apiKey,
            'callback' => 'Function.prototype',
        ];
        HTMLHelper::_('script', '//maps.googleapis.com/maps/api/js?' . http_build_query($googleVars));

        $init = <<<JSCRIPT
jQuery(document).ready(function ($) {
    let map = new $.sloc.map.google();
    map.init({$options});
    $.sloc.map.register('{$id}', map); 
});
JSCRIPT;
        $app->getDocument()->addScriptDeclaration($init);

        HTMLHelper::_('jquery.framework');
        HTMLHelper::_('script', 'com_focalpoint/infobox.min.js', ['relative' => true]);
        HTMLHelper::_('script', 'com_focalpoint/sloc.min.js', ['relative' => true]);
        HTMLHelper::_('script', 'com_focalpoint/googleMap.min.js', ['relative' => true]);
    }

    /**
     * @param Registry $params
     *
     * @return bool
     * @throws Exception
     */
    protected static function checkMootools(Registry $params): bool
    {
        $app      = Factory::getApplication();
        $mootools = $params->get('mootools');
        $fail     = $mootools == 'fail';

        switch ($mootools) {
            case 'fix':
                $scripts = &$app->getDocument()->_scripts;
                foreach ($scripts as $src => $script) {
                    if (str_contains($src, 'mootools')) {
                        unset($scripts[$src]);
                    }
                }
                $fail = false;
                break;

            case 'warn':
                $app->enqueueMessage('Cannot work with Mootools', 'error');
                break;
        }

        return $fail;
    }

    /**
     * @param object $marker
     *
     * @return string
     */
    protected static function infoboxContent(object $marker): string
    {
        $link = ($marker->params->get('infopopupevent') !== 'hover')
            ? static::infoboxLink($marker)
            : '';

        $content = LayoutHelper::render(
            'marker.infobox.content',
            [
                'marker' => $marker,
                'params' => $marker->params,
                'link'   => $link,
            ],
            null,
            ['component' => 'com_focalpoint']
        );

        if (preg_match_all('/<img.*?src="(image[^"].*?)".*?>/', $content, $images)) {
            $fixed = [];
            foreach ($images[0] as $idx => $source) {
                $imageUri    = HTMLHelper::_('image', $images[1][$idx], null, null, false, 1);
                $fixed[$idx] = str_replace($images[1][$idx], $imageUri, $source);
            }
            $content = str_replace($images[0], $fixed, $content);
        }

        return str_replace(["\t", "\n", "\r",], [' ', ''], $content);
    }

    /**
     * @param object $marker
     *
     * @return string
     */
    protected static function infoboxLink(object $marker): string
    {
        if (empty($marker->link) == false) {
            return LayoutHelper::render(
                'marker.infobox.link',
                [
                    'marker' => $marker,
                    'params' => $marker->params,
                ]
            );
        }

        return '';
    }

    /**
     * @param object[] $markerData
     *
     * @return object[]
     */
    protected static function createMarkers(array $markerData): array
    {
        $markers = [];
        foreach ($markerData as $marker) {
            $popupEvent = $marker->params->get('infopopupevent');

            $markers[] = [
                'id'       => (int)$marker->id,
                'typeId'   => isset($marker->locationtype_id) ? (int)$marker->locationtype_id : null,
                'infoBox'  => [
                    'event'   => $popupEvent,
                    'content' => static::infoboxContent($marker),
                    'link'    => $popupEvent == 'hover' ? static::infoboxLink($marker) : '',
                ],
                'marker'   => $marker->marker,
                'position' => [
                    'lat' => $marker->latitude,
                    'lng' => $marker->longitude,
                ],
            ];
        }

        return $markers;
    }
}
