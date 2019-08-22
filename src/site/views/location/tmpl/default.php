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

defined('_JEXEC') or die;

if ($this->item->params->get('loadBootstrap')) :
    JHtml::_('stylesheet', 'components/com_focalpoint/assets/css/bootstrap.css');
    JHtml::_('bootstrap.framework');
endif;

if (empty($this->item->backlink)) :
    $backLink = null;

else :
    $backLink = JHtml::_(
        'link',
        $this->item->backlink,
        JText::_('COM_FOCALPOINT_BACK_TO_MAP'),
        'class="backtomap"'
    );
endif;

$pageHeading = $this->getPageHeading();
$pageClass   = $this->getPageClass('fp-location-view');

?>
<div id="focalpoint" class="<?php echo $pageClass; ?>">
    <div class="row-fluid">
        <?php if ($pageHeading) : ?>
            <h1><?php echo $pageHeading; ?></h1>
        <?php endif;

        echo sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $pageHeading ? 'h2' : 'h1',
            $backLink ? ' class="backlink"' : '',
            trim($backLink . ' ' . $this->item->title)
        );
        ?>
    </div>

    <div class="row-fluid">
        <div class="fp_left_column span8">
            <div id="fp_googleMap"></div>
            <?php
            if ($this->item->params->get('getdirections')) :
                ?>
                <div id="fp_googleMap_directions"></div>
                <div id="fp_map_actions" class="input-append">
                    <form onsubmit="return false;">
                        <label for="fp_searchAddress">
                            <?php echo JText::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>
                        </label>
                        <input id="fp_searchAddress"
                               type="text"
                               placeholder="<?php echo JText::_('COM_FOCALPOINT_YOUR_ADDRESS'); ?>"
                               value=""/>
                        <button class="btn"
                                id="fp_searchAddressBtn"
                                type="button">
                            <?php echo JText::_('COM_FOCALPOINT_GET_DIRECTIONS'); ?>
                        </button>
                    </form>
                </div>

            <?php endif;

            if (!$this->item->params->get('hideintrotext')) :
                echo $this->item->description;
            endif;

            echo $this->item->fulldescription;

            if ($this->item->customfields) : ?>
                <div class="fp_customfields fp_content">
                    <?php
                    foreach ($this->item->customfields as $key => $customfield) :
                        echo $this->renderField($customfield);
                    endforeach;
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="fp_right_column span4">
            <?php echo JHtml::_('content.prepare', '{loadposition shacklocations-above-info}'); ?>
            <?php if ($this->item->address || $this->item->phone) : ?>
                <div class="row-fluid fp_address">
                    <?php if ($this->item->address) : ?>
                        <div class="span12">
                            <h3><?php echo JText::_('COM_FOCALPOINT_ADDRESS'); ?>:</h3>
                            <p><?php echo $this->item->address; ?></p>
                        </div>
                    <?php endif;

                    if ($this->item->phone) :
                        ?>
                        <div class="span12">
                            <h3><?php echo JText::_('COM_FOCALPOINT_PHONE'); ?>:</h3>
                            <p><?php echo $this->item->phone; ?></p>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif;

            echo JHtml::_('content.prepare', '{loadposition shacklocations-below-info}');

            if ($this->item->image) :
                ?>
                <div class="fp_article_image">
                    <p><img src="<?php echo $this->item->image; ?>" title=""/></p>
                </div>
            <?php endif;

            echo JHtml::_('content.prepare', '{loadposition shacklocations-below-image}');
            ?>
        </div>
    </div>

    <div class="row-fluid">
        <?php
        if ($backLink) :
            echo sprintf('<p class="btn_backtomap">%s</p>', $backLink);
        endif;
        ?>
    </div>

    <?php
    echo $this->loadTemplate('mapjs');

    echo JHtml::_('content.prepare', '{loadposition shacklocations-below-map}');

    if ($this->app->input->getBool('debug')) :
        echo '<pre>' . print_r($this->item, 1) . '</pre>';
    endif;
    ?>
</div>
