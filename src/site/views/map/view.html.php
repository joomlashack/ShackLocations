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

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class FocalpointViewMap extends JViewLegacy
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
     * @var Registry
     */
    protected $params = null;

    /**
     * @var object
     */
    protected $outputfield = null;

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

        /** @var FocalpointModelMap $model */
        $model = $this->getModel();

        $this->params = $app->getParams('com_focalpoint');
        $this->state  = $model->getState();
        $this->item   = $model->getData();

        $this->item->markerdata = $model->getMarkerData($this->item->id);

        // Check for errors.
        if ($errors = $model->getErrors()) {
            throw new Exception(implode("\n", $errors));
        }

        // Load FocalPoint Plugins. Trigger onBeforeMapPrepareRender
        JPluginHelper::importPlugin('focalpoint');
        JFactory::getApplication()->triggerEvent('onBeforeMapPrepareRender', array(&$this->item));

        $offset = $this->state->get('list.offset');
        JPluginHelper::importPlugin('content');

        JFactory::getApplication()->triggerEvent(
            'onContentPrepare',
            array(
                'com_focalpoint.map',
                &$this->item,
                &$this->params,
                $offset
            )
        );

        // Trigger onContentPrepare for any custom map tabs
        foreach ($this->item->tabsdata->tabs as $key => &$tab) {
            if (empty($tab->name) || empty($tab->content)) {
                unset($this->item->tabsdata->tabs[$key]);
                continue;
            }

            $tab->text = $tab->content;
            JFactory::getApplication()->triggerEvent(
                'onContentPrepare',
                array(
                    'com_focalpoint.map',
                    &$tab,
                    &$this->params,
                    $offset
                )
            );

            $tab->content = $tab->text;
            unset($tab->text);
        }

        // Setup metadata
        $this->prepareDocument();

        // Scan for custom field tags in the description and replace accordingly.
        foreach ($this->item->markerdata as &$markerdata) {
            $regex = '/{(.*?)}/i';
            preg_match_all($regex, $markerdata->description, $matches, PREG_SET_ORDER);

            if (!empty($matches) && !empty($markerdata->customfields)) {
                foreach ($matches as $match) {
                    foreach ($markerdata->customfields as $name => $customfield) {
                        if ($name == $match[1]) {
                            $this->outputfield = (object)array(
                                'hidelabel' => true,
                                'data'      => $customfield->data
                            );

                            ob_start();
                            echo $this->loadTemplate('customfield_' . $customfield->datatype);
                            $output = ob_get_contents();
                            ob_end_clean();

                            $markerdata->description = str_replace($match[0], $output, $markerdata->description);
                        }

                    }
                }
            }
        }

        // Load FocalPoint Plugins. Trigger onBeforeRenderMap
        JPluginHelper::importPlugin('focalpoint');
        JFactory::getApplication()->triggerEvent('onBeforeRenderMap', array(&$this->item));

        parent::display($tpl);

        // Load FocalPoint Plugins. Trigger onAfterRenderMap
        JFactory::getApplication()->triggerEvent('onAfterRenderMap', array(&$this->item));
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
        $title = null;

        $menu = $menus->getActive();
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

        $articlemeta = ($this->item->metadata->get('metadesc'));
        if ($articlemeta) {
            $this->document->setDescription($this->item->metadata->get('metadesc'));

        } elseif ($menu) {
            if ($menu->params->get('menu-meta_description')) {
                $this->document->setDescription($this->params->get('menu-meta_description'));
            }
        }

        $articlekeywords = ($this->item->metadata->get('metakey'));
        if ($articlekeywords) {
            $this->document->setMetadata('keywords', $this->item->metadata->get('metakey'));

        } elseif ($menu) {
            if ($menu->params->get('menu-meta_keywords')) {
                $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
            }
        }

        $articlerobots = ($this->item->metadata->get('robots'));
        if ($articlerobots) {
            $this->document->setMetadata('robots', $this->item->metadata->get('robots'));

        } elseif ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

        $articlerights = ($this->item->metadata->get('rights'));
        if ($articlerights) {
            $this->document->setMetadata('rights', $this->item->metadata->get('rights'));
        }

        $articleauthor = ($this->item->metadata->get('author'));
        if ($articleauthor) {
            $this->document->setMetadata('author', $this->item->metadata->get('author'));
        }
    }

    /**
     * Turn flat array of legend markers into separate columns
     *
     * @param array $markerData
     * @param bool  $hasSubtitles A returned value for use by the caller
     *
     * @return object[]
     */
    protected function chunkLegends(array $markerData, &$hasSubtitles = null)
    {
        $column  = 0;
        $legends = array();

        $uniqueMarkers = array_filter(
            $markerData,
            function ($marker) {
                static $keys = array();
                $key = md5($marker->legendalias . $marker->locationtypealias);

                $result = !in_array($key, $keys);
                if ($result) {
                    $keys[] = $key;
                }

                return $result;
            }
        );

        // Rearrange marker array into columns
        $lastLegend   = null;
        $hasSubtitles = false;
        foreach ($uniqueMarkers as $marker) {
            if ($lastLegend && $lastLegend != $marker->legendalias) {
                $column++;
            }
            if (!isset($legends[$column])) {
                $legends[$column] = (object)array(
                    'alias'    => $marker->legendalias,
                    'title'    => $marker->legend,
                    'subtitle' => $marker->legendsubtitle,
                    'markers'  => array()
                );

                $hasSubtitles = $hasSubtitles || (bool)$marker->legendsubtitle;
            }
            $legends[$column]->markers[] = $marker;

            $lastLegend = $marker->legendalias;
        }

        return $legends;
    }
}
