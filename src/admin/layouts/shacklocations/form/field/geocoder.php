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

$noAPI = sprintf(
    '<span class="alert alert-error">%s</span>',
    Text::_('COM_FOCALPOINT_ERROR_MAPS_API_MISSING')
);

$defaultCenter = json_encode([
    'lat' => FocalpointHelper::HOME_LAT,
    'lng' => FocalpointHelper::HOME_LNG
]);
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
        <h3 id="myModalLabel"><?php echo Text::_('COM_FOCALPOINT_GEOCODER_DRAG'); ?></h3>
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
            <b><?php echo Text::_('COM_FOCALPOINT_GEOCODER_CURRENT'); ?></b>
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
        let defaultCenter = <?php echo $defaultCenter;  ?>,
            mapCanvas     = document.getElementById('mapCanvas');

        if (google.maps) {
            google.maps.event.addDomListener(window, 'load', function() {
                let geocoder      = new google.maps.Geocoder(),
                    $latitude     = $('#jform_latitude'),
                    $longitude    = $('#jform_longitude'),
                    $searchButton = $('#fp_searchAddressBtn'),
                    startLat      = $latitude.val(),
                    startLng      = $longitude.val();

                let zoom = (startLat && startLng) ? 15 : 2;

                startLat   = startLat || defaultCenter.lat;
                startLng   = startLng || defaultCenter.lng;
                let latLng = new google.maps.LatLng(startLat, startLng);

                let map = new google.maps.Map(
                    mapCanvas,
                    {
                        zoom     : zoom,
                        center   : latLng,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    }
                );

                let marker = new google.maps.Marker({
                    position : latLng,
                    title    : 'Point A',
                    map      : map,
                    draggable: true
                });

                let updateMarkerPosition = function(latLng) {
                    document.getElementById('info').innerHTML = [
                        latLng.lat(),
                        latLng.lng()
                    ].join(', ');
                }

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
                                alert(
                                    Joomla.Text._('COM_FOCALPOINT_ERROR_GEOCODE', '*ERROR: %s')
                                        .replace('%s', status)
                                );
                            }
                        });
                    });

                $('#openGeocoder').on('click', function() {
                    setTimeout(function() {
                        google.maps.event.trigger(map, 'resize');
                        map.panTo(marker.getPosition());
                    }, 800);
                });

                $('#saveLatLng').on('click', function() {
                    $latitude.val(marker.getPosition().lat());
                    $longitude.val(marker.getPosition().lng());
                });
            });

        } else {
            $(mapCanvas).html('<?php echo $noAPI; ?>');
        }
    })(jQuery);
</script>
