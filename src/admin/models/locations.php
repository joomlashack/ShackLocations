<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2024 Joomlashack. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class FocalpointModellocations extends FocalpointModelList
{
    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $config = array_merge_recursive(
            $config,
            [
                'filter_fields' => [
                    'creator.name',
                    'a.id',
                    'a.ordering',
                    'a.state',
                    'a.title',
                    'map.title',
                    'legend.title',
                    'type.title',
                    'state',
                    'map_id',
                    'legend',
                    'type',
                ],
            ]
        );


        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = 'a.title', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);


        $mapId = $this->getUserStateFromRequest($this->context . '.filter.map_id', 'filter_map_id', '', 'string');
        $this->setState('filter.map_id', $mapId);

        $legendId = $this->getUserStateFromRequest($this->context . '.filter.legend', 'filter_legend', '', 'int');
        $this->setState('filter.legend', $legendId);

        $typeId = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string');
        $this->setState('filter.type', $typeId);

        parent::populateState($ordering, $direction);
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
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select([
                'a.*',
                'uc.name AS editor',
                'map.title AS map_title',
                'type.title AS locationtype_title',
                'legend.title AS legend_title',
                'creator.name AS created_by_alias',
            ])
            ->from('`#__focalpoint_locations` AS a')
            ->leftJoin('#__users AS uc ON uc.id=a.checked_out')
            ->leftJoin('#__focalpoint_maps AS map ON map.id = a.map_id')
            ->leftJoin('#__focalpoint_locationtypes AS type ON type.id = a.type')
            ->leftJoin('#__focalpoint_legends AS legend ON legend.id = type.legend')
            ->leftJoin('#__users AS creator ON creator.id = a.created_by');

        $published = $this->getState('filter.state');
        if ($published != '*') {
            if ($published == '') {
                $query->where('a.state IN (0, 1)');

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
                    'a.address LIKE ' . $search,
                ];
                $query->where(sprintf('(%s)', join(' OR ', $ors)));
            }
        }

        if ($createdBy = (int)$this->state->get('filter.created_by')) {
            $query->where('a.created_by = ' . $createdBy);
        }

        if ($mapId = (int)$this->state->get('filter.map_id')) {
            $query->where('a.map_id = ' . $mapId);
        }

        if ($legendId = (int)$this->getState('filter.legend')) {
            $query->where('type.legend = ' . $legendId);
        }

        if ($typeId = (int)$this->state->get('filter.type')) {
            $query->where('a.type = ' . $typeId);
        }

        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');

        if ($ordering == 'a.ordering') {
            $query->order([
                'map.title ' . $direction,
                'a.map_id ' . $direction,
                'a.ordering ' . $direction,
            ]);

        } else {
            $query->order($ordering . ' ' . $direction);
            switch ($ordering) {
                case 'a.state':
                case 'map.title':
                case 'type.title':
                case 'creator.name':
                    $query->order('a.title ' . $direction);
                    break;

                case 'legend.title':
                    $query->order([
                        'type.title ' . $direction,
                        'a.title ' . $direction,
                    ]);
                    break;
            }
        }

        return $query;
    }
}
