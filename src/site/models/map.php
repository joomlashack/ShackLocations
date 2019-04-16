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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class FocalpointModelMap extends JModelForm
{
    /**
     * @var object
     */
    protected $item = null;

    /**
     * @throws Exception
     */
    protected function populateState()
    {
        /** @var SiteApplication $app */
        $app = JFactory::getApplication();

        $id = $app->input->getInt('id') ?: $app->getParams()->get('item_id');

        $this->setState('map.id', $id);
    }

    /**
     * @param int $id
     *
     * @return object
     * @throws Exception
     */
    public function getData($id = null)
    {
        if ($this->item === null) {
            $this->item = false;

            $id    = $id ?: $this->getState('map.id');
            $table = $this->getTable();
            if ($table->load($id)) {
                // Check published state.
                $published = $this->getState('filter.published');
                if (!$published || ($published == $table->state)) {
                    $this->item = new JObject($table->getProperties());

                    $this->item->tabsdata = json_decode($this->item->tabsdata) ?: new stdClass();
                    $this->item->metadata = new Registry($this->item->metadata);

                    // Some additional tweaking for custom tabs
                    $mapTabs = empty($this->item->tabsdata->tabs)
                        ? array()
                        : (array)$this->item->tabsdata->tabs;

                    $this->item->tabsdata->tabs = $mapTabs;

                    // Load the item params merged from component config
                    $params = JComponentHelper::getParams('com_focalpoint');
                    $params->merge(new Registry($this->item->params));
                    $this->item->params = $params;
                }

            } elseif ($error = $table->getError()) {
                $this->setError($error);
            }
        }

        return $this->item;
    }

    /**
     * @param string $type
     * @param string $prefix
     * @param array  $config
     *
     * @return Table
     */
    public function getTable($type = 'Map', $prefix = 'FocalpointTable', $config = array())
    {
        $this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_focalpoint/tables');

        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * We're using the form model but don't have any forms to load on the front end
     *
     * @param array $customFieldsData
     * @param bool  $loadData
     *
     * @return null
     */
    public function getForm($customFieldsData = array(), $loadData = true)
    {
        return null;
    }

    /**
     * @param int $id
     *
     * @return object[]
     * @throws Exception
     */
    public function getMarkerData($id = null)
    {
        /** @var SiteApplication $app */
        $app    = JFactory::getApplication();
        $params = $app->getParams('com_focalpoint');

        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'c.title AS legend',
                    'c.subtitle AS legendsubtitle',
                    'c.alias AS legendalias',
                    'b.title AS locationtype',
                    'b.id AS locationtype_id',
                    'b.alias AS locationtypealias',
                    'e.marker AS marker_type',
                    'a.id',
                    'a.state',
                    'a.title',
                    'a.alias',
                    'a.map_id',
                    'a.type',
                    'a.address',
                    'a.phone',
                    'a.description',
                    'a.customfieldsdata',
                    'a.latitude',
                    'a.longitude',
                    'a.marker AS marker_location',
                    'a.linktype',
                    'a.altlink',
                    'a.maplinkid',
                    'a.menulink',
                    'a.params'
                )
            )
            ->from('#__focalpoint_locations AS a')
            ->innerJoin('#__focalpoint_locationtypes AS e on e.id = a.type')
            ->innerJoin('#__focalpoint_location_type_xref AS d ON d.location_id = a.id')
            ->innerJoin('#__focalpoint_locationtypes AS b ON b.id = d.locationtype_id')
            ->innerJoin('#__focalpoint_legends AS c ON  c.id = b.legend')
            ->where(
                array(
                    'a.map_id = ' . (int)$id,
                    'a.state = 1',
                    'b.state = 1',
                    'c.state = 1'
                )
            )
            ->order(
                array(
                    'c.ordering ASC',
                    'b.ordering ASC',
                    'a.ordering ASC'
                )
            );

        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as $result) {
            // Merge global params with item params so Item params take precedence
            $itemParams = new Registry($result->params);
            $itemParams->set('mapTypeId', 'TEST');
            $result->params = clone $params;
            $result->params->merge($itemParams);

            /*
             * Set the marker icon
             *
             * The rule is as follows.
             *   1.Location marker (top priority)
             *   2.Location Type marker (second priority)
             *   3.Configuration default marker (third priority).
             */
            if ($result->marker_location) {
                $result->marker = $result->marker_location;

            } elseif ($result->marker_type) {
                $result->marker = $result->marker_type;

            } else {
                $result->marker = $params->get('marker');
            }
            $result->marker = JUri::base() . $result->marker;
            unset($result->marker_location, $result->marker_type);

            /*
             * Create $result->link.
             */
            $linkQuery = array(
                'option' => 'com_focalpoint',
                'view'   => 'location',
                'id'     => $result->id
            );

            $result->link = null;
            switch ($result->linktype) {
                case '0':
                    // Current Page (Own page)
                    if ($menu = $app->getMenu()->getActive()) {
                        $linkQuery['Itemid'] = $menu->id;
                    }
                    break;

                case '1':
                    // URL
                    if ($result->altlink) {
                        $result->link = $result->altlink;
                        $linkQuery    = null;

                    } elseif ($menu = $app->getMenu()->getActive()) {
                        $linkQuery['Itemid'] = $menu->id;
                    }
                    break;

                case '2':
                    // Map Id
                    if ($result->maplinkid) {
                        $linkQuery['view'] = 'map';
                        $linkQuery['id']   = $result->maplinkid;
                    }
                    break;

                case '3':
                    // No Link
                    $linkQuery = null;
                    break;

                case '4':
                    // Menu Item
                    if ($result->menulink) {
                        if ($targetMenu = $app->getMenu()->getItem($result->menulink)) {
                            $linkQuery = array(
                                'option' => $targetMenu->query['option'],
                                'Itemid' => $targetMenu->id
                            );
                        }
                    }
                    break;


            }
            unset($result->altlink, $result->maplink);

            if (!$result->link && $linkQuery) {
                $result->link = 'index.php?' . http_build_query($linkQuery);
            }
            if ($result->link && !preg_match('#https?://#', $result->link)) {
                $result->link = JRoute::_($result->link);
            }

            // check format of address field
            $result->address = str_replace("||", " <br>", $result->address);

            // Decode the custom field data
            if (!empty($result->customfieldsdata)) {
                $customFieldsData = json_decode($result->customfieldsdata);

                /*
                 * Grab the location type record so we can match up the label. We don't save the labels with the data.
                 * This is so we can change individaul labels at any time without having to update every record.
                 *
                 */
                $db = $this->getDbo();

                $query = $db->getQuery(true)
                    ->select('customfields')
                    ->from('#__focalpoint_locationtypes')
                    ->where('id = ' . $result->type);

                $fieldSettings = (json_decode($db->setQuery($query)->loadResult()));

                $result->customfields = new stdClass();
                foreach ($customFieldsData as $field => $value) {
                    $segments = explode(".", $field);

                    $dataType = $segments[0];
                    $fieldKey = join('.', $segments);

                    // Before adding the custom field data to the results we first need to check field settings matches
                    // the data. This is required in case the admin changes or deletes a custom field
                    // from the location type but the data still exists in the location items record.
                    if (!empty($fieldSettings->{$fieldKey})) {
                        $field = $fieldSettings->{$fieldKey};

                        $result->customfields->{$field->name} = (object)array(
                            'datatype' => $dataType,
                            'label'    => $field->label,
                            'data'     => $value
                        );
                    }
                }
            }
            unset($result->customfieldsdata);
        }

        return $results;
    }
}
