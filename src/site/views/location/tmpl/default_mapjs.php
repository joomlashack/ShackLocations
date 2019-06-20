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

$document = JFactory::getDocument();
$document->addScript('//maps.googleapis.com/maps/api/js?key=' . $this->item->params->get('apikey'));
$document->addScript(JURI::base() . 'components/com_focalpoint/assets/js/infobox.js');

$getdirections = $this->item->params->get('getdirections');
$searchassist  = ", " . $this->item->params->get('searchassist');

$script          = '
    function initialize() {
        var mapProp = {
            center:new google.maps.LatLng(' . $this->item->latitude . ',' . $this->item->longitude . '),
            zoom:' . $this->item->params->get('zoomin') . ',
            mapTypeControl: ' . $this->item->params->get('mapTypeControl') . ',
            zoomControl: ' . $this->item->params->get('zoomControl') . ',
            scrollwheel: ' . $this->item->params->get('scrollwheel') . ',
            streetViewControl: ' . $this->item->params->get('streetViewControl') . ',
            panControl: ' . $this->item->params->get('panControl') . ',
            draggable: ' . $this->item->params->get('draggable') . ',
            mapTypeId:google.maps.MapTypeId.' . $this->item->params->get('mapTypeId') . ',
            styles: ' . $this->item->params->get('mapstyle', "[]") . '
        };
        var map=new google.maps.Map(document.getElementById("fp_googleMap"),mapProp);
        var markerSets = new Array();
        var marker= new Array();    
        var infoBox = new Array();
	    var searchassist = "' . $searchassist . '";
';
$infodescription = "";
if ($this->item->params->get('infoshowaddress') && $this->item->address != "") {
    $infodescription .= "<p>" . JText::_($this->item->address) . "</p>";
}
if ($this->item->params->get('infoshowphone') && $this->item->phone != "") {
    $infodescription .= "<p>" . JText::_($this->item->phone) . "</p>";
}
if ($this->item->params->get('infoshowintro') && $this->item->description != "") {
    $infodescription .= $this->item->description;
}
$boxtext = '<h4>' . addslashes($this->item->title) . '</h4><div class=\"infoboxcontent\">' . addslashes(str_replace("src=\"images",
        "src=\"" . JUri::base(true) . "/images", (str_replace(array("\n", "\t", "\r"), '', $infodescription))));
$boxtext .= '<div class=\"infopointer\"></div></div>';

$script .= '
        var myCenter' . $this->item->id . '=new google.maps.LatLng(' . $this->item->latitude . ',' . $this->item->longitude . ');
        marker[' . $this->item->id . ']=new google.maps.Marker({
            position:myCenter' . $this->item->id . ',
            icon: "' . $this->item->marker . '"
        });
        marker[' . $this->item->id . '].setMap(map);
        marker[' . $this->item->id . '].status = 1;    
        var boxText' . $this->item->id . ' = "' . $boxtext . '";

        infoBox[' . $this->item->id . '] = new InfoBox({
            content: boxText' . $this->item->id . ',
            alignBottom: true, 
            position: new google.maps.LatLng(' . $this->item->latitude . ',' . $this->item->longitude . '),
            pixelOffset: new google.maps.Size(-160, -55),
            maxWidth: 320,
            zIndex: null,
            closeBoxMargin: "7px 5px 1px 1px",
            closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
            infoBoxClearance: new google.maps.Size(20, 30)
        });
        
        google.maps.event.addListener(marker[' . $this->item->id . '], \'click\', function() {
            infoBox[' . $this->item->id . '].open(map,marker[' . $this->item->id . ']);
        });';

if ($getdirections) {
    $script .= '
		jQuery("#fp_searchAddressBtn").click(function() {
			geocoder = new google.maps.Geocoder();
			searchTxt = document.getElementById("fp_searchAddress").value;
			if (searchTxt == "") {return false;}
			geocoder.geocode( { "address": searchTxt+searchassist}, function(results, status) {

                if (status == google.maps.GeocoderStatus.OK) {
                    jQuery("#fp_googleMap_directions").html("");
                    if (status == google.maps.GeocoderStatus.OK) {
                        var startLocation =	results[0].geometry.location;
                    }
                    var directionsService = new google.maps.DirectionsService();
                    var directionsDisplay = new google.maps.DirectionsRenderer();

                    directionsDisplay.setMap(map);
                    directionsDisplay.setPanel(document.getElementById("fp_googleMap_directions"));

                    var request = {
                        origin: startLocation,
                        destination: myCenter' . $this->item->id . ',
                        travelMode: google.maps.DirectionsTravelMode.DRIVING
                    };

                    directionsService.route(request, function(response, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                            directionsDisplay.setDirections(response);
                        } else {
                            alert("' . JText::_('COM_FOCALPOINT_GEOCODE_FAIL') . '" + status);
                        }
                    });
				} else {
                    alert("' . JText::_('COM_FOCALPOINT_GEOCODE_FAIL') . '" + status);
				}
			});
		});';
}

$script .= '
    }       
    google.maps.event.addDomListener(window, \'load\', initialize);
';

$document->addScriptDeclaration($script);
