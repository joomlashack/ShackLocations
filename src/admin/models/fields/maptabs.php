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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

class ShacklocationsFormFieldMaptabs extends FormField
{
    /**
     * @var bool
     */
    protected static $assetsLoaded = false;

    /**
     * @var string
     */
    protected static $trashButton = null;

    /**
     * @var string
     */
    protected static $insertButton = null;

    /**
     * @var SimpleXMLElement
     */
    protected $tabGroup = null;

    /**
     * @inheritDoc
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            if ($parent = $element->xpath('parent::fieldset')) {
                /*
                 * Create a field group based on the field name
                 */
                $parent = array_pop($parent);

                $this->tabGroup         = $parent->addChild('fields');
                $this->tabGroup['name'] = $this->fieldname;

                return true;
            }
        }

        return false;
    }

    /**
     * The field is actually a group of fields that will be stored
     * as an array or object
     *
     * @inheritDoc
     * @throws Exception
     */
    public function renderField($options = [])
    {
        $this->loadAssets($options);

        $htmlOutput = ['<div class="span7 sl-subfield-wrapper">'];

        $values = (array)($this->value ?: []);
        foreach ($values as $hash => $data) {
            $htmlOutput[] = $this->getFieldBlock([
                $this->renderSubfield($hash, 'name', 'text', Text::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'), $options),
                $this->renderSubfield($hash, 'content', 'editor', '', $options)
            ]);
        }

        $appendButton = '<div>'
            . '<button class="btn btn-small button-apply btn-success sl-subfield-append">'
            . '<span class="icon-plus icon-white"></span>'
            . Text::_('COM_FOCALPOINT_FORM_FIELD_MAPTABS_NEW')
            . '</button>'
            . '</div>';

        $htmlOutput = array_merge(
            $htmlOutput,
            [
                $appendButton,
                '</div>'
            ]
        );

        return join('', $htmlOutput);
    }

    /**
     * This provides a standardized way to get a rendered field on the form that
     * will be in a hashed field group inside the main field group
     *
     * @param string $hash
     * @param string $name
     * @param string $type
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfield($hash, $name, $type, $label, $options)
    {
        $baseGroup = $this->group . '.' . $this->tabGroup['name'];
        $groupName = $baseGroup . '.' . $hash;

        $fieldGroup         = $this->tabGroup->addChild('fields');
        $fieldGroup['name'] = $hash;

        $fieldXml = sprintf('<field name="%s" type="%s" label="%s"/>', $name, $type, $label);
        $field    = new SimpleXMLElement($fieldXml);

        $this->form->setField($field, $groupName);

        return $this->form->renderField($name, $groupName, null, $options);
    }

    /**
     * This renders a complete field block with subfields under the name specified by
     * the fieldname. It includes all mover handles and add/delete buttons
     *
     * @param string|string[] $fields
     *
     * @return string
     */
    protected function getFieldBlock($fields)
    {
        $blockHtml = [
            '<fieldset class="clearfix">',
            '<legend><i class="icon-menu"></i>&nbsp;Tab</legend>',
            $this->getTrashButton(),
            $this->getInsertButton()
        ];

        foreach ((array)$fields as $fieldHtml) {
            $blockHtml[] = $fieldHtml;
        }

        $blockHtml[] = '</fieldset>';

        return join('', $blockHtml);
    }

    /**
     * @return string
     */
    protected function getTrashButton()
    {
        if (static::$trashButton === null) {
            static::$trashButton = sprintf(
                '<a %s></a>',
                ArrayHelper::toString([
                    'class' => 'hasTip sl-subfield-delete icon-cancel',
                    'title' => Text::_('COM_FOCALPOINT_FORM_FIELD_MAPTABS_DELETE')
                ])
            );
        }

        return static::$trashButton;
    }

    /**
     * @return string
     */
    protected function getInsertButton()
    {
        if (static::$insertButton === null) {
            static::$insertButton = sprintf(
                '<a %s></a>',
                ArrayHelper::toString([
                    'class' => 'hasTip sl-subfield-insert icon-plus',
                    'title' => Text::_('COM_FOCALPOINT_FORM_FIELD_MAPTABS_INSERT')
                ])
            );
        }

        return static::$insertButton;
    }

    /**
     * Load all the js/css required to make this work
     *
     * @param array $options
     *
     * @return void
     */
    protected function loadAssets($options)
    {
        if (!static::$assetsLoaded) {
            HTMLHelper::_('jquery.ui', ['core', 'sortable']);

            $dummyId = 'BLANKFIELD';

            $nameField = $this->renderSubfield(
                $dummyId,
                'name',
                'text',
                Text::_('COM_FOCALPOINT_CUSTOMFIELD_NAME'),
                $options
            );

            $fieldBlock = preg_replace(
                '/\n?\r?/',
                '',
                $this->getFieldBlock([
                    $nameField,
                    sprintf(
                        '<p class="alert"><span class="icon-info"></span>%s</p>',
                        Text::_('COM_FOCALPOINT_FORM_FIELD_MAPTABS_SAVE_CONFIG')
                    )
                ])
            );

            Factory::getDocument()->addScriptDeclaration(
                <<<JSCRIPT
;jQuery(document).ready(function($) {
    var dummyId    = /{$dummyId}/g,
        fieldBlank = '{$fieldBlock}';
    
    var deleteTab = function(evt) {
            evt.preventDefault();
            
            var fieldset = $(this).parents('fieldset').get(0);
            if (fieldset) {
                $(fieldset).remove();
            }
        };
        
    var createTab = function(evt) {
            evt.preventDefault();
            
            var fieldset = $(this).parents('fieldset').get(0)
                \$newFieldset = $(fieldBlank.replace(dummyId, createId())); 
            if (fieldset) {
                \$newFieldset.insertBefore($(fieldset));
                
            } else {
                \$newFieldset.insertBefore($(this).parent());
            }
            init();
        };
    
        var createId = function() {
            var text = "";
            var possible = "abcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 10; i++) {
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            }

            return text;
        };
       
    var init = function() {
        $('.sl-subfield-wrapper').sortable({handle : 'legend',axis:'y',opacity:'0.6', distance:'1'});


        $('.sl-subfield-delete')
            .off('click', deleteTab)
            .on('click', deleteTab);
        
        $('.sl-subfield-insert,.sl-subfield-append')
            .off('click', createTab)
            .on('click', createTab);
    };
    
    init();
});
JSCRIPT
            );

            static::$assetsLoaded = true;
        }
    }
}
