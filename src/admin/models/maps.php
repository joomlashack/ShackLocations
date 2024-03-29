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

class FocalpointModelmaps extends FocalpointModelList
{
    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $config = array_merge_recursive(
            [
                'filter_fields' => [
                    'created_by_alias',
                    'a.id',
                    'a.ordering',
                    'a.state',
                    'a.title',
                    'state',
                ],
            ],
            $config
        );

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);

        parent::populateState($ordering, $direction);
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

        // Select the required fields from the table.
        $query = $db->getQuery(true)
            ->select([
                'a.*',
                'uc.name AS editor',
                'creator.name AS created_by_alias',
            ])
            ->from('#__focalpoint_maps AS a')
            ->leftJoin('#__users AS uc ON uc.id = a.checked_out')
            ->leftJoin('#__users AS creator ON creator.id = a.created_by');

        // Filter by published
        $published = $this->getState('filter.state');
        if ($published != '*') {
            if ($published == '') {
                $query->where('(a.state IN (0, 1))');

            } else {
                $query->where('a.state = ' . (int)$published);
            }
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));

            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where(
                    sprintf(
                        '(%s)',
                        join(
                            ' OR ',
                            [
                                'a.title LIKE ' . $search,
                                'a.text LIKE ' . $search,
                            ]
                        )
                    )
                );
            }
        }

        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        $query->order($ordering . ' ' . $direction);

        return $query;
    }
}
