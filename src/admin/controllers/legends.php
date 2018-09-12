<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Legends list controller class.
 */
class FocalpointControllerLegends extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     * @since    1.6
     */
    public function getModel($name = 'legend', $prefix = 'FocalpointModel')
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }


}
