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

$task = JFactory::getApplication()->input->getCmd('task', 'config');
?>
<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=legends'); ?>" method="post" name="adminForm"
      id="adminForm" class="fp_<?php echo $task; ?> tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?>">
    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php else : ?>
        <div id="j-main-container">
            <?php endif; ?>

            <div id="fp_pointer"></div>
            <div class="hero-unit" style="text-align:left;">
                <?php if ($task == 'config') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_CONFIG'); ?>
                <?php } ?>

                <?php if ($task == 'map') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_MAPS'); ?>
                <?php } ?>

                <?php if ($task == 'legend') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_LEGENDS'); ?>
                <?php } ?>

                <?php if ($task == 'locationtype') { ?>
                    <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_LOCATIONTYPES'); ?>
                <?php } ?>
            </div>

        </div>
</form>
