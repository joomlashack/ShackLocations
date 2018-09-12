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

        // Please do not modify the following code unless you absolutely know what you are doing.
        // Changes to the following usually results in unrecoverable system crashes, itching and nasty warts.
        $query=$db->getQuery(true);
        $query->select("*")->from("#__focalpoint_locations");
        $db->setQuery($query);
        $locations_exist = $db->loadResult();

        if (!empty($locations_exist) && "s"== substr(JFactory::getApplication()->input->getCmd('view'), -1)){
            $query = $db->getQuery(true)->select('extension_id')->from('#__extensions')->where('element = "mod_fplocation" or (type= "plugin" AND name LIKE "%FocalPoint%")');
            $db->setQuery($query);
            $s = $db->loadResult();
            $t = isset($_COOKIE['ppr']);

            if (!$s&&!$t){
                echo '<div class="row-fluid"><div class="span2"></div><div class="span10"><div id="ppr" class="alert" style="margin-top:50px; clear:both"><button type="button" class="close" data-dismiss="alert"><small>[HIDE]</small></button><p>You can extend the functionality of your maps with professional plugins.</p><ul><li>Give your visitors a fullscreen button using the HTML5 Fullscreen API.</li><li>Custom map styles.</li><li>Automatic panning/zooming.</li><li>Marker Clustering for overlapping markers.</li><li>A Joomla search plugin.</li><li>Location module.</li></ul><p>and more are available at <a target="_blank" href="http://focalpointx.com/download/pro-downloads.html?ppr=1">http://focalpointx.com/download/pro-downloads.html.</a></p></div></div><div>';
                $script = '<script>jQuery("#ppr .close").click(function(){sc("ppr","1",14);});
                function sc(cname, cvalue, exdays) {var d = new Date();d.setTime(d.getTime() + (exdays*24*60*60*1000));var expires = "expires="+d.toUTCString();document.cookie = cname + "=" + cvalue + "; " + expires;}
                </script>';
                echo $script;

            }

        }
        return $this;
    }
}
