<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2022 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

?>
<form name="adminForm"
      id="adminForm"
      action="<?php echo Route::_('index.php?option=com_focalpoint'); ?>"
      method="post"
      class="fp_config">
    <?php
    $class = '';
    if ($this->sidebar) :
        $class = 'span10';
        ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
    <?php endif; ?>
    <div id="j-main-container" class="<?php echo $class; ?>>">
        <div id="fp_pointer"></div>
        <div class="hero-unit" style="text-align:left;">
            <?php echo Text::_('COM_FOCALPOINT_GETSTARTED_CONFIG'); ?>
        </div>
    </div>
</form>
