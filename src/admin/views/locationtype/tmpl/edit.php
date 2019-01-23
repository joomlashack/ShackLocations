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
JHtml::_('jquery.ui', array('core', 'sortable'));
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task == 'locationtype.cancel' || document.formvalidator.isValid(document.id('locationtype-form'))) {
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

    echo JHtml::_('bootstrap.startTabSet', 'locationtype', array('active' => 'general'));
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
    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span7 customfields">
                <?php
                $inserthtml = "";
                if (isset($this->item->custom)) {
                    foreach ($this->item->custom as $key1 => $array) {
                        $thiskey = explode(".", $key1);
                        switch ($thiskey[0]) {
                            case "textbox":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_TEXTBOX') . '</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][textbox.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "textarea":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Textarea</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][textarea.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LOAD_EDITOR') . '</label></div><div class="controls"><select name="jform[custom][textarea.' . $thiskey[1] . '][loadeditor]" class="inputbox" size="1" >';
                                $inserthtml .= '	<option value="1"' . ($array['loadeditor'] ? " selected=\"true\" " : "") . '>' . JText::_('JYES') . '</option>';
                                $inserthtml .= '	<option value="0"' . ($array['loadeditor'] ? "" : " selected=\"true\" ") . '>' . JText::_('JNO') . '</option>';
                                $inserthtml .= '</select></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "image":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Image</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DEFAULT_DIRECTORY') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' . $thiskey[1] . '][directory]" value="' . $array['directory'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "link":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Link</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][link.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "email":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Email</legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][email.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "selectlist":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Select List</legend></i><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_OPTIONS') . '</label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][selectlist.' . $thiskey[1] . '][options]" >' . $array['options'] . '</textarea></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;
                            case "multiselect":
                                $inserthtml .= '<fieldset><legend><i class="icon-menu"></i>&nbsp;Multi Select</legend></i><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN') .'"></a>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME') . '</label></div><div class="controls"><input readonly type="text" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][name]" value="' . $array['name'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][description]" value="' . $array['description'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL') . '</label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][label]" value="' . $array['label'] . '" /></div></div>';
                                $inserthtml .= '<div class="control-group"><div class="control-label"><label>' . JText::_('COM_FOCALPOINT_CUSTOMFIELD_OPTIONS') . '</label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][multiselect.' . $thiskey[1] . '][options]" >' . $array['options'] . '</textarea></div></div>';
                                $inserthtml .= '</fieldset>';
                                break;

                        }
                        $customFieldId = $thiskey[1];
                    }
                    $inserthtml .= "<script>jQuery.noConflict();";
                    $inserthtml .= "jQuery('.deletefield').click(function(){";
                    $inserthtml .= "    if (confirm('" . JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE') . "')) {";
                    $inserthtml .= "       jQuery(this).tooltip('hide');";
                    $inserthtml .= "       jQuery(this).parent().remove();";
                    $inserthtml .= "    }";
                    $inserthtml .= "});";
                    $inserthtml .= "</script>";
                }

                $inserthtml .= "<script>";
                $inserthtml .= "    jQuery('.customfields').sortable({handle : 'legend',axis:'y',opacity:'0.6', distance:'1'});";
                $inserthtml .= "</script>";
                echo $inserthtml;
                ?>
            </div>
        </div>
        <h4><?php echo JText::_('COM_FOCALPOINT_LOCATIONTYPE_CUSTOM_FIELDS_DESCRIPTION'); ?></h4>
        <dl class="adminformlist">
            <dd>
                <a id="add-textbox" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_TEXTBOX'); ?>
                </a>
            </dd>
            <dd>
                <a id="add-textarea" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_TEXTAREA'); ?>
                </a>
            </dd>
            <dd>
                <a id="add-image" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_IMAGE'); ?>
                </a>
            </dd>
            <dd>
                <a id="add-link" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_LINK'); ?>
                </a>
            </dd>
            <dd>
                <a id="add-email" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_EMAIL'); ?>
                </a>
            </dd>
            <dd>
                <a id="add-selectlist" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_SELECT_LIST'); ?>
                </a>
            </dd>
            <dd>
                <a id="add-multiselect" class="btn btn-small element-add" href="#">
                    <i class="icon-plus"></i>
                    <?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_MULTI_SELECT'); ?>
                </a>
            </dd>
        </dl>
    </div>
    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.endTabSet'); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>

</form>


<script>
    function makeid() {
        var text = "";
        var possible = "abcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < 10; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }

    jQuery.noConflict();
    jQuery(document).ready(function() {

        jQuery('#add-textbox').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_TEXTBOX'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field required" name="jform[custom][textbox.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][textbox.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-textarea').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_TEXTAREA'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][textarea.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][textarea.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LOAD_EDITOR'); ?></label></div><div class="controls"><select name="jform[custom][textarea.' + id + '][loadeditor]" class="inputbox" size="1" ></div></div>';
            inserthtml = inserthtml + '	<option value="1" selected="selected">Yes</option>';
            inserthtml = inserthtml + '	<option value="0">No</option>';
            inserthtml = inserthtml + '</select></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-image').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_IMAGE'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][image.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DEFAULT_DIRECTORY'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][image.' + id + '][directory]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-link').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_LINK'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][link.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][link.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-email').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_EMAIL'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][email.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][email.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-selectlist').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_SELECT_LIST'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][selectlist.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][selectlist.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_OPTIONS'); ?></label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][selectlist.' + id + '][options]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });

        jQuery('#add-multiselect').click(function() {
            var id = makeid();
            var inserthtml = '<fieldset><legend><i class="icon-menu"></i>&nbsp;<?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_MULTI_SELECT'); ?></legend><a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br /><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_DELETE_WARN'); ?>"></a>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label for="field' + id + '" class="required"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'); ?></label></div><div class="controls"><input id="field' + id + '" type="text" class="field" name="jform[custom][multiselect.' + id + '][name]" value="" required="required" aria-required="true" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' + id + '][description]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_LABEL'); ?></label></div><div class="controls"><input type="text" class="field" name="jform[custom][multiselect.' + id + '][label]" value="" /></div></div>';
            inserthtml = inserthtml + '<div class="control-group"><div class="control-label"><label><?php echo JText::_('COM_FOCALPOINT_CUSTOMFIELD_OPTIONS'); ?></label></div><div class="controls"><textarea style="width:300px;" rows="20" class="field" name="jform[custom][multiselect.' + id + '][options]" value="" /></div></div>';
            inserthtml = inserthtml + '</fieldset>';
            jQuery(inserthtml).fadeIn('slow').appendTo('.customfields');
            jQuery('.hasTooltip').tooltip({"html": true, "container": "body"});
            jQuery('.deletefield').click(function() {
                if (confirm('Delete this field?')) {
                    jQuery(this).tooltip('hide');
                    jQuery(this).parent().remove();
                }
            });
            return false;
        });
    })
</script>
