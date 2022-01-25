/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020-2021 Joomlashack.com. All rights reserved
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

;jQuery(function($) {
    $.sloc = $.extend(true, {map: {}}, $.sloc);

    $.sloc.map.google = function() {
        const FULLSCREEN = {
            position: {
                BOTTOM_CENTER: google.maps.ControlPosition.BOTTOM_CENTER,
                BOTTOM_LEFT  : google.maps.ControlPosition.BOTTOM_LEFT,
                BOTTOM_RIGHT : google.maps.ControlPosition.BOTTOM_RIGHT,
                LEFT_BOTTOM  : google.maps.ControlPosition.LEFT_BOTTOM,
                LEFT_CENTER  : google.maps.ControlPosition.LEFT_CENTER,
                LEFT_TOP     : google.maps.ControlPosition.LEFT_TOP,
                RIGHT_BOTTOM : google.maps.ControlPosition.RIGHT_BOTTOM,
                RIGHT_CENTER : google.maps.ControlPosition.RIGHT_CENTER,
                RIGHT_TOP    : google.maps.ControlPosition.RIGHT_TOP,
                TOP_CENTER   : google.maps.ControlPosition.TOP_CENTER,
                TOP_LEFT     : google.maps.ControlPosition.TOP_LEFT,
                TOP_RIGHT    : google.maps.ControlPosition.TOP_RIGHT
            }
        };

        let defaults       = {
                canvasId      : 'fp_googleMap',
                selector      : {
                    resetButton  : '#fp_reset',
                    listHolder   : '#fp_locationlist .fp_ll_holder',
                    legendToggles: '.markertoggles',
                    mainToggle   : '#fp_toggle',
                    searchAddress: '#fp_searchAddress',
                    searchbutton : '#fp_searchAddressBtn',
                    directions   : '#fp_googleMap_directions'
                },
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
                    fullscreenControlOptions: {},
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
                    legend  : false,
                    listTab : false,
                    markers : true,
                    search  : false,
                }
            },
            isDesktop      = true,
            allowScrollTo  = true,
            canvas         = null,
            destination    = $.sloc.map.destination,
            directions     = null,
            clusterMarkers = [],
            clusterManager = null,
            $listHolder    = null,
            map            = null,
            markerBase     = {
                id     : null,
                typeId : null,
                infoBox: {
                    event  : null,
                    content: null,
                    link   : null
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

        /**
         * @param {google.maps.map} map
         *
         * @return void
         */
        let addOverlay = function(map) {
            let kml = options.overlay || null;

            if (kml && kml.url) {
                kml.map      = map;
                let kmlLayer = new google.maps.KmlLayer(kml);

                kmlLayer.addListener('status_changed', function() {
                    if (kmlLayer.getStatus() !== 'OK') {
                        alert(Joomla.Text._('COM_FOCALPOINT_ERROR_OVERLAY').replace('%s', kmlLayer.getStatus()));
                    }
                });
            }
        };

        /**
         * @param {object} params
         *
         * @return void
         */
        let init = function(params) {
            options = $.extend(true, {}, defaults, params);

            canvas    = document.getElementById(options.canvasId);
            isDesktop = window.screen.availWidth > 979;

            if (options.show.listTab) {
                $listHolder = $(options.selector.listHolder);
            }

            initMap();
            setMarkers();
            initDirections();

            if (options.show.legend) {
                $(options.selector.resetButton).on('click', resetMap);
                $(options.selector.legendToggles).on('click', toggleTypes);
                $(options.selector.mainToggle).on('click', toggleMarkers).trigger('click');
            }

            setSearch();
            updateActiveCount();

            if (!options.show.markers) {
                setTimeout(function() {
                    $(options.selector.mainToggle).trigger('click');
                }, 100);
            }
        };

        /**
         * @param {int} delay
         *
         * @return void
         */
        let updateDisplay = function(delay) {
            updateList(delay);
        };

        /**
         * @return void
         */
        let initMap = function() {
            let mapProperties = options.mapProperties,
                mapCenter     = mapProperties.center;

            mapProperties.mapTypeId = google.maps.MapTypeId[mapProperties.mapTypeId] || null;
            if (mapCenter.lat && mapCenter.lng) {
                mapProperties.center = new google.maps.LatLng(mapCenter.lat, mapCenter.lng);

            } else {
                mapProperties.center = null;
            }

            for (let fsOption in mapProperties.fullscreenControlOptions) {
                let tag      = mapProperties.fullscreenControlOptions[fsOption] || null,
                    fsValues = FULLSCREEN[fsOption] || null;

                if (fsValues) {
                    mapProperties.fullscreenControlOptions[fsOption] = fsValues[tag] || null;
                }
            }

            map = new google.maps.Map(canvas, mapProperties);

            addOverlay(map);
        };

        /**
         * @return void
         */
        let initDirections = function() {
            if (destination === null) {
                return;
            }

            directions = $(options.selector.directions).get(0) || false;
            if (directions) {
                let $address   = $(options.selector.searchAddress),
                    searchText = null;

                $(options.selector.searchbutton).on('click', function(evt) {
                    evt.preventDefault();

                    searchText = $address.val();
                    if (searchText) {
                        try {
                            getDirections(searchText, destination);

                        } catch (error) {
                            alert('Error: ' + error.message);
                        }

                    } else {
                        alert(Joomla.Text._('COM_FOCALPOINT_SEARCH_ADDRESS_REQUIRED'));
                    }
                });
            }
        };

        /**
         * @param {string} searchAddress
         * @param {object} destination
         *
         * @return void
         */
        let getDirections = function(searchAddress, destination) {
            geocoder = new google.maps.Geocoder();

            if (options.search.assist) {
                searchAddress += ', ' + options.search.assist;
            }

            geocoder.geocode(
                {address: searchAddress},
                function(results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        let startLocation     = results[0].geometry.location,
                            directionsService = new google.maps.DirectionsService(),
                            directionsDisplay = new google.maps.DirectionsRenderer();

                        $('#fp_googleMap_directions').html('');

                        directionsDisplay.setMap(map);
                        directionsDisplay.setPanel(document.getElementById('fp_googleMap_directions'));

                        let request = {
                            origin     : startLocation,
                            destination: new google.maps.LatLng(destination.lat, destination.lng),
                            travelMode : google.maps.DirectionsTravelMode.DRIVING
                        };

                        directionsService.route(request, function(response, status) {
                            if (status === google.maps.DirectionsStatus.OK) {
                                directionsDisplay.setDirections(response);

                            } else {
                                alert(
                                    Joomla.Text._(
                                        'COM_FOCALPOINT_ERROR_GEOCODE',
                                        '*ERROR: %s').replace('%s', status
                                    )
                                );
                            }
                        });

                    } else {
                        alert(
                            Joomla.Text._(
                                'COM_FOCALPOINT_ERROR_GEOCODE',
                                '*ERROR: %s').replace('%s', status
                            )
                        );
                    }
                });
        };

        /**
         * @return void
         */
        let setMarkers = function() {
            $(options.markerData).each(function() {
                let $listItem = null,
                    marker    = $.extend(true, {}, markerBase, this);

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
                        markers.forEach(function(marker, id) {
                            markerInfoBox[id].close();
                        });
                    });

                    if (options.show.clusters) {
                        clusterMarkers.push(markers[marker.id]);

                    } else {
                        markers[marker.id].setMap(map);
                    }

                    if (marker.infoBox.event) {
                        let infoOpen = function(evt) {
                            if (mapinfobox === markerInfoBox[marker.id] && mapinfobox.getVisible()) {
                                mapinfobox.close();

                            } else {
                                if (mapinfobox) {
                                    mapinfobox.close()
                                }

                                mapinfobox = markerInfoBox[marker.id];
                                mapinfobox.open(map, markers[marker.id]);
                            }
                        };

                        let infoClose = function() {
                            if (mapinfobox) {
                                mapinfobox.close();
                            }
                        };

                        switch (marker.infoBox.event) {
                            case 'always':
                                markerInfoBox[marker.id].open(map, markers[marker.id]);
                                google.maps.event.addListener(markers[marker.id], 'click', function() {
                                    let thisBox = markerInfoBox[marker.id];
                                    if (thisBox.getVisible()) {
                                        thisBox.close();

                                    } else {
                                        thisBox.open(map, thisBox);
                                    }
                                });
                                break;

                            case 'click':
                                google.maps.event.addListener(markers[marker.id], 'click', infoOpen);
                                break;

                            case'hover':
                                if (isDesktop) {
                                    google.maps.event.addListener(markers[marker.id], 'mouseover', infoOpen);
                                    google.maps.event.addListener(markers[marker.id], 'mouseout', infoClose);

                                } else {
                                    google.maps.event.addListener(markers[marker.id], 'click', infoOpen);
                                }

                                break;
                        }
                    }

                    if (options.show.listTab) {
                        $listItem = $('<div class="fp_listitem">'
                            + marker.infoBox.content + marker.infoBox.link
                            + '</div>'
                        );
                        $listItem.addClass('fp_list_marker' + marker.id)
                        $listHolder.append($listItem);
                        $listItem.status = 0;
                    }

                    markers[marker.id].status = 0;
                }

                markers[marker.id].status += 1;

                if ($listItem) {
                    $listItem.status += 1;
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

        /**
         * @return void
         */
        let updateActiveCount = function() {
            if (options.show.legend) {
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

                if (options.show.listTab) {
                    if (activeCount === 0) {
                        if ($noLocations.length === 0) {
                            setTimeout(function() {
                                $noLocations = $('<div class="nolocations"/>')
                                    .html(Joomla.Text._('COM_FOCALPOINT_NO_LOCATION_TYPES_SELECTED'))
                                    .appendTo($listHolder);
                            }, 100);
                        }

                    } else {
                        $noLocations.remove();
                    }
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
            }
        };

        /**
         * @param {jQuery.Event} evt
         *
         * @return void
         */
        let toggleTypes = function(evt) {
            evt.preventDefault();

            let $this  = $(this),
                typeId = $this.attr('data-marker-type');

            markers.forEach(function(marker, id) {
                markerInfoBox[id].close();
            });

            $(markerSets[typeId]).each(function(i, markerId) {
                let marker    = markers[markerId],
                    $listItem = $('.fp_list_marker' + markerId);

                if ($this.hasClass('active')) {
                    marker.status -= 1;
                    if (marker.status === 0) {
                        if (!options.show.clusters) {
                            marker.setMap();
                        }

                        markerInfoBox[markerId].close();

                        $listItem.fadeOut(100);
                    }

                } else {
                    marker.status += 1;

                    if (marker.status === 1) {
                        if (!options.show.clusters) {
                            marker.setMap(map);
                        }

                        $listItem.fadeIn(100);
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

            updateActiveCount();
            updateList(150);
        };

        /**
         * @param {jQuery.Event} evt
         *
         * @return void
         */
        let resetMap = function(evt) {
            allowScrollTo = false;

            search.text = '';
            $(options.selector.searchAddress).val(search.text);

            markers.forEach(function(marker, id) {
                if (marker.status < -999) {
                    marker.status += 5000;
                    marker.setMap(map);
                    $('.fp_list_marker' + id).fadeIn(100);
                }
            });

            $(options.selector.mainToggle).each(function() {
                let $toggle        = $(this),
                    $markerToggles = $(options.selector.legendToggles);

                if (options.show.markers) {
                    $toggle.data('togglestate', 'off')
                        .html(Joomla.Text._('COM_FOCALPOINT_BUTTON_HIDE_ALL'));

                    $markerToggles.each(function() {
                        let $markerToggle = $(this);

                        if ($markerToggle.hasClass('active')) {
                            $markerToggle.trigger('click');
                        }
                        $markerToggle.trigger('click');
                    });

                } else {
                    $toggle.data('togglestate', 'on')
                        .html(Joomla.Text._('COM_FOCALPOINT_BUTTON_SHOW_ALL'));

                    $markerToggles.each(function() {
                        let $markerToggle = $(this);
                        if ($markerToggle.hasClass('active')) {
                            $markerToggle.trigger('click');
                        }
                    });
                }
            });

            allowScrollTo = true;
            map.panTo(options.mapProperties.center);
            map.setZoom(options.mapProperties.zoom);

            // Open all the infoboxes if requested
            options.markerData.forEach(function(marker, id) {
                if (marker.infoBox.event === 'always' && markers[marker.id].getVisible()) {
                    markerInfoBox[marker.id].open(map, markers[marker.id]);
                }
            });
        };

        /**
         * @param {jQuery.Event} evt
         *
         * @return
         */
        let toggleMarkers = function(evt) {
            allowScrollTo = false;
            let $this     = $(this),
                $toggles  = $(options.selector.legendToggles);

            if ($this.data('togglestate') === 'on') {
                $this.data('togglestate', 'off')
                    .html(Joomla.Text._('COM_FOCALPOINT_BUTTON_HIDE_ALL'));

                $toggles.each(function() {
                    if (!$(this).hasClass('active')) {
                        $(this).trigger('click');
                    }
                });

            } else {
                $this.data('togglestate', 'on')
                    .html(Joomla.Text._('COM_FOCALPOINT_BUTTON_SHOW_ALL'));

                $toggles.each(function() {
                    if ($(this).hasClass('active')) {
                        $(this).trigger('click');
                    }
                });
            }

            allowScrollTo = true;
        };

        /**
         * @return void
         */
        let setSearch = function() {
            if (options.show.search) {
                let $searchField  = $(options.selector.searchAddress),
                    $searchButton = $(options.selector.searchbutton);

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

                                            $('.fp_list_marker' + id).fadeIn(100);
                                        }
                                    });

                                    $(options.selector.mainToggle).each(function() {
                                        let $toggle = $(this);

                                        $toggle.data('togglestate', 'off')
                                            .html(Joomla.Text._('COM_FOCALPOINT_BUTTON_HIDE_ALL'));

                                        $(options.selector.legendToggles).each(function() {
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

                                                $('.fp_list_marker' + id).fadeOut(100);
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

                                    updateList(500);

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

        /**
         * @param {int}  delay
         *
         * @return void
         */
        let updateList = function(delay) {
            if (options.show.listTab) {
                let update = function() {
                    let locationListHeight = $listHolder.outerHeight();

                    if (locationListHeight > 0) {
                        $listHolder.parent().css('min-height', locationListHeight);
                    }
                };

                if (delay) {
                    setTimeout(function() {
                        update();
                    }, delay);

                } else {
                    update();
                }
            }
        };

        return {
            init      : init,
            update    : updateDisplay,
        };
    };
});
