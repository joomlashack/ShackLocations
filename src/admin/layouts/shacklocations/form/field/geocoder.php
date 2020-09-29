<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2020 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var array      $displayData
 * @var string     $layoutOutput
 * @var string     $path
 */

Text::script('COM_FOCALPOINT_ERROR_GEOCODE');

// @TODO: refactor this into a proper jQuery plugin
?>
<!-- Button to trigger modal -->
<a id="openGeocoder"
   href="#myModal"
   role="button"
   class="btn btn-mini btn-primary"
   data-toggle="modal">
    <span class="icon-out-2 small"></span>
    <?php echo Text::_('COM_FOCALPOINT_OPEN_GEOCODER'); ?>
</a>

<!-- Modal -->
<div id="myModal"
     class="modal hide fade"
     tabindex="-1"
     role="dialog"
     aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Drag the marker or enter a location</h3>
    </div>

    <div class="modal-body">
        <div class="row-fluid">
            <div id="mapCanvas"></div>
        </div>
        <div class="row-fluid">
            <div class="input-append span12">
                <input class="span6"
                       id="geoaddress"
                       type="text"
                       placeholder="Enter an address...">
                <input type="button"
                       id="fp_searchAddressBtn"
                       value="GeoCode this!"
                       disabled
                       class="btn">
            </div>
        </div>
        <div class="row-fluid">
            <b>Current position:</b>
            <div id="info"></div>
        </div>

    </div>
    <div class="modal-footer">
        <button class="btn"
                data-dismiss="modal"
                aria-hidden="true">
            Close
        </button>
        <button id="saveLatLng"
                class="btn btn-primary"
                data-dismiss="modal">
            Save Lat/Lng
        </button>
    </div>
</div>

<script>
    ;(function($) {
        let latLng;

        function initialise() {
            let geocoder      = new google.maps.Geocoder(),
                map           = null,
                marker        = null,
                zoom          = 15,
                $latitude     = $('#jform_latitude'),
                $longitude    = $('#jform_longitude'),
                $searchButton = $('#fp_searchAddressBtn'),
                startLat      = $latitude.val(),
                startLng      = $longitude.val();

            zoom     = (startLat && startLng) ? 15 : 2;
            startLat = startLat || 27.6648274;
            startLng = startLng || -81.5157535;

            latLng = new google.maps.LatLng(startLat, startLng);
            map    = new google.maps.Map(document.getElementById('mapCanvas'), {
                zoom     : zoom,
                center   : latLng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            marker = new google.maps.Marker({
                position : latLng,
                title    : 'Point A',
                map      : map,
                draggable: true
            });

            // Update current position info.
            updateMarkerPosition(latLng);

            google.maps.event.addListener(marker, 'drag', function() {
                updateMarkerPosition(marker.getPosition());
            });

            $('#geoaddress')
                .on('blur', function() {
                    if (this.value === '') {
                        $searchButton.attr('disabled', true);
                    }
                })
                .on('focus', function() {
                    $searchButton.attr('disabled', false);
                });

            $searchButton
                .on('click', function(evt) {
                    evt.preventDefault();

                    let geoaddress = document.getElementById('geoaddress').value;
                    geocoder.geocode({'address': geoaddress}, function(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            map.setCenter(results[0].geometry.location);
                            marker.setPosition(results[0].geometry.location);
                            map.setZoom(15);
                            updateMarkerPosition(marker.getPosition());
                        } else {
                            alert(Joomla.Text._('COM_FOCALPOINT_ERROR_GEOCODE').replace('%s', status));
                        }
                    });
                });

            $('#openGeocoder').on('click', function() {
                setTimeout(function() {
                    google.maps.event.trigger(map, 'resize');
                    map.panTo(marker.getPosition());
                }, 800);
            });

            $('#saveLatLng').click(function() {
                $latitude.val(marker.getPosition().lat());
                $longitude.val(marker.getPosition().lng());
            });
        }

        function updateMarkerPosition(latLng) {
            document.getElementById('info').innerHTML = [
                latLng.lat(),
                latLng.lng()
            ].join(', ');
        }

        // Onload handler to fire off the app.
        google.maps.event.addDomListener(window, 'load', initialise);
    })(jQuery);
</script>
