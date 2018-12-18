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

defined('_JEXEC') or die();

class FocalpointController extends JControllerLegacy
{
    protected $default_view = 'maps';

    /**
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @return JControllerLegacy
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = false)
    {
        /*
         * The first thing a user needs to do is configure options. This checks if component parameters exists
         * If not it redirects to the getting started view.
         */
        $params     = JComponentHelper::getParams('com_focalpoint');
        $paramsdata = $params->jsonSerialize();
        if (!count((array)$paramsdata)) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            setcookie("ppr", 1, time() + 604800);
        }

        $view = JFactory::getApplication()->input->getCmd('view', $this->default_view);
        JFactory::getApplication()->input->set('view', $view);

        $db = JFactory::getDbo();

        // Check we have at least one locationtype defined
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__focalpoint_locationtypes');

        $typesExist = $db->setQuery($query)->loadResult();

        if (!$typesExist
            && ($view != "maps"
                && $view != "map"
                && $view != "legends"
                && $view != "legend"
                && $view != "locationtypes"
                && $view != "locationtype"
                && $view != "getstarted")
        ) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            JFactory::getApplication()->input->set('task', 'locationtype');
        }

        // Check we have at least one legend defined
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__focalpoint_legends');

        $legendsExist = $db->setQuery($query)->loadResult();

        if (!$legendsExist
            && ($view != "maps"
                && $view != "map"
                && $view != "legends"
                && $view != "legend"
                && $view != "getstarted")
        ) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            JFactory::getApplication()->input->set('task', 'legend');
        }

        // Check we have at least one map defined
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__focalpoint_maps');

        $mapsExists = $db->setQuery($query)->loadResult();

        if (!$mapsExists && ($view != "maps" && $view != "map" && $view != "getstarted")) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            JFactory::getApplication()->input->set('task', 'map');
        }

        parent::display($cachable, $urlparams);

        return $this;
    }

    public function fixLocations()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'customfields'
                )
            )
            ->from('#__focalpoint_locationtypes')
            ->where('customfields != ' . $db->quote(''));

        $locationTypes = $db->setQuery($query)->loadObjectList();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'type',
                    'othertypes',
                    'customfieldsdata'
                )
            )
            ->from('#__focalpoint_locations')
        ->where(
            array(
                'othertypes != ' . $db->quote(''),
                'customfieldsdata != ' . $db->quote('')
            ),
            'OR'
        );

        $locations = $db->setQuery($query)->loadObjectList();

        echo '<pre>' . print_r($locationTypes, 1) . '</pre>';
        echo '<pre>' . print_r($locations, 1) . '</pre>';
    }
}
