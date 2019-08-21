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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class FocalpointViewLocation extends FocalpointViewSite
{
    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var CMSObject
     */
    protected $item = null;

    /**
     * @var object
     */
    protected $outputfield;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var FocalpointModelLocation $model */
        $model = $this->getModel();

        $user = JFactory::getUser();

        $this->state  = $model->getState();
        $this->item   = $model->getData();

        // Check for errors.
        if ($errors = $model->getErrors()) {
            throw new Exception(implode("\n", $errors));
        }

        if ($this->_layout == 'edit') {
            $authorised = $user->authorise('core.create', 'com_focalpoint');
            if ($authorised !== true) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }
        }

        JPluginHelper::importPlugin('focalpoint');
        JFactory::getApplication()->triggerEvent('onBeforeMapPrepareRender', array(&$this->item));

        $this->params->merge($this->item->params);
        
        JPluginHelper::importPlugin('content');
        $this->item->text = $this->item->description;
        JFactory::getApplication()->triggerEvent(
            'onContentPrepare',
            array(
                'com_focalpoint.location',
                &$this->item,
                &$this->params,
                $limitstart = 0
            )
        );
        $this->item->description = $this->item->text;

        $this->item->text = $this->item->fulldescription;
        JFactory::getApplication()->triggerEvent(
            'onContentPrepare',
            array(
                'com_focalpoint.location',
                &$this->item,
                &$this->params,
                $limitstart = 0
            )
        );
        $this->item->fulldescription = $this->item->text;
        unset($this->item->text);

        $this->item->description     = $this->replaceCustomFieldTags($this->item->description);
        $this->item->fulldescription = $this->replaceCustomFieldTags($this->item->fulldescription);

        $this->setDocumentTitle($this->item->title);
        $this->setDocumentMetadata($this->item->metadata);

        parent::display($tpl);
    }

    /**
     * Replaces all custom field tags in the text.
     *
     * @param string $text
     *
     * @return string
     * @throws Exception
     */
    protected function replaceCustomFieldTags($text)
    {
        $regex = '/{(.*?)}/i';
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            foreach ($matches as $match) {
                foreach ($this->item->customfields as $name => $customfield) {
                    if ($name == $match[1]) {
                        $this->outputfield = (object)array(
                            'hidelabel' => true,
                            'data'      => $customfield->data
                        );

                        ob_start();
                        echo $this->loadTemplate('customfield_' . $customfield->datatype);
                        $output = ob_get_contents();
                        ob_end_clean();

                        $text = str_replace($match[0], $output, $text);
                    }
                }
            }

        }

        return $text;
    }

    /**
     * Renders a custom field using the relevant template.
     *
     * @param object $field
     * @param bool   $hidelabel
     *
     * @return string
     * @throws Exception
     *
     */
    protected function renderField($field, $hidelabel = false)
    {
        if (!empty($field->datatype)) {
            $data = array_merge(
                get_object_vars($field),
                array(
                    'showlabel' => !$hidelabel
                )
            );

            return JLayoutHelper::render('custom.field.' . $field->datatype, $data);
        }

        return '';
    }

    /**
     * @return void
     * @throws Exception
     *
     * @deprecated v1.4.0
     */
    protected function renderCustomField()
    {
        JFactory::getApplication()->enqueueMessage(
            'The template override is using an obsolete method and requires updating'
        );
    }
}
