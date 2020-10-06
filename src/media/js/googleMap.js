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
                fitBounds    : false,
                mapProperties: {
                    center                  : {
                        lat: null,
                        lng: null
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
                markerData   : [],
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
            markerBase     = {
                id     : null,
                typeId : null,
                infoBox: {
                    content: null
                },
                lat    : null,
                lng    : null,
                marker : null,
            },
            markers        = [],
            markerSets     = [],
            markerInfoBox  = [],
            mappedMarkers  = [],
            mapinfobox     = false,
            options        = {};

        // Temporary hardocdes
        let
            allowScrollTo   = false,
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
            options = $.extend(true, {}, defaults, params);
            canvas  = document.getElementById(options.canvasId);

            options.clusters.show = options.clusters.show && typeof clusterOptions !== 'undefined';

            initMap();
            setMarkers();

            updateActiveCount();

            $('.markertoggles').on('click', toggleMarker);
        };

        initMap = function() {
            let mapProperties = options.mapProperties,
                mapCenter     = mapProperties.center;

            mapProperties.mapTypeId = google.maps.MapTypeId[mapProperties.mapTypeId] || null;
            if (mapCenter.lat && mapCenter.lng) {
                mapProperties.center = new google.maps.LatLng(mapCenter.lat, mapCenter.lng);

            } else {
                mapProperties.center = null;
            }

            map = new google.maps.Map(canvas, mapProperties);
        };

        setMarkers = function() {
            $(options.markerData).each(function(index, data) {
                let $listDisplay = null,
                    marker       = $.extend(true, {}, markerBase, data);

                if ($.inArray(marker.id, mappedMarkers) === -1) {
                    let position = new google.maps.LatLng(marker.position.lat, marker.position.lng);

                    markers[marker.id] = new google.maps.Marker({
                        position: position,
                        icon    : marker.marker
                    });

                    let infoBoxData = $.extend({}, defaults.infoBox, {
                        content         : marker.infoBox.content,
                        infoBoxClearance: new google.maps.Size(20, 30),
                        pixelOffset     : new google.maps.Size(-160, -55),
                        position        : position
                    });

                    markerInfoBox[marker.id] = new InfoBox(infoBoxData);

                    google.maps.event.addListener(map, 'click', function(e) {
                        // wtf is this? And why doesn't it throw an error?
                        contextMenu:true
                    });

                    if (options.clusters.show) {
                        clusterMarkers.push(markers[marker.id]);

                    } else {
                        markers[marker.id].setMap(map);
                    }

                    google.maps.event.addListener(markers[marker.id], 'click', function() {
                        if (mapinfobox === markerInfoBox[marker.id] && mapinfobox.getVisible()) {
                            mapinfobox.close();

                        } else {
                            if (mapinfobox) {
                                mapinfobox.close()
                            }

                            mapinfobox = markerInfoBox[marker.id];
                            mapinfobox.open(map, markers[marker.id]);
                        }
                    });

                    if (options.list.showTab) {
                        $listDisplay = $('<div class="fp_listitem">' + marker.infoBox.content + '</div>');
                        $listDisplay.addClass('fp_list_marker' + marker.id)
                        $('#fp_locationlist .fp_ll_holder').append();
                        $listDisplay.status = 0;
                    }

                    markers[marker.id].status = 0;
                }

                markers[marker.id].status += 1;

                if ($listDisplay) {
                    $listDisplay.status += 1;
                }

                if (typeof markerSets[marker.typeId] === 'undefined') {
                    markerSets[marker.typeId] = [];
                }

                mappedMarkers.push(marker.id);
                markerSets[marker.typeId].push(marker.id);
            });
        };

        updateActiveCount = function() {
            let locationTxt = '',
                status      = '',
                activeCount = 0;

            $.each(markers, function(index, marker) {
                if (typeof marker !== 'undefined') {
                    if (marker.status > 0) {
                        activeCount += 1;
                        status = status + ' ' + marker.status;
                    }
                }
            });

            if (searchTxt !== '') {
                locationTxt = ' (within ' + mapsearchrange + 'Km of ' + searchTxt + ')';
            }

            let locationPlural = 'locations';
            if (activeCount === 1) {
                locationPlural = 'location';
            }

            $('#activecount').html('Showing ' + activeCount + ' ' + locationPlural + locationTxt + '.');

            if (activeCount === 0) {
                if ($('.nolocations').length === 0) {
                    $('#fp_locationlist .fp_ll_holder').append('<div class="nolocations">No location types selected.</div>');
                }

            } else {
                $('.nolocations').remove();
            }
        };

        toggleMarker = function(evt) {
            evt.preventDefault();

            let $this  = $(this),
                typeId = $this.attr('data-marker-type');

            markers.forEach(function(marker, index) {
                markerInfoBox[index].close();
            });

            $(markerSets[typeId]).each(function(i, markerId) {
                let marker      = markers[markerId],
                    $listMarker = $('.fp_list_marker' + markerId);

                if ($this.hasClass('active')) {
                    marker.status -= 1;
                    if (marker.status === 0) {
                        if (!options.clusters.show) {
                            marker.setMap();
                        }

                        markerInfoBox[markerId].close();
                        $listMarker.fadeOut(100, function() {
                            $this
                                .addClass('fp_listitem_hidden')
                                .appendTo('#fp_locationlist .fp_ll_holder');
                        });
                    }

                } else {
                    marker.status += 1;

                    if (marker.status === 1) {
                        if (!options.clusters.show) {
                            marker.setMap(map);
                        }

                        $listMarker
                            .prependTo('#fp_locationlist .fp_ll_holder')
                            .fadeIn(100, function() {
                                $this.removeClass('fp_listitem_hidden');
                            });
                    }
                }
            });
            if ($this.hasClass('active')) {
                $this.removeClass('active');
            } else {
                $this.addClass('active');
            }

            if (options.fitBounds) {
                let bounds = null

                markers.forEach(function(marker) {
                    if (marker.status > 0) {
                        if (bounds === null) {
                            bounds = new google.maps.LatLngBounds();
                        }
                        bounds.extend(marker.getPosition());
                    }
                });

                if (bounds !== null) {
                    map.fitBounds(bounds);

                } else {
                    if (options.mapProperties.center) {
                        map.panTo(options.mapProperties.center);
                    }
                    map.setZoom(options.mapProperties.zoom);
                }
            }

            if (options.clusters.show) {
                clusterMarkers = [];
                markers.forEach(function(m, i) {
                    if (markers[i].status > 0) {
                        clusterMarkers.push(markers[i]);
                    }
                });

                markerCluster.clearMarkers();
                markerCluster = new MarkerClusterer(map, clusterMarkers, clusterOptions);
            }

            setTimeout(function() {
                let locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
                jQuery('#fp_locationlist').css('height', locationListHeight);
            }, 150);

            updateActiveCount(searchTxt);

            return false;
        };

        return {
            init: init
        }
    };
})(jQuery);
