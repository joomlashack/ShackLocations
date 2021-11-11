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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die();

class FocalpointController extends BaseController
{
    protected $default_view = 'maps';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = false)
    {
        $app = Factory::getApplication();

        /*
         * The first thing a user needs to do is configure options. This checks if component parameters exists
         * If not it redirects to the getting started view.
         */
        $params     = ComponentHelper::getParams('com_focalpoint');
        $paramsdata = $params->jsonSerialize();
        if (!count((array)$paramsdata)) {
            $app->input->set('view', 'getstarted');
            setcookie('ppr', 1, time() + 604800);
        }

        $view = $app->input->getCmd('view', $this->default_view);
        $app->input->set('view', $view);

        $db = Factory::getDbo();

        // Check we have at least one locationtype defined
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__focalpoint_locationtypes');

        $typesExist = $db->setQuery($query)->loadResult();

        if (!$typesExist
            && ($view != 'maps'
                && $view != 'map'
                && $view != 'legends'
                && $view != 'legend'
                && $view != 'locationtypes'
                && $view != 'locationtype'
                && $view != 'getstarted')
        ) {
            $app->input->set('view', 'getstarted');
            $app->input->set('task', 'locationtype');
        }

        // Check we have at least one legend defined
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__focalpoint_legends');

        $legendsExist = $db->setQuery($query)->loadResult();

        if (!$legendsExist
            && ($view != 'maps'
                && $view != 'map'
                && $view != 'legends'
                && $view != 'legend'
                && $view != 'getstarted')
        ) {
            $app->input->set('view', 'getstarted');
            $app->input->set('task', 'legend');
        }

        // Check we have at least one map defined
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__focalpoint_maps');

        $mapsExists = $db->setQuery($query)->loadResult();

        if (!$mapsExists && ($view != 'maps' && $view != 'map' && $view != 'getstarted')) {
            $app->input->set('view', 'getstarted');
            $app->input->set('task', 'map');
        }

        parent::display($cachable, $urlparams);

        return $this;
    }
}
