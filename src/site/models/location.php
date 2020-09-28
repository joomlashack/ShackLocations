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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

class FocalpointModelLocation extends JModelForm
{
    /**
     * @var JObject
     */
    protected $item = null;

    /**
     * @return void
     * @throws Exception
     */
    protected function populateState()
    {
        /** @var SiteApplication $app */
        $app = Factory::getApplication('com_focalpoint');

        $locationId = $app->input->getInt('id', $app->getParams()->get('item_id'));
        $this->setState('location.id', $locationId);
    }

    /**
     * @param int $id
     *
     * @return JObject
     * @throws Exception
     */
    public function getData($id = null)
    {
        if ($this->item === null) {
            $this->item = false;

            $id = $id ?: $this->getState('location.id');

            $table = $this->getTable();
            if ($table->load($id)) {
                if ($published = $this->getState('filter.published')) {
                    if ($table->state != $published) {
                        return null;
                    }
                }

                $properties = $table->getProperties(1);
                $this->item = ArrayHelper::toObject($properties, 'JObject');

                $this->item->customfields = $this->formatCustomFields($this->item);
                $this->item->marker       = $this->getMarker($this->item);
                $this->item->address      = str_replace('||', ' <br>', $this->item->address);
                $this->item->backlink     = $this->getBackLink($this->item->map_id);
                $this->item->metadata     = new Registry($this->item->metadata);

                $params = JComponentHelper::getParams('com_focalpoint');
                $params->merge(new Registry($this->item->params));
                $this->item->params = $params;

            } elseif ($error = $table->getError()) {
                $this->setError($error);
            }
        }

        return $this->item ?: null;
    }

    /**
     * @param int $mapId
     *
     * @return int
     */
    public function getItemid($mapId)
    {
        $db = $this->getDbo();

        $link = 'index.php?option=com_focalpoint&view=map';

        $query = $db->getQuery(true)
            ->select([
                'id',
                'params'
            ])
            ->from('#__menu')
            ->where([
                'link = ' . $db->quote($link),
                'published=1'
            ]);

        $menus = $db->setQuery($query)->loadObjectList();
        foreach ($menus as $menu) {
            $menuParams = new JRegistry($menu->params);
            if ($menuParams->get('item_id') == $mapId) {
                return $menu->id;
            }
        }

        return null;
    }

    /**
     * @param int $mapId
     *
     * @return string|null
     */
    public function getBackLink($mapId)
    {
        if ($itemid = $this->getItemid($mapId)) {
            return 'index.php?option=com_focalpoint&view=map&Itemid=' . $itemid;
        }

        return null;
    }

    /**
     * @param JObject $location
     *
     * @return object
     */
    protected function formatCustomFields(JObject $location)
    {
        $customFieldsData = json_decode($location->get('customfieldsdata'), true);

        $type = (int)$location->get('type');

        if (!$customFieldsData || !$type) {
            return null;
        }

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

        return (object)$customFields;
    }

    /**
     * @param CMSObject $location
     *
     * @return string
     * @throws Exception
     */
    protected function getMarker(CMSObject $location)
    {
        $marker = $location->get('marker');

        if (!$marker) {
            if ($locationId = (int)$location->get('id')) {
                $db    = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select('a.marker')
                    ->from('#__focalpoint_locationtypes AS a')
                    ->leftJoin('#__focalpoint_locations AS b ON b.type = a.id')
                    ->where('b.id = ' . $locationId);

                $marker = $db->setQuery($query)->loadResult();
            }
        }

        if (!$marker) {
            /*
             * Fallback onto the component parameters. The parameters have already been merged in the view.
             * If a marker has been set in the map settings or global option it will defined in $params.
             */

            /** @var SiteApplication $app */
            $app = Factory::getApplication();

            $params = $app->getParams('com_focalpoint');
            $marker = $params->get('marker');
        }

        if ($marker) {
            $marker = HTMLHelper::_('image', $marker, null, null, false, true);
        }

        return $marker;
    }

    public function getTable($type = 'Location', $prefix = 'FocalpointTable', $config = [])
    {
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        return null;
    }
}
