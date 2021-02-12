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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();

FormHelper::loadFieldClass('List');

/**
 * Loads a select list of Shack Locations.
 */
class ShacklocationsFormFieldLocation extends JFormFieldList
{
    /**
     * @inheritdoc
     */
    public $type = 'shacklocations.location';

    /**
     * @var object[]
     */
    protected static $options = null;

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        if (static::$options === null) {
            static::$options = [];

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('id, title')
                ->from('#__focalpoint_locations')
                ->order('title');
            $locations = $db->setQuery($query)->loadObjectList();

            foreach ($locations as $location) {
                static::$options[] = HTMLHelper::_('select.option', $location->id, $location->title);
            }
        }

        return array_merge(parent::getOptions(), static::$options);
    }
}
