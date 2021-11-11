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

use Alledia\Framework\Joomla\Form\Field\GroupedListField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

class ShacklocationsFormFieldLocationtype extends GroupedListField
{
    /**
     * @inheritdoc
     */
    public $type = 'locationtype';

    /**
     * @var object[]
     */
    protected static $typeOptions = null;

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        if ($this->multiple) {
            if (is_string($this->value)) {
                $this->value = array_filter(array_map('intval', explode('|', $this->value)));
            }

            $primaryName = (string)$this->element['primary'];
            if ($primary = $this->form->getField($primaryName)) {
                HTMLHelper::_('jquery.framework');
                $js = <<<JSCODE
(function($) {
    $(document).ready(function() {
        $('#{$primary->id}')
            .on('change', function() {
                var primary = this.value,
                    secondary = $('#{$this->id}');
                    
                secondary.find('option').each(function (idx, option) {
                    if (option.value == primary) {
                        $(option).prop('disabled', true).prop('selected', false);
                    } else {
                        $(option).prop('disabled', false);
                    }
                });
                secondary.trigger('liszt:updated');
            })
            .trigger('change');
    });
})(jQuery);
JSCODE;
                Factory::getDocument()->addScriptDeclaration($js);
            }
        }

        return parent::getInput();
    }

    /**
     * @inheritDoc
     */
    protected function getGroups()
    {
        if (static::$typeOptions === null) {
            $db = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select([
                    'a.id',
                    'a.title',
                    'b.title AS legend'
                ])
                ->from('#__focalpoint_locationtypes AS a')
                ->innerJoin('#__focalpoint_legends AS b on a.legend = b.id')
                ->where('a.state > -1')
                ->order([
                    'b.ordering ASC',
                    'a.ordering ASC'
                ]);

            $types      = $db->setQuery($query)->loadObjectList();
            $lastLegend = null;

            static::$typeOptions = [];
            foreach ($types as $type) {
                if ($type->legend !== $lastLegend) {
                    static::$typeOptions[$type->legend] = [];
                }
                static::$typeOptions[$type->legend][] = HTMLHelper::_('select.option', $type->id, $type->title);

                $lastLegend = $type->legend;
            }
        }

        return array_merge(parent::getGroups(), static::$typeOptions);
    }
}
