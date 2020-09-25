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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.ui', ['core', 'sortable']);
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'locationtype.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post"
      enctype="multipart/form-data"
      name="adminForm"
      id="adminForm"
      class="form-validate">
    <?php
    echo JLayoutHelper::render('joomla.edit.title_alias', $this);

    echo $this->form->renderFieldset('hidden');

    echo JHtml::_('bootstrap.startTabSet', 'locationtype', ['active' => 'general']);
    echo JHtml::_('bootstrap.addTab', 'locationtype', 'general', JText::_('COM_FOCALPOINT_LOCATIONTYPE_GENERAL'));
    ?>
    <div class="row-fluid">
        <div class="form-horizontal">
            <?php echo $this->form->renderFieldset('general'); ?>
        </div>
    </div>
    <?php
    echo JHtml::_('bootstrap.endTab');

    echo JHtml::_(
        'bootstrap.addTab',
        'locationtype',
        'customfields',
        JText::_('COM_FOCALPOINT_LOCATIONTYPE_CUSTOM_FIELDS_LABEL')
    );
    ?>
    <div class="row-fluid">
        <div class="form-horizontal">
            <?php echo $this->form->renderFieldset('customfields'); ?>
        </div>
    </div>
    <?php

    echo JHtml::_('bootstrap.endTab');

    echo JHtml::_('bootstrap.endTabSet');

    ?>
    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>
