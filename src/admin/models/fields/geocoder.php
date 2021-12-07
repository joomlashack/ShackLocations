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

use Alledia\Framework\Joomla\Form\Field\TraitLayouts;
use Joomla\CMS\Form\FormField;

defined('_JEXEC') or die();

include JPATH_ADMINISTRATOR . '/components/com_focalpoint/include.php';

class ShacklocationsFormFieldGeocoder extends FormField
{
    use TraitLayouts;

    /**
     * @inheritdoc
     */
    protected $layout = 'shacklocations.geocoder';

    /**
     * @inheritDoc
     */
    protected function getlabel()
    {
        return '';
    }
}
