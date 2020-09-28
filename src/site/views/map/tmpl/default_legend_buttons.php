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

defined('_JEXEC') or die('Restricted access');

$showMapSearch = $this->item->params->get('mapsearchenabled');
$searchPrompt  = $this->item->params->get('mapsearchprompt');
$sidebar       = $this->item->params->get('legendposition') == 'left'
    || $this->item->params->get('legendposition') == 'right';

$js = <<<JSCRIPT

JSCRIPT;

?>
<div class="row-fluid ">
    <p><small id="activecount"></small></p>
    <div id="fp_map_actions">
        <form onsubmit="return false;">
            <?php if ($showMapSearch) : ?>
                <div class="fp_mapsearch input-append">
                    <label for="fp_searchAddress"><?php echo $searchPrompt; ?></label>
                    <input class=""
                           id="fp_searchAddress"
                           type="text"
                           placeholder="<?php echo $searchPrompt; ?>">
                    <button class="btn " id="fp_searchAddressBtn" type="button">Go!</button>
                </div>
            <?php endif; ?>
            <div id="fp_map_buttons" class="input-append">
                <button class="btn btn-mini"
                        id="fp_reset"
                        onclick="return false;"><?php echo JText::_('COM_FOCALPOINT_BUTTON_RESET_MAP'); ?></button>
                <button class="btn btn-mini" id="fp_toggle" data-togglestate="on" onclick="return false;"></button>
            </div>
        </form>
    </div>
</div>
