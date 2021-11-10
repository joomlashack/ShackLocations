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

class ShacklocationsFormFieldCustomfieldsdata extends FormField
{
    /**
     * @var FormField
     */
    protected $typeField = null;

    /**
     * @var object[]
     */
    protected $customFields = null;

    /**
     * @var SimpleXMLElement
     */
    protected $fieldGroup = null;

    /**
     * @inheritDoc
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            $typeField     = explode('.', (string)$element['locationtype'] ?: 'type');
            $typeFieldName = array_pop($typeField);
            $typeGroupName = array_pop($typeField);

            $this->typeField = $this->form->getField($typeFieldName, $typeGroupName);

            if ($parent = $element->xpath('parent::fieldset')) {
                /*
                 * Create a field group based on the field name
                 */
                $parent = array_pop($parent);

                $this->fieldGroup         = $parent->addChild('fields');
                $this->fieldGroup['name'] = $this->fieldname;

                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function renderField($options = [])
    {
        if ($customFields = $this->getCustomFields()) {
            $renderedFields = [];

            foreach ($customFields as $hash => $value) {
                if (isset($customFields[$hash])) {
                    $renderedFields[] = $this->renderFieldBlock($hash, $customFields[$hash], $options);
                }
            }

        } else {
            if ($this->typeField->value) {
                $db = Factory::getDbo();

                $locationType = $db->setQuery(
                    $db->getQuery(true)
                        ->select('title')
                        ->from('#__focalpoint_locationtypes')
                        ->where('id = ' . (int)$this->typeField->value)
                )
                    ->loadResult();

                $message = Text::sprintf('COM_FOCALPOINT_CUSTOMFIELDSDATA_NONE', $locationType);

            } else {
                $message = Text::_('COM_FOCALPOINT_CUSTOMFIELDSDATA_SAVE');
            }


            $renderedFields = [
                '<div class="tab-description alert alert-info">',
                '<span class="icon-info" aria-hidden="true"></span>',
                $message,
                '</div>'
            ];
        }

        return join("\n", $renderedFields);
    }

    /**
     * @param string $hash
     * @param array  $customField
     * @param array  $options
     *
     * @return string
     * @throws Exception
     */
    protected function renderFieldBlock($hash, array $customField, array $options)
    {
        $fieldType = $customField['type'];
        $fieldName = $customField['name'];

        if ($fieldType && $fieldName) {
            $fieldGroup         = $this->fieldGroup->addChild('fields');
            $fieldGroup['name'] = $hash;

            $baseGroup = $this->fieldGroup['name'];
            $groupName = $baseGroup . '.' . $fieldGroup['name'];

            $renderer = 'renderSubField' . ucfirst($fieldType);
            if (method_exists($this, $renderer)) {
                $subField = $this->{$renderer}($fieldName, $fieldGroup, $groupName, $customField, $options);

                return $subField;
            }

            $error = Text::sprintf('COM_FOCALPOINT_ERROR_CUSTOMFIELD_UNKNOWN', $fieldType);

        } else {
            $error = Text::sprintf('COM_FOCALPOINT_ERROR_CUSTOMFIELD_CONFIGURATION', $fieldType, $fieldName);
        }

        Factory::getApplication()->enqueueMessage('Custom field configuration issue', 'Warning');

        return sprintf(
            '<div class="alert">%s</div>',
            empty($error) ? '**syserr**' : $error
        );
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $label
     * @param string $description
     * @param string $groupName
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfield($type, $name, $label, $description, $groupName, array $options)
    {
        $attributes = [
            'name'        => $name,
            'type'        => $type,
            'label'       => addslashes(htmlspecialchars($label)),
            'description' => addslashes(htmlspecialchars($description))
        ];
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

        $renderedField = $this->form->renderField($name, $groupName, null, $options);

        return $renderedField;
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $subFields
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldGroup(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $subFields,
        array $options
    ) {
        $subFieldGroup         = $fieldGroup->addChild('fields');
        $subFieldGroup['name'] = $fieldName;

        $renderedSubfields = [
            '<fieldset>',
            sprintf('<legend>%s</legend>', $customField['label'])
        ];

        foreach ($subFields as $name => $subField) {
            $fieldOptions = empty($subField['options']) ? $options : array_merge($options, $subField['options']);

            $languageConstant = 'COM_FOCALPOINT_CUSTOMFIELD_TYPE_' . $customField['type'] . '_' . $name;

            $label = empty($subField['label'])
                ? $this->safeTranslate($languageConstant)
                : $subField['label'];

            $description = empty($subField['description'])
                ? $this->safeTranslate($languageConstant . '_DESC')
                : $subField['description'];

            $renderedSubfields[] = $this->renderSubfield(
                $subField['type'],
                $name,
                $label,
                $description,
                $groupName . '.' . $fieldName,
                $fieldOptions
            );
        }

        $renderedSubfields[] = '</fieldset>';

        return join("\n", $renderedSubfields);
    }

    /**
     * Checks if the language constant exists before translating.
     * Returns empty string if it doesn't
     *
     * @param string $constant
     *
     * @return string
     */
    protected function safeTranslate($constant)
    {
        if (Factory::getLanguage()->hasKey($constant)) {
            return Text::_($constant);
        }

        return '';
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubFieldTextbox(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $label       = $customField['label'];
        $description = $customField['description'];

        return $this->renderSubfield('text', $fieldName, $label, $description, $groupName, $options);
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldTextarea(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $type        = $customField['loadeditor'] ? 'editor' : 'textarea';
        $label       = $customField['label'];
        $description = $customField['description'];

        return $this->renderSubfield($type, $fieldName, $label, $description, $groupName, $options);
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldImage(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $label       = $customField['label'];
        $description = $customField['description'];

        $fieldOptions = array_merge(
            $options,
            [
                'attributes' => [
                    'directory' => $customField['directory']
                ]
            ]
        );

        return $this->renderSubfield('media', $fieldName, $label, $description, $groupName, $fieldOptions);
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldLink(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $subFields = [
            'url'      => ['type' => 'text'],
            'linktext' => ['type' => 'text'],
            'target'   => [
                'type'    => 'radio',
                'options' => [
                    'attributes' => [
                        'class'   => 'btn-group btn-group-yesno',
                        'default' => '1'
                    ],
                    'options'    => HTMLHelper::_(
                        'select.options',
                        [
                            HTMLHelper::_('select.option', 1, Text::_('JYES')),
                            HTMLHelper::_('select.option', 0, Text::_('JNO'))
                        ],
                        [
                            'option.key.toHtml'  => false,
                            'option.text.toHtml' => false
                        ]
                    )
                ]
            ]
        ];

        return $this->renderSubfieldGroup($fieldName, $fieldGroup, $groupName, $customField, $subFields, $options);
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldEmail(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $subFields = [
            'email'    => ['type' => 'email'],
            'linktext' => ['type' => 'text']
        ];

        return $this->renderSubfieldGroup($fieldName, $fieldGroup, $groupName, $customField, $subFields, $options);
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldSelectlist(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $selectOptions = array_filter(
            array_unique(
                array_map('trim', preg_split('/\r?\n/', $customField['options']))
            )
        );

        foreach ($selectOptions as &$option) {
            $option = HTMLHelper::_('select.option', $option, $option);
        }

        $fieldOptions = array_merge(
            $options,
            [
                'options' => HTMLHelper::_(
                    'select.options',
                    $selectOptions,
                    [
                        'option.key.toHtml'  => false,
                        'option.text.toHtml' => false
                    ]
                )
            ]
        );

        $label       = $customField['label'];
        $description = $customField['description'];

        $renderedField = $this->renderSubfield('list', $fieldName, $label, $description, $groupName, $fieldOptions);

        return $renderedField;
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfieldMultiselect(
        $fieldName,
        SimpleXMLElement $fieldGroup,
        $groupName,
        array $customField,
        array $options
    ) {
        $fieldOptions = array_merge(
            $options,
            [
                'attributes' => ['multiple' => 'true']
            ]
        );

        return $this->renderSubfieldSelectlist($fieldName, $fieldGroup, $groupName, $customField, $fieldOptions);
    }

    /**
     * Get defined custom fields for the selected location type
     *
     * @param int $type
     *
     * @return array
     */
    protected function getCustomFields()
    {
        if ($this->customFields === null && $this->typeField) {
            $db = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select('customfields')
                ->from('#__focalpoint_locationtypes')
                ->where('id = ' . (int)$this->typeField->value);

            if ($customFields = $db->setQuery($query)->loadResult()) {
                $this->customFields = json_decode($customFields, true);
            }
        }

        return $this->customFields;
    }
}
