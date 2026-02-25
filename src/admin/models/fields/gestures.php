<?php

/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2024-2026 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Joomla\Form\Field\ListField;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

if ((include JPATH_ADMINISTRATOR . '/components/com_focalpoint/include.php') == false) {
    return false;
}
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class ShacklocationsFormFieldGestures extends ListField
{
    public $type = 'Gestures';

    /**
     * @var string[]
     */
    protected $predefinedOptions = [
        'auto'        => 'COM_FOCALPOINT_OPTION_AUTO',
        'none'        => 'COM_FOCALPOINT_OPTION_NONE',
        'greedy'      => 'COM_FOCALPOINT_OPTION_GREEDY',
        'cooperative' => 'COM_FOCALPOINT_OPTION_COOPERATIVE',
    ];

    /**
     * @var bool
     */
    protected static bool $loaded = false;

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
