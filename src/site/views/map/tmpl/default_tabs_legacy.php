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

?>
<?php if ($customTabs || $showListTab) : ?>
<div id="tab-container" class="tab-container">
    <ul class='nav nav-tabs'>
        <?php if ($showListTab && $showListFirst) : ?>
            <li class=''>
                <a id="locationlisttab" href="#"><?php echo JText::_('COM_FOCALPOINT_LIST') ?></a>
            </li>
        <?php endif; ?>

        <li class='active'>
            <a href="#tabs1-map" data-toggle="tab"><?php echo JText::_('COM_FOCALPOINT_MAP') ?></a>
        </li>

        <?php if ($showListTab && !$showListFirst) : ?>
            <li>
                <a id="locationlisttab" href="#"><?php echo JText::_('COM_FOCALPOINT_LIST') ?></a>
            </li>
        <?php endif;

        if ($customTabs) :
            foreach ($customTabs as $key => $tab) :
                ?>
                <li><a href="#tabs1-<?php echo $key; ?>" data-toggle="tab"><?php echo $tab->name; ?></a>
                </li>
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
        <div id="fp_googleMapContainer" class="<?php echo $sidebarClass; ?>"
             style="<?php echo $containerStyle; ?>">
            <?php if ($legendPosition == "above" || $legendPosition == "left") : ?>
                <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                    <?php echo $this->loadTemplate('legend_' . $legendPosition); ?>
                </div>
            <?php endif; ?>

            <div id="fp_googleMap" style="<?php echo $mapStyle; ?>"></div>

            <?php if ($showListTab) : ?>
                <div id="fp_locationlist_container">
                    <div id="fp_locationlist" class="" style="<?php echo $mapStyle; ?>">
                        <div class="fp_ll_holder"></div>
                    </div>
                </div>
            <?php endif;

            if ($legendPosition == "below" || $legendPosition == "right") : ?>
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
        ?>
    </div>
</div>

