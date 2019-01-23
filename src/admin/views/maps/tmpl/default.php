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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDir   = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$task      = JFactory::getApplication()->input->getCmd('task');

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_focalpoint&task=maps.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'mapsList', 'adminForm', strtolower($listDir), $saveOrderingUrl);
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_focalpoint&view=maps'); ?>"
      method="post"
      name="adminForm"
      id="adminForm">

    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>

    <div id="j-main-container" class="span10">
        <?php
        if ($task != "showhelp") :
            echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        endif;

        if (empty($this->items)) :
            if ($task == "showhelp") :
                ?>
                <div class="fp_maps_view">
                    <div id="fp_pointer"></div>
                    <div class="hero-unit" style="text-align:left;">
                        <?php echo JText::_('COM_FOCALPOINT_GETSTARTED_MAPS_NEW'); ?>
                    </div>
                </div>
                <?php
            else :
                ?>
                <div class="alert alert-no-items">
                    <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
                <?php
            endif;
        else :
            ?>
            <table class="table table-striped" id="mapsList">
                <thead>
                <tr>
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php
                        echo JHtml::_(
                            'searchtools.sort',
                            '',
                            'a.ordering',
                            $listDir,
                            $listOrder,
                            null,
                            'asc',
                            'JGRID_HEADING_ORDERING',
                            'icon-menu-2'
                        );
                        ?>
                    </th>

                    <th width="1%" class="hidden-phone">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>

                    <th width="1%" style="min-width:55px" class="nowrap center">
                        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDir, $listOrder); ?>
                    </th>

                    <th>
                        <?php
                        echo JHtml::_(
                            'searchtools.sort',
                            'COM_FOCALPOINT_MAPS_TITLE',
                            'a.title',
                            $listDir,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th width="10%" class="nowrap hidden-phone">
                        <?php
                        echo JHtml::_(
                            'searchtools.sort',
                            'COM_FOCALPOINT_MAPS_CREATED_BY',
                            'a.created_by',
                            $listDir,
                            $listOrder
                        );
                        ?>
                    </th>

                    <th width="1%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDir, $listOrder); ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    $ordering = ($listOrder == 'a.ordering');
                    $canCreate = $user->authorise('core.create', 'com_focalpoint');
                    $canEdit = $user->authorise('core.edit', 'com_focalpoint');
                    $canCheckin = $user->authorise('core.manage', 'com_focalpoint');
                    $canChange = $user->authorise('core.edit.state', 'com_focalpoint');
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="0">
                        <td class="order nowrap center hidden-phone">
                            <?php
                            $iconClass = '';
                            if (!$canChange) {
                                $iconClass = ' inactive';
                            } elseif (!$saveOrder) {
                                $iconClass = ' inactive tip-top hasTooltip" title="'
                                    . JHtml::tooltipText('JORDERINGDISABLED');
                            }
                            ?>
                            <span class="sortable-handler<?php echo $iconClass ?>">
                                <i class="icon-menu"></i>
                            </span>
                            <?php
                            if ($canChange && $saveOrder) :
                                ?>
                                <input type="text"
                                       style="display:none"
                                       name="order[]"
                                       size="5"
                                       value="<?php echo $item->ordering; ?>"/>
                                <?php
                            endif;
                            ?>
                        </td>

                        <td class="center hidden-phone">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>

                        <td class="center">
                            <div class="btn-group">
                                <?php echo JHtml::_('jgrid.published', $item->state, $i, 'maps.', $canChange, 'cb'); ?>
                            </div>
                        </td>

                        <td class="has-context">
                            <div class="pull-left">
                                <?php
                                if ($item->checked_out) :
                                    echo JHtml::_(
                                        'jgrid.checkedout',
                                        $i,
                                        $item->editor,
                                        $item->checked_out_time,
                                        'maps.',
                                        $canCheckin
                                    );
                                endif;

                                if ($canEdit) :
                                    echo JHtml::_(
                                        'link',
                                        JRoute::_('index.php?option=com_focalpoint&task=map.edit&id=' . $item->id),
                                        $this->escape($item->title),
                                        array('title' => JText::_('JACTION_EDIT'))
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

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>

