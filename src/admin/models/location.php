<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018 Joomlashack <https://www.joomlashack.com
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

use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die();

class FocalpointModellocation extends JModelAdmin
{
    protected $text_prefix = 'COM_FOCALPOINT';

    /**
     * @var CMSObject
     */
    protected $item = null;

    public function getTable($type = 'Location', $prefix = 'FocalpointTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_focalpoint.location',
            'location',
            array('control' => 'jform', 'load_data' => $loadData)
        );

        return $form;
    }

    /**
     * @param JForm     $form
     * @param CMSObject $data
     * @param string    $group
     *
     * @return void
     * @throws Exception
     */
    protected function preprocessForm(\JForm $form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);

        $customFields = $form->getXml()->xpath('//fieldset[@name="customfields"]');
        if ($customFields = array_pop($customFields)) {
            if (empty($data->id) || empty($data->type)) {
                $customFields['description'] = 'COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_SAVE_FIRST';

            } else {
                $app = JFactory::getApplication();

                $definedFields = $this->getCustomFields($data->type);
                if (!$definedFields) {
                    $customFields['description'] = 'COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_NONE_DEFINED';

                } else {
                    $group         = $customFields->addChild('fields');
                    $group['name'] = 'customfields';

                    foreach ($definedFields as $key => $definedField) {
                        $keyParts = explode('_', $key);
                        if (count($keyParts) != 2) {
                            $app->enqueueMessage('Invalid custom field key found - ' . $key, 'error');
                            continue;
                        }

                        $keyParts[] = $definedField['name'];
                        $hashedName = join('_', $keyParts);

                        $fieldAttribs = array(
                            'name'        => $hashedName,
                            'label'       => $definedField['label'],
                            'description' => $definedField['description']
                        );

                        $dataType = $keyParts[0];
                        switch ($dataType) {
                            case 'textbox':
                                $fieldAttribs['type'] = 'textbox';
                                $this->addCustomField($group, $fieldAttribs);
                                break;

                            case 'email':
                                $emailGroup         = $group->addChild('fields');
                                $emailGroup['name'] = $hashedName;

                                $spacer = array(
                                    'type'        => 'spacer',
                                    'label'       => $fieldAttribs['label'],
                                    'description' => $fieldAttribs['description']
                                );
                                $this->addCustomField($emailGroup, $spacer);

                                $email = array_merge(
                                    $fieldAttribs,
                                    array(
                                        'name'       => 'email',
                                        'type'       => 'email',
                                        'label'      => 'Address',
                                        'descripton' => 'The actual email address. Do not include mailto:. This will be added automatically'
                                    )
                                );
                                $this->addCustomField($emailGroup, $email);

                                $emailText = array_merge(
                                    $fieldAttribs,
                                    array(
                                        'name'        => 'linktext',
                                        'label'       => 'Text',
                                        'description' => 'Optional link text. If left blank the URL will be used as link text.'
                                    )
                                );
                                $this->addCustomField($emailGroup, $emailText);
                                break;

                            case 'textarea':
                                if ($definedField['loadeditor']) {
                                    $fieldAttribs['type']   = 'editor';
                                    $fieldAttribs['filter'] = 'JComponentHelper::filterText';
                                } else {
                                    $fieldAttribs['type'] = 'textarea';
                                }
                                $this->addCustomField($group, $fieldAttribs);
                                break;

                            case 'image':
                                $fieldAttribs['type']      = 'media';
                                $fieldAttribs['directory'] = $definedField['directory'];
                                $this->addCustomField($group, $fieldAttribs);
                                break;

                            case 'link':
                                $linkGroup         = $group->addChild('fields');
                                $linkGroup['name'] = $hashedName;

                                $spacer = array(
                                    'type'        => 'spacer',
                                    'label'       => $fieldAttribs['label'],
                                    'description' => $fieldAttribs['description']
                                );
                                $this->addCustomField($linkGroup, $spacer);

                                // URL input
                                $linkUrl = array_merge(
                                    $fieldAttribs,
                                    array(
                                        'name'        => 'url',
                                        'type'        => 'text',
                                        'label'       => 'URL',
                                        'description' => 'The URL to link to. Include http:// at the start for external links.'
                                    )
                                );
                                $this->addCustomField($linkGroup, $linkUrl);

                                // Link text field
                                $linkText = array_merge(
                                    $fieldAttribs,
                                    array(
                                        'name'        => 'linktext',
                                        'type'        => 'text',
                                        'label'       => 'Link text',
                                        'description' => 'Optional link text. If left blank the URL will be used as link text.'
                                    )
                                );
                                $this->addCustomField($linkGroup, $linkText);

                                // Target field
                                $linkTarget  = array_merge(
                                    $fieldAttribs,
                                    array(
                                        'name'    => 'target',
                                        'type'    => 'radio',
                                        'class'   => 'btn-group btn-group-yesno',
                                        'label'   => 'Target',
                                        'default' => 0
                                    )
                                );
                                $targetField = $this->addCustomField($linkGroup, $linkTarget);

                                $yes = $targetField->addChild('option', JText::_('JYES'));
                                $no  = $targetField->addChild('option', JText::_('JNO'));

                                $yes['value'] = '1';
                                $no['value']  = '0';
                                break;

                            case 'selectlist':
                            case 'multiselect':
                                $options = preg_split('/\n\r?/', $definedField['options']);
                                $options = array_map('trim', $options);

                                if ($options) {
                                    $fieldAttribs['type'] = 'list';
                                    if ($dataType == 'multiselect') {
                                        $fieldAttribs['multiple'] = 'true';
                                    }

                                    $selectField = $this->addCustomField($group, $fieldAttribs);
                                    foreach ($options as $option) {
                                        $option          = $selectField->addChild('option', $option);
                                        $option['value'] = $option;
                                    }
                                }

                                break;

                            default:
                                $fieldAttribs['type'] = $dataType;
                                $this->addCustomField($group, $fieldAttribs);
                        }
                    }
                }
            }
        }
    }

    /**
     * Add a field to the specified <fields> group using the supplied attributes
     *
     * @param SimpleXMLElement $group
     * @param array            $attributes
     *
     * @return SimpleXMLElement
     */
    protected function addCustomField(SimpleXMLElement $group, array $attributes)
    {
        $field = $group->addChild('field');
        foreach ($attributes as $attribute => $value) {
            $field[$attribute] = $value;
        }

        return $field;
    }

    protected function loadFormData()
    {
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_focalpoint.edit.location.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * @param int $pk
     *
     * @return CMSObject
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            $item->description = trim($item->fulldescription) == ''
                ? $item->description
                : $item->description . '<hr id="system-readmore" />' . $item->fulldescription;
        }

        if (empty($item->id)) {
            $item->created_by = JFactory::getUser()->id;
        }

        return $item;
    }

    /**
     * @param JTable $table
     *
     * @return void
     */
    protected function prepareTable($table)
    {
        $table->alias = JFilterOutput::stringURLSafe($table->alias ?: $table->title);

        if (!$table->id) {
            $table->ordering = $table->getNextOrder();
        }

        $parts = preg_split('#(<hr\s+id="system-readmore"\s*/>)#', $table->description);
        if (count($parts) == 2) {
            $table->fulldescription = trim(array_pop($parts));
            $table->description     = trim(array_pop($parts));

        } else {
            $table->fulldescription = '';
        }
    }

    /**
     * Get defined custom fields for the selected location type
     *
     * @param int $type
     *
     * @return array
     */
    public function getCustomFields($type)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('customfields')
            ->from('#__focalpoint_locationtypes')
            ->where('id = ' . (int)$type);

        if ($customFields = $db->setQuery($query)->loadResult()) {
            return json_decode($customFields, true);
        }

        return array();
    }

    public function save($data)
    {
        if (empty($data['othertypes'])) {
            $data['othertypes'] = '';
        }

        if (parent::save($data)) {
            $db = $this->getDbo();
            $id = $data['id'] ?: $db->insertid();

            $sql = $db->getQuery(true)
                ->delete('#__focalpoint_location_type_xref')
                ->where('location_id = ' . $id);
            $db->setQuery($sql)->execute();

            $types = array_merge(
                array($data['type']),
                empty($data['othertypes']) ? array() : $data['othertypes']
            );
            $types = array_filter(array_unique($types));

            foreach ($types as $type) {
                $insert = (object)array(
                    'location_id'     => $id,
                    'locationtype_id' => $type
                );
                $db->insertObject('#__focalpoint_location_type_xref', $insert);
            }

            return true;
        }

        return false;
    }
}
