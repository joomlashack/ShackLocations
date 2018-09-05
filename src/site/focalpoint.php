<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @author      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */

defined('_JEXEC') or die;

// Execute the task.
$controller	= JControllerLegacy::getInstance('Focalpoint');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
