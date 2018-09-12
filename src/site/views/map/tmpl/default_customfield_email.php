<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<p class="fp_customfield fp_email">
	<?php if (!$this->outputfield->hidelabel) { ?>
	<span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
    <?php } ?>
	<a href="mailto:<?php echo $this->outputfield->data->email; ?>" >
        <?php echo ($this->outputfield->data->linktext!="")?$this->outputfield->data->linktext:$this->outputfield->data->email; ?>
    </a>
</p>
