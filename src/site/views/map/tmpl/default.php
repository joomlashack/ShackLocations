<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2024 Joomlashack. All rights reserved
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
use Joomla\CMS\Version;

defined('_JEXEC') or die();

$legendPosition = $this->params->get('legendposition');
$pageHeading    = $this->getPageHeading($this->item->title);
$pageClass      = $this->getPageClass('fp-map-view legend_' . $legendPosition);

?>
    <div id="focalpoint" class="<?php echo $pageClass; ?>">
        <?php if ($pageHeading) : ?>
            <h1><?php echo $pageHeading; ?></h1>
        <?php endif;

        if ($this->item->text) :
            ?>
            <div class="fp_mapintro clearfix">
                <?php echo $this->item->text; ?>
            </div>
        <?php endif; ?>

        <div id="fp_main" class="clearfix">
            <?php
            echo $this->loadTemplate($this->mapEngine . '_tabs');

            if ($this->app->input->getBool('debug')) :
                echo sprintf(
                    '<textarea style="width:100%;height:500px;"><pre>%s</pre></textarea>',
                    print_r($this->item, 1)
                );
            endif;
            ?>
        </div>
    </div>
<?php

echo $this->loadTemplate($this->mapEngine);
