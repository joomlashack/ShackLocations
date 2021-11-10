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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$task      = Factory::getApplication()->input->getCmd('task');
$mainClass = empty($this->sidebar) ? 'span12' : 'span10';

if ($saveOrder) :
    $saveOrderingUrl = 'index.php?option=com_focalpoint&task=legends.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'legendsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
endif;
?>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=legends'); ?>"
      method="post"
      name="adminForm"
      id="adminForm">
    <?php
    if (!empty($this->sidebar)) :
        ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
    <?php
    endif;
    ?>
    <div id="j-main-container" class="<?php echo $mainClass; ?>">
        <?php
        if ($task != "showhelp") :
            echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
        endif;

        if (empty($this->items)) :
            if ($task == "showhelp") :
                ?>
                <div class="fp_legends_view">
                    <div class="hero-unit" style="text-align:left;">
                        <?php echo Text::_('COM_FOCALPOINT_GETSTARTED_LEGENDS'); ?>
                    </div>
                </div>
            <?php
            else :
                ?>
                <div class="alert alert-no-items">
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
            <?php
            endif;
        else :
            ?>
            <table class="table table-striped" id="legendsList">
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
                            'COM_FOCALPOINT_LEGENDS_TITLE',
                            'a.title',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_FOCALPOINT_LEGENDS_CREATED_BY',
                            'a.created_by',
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
                <?php
                foreach ($this->items as $i => $item) :
                    $ordering = ($listOrder == 'a.ordering');
                    $canCreate = $user->authorise('core.create', 'com_focalpoint');
                    $canEdit = $user->authorise('core.edit', 'com_focalpoint') || $item->checked_out == $user->id;
                    $canCheckin = $user->authorise('core.manage', 'com_focalpoint');
                    $canChange = $user->authorise('core.edit.state', 'com_focalpoint');
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="0">
                        <td class="order nowrap center hidden-phone">
                            <?php
                            $sortableAttribs = ['class' => 'sortable-handler'];

                            if (!$canChange) :
                                $sortableAttribs['class'] .= ' inactive';

                            elseif (!$saveOrder) :
                                $sortableAttribs['class'] .= ' inactive tip-top hasTooltip';
                                $sortableAttribs['title'] = HTMLHelper::tooltipText('JORDERINGDISABLED');
                            endif;

                            echo sprintf(
                                '<span %s> <i class="icon-menu"></i></span>',
                                ArrayHelper::toString($sortableAttribs)
                            );

                            if ($canChange && $saveOrder) :
                                ?>
                                <input type="text"
                                       style="display:none"
                                       name="order[]"
                                       value="<?php echo $item->ordering; ?>"/>
                                 <?php
                            endif;
                            ?>
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
                                    'legends.',
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
                                        'legends.',
                                        $canCheckin
                                    );
                                endif;

                                if ($canEdit) :
                                    echo HTMLHelper::_(
                                        'link',
                                        JRoute::_('index.php?option=com_focalpoint&task=legend.edit&id=' . $item->id),
                                        $this->escape($item->title),
                                        sprintf('title="%s"', Text::_('JACTION_EDIT'))
                                    );

                                else :
                                    echo $this->escape($item->title);
                                endif;
                                ?>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $item->created_by; ?>
                        </td>

                        <td class="center hidden-phone">
                            <?php echo (int)$item->id; ?>
                        </td>
                    </tr>
                <?php
                endforeach;
                ?>
                </tbody>
            </table>
            <?php
            echo $this->pagination->getListFooter();
        endif;
        ?>
        <div>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </div>
</form>
