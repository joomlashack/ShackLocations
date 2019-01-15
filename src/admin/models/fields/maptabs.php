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

defined('_JEXEC') or die();

class ShacklocationsFormFieldMaptabs extends JFormField
{
    protected static $assetsLoaded = false;

    /**
     * @param array $options
     *
     * @return string
     * @throws Exception
     */
    public function renderField($options = array())
    {
        if ($parent = $this->element->xpath('parent::fieldset')) {
            $this->loadAssets();

            $htmlOutput = array(
                '<div class="span7 custom-maptabs">'
            );

            if ($values = (array)($this->value ?: array())) {
                // Add our current tab name group
                $parent           = array_pop($parent);
                $tabGroup         = $parent->addChild('fields');
                $tabGroup['name'] = $this->fieldname;

                $baseGroup = $this->group . '.' . $tabGroup['name'];

                foreach ($values as $hash => $data) {
                    //'<a class="hasTooltip deletefield icon-trash" data-original-title="<strong>Delete this field?</strong><br />This can NOT be undone."></a>';

                    $htmlOutput = array_merge(
                        $htmlOutput,
                        array(
                            '<fieldset class="clearfix">',
                            '<legend><i class="icon-menu"></i>&nbsp;Tab</legend>',
                            $this->renderSubfields($tabGroup, $baseGroup, $hash, $options),
                            '</fieldset>'
                        )
                    );
                }
            }

            $htmlOutput[] = '</div>';

            return join('', $htmlOutput);
        }

        JFactory::getApplication()->enqueueMessage('Error with setup of custom tab field - ' . $this->name, 'error');
        return '';
    }

    /**
     * @param SimpleXMLElement $tabGroup
     * @param string           $baseGroup
     * @param string           $tabHash
     * @param array            $options
     *
     * @return string
     */
    protected function renderSubfields(SimpleXMLElement $tabGroup, $baseGroup, $tabHash, $options = array())
    {
        $fieldGroup         = $tabGroup->addChild('fields');
        $fieldGroup['name'] = $tabHash;

        $groupName = $baseGroup . '.' . $tabHash;

        $nameFieldXml = sprintf(
            '<field name="name" type="text" label="%s"/>',
            JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME')
        );
        $nameField    = new SimpleXMLElement($nameFieldXml);
        $this->form->setField($nameField, $groupName);

        $contentFieldXml = '<field name="content" type="editor" label=""/>';
        $contentField    = new SimpleXMLElement($contentFieldXml);
        $this->form->setField($contentField, $groupName);

        $fieldHtml = $this->form->renderField('name', $groupName, null, $options)
            . $this->form->renderField('content', $groupName, null, $options);

        return $fieldHtml;
    }

    protected function loadAssets()
    {
        if (!static::$assetsLoaded) {
            JHtml::_('jquery.ui', array('core', 'sortable'));

            JFactory::getDocument()->addScriptDeclaration(
                <<<JSCRIPT
jQuery(document).ready(function($) {
    $('.custom-maptabs').sortable({handle : 'legend',axis:'y',opacity:'0.6', distance:'1'});
});
JSCRIPT
            );

            static::$assetsLoaded = true;
        }
    }
}
