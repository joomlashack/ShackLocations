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
        $app = JFactory::getApplication('com_focalpoint');

        $params = $app->getParams();
        $this->setState('params', $params);

        $id = $app->getUserStateFromRequest('com_focalpoint.map.id', 'id', $params->get('item_id'), 'int');
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

                    $this->item->tabs = json_decode($this->item->tabsdata);
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
     * @param array $data
     * @param bool  $loadData
     *
     * @return null
     */
    public function getForm($data = array(), $loadData = true)
    {
        return null;
    }

    /**
     * Method to get the sidebar data.
     *
     */
    public function getMarkerData($id = null)
    {

        // Load the component parameters.
        $app    = JFactory::getApplication();
        $params = $app->getParams('com_focalpoint');

        // Grab all our required location info from the database as an object
        $db = JFactory::getDbo();

        //Check Multicategorisation plugin?
        //$multicategorisation = false;
        //if ($plugin = JPluginHelper::getPlugin('focalpoint','multicategorisation')){
        //    $params->set("multicategorisation",1);
        //    $multicategorisation = true;
        //}

        $multicategorisation = true;
        $params->set("multicategorisation", 1);

        if ($multicategorisation) {
            $query = "
			SELECT c.title AS legend, c.subtitle AS legendsubtitle, c.alias AS legendalias,
			b.title AS locationtype, b.id as locationtype_id, b.alias AS locationtypealias, e.marker AS marker_type,
			a.id, a.state, a.title, a.alias, a.map_id, a.type, a.address, a.phone, a.description,
			a.customfieldsdata,
			a.latitude, a.longitude, a.marker AS marker_location, a.linktype, a.altlink, a.maplinkid, a.menulink, a.params,
			CONCAT('index.php?option=com_focalpoint&view=location&id=',a.id) AS link
			FROM #__focalpoint_locations AS a
			INNER JOIN #__focalpoint_locationtypes AS b
			INNER JOIN #__focalpoint_locationtypes AS e
			INNER JOIN #__focalpoint_legends AS c
			INNER JOIN #__focalpoint_location_type_xref AS d
			ON d.location_id = a.id
			AND d.locationtype_id = b.id
			AND e.id = a.type
			AND b.legend = c.id
			WHERE a.map_id = " . $id . " AND a.state = 1 AND b.state = 1 AND c.state = 1
			ORDER BY c.ordering, b.ordering
			";
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();

        // Let's do a little processing before passing the results back to the view
        //
        // Cycle through the results and store the relevant marker icon in $result->marker.
        // The rule is as follows.
        //   1.Location marker (top priority)
        //   2.Location Type marker (second priority)
        //   3.Configuration default marker (third priority).
        //
        // If a maplink or URL link has been defined then overwrite $result->link. Saves extra processing in the template.
        // Do some extra processing on the link at the end.

        foreach ($results as $result) {

            // Merge the item params and global params. For the maps view we only need the infobox parameters
            // but easier to merge them all anyway.
            $itemparams = new JRegistry;
            $itemparams->loadString($result->params, 'JSON');
            $result->params = $itemparams;

            // Merge global params with item params
            $newparams = clone $params;
            $newparams->merge($result->params);
            $result->params = $newparams;

            if ($result->marker_location) {
                $result->marker = JURI::base() . $result->marker_location;
            } else {
                if ($result->marker_type) {
                    $result->marker = JURI::base() . $result->marker_type;
                } else {
                    $result->marker = JURI::base() . $params->get('marker');
                }
            }

            unset($result->marker_location);
            unset($result->marker_type);
            switch ($result->linktype) {
                case "0":
                    $app  = JFactory::getApplication();
                    $menu = $app->getMenu()->getActive();
                    if ($menu) {
                        $result->link .= "&Itemid=" . $menu->id;
                    }
                    break;
                case "1":
                    if ($result->altlink) {
                        $result->link = $result->altlink;
                    } else {
                        $app          = JFactory::getApplication();
                        $menu         = $app->getMenu()->getActive();
                        $result->link .= "&Itemid=" . $menu->id;
                    }
                    break;
                case "2":
                    if ($result->maplinkid) {
                        $app   = JFactory::getApplication();
                        $db    = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select('id');
                        $query->from('#__menu');
                        $query->where('link = "index.php?option=com_focalpoint&view=map" AND params LIKE "%{\"item_id\":\"' . $result->maplinkid . '\",%"');
                        $db->setQuery($query);
                        $itemid       = $db->loadResult();
                        $result->link = 'index.php?option=com_focalpoint&view=map&id=' . $result->maplinkid . "&Itemid=";
                    }
                    break;
                case "3":
                    unset($result->link);
                    break;
                case "4":
                    if ($result->menulink) {
                        $result->link = JRoute::_(JFactory::getApplication()
                                ->getMenu()
                                ->getItem($result->menulink)->link . "&Itemid=" . $result->menulink, true);
                    }
            }

            unset($result->altlink);
            unset($result->maplink);

            //Replace || with <br> in the address. Allows the user to easily add linebreaks to the address field.
            $result->address = str_replace("||", " <br>", $result->address);

            //Route the location link.
            if (isset($result->link)) {
                if (!strstr($result->link, "http://")) {
                    $result->link = JRoute::_($result->link);
                }
            }

            // Decode the custom field data
            if (!empty($result->customfieldsdata)) {
                $data = json_decode($result->customfieldsdata);

                // Grab the location type record so we can match up the label. We don't save the labels with the data.
                // This is so we can change individaul labels at any time without having to update every record.
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('customfields')
                    ->from('#__focalpoint_locationtypes')
                    ->where('id = ' . $result->type);
                $db->setQuery($query);
                $fieldsettings        = (json_decode($db->loadResult()));
                $result->customfields = New stdClass();
                foreach ($data as $field => $value) {
                    $segments = explode(".", $field);

                    // Before adding the custom field data to the results we first need to check field settings matches
                    // the data. This is required in case the admin changes or deletes a custom field
                    // from the location type but the data still exists in the location items record.
                    if (!empty($fieldsettings->{$segments[0] . "." . $segments[1]})) {
                        $result->customfields->{end($segments)}           = New stdClass();
                        $result->customfields->{end($segments)}->datatype = $segments[0];
                        $result->customfields->{end($segments)}->label    = $fieldsettings->{$segments[0] . "." . $segments[1]}->label;
                        $result->customfields->{end($segments)}->data     = $value;
                    }
                }
            }
            unset($result->customfieldsdata);
        }
        //Send it back to the template.
        return $results;
    }
}
