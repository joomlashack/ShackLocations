<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2024 Joomlashack.com. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;

defined('_JEXEC') or die();

if (class_exists(listField::class) == false) {
    // Joomla 3 support
    FormHelper::loadFieldClass('predefinedlist');
    class_alias('\\JFormFieldlist', ListField::class);
}
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class ShacklocationsFormFieldGestures extends ListField
{
    public $type = 'Gestures';

    /**
     * @inheritdoc
     */
    protected $predefinedOptions = [
        'auto'        => 'COM_FOCALPOINT_OPTION_AUTO',
        'none'        => 'COM_FOCALPOINT_OPTION_NONE',
        'greedy'      => 'COM_FOCALPOINT_OPTION_GREEDY',
        'cooperative' => 'COM_FOCALPOINT_OPTION_COOPERATIVE',
    ];

    protected static $loaded = false;

    /**
     * @inheritDoc
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            if (static::$loaded == false) {
                foreach ($this->predefinedOptions as $value => $text) {
                    $option = $this->element->addChild('option', $text);
                    $option->addAttribute('value', $value);
                }

                static::$loaded = true;
            }

            return true;
        }

        return false;
    }
}
