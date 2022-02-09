<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2022 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

if (!defined('SLOC_LOADED')) {
    $include = JPATH_ADMINISTRATOR . '/components/com_focalpoint/include.php';
    if (is_file($include)) {
        require_once $include;
    }
}

class FocalpointModelMap extends FocalpointModelSite
{
    /**
     * @var object
     */
    protected $item = null;

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function populateState()
    {
        /** @var SiteApplication $app */
        $app = Factory::getApplication();

        $id = $app->input->getInt('id') ?: $app->getParams()->get('item_id');
        $this->setState('map.id', $id);

        $menu = $app->getMenu()->getActive();
        $this->setState('menu.id', $menu ? $menu->id : null);
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

            $id = $id ?: $this->getState('map.id');
            if ($id) {
                $table = $this->getTable();
                if ($table->load($id)) {
                    // Check published state.
                    $published = $this->getState('filter.published');
                    if (!$published || ($published == $table->state)) {
                        $this->item = new CMSObject($table->getProperties());

                        $this->item->tabsdata = json_decode($this->item->tabsdata) ?: new stdClass();
                        $this->item->metadata = new Registry($this->item->metadata);

                        // Some additional tweaking for custom tabs
                        $mapTabs = empty($this->item->tabsdata->tabs)
                            ? []
                            : (array)$this->item->tabsdata->tabs;

                        $this->item->tabsdata->tabs = $mapTabs;

                        // Load the item params merged from component config
                        $params = ComponentHelper::getParams('com_focalpoint');
                        $params->merge(new Registry($this->item->params));
                        $this->item->params = $params;

                        $this->item->markerdata = $this->getMarkerData($this->item);
                    }

                } elseif ($error = $table->getError()) {
                    $this->setError($error);
                }

            } else {
                $this->setError(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'));
            }
        }

        return $this->item;
    }

    /**
     * @inheritDoc
     */
    public function getTable($name = 'Map', $prefix = 'FocalpointTable', $options = [])
    {
        return Table::getInstance($name, $prefix, $options);
    }

    /**
     * @inheritDoc
     */
    public function getForm($data = [], $loadData = true)
    {
        // We aren't using forms in this model
        return null;
    }

    /**
     * @param object $item
     *
     * @return object[]
     * @throws Exception
     */
    protected function getMarkerData($item)
    {
        /** @var SiteApplication $app */
        $app    = Factory::getApplication();
        $params = $app->getParams('com_focalpoint');

        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select([
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
            ])
            ->from('#__focalpoint_locations AS a')
            ->innerJoin('#__focalpoint_locationtypes AS e on e.id = a.type')
            ->innerJoin('#__focalpoint_location_type_xref AS d ON d.location_id = a.id')
            ->innerJoin('#__focalpoint_locationtypes AS b ON b.id = d.locationtype_id')
            ->innerJoin('#__focalpoint_legends AS c ON  c.id = b.legend')
            ->where([
                'a.map_id = ' . (int)$item->id,
                'a.state = 1',
                'b.state = 1',
                'c.state = 1'
            ]);

        $order     = $item->params->get('locationorder', 'ordering');
        $direction = $item->params->get('locationorderdir', 'asc');
        if ($item->params->get('locationgroup')) {
            $query->order([
                "c.{$order} {$direction}",
                "b.{$order} {$direction}",
                "a.{$order} {$direction}"
            ]);

        } else {
            $query->order('a.' . $order . ' ' . $direction);
        }

        $locations = $db->setQuery($query)->loadObjectList();

        foreach ($locations as $location) {
            // Merge global params with item params so Item params take precedence
            $itemParams = new Registry($location->params);
            $itemParams->set('mapTypeId', 'TEST');
            $location->params = clone $params;
            $location->params->merge($itemParams);

            /*
             * Set the marker icon
             *
             * The rule is as follows.
             *   1.Location marker (top priority)
             *   2.Location Type marker (second priority)
             *   3.Configuration default marker (third priority).
             */
            if ($location->marker_location) {
                $location->marker = $location->marker_location;

            } elseif ($location->marker_type) {
                $location->marker = $location->marker_type;

            } else {
                $location->marker = $params->get('marker');
            }
            $location->marker = explode('#', $location->marker);
            $location->marker = array_shift($location->marker);

            $location->marker = Uri::base() . $location->marker;
            unset($location->marker_location, $location->marker_type);

            // Create link.
            $linkQuery = [
                'option' => 'com_focalpoint',
                'view'   => 'location',
                'id'     => $location->id
            ];

            $location->link = null;
            switch ($location->linktype) {
                case '0':
                    // Current Page (Own page)
                    $linkQuery['Itemid'] = $this->getState('menu.id');
                    break;

                case '1':
                    // URL
                    if ($location->altlink) {
                        $location->link = $location->altlink;
                        $linkQuery    = null;

                    } elseif ($menuId = $this->getState('menu.id')) {
                        $linkQuery['Itemid'] = $menuId;
                    }
                    break;

                case '2':
                    // Map Id
                    if ($location->maplinkid) {
                        $linkQuery['view'] = 'map';
                        $linkQuery['id']   = $location->maplinkid;
                    }
                    break;

                case '3':
                    // No Link
                    $linkQuery = null;
                    break;

                case '4':
                    // Menu Item
                    if ($location->menulink) {
                        if ($targetMenu = $app->getMenu()->getItem($location->menulink)) {
                            $linkQuery = [
                                'option' => $targetMenu->query['option'],
                                'Itemid' => $targetMenu->id
                            ];
                        }
                    }
                    break;
            }
            unset($location->altlink, $location->maplink);

            if (!$location->link && $linkQuery) {
                $location->link = 'index.php?' . http_build_query($linkQuery);
            }

            // check format of address field
            $location->address = str_replace('||', '<br>', $location->address);

            $this->formatCustomFields($location);
        }

        return $locations;
    }
}
