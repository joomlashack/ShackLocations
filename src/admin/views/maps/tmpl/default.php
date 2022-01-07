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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * @var FocalpointViewMaps $this
 * @var object             $template
 * @var string             $layout
 * @var string             $layoutTemplate
 * @var Language           $lang
 * @var string             $filetofind
 */

HTMLHelper::_('bootstrap.tooltip');

$user      = Factory::getUser();
$ordering  = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
$saveOrder = $ordering == 'a.ordering';

if ($saveOrder && $this->items) :
    HTMLHelper::_('draggablelist.draggable');

    $saveOrderingUrl = 'index.php?' . http_build_query([
            'option'                => 'com_focalpoint',
            'task'                  => 'maps.saveOrderAjax',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1
        ]);

    $bodyAttribs = ArrayHelper::toString([
        'class'          => 'js-draggable',
        'data-url'       => $saveOrderingUrl,
        'data-direction' => strtolower($direction),
        'data-nested'    => 'true'
    ]);
endif;
?>
<form action="<?php echo Route::_('index.php?option=com_focalpoint&view=maps'); ?>"
      method="post"
      name="adminForm"
      id="adminForm">

    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

                <table class="adminlist table" id="adminList">
                    <thead>
                    <tr>
                        <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>

                        <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                            <?php
                            echo HTMLHelper::_(
                                'searchtools.sort',
                                '',
                                'a.ordering',
                                $direction,
                                $ordering,
                                null,
                                'asc',
                                'JGRID_HEADING_ORDERING',
                                'icon-menu-2'
                            );
                            ?>
                        </th>

                        <th scope="col" class="w-1 text-nowrap text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $direction,
                                $ordering); ?>
                        </th>

                        <th scope="col">
                            <?php
                            echo HTMLHelper::_(
                                'searchtools.sort',
                                'COM_FOCALPOINT_MAPS_TITLE',
                                'a.title',
                                $direction,
                                $ordering
                            );
                            ?>
                        </th>

                        <th scope="col" class="w-10 text-nowrap d-none d-md-table-cell">
                            <?php
                            echo HTMLHelper::_(
                                'searchtools.sort',
                                'COM_FOCALPOINT_MAPS_CREATED_BY',
                                'created_by_alias',
                                $direction,
                                $ordering
                            );
                            ?>
                        </th>

                        <th scope="col" class="w-1 text-nowrap d-none d-md-table-cell">
                            <?php
                            echo HTMLHelper::_(
                                'searchtools.sort',
                                'JGRID_HEADING_ID',
                                'a.id',
                                $direction,
                                $ordering
                            ); ?>
                        </th>

                    </tr>
                    </thead>

                    <tbody <?php echo $bodyAttribs ?? ''; ?>>
                    <?php
                    foreach ($this->items as $i => $item) :
                        $editLink = Route::_('index.php?option=com_focalpoint&task=map.edit&id=' . $item->id);
                        $ordering = ($ordering == 'a.ordering');
                        $canCreate = $user->authorise('core.create', 'com_focalpoint');
                        $canEdit = $user->authorise('core.edit', 'com_focalpoint')
                            || ($user->authorise('core.edit.own', 'com_focalpoint') && $user->id == $item->created_by);
                        $canCheckin = $user->authorise('core.manage', 'com_focalpoint');
                        $canChange = $user->authorise('core.edit.state', 'com_focalpoint');
                        ?>
                        <tr class="<?php echo 'row' . ($i % 2); ?>"
                            data-draggable-group="0">
                            <td class="center hidden-phone">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>

                            <td class="text-nowrap text-center d-none d-md-table-cell">
                                <?php
                                $class = ['sortable-handler'];
                                $title = '';
                                if (($canChange && $saveOrder) == false) :
                                    $class[] = 'inactive';
                                    $title = HTMLHelper::tooltipText('JORDERINGDISABLED');
                                endif;
                                ?>
                                <span class="<?php echo join(' ', $class); ?>" title="<?php echo $title ?? ''; ?>">
                                    <span class="icon-ellipsis-v"></span>
                                </span>

                                <?php
                                if ($canChange && $saveOrder) :
                                    ?>
                                    <input type="text"
                                           name="order[]"
                                           size="5"
                                           class="width-20 text-area-order hidden"
                                           value="<?php echo $item->ordering; ?>"/>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                    <?php
                                    echo (new PublishedButton())->render(
                                        (int)$item->state,
                                        $i,
                                        [
                                            'task_prefix' => 'maps.',
                                            'id'          => 'state-' . $item->id
                                        ]
                                    );
                                    ?>
                            </td>

                            <th class="has-context">
                                <div class="break-word">
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor,
                                            $item->checked_out_time, 'maps.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit) :
                                        echo HTMLHelper::_(
                                            'link',
                                            $editLink,
                                            $this->escape($item->title),
                                            ['title' => Text::_('JACTION_EDIT')]
                                        ); ?>
                                    <?php else : ?>
                                        <span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
                                            <?php echo $this->escape($item->title); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </th>

                            <td class="d-none d-md-table-cell">
                                <?php echo $item->created_by_alias; ?>
                            </td>

                            <td class="text-center d-none d-md-table-cell">
                                <?php echo (int)$item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
