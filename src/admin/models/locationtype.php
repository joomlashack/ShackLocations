<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2020 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();


class FocalpointModellocationtype extends JModelAdmin
{
    protected $text_prefix = 'COM_FOCALPOINT';

    /**
     * @param string $type
     * @param string $prefix
     * @param array  $config
     *
     * @return Table
     */
    public function getTable($type = 'Locationtype', $prefix = 'FocalpointTable', $config = [])
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return Form
     * @throws Exception
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_focalpoint.locationtype',
            'locationtype',
            [
                'control'   => 'jform',
                'load_data' => $loadData
            ]
        );

        return $form;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_focalpoint.edit.locationtype.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * @param int $pk
     *
     * @return CMSObject
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if (isset($item->customfields)) {
                $item->customfields = json_decode($item->customfields, true);
            }

            if (empty($item->id)) {
                $item->created_by = Factory::getUser()->id;
            }
        }

        return $item;
    }
}
