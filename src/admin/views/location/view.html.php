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

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

/**
 * View to edit
 */
class FocalpointViewLocation extends JViewLegacy
{
    /**
     * @var FocalpointModelLocation
     */
    protected $model = null;

    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var object
     */
    protected $item = null;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        try {
            $this->model = $this->getModel();
            $this->state = $this->model->getState();
            $this->item  = $this->model->getItem();
            $this->form  = $this->model->getForm();

            //Get custom form fields
            $this->item->customformfieldshtml = $this->getCustomFieldsHTML($this->item->type);

            // Check for errors.
            if (count($errors = $this->get('Errors'))) {
                throw new Exception(implode("\n", $errors));
            }

            $this->addToolbar();

            parent::display($tpl);

        } catch (Throwable $e) {
            echo $e->getMessage();
            echo $e->getLine() . ': ' . $e->getCode();
        }
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user  = JFactory::getUser();
        $isNew = ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
            $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
        $canDo = FocalpointHelper::getActions();

        JToolBarHelper::title(JText::_('COM_FOCALPOINT_TITLE_LOCATION'), 'location.png');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {

            JToolBarHelper::apply('location.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('location.save', 'JTOOLBAR_SAVE');
        }
        if (!$checkedOut && ($canDo->get('core.create'))) {
            JToolBarHelper::custom('location.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW',
                false);
        }
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::custom('location.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY',
                false);
        }
        if (empty($this->item->id)) {
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CANCEL');
        } else {
            JToolBarHelper::cancel('location.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Function to retrieve the form elements defined in locationtypes and populate with saved values
     *
     * @param string $type
     *
     * @return string
     * @throws Exception
     */
    public function getCustomFieldsHTML($type)
    {
        return '';

        /** @var AdministratorApplication $app */
        $app          = JFactory::getApplication();
        $customFields = $this->model->getCustomFieldsHTML($type);

        /*
         * First check the session for data. If the previous save failed
         * with an error the data will be in the session so we can
         * repopulate the custom fields. Otherwise we lose unsaved info.
         */
        $data = $app->getUserState('com_focalpoint.edit.location.data', array());
        if (isset($data['customfieldsdata'])) {
            $this->item->custom = json_decode($data['customfieldsdata'], true);
        }

        if ($customFields) {
            $config = JFactory::getConfig();

            $html = array();
            foreach ($customFields as $key => $customField) {
                list($dataType, $hash) = explode(".", $key);
                $dataKey = $key . '.' . $customField['name'];

                $value = empty($this->item->custom[$dataKey]) ? '' : $this->item->custom[$dataKey];
                $value = $this->escape($value);

                $id   = "jform_custom_{$dataKey}_target";
                $name = "jform[custom][{$dataKey}]";

                // Create the standard label
                $labelAttribs = array(
                    'id'    => $id . '-lbl',
                    'for'   => $name,
                    'class' => 'hasTooltip',
                );
                if ($customField['description']) {
                    $labelAttribs['title'] = $customField['description'];
                }

                $label = sprintf(
                    '<label %s>%s</label>',
                    ArrayHelper::toString($labelAttribs),
                    $customField['label']
                );

                switch ($dataType) {
                    case 'textbox':
                        $control = sprintf(
                            '<input id="%s" name="%s" type="text" class="field" value="%s"/>',
                            $id,
                            $name,
                            $value
                        );
                        $html[]  = $this->createControlGroup($control, $label);
                        break;

                    case 'textarea':
                        if ($customField['loadeditor']) {
                            $html[] = '<div class="control-group">';
                            $html[] = '<div>' . $label . '</div>';

                            $editorConfig = $config->get('editor');
                            $editor       = JEditor::getInstance($editorConfig);

                            $control = $editor->display($name, $value, '100%', '300px', null, null, true, $id);
                            $html[]  = $control;
                            $html[]  = '</div>';

                        } else {
                            $control = sprintf(
                                '<textarea id="%s" name="%s" class="field">%s</textarea>',
                                $id,
                                $name,
                                $value
                            );
                            $html[]  = $this->createControlGroup($control, $label);
                        }

                        break;

                    case 'image':
                        $imageId = 'custimg-' . $hash;

                        $control = array(
                            '<div class="input-append">',
                            sprintf(
                                '<input id="%s" name="%s" type="text" value="%s"/>',
                                $imageId,
                                $name,
                                $value
                            ),
                            JHtml::_(
                                'link',
                                'index.php?option=com_media&view=images&tmpl=component&fieldid=' . $imageId,
                                'Select',
                                'rel="{handler: \'iframe\', size: {x: 800, y: 500}}" class="modal btn"'
                            ),
                            '</div>'
                        );

                        $html[] = $this->createControlGroup(join('', $control), $label);
                        break;
/*
                    case 'link':
                        if (!is_array($value)) {
                            //Define blank values so PHP doesn't generate notices
                            $value = array_fill_keys(array('url', 'linktext', 'target'), '');
                        }

                        // URL input
                        $urlControl = array(
                            sprintf(
                                '<input id="%s_url" name="%s[url]" type="text" class="field" value="%s" />',
                                $id,
                                $name,
                                $value['url']
                            ),
                            '<br>',
                            sprintf(
                                '<span class="hasTooltip small" title="%s">%s</span>',
                                'The URL to link to. Include http:// at the start for external links.',
                                'URL'
                            )
                        );
                        $html[]     = $this->createControlGroup(join('', $urlControl), $label);

                        // Link text field
                        $linkTextControl = array(
                            sprintf(
                                '<input id="%s_linktext" name="%s[linktext]" type="text" class="field" value="%s"/>',
                                $id,
                                $name,
                                $value['linktext']
                            ),
                            '<br>',
                            sprintf(
                                '<span class="hasTooltip small" title="%s">%s</span>',
                                'Optional link text. If left blank the URL will be used as link text.',
                                'Link text'
                            )
                        );

                        $html[] = $this->createControlGroup(join('', $linkTextControl), '&nbsp;');

                        // Target field
                        $targetControl = array(
                            sprintf('<fieldset id="%s_target" class="btn-group btn-group-yesno radio">', $id),
                            sprintf(
                                '<input type="radio" id="%s_target0" name="%s[target]" value="1"%s>',
                                $id,
                                $name,
                                $value['target'] ? ' checked' : ''
                            ),
                            sprintf('<label for="%s_target0" class="btn">%s</label>', $id, JText::_('JYES')),
                            sprintf(
                                '<input type="radio" id="%s_target1" name="%s][target]" value="0"%s>',
                                $id,
                                $name,
                                $value['target'] ? '' : ' checked'
                            ),
                            sprintf('<label for="%s_target1">%s</label>', $id, JText::_('JNO')),
                            '</fieldset>'
                        );

                        $html[] = $this->createControlGroup(join($targetControl), '&nbsp;');
                        break;

                    case 'email':
                        if (!is_array($value)) {
                            $value = array_fill_keys(array('email', 'linktext'), '');
                        }

                        $emailControl = array(
                            sprintf(
                                '<input id="%s_email" name="%s[email]" type="text" class="field" value="%s" />',
                                $id,
                                $name,
                                $value['email']
                            ),
                            '<br>',
                            sprintf(
                                '<span class="small hasTooltip" title="%s">%s</span>',
                                'The actual email address. Do not include mailto:. This will be added automatically',
                                'email address'
                            )
                        );

                        $html[] = $this->createControlGroup(join('', $emailControl), $label);

                        $textControl = array(
                            sprintf(
                                '<input id="%s_linktext" name="%s[linktext]" value="%s" />',
                                $id,
                                $name,
                                $value['linktext']
                            ),
                            '<br>',
                            sprintf(
                                '<span class="small hasTooltip" title="%s">%s</span>',
                                'Optional link text. If left blank the URL will be used as link text.',
                                'Link text'
                            )
                        );

                        $html[] = $this->createControlGroup(join('', $textControl), '&nbsp;');
                        break;

                    case 'selectlist':
                    case 'multiselect':
                        $options = preg_split('/\n\r/', $customField['options']);

                        foreach ($options as $i => $option) {
                            $selected = false;
                            if (is_array($value)) {
                                if (array_search($option, $value) !== false) {
                                    $selected = true;
                                }
                            } else {
                                $selected = ($value == $option);
                            }

                            $options[$i] = sprintf(
                                '<option value="%1$s"%2$s>%1$s</option>',
                                $option,
                                $selected ? ' selected="selected"' : ''
                            );
                        }

                        $selectControl = sprintf(
                            '<select id="%s" name="%s"%s>%s</select>',
                            $id,
                            $name,
                            $dataType == 'multiselect' ? ' multiple' : '',
                            join('', $options)
                        );

                        $html[] = $this->createControlGroup($selectControl, $label);

                        break;
*/                }

                $html[] = '<hr>';
            }

        } else {
            if ($this->item->type == '') {
                $html = JText::_('COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_SAVE_FIRST');

            } else {
                $html = JText::_('COM_FOCALPOINT_LEGEND_LOCATION_CUSTOMFIELDS_NONE_DEFINED');
            }
        }

        if (is_array($html)) {
            return join('', $html);
        }

        return $html;
    }

    /**
     * @param string $control
     * @param string $label
     * @param bool   $before
     *
     * @return string
     */
    protected function createControlGroup($control, $label = null, $before = true)
    {
        $controlGroup = array('<div class="control-group">');

        if ($label && $before) {
            $controlGroup[] = '<div class="control-label">';
            $controlGroup[] = $label;
            $controlGroup[] = '</div>';

        }

        $controlGroup[] = '<div class="controls">';
        $controlGroup[] = $control;
        $controlGroup[] = '</div>';

        if ($label && !$before) {

        }
        $controlGroup[] = '</div>';

        return join('', $controlGroup);
    }
}
