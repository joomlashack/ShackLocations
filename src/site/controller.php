<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @author      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */
 
// No direct access
defined('_JEXEC') or die;


class FocalpointController extends JControllerLegacy
{

	/**
	 * Method to display a view.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		return parent::display($cachable = false, $urlparams = false);
	}
}