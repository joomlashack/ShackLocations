<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2023 Joomlashack.com. All rights reserved
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
     * @throws Exception
     */
    public function renderField($options = [])
    {
        if ($customFields = $this->getCustomFields()) {
            $renderedFields = [];

            foreach ($customFields as $hash => $value) {
                $renderedFields[] = $this->renderFieldBlock($hash, $value, $options);
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
    protected function renderFieldBlock(string $hash, array $customField, array $options): string
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
                return $this->{$renderer}($fieldName, $fieldGroup, $groupName, $customField, $options);
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
     * @throws Exception
     */
    protected function renderSubfield(
        string $type,
        string $name,
        string $label,
        string $description,
        string $groupName,
        array $options
    ): string {
        $attributes = [
            'name'        => $name,
            'type'        => $type,
            'label'       => addslashes(htmlspecialchars($label)),
            'description' => addslashes(htmlspecialchars($description))
        ];
        if (empty($options['attributes']) == false) {
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
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $subFields
     * @param array            $options
     *
     * @return string
     * @throws Exception
     */
    protected function renderSubfieldGroup(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $subFields,
        array $options
    ): string {
        $subFieldGroup         = $fieldGroup->addChild('fields');
        $subFieldGroup['name'] = $fieldName;

        $renderedSubfields = [
            '<fieldset class="sl-customfield-group">',
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
    protected function safeTranslate(string $constant): string
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
     * @throws Exception
     */
    protected function renderSubFieldTextbox(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
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
     * @throws Exception
     */
    protected function renderSubfieldTextarea(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
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
     * @throws Exception
     */
    protected function renderSubfieldImage(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
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
     * @throws Exception
     */
    protected function renderSubfieldLink(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
        $subFields = [
            'url'      => ['type' => 'text'],
            'linktext' => ['type' => 'text'],
            'target'   => [
                'type'    => 'radio',
                'options' => [
                    'attributes' => [
                        'class'   => 'btn-group btn-group-yesno',
                        'layout'  => 'joomla.form.field.radio.switcher',
                        'default' => '1'
                    ],
                    'options'    => HTMLHelper::_(
                        'select.options',
                        [
                            HTMLHelper::_('select.option', 0, Text::_('JNO')),
                            HTMLHelper::_('select.option', 1, Text::_('JYES')),
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
     * @throws Exception
     */
    protected function renderSubfieldEmail(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
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
     * @throws Exception
     */
    protected function renderSubfieldSelectlist(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
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

        return $this->renderSubfield('list', $fieldName, $label, $description, $groupName, $fieldOptions);
    }

    /**
     * @param string           $fieldName
     * @param SimpleXMLElement $fieldGroup
     * @param string           $groupName
     * @param array            $customField
     * @param array            $options
     *
     * @return string
     * @throws Exception
     */
    protected function renderSubfieldMultiselect(
        string $fieldName,
        SimpleXMLElement $fieldGroup,
        string $groupName,
        array $customField,
        array $options
    ): string {
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
     * @return array
     */
    protected function getCustomFields(): array
    {
        if ($this->customFields === null && $this->typeField) {
            $db = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select('customfields')
                ->from('#__focalpoint_locationtypes')
                ->where('id = ' . (int)$this->typeField->value);

            $customFields       = $db->setQuery($query)->loadResult();
            $this->customFields = $customFields ? json_decode($customFields, true) : [];
        }

        return $this->customFields;
    }
}
