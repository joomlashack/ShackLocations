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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class FocalpointViewLocation extends JViewLegacy
{
    /**
     * @var JObject
     */
    protected $state;

    /**
     * @var JObject
     */
    protected $item;

    /**
     * @var Registry
     */
    protected $params;

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
        /** @var SiteApplication $app */
        $app = JFactory::getApplication();

        /** @var FocalpointModelLocation $model */
        $model = $this->getModel();

        $user = JFactory::getUser();

        $this->state  = $model->getState();
        $this->item   = $model->getData();
        $this->params = $app->getParams('com_focalpoint');

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

        $this->item->metadata = new Registry($this->item->metadata);

        $params             = new Registry($this->item->params);
        $this->item->params = clone $this->params;
        $this->item->params->merge($params);

        $this->prepareDocument();

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

        $this->item->description     = $this->replace_custom_field_tags($this->item->description);
        $this->item->fulldescription = $this->replace_custom_field_tags($this->item->fulldescription);

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
    public function replace_custom_field_tags($text)
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
     * Prepares the document by setting up page titles and metadata.
     *
     * @return void
     * @throws Exception
     */
    protected function prepareDocument()
    {
        $app   = JFactory::getApplication();
        $menus = $app->getMenu();
        $menu  = $menus->getActive();

        // Set page title
        if ($menu) {
            if ($menu->params->get('show_page_heading') && $menu->params->get('page_heading')) {
                $this->item->page_title = $menu->params->get('page_heading');
            }

            if ($menu->params->get('page_title')) {
                $title = $menu->params->get('page_title');

            } else {
                $title = $this->item->title;
            }

        } else {
            $title = $this->item->title;
        }

        if (empty($title)) {
            $title = $app->get('sitename');

        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);

        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }
        $this->document->setTitle($title);

        // Set the page meta description. Article Meta over rides menu meta.
        $articlemeta = ($this->item->metadata->get('metadesc'));
        if ($articlemeta) {
            $this->document->setDescription($this->item->metadata->get('metadesc'));

        } elseif ($menu) {
            if ($menu->params->get('menu-meta_description')) {
                $this->document->setDescription($this->params->get('menu-meta_description'));
            }
        }

        // Set the page keywords
        $articlekeywords = ($this->item->metadata->get('metakey'));
        if ($articlekeywords) {
            $this->document->setMetadata('keywords', $this->item->metadata->get('metakey'));

        } elseif ($menu) {
            if ($menu->params->get('menu-meta_keywords')) {
                $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
            }
        }

        // Set the robots declarations
        $articlerobots = ($this->item->metadata->get('robots'));
        if ($articlerobots) {
            $this->document->setMetadata('robots', $this->item->metadata->get('robots'));

        } elseif ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        // Set rights declaration
        $articlerights = ($this->item->metadata->get('rights'));
        if ($articlerights) {
            $this->document->setMetadata('rights', $this->item->metadata->get('rights'));
        }

        // Set the author declaration
        $articleauthor = ($this->item->metadata->get('author'));
        if ($articleauthor) {
            $this->document->setMetadata('author', $this->item->metadata->get('author'));
        }
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
