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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

$showListTab   = $this->params->get('locationlist');
$showListFirst = $this->params->get('showlistfirst');
$customTabs    = $this->item->tabsdata->tabs;

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

$listTab = HTMLHelper::_(
    'link',
    null,
    Text::_('COM_FOCALPOINT_LIST'),
    ['id' => 'locationlisttab']
);
$mapTab  = HTMLHelper::_(
    'link',
    '#tabs1-map',
    Text::_('COM_FOCALPOINT_MAP'),
    ['data-toggle' => 'tab']
);

if ($customTabs || $showListTab) :
    // Begin tabs
    echo '<div id="xtab-container" class="xtab-container">';
    ?>
    <ul class='xnav xnav-tabs'>
        <?php if ($showListTab && $showListFirst) : ?>
            <li><?php echo $listTab; ?></li>
        <?php endif; ?>

        <li class='active'><?php echo $mapTab; ?></li>

        <?php if ($showListTab && !$showListFirst) : ?>
            <li><?php echo $listTab; ?></li>
        <?php endif;

        if ($customTabs) :
            foreach ($customTabs as $key => $tab) :
                $customTab = HTMLHelper::_('link', '#tabs1-' . $key, $tab->name, ['data-toggle' => 'tab']);
                ?>
                <li><?php echo $customTab; ?></li>
            <?php endforeach;
        endif;
        ?>
    </ul>
<?php endif; ?>
    <div class="tab-content">
        <?php
        if ($customTabs || $showListTab) :
            echo '<div id="tabs1-map" class="tab-pane active">';
        endif;
        ?>
        <div id="xfp_googleMapContainer"
             class="<?php echo $sidebarClass; ?>"
             style="<?php echo $containerStyle; ?>">
            <?php if (in_array($legendPosition, ['above', 'left'])) : ?>
                <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                    <?php echo $this->loadTemplate('legend_' . $legendPosition); ?>
                </div>
            <?php endif; ?>

            <div id="fp_googleMap" style="<?php echo $mapStyle; ?>"></div>

            <?php if ($showListTab) : ?>
                <div id="fp_locationlist_container">
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
        if ($customTabs || $showListTab) :
            echo '</div>';

            if ($customTabs) :
                foreach ($customTabs as $key => $tab) :
                    ?>
                    <div id="tabs1-<?php echo $key; ?>" class="fp-custom-tab tab-pane">
                        <?php echo $tab->content; ?>
                    </div>
                <?php endforeach;
            endif;
        endif;

        echo '</div>';
        ?>
    </div>
<?php
if ($showListTab) :
    $jScript = <<<JSCRIPT
jQuery(function($) {
    $('#locationlisttab').on ('click', function(evt) {
        evt.preventDefault();
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
}(;
JSCRIPT;

endif;

/*
    jQuery('ul.nav-tabs > li >a').click(function() {
        setTimeout(function() {
            google.maps.event.trigger(map, 'resize');
            map.panTo(new google.maps.LatLng({$this->item->latitude}, {$this->item->longitude}));
            map.setZoom({$zoom});
        },500);

 */
