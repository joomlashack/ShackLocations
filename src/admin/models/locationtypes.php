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

defined('_JEXEC') or die;

class FocalpointModellocationtypes extends JModelList
{
    public function __construct($config = array())
    {
        $config = array_merge_recursive(
            $config,
            array(
                'filter_fields' => array(
                    'a.id',
                    'a.ordering',
                    'a.state',
                    'a.title',
                    'a.legend',
                    'state',
                    'legend_title'
                )
            )
        );

        parent::__construct($config);
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @return void
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('administrator');

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        $legend = $app->getUserStateFromRequest($this->context . '.filter.legend', 'filter_legend', '', 'string');
        $this->setState('filter.legend', $legend);

        $params = JComponentHelper::getParams('com_focalpoint');
        $this->setState('params', $params);

        parent::populateState('a.title', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'a.*',
                    'uc.name AS editor',
                    'b.title AS legend_title',
                    'created_by.name AS created_by'
                )
            )
            ->from('`#__focalpoint_locationtypes` AS a')
            ->leftJoin('#__users AS uc ON uc.id=a.checked_out')
            ->leftJoin('#__focalpoint_legends AS b ON b.id = a.legend')
            ->leftJoin('#__users AS created_by ON created_by.id = a.created_by');

        $published = $this->getState('filter.state');
        if ($published == '') {
            $query->where('(a.state IN (0, 1))');

        } else {
            $query->where('a.state = ' . (int)$published);
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
                            array(
                                'a.title LIKE ' . $search,
                                'a.description LIKE ' . $search
                            )
                        )
                    )
                );
            }
        }

        if ($filterLegend = (int)$this->state->get("filter.legend")) {
            $query->where('a.legend = ' . $filterLegend);
        }

        $odering   = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        if ($odering && $direction) {
            $query->order($db->escape($odering . ' ' . $direction));
        }

        return $query;
    }
}
