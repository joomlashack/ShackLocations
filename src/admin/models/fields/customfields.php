<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018 Open Source Training, LLC. All rights reserved
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

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

class ShacklocationsFormFieldCustomfields extends JFormField
{
    /**
     * @var bool
     */
    protected static $assetsLoaded = false;

    protected $fieldTypes = array(
        'textbox',
        'link',
        'email',
        'textarea',
        'image',
        'selectlist',
        'multiselect'
    );

    /**
     * @var string
     */
    protected static $trashButton = null;

    /**
     * @var SimpleXMLElement
     */
    protected $fieldGroup = null;

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            if ($parent = $element->xpath('parent::fieldset')) {
                /*
                 * Create a field group based on the field name
                 */
                $parent = array_pop($parent);

                $this->fieldGroup         = $parent->addChild('fields');
                $this->fieldGroup['name'] = $this->fieldname;

                return true;
            }
        }

        return false;
    }

    /**
     * The field is actually a group of fields that will be stored
     * as an array or object
     *
     * @param array $options
     *
     * @return string
     * @throws Exception
     */
    public function renderField($options = array())
    {
        $this->loadAssets($options);

        $htmlOutput = array(
            sprintf('<input type="hidden" name="%s"/>', $this->name),
            '<div class="span7 sl-subfield-wrapper">',
        );

        if ($this->value) {
            foreach ($this->value as $hash => $data) {
                $htmlOutput[] = $this->getFieldBlock($hash, $data, $options);
            }
        }

        $htmlOutput[] = $this->createNewButtons();
        $htmlOutput[] = '</div>';

        return join('', $htmlOutput);
    }

    /**
     * @return string
     */
    protected function createNewButtons()
    {
        $newButtons = array('<ul class="inline">');

        foreach ($this->fieldTypes as $fieldType) {
            $newButtons[] = '<li>'
                . sprintf(
                    '<button class="btn btn-small button-apply btn-success pull-left sl-customfield-new" data-type="%s">',
                    $fieldType
                )
                . '<span class="icon-plus icon-white"></span>'
                . JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_' . $fieldType)
                . '</button>'
                . '</li>';
        }

        $newButtons[] = '</ul>';

        return join("\n", $newButtons);
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
        $baseGroup = $this->fieldGroup['name'];
        $groupName = $baseGroup . '.' . $hash;

        $fieldGroup         = $this->fieldGroup->addChild('fields');
        $fieldGroup['name'] = $hash;

        $attributes = array(
            'name'  => $name,
            'type'  => $type,
            'label' => $label
        );

        if (!empty($options['attributes'])) {
            $attributes = array_merge($attributes, $options['attributes']);
        }

        if (empty($options['options'])) {
            $fieldXml = sprintf('<field %s/>', ArrayHelper::toString($attributes));

        } else {
            $fieldXml = sprintf('<field %s>%s</field>', ArrayHelper::toString($attributes), $options['options']);
        }

        $field = new SimpleXMLElement($fieldXml);

        $this->form->setField($field, $groupName);

        return $this->form->renderField($name, $groupName, null, $options);
    }

    /**
     * This renders a complete field block with subfields under the name specified by
     * the fieldname. It includes all mover handles and add/delete buttons
     *
     * @param string   $hash
     * @param string[] $data
     * @param array    $options
     *
     * @return string
     */
    protected function getFieldBlock($hash, $data, $options)
    {
        $type = empty($data['type']) ? null : $data['type'];
        if ($type) {
            $blockHeader = JText::_('COM_FOCALPOINT_CUSTOMFIELD_TYPE_' . $data['type']);

            $blockHtml = array_merge(
                array(
                    '<fieldset class="clearfix">',
                    sprintf('<legend><i class="icon-menu"></i>&nbsp;%s</legend>', $blockHeader),
                    $this->getTrashButton()
                ),
                $this->getSubfields($hash, $data, $options),
                array('</fieldset>')
            );

        } else {
            $blockHtml = array(
                '<fieldset class="clarfix">',
                sprintf(
                    '<legend><i class="icon-ban-circle"></i>&nbsp;%s</legend>',
                    JText::sprintf('COM_FOCALPOINT_CUSTOMFIELD_TYPE_UNKNOWN', $type)
                ),
                '</fieldset>'
            );
        }

        return join('', $blockHtml);
    }

    /**
     * @param string   $hash
     * @param string[] $data
     * @param array    $options
     *
     * @return array
     */
    protected function getSubfields($hash, $data, $options)
    {
        $type = empty($data['type']) ? null : $data['type'];
        if (!$type) {
            return array('Bad field ' . print_r($data, 1));
        }

        $hiddenOptions = array(
            'attributes' => array(
                'default' => $type
            )
        );

        $renderedFields = array(
            $this->renderSubfield($hash, 'type', 'hidden', '', array_merge($options, $hiddenOptions)),
            $this->renderSubfield($hash, 'name', 'text', 'COM_FOCALPOINT_CUSTOMFIELD_NAME', $options),
            $this->renderSubfield($hash, 'description', 'text', 'COM_FOCALPOINT_CUSTOMFIELD_TOOLTIP', $options),
            $this->renderSubfield($hash, 'label', 'text', 'COM_FOCALPOINT_CUSTOMFIELD_LABEL', $options)
        );

        $typeRenderer = 'renderSubfield' . ucfirst($type);
        if (method_exists($this, $typeRenderer)) {
            $renderedFields[] = $this->{$typeRenderer}($hash, $options);
        }

        return $renderedFields;
    }

    /**
     * @param string $hash
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfieldTextarea($hash, $options)
    {
        $fieldOptions = array(
            'attributes' => array(
                'class'   => 'btn-group btn-group-yesno',
                'default' => 0
            ),
            'options'    => JHtml::_(
                'select.options',
                array(
                    JHtml::_('select.option', 1, JText::_('JYES')),
                    JHtml::_('select.option', 0, JText::_('JNO'))
                ),
                array(
                    'option.key.toHtml'  => false,
                    'option.text.toHtml' => false
                )
            )
        );

        $renderedField = $this->renderSubfield(
            $hash,
            'loadeditor',
            'radio',
            JText::_('COM_FOCALPOINT_CUSTOMFIELD_LOAD_EDITOR'),
            array_merge($options, $fieldOptions)
        );

        return $renderedField;
    }

    /**
     * @param string $hash
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfieldImage($hash, $options)
    {
        $renderedField = $this->renderSubfield(
            $hash,
            'directory',
            'text',
            'COM_FOCALPOINT_CUSTOMFIELD_DEFAULT_DIRECTORY',
            $options
        );

        return $renderedField;
    }

    /**
     * @param string $hash
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfieldSelectlist($hash, $options)
    {
        $fieldOptions = array(
            'attributes' => array(
                'rows' => 20
            )
        );

        $renderedField = $this->renderSubfield(
            $hash,
            'options',
            'textarea',
            'COM_FOCALPOINT_CUSTOMFIELD_OPTIONS',
            array_merge($options, $fieldOptions)
        );

        return $renderedField;
    }

    /**
     * @param string $hash
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfieldMultiselect($hash, $options)
    {
        return $this->renderSubfieldSelectlist($hash, $options);
    }

    /**
     * @return string
     */
    protected function getTrashButton()
    {
        if (static::$trashButton === null) {
            static::$trashButton = sprintf(
                '<a %s></a>',
                ArrayHelper::toString(
                    array(
                        'class' => 'hasTip sl-subfield-delete icon-cancel',
                        'title' => 'Delete this field'
                    )
                )
            );
        }

        return static::$trashButton;
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
            $dummyId = 'BLANKFIELD';
            $blanks  = array();

            foreach ($this->fieldTypes as $fieldType) {
                $data = array('type' => $fieldType);

                $blanks[$fieldType] = preg_replace('/\n?\r?/', '', $this->getFieldBlock($dummyId, $data, $options));
            }
            $blanks = json_encode($blanks);

            JHtml::_('jquery.ui', array('core', 'sortable'));

            JFactory::getDocument()->addScriptDeclaration(
                <<<JSCRIPT
;jQuery(document).ready(function($) {
    var dummyId    = /{$dummyId}/g,
        fieldBlank = {$blanks};

    var deleteField = function(evt) {
        evt.preventDefault();

        var fieldset = $(this).parents('fieldset').get(0);
        if (fieldset) {
            $(fieldset).remove();
        }
    };

    var createField = function(evt) {
        evt.preventDefault();

        var type = $(this).data('type');
        if (fieldBlank[type]) {
            $(fieldBlank[type].replace(dummyId, createId()))
                .insertBefore($(this).parents('ul'));
            init();
            $('body').trigger('subform-row-add');
            
        } else {
            alert('Field Type not found - ' + type);
        }
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
        $('.sl-subfield-wrapper').sortable({handle: 'legend', axis: 'y', opacity: '0.6', distance: '1'});

        $('.sl-subfield-delete')
            .off('click', deleteField)
            .on('click', deleteField);

        $('.sl-customfield-new')
            .off('click', createField)
            .on('click', createField);
    };

    init();
});
JSCRIPT
            );

            static::$assetsLoaded = true;
        }
    }
}
