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
            marker         = [],
            markerSets     = [],
            markerInfoBox  = [],
            mappedMarkers  = [],
            mapinfobox     = false;

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

        init = function(options) {
            options = $.extend(true, defaults, options);

            canvas = document.getElementById(options.canvasId);
            options.clusters.show = options.clusters.show && typeof clusterOptions !== 'undefined';

            initMap(options);
            setMarkers(options)
        };

        initMap = function(options) {
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

        setMarkers = function(options) {
            $(options.markerData).each(function(index, data) {
                let $listDisplay = null;

                if ($.inArray(index, mappedMarkers) === -1) {
                    let myCenter  = new google.maps.LatLng(data.latitude, data.longitude);
                    marker[index] = new google.maps.Marker({
                        position: myCenter,
                        icon    : data.marker
                    });

                    let infoBoxData = $.extend({}, defaults.infoBox, {
                        content         : data.infoBox.content,
                        infoBoxClearance: new google.maps.Size(20, 30),
                        pixelOffset     : new google.maps.Size(-160, -55),
                        position        : new google.maps.LatLng(data.latitude, data.longitude)
                    });

                    markerInfoBox[index] = new InfoBox(infoBoxData);

                    google.maps.event.addListener(map, 'click', function(e) {
                        // wtf is this? And why doesn't it throw an error?
                        contextMenu:true
                    });

                    if (options.clusters.show) {
                        clusterMarkers.push(marker[index]);

                    } else {
                        marker[index].setMap(map);
                    }

                    google.maps.event.addListener(marker[index], 'click', function() {
                        if (mapinfobox === markerInfoBox[index] && mapinfobox.getVisible()) {
                            mapinfobox.close();

                        } else {
                            if (mapinfobox) {
                                mapinfobox.close()
                            }

                            mapinfobox = markerInfoBox[index];
                            mapinfobox.open(map, marker[index]);
                        }
                    });

                    if (options.list.showTab) {
                        $listDisplay = $('<div class="fp_listitem">' + data.infoBox.content + '</div>');
                        $listDisplay.addClass('fp_list_marker' + index)
                        $('#fp_locationlist .fp_ll_holder').append();
                        $listDisplay.status = 0;
                    }

                    marker[index].status = 0;
                    marker[index].lat    = 45.521642;
                    marker[index].lng    = -122.642595;
                }
                marker[index].status += 1;

                if ($listDisplay) {
                    $listDisplay.status += 1;
                }

                if (typeof markerSets[index] === 'undefined') {
                    markerSets[index] = [];
                }

                mappedMarkers.push(index);
                markerSets[index].push(index);
            });
        };

        return {
            init: init
        }
    };
})(jQuery);
