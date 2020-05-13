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

/*
 * This file generates all the javascript required to show the map, markers and infoboxes.
 * In most custom templates this file should not require any changes and can be left as is.
 *
 * Backup this file before making any alterations.
 *
 * If you need to customise this file, create an override in your template and edit that.
 * Copy this file to templates/your+template/html/com_focalpoint/location/default_mapsjs.php
 *
 *
 *
 * To output a customfield use
 * 		$this->renderField($marker->customfields->yourcustomfield, $hidelabel, $buffer)
 *  	$hidelabel is TRUE or FALSE
 *      $buffer is TRUE or FALSE. If TRUE the output is buffered and returned. If FALSE it is output directly.
 *
 * To avoid notices first check that the field exists;
 *      if (!empty($marker->customfields->yourcustomfield)) { //Do something }
 *
 *
 * Alternatively iterate through the object $marker->customfields AS $field and call
 *  	$this->renderField($field,$hidelabel, $buffer);
 */

defined('_JEXEC') or die();

// Load the Google API and initialise the map.
JHtml::_('script', '//maps.googleapis.com/maps/api/js?key=' . $this->item->params->get('apikey'));
JHtml::_('script', 'components/com_focalpoint/assets/js/infobox.js');

$params            = JComponentHelper::getParams('com_focalpoint');
$showlisttab       = $this->item->params->get('locationlist');
$showmapsearch     = $this->item->params->get('mapsearchenabled');
$mapsearchzoom     = $this->item->params->get('mapsearchzoom');
$mapsearchrange    = $this->item->params->get('resultradius');
$mapsearchprompt   = $this->item->params->get('mapsearchprompt');
$searchassist      = ', ' . $this->item->params->get('searchassist');
$zoom              = $this->item->params->get('zoom');
$maxZoom           = $this->item->params->get('maxzoom', 'null');
$zoomControl       = $this->item->params->get('zoomControl');
$mapTypeControl    = $this->item->params->get('mapTypeControl');
$scrollWheel       = $this->item->params->get('scrollwheel');
$streetViewControl = $this->item->params->get('streetViewControl');
$panControl        = $this->item->params->get('panControl');
$draggable         = $this->item->params->get('draggable');
$mapTypeId         = 'google.maps.MapTypeId.' . $this->item->params->get('mapTypeId');
$mapStyle          = $this->item->params->get('mapstyle', '[]');
$fitbounds         = (int)(bool)$this->item->params->get('fitbounds');
$markerclusters    = (int)(bool)$this->item->params->get('markerclusters');
$listtabfirst      = (int)(bool)$this->item->params->get('showlistfirst');
$showMarkers       = $this->item->params->get('showmarkers');
$text              = (object)array(
    'within'     => JText::_('COM_FOCALPOINT_WITHIN', true),
    'distance'   => JText::_('COM_FOCALPOINT_DISTANCE', true),
    'locations'  => JText::_('COM_FOCALPOINT_LOCATIONS', true),
    'location'   => JText::_('COM_FOCALPOINT_LOCATION', true),
    'showing'    => JText::_('COM_FOCALPOINT_SHOWING', true),
    'notypes'    => JText::_('COM_FOCALPOINT_NO_LOCATION_TYPES_SELECTED', true),
    'hideButton' => JText::_('COM_FOCALPOINT_BUTTTON_HIDE_ALL', true),
    'showButton' => JText::_('COM_FOCALPOINT_BUTTTON_SHOW_ALL', true)
);

$script = <<<JSCRIPT
var map             = null,
    markerCluster   = null,
    clusterMarkers  = [],
    allowScrollTo   = false,
    searchTxt       = '',
    showlisttab     = {$showlisttab},
    showmapsearch   = {$showmapsearch},
    mapsearchzoom   = {$mapsearchzoom},
    mapsearchrange  = {$mapsearchrange},
    mapsearchprompt = '{$mapsearchprompt}',
    searchassist    = '{$searchassist}',
    fitbounds       = {$fitbounds},
    markerclusters  = {$markerclusters} && (typeof clusterOptions !== 'undefined'),
    listtabfirst    = {$listtabfirst},
    mapCenter       = new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}),
    marker          = [];

function updateActiveCount(marker) {
    var locationTxt = '',
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
        locationTxt = ' ({$text->within} ' + mapsearchrange + '{$text->distance} ' + searchTxt + ')';
    }
    var locationPlural = '{$text->locations}';
    if (activeCount == 1) {
        locationPlural = '{$text->location}';
    }

    jQuery('#activecount').html('{$text->showing} ' + activeCount + ' ' + locationPlural + locationTxt + '.');

    if (activeCount == 0) {
        if (jQuery('.nolocations').length == 0) {
            jQuery('#fp_locationlist .fp_ll_holder').append('<div class="nolocations">{$text->notypes}</div>');
        }

    } else {
        jQuery('.nolocations').remove();
    }
}

function initialize() {
    var mapProperties = {
        center           : mapCenter,
        zoom             : {$zoom},
        maxZoom          : {$maxZoom},
        mapTypeControl   : {$mapTypeControl},
        zoomControl      : {$zoomControl},
        scrollwheel      : {$scrollWheel},
        streetViewControl: {$streetViewControl},
        panControl       : {$panControl},
        draggable        : {$draggable},
        mapTypeId        : {$mapTypeId},
        styles           : {$mapStyle}
    };
    
    map = new google.maps.Map(document.getElementById('fp_googleMap'), mapProperties);
    var markerSets    = [],
        markerInfoBox = [],
        mappedMarkers = [],
        mapinfobox    = false;
JSCRIPT;

// Cycle through each location creating a marker and infobox.
foreach ($this->item->markerdata as $marker) {
    //Assemble the infobox.
    $infoDescription = '';
    if ($marker->params->get('infoshowaddress') && $marker->address != '') {
        $infoDescription .= '<p>' . JText::_($marker->address) . '</p>';
    }
    if ($marker->params->get('infoshowphone') && $marker->phone != '') {
        $infoDescription .= '<p>' . JText::_($marker->phone) . '</p>';
    }
    if ($marker->params->get('infoshowintro') && $marker->description != '') {
        $infoDescription .= '<p>' . JText::_($marker->description) . '</p>';
    }

    // Example. If a custom fields was defined called 'yourcustomfield' the following line would render
    // that field in the infobox and location list
    if (!empty($marker->customfields->yourcustomfield->data)) {
        $infoDescription .= $this->renderField($marker->customfields->yourcustomfield, true, true);
    }

    $boxText = sprintf(
        '<h4>%s</h4><div class="infoboxcontent">%s',
        $marker->title,
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

    if (isset($marker->link)) {
        $boxText .= sprintf(
            '<p class="infoboxlink">%s</p>',
            JHtml::_(
                'link',
                $marker->link,
                JText::_('COM_FOCALPOINT_FIND_OUT_MORE'),
                array('title' => $marker->title)
            )
        );
    }
    $boxText .= '<div class="infopointer"></div></div>';

    $boxText = addslashes(str_replace(array("\n", "\t", "\r"), '', $boxText));

    $script .= <<<JSCRIPT
    if (jQuery.inArray({$marker->id} ,mappedMarkers) == -1) {
        var myCenter{$marker->id} = new google.maps.LatLng({$marker->latitude}, {$marker->longitude});
        marker[{$marker->id}] = new google.maps.Marker({
            position:myCenter{$marker->id},
            icon: '{$marker->marker}'
        });
    
        var boxText{$marker->id} = '{$boxText}';
        markerInfoBox[{$marker->id}] = new InfoBox({
            content         : boxText{$marker->id},
            alignBottom     : true,
            position        : new google.maps.LatLng({$marker->latitude}, {$marker->longitude}),
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
            clusterMarkers.push(marker[{$marker->id}]);
    
        } else {
            marker[{$marker->id}].setMap(map);
        }
    
        google.maps.event.addListener(marker[{$marker->id}], 'click', function() {
            if (mapinfobox == markerInfoBox[{$marker->id}] && mapinfobox.getVisible()) {
                mapinfobox.close();
        
            } else {
                if (mapinfobox) {
                    mapinfobox.close()
                }
        
                mapinfobox = markerInfoBox[{$marker->id}];
                mapinfobox.open(map,marker[{$marker->id}]);
            }
        });
    
        if (showlisttab) {
            jQuery('#fp_locationlist .fp_ll_holder').append('<div class="fp_list_marker{$marker->id} fp_listitem">'
                + boxText{$marker->id} + '</div>');
        }
    
        marker[{$marker->id}].status = 0;
        marker[{$marker->id}].lat = {$marker->latitude};
        marker[{$marker->id}].lng = {$marker->longitude};
        jQuery('.fp_list_marker{$marker->id}').status = 0;
    }
    marker[{$marker->id}].status += 1;
    jQuery('.fp_list_marker{$marker->id}').status +=1;
    
    if(typeof markerSets[{$marker->locationtype_id}] === 'undefined') {
        markerSets[{$marker->locationtype_id}] = [];
    }
    
    mappedMarkers.push({$marker->id});
    markerSets[{$marker->locationtype_id}].push({$marker->id});
JSCRIPT;
}

// Close the initialize() function. Use JQuery for the click events on the sidebar links (a.markertoggles)
// and setup the load event.
$script .= <<<JSCRIPT
    if (showlisttab) {
        jQuery('#locationlisttab').on ('click', function(e) {
            e.preventDefault();
            jQuery('a[href="#tabs1-map"]').tab('show');
            jQuery('#fp_googleMap').css('display','none');
            jQuery('.fp-map-view .nav-tabs li.active').removeClass('active');
            jQuery('#fp_locationlist_container').css('display', 'block');
            jQuery('#locationlisttab').parent().addClass('active');
            var locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
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

        var arrlength = markerSets[mid].length;
        if (el.hasClass('active')) {
            for (var i = 0; i < arrlength; i++) {
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
            for (var i = 0; i < arrlength; i++) {
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
            var bounds = new google.maps.LatLngBounds();
            var newbounds = false;
            marker.map(function(m) {
                if (m.status > 0) {
                    newbounds = true;
                    var thisbounds = new google.maps.LatLng(m.lat,m.lng);
                    bounds.extend(thisbounds);
                }
            });
            
            if (newbounds) {
                map.fitBounds(bounds);
                
            } else {
                map.panTo(new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}));
                map.setZoom({$zoom});
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
            var locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
            jQuery('#fp_locationlist').css('height', locationListHeight);
        },150);
        
        updateActiveCount(marker, searchTxt);

        return false;
    });
    
    jQuery('ul.nav-tabs > li >a').click(function() {
        setTimeout(function() {
            google.maps.event.trigger(map, 'resize');
            map.panTo(new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}));
            map.setZoom({$zoom});
        },500); 
    });

    jQuery(window).resize(function() {
        map.panTo(new google.maps.LatLng({$this->item->latitude},{$this->item->longitude})); 
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
            if ('on' == '{$showMarkers}') {
                jQuery(this).data('togglestate','off');
                jQuery(this).html('{$text->hideButton}');
                jQuery('.markertoggles').each(function(e) {
                    if (jQuery(this).hasClass('active')) {
                        jQuery(this).trigger('click');
                    }
                    jQuery(this).trigger('click');
                });
                
            } else {
                jQuery(this).data('togglestate', 'on');
                jQuery(this).html('{$text->showButton}');
                jQuery('.markertoggles').each(function(e) {
                    if (jQuery(this).hasClass('active')) {
                        jQuery(this).trigger('click');
                    }
                });
            }
        });
        
        allowScrollTo = true;
        map.panTo(new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}));
        map.setZoom({$zoom});
    });
    
    jQuery('#fp_toggle').click(function() {
        allowScrollTo = false;
        if (jQuery(this).data('togglestate') == 'on') {
            jQuery(this).data('togglestate', 'off');
            jQuery(this).html('{$text->hideButton}');
            jQuery('.markertoggles').each(function(e) {
                if (!jQuery(this).hasClass('active')) {
                    jQuery(this).trigger('click');
                }
            });
            
        } else {
            jQuery(this).data('togglestate', 'on');
            jQuery(this).html('{$text->showButton}');
            jQuery('.markertoggles').each(function(e) {
                if (jQuery(this).hasClass('active')) {
                    jQuery(this).trigger('click');
                }
            });
        }
        
        allowScrollTo = true;
    });

    if (showmapsearch) {
        var geocoder,
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
                        jQuery(this).html('{$text->hideButton}');
                        jQuery('.markertoggles').each(function(e) {
                            if (jQuery(this).hasClass('active')) {
                                jQuery(this).trigger('click');
                            }
                            
                            jQuery(this).trigger('click');
                        });
                    });
                    
                    marker.forEach(function(m, i) {
                        var dLat = resultLat-m.lat;
                        var dLong = resultLng-m.lng;
                        var distance = Math.sqrt(dLat*dLat + dLong*dLong) * 111.32;
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
                        var locationListHeight = jQuery('#fp_locationlist .fp_ll_holder').outerHeight();
                        jQuery('#fp_locationlist').css('height', locationListHeight);
                    },500);

                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
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

    if ('off' == '{$showMarkers}') {
        setTimeout(function(){
            jQuery('#fp_toggle').trigger('click');
        },100);
    }
}

google.maps.event.addDomListener(window, 'load', initialize);
JSCRIPT;

JFactory::getDocument()->addScriptDeclaration($script);
