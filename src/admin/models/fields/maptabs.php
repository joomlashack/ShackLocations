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

class ShacklocationsFormFieldMaptabs extends JFormField
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

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            if ($parent = $element->xpath('parent::fieldset')) {
                $parent = array_pop($parent);

                $this->tabGroup         = $parent->addChild('fields');
                $this->tabGroup['name'] = $this->fieldname;

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws Exception
     */
    public function renderField($options = array())
    {
        $this->loadAssets();

        $htmlOutput = array(
            '<div class="span7 custom-maptabs">',
        );

        $values = (array)($this->value ?: array());
        foreach ($values as $hash => $data) {
            $htmlOutput[] = $this->renderSubfield($hash, $options);
        }

        $appendButton = '<div>'
            . '<button class="btn btn-small button-apply btn-success maptab-append">'
            . '<span class="icon-plus icon-white"></span>'
            . 'New Tab'
            . '</button>'
            . '</div>';

        $htmlOutput = array_merge(
            $htmlOutput,
            array(
                $appendButton,
                '</div>'
            )
        );

        return join('', $htmlOutput);
    }

    /**
     * @param string $tabHash
     * @param array  $options
     *
     * @return string
     */
    protected function renderSubfield($tabHash, $options = array())
    {
        $baseGroup = $this->group . '.' . $this->tabGroup['name'];
        $groupName = $baseGroup . '.' . $tabHash;

        $fieldGroup         = $this->tabGroup->addChild('fields');
        $fieldGroup['name'] = $tabHash;


        $nameFieldXml = sprintf(
            '<field name="name" type="text" label="%s"/>',
            JText::_('COM_FOCALPOINT_CUSTOMFIELD_NAME')
        );
        $nameField    = new SimpleXMLElement($nameFieldXml);
        $this->form->setField($nameField, $groupName);

        $contentFieldXml = '<field name="content" type="editor" label=""/>';
        $contentField    = new SimpleXMLElement($contentFieldXml);
        $this->form->setField($contentField, $groupName);

        $fieldHtml = array(
            '<fieldset class="clearfix">',
            '<legend><i class="icon-menu"></i>&nbsp;Tab</legend>',
            $this->getTrashButton(),
            $this->getInsertButton(),
            $this->form->renderField('name', $groupName, null, $options),
            $this->form->renderField('content', $groupName, null, $options),
            '</fieldset>'
        );

        return join('', $fieldHtml);
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
                        'class' => 'hasTip maptab-delete icon-cancel',
                        'title' => 'Delete this tab'
                    )
                )
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
                ArrayHelper::toString(
                    array(
                        'class' => 'hasTip maptab-insert icon-plus',
                        'title' => 'Insert new tab before this one'
                    )
                )
            );
        }

        return static::$insertButton;
    }

    protected function loadAssets()
    {
        if (!static::$assetsLoaded) {
            JHtml::_('jquery.ui', array('core', 'sortable'));

            JFactory::getDocument()->addScriptDeclaration(
                <<<JSCRIPT
jQuery(document).ready(function($) {
    $('.custom-maptabs').sortable({handle : 'legend',axis:'y',opacity:'0.6', distance:'1'});
    
    $('.maptab-delete').on('click', function(evt) {
        evt.preventDefault();
        
        var fieldset = $(this).parents('fieldset').get(0);
        if (fieldset) {
            $(fieldset).remove();
        }
    });
    
    $('.maptab-insert,.maptab-append').on('click', function(evt) {
        evt.preventDefault();
        
        var fieldset = $(this).parents('fieldset').get(0);
        if (fieldset) {
            $('<fieldset><legend>Hey!</legend></fieldset>').insertBefore($(fieldset));
            
        } else {
            $('<fieldset><legend>HO!</legend></fieldset>').insertBefore($(this).parent());
        }
    });
});
JSCRIPT
            );

            static::$assetsLoaded = true;
        }
    }
}
