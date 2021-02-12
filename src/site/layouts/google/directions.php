<?php
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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var array      $displayData
 * @var string     $layoutOutput
 * @var string     $path
 */

extract($displayData);
/**
 * @var string   $mapId
 * @var Registry $params
 * @var array    $destination
 */
?>
    <div id="fp_googleMap_directions"></div>
    <div id="fp_map_actions" class="input-append">
        <form>
            <label for="fp_searchAddress">
                <?php echo Text::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>
            </label>
            <input id="fp_searchAddress"
                   type="text"
                   placeholder="<?php echo Text::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>"/>
            <button class="btn"
                    id="fp_searchAddressBtn"
                    type="button">
                <?php echo Text::_('COM_FOCALPOINT_GET_DIRECTIONS'); ?>
            </button>
        </form>
    </div>
<?php

$destination = json_encode($destination);

$jScript = <<<JSCRIPT
;jQuery(function($) {
    let mapId      = '{$mapId}',
        \$address   = $('#fp_searchAddress'),
        searchText = null;

    $('#fp_searchAddressBtn').on('click', function(evt) {
        evt.preventDefault();

        searchText = \$address.val();
        if (!searchText) {
            alert(Joomla.Text._('COM_FOCALPOINT_SEARCH_ADDRESS_REQUIRED'));
            return;
        }

        try {
            map = window.slocMap['{$mapId}'];

        } catch (error) {
            alert('Unable to find basemap');

            return;
        }

        map.getDirections(searchText, {$destination});
    });
});
JSCRIPT;

Factory::getDocument()->addScriptDeclaration($jScript);
