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

defined('_JEXEC') or die();

class FocalpointModellocation extends JModelAdmin
{
    protected $text_prefix = 'COM_FOCALPOINT';

    public function getTable($type = 'Location', $prefix = 'FocalpointTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_focalpoint.location',
            'location',
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form) {
            $customFields = $form->getXml()->xpath('//fieldset[@name="customfields"]');
            if ($customFields = array_pop($customFields)) {
                $customFields['description'] = 'COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_NONE_DEFINED';
            }
        }

        return $form;
    }

    protected function loadFormData()
    {
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_focalpoint.edit.location.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            $array = array();
            foreach ((array)$data->type as $value):
                if (!is_array($value)):
                    $array[] = $value;
                endif;
            endforeach;
            $data->type = implode(',', $array);
        }

        return $data;
    }

    /**
     * @param int $pk
     *
     * @return JObject
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Merge the intro and full text.
            $item->description = trim($item->fulldescription) != ''
                ? $item->description . "<hr id=\"system-readmore\" />" . $item->fulldescription
                : $item->description;

            $otherTypes       = new JRegistry($item->othertypes);
            $item->othertypes = $otherTypes->toArray();

            if ($item->customfieldsdata) {
                $item->custom = json_decode($item->customfieldsdata, true);
                if ($item->custom) {
                    foreach ($item->custom as $key => $value) {
                        $item->custom[$key] = $this->clean($value);
                    }
                }
            }

            // Convert the metadata field to an array.
            $metaData       = new JRegistry($item->metadata);
            $item->metadata = $metaData->toArray();
        }

        return $item;
    }

    /**
     * @param string|array $string
     *
     * @return string|array
     */
    protected function clean($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = $this->clean($value);
            }

        } else {
            $string = stripslashes($string);
        }
        return $string;
    }

    /**
     * @param JTable $table
     */
    protected function prepareTable($table)
    {
        $table->alias = JFilterOutput::stringURLSafe($table->alias ?: $table->title);

        // Split the description into two parts if required.
        $parts = preg_split('#(<hr\s+id="system-readmore"\s*/>)#', $table->description);
        if (count($parts) == 2) {
            $table->fulldescription = trim(array_pop($parts));
            $table->description     = trim(array_pop($parts));

        } else {
            $table->fulldescription = '';
        }

        if (!$table->id) {
            $table->ordering = $table->getNextOrder();
        }
    }

    /**
     * @param int $type
     *
     * @return array
     */
    public function getCustomFieldsHTML($type)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('customfields')
            ->from('#__focalpoint_locationtypes')
            ->where('id = ' . (int)$type);

        if ($customFields = $db->setQuery($query)->loadResult()) {
            return json_decode($customFields, true);
        }

        return array();
    }

    /**
     * @param array|string $data
     *
     * @return string
     */
    public function toJSON($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->toJSON($value);

            } else {
                $value = addslashes($value);
            }
        }
        return json_encode($data);
    }

    public function save($data)
    {
        $id = $data['id'];
        $db = $this->getDbo();

         // Including primary type in the other types field makes the frontend sql much easier
        if (!in_array($data['type'], $data['othertypes'])) {
            $data['othertypes'][] = $data['type'];
        }

        //Delete all xrefs before saving new.
        $sql = $db->getQuery(true);
        $sql->delete('#__focalpoint_location_type_xref');
        $sql->where('location_id = ' . $id);
        $db->setQuery($sql);
        $db->execute();

        $datasave = parent::save($data);

        //Get the last used id
        if (!isset($id) || $id == "") {
            $id = $db->insertid();
        }

        // Insert xrefs from this save. This is to cross ref location types against this location.
        foreach ($data['othertypes'] as $type) {
            $sql = $db->getQuery(true);
            $sql->insert('#__focalpoint_location_type_xref');
            $sql->values('NULL,' . $id . ',' . $type);
            $db->setQuery($sql);
            $db->execute();
        }
        return $datasave;

    }
}
