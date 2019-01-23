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

$params = JComponentHelper::getParams('com_focalpoint');
JHtml::_('script', '//maps.googleapis.com/maps/api/js?key=' . $params->get('apikey'));

$formFieldsets = $this->form->getFieldsets();

?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'location.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>
<form name="adminForm"
      id="adminForm"
      action="<?php echo JRoute::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post"
      enctype="multipart/form-data"
      class="tmpl_<?php echo JFactory::getApplication()->getTemplate(); ?> form-validate">

    <?php
    echo $this->form->renderFieldset('hidden');
    unset($formFieldsets['hidden']);

    echo JLayoutHelper::render('joomla.edit.title_alias', $this);

    echo JHtml::_('bootstrap.startTabSet', 'location', array('active' => 'basic'));

    echo JHtml::_('bootstrap.addTab', 'location', 'basic', JText::_($formFieldsets['basic']->label));
    ?>
    <div class="row-fluid">
        <div class="span9">
            <div class="form-vertical">
                <?php
                echo $this->form->renderFieldset('basic');
                unset($formFieldsets['basic']);
                ?>
            </div>
        </div>

        <div class="span3">
            <div class="form-vertical">
                <?php
                echo $this->form->renderFieldset('params');
                unset($formFieldsets['params']);
                ?>
            </div>
        </div>
    </div>
    <?php
    echo JHtml::_('bootstrap.endTab');

    foreach ($formFieldsets as $fieldsetName => $fieldset) :
        echo JHtml::_(
            'bootstrap.addTab',
            'location',
            $fieldsetName,
            JText::_($fieldset->label)
        );
        ?>
        <div class="form-horizontal">
            <?php
            if ($fieldset->description) :
                ?>
                <div class="tab-description alert alert-info">
                    <span class="icon-info" aria-hidden="true"></span>
                    <?php echo JText::_($fieldset->description); ?>
                </div>
                <?php
            endif;

            echo $this->form->renderFieldset($fieldsetName);
            echo JHtml::_('bootstrap.endTab');
            ?>
        </div>
        <?php
    endforeach;

    echo JHtml::_('bootstrap.endTabSet');
    ?>
    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>

</form>
