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

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

class FocalpointModellocations extends JModelList
{
    public function __construct($config = [])
    {
        $config = array_merge_recursive(
            $config,
            [
                'filter_fields' => [
                    'a.ordering',
                    'a.state',
                    'a.title',
                    'map_title',
                    'locationtype_title',
                    'a.created_by',
                    'a.id',
                    'state',
                    'map_id',
                    'type'
                ]
            ]
        );


        parent::__construct($config);
    }


    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);


        //Filtering map_id
        $this->setState('filter.map_id',
            $app->getUserStateFromRequest($this->context . '.filter.map_id', 'filter_map_id', '', 'string'));

        //Filtering type
        $this->setState('filter.type',
            $app->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));


        // Load the parameters.
        $params = JComponentHelper::getParams('com_focalpoint');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.title', 'asc');
    }

    /**
     * @inheritDoc
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * @inheritDoc
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from('`#__focalpoint_locations` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the category 'map_id'
        $query->select('map_title.title AS map_title');
        $query->join('LEFT', '#__focalpoint_maps AS map_title ON map_title.id = a.map_id');

        // Join over the foreign key 'type'
        $query->select('c.title AS locationtype_title');
        $query->join('LEFT', '#__focalpoint_locationtypes AS c ON c.id = a.type');

        // Join over the user field 'created_by'
        $query->select('created_by.name AS created_by');
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Filter by published
        $published = $this->getState('filter.state');
        if ($published != '*') {
            if ($published == '') {
                $query->where('(a.state IN (0, 1))');

            } else {
                $query->where('a.state = ' . (int)$published);
            }
        }

        if ($search = $this->getState('filter.search')) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));

            } else {
                $search = $db->quote('%' . $search . '%');

                $ors = [
                    'a.title LIKE ' . $search,
                    'a.description LIKE ' . $search,
                    'a.address LIKE ' . $search
                ];
                $query->where(sprintf('(%s)', join(' OR ', $ors)));
            }
        }

        if ($filter_created_by = $this->state->get('filter.created_by')) {
            $query->where('a.created_by = ' . $db->quote($filter_created_by));
        }

        if ($filter_map_id = (int)$this->state->get('filter.map_id')) {
            $query->where('a.map_id = ' . $filter_map_id);
        }

        if ($filter_type = (int)$this->state->get('filter.type')) {
            $query->where('a.type = ' . $filter_type);
        }

        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        $query->order($ordering . ' ' . $direction);

        return $query;
    }
}
