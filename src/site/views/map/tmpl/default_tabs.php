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

        $mapStyle[]     = "min-height: {$containerHeight};";
        $mapStyle[]     = "width: {$mapWidth}{$mapWidthType};";
        $sidebarClass[] = 'fp_side fp_' . $legendPosition;

    } else {
        $sidebarClass = ['fp_vert fp_' . $legendPosition];
    }
}

$containerStyle = [
    "width: {$containerWidth};",
    "min-height: {$containerHeight};"
];

$mapContent = sprintf(
    '<div id="fp_googleMap" style="%s"></div>',
    join(' ', $mapStyle)
);

$mapContent = sprintf(
    '<div id="fp_googleMapContainer" class="%s" style="%s">%s</div>',
    join(' ', $sidebarClass),
    join(' ', $containerStyle),
    $mapContent
);

// Build the tabs in specified order
$mapTabId = 'map';
$tabs     = [
    $mapTabId => (object)[
        'name'    => Text::_('COM_FOCALPOINT_MAP'),
        'content' => $mapContent
    ]
];

$listTabId = 'locationlisttab';
if ($this->params->get('locationlist')) {
    $listTab = [
        $listTabId => (object)[
            'name'    => Text::_('COM_FOCALPOINT_LIST'),
            'content' => sprintf(
                '<div id="fp_locationlist_container">'
                . '<div id="fp_locationlist" style="%s">'
                . '<div class="fp_ll_holder">'
                . '</div></div></div>',
                join(' ', $mapStyle)
            )
        ]
    ];

    if ($this->params->get('showlistfirst')) {
        $tabs = array_merge($listTab, $tabs);
    } else {
        $tabs = array_merge($tabs, $listTab);
    }
}
$tabs = array_merge($tabs, $this->item->tabsdata->tabs);

$legendId    = 'fp_googleMapSidebar';
$legend      = sprintf(
    '<div id="%s" style="%s">%s</div>',
    $legendId,
    join(' ', $sidebarStyle),
    $this->loadTemplate('legend_' . $legendPosition)
);
$legendFirst = in_array($legendPosition, ['above', 'left']);

?>
    <div class="tab-content">
        <div id="fp_main" class="clearfix">
            <?php
            if (count($tabs) > 1) :
                echo HTMLHelper::_('bootstrap.startTabSet', 'map', ['active' => $mapTabId]);

                if ($legendFirst) :
                    echo $legend;
                endif;

                foreach ($tabs as $id => $tab) {
                    echo HTMLHelper::_('bootstrap.addTab', 'map', $id, $tab->name);
                    echo $tab->content;
                    echo HTMLHelper::_('bootstrap.endTab');
                }

                if (!$legendFirst) :
                    echo $legend;
                endif;

                echo HTMLHelper::_('bootstrap.endTabSet');

                if ($this->app->input->getBool("debug")) :
                    echo sprintf(
                        '<textarea style="width:100%;height:500px;"><pre>%s</pre></textarea>',
                        print_r($this->item, 1)
                    );
                endif;

            elseif ($legendFirst) :
                echo $legend;
                echo $tabs[$mapTabId]->content;

            else :
                echo $tabs[$mapTabId]->content;
                echo $legend;

            endif;
            ?>
        </div>
    </div>
<?php

if (count($tabs) > 1) :
    // This must be the last thing in this template
    $js = <<<JSCRIPT
;jQuery(function($) {
    $('#mapTabs a').on('click', function(evt) {
        let href       =  $(this).attr('href').replace('#',''),
            hideLegend = ['{$mapTabId}','{$listTabId}'].indexOf(href) < 0;

        if (hideLegend) {
            $('#{$legendId}').hide();
        } else {
            $('#{$legendId}').show();
        }
    });
});
JSCRIPT;

    Factory::getDocument()->addScriptDeclaration($js);
endif;
