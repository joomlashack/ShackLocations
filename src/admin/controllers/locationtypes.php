<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Locationtypes list controller class.
 */
class FocalpointControllerLocationtypes extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     * @since    1.6
     */
    public function getModel($name = 'locationtype', $prefix = 'FocalpointModel')
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }


    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        // Get the input
        $input = JFactory::getApplication()->input;
        $pks = $input->post->get('cid', array(), 'array');
        $order = $input->post->get('order', array(), 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        JFactory::getApplication()->close();
    }


}
