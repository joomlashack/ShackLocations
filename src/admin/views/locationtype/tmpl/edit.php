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
    ->useScript('form.validate');

?>
<form action="<?php echo Route::_('index.php?option=com_focalpoint&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post"
      enctype="multipart/form-data"
      name="adminForm"
      id="adminForm"
      class="form-validate">
    <?php
    echo $this->form->renderFieldset('hidden');

    echo LayoutHelper::render('joomla.edit.title_alias', $this);
    ?>
    <div class="main-card">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'locationtype', ['active' => 'general', 'recall' => true]);

        echo HTMLHelper::_('uitab.addTab', 'locationtype', 'general',
            Text::_('COM_FOCALPOINT_LOCATIONTYPE_GENERAL'));
        ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderFieldset('general'); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab');

        echo HTMLHelper::_(
            'uitab.addTab',
            'locationtype',
            'customfields',
            Text::_('COM_FOCALPOINT_LOCATIONTYPE_CUSTOM_FIELDS_LABEL')
        );
        ?>
        <div class="row">
            <?php echo $this->form->renderFieldset('customfields'); ?>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab');

        echo HTMLHelper::_('uitab.endTabSet');
        ?>
    </div>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
