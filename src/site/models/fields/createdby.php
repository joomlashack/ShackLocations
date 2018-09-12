<?php
/**
 * @package     ShackLocations
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 */
class JFormFieldCreatedby extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'createdby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();


		//Load user
		$user_id = $this->value;
		if ($user_id) {
			$user = JFactory::getUser($user_id);
		} else {
			$user = JFactory::getUser();
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$user->id.'" />';
		}
		$html[] = "<div>".$user->name." (".$user->username.")</div>";

		return implode($html);
	}
}
