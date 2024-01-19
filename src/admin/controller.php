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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class FocalpointController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected $default_view = 'maps';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = false)
    {
        $app  = Factory::getApplication();
        $view = $app->input->getCmd('view', $this->default_view);

        $params = ComponentHelper::getParams('com_focalpoint');
        if (empty($params->get('apikey'))) {
            if ($view != 'getstarted') {
                $app->input->set('view', 'getstarted');
            }

        } else {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)->select('COUNT(*)');

            $maps          = $db->setQuery((clone $query)->from('#__focalpoint_maps'))->loadResult();
            $legends       = $db->setQuery((clone $query)->from('#__focalpoint_legends'))->loadResult();
            $locationTypes = $db->setQuery((clone $query)->from('#__focalpoint_locationtypes'))->loadResult();
            $locations     = $db->setQuery((clone $query)->from('#__focalpoint_locations'))->loadResult();

            if (array_sum([$maps, $legends, $locationTypes, $locations]) == 0) {
                if (in_array($view, ['maps', 'map']) == false) {
                    $this->setRedirect('index.php?option=com_focalpoint&view=maps');
                }

            } elseif (array_sum([$legends, $locationTypes, $locations]) == 0) {
                if (in_array($view, ['maps', 'map', 'legends', 'legend']) == false) {
                    $this->setRedirect('index.php?option=com_focalpoint&view=legends');
                }

            } elseif (array_sum([$locationTypes, $locations]) == 0) {
                if (in_array($view, ['maps', 'map', 'legends', 'legend', 'locationtypes', 'locationtype']) == false) {
                    $this->setRedirect('index.php?option=com_focalpoint&view=locationtypes');
                }
            }
        }

        return parent::display($cachable, $urlparams);
    }
}
