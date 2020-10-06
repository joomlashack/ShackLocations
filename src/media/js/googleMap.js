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
jQuery.sloc = {map: {foo: 'foo'}};

;(function($) {
    $.sloc = $.extend({map: null}, $.sloc);

    $.sloc.map.google = function() {
        let defaults       = {
                canvasId     : 'fp_googleMap',
                mapProperties: {
                    center                  : {
                        latitude : null,
                        longitude: null
                    },
                    zoom                    : null,
                    maxZoom                 : null,
                    mapTypeControl          : null,
                    fullscreenControl       : null,
                    fullscreenControlOptions: null,
                    zoomControl             : null,
                    scrollwheel             : null,
                    streetViewControl       : null,
                    panControl              : null,
                    draggable               : null,
                    mapTypeId               : null,
                    styles                  : null
                },
                infoBox      : {
                    alignBottom   : true,
                    closeBoxMargin: '7px 5px 1px 1px',
                    closeBoxURL   : 'https://www.google.com/intl/en_us/mapfiles/close.gif',
                    content       : null,
                    maxWidth      : 320,
                    zIndex        : null
                },
                list         : {
                    showTab: true
                },
                clusters     : {
                    show: false
                }
            },
            canvas         = null,
            clusterMarkers = [],
            map            = null,
            markers        = [],
            markerSets     = [],
            markerInfoBox  = [],
            mappedMarkers  = [],
            mapinfobox     = false,
            options        = {};

        // Temporary hardocdes
        let
            allowScrollTo   = false,
            fitbounds       = 0,
            listtabfirst    = 0,
            mapsearchprompt = 'Suburb or Postal code',
            mapsearchrange  = 15,
            mapsearchzoom   = 12,
            searchassist    = ', ',
            searchTxt       = '',
            showmapsearch   = 1,
            markerCluster   = null;
        // End Temp hardocdes

        init = function(params) {
            options = $.extend(true, defaults, params);
            canvas  = document.getElementById(options.canvasId);

            options.clusters.show = options.clusters.show && typeof clusterOptions !== 'undefined';

            initMap();
            setMarkers();
        };

        initMap = function() {
            let mapProperties = options.mapProperties,
                mapCenter     = mapProperties.center;

            mapProperties.mapTypeId = google.maps.MapTypeId[mapProperties.mapTypeId] || null;
            if (mapCenter.latitude && mapCenter.longitude) {
                mapProperties.center = new google.maps.LatLng(mapCenter.latitude, mapCenter.longitude);

            } else {
                mapProperties.center = null;
            }

            map = new google.maps.Map(canvas, mapProperties);
        };

        setMarkers = function() {
            $(options.markerData).each(function(index, data) {
                let $listDisplay = null,
                    markerId     = data.id;

                if ($.inArray(markerId, mappedMarkers) === -1) {
                    let myCenter      = new google.maps.LatLng(data.latitude, data.longitude);
                    markers[markerId] = new google.maps.Marker({
                        position: myCenter,
                        icon    : data.marker
                    });

                    let infoBoxData = $.extend({}, defaults.infoBox, {
                        content         : data.infoBox.content,
                        infoBoxClearance: new google.maps.Size(20, 30),
                        pixelOffset     : new google.maps.Size(-160, -55),
                        position        : new google.maps.LatLng(data.latitude, data.longitude)
                    });

                    markerInfoBox[markerId] = new InfoBox(infoBoxData);

                    google.maps.event.addListener(map, 'click', function(e) {
                        // wtf is this? And why doesn't it throw an error?
                        contextMenu:true
                    });

                    if (options.clusters.show) {
                        clusterMarkers.push(markers[markerId]);

                    } else {
                        markers[markerId].setMap(map);
                    }

                    google.maps.event.addListener(markers[markerId], 'click', function() {
                        if (mapinfobox === markerInfoBox[markerId] && mapinfobox.getVisible()) {
                            mapinfobox.close();

                        } else {
                            if (mapinfobox) {
                                mapinfobox.close()
                            }

                            mapinfobox = markerInfoBox[markerId];
                            mapinfobox.open(map, markers[markerId]);
                        }
                    });

                    if (options.list.showTab) {
                        $listDisplay = $('<div class="fp_listitem">' + data.infoBox.content + '</div>');
                        $listDisplay.addClass('fp_list_marker' + markerId)
                        $('#fp_locationlist .fp_ll_holder').append();
                        $listDisplay.status = 0;
                    }

                    markers[markerId].status = 0;
                    markers[markerId].lat    = 45.521642;
                    markers[markerId].lng    = -122.642595;
                }
                markers[markerId].status += 1;

                if ($listDisplay) {
                    $listDisplay.status += 1;
                }

                if (typeof markerSets[markerId] === 'undefined') {
                    markerSets[markerId] = [];
                }

                mappedMarkers.push(markerId);
                markerSets[markerId].push(markerId);
            });
        };
            });
        };

        return {
            init: init
        }
    };
})(jQuery);
