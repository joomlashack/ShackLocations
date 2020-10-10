<?php
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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

$legendPosition = $this->params->get('legendposition');
$sidebarClass   = "fp_vert fp_" . $legendPosition;

$containerStyle = "";
$mapStyle       = "";
$sidebarStyle   = "";
$mapsizexouter  = "auto";
$mapsizey       = "0";
if ($this->params->get('mapsizecontrol') == 1) {
    $mapsizexouter    = $this->params->get('mapsizex');
    $mapsizey         = $this->params->get('mapsizey');
    $mapsizexouterfmt = str_replace(str_split(' 0123456789'), '', $mapsizexouter);
    $mapsizexouteramt = str_replace(str_split(' px%'), '', $mapsizexouter);
    $mapsizex         = "auto";
    $mapStyle         .= "min-height: " . $mapsizey . "; ";

    if ($legendPosition == "left" || $legendPosition == "right") {
        $sidebarx     = str_replace(str_split(' px%'), '', $this->params->get('sidebarx'));
        $mapsizex     = $mapsizexouteramt * (1 - ($sidebarx / 100)) . $mapsizexouterfmt;
        $mapStyle     .= "width: " . $mapsizex . "; ";
        $sidebarStyle .= "width: " . $sidebarx . "%; ";
        $sidebarStyle .= "min-height: " . $mapsizey . "; ";
        $sidebarStyle .= "float: left; ";
        $sidebarClass = "fp_side fp_" . $legendPosition;
    }
}

if (!empty($mapsizex)) {
    $containerStyle .= "width:" . $mapsizexouter . "; ";
}

if (!empty($mapsizey)) {
    $containerStyle .= "min-height:" . $mapsizey . "; ";
}

$mapTabId = 'tabs1-map';
$mapTab   = HTMLHelper::_(
    'link',
    '#' . $mapTabId,
    Text::_('COM_FOCALPOINT_MAP'),
    [
        'data-toggle' => 'tab',
        'data-show'   => '#fp_googleMap'
    ]
);

$listTabId = 'tabs1-list';
$listTab   = HTMLHelper::_(
    'link',
    '#' . $mapTabId,
    Text::_('COM_FOCALPOINT_LIST'),
    [
        'data-toggle' => 'tab',
        'data-show'   => '#fp_locationlist_container'
    ]
);

$showListTab   = $this->params->get('locationlist');
$showListFirst = $this->params->get('showlistfirst');
$customTabs    = $this->item->tabsdata->tabs;
$createTabs    = $showListTab || $customTabs;

if ($createTabs) :
    // Begin tabs
    echo '<div id="tab-container" class="tab-container">';
    ?>
    <ul id="mapTabs" class='nav nav-tabs'>
        <?php if ($showListTab && $showListFirst) : ?>
            <li><?php echo $listTab; ?></li>
        <?php endif; ?>

        <li class='active'><?php echo $mapTab; ?></li>

        <?php if ($showListTab && !$showListFirst) : ?>
            <li><?php echo $listTab; ?></li>
        <?php endif;

        foreach ($customTabs as $key => $tab) :
            $customTab = HTMLHelper::_('link', '#tabs1-' . $key, $tab->name, ['data-toggle' => 'tab']);
            ?>
            <li><?php echo $customTab; ?></li>
        <?php endforeach;
        ?>
    </ul>
<?php endif; ?>
    <div class="tab-content">
        <?php
        if ($createTabs) :
            echo sprintf('<div id="%s" class="tab-pane active">', $mapTabId);
        endif;
        ?>
        <div id="fp_googleMapContainer"
             class="<?php echo $sidebarClass; ?>"
             style="<?php echo $containerStyle; ?>">
            <?php if (in_array($legendPosition, ['above', 'left'])) : ?>
                <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                    <?php echo $this->loadTemplate('legend_' . $legendPosition); ?>
                </div>
            <?php endif; ?>

            <div id="fp_googleMap" style="<?php echo $mapStyle; ?>"></div>

            <?php if ($showListTab) : ?>
                <div id="fp_locationlist_container" style="display: none;">
                    <div id="fp_locationlist" style="<?php echo $mapStyle; ?>">
                        <div class="fp_ll_holder"></div>
                    </div>
                </div>
            <?php endif;

            if (in_array($legendPosition, ['below', 'right'])) : ?>
                <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                    <?php echo $this->loadTemplate('legend_' . $legendPosition); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        if ($createTabs) :
            echo '</div>';

            foreach ($customTabs as $key => $tab) :
                ?>
                <div id="tabs1-<?php echo $key; ?>" class="fp-custom-tab tab-pane">
                    <?php echo $tab->content; ?>
                </div>
            <?php endforeach;
        endif;

        echo '</div>';
        ?>
    </div>
<?php

if ($createTabs) :
    $mapUpdate = sprintf('window.slocMap.map%s.update();', $this->item->id);

    $jScript = <<<JSCRIPT
;jQuery(function($) {
    let \$mapTabs = $('#mapTabs li').find('a'),
        showAreas = [];

    \$mapTabs.each(function () {
        let show = this.getAttribute('data-show');
        if (show) {
            showAreas.push($(this).data('show'));
        }
    });
    
    \$mapTabs.on('click', function(evt) {
        evt.preventDefault();
        
        let show = this.getAttribute('data-show');
        showAreas.forEach(function(area) {
            if (area === show) {
                $(area).show();
            } else {
                $(area).hide();
            }
        });
        
        {$mapUpdate}
    });
});
JSCRIPT;

    Factory::getDocument()->addScriptDeclaration($jScript);
endif;
