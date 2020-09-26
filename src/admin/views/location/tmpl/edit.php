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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');

$params = JComponentHelper::getParams('com_focalpoint');
HTMLHelper::_('script', '//maps.googleapis.com/maps/api/js?key=' . $params->get('apikey'));

$formFieldsets = $this->form->getFieldsets();

?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'location.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
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

    echo LayoutHelper::render('joomla.edit.title_alias', $this);

    echo HTMLHelper::_('bootstrap.startTabSet', 'location', ['active' => 'basic']);

    echo HTMLHelper::_('bootstrap.addTab', 'location', 'basic', Text::_($formFieldsets['basic']->label));
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
    echo HTMLHelper::_('bootstrap.endTab');

    foreach ($formFieldsets as $fieldsetName => $fieldset) :
        echo HTMLHelper::_(
            'bootstrap.addTab',
            'location',
            $fieldsetName,
            Text::_($fieldset->label)
        );
        ?>
        <div class="form-horizontal">
            <?php
            if ($fieldset->description) :
                ?>
                <div class="tab-description alert alert-info">
                    <span class="icon-info" aria-hidden="true"></span>
                    <?php echo Text::_($fieldset->description); ?>
                </div>
                <?php
            endif;

            echo $this->form->renderFieldset($fieldsetName);
            echo HTMLHelper::_('bootstrap.endTab');
            ?>
        </div>
        <?php
    endforeach;

    echo HTMLHelper::_('bootstrap.endTabSet');
    ?>
    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
