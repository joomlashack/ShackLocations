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

// *********************************************************
//
// This file generates all the javascript required to show the map, markers and infoboxes.
// In most custom templates this file should not require any changes and can be left as is.
//
// If you need to customise this file, create an override in your template and edit that.
// Copy this file to templates/your+template/html/com_focalpoint/location/default_mapsjs.php
//
// *********************************************************

defined('_JEXEC') or die('Restricted access');

// Load the Google API and initialise the map.

JHtml::_('script', '//maps.googleapis.com/maps/api/js?key=' . $this->item->params->get('apikey'));
JHtml::_('script', 'components/com_focalpoint/assets/js/infobox.js');

$params = (object)array(
    'searchAssist'      => ', ' . $this->item->params->get('searchAssist'),
    'zoomin'            => $this->item->params->get('zoomin'),
    'mapTypeControl'    => $this->item->params->get('mapTypeControl'),
    'zoomControl'       => $this->item->params->get('zoomControl'),
    'scrollwheel'       => $this->item->params->get('scrollwheel'),
    'streetViewControl' => $this->item->params->get('streetViewControl'),
    'panControl'        => $this->item->params->get('panControl'),
    'draggable'         => $this->item->params->get('draggable'),
    'mapTypeId'         => 'google.maps.MapTypeId.' . $this->item->params->get('mapTypeId'),
    'mapstyle'          => $this->item->params->get('mapstyle', '[]')
);
$text = (object)array(
    'geocodeFail' => JText::_('COM_FOCALPOINT_GEOCODE_FAIL', true),
    'searchAddressRequired' => JText::_('COM_FOCALPOINT_SEARCH_ADDRESS_REQUIRED', true)
);

$script = <<<JSCRIPT
function initialize() {
    var mapProp      = {
            center           : new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}),
            zoom             : {$params->zoomin},
            mapTypeControl   : {$params->mapTypeControl},
            zoomControl      : {$params->zoomControl},
            scrollwheel      : {$params->scrollwheel},
            streetViewControl: {$params->streetViewControl},
            panControl       : {$params->panControl},
            draggable        : {$params->draggable},
            mapTypeId        : {$params->mapTypeId},
            styles           : {$params->mapstyle}
        },
        map          = new google.maps.Map(document.getElementById('fp_googleMap'), mapProp),
        markerSets   = [],
        marker       = [],
        infoBox      = [],
        searchAssist = '{$params->searchAssist}';
JSCRIPT;


$infoDescription = '';
if ($this->item->params->get('infoshowaddress') && $this->item->address != '') {
    $infoDescription .= '<p>' . JText::_($this->item->address) . '</p>';
}
if ($this->item->params->get('infoshowphone') && $this->item->phone != '') {
    $infoDescription .= '<p>' . JText::_($this->item->phone) . '</p>';
}
if ($this->item->params->get('infoshowintro') && $this->item->description != '') {
    $infoDescription .= '<p>\'' . JText::_($this->item->description) . '</p>';
}

$boxText = sprintf(
    '<h4>%s</h4><div class="infoboxcontent">%s<div class="infopointer"></div></div>',
    $this->item->title,
    $infoDescription
);
if (preg_match_all('/<img.*?src="(image[^"].*?)".*?>/', $boxText, $images)) {
    $fixed = array();
    foreach ($images[0] as $idx => $source) {
        $imageUri    = JHtml::_('image', $images[1][$idx], null, null, false, 1);
        $fixed[$idx] = str_replace($images[1][$idx], $imageUri, $source);
    }
    $boxText = str_replace($images[0], $fixed, $boxText);
}
$boxText = addslashes(str_replace(array("\n", "\t", "\r"), '', $boxText));

$script .= <<<JSCRIPT
    var myCenter{$this->item->id} = new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude});
    
    marker[{$this->item->id}] = new google.maps.Marker({
        position:myCenter{$this->item->id},
        icon: '{$this->item->marker}'
    });
    
    marker[{$this->item->id}].setMap(map);
    marker[{$this->item->id}].status = 1;    
    var boxText{$this->item->id} = '{$boxText}';
    
    infoBox[{$this->item->id}] = new InfoBox({
        content         : boxText{$this->item->id},
        alignBottom     : true, 
        position        : new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}),
        pixelOffset     : new google.maps.Size(-160, -55),
        maxWidth        : 320,
        zIndex          : null,
        closeBoxMargin  : '7px 5px 1px 1px',
        closeBoxURL     : 'http://www.google.com/intl/en_us/mapfiles/close.gif',
        infoBoxClearance: new google.maps.Size(20, 30)
    });
    
    google.maps.event.addListener(marker[{$this->item->id}], 'click', function() {
        infoBox[{$this->item->id}].open(map,marker[{$this->item->id}]);
    });
JSCRIPT;

if ($this->item->params->get('getdirections')) {
    $script .= <<<JSCRIPT
    jQuery('#fp_searchAddressBtn').on('click', function(evt) {
        evt.preventDefault();

        var \$address = jQuery('#fp_searchAddress'),
            searchText = \$address.val();

        if (!searchText) {
            alert('{$text->searchAddressRequired}');
            return;
        }

        var geocoder = new google.maps.Geocoder();
    
        geocoder.geocode( { address: searchText+searchAssist }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                jQuery('#fp_googleMap_directions').html('');
    
                if (status == google.maps.GeocoderStatus.OK) {
                    var startLocation =	results[0].geometry.location;
                }
                var directionsService = new google.maps.DirectionsService(),
                    directionsDisplay = new google.maps.DirectionsRenderer();
    
                directionsDisplay.setMap(map);
                directionsDisplay.setPanel(document.getElementById('fp_googleMap_directions'));
    
                var request = {
                    origin     : startLocation,
                    destination: myCenter{$this->item->id},
                travelMode : google.maps.DirectionsTravelMode.DRIVING
            };
    
                directionsService.route(request, function(response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        directionsDisplay.setDirections(response);
                    } else {
                        alert('{$text->geocodeFail}' + status);
                    }
                });
    
            } else {
                alert('{$text->geocodeFail}' + status);
            }
        });
    });
JSCRIPT;
}

$script .= <<<JSCRIPT
}       
google.maps.event.addDomListener(window, 'load', initialize);
JSCRIPT;

JFactory::getDocument()->addScriptDeclaration($script);
