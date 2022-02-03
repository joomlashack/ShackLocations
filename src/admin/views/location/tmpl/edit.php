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
    unset($formFieldsets['hidden']);

    echo LayoutHelper::render('joomla.edit.title_alias', $this);

    echo HTMLHelper::_('uitab.startTabSet', 'location', ['active' => 'basic', 'recall' => true]);

    echo HTMLHelper::_('uitab.addTab', 'location', 'basic', Text::_($formFieldsets['basic']->label));
    ?>
    <div class="row">
        <div class="col-lg-9">
            <div>
                <?php
                echo $this->form->renderFieldset('basic');
                unset($formFieldsets['basic']);
                ?>
            </div>
        </div>
        <div class="col-lg-3">
            <?php
            echo $this->form->renderFieldset('params');
            unset($formFieldsets['params']);
            ?>

        </div>
    </div>

    <?php
    echo HTMLHelper::_('uitab.endTab');

    foreach ($formFieldsets as $fieldsetName => $fieldset) :
        echo HTMLHelper::_(
            'uitab.addTab',
            'location',
            $fieldsetName,
            Text::_($fieldset->label)
        );
        ?>
        <div class="row">
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
            ?>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab');

    endforeach;

    echo HTMLHelper::_('uitab.endTabSet');
    ?>
    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
