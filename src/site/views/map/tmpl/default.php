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

defined('_JEXEC') or die();

// Location List tab parameters
$showlisttab  = $this->item->params->get('locationlist');
$listtabfirst = $this->item->params->get('showlistfirst');

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_focalpoint', JPATH_ADMINISTRATOR . '/components/com_focalpoint');

if ($this->item->params->get('loadBootstrap')) {
    JHtml::_('stylesheet', 'components/com_focalpoint/assets/css/bootstrap.css');
    JHtml::_('bootstrap.framework');
}

// Set up width/height styles for the map and sidebar.
$containerStyle = "";
$mapStyle       = "";
$sidebarStyle   = "";
$mapsizexouter  = "auto";
$mapsizey       = "0";
$legendposition = $this->item->params->get('legendposition');
$sidebarClass   = "fp_vert fp_" . $legendposition;
if ($this->item->params->get('mapsizecontrol') == 1) {
    $mapsizexouter    = $this->item->params->get('mapsizex');
    $mapsizey         = $this->item->params->get('mapsizey');
    $mapsizexouterfmt = str_replace(str_split(' 0123456789'), '', $mapsizexouter);
    $mapsizexouteramt = str_replace(str_split(' px%'), '', $mapsizexouter);
    $mapsizex         = "auto";
    $mapStyle         .= "min-height: " . $mapsizey . "; ";

    if ($legendposition == "left" || $legendposition == "right") {
        $sidebarx     = str_replace(str_split(' px%'), '', $this->item->params->get('sidebarx'));
        $mapsizex     = $mapsizexouteramt * (1 - ($sidebarx / 100)) . $mapsizexouterfmt;
        $mapStyle     .= "width: " . $mapsizex . "; ";
        $sidebarStyle .= "width: " . $sidebarx . "%; ";
        $sidebarStyle .= "min-height: " . $mapsizey . "; ";
        $sidebarStyle .= "float: left; ";
        $sidebarClass = "fp_side fp_" . $legendposition;
    }
}

if (!empty($mapsizex)) {
    $containerStyle .= "width:" . $mapsizexouter . "; ";
}

if (!empty($mapsizey)) {
    $containerStyle .= "min-height:" . $mapsizey . "; ";
}

$pageclass_sfx = $this->item->params->get('pageclass_sfx');
$customTabs = $this->item->tabsdata->tabs;

?>
    <div id="focalpoint" class="fp-map-view <?php echo "legend_" . $legendposition . " " . $pageclass_sfx; ?>">
        <?php
        if (isset($this->item->page_title)) :
            ?>
            <h1><?php echo $this->item->page_title; ?></h1>
            <h2><?php echo $this->item->title; ?></h2>
            <?php
        else :
            ?>
            <h1><?php echo $this->item->title; ?></h1>
            <?php
        endif;

        if ($this->item->text) :
            ?>
            <div class="fp_mapintro clearfix">
                <?php echo $this->item->text; ?>
            </div>
            <?php
        endif;
        ?>
        <div id="fp_main" class="clearfix">
            <?php
            if ($customTabs || $showlisttab) :
                ?>
            <div id="tab-container" class="tab-container">
                <ul class='nav nav-tabs'>
                    <?php
                    if ($showlisttab && $listtabfirst) :
                        ?>
                        <li class=''><a id="locationlisttab" href="#"><?php echo JText::_('COM_FOCALPOINT_LIST') ?></a>
                        </li>
                        <?php
                    endif;
                    ?>
                    <li class='active'><a href="#tabs1-map"
                                          data-toggle="tab"><?php echo JText::_('COM_FOCALPOINT_MAP') ?></a></li>
                    <?php
                    if ($showlisttab && !$listtabfirst) :
                        ?>
                        <li class=''><a id="locationlisttab" href="#"><?php echo JText::_('COM_FOCALPOINT_LIST') ?></a>
                        </li>
                        <?php
                    endif;

                    if ($customTabs) :
                        foreach ($customTabs as $key => $tab) :
                            ?>
                            <li><a href="#tabs1-<?php echo $key; ?>" data-toggle="tab"><?php echo $tab->name; ?></a>
                            </li>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
                <?php
            endif;
            ?>
                <div class="tab-content">
                    <?php
                    if ($customTabs || $showlisttab) :
                        echo '<div id="tabs1-map" class="tab-pane active">';
                    endif;
                    ?>
                    <div id="fp_googleMapContainer" class="<?php echo $sidebarClass; ?>"
                         style="<?php echo $containerStyle; ?>">
                        <?php if ($legendposition == "above" || $legendposition == "left") { ?>
                            <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                                <?php echo $this->loadTemplate('legend_' . $legendposition); ?>
                            </div>
                        <?php } ?>
                        <div id="fp_googleMap" style="<?php echo $mapStyle; ?>"></div>
                        <?php if ($showlisttab) { ?>
                            <div id="fp_locationlist_container">
                                <div id="fp_locationlist" class="" style="<?php echo $mapStyle; ?>">
                                    <div class="fp_ll_holder"></div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($legendposition == "below" || $legendposition == "right") { ?>
                            <div id="fp_googleMapSidebar" style="<?php echo $sidebarStyle; ?>">
                                <?php echo $this->loadTemplate('legend_' . $legendposition); ?>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                    if ($customTabs || $showlisttab) :
                        echo '</div>';

                        if ($customTabs) :
                            foreach ($customTabs as $key => $tab) :
                                ?>
                                <div id="tabs1-<?php echo $key; ?>" class="fp-custom-tab tab-pane">
                                    <?php echo $tab->content; ?>
                                </div>
                                <?php
                            endforeach;
                        endif;
                    endif;
                    ?>
                </div>
            </div>
            <?php
            if (JFactory::getApplication()->input->getBool("debug")) :
                echo sprintf(
                    '<textarea style="width:100%;height:500px;"><pre>%s</pre></textarea>',
                    print_r($this->item, 1)
                );
            endif;
            ?>
        </div>
    </div>

<?php
echo $this->loadTemplate('mapjs');
