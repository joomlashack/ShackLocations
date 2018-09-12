<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
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
