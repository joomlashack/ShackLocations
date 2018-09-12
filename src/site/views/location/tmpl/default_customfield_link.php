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
    <p class="fp_customfield fp_link">
    <span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
	<?php }?>
    <a href="<?php echo $this->outputfield->data->url; ?>" target="<?php echo $this->outputfield->data->target?"_blank":"_self"; ?>">
        <?php echo ($this->outputfield->data->linktext!="")?$this->outputfield->data->linktext:$this->outputfield->data->url; ?>
    </a>
    <?php if (!$this->outputfield->hidelabel) { ?>
    </p>
    <?php } ?>
