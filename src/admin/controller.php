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

class FocalpointController extends JControllerLegacy
{
    /**
     * @var        string    The default view.
     * @since   1.6
     */
    protected $default_view = 'maps';

    /**
     * Method to display a view.
     *
     * @param    boolean $cachable If true, the view output will be cached
     * @param    array $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return    JController        This object to support chaining.
     * @since    1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        // The first thing a user needs to do is configure options. This checks if component parameters exists
        // If not it redirects to the getting started view.
        $params = JComponentHelper::getParams('com_focalpoint');
        $paramsdata = $params->jsonSerialize();
        if (!count((array)$paramsdata)) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            setcookie("ppr",1,time()+604800);
        }

        $view = JFactory::getApplication()->input->getCmd('view', $this->default_view);
        JFactory::getApplication()->input->set('view', $view);

        $db = JFactory::getDbo();

        // Check we have at least one locationtype defined
        $query = $db->getQuery(true);
        $query->select('id')->from('#__focalpoint_locationtypes');
        $db->setQuery($query);
        $types_exist = $db->loadResult();

        if (!$types_exist && ($view != "maps" && $view != "map" && $view != "legends" && $view != "legend" && $view != "locationtypes" && $view != "locationtype" && $view != "getstarted")) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            JFactory::getApplication()->input->set('task', 'locationtype');
        }

        // Check we have at least one legend defined
        $query = $db->getQuery(true);
        $query->select('id')->from('#__focalpoint_legends');
        $db->setQuery($query);
        $legends_exist = $db->loadResult();

        if (!$legends_exist && ($view != "maps" && $view != "map" && $view != "legends" && $view != "legend" && $view != "getstarted")) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            JFactory::getApplication()->input->set('task', 'legend');
        }

        // Check we have at least one map defined
        $query = $db->getQuery(true);
        $query->select('id')->from('#__focalpoint_maps');
        $db->setQuery($query);
        $maps_exist = $db->loadResult();

        if (!$maps_exist && ($view != "maps" && $view != "map" && $view != "getstarted")) {
            JFactory::getApplication()->input->set('view', 'getstarted');
            JFactory::getApplication()->input->set('task', 'map');
        }

        parent::display($cachable, $urlparams);

        return $this;
    }
}
