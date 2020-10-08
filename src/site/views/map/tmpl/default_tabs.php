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

$legendPosition = $this->params->get('legendposition');

// Calculate attributes
$mapStyle     = [];
$sidebarStyle = [];
$sidebarClass = [];

$containerWidth  = '100%';
$containerHeight = 'auto';
if ($this->params->get('mapsizecontrol') == 1) {
    $containerWidth  = $this->params->get('mapsizex');
    $containerHeight = $this->params->get('mapsizey');

    if (in_array($legendPosition, ['left', 'right'])) {
        $mapWidthType = str_replace(str_split(' 0123456789.'), '', $containerWidth);
        $mapWidthAmt  = str_replace(str_split(' px%'), '', $containerWidth);

        $sidebarPercent = str_replace(str_split(' px%'), '', $this->params->get('sidebarx'));
        $mapWidth       = $mapWidthAmt * (1 - ($sidebarPercent / 100));

        $sidebarStyle = array_merge(
            $sidebarStyle,
            [
                "width: {$sidebarPercent}%;",
                "height: {$containerHeight};",
                'float: left;'
            ]
        );

        $mapStyle[]     = "height: {$containerHeight};";
        $mapStyle[]     = "width: {$mapWidth}{$mapWidthType};";
        $sidebarClass[] = 'fp_side fp_' . $legendPosition;

    } else {
        $sidebarClass = ['fp_vert fp_' . $legendPosition];
    }
}

$containerStyle = [
    "width: {$containerWidth};",
    "height: {$containerHeight};"
];

// Build the map content
$mapContent = sprintf(
    '<div id="fp_googleMap" style="%s"></div>',
    join(' ', $mapStyle)
);

$legend = sprintf(
    '<div id="fp_googleMapSidebar" style="%s">%s</div>',
    join(' ', $sidebarStyle),
    $this->loadTemplate('legend_' . $legendPosition)
);

if (in_array($legendPosition, ['above', 'left'])) :
    $mapContent = $legend . $mapContent;
else :
    $mapContent .= $legend;
endif;
$mapContent = sprintf(
    '<div id="fp_googleMapContainer" class="%s" style="%s">%s</div>',
    join(' ', $sidebarClass),
    join(' ', $containerStyle),
    $mapContent
);

// Build the tabs in specified order
$tabs = [
    'tabs1-map' => (object)[
        'name'    => Text::_('COM_FOCALPOINT_MAP'),
        'content' => $mapContent
    ]
];

if ($this->params->get('locationlist')) {
    $listTab = [
        'locationlisttab' => (object)[
            'name'    => Text::_('COM_FOCALPOINT_LIST'),
            'content' => 'Here will be the list'
        ]
    ];
    if ($this->params->get('showlistfirst')) {
        $tabs = array_merge($listTab, $tabs);
    } else {
        $tabs = array_merge($tabs, $listTab);
    }
}
if ($customTabs = $this->item->tabsdata->tabs) {
    $tabs = array_merge($tabs, $customTabs);
}

?>
<div class="tab-content">
    <div id="fp_main" class="clearfix">
        <?php
        if (count($tabs) == 1) :
            echo $tabs['tabs1-map']->content;

        else :
            echo HTMLHelper::_('bootstrap.startTabSet', 'mapTab', ['active' => 'tabs1-map']);
            foreach ($tabs as $id => $tab) {
                echo HTMLHelper::_('bootstrap.addTab', 'mapTab', $id, $tab->name);
                echo $tab->content;
                echo HTMLHelper::_('bootstrap.endTab');
            }
            echo HTMLHelper::_('bootstrap.endTabSet');

            if ($this->app->input->getBool("debug")) :
                echo sprintf(
                    '<textarea style="width:100%;height:500px;"><pre>%s</pre></textarea>',
                    print_r($this->item, 1)
                );
            endif;
        endif;
        ?>
    </div>
</div>
