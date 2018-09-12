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
    <p class="fp_customfield fp_image">
	<span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
	<?php } ?>
	<?php echo '<img src="'.JUri::base(true)."/".$this->outputfield->data.'" title="'.$this->outputfield->label.'" alt="'.$this->outputfield->label.'" />'; ?>

    <?php if (!$this->outputfield->hidelabel) { ?>
    </p>
    <?php } ?>
