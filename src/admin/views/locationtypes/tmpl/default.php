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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$task      = JFactory::getApplication()->input->getCmd('task');

if ($saveOrder) :
    $saveOrderingUrl = 'index.php?option=com_focalpoint&task=locationtypes.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'locationTypesList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
endif;

$mainContainer = [
    'id' => 'j-main-container'
];
if (!empty($this->sidebar)) {
    $mainContainer['class'] = 'span10';
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=locationtypes'); ?>"
      method="post"
      id="adminForm"
      name="adminForm">
    <?php
    if (!empty($this->sidebar)) :
        ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
    <?php endif; ?>

    <div <?php echo ArrayHelper::toString($mainContainer); ?>>
        <?php
        // Search tools bar
        if ($task != 'showhelp') :
            echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
        endif;

        if (empty($this->items)) :
            if ($task == 'showhelp') : ?>
                <div class="fp_locationtypes_view">
                    <div class="hero-unit" style="text-align:left;">
                        <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_LOCATIONTYPES_NEW'); ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="alert alert-no-items">
                    <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <table class="table table-striped" id="locationTypesList">
                <thead>
                <tr>
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            '',
                            'a.ordering',
                            $listDirn,
                            $listOrder,
                            null,
                            'asc',
                            'JGRID_HEADING_ORDERING',
                            'icon-menu-2'
                        );
                        ?>
                    </th>

                    <th width="1%" class="hidden-phone">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>

                    <th width="1%" style="min-width:55px" class="nowrap center">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                    </th>

                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_FOCALPOINT_LOCATIONTYPES_TITLE',
                            'a.title',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_FOCALPOINT_LOCATIONTYPES_LEGEND',
                            'legend_title',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th width="10%" class="nowrap hidden-phone">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_FOCALPOINT_LOCATIONTYPES_CREATED_BY',
                            'created_by.name',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th width="1%" class="nowrap hidden-phone">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'JGRID_HEADING_ID',
                            'a.id',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $ordering = ($listOrder == 'a.ordering');
                    $canCreate = $user->authorise('core.create', 'com_focalpoint');
                    $canEdit = $user->authorise('core.edit', 'com_focalpoint');
                    $canEditOwn = $user->authorise('core.edit.own', 'com_focalpoint');
                    $canCheckin = $user->authorise('core.manage', 'com_focalpoint');
                    $canChange = $user->authorise('core.edit.state', 'com_focalpoint');
                    $editLink = JRoute::_('index.php?option=com_focalpoint&task=locationtype.edit&id=' . $item->id);
                    ?>

                    <tr class="<?php echo 'row' . ($i % 2); ?>" sortable-group-id="<?php echo $item->legend; ?>">
                        <td class="order nowrap center hidden-phone">
                            <?php
                            $class = 'sortable-handler';
                            if (!$canChange) :
                                $class .= ' inactive';
                            elseif (!$saveOrder) :
                                $class .= sprintf(
                                    ' inactive tip-top hasTooltip" title="%s"',
                                    HTMLHelper::tooltipText('JORDERINGDISABLED')
                                );
                            endif;
                            ?>
                            <span class="<?php echo $class; ?>">
                                <i class="icon-menu"></i>
                            </span>
                            <?php
                            if ($canChange && $saveOrder) : ?>
                                <input type="text"
                                       style="display:none"
                                       name="order[]"
                                       size="5"
                                       value="<?php echo $item->ordering; ?>"/>
                            <?php endif; ?>
                        </td>

                        <td class="center hidden-phone">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>

                        <td class="center">
                            <div class="btn-group">
                                <?php
                                echo HTMLHelper::_(
                                    'jgrid.published',
                                    $item->state,
                                    $i,
                                    'locationtypes.',
                                    $canChange,
                                    'cb'
                                );
                                ?>
                            </div>
                        </td>

                        <td class="has-context">
                            <div class="pull-left">
                                <?php
                                if ($item->checked_out) :
                                    echo HTMLHelper::_(
                                        'jgrid.checkedout',
                                        $i,
                                        $item->editor,
                                        $item->checked_out_time,
                                        'locationtypes.',
                                        $canCheckin
                                    );
                                endif;

                                if ($canEdit || $canEditOwn) :
                                    echo HTMLHelper::_(
                                        'link',
                                        $editLink,
                                        $this->escape($item->title),
                                        ['title' => JText::_('JACTION_EDIT')]
                                    );
                                else :
                                    echo $this->escape($item->title);
                                endif;
                                ?>
                            </div>
                        </td>

                        <td>
                            <?php echo $item->legend_title; ?>
                        </td>

                        <td class="small hidden-phone">
                            <?php echo $item->created_by; ?>
                        </td>

                        <td class="center hidden-phone">
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
</form>
