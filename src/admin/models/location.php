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
            foreach ((array)$data->type as $value) {
                if (!is_array($value)) {
                    $array[] = $value;
                }
            }
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

            $item->othertypes = json_decode($item->othertypes);

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
        if (empty($data['othertypes'])) {
            $data['othertypes'] = '';
        }

        if (parent::save($data)) {
            $db = $this->getDbo();
            $id = $data['id'] ?: $db->insertid();

            $sql = $db->getQuery(true)
                ->delete('#__focalpoint_location_type_xref')
                ->where('location_id = ' . $id);
            $db->setQuery($sql)->execute();

            $types = array_merge(
                array($data['type']),
                empty($data['othertypes']) ? array() : $data['othertypes']
            );
            $types = array_filter(array_unique($types));

            foreach ($types as $type) {
                $insert = (object)array(
                    'location_id'     => $id,
                    'locationtype_id' => $type
                );
                $db->insertObject('#__focalpoint_location_type_xref', $insert);
            }

            return true;
        }

        return false;
    }
}
