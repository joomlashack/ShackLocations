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

$params = JComponentHelper::getParams('com_focalpoint');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.ui', array('core', 'sortable'));
JHtml::_('script', '//maps.googleapis.com/maps/api/js?key=' . $params->get('apikey'));

$formFieldsets = $this->form->getFieldsets();
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'map.cancel' || document.formvalidator.isValid(document.id('map-form'))) {
            Joomla.submitform(task, document.getElementById('map-form'));
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
    echo $this->form->renderFieldset('hidden');
    echo JLayoutHelper::render('joomla.edit.title_alias', $this);

    echo JHtml::_('bootstrap.startTabSet', 'map', array('active' => 'basic'));

    echo JHtml::_('bootstrap.addTab', 'map', 'basic', JText::_($formFieldsets['basic']->label));
    ?>
    <div class="row-fluid">
        <div class="form-vertical">
            <div class="span9">
                <?php echo $this->form->renderFieldset('basic'); ?>
            </div>
            <div class="span3">
                <?php echo $this->form->renderFieldset('settings'); ?>
            </div>
        </div>
    </div>
    <?php
    echo JHtml::_('bootstrap.endTab');

    $tabFieldset = $formFieldsets['tabs'];
    echo JHtml::_('bootstrap.addTab', 'map', 'tabs', JText::_($tabFieldset->label));
    ?>
    <div class="row-fluid">
        <div class="form-vertical">
            <?php
            if ($tabDescription = JText::_($tabFieldset->description)) :
                ?>
                <div class="tab-description alert alert-info">
                    <span class="icon-info" aria-hidden="true"></span>
                    <?php echo $tabDescription; ?>
                </div>
                <?php
            endif;

            echo $this->form->renderFieldset('tabs');
            ?>
        </div>
    </div>
    <?php
    echo JHtml::_('bootstrap.endTab');

    // Allow pluginsto add form tabs
    JPluginHelper::importPlugin('focalpoint');
    JFactory::getApplication()->triggerEvent('onLoadMapTabs', array($this->form));

    echo JHtml::_('bootstrap.addTab', 'map', 'metadata', JText::_($formFieldsets['metadata']->label));
    ?>
    <div class="row-fluid">
        <div class="form-horizontal">
            <?php echo $this->form->renderFieldset('metadata'); ?>
        </div>
    </div>
    <?php
    echo JHtml::_('bootstrap.endTab');

    echo JHtml::_('bootstrap.addTab', 'map', 'params', JText::_($formFieldsets['params']->label));
    ?>
    <div class="row-fluid">
        <div class="form-horizontal">
            <?php echo $this->form->renderFieldset('params'); ?>
        </div>
    </div>
    <?php
    echo JHtml::_('bootstrap.endTab');

    echo JHtml::_('bootstrap.endTabSet');
    ?>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>
