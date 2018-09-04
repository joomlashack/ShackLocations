<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<?php if (!$this->outputfield->hidelabel) { ?>
<p class="fp_customfield fp_textarea">
	<span class="fp_label"><?php echo $this->outputfield->label.": "; ?></span>
<?php } ?>
<div><?php echo $this->outputfield->data; ?></div>
<?php if (!$this->outputfield->hidelabel) { ?>
    </p>
<?php } ?>