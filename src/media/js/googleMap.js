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
    $.sloc = $.extend(true, {map: {}}, $.sloc);

    $.sloc.sprintf = function(string, replacements) {
        let result = string.toString();

        for (let i = 0; i < replacements.length; i++) {
            let ordered = '%' + (i + 1) + '$s';

            if (result.indexOf(ordered) === -1) {
                result = result.replace('%s', replacements[i]);
            } else {
                result = result.replace(ordered, replacements[i]);
            }
        }

        return result;
    };

    $.sloc.map.google = function() {
        let defaults       = {
                canvasId      : 'fp_googleMap',
                clusterOptions: null,
                fitBounds     : false,
                mapProperties : {
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
                markerData    : [],
                infoBox       : {
                    alignBottom   : true,
                    closeBoxMargin: '7px 5px 1px 1px',
                    closeBoxURL   : 'https://www.google.com/intl/en_us/mapfiles/close.gif',
                    content       : null,
                    maxWidth      : 320,
                    zIndex        : null
                },
                search        : {
                    assist: '',
                    radius: 15,
                    zoom  : 12
                },
                show          : {
                    clusters: false,
                    listTab : true,
                    markers : true,
                    search  : false,
                }
            },
            allowScrollTo  = true,
            canvas         = null,
            clusterMarkers = [],
            clusterManager = null,
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
            mapinfobox     = false,
            mappedMarkers  = [],
            markerInfoBox  = [],
            markers        = [],
            markerSets     = [],
            options        = {},
            search         = {
                text: ''
            };

        // Temporary hardocdes
        let
            listtabfirst = 0
        // End Temp hardocdes

        init = function(params) {
            options = $.extend(true, {}, defaults, params);
            canvas  = document.getElementById(options.canvasId);

            initMap();
            setMarkers();

            $('.markertoggles').on('click', toggleTypes);
            $('#fp_reset').on('click', resetMap);
            $('#fp_toggle').on('click', toggleMarkers).trigger('click');

            setSearch();
            updateActiveCount();
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

                    if (options.show.clusters) {
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

                    if (options.show.listTab) {
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

            if (options.show.clusters) {
                clusterManager = new MarkerClusterer(map, clusterMarkers, options.clusterOptions);
            }
        };

        updateActiveCount = function() {
            let displayText      = '',
                displayArguments = [],
                status           = '',
                activeCount      = 0,
                $noLocations     = $('.nolocations');

            markers.forEach(function(marker, id) {
                if (marker.status > 0) {
                    activeCount += 1;
                    status += marker.status;
                }
            });

            if (activeCount === 0) {
                if ($noLocations.length === 0) {
                    $('#fp_locationlist .fp_ll_holder')
                        .append('<div class="nolocations"/>')
                        .html(Joomla.Text._('COM_FOCALPOINT_NO_LOCATION_TYPES_SELECTED'));
                }

            } else {
                $noLocations.remove();
            }

            if (search.text !== '') {
                displayText      = 'COM_FOCALPOINT_SEARCH_WITHIN';
                displayArguments = [activeCount, options.search.radius, search.text];
            } else {
                displayText      = 'COM_FOCALPOINT_SEARCH_SHOWING';
                displayArguments = [activeCount]
            }

            if (activeCount === 1) {
                displayText += '_1';
            }

            $('#activecount').html($.sloc.sprintf(Joomla.Text._(displayText), displayArguments));
        };

        toggleTypes = function(evt) {
            evt.preventDefault();

            let $this  = $(this),
                typeId = $this.attr('data-marker-type');

            markers.forEach(function(marker, id) {
                markerInfoBox[id].close();
            });

            $(markerSets[typeId]).each(function(i, markerId) {
                let marker      = markers[markerId],
                    $listMarker = $('.fp_list_marker' + markerId);

                if ($this.hasClass('active')) {
                    marker.status -= 1;
                    if (marker.status === 0) {
                        if (!options.show.clusters) {
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
                        if (!options.show.clusters) {
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

            if (options.show.clusters) {
                clusterMarkers = [];
                markers.forEach(function(marker) {
                    if (marker.status > 0) {
                        clusterMarkers.push(marker);
                    }
                });

                clusterManager.clearMarkers();
                clusterManager = new MarkerClusterer(map, clusterMarkers, options.clusterOptions);
            }

            setTimeout(function() {
                let locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
                jQuery('#fp_locationlist').css('height', locationListHeight);
            }, 150);

            updateActiveCount();

            return false;
        };

        resetMap = function(evt) {
            allowScrollTo = false;

            search.text = '';
            $('#fp_searchAddress').val(search.text);

            markers.forEach(function(marker, id) {
                let $this = $(this);

                if (marker.status < -999) {
                    marker.status += 5000;
                    marker.setMap(map);
                    $('.fp_list_marker' + id).fadeIn(100, function() {
                        $this.removeClass('fp_listitem_hidden')
                            .prependTo('#fp_locationlist .fp_ll_holder');
                    });
                }
            });

            $('#fp_toggle').each(function() {
                if (options.show.markers) {
                    $(this).data('togglestate', 'off')
                        .html(Joomla.Text._('COM_FOCALPOINT_BUTTTON_HIDE_ALL'));

                    $('.markertoggles').each(function() {
                        let $this = $(this);

                        if ($this.hasClass('active')) {
                            $this.trigger('click');
                        }
                        $this.trigger('click');
                    });

                } else {
                    $(this).data('togglestate', 'on')
                        .html(Joomla.Text._('COM_FOCALPOINT_BUTTTON_SHOW_ALL'));

                    $('.markertoggles').each(function() {
                        let $this = $(this);
                        if ($this.hasClass('active')) {
                            $this.trigger('click');
                        }
                    });
                }
            });

            allowScrollTo = true;
            map.panTo(options.mapProperties.center);
            map.setZoom(options.mapProperties.zoom);
        };

        toggleMarkers = function(evt) {
            allowScrollTo = false;
            let $this     = $(this),
                $toggles  = $('.markertoggles');

            if ($this.data('togglestate') === 'on') {
                $this.data('togglestate', 'off')
                    .html(Joomla.Text._('COM_FOCALPOINT_BUTTTON_HIDE_ALL'));

                $toggles.each(function() {
                    if (!$(this).hasClass('active')) {
                        $(this).trigger('click');
                    }
                });

            } else {
                $this.data('togglestate', 'on')
                    .html(Joomla.Text._('COM_FOCALPOINT_BUTTTON_SHOW_ALL'));

                $toggles.each(function() {
                    if ($(this).hasClass('active')) {
                        $(this).trigger('click');
                    }
                });
            }

            allowScrollTo = true;
        };

        setSearch = function() {
            if (options.show.search) {
                let $searchField  = $('#fp_searchAddress'),
                    $searchButton = $('#fp_searchAddressBtn');

                $searchField.on('keypress', function(evt) {
                    if (this.value && evt.which === 13) {
                        $searchButton.trigger('click');
                        return false;
                    }
                });

                $searchButton.on('click', function(evt) {
                    evt.preventDefault();

                    search.text = $searchField.val();
                    if (search.text) {
                        let geocoder = new google.maps.Geocoder(),
                            address  = search.text,
                            location = null;

                        if (options.search.assist) {
                            address += ', ' + options.search.assist;
                        }

                        geocoder.geocode(
                            {address: address},
                            function(results, status) {
                                if (status === google.maps.GeocoderStatus.OK) {
                                    location = results[0].geometry.location;

                                    allowScrollTo = false;
                                    markers.forEach(function(marker, id) {
                                        if (marker.status < -999) {
                                            marker.status += 5000;
                                            if (!options.show.clusters) {
                                                marker.setMap(map);
                                            }

                                            $('.fp_list_marker' + id).fadeIn(100, function() {
                                                $(this).removeClass('fp_listitem_hidden')
                                                    .prependTo('#fp_locationlist .fp_ll_holder');
                                            });
                                        }
                                    });

                                    $('#fp_toggle').each(function() {
                                        let $toggle = $(this);

                                        $toggle.data('togglestate', 'off')
                                            .html(Joomla.Text._('COM_FOCALPOINT_BUTTTON_HIDE_ALL'));

                                        $('.markertoggles').each(function() {
                                            let $typeToggle = $(this);

                                            if ($typeToggle.hasClass('active')) {
                                                $typeToggle.trigger('click');
                                            }

                                            $typeToggle.trigger('click');
                                        });
                                    });

                                    markers.forEach(function(marker, id) {
                                        let position = marker.getPosition(),
                                            deltaLat = location.lat() - position.lat(),
                                            deltaLng = location.lng() - position.lng(),
                                            distance = Math.sqrt(deltaLat * deltaLat + deltaLng * deltaLng) * 111.32;

                                        if (distance > options.search.radius) {
                                            marker.status -= 5000;
                                            if (marker.status < 1) {
                                                markerInfoBox[id].close();
                                                if (!options.show.clusters) {
                                                    marker.setMap();
                                                }

                                                $('.fp_list_marker' + id).fadeOut(100, function() {
                                                    $(this).addClass('fp_listitem_hidden')
                                                        .appendTo('#fp_locationlist .fp_ll_holder');
                                                });
                                            }
                                        }

                                        updateActiveCount();
                                        allowScrollTo = true;
                                    });

                                    if (options.show.clusters) {
                                        clusterMarkers = [];
                                        markers.forEach(function(marker) {
                                            if (marker.status > 0) {
                                                clusterMarkers.push(marker);
                                            }
                                        });

                                        clusterManager.clearMarkers();
                                        clusterManager = new MarkerClusterer(map, clusterMarkers, options.clusterOptions);
                                    }

                                    map.setCenter(location);
                                    map.setZoom(options.search.zoom);
                                    setTimeout(function() {
                                        let locationListHeight = $('#fp_locationlist .fp_ll_holder').outerHeight();
                                        $('#fp_locationlist').css('height', locationListHeight);
                                    }, 500);

                                } else {
                                    alert(Joomla.Text._('COM_FOCALPOINT_ERROR_GEOCODE').replace('%s', status));
                                }
                            }
                        );
                    } else {
                        alert(Joomla.Text._('COM_FOCALPOINT_SEARCH_ADDRESS_REQUIRED'));
                    }
                });
            }
        }

        return {
            init: init
        }
    };
})(jQuery);
