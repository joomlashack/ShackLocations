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

use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die();

class FocalpointModellegend extends JModelAdmin
{
    protected $text_prefix = 'COM_FOCALPOINT';

    public function getTable($type = 'Legend', $prefix = 'FocalpointTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_focalpoint.legend',
            'legend',
            array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * @return CMSObject
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_focalpoint.edit.legend.data', array());

        if (empty($data)) {
            $data = $this->getItem();

        }

        return $data;
    }

    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if (empty($item->id)) {
                $item->created_by = JFactory::getUser()->id;
            }
        }

        return $item;
    }

    /**
     * @param JTable $table
     *
     * @return void
     */
    protected function prepareTable($table)
    {
        $table->alias = JFilterOutput::stringURLSafe($table->alias ?: $table->title);

        if (!$table->id) {
            $table->ordering   = $table->getNextOrder();
        }
    }
}
