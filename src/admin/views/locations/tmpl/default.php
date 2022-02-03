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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

/**
 * @var FocalpointViewLocations $this
 * @var object                  $template
 * @var string                  $layout
 * @var string                  $layoutTemplate
 * @var Language                $lang
 * @var string                  $filetofind
 */

HTMLHelper::_('bootstrap.tooltip');

$user      = Factory::getUser();
$ordering  = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
$saveOrder = $ordering == 'a.ordering';

if ($saveOrder && $this->items) :
    HTMLHelper::_('draggablelist.draggable');

    $saveOrderingUrl = 'index.php?' . http_build_query([
            'option' => 'com_focalpoint',
            'task'   => 'locations.saveOrderAjax',
            'tmpl'   => 'component'
        ]);

    $bodyAttribs = ArrayHelper::toString([
        'class'          => 'js-draggable',
        'data-url'       => $saveOrderingUrl,
        'data-direction' => strtolower($direction),
        'data-nested'    => 'true'
    ]);
endif;
?>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=locations'); ?>"
      method="post"
      name="adminForm"
      id="adminForm">

    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);

                if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>

                <?php else :
                    ?>
                    <table class="adminlist table" id="adminList">
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </th>

                            <th scope="col" class="w-1 text-nowrap text-center d-none d-md-table-cell">
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
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JSTATUS',
                                    'a.state',
                                    $direction,
                                    $ordering
                                );
                                ?>
                            </th>

                            <th scope="col">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_FOCALPOINT_LOCATIONS_TITLE',
                                    'a.title',
                                    $direction,
                                    $ordering
                                );
                                ?>
                            </th>

                            <th scope="col">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_FOCALPOINT_LOCATIONS_MAP_ID',
                                    'map.title',
                                    $direction,
                                    $ordering
                                );
                                ?>
                            </th>

                            <th scope="col">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_FOCALPOINT_LOCATIONS_LEGEND',
                                    'legend.title',
                                    $direction,
                                    $ordering
                                );
                                ?>
                            </th>

                            <th scope="col">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_FOCALPOINT_LOCATIONS_TYPE',
                                    'type.title',
                                    $direction,
                                    $ordering
                                );
                                ?>
                            </th>

                            <th scope="col" class="w-10 text-nowrap d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_FOCALPOINT_LOCATIONS_CREATED_BY',
                                    'creator.name',
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
                                );
                                ?>
                            </th>
                        </tr>
                        </thead>

                        <tbody <?php echo $bodyAttribs ?? ''; ?>>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $ordering = ($ordering == 'a.ordering');
                            $canCreate = $user->authorise('core.create', 'com_focalpoint');
                            $canEdit = $user->authorise('core.edit', 'com_focalpoint')
                                || ($user->authorise('core.edit.own') && $user->id == $item->created_by);
                            $canCheckin = $user->authorise('core.manage', 'com_focalpoint');
                            $canChange = $user->authorise('core.edit.state', 'com_focalpoint');
                            ?>
                            <tr class="<?php echo 'row' . ($i % 2); ?>"
                                data-draggable-group="<?php echo $item->map_id; ?>">
                                <td class="center hidden-phone">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>

                                <td class="text-nowrap text-center d-none d-md-table-cell">
                                    <?php
                                    $class = ['sortable-handler'];
                                    $title = '';
                                    if (($canChange && $saveOrder) == false) :
                                        $class[] = 'inactive';
                                        $title   = HTMLHelper::tooltipText('JORDERINGDISABLED');
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
                                            'id'          => 'state-' . $item->id,
                                            'disabled'    => $canChange == false
                                        ]
                                    );
                                    ?>
                                </td>

                                <td class="has-context">
                                    <div class="break-word">
                                        <?php
                                        if ($item->checked_out) :
                                            echo HTMLHelper::_(
                                                'jgrid.checkedout',
                                                $i,
                                                $item->editor,
                                                $item->checked_out_time,
                                                'locations.',
                                                $canCheckin
                                            );
                                        endif;

                                        if ($canEdit) :
                                            echo HTMLHelper::_(
                                                'link',
                                                Route::_('index.php?option=com_focalpoint&task=location.edit&id=' . $item->id),
                                                $this->escape($item->title),
                                                [
                                                    'title' => Text::_('JACTION_EDIT')
                                                ]
                                            );

                                        else :
                                            echo $this->escape($item->title);
                                        endif;
                                        ?>
                                    </div>
                                </td>

                                <td>
                                    <?php echo $item->map_title; ?>
                                </td>

                                <td>
                                    <?php echo $item->legend_title; ?>
                                </td>

                                <td>
                                    <?php echo $item->locationtype_title; ?>
                                </td>

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
                    <?php
                    echo $this->pagination->getListFooter();
                endif;
                ?>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
