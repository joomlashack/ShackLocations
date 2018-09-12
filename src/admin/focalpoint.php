<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */


defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_focalpoint')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// Register the Helper class
JLoader::register('FocalpointHelper', __DIR__ . '/helpers/focalpoint.php');

// Register the mapsAPI class to handle geocoding and map functions.
JLoader::register('mapsAPI', __DIR__ . '/helpers/maps.php');

$controller = JControllerLegacy::getInstance('Focalpoint');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
