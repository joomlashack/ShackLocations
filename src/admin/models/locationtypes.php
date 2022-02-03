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

use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die();

class FocalpointModellocationtypes extends FocalpointModelList
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
                    'a.id',
                    'a.ordering',
                    'a.state',
                    'a.title',
                    'a.legend',
                    'created_by_alias',
                    'legend_title',
                    'state'
                ]
            ]
        );

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        $legend = $this->getUserStateFromRequest($this->context . '.filter.legend', 'filter_legend', '', 'string');
        $this->setState('filter.legend', $legend);

        $params = ComponentHelper::getParams('com_focalpoint');
        $this->setState('params', $params);

        parent::populateState('a.title', 'asc');
    }

    /**
     * @inheritDoc
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search')
            . ':' . $this->getState('filter.state');

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
                'b.title AS legend_title',
                'creator.name AS created_by_alias'
            ])
            ->from('`#__focalpoint_locationtypes` AS a')
            ->leftJoin('#__users AS uc ON uc.id=a.checked_out')
            ->leftJoin('#__focalpoint_legends AS b ON b.id = a.legend')
            ->leftJoin('#__users AS creator ON creator.id = a.created_by');

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
                    'a.description LIKE ' . $search
                ];
                $query->where(sprintf('(%s)', join(' OR ', $ors)));
            }
        }

        if ($filterLegend = (int)$this->state->get('filter.legend')) {
            $query->where('a.legend = ' . $filterLegend);
        }

        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        if ($ordering == 'a.ordering') {
            $query->order([
                'legend_title ' . $direction,
                'a.legend ' . $direction,
                'a.ordering ' . $direction
            ]);

        } else {
            $query->order($ordering . ' ' . $direction);
            switch ($ordering) {
                case 'legend_title':
                    $query->order('a.title ' . $direction);
                    break;

                case 'a.title':
                    $query->order('legend_title ' . $direction);
                    break;
            }
        }

        return $query;
    }
}
