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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

// Load the Google API and initialise the map.
HTMLHelper::_('script', '//maps.googleapis.com/maps/api/js?key=' . $this->item->params->get('apikey'));
HTMLHelper::_('script', 'components/com_focalpoint/assets/js/infobox.js');
?>
<script>
    <!-- MAPJS START -->
    let map             = null,
        markerCluster   = null,
        clusterMarkers  = [],
        allowScrollTo   = false,
        searchTxt       = '',
        showlisttab     = 1,
        showmapsearch   = 1,
        mapsearchzoom   = 12,
        mapsearchrange  = 15,
        mapsearchprompt = 'Suburb or Postal code',
        searchassist    = ', ',
        fitbounds       = 0,
        markerclusters  = 0 && (typeof clusterOptions !== 'undefined'),
        listtabfirst    = 0,
        mapCenter       = new google.maps.LatLng(37.090240, -95.712891),
        marker          = [];

    function updateActiveCount(marker) {
        let locationTxt = '',
            status      = '',
            activeCount = 0;

        jQuery.each(marker, function(i, m) {
            if (typeof m !== 'undefined') {
                if (marker[i].status > 0) {
                    activeCount += 1;
                    status = status + ' ' + marker[i].status;
                }
            }
        });
        if (searchTxt !== '') {
            locationTxt = ' (within ' + mapsearchrange + 'Km of ' + searchTxt + ')';
        }
        let locationPlural = 'locations';
        if (activeCount == 1) {
            locationPlural = 'location';
        }

        jQuery('#activecount').html('Showing ' + activeCount + ' ' + locationPlural + locationTxt + '.');

        if (activeCount == 0) {
            if (jQuery('.nolocations').length == 0) {
                jQuery('#fp_locationlist .fp_ll_holder').append('<div class="nolocations">No location types selected.</div>');
            }

        } else {
            jQuery('.nolocations').remove();
        }
    }
    function initialize() {
        let mapProperties = {
            center                  : mapCenter,
            zoom                    : 4,
            maxZoom                 : null,
            mapTypeControl          : 1,
            fullscreenControl       : 0,
            fullscreenControlOptions: null,
            zoomControl             : 1,
            scrollwheel             : 0,
            streetViewControl       : 1,
            panControl              : 0,
            draggable               : 1,
            mapTypeId               : google.maps.MapTypeId.ROADMAP,
            styles                  : []
        };

        map = new google.maps.Map(document.getElementById('fp_googleMap'), mapProperties);

        let markerSets    = [],
            markerInfoBox = [],
            mappedMarkers = [],
            mapinfobox    = false;
        <!-- MARKER START -->
        if (jQuery.inArray(2 ,mappedMarkers) == -1) {
            let myCenter2 = new google.maps.LatLng(45.521642, -122.642595);
            marker[2] = new google.maps.Marker({
                position:myCenter2,
                icon: 'http://localhost:81/images/markers/pins/style4/red.png'
            });

            let boxText2 = '<h4>Betsy\'s House</h4><div class=\"infoboxcontent\"><p class=\"infoboxlink\"><a href=\"/index.php/locations/betsy-s-house\" title=\"Betsy\'s House\">Find out more...</a></p><div class=\"infopointer\"></div></div>';
            markerInfoBox[2] = new InfoBox({
                content         : boxText2,
                alignBottom     : true,
                position        : new google.maps.LatLng(45.521642, -122.642595),
                pixelOffset     : new google.maps.Size(-160, -55),
                maxWidth        : 320,
                zIndex          : null,
                closeBoxMargin  : '7px 5px 1px 1px',
                closeBoxURL     : 'https://www.google.com/intl/en_us/mapfiles/close.gif',
                infoBoxClearance: new google.maps.Size(20, 30)
            });

            google.maps.event.addListener(map, 'click', function(e) {
                contextMenu:true
            });

            if (markerclusters) {
                clusterMarkers.push(marker[2]);

            } else {
                marker[2].setMap(map);
            }

            google.maps.event.addListener(marker[2], 'click', function() {
                if (mapinfobox == markerInfoBox[2] && mapinfobox.getVisible()) {
                    mapinfobox.close();

                } else {
                    if (mapinfobox) {
                        mapinfobox.close()
                    }

                    mapinfobox = markerInfoBox[2];
                    mapinfobox.open(map,marker[2]);
                }
            });

            if (showlisttab) {
                jQuery('#fp_locationlist .fp_ll_holder').append('<div class="fp_list_marker2 fp_listitem">'
                    + boxText2 + '</div>');
            }

            marker[2].status = 0;
            marker[2].lat = 45.521642;
            marker[2].lng = -122.642595;
            jQuery('.fp_list_marker2').status = 0;
        }
        marker[2].status += 1;
        jQuery('.fp_list_marker2').status +=1;

        if(typeof markerSets[2] === 'undefined') {
            markerSets[2] = [];
        }

        mappedMarkers.push(2);
        markerSets[2].push(2);
        <!-- MARKER END -->

        <!-- MARKER START -->
        if (jQuery.inArray(1 ,mappedMarkers) == -1) {
            let myCenter1 = new google.maps.LatLng(45.468837, -122.590507);
            marker[1] = new google.maps.Marker({
                position:myCenter1,
                icon: 'http://localhost:81/images/markers/pins/style4/red.png'
            });

            let boxText1 = '<h4>My House</h4><div class=\"infoboxcontent\"><p>7035 SE Flavel St</p><p>111-111-1111</p><p><p>It\'s my house!</p></p><p class=\"infoboxlink\"><a href=\"/index.php/my-house\" title=\"My House\">Find out more...</a></p><div class=\"infopointer\"></div></div>';
            markerInfoBox[1] = new InfoBox({
                content         : boxText1,
                alignBottom     : true,
                position        : new google.maps.LatLng(45.468837, -122.590507),
                pixelOffset     : new google.maps.Size(-160, -55),
                maxWidth        : 320,
                zIndex          : null,
                closeBoxMargin  : '7px 5px 1px 1px',
                closeBoxURL     : 'https://www.google.com/intl/en_us/mapfiles/close.gif',
                infoBoxClearance: new google.maps.Size(20, 30)
            });

            google.maps.event.addListener(map, 'click', function(e) {
                contextMenu:true
            });

            if (markerclusters) {
                clusterMarkers.push(marker[1]);

            } else {
                marker[1].setMap(map);
            }

            google.maps.event.addListener(marker[1], 'click', function() {
                if (mapinfobox == markerInfoBox[1] && mapinfobox.getVisible()) {
                    mapinfobox.close();

                } else {
                    if (mapinfobox) {
                        mapinfobox.close()
                    }

                    mapinfobox = markerInfoBox[1];
                    mapinfobox.open(map,marker[1]);
                }
            });

            if (showlisttab) {
                jQuery('#fp_locationlist .fp_ll_holder').append('<div class="fp_list_marker1 fp_listitem">'
                    + boxText1 + '</div>');
            }

            marker[1].status = 0;
            marker[1].lat = 45.468837;
            marker[1].lng = -122.590507;
            jQuery('.fp_list_marker1').status = 0;
        }
        marker[1].status += 1;
        jQuery('.fp_list_marker1').status +=1;

        if(typeof markerSets[1] === 'undefined') {
            markerSets[1] = [];
        }

        mappedMarkers.push(1);
        markerSets[1].push(1);
        <!-- MARKER END -->

        <!-- MARKER START -->
        if (jQuery.inArray(3 ,mappedMarkers) == -1) {
            let myCenter3 = new google.maps.LatLng(45.517847, -122.677485);
            marker[3] = new google.maps.Marker({
                position:myCenter3,
                icon: 'http://localhost:81/images/markers/pins/style4/red.png'
            });

            let boxText3 = '<h4>Portland Apple Store</h4><div class=\"infoboxcontent\"><p class=\"infoboxlink\"><a href=\"/index.php/locations/portland-apple-store\" title=\"Portland Apple Store\">Find out more...</a></p><div class=\"infopointer\"></div></div>';
            markerInfoBox[3] = new InfoBox({
                content         : boxText3,
                alignBottom     : true,
                position        : new google.maps.LatLng(45.517847, -122.677485),
                pixelOffset     : new google.maps.Size(-160, -55),
                maxWidth        : 320,
                zIndex          : null,
                closeBoxMargin  : '7px 5px 1px 1px',
                closeBoxURL     : 'https://www.google.com/intl/en_us/mapfiles/close.gif',
                infoBoxClearance: new google.maps.Size(20, 30)
            });

            google.maps.event.addListener(map, 'click', function(e) {
                contextMenu:true
            });

            if (markerclusters) {
                clusterMarkers.push(marker[3]);

            } else {
                marker[3].setMap(map);
            }

            google.maps.event.addListener(marker[3], 'click', function() {
                if (mapinfobox == markerInfoBox[3] && mapinfobox.getVisible()) {
                    mapinfobox.close();

                } else {
                    if (mapinfobox) {
                        mapinfobox.close()
                    }

                    mapinfobox = markerInfoBox[3];
                    mapinfobox.open(map,marker[3]);
                }
            });

            if (showlisttab) {
                jQuery('#fp_locationlist .fp_ll_holder').append('<div class="fp_list_marker3 fp_listitem">'
                    + boxText3 + '</div>');
            }

            marker[3].status = 0;
            marker[3].lat = 45.517847;
            marker[3].lng = -122.677485;
            jQuery('.fp_list_marker3').status = 0;
        }
        marker[3].status += 1;
        jQuery('.fp_list_marker3').status +=1;

        if(typeof markerSets[3] === 'undefined') {
            markerSets[3] = [];
        }

        mappedMarkers.push(3);
        markerSets[3].push(3);
        <!-- MARKER END -->

        if (showlisttab) {
            jQuery('#locationlisttab').on ('click', function(e) {
                e.preventDefault();
                jQuery('a[href="#tabs1-map"]').tab('show');
                jQuery('#fp_googleMap').css('display','none');
                jQuery('.fp-map-view .nav-tabs li.active').removeClass('active');
                jQuery('#fp_locationlist_container').css('display', 'block');
                jQuery('#locationlisttab').parent().addClass('active');
                let locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
                jQuery('#fp_locationlist').css('height', locationListHeight);
            });

            jQuery('a[href="#tabs1-map"]').on('click', function() {
                jQuery('#fp_googleMap').css('display', 'block');
                jQuery('.fp-map-view .nav-tabs li.active').addClass('active');
                jQuery('#fp_locationlist_container').css('display', 'none');
                jQuery('#locationlisttab').parent().removeClass('active');
            });
        }

        jQuery('.markertoggles').on('click', function() {
            marker.forEach(function(m,i) {
                markerInfoBox[i].close();
            });

            el = jQuery(this);
            mid = el.attr('data-marker-type');

            let arrlength = markerSets[mid].length;
            if (el.hasClass('active')) {
                for (let i = 0; i < arrlength; i++) {
                    marker[markerSets[mid][i]].status -= 1;
                    if ( marker[markerSets[mid][i]].status == 0) {
                        if (!markerclusters) {
                            marker[markerSets[mid][i]].setMap();
                        }

                        markerInfoBox[markerSets[mid][i]].close();
                        jQuery('.fp_list_marker' + markerSets[mid][i]).fadeOut(100,function() {
                            jQuery(this).addClass('fp_listitem_hidden');
                            jQuery(this).appendTo('#fp_locationlist .fp_ll_holder');
                        });
                    }
                }

                el.removeClass('active');

            } else {
                for (let i = 0; i < arrlength; i++) {
                    marker[markerSets[mid][i]].status += 1;

                    if ( marker[markerSets[mid][i]].status == 1) {
                        if (!markerclusters) {
                            marker[markerSets[mid][i]].setMap(map);
                        }

                        jQuery('.fp_list_marker' + markerSets[mid][i]).prependTo('#fp_locationlist .fp_ll_holder');
                        jQuery('.fp_list_marker' + markerSets[mid][i]).fadeIn(100,function() {
                            jQuery(this).removeClass('fp_listitem_hidden');
                        });
                    }
                }

                el.addClass('active');
            }

            if (fitbounds) {
                let bounds = new google.maps.LatLngBounds();
                let newbounds = false;
                marker.map(function(m) {
                    if (m.status > 0) {
                        newbounds = true;
                        let thisbounds = new google.maps.LatLng(m.lat,m.lng);
                        bounds.extend(thisbounds);
                    }
                });

                if (newbounds) {
                    map.fitBounds(bounds);

                } else {
                    map.panTo(new google.maps.LatLng(37.090240, -95.712891));
                    map.setZoom(4);
                }
            }

            if (markerclusters) {
                clusterMarkers = [];
                marker.forEach(function(m,i) {
                    if(marker[i].status > 0) {
                        clusterMarkers.push(marker[i]);
                    }
                });

                markerCluster.clearMarkers();
                markerCluster = new MarkerClusterer(map, clusterMarkers, clusterOptions);
            }

            setTimeout(function() {
                let locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
                jQuery('#fp_locationlist').css('height', locationListHeight);
            },150);

            updateActiveCount(marker, searchTxt);

            return false;
        });

        jQuery('ul.nav-tabs > li >a').click(function() {
            setTimeout(function() {
                google.maps.event.trigger(map, 'resize');
                map.panTo(new google.maps.LatLng(37.090240, -95.712891));
                map.setZoom(4);
            },500);
        });

        jQuery(window).resize(function() {
            map.panTo(new google.maps.LatLng(37.090240,-95.712891));
        });

        jQuery('#fp_reset').click(function() {
            allowScrollTo = false;
            jQuery('#fp_searchAddress').val(mapsearchprompt);
            jQuery('#fp_searchAddressBtn').attr('disabled', true);
            searchTxt = '';
            marker.forEach(function(m,i) {
                if (marker[i].status < -999 ) {
                    marker[i].status += 5000;
                    marker[i].setMap(map);
                    jQuery('.fp_list_marker' + i).fadeIn(100,function() {
                        jQuery(this).removeClass('fp_listitem_hidden');
                        jQuery(this).prependTo('#fp_locationlist .fp_ll_holder');
                    });
                }
            });

            jQuery('#fp_toggle').each(function() {
                if ('on' == 'on') {
                    jQuery(this).data('togglestate','off');
                    jQuery(this).html('Hide  all');
                    jQuery('.markertoggles').each(function(e) {
                        if (jQuery(this).hasClass('active')) {
                            jQuery(this).trigger('click');
                        }
                        jQuery(this).trigger('click');
                    });

                } else {
                    jQuery(this).data('togglestate', 'on');
                    jQuery(this).html('Show all');
                    jQuery('.markertoggles').each(function(e) {
                        if (jQuery(this).hasClass('active')) {
                            jQuery(this).trigger('click');
                        }
                    });
                }
            });

            allowScrollTo = true;
            map.panTo(new google.maps.LatLng(37.090240, -95.712891));
            map.setZoom(4);
        });

        jQuery('#fp_toggle').click(function() {
            allowScrollTo = false;
            if (jQuery(this).data('togglestate') == 'on') {
                jQuery(this).data('togglestate', 'off');
                jQuery(this).html('Hide  all');
                jQuery('.markertoggles').each(function(e) {
                    if (!jQuery(this).hasClass('active')) {
                        jQuery(this).trigger('click');
                    }
                });

            } else {
                jQuery(this).data('togglestate', 'on');
                jQuery(this).html('Show all');
                jQuery('.markertoggles').each(function(e) {
                    if (jQuery(this).hasClass('active')) {
                        jQuery(this).trigger('click');
                    }
                });
            }

            allowScrollTo = true;
        });

        if (showmapsearch) {
            let geocoder,
                resultLat,
                resultLng;

            jQuery('#fp_searchAddress').keypress(function(e) {
                if (e.which == 13) {
                    return false;
                }
            });

            jQuery('#fp_searchAddressBtn').on('click', function() {
                geocoder = new google.maps.Geocoder();
                searchTxt = document.getElementById('fp_searchAddress').value;
                if (searchTxt == '') {
                    return false;
                }

                geocoder.geocode({ address: searchTxt+searchassist}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        resultLat = results[0].geometry.location.lat();
                        resultLng = results[0].geometry.location.lng();
                        allowScrollTo = false;
                        marker.forEach(function(m, i) {
                            if (marker[i].status < -999 ) {
                                marker[i].status += 5000;
                                if (!markerclusters) {
                                    marker[i].setMap(map);
                                }

                                jQuery('.fp_list_marker' + i).fadeIn(100, function() {
                                    jQuery(this).removeClass('fp_listitem_hidden');
                                    jQuery(this).prependTo('#fp_locationlist .fp_ll_holder');
                                });
                            }
                        });

                        jQuery('#fp_toggle').each(function() {
                            jQuery(this).data('togglestate','off');
                            jQuery(this).html('Hide  all');
                            jQuery('.markertoggles').each(function(e) {
                                if (jQuery(this).hasClass('active')) {
                                    jQuery(this).trigger('click');
                                }

                                jQuery(this).trigger('click');
                            });
                        });

                        marker.forEach(function(m, i) {
                            let dLat = resultLat-m.lat;
                            let dLong = resultLng-m.lng;
                            let distance = Math.sqrt(dLat*dLat + dLong*dLong) * 111.32;
                            if (distance > mapsearchrange) {
                                marker[i].status -= 5000;
                                if ( marker[i].status < 1) {
                                    markerInfoBox[i].close();
                                    if (!markerclusters) {
                                        marker[i].setMap();
                                    }

                                    jQuery('.fp_list_marker'+i).fadeOut(100,function() {
                                        jQuery(this).addClass('fp_listitem_hidden');
                                        jQuery(this).appendTo('#fp_locationlist .fp_ll_holder');
                                    });
                                }
                            }

                            updateActiveCount(marker,searchTxt);
                            allowScrollTo = true;
                        });

                        if (markerclusters) {
                            clusterMarkers = [];
                            marker.forEach(function(m, i) {
                                if (marker[i].status > 0) {
                                    clusterMarkers.push(marker[i]);
                                }
                            });

                            markerCluster.clearMarkers();
                            markerCluster = new MarkerClusterer(map, clusterMarkers, clusterOptions);
                        }

                        map.setCenter(results[0].geometry.location);
                        map.setZoom(mapsearchzoom);
                        setTimeout(function() {
                            let locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
                            jQuery('#fp_locationlist').css('height', locationListHeight);
                        },500);

                    } else {
                        alert(Joomla.Text._('COM_FOCALPOINT_ERROR_GEOCODE').replace('%s', status));
                    }
                });
            });
        }

        jQuery('#fp_toggle').trigger('click');
        allowScrollTo = true;
        updateActiveCount(marker);

        if (markerclusters) {
            markerCluster = new MarkerClusterer(map, clusterMarkers, clusterOptions);
        }

        if (showlisttab && (listtabfirst == 1)) {
            setTimeout(function() {
                jQuery('#locationlisttab').trigger('click');
            },100);
        }

        if ('off' == 'on') {
            setTimeout(function(){
                jQuery('#fp_toggle').trigger('click');
            },100);
        }
    }

    google.maps.event.addDomListener(window, 'load', initialize);
    <!-- MAPJS END -->
</script>


