<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021 Joomlashack.com. All rights reserved
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

use Joomla\CMS\MVC\Model\FormModel;

defined('_JEXEC') or die();

abstract class FocalpointModelSite extends FormModel
{
    /**
     * @param string|object $customFieldsData
     * @param int           $locationType
     *
     * @return object
     */
    protected function xprocessCustomFields($customFieldsData, $locationType)
    {
        $customFields     = null;
        $customFieldsData = is_string($customFieldsData) ? json_decode($customFieldsData) : $customFieldsData;

        if ($customFieldsData && is_object($customFieldsData)) {
            /*
             * Grab the location type record so we can match up the label. We don't save the labels with the data.
             * This is so we can change individual labels at any time without having to update every record.
             */
            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select('customfields')
                ->from('#__focalpoint_locationtypes')
                ->where('id = ' . $locationType);

            $fieldSettings = (json_decode($db->setQuery($query)->loadResult()));

            $customFields = (object)[];
            foreach ($customFieldsData as $fieldName => $value) {
                $field = empty($fieldSettings->{$fieldName}) ? null : $fieldSettings->{$fieldName};
                if (is_object($field)) {
                    $customFields->{$field->name} = (object)[
                        'datatype' => $field->type,
                        'label'    => $field->label,
                        'data'     => $value
                    ];
                }
            }
        }

        return $customFields;
    }

    /**
     * @return object
     */
    protected function formatCustomFields($location)
    {
        $customFields = null;

        $customFieldsData = empty($location->customfieldsdata) ? null : $location->customfieldsdata;
        $type             = empty($location->type) ? null : $location->type;

        if ($customFieldsData && $type) {
            $customFieldsData = json_decode($customFieldsData, true);

            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select('customfields')
                ->from('#__focalpoint_locationtypes')
                ->where('id = ' . $type);

            $customFields  = [];
            $fieldSettings = json_decode($db->setQuery($query)->loadResult());

            if ($fieldSettings) {
                foreach ($fieldSettings as $hash => $customField) {
                    if (!empty($customFieldsData[$hash])) {
                        $fieldName = $customField->name;
                        $fieldData = $customFieldsData[$hash];

                        if (isset($fieldData[$fieldName])) {
                            $customFields[$fieldName] = (object)[
                                'datatype' => $customField->type,
                                'label'    => $customField->label,
                                'data'     => $fieldData[$fieldName]
                            ];
                        }
                    }
                }
            }

            $customFields = (object)$customFields;
        }

        return $customFields;
    }
}
