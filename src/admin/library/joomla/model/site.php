<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021-2024 Joomlashack. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

abstract class FocalpointModelSite extends FormModel
{
    /**
     * @param object $location
     *
     * @return void
     */
    protected function formatCustomFields(object $location): void
    {
        $location->customfields = null;

        $customFieldsData = empty($location->customfieldsdata) ? null : $location->customfieldsdata;
        $type             = empty($location->type) ? null : $location->type;

        if ($customFieldsData && $type) {
            $customFieldsData = json_decode($customFieldsData, true);
            unset($location->customfieldsdata);

            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select('customfields')
                ->from('#__focalpoint_locationtypes')
                ->where('id = ' . $type);

            $customFields  = [];
            $fieldSettings = json_decode((string)$db->setQuery($query)->loadResult());

            if ($fieldSettings) {
                foreach ($fieldSettings as $hash => $customField) {
                    if (!empty($customFieldsData[$hash])) {
                        $fieldName = $customField->name;
                        $fieldData = $customFieldsData[$hash];

                        if (isset($fieldData[$fieldName])) {
                            $customFields[$fieldName] = (object)[
                                'datatype' => $customField->type,
                                'label'    => $customField->label,
                                'data'     => $fieldData[$fieldName],
                            ];
                        }
                    }
                }
            }

            $location->customfields = (object)$customFields;
        }
    }
}
