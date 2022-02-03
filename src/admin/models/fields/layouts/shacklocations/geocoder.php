<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Version;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var array      $displayData
 * @var string     $layoutOutput
 * @var string     $path
 */

/**
 * @var string                          $autocomplete
 * @var bool                            $autofocus
 * @var string                          $class
 * @var string                          $description
 * @var bool                            $disabled
 * @var ShacklocationsFormFieldGeocoder $field
 * @var string                          $group
 * @var bool                            $hidden
 * @var string                          $hint
 * @var string                          $id
 * @var string                          $label
 * @var string                          $labelclass
 * @var bool                            $multiple
 * @var string                          $name
 * @var string                          $onchange
 * @var string                          $onclick
 * @var string                          $pattern
 * @var string                          $validationtext
 * @var bool                            $readonly
 * @var bool                            $repeat
 * @var bool                            $required
 * @var int                             $size
 * @var bool                            $spellcheck
 * @var string                          $validate
 * @var string                          $value
 * @var JFormFieldText                  $latitude
 * @var JFormFieldText                  $longitude
 */
extract($displayData);

HTMLHelper::_('jquery.framework');

Text::script('COM_FOCALPOINT_ERROR_GEOCODE');

$noAPI = sprintf(
    '<span class="alert alert-error">%s</span>',
    Text::_('COM_FOCALPOINT_ERROR_MAPS_API_MISSING')
);

$defaultCenter = json_encode([
    'lat' => FocalpointHelper::HOME_LAT,
    'lng' => FocalpointHelper::HOME_LNG
]);

$modalId = $field->id . '_modal';
$saveId  = $field->id . '_save';
$linkId  = $field->id . '_link';

$selector = (object)[
    'link'      => '#' . $linkId,
    'modal'     => '#' . $modalId,
    'save'      => '#' . $saveId,
    'latitude'  => $latitude ? '#' . $latitude->id : null,
    'longitude' => $longitude ? '#' . $longitude->id : null,
];

echo HTMLHelper::_(
    'link',
    $selector->modal,
    '<span class="icon-out-2"></span> ' . Text::_('COM_FOCALPOINT_OPEN_GEOCODER'),
    [
        'id'             => $linkId,
        'role'           => 'button',
        'class'          => 'btn btn-primary uneditable-input',
        'data-toggle'    => 'modal',
        'data-bs-toggle' => 'modal',
        'data-bs-target' => $selector->modal,
    ]
);

ob_start()
?>
<div class="row-fluid">
    <div id="mapCanvas"></div>
</div>
<div class="<?php echo 'fp_controls fp_joomla' . Version::MAJOR_VERSION; ?>">
    <div class="control-group">
        <div class="controls">
            <span class="input-append input-group span12 w-100">
            <input class="form-control span6 w-50"
                   id="fp_address"
                   type="text"
                   placeholder="<?php echo Text::_('COM_FOCALPOINT_GEOCODER_ADDRESS_HINT'); ?>">
            <input type="button"
                   id="fp_search"
                   value="<?php echo Text::_('COM_FOCALPOINT_GEOCODER_SEARCH'); ?>"
                   disabled
                   class="btn btn-success">
            </span>
        </div>
    </div>
    <div class="row-fluid">
        <b><?php echo Text::_('COM_FOCALPOINT_GEOCODER_CURRENT'); ?></b>
        <div id="current"></div>
    </div>
</div>
<?php
$modalBody = ob_get_contents();
ob_end_clean();

echo HTMLHelper::_(
    'bootstrap.renderModal',
    $modalId,
    [
        'title'      => Text::_('COM_FOCALPOINT_GEOCODER_DRAG'),
        'height' => '400px',
        'modalWidth' => '80',
        'footer'     => HTMLHelper::_(
                'alledia.modal.footerSaveButton',
                ['id' => $saveId],
                Text::_('COM_FOCALPOINT_GEOCODER_SAVE')
            )
            . HTMLHelper::_('alledia.modal.footerCloseButton')
    ],
    $modalBody
);

?>
<script>
    ;jQuery(function($) {
        let defaultCenter = <?php echo $defaultCenter;  ?>,
            mapCanvas     = document.getElementById('mapCanvas'),
            selector      = <?php echo json_encode($selector); ?>

        if (google.maps) {
            google.maps.event.addDomListener(window, 'load', function() {
                let geocoder      = new google.maps.Geocoder(),
                    $latitude     = selector.latitude ? $(selector.latitude) : null,
                    $longitude    = selector.longitude ? $(selector.longitude) : null,
                    $address      = $('#fp_address'),
                    $searchButton = $('#fp_search'),
                    startLat      = $latitude ? $latitude.val() : null,
                    startLng      = $longitude ? $longitude.val() : null;

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
                    document.getElementById('current').innerHTML = [
                        latLng.lat(),
                        latLng.lng()
                    ].join(', ');
                }

                updateMarkerPosition(latLng);

                google.maps.event.addListener(marker, 'drag', function() {
                    updateMarkerPosition(marker.getPosition());
                });

                $address
                    .on('blur', function() {
                        if (this.value === '') {
                            $searchButton.attr('disabled', true);
                        }
                    })
                    .on('focus', function() {
                        if (this.value === '') {
                            $searchButton.attr('disabled', true);

                        } else {
                            $searchButton.attr('disabled', false);
                        }
                    })
                    .on('keyup', function(evt) {
                        if (this.value === '') {
                            $searchButton.attr('disabled', true);

                        } else {
                            $searchButton.attr('disabled', false);

                            if (evt.which === 13) {
                                $searchButton.trigger('click');
                            }
                        }
                    });

                $searchButton
                    .on('click', function(evt) {
                        evt.preventDefault();

                        let address = $address.val();
                        geocoder.geocode({address: address}, function(results, status) {
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

                $(selector.link).on('click', function() {
                    setTimeout(function() {
                        google.maps.event.trigger(map, 'resize');
                        map.panTo(marker.getPosition());
                    }, 800);
                });

                $(selector.save).on('click', function(evt) {
                    if ($latitude) {
                        $latitude.val(marker.getPosition().lat());
                    }
                    if ($longitude) {
                        $longitude.val(marker.getPosition().lng());
                    }
                });
            });

        } else {
            $(mapCanvas).html('<?php echo $noAPI; ?>');
        }
    });
</script>
