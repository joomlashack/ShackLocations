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

use Alledia\Installer\AbstractScript;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

$installPath = __DIR__ . (is_dir(__DIR__ . '/admin') ? '/admin' : '');
require_once $installPath . '/library/Installer/include.php';

class com_focalpointInstallerScript extends AbstractScript
{
    /**
     * @inheritDoc
     */
    protected function customUpdate(InstallerAdapter $parent): bool
    {
        if (version_compare($this->previousManifest->version, '1.2', 'lt')) {
            $this->sendMessage(Text::_('COM_FOCALPOINT_INSTALL_V12_NOTICE'), 'notice');
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function customPostFlight($type, $parent)
    {
        switch ($type) {
            case 'install':
            case 'discover_install':
                $this->moveMarkers();
                break;

            case 'update':
                $this->fixMapParameters();
                $this->updateTabsdata();
                $this->updateCustomFields();
                $this->updateCustomFieldsData();
                $this->removeObsoleteFiles();
                $this->checkParameters();
                break;
        }
    }

    /**
     * Update map/config parameters from string true/false to 1/0 to match global versions
     */
    protected function fixMapParameters()
    {
        $db    = $this->dbo;
        $query = $db->getQuery(true)
            ->select('id,params')
            ->from('#__focalpoint_maps');


        $maps = $db->setQuery($query)->loadObjectList();
        foreach ($maps as $map) {
            $params = json_decode($map->params, true);
            if (array_intersect($params, ['true', 'false', 'null', 'on', 'off'])) {
                foreach ($params as $name => $value) {
                    switch ($value) {
                        case 'true':
                        case 'on':
                            $params[$name] = '1';
                            break;

                        case 'false':
                        case 'off':
                        case 'null':
                            $params[$name] = '0';
                            break;
                    }
                }

                $map->params = json_encode($params);
                $db->updateObject('#__focalpoint_maps', $map, ['id']);
            }
        }

        /** @var Extension $table */
        $table = Table::getInstance('Extension');
        $table->load(['element' => 'com_focalpoint', 'type' => 'component']);

        $params      = json_decode($table->params);
        $paramsCheck = md5(json_encode($params));

        if (isset($params->mapTypeControl) && in_array($params->mapTypeControl, ['true', 'false'])) {
            $params->mapTypeControl = $params->mapTypeControl == 'true' ? '1' : '0';
        }
        if (isset($params->maxzoom) && $params->maxzoom == 'null') {
            $params->maxzoom = '0';
        }
        if (isset($params->mapsearchprompt) && $params->mapsearchprompt == 'Suburb or Postal code') {
            $params->mapsearchprompt = '';
        }
        if (isset($params->showmarkers) && in_array($params->showmarkers, ['on', 'off'])) {
            $params->showmarkers = $params->showmarkers == 'on' ? '1' : '0';
        }

        if ($paramsCheck != md5(json_encode($params))) {
            $table->params = json_encode($params);
            $table->store();
        }
    }

    /**
     * Move the markers to the images folder (on new install only)
     */
    protected function moveMarkers()
    {
        $source      = $this->installer->getPath('source') . '/assets/markers';
        $destination = JPATH_SITE . '/images/markers';

        if (Folder::move($source, $destination)) {
            $this->sendMessage(Text::sprintf('COM_FOCALPOINT_INSTALL_MARKER_SUCCESS', $destination), 'notice');

        } else {
            $this->sendMessage(Text::_('COM_FOCALPOINT_INSTALL_MARKER_FAIL'), 'notice');
        }
    }

    /**
     * Removes all obsolete files that are easier to remove here rather than in the manifest
     */
    protected function removeObsoleteFiles()
    {
        $files = array_merge(
            Folder::files(JPATH_SITE . '/language', '.*focalpoint.*', true, true),
            Folder::files(JPATH_ADMINISTRATOR . '/language', '.*focalpoint.*', true, true),
            Folder::files(
                JPATH_SITE . '/components/com_focalpoint/views',
                '^default_customfield_.*\.php$',
                true,
                true
            )
        );

        foreach ($files as $file) {
            File::delete($file);
        }
    }

    /**
     * Reformats the tabsdata field
     *
     * @return void
     * @since v1.4.0
     */
    protected function updateTabsdata()
    {
        $db = $this->dbo;

        $query = $db->getQuery(true)
            ->select([
                'id',
                'tabsdata'
            ])
            ->from('#__focalpoint_maps');

        $maps = $db->setQuery($query)->loadObjectList();

        $fixed = 0;
        foreach ($maps as $map) {
            if ($tabsdata = json_decode($map->tabsdata)) {
                $newData = [];
                if (!isset($tabsdata->tabs)) {
                    foreach ($tabsdata as $hash => $tab) {
                        if ($hash =='mapstyle') {
                            $newData[$hash] = $tab;
                        } else {
                            $newData['tabs'][$hash] = $tab;
                        }
                    }
                }
                if ($newData) {
                    $map->tabsdata = json_encode($newData);
                    $fixed         += (int)$db->updateObject('#__focalpoint_maps', $map, ['id']);
                }
            }
        }
    }

    /**
     * update the custom fields in location types
     *
     * @return void
     * @since v1.4.0
     */
    protected function updateCustomFields()
    {
        $db = $this->dbo;

        $locationTypes = $db->setQuery(
            $db->getQuery(true)
                ->select([
                    'id',
                    'customfields'
                ])
                ->from('#__focalpoint_locationtypes')
                ->where('customfields != ' . $db->quote(''))
        )
            ->loadObjectList();

        foreach ($locationTypes as $locationType) {
            $customFields = json_decode($locationType->customfields, true);
            if (substr_count(key($customFields), '.') == 1) {
                $locationType->customfields = [];
                foreach ($customFields as $key => $customField) {
                    list($type, $hash) = explode('.', $key);

                    $customField['type']               = $type;
                    $locationType->customfields[$hash] = $customField;
                }

                $locationType->customfields = json_encode($locationType->customfields);
                $db->updateObject('#__focalpoint_locationtypes', $locationType, ['id']);
            }
        }
    }

    /**
     * update the custom fields date in locations
     *
     * @return void
     * @since v1.4.0
     */
    protected function updateCustomFieldsData()
    {
        $db = $this->dbo;

        $query = $db->getQuery(true)
            ->select([
                'id',
                'customfieldsdata'
            ])
            ->from('#__focalpoint_locations');

        $locations = $db->setQuery($query)->loadObjectList();
        foreach ($locations as $location) {
            if ($values = json_decode($location->customfieldsdata, true)) {
                $fixedValues = [];
                foreach ($values as $fieldKey => $value) {
                    $keyParts = explode('.', $fieldKey);
                    if (count($keyParts) == 3) {
                        $fieldName = array_pop($keyParts);
                        $hash      = array_pop($keyParts);

                        $fixedValues[$hash] = [
                            $fieldName => $value
                        ];

                        $location->customfieldsdata = json_encode($fixedValues);
                        $db->updateObject('#__focalpoint_locations', $location, ['id']);
                    }
                }
            }
        }
    }

    /**
     * Updates for component parameters
     */
    protected function checkParameters()
    {
        $db = $this->dbo;

        $query = $db->getQuery(true)
            ->select([
                'extension_id',
                'params'
            ])
            ->from('#__extensions')
            ->where([
                'type = ' . $db->quote('component'),
                'element = ' . $db->quote('com_focalpoint')
            ]);

        $focalpoint = $db->setQuery($query)->loadObject();

        $params = json_decode($focalpoint->params);
        $update = clone $params;

        /**
         * Add Choosable info popup event
         * @since v1.5.0
         */
        if (!property_exists($update, 'infopopupevent')) {
            $update->infopopupevent = 'click';
        }

        if ($update != $params) {
            $focalpoint->params = json_encode($update);
            $db->updateObject('#__extensions', $focalpoint, 'extension_id');
        }
    }
}
