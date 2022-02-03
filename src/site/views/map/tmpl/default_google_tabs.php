<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020-2022 Joomlashack.com. All rights reserved
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
$legendSidebar  = in_array($legendPosition, ['left', 'right']);
$legendClass    = sprintf(
    'fp_%s fp_%s',
    $legendSidebar ? 'side' : 'vert',
    $legendPosition
);

$containerStyle = [];
$mapStyle       = [];
$legendStyle    = [];

$mapWidthOuter = 'auto';
$mapHeight     = 0;
if ($this->params->get('mapsizecontrol')) {
    $mapWidth      = 'auto';
    $mapWidthOuter = $this->params->get('mapsizex');

    $mapHeight  = $this->params->get('mapsizey');
    $mapStyle[] = "min-height: {$mapHeight};";

    if ($legendSidebar) {
        $mapWidthOuterType = str_replace(str_split(' 0123456789'), '', $mapWidthOuter);
        $mapWidthOuter     = str_replace(str_split(' px%'), '', $mapWidthOuter);

        $legendWidth = str_replace(str_split(' px%'), '', $this->params->get('sidebarx'));
        $mapWidth    = $mapWidthOuter * (1 - ($legendWidth / 100)) . $mapWidthOuterType;
        $mapStyle[]  = "width: {$mapWidth};";
        $legendStyle = array_merge(
            $legendStyle,
            [
                "width: {$legendWidth}%;",
                "min-height: {$mapHeight};",
                "float: left;"
            ]
        );
    }
}

if (!empty($mapWidth)) {
    $containerStyle[] = "width: {$mapWidthOuter};";
}

if (!empty($mapHeight)) {
    $containerStyle[] = "min-height: {$mapHeight};";
}

$containerStyle = join(' ', $containerStyle);
$mapStyle       = join(' ', $mapStyle);
$legendStyle    = join(' ', $legendStyle);


$tabPrefix = 'maptab-';
$mapTabId  = $tabPrefix . 'map';
$mapTab    = HTMLHelper::_(
    'link',
    '#' . $mapTabId,
    Text::_('COM_FOCALPOINT_MAP'),
    [
        'data-show' => 'fp_googleMap'
    ]
);

$listTabId = $tabPrefix . 'list';
$listTab   = HTMLHelper::_(
    'link',
    '#' . $mapTabId,
    Text::_('COM_FOCALPOINT_LIST'),
    [
        'data-show' => 'fp_locationlist_container'
    ]
);

$showListTab   = $this->params->get('locationlist');
$showListFirst = $this->params->get('showlistfirst');
$customTabs    = $this->item->tabsdata->tabs;
$createTabs    = $showListTab || $customTabs;

if ($createTabs) :
    echo '<div id="tab-container" class="tab-container">';
    ?>
    <ul id="slocTabs" class='sloc-tabs'>
        <?php if ($showListTab && $showListFirst) : ?>
            <li><?php echo $listTab; ?></li>
        <?php endif; ?>

        <li class='active'><?php echo $mapTab; ?></li>

        <?php if ($showListTab && !$showListFirst) : ?>
            <li><?php echo $listTab; ?></li>
        <?php endif;

        foreach ($customTabs as $key => $tab) :
            $customId = $tabPrefix . 'custom' . $key;
            $customTab = HTMLHelper::_('link', '#' . $customId, $tab->name);
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
             class="<?php echo $legendClass; ?>"
             style="<?php echo $containerStyle; ?>">
            <?php if (in_array($legendPosition, ['above', 'left'])) : ?>
                <div id="fp_googleMapSidebar" style="<?php echo $legendStyle; ?>">
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
                <div id="fp_googleMapSidebar" style="<?php echo $legendStyle; ?>">
                    <?php echo $this->loadTemplate('legend_' . $legendPosition); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        if ($createTabs) :
            echo '</div>';

            foreach ($customTabs as $key => $tab) :
                ?>
                <div id="<?php echo $tabPrefix . 'custom' . $key; ?>" class="fp-custom-tab tab-pane">
                    <?php echo $tab->content; ?>
                </div>
            <?php endforeach;
        endif;

        echo '</div>';
        ?>
    </div>
<?php

if ($createTabs) :
    Factory::getDocument()->addScriptDeclaration(
        "jQuery(document).ready(function($) { new jQuery.sloc.tabs({$this->item->id}); });"
    );
endif;
