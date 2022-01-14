<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\WebAsset\WebAssetManager;

defined('_JEXEC') or die();

/** @var WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->registerAndUseScript('googlemaps', '//maps.googleapis.com/maps/api/js?key=' . $this->params->get('apikey'));

$formFieldsets = $this->form->getFieldsets();
?>
<form name="adminForm"
      id="adminForm"
      action="<?php echo Route::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post"
      enctype="multipart/form-data"
      class="form-validate fp_joomla4">

    <?php
    echo $this->form->renderFieldset('hidden');
    echo LayoutHelper::render('joomla.edit.title_alias', $this);
    ?>
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'map', ['active' => 'basic', 'recall' => true]);

        echo HTMLHelper::_('uitab.addTab', 'map', 'basic', Text::_($formFieldsets['basic']->label));
        ?>
        <div class="row">
            <div class="col-lg-9">
                <div>
                    <fieldset class="adminform">
                        <?php echo $this->form->getLabel('text'); ?>
                        <?php echo $this->form->getInput('text'); ?>
                    </fieldset>
                </div>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderFieldset('settings'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab');

        $tabFieldset = $formFieldsets['tabs'];
        echo HTMLHelper::_('uitab.addTab', 'map', 'tabs', Text::_($tabFieldset->label));
        ?>
        <div class="row">
            <?php
            if ($tabDescription = Text::_($tabFieldset->description)) :
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
        <?php
        echo HTMLHelper::_('uitab.endTab');

        // Allow plugins to add form tabs
        PluginHelper::importPlugin('focalpoint');
        $this->app->triggerEvent('onSlocmapTabs', [$this->form]);

        echo HTMLHelper::_('uitab.addTab', 'map', 'metadata', Text::_($formFieldsets['metadata']->label));
        ?>
        <div class="row">
            <div class="form-horizontal">
                <?php echo $this->form->renderFieldset('metadata'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab');

        echo HTMLHelper::_('uitab.addTab', 'map', 'params', Text::_($formFieldsets['params']->label));
        ?>
        <div class="row">
            <div class="form-horizontal">
                <?php echo $this->form->renderFieldset('params'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab');

        echo HTMLHelper::_('uitab.endTabSet');
        ?>
    </div>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
