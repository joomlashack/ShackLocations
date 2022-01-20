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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;

defined('_JEXEC') or die();

FormHelper::loadFieldClass('GroupedList');

class ShacklocationsFormFieldLocationtype extends JFormFieldGroupedList
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
            if (
                ($primaryField = $this->form->getField($primaryName))
                && $primaryField instanceof self
            ) {
                $this->loadJs($primaryField);
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

    /**
     * @param self $primaryField
     *
     * @return void
     */
    protected function loadJs(self $primaryField)
    {
        HTMLHelper::_('jquery.framework');

        if (Version::MAJOR_VERSION < 4) {
            $js = <<<JSCODE
jQuery(document).ready(function($) {
    let \$primary = $('#{$primaryField->id}'),
        \$secondary = $('#{$this->id}');
        
        \$primary.on('change', function() {
            let primaryValue = this.value;

            \$secondary.find('option').each(function (idx, option) {
                if (option.value == primaryValue) {
                    $(option).prop('disabled', true).prop('selected', false);
                } else {
                    $(option).prop('disabled', false);
                }
            });
            \$secondary.trigger('liszt:updated');
        })
        .trigger('change');
});
JSCODE;

        } else {
            $js = <<<JSCODE
document.addEventListener('DOMContentLoaded', function() {
    let primaryField        = document.getElementById('{$primaryField->id}'),
        secondaryField      = document.getElementById('{$this->id}'),
        secondary           = secondaryField.closest('joomla-field-fancy-select') || null;

    if (primaryField && secondary) {
        let choices       = secondary.choicesInstance,
            dropdownClass = choices.config.classNames.listDropdown,
            groupClass    = choices.config.classNames.group,
            itemClass     = choices.config.classNames.itemChoice,
            listClass     = choices.config.classNames.list,
            choiceList    = secondary.getElementsByClassName(dropdownClass).item(0), 
            options       = [];

        if (choiceList) {
            if (choiceList = choiceList.getElementsByClassName(listClass).item(0)) {
                choiceList = choiceList.childNodes
                
                let group = null;
                for (let i = 0; i < choiceList.length; i++) {
                    let option = choiceList[i];
                    
                    if (option.classList.contains(groupClass)) {
                        if (group) {
                            options.push(group);
                        }
        
                        group = {
                        label: option.dataset.value,
                        id: option.dataset.id,
                        disabled: false,
                        choices: []
                    }
        
                    } else {
                        if (group) {
                            group.choices.push(
                                {
                                    value: option.dataset.value,
                                    label: option.innerHTML
                                 }
                            );
                        }
                    }
                }
                if (group) {
                    options.push(group);
                }
            } 
        }

        primaryField.addEventListener('change', function() {
            choices.passedElement.triggerEvent('change');
        });
        
        secondaryField.addEventListener('change', function() {
            let primaryValue = primaryField.value;

            options.forEach(function(option) {
                option.choices.forEach(function(choice) {
                    choice.disabled = primaryValue === choice.value
                });
            });
            
            choices.setChoices(options, 'value', 'label', true);
            choices.removeActiveItemsByValue(primaryValue);
        });
        
        secondaryField.dispatchEvent(new Event('change'));
    }
});
JSCODE;
        }

        Factory::getDocument()->addScriptDeclaration($js);
    }
}
