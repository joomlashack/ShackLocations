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

defined('_JEXEC') or die('Restricted access');
?>
<?php if (!$this->outputfield->hidelabel) { ?>
<p class="fp_customfield fp_selectlist">
<?php } ?>
    <?php $first = true; ?>
    <?php foreach ($this->outputfield->data as $data) { ?>
	    <?php if (!$this->outputfield->hidelabel) { ?>
            <?php if (!$first){?>
                <br />
            <?php } ?>
	        <span class="fp_label"><?php echo $first?($this->outputfield->label.": "):" "; ?></span>
            <?php $first = false; ?>
	    <?php } ?>
	    <?php echo $data; ?>
    <?php } ?>
<?php if (!$this->outputfield->hidelabel) { ?>
</p>
<?php } ?>
