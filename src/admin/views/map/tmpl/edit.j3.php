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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('script', '//maps.googleapis.com/maps/api/js?key=' . $this->params->get('apikey'));

$formFieldsets = $this->form->getFieldsets();
?>
<form name="adminForm"
      id="adminForm"
      action="<?php echo Route::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post"
      enctype="multipart/form-data"
      class="form-validate">

    <?php
    echo $this->form->renderFieldset('hidden');
    echo LayoutHelper::render('joomla.edit.title_alias', $this);

    echo HTMLHelper::_('bootstrap.startTabSet', 'map', ['active' => 'basic']);

    echo HTMLHelper::_('bootstrap.addTab', 'map', 'basic', Text::_($formFieldsets['basic']->label));
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
    echo HTMLHelper::_('bootstrap.endTab');

    $tabFieldset = $formFieldsets['tabs'];
    echo HTMLHelper::_('bootstrap.addTab', 'map', 'tabs', Text::_($tabFieldset->label));
    ?>
    <div class="row-fluid">
        <div class="form-vertical">
            <?php echo $this->form->renderFieldset('tabs'); ?>
        </div>
    </div>
    <?php
    echo HTMLHelper::_('bootstrap.endTab');

    $customTabs = array_filter($this->app->triggerEvent('onSlocmapTabs', [$this->form]));
    foreach ($customTabs as $customTab) :
        echo HTMLHelper::_('bootstrap.addTab', 'map', $customTab, Text::_($formFieldsets[$customTab]->label));
        $customDescription = $formFieldsets[$customTab]->description ?? null;
        ?>
        <div class="row-fluid">
            <div class="form-horizontal">
                <?php
                if ($customDescription) :
                    ?>
                    <div class="tab-description alert alert-info">
                        <span class="icon-info" aria-hidden="true"></span>
                        <?php echo $customDescription; ?>
                    </div>
                <?php
                endif;

                echo $this->form->renderFieldset($customTab); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('bootstrap.endTab');
    endforeach;

    echo HTMLHelper::_('bootstrap.addTab', 'map', 'metadata', Text::_($formFieldsets['metadata']->label));
    ?>
    <div class="row-fluid">
        <div class="form-horizontal">
            <?php echo $this->form->renderFieldset('metadata'); ?>
        </div>
    </div>
    <?php
    echo HTMLHelper::_('bootstrap.endTab');

    echo HTMLHelper::_('bootstrap.addTab', 'map', 'params', Text::_($formFieldsets['params']->label));
    ?>
    <div class="row-fluid">
        <div class="form-horizontal">
            <?php echo $this->form->renderFieldset('params'); ?>
        </div>
    </div>
    <?php
    echo HTMLHelper::_('bootstrap.endTab');

    echo HTMLHelper::_('bootstrap.endTabSet');
    ?>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
