<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019-2024 Joomlashack. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with ShackLocations.  If not, see <http://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class FocalpointViewSite extends FocalpointView
{
    /**
     * @var string
     */
    protected $mapEngine = 'google';

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $lang = Factory::getLanguage();
        $lang->load('com_focalpoint', JPATH_ADMINISTRATOR . '/components/com_focalpoint');
    }

    /**
     * Render the modules in a position
     *
     * @param string $position
     * @param mixed  $attribs
     *
     * @return string
     */
    public static function renderModule(string $position, $attribs = []): string
    {
        $results = ModuleHelper::getModules($position);
        $content = '';

        ob_start();
        foreach ($results as $result) {
            $content .= ModuleHelper::renderModule($result, $attribs);
        }
        ob_end_clean();

        return $content;
    }

    /**
     * Renders a custom field using the relevant template.
     *
     * @param object $field
     * @param ?bool  $hidelabel
     *
     * @return string
     * @throws Exception
     *
     */
    protected function renderField(object $field, ?bool $hidelabel = false): string
    {
        if (empty($field->datatype) == false) {
            $data = array_merge(
                get_object_vars($field),
                [
                    'showlabel' => !$hidelabel,
                ]
            );

            return LayoutHelper::render('custom.field.' . $field->datatype, $data);
        }

        return '';
    }

    /**
     * @param string  $string
     * @param ?object $customFields
     *
     * @return string
     */
    protected function replaceFieldTokens(string $string, ?object $customFields): string
    {
        if ($customFields) {
            preg_match_all('/{(.*?)}/i', $string, $matches, PREG_SET_ORDER);

            if ($matches) {
                foreach ($matches as $match) {
                    foreach ($customFields as $name => $customField) {
                        if ($name == $match[1]) {
                            $output = LayoutHelper::render(
                                'custom.field.' . $customField->datatype,
                                [
                                    'label' => $customField->label,
                                    'data'  => $customField->data,
                                ]
                            );

                            $string = str_replace($match[0], $output, $string);
                        }
                    }
                }
            }
        }

        return $string;
    }
}
