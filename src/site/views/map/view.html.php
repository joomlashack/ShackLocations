<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2020 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class FocalpointViewMap extends FocalpointViewSite
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
        $app = Factory::getApplication();

        /** @var FocalpointModelMap $model */
        $model = $this->getModel();

        $this->state = $model->getState();
        $this->item  = $model->getData();

        // Check for errors.
        if ($errors = $model->getErrors()) {
            throw new Exception(implode("\n", $errors));
        }

        // Load FocalPoint Plugins. Trigger onBeforeMapPrepareRender
        PluginHelper::importPlugin('focalpoint');
        $app->triggerEvent('onBeforeMapPrepareRender', [&$this->item]);

        $offset = $this->state->get('list.offset');
        PluginHelper::importPlugin('content');

        $this->params->merge($this->item->params);

        PluginHelper::importPlugin('content');
        $app->triggerEvent(
            'onContentPrepare',
            [
                'com_focalpoint.map',
                &$this->item,
                &$this->params,
                $offset
            ]
        );

        // Trigger onContentPrepare for any custom map tabs
        foreach ($this->item->tabsdata->tabs as $key => &$tab) {
            if (empty($tab->name) || empty($tab->content)) {
                unset($this->item->tabsdata->tabs[$key]);
                continue;
            }

            $tab->text = $tab->content;
            $app->triggerEvent(
                'onContentPrepare',
                [
                    'com_focalpoint.map',
                    &$tab,
                    &$this->params,
                    $offset
                ]
            );

            $tab->content = $tab->text;
            unset($tab->text);
        }

        $this->setDocumentTitle($this->item->title);
        $this->setDocumentMetadata($this->item->metadata);

        // Scan for custom field tags in the description and replace accordingly.
        foreach ($this->item->markerdata as &$markerdata) {
            $regex = '/{(.*?)}/i';
            preg_match_all($regex, $markerdata->description, $matches, PREG_SET_ORDER);

            if (!empty($matches) && !empty($markerdata->customfields)) {
                foreach ($matches as $match) {
                    foreach ($markerdata->customfields as $name => $customfield) {
                        if ($name == $match[1]) {
                            $this->outputfield = (object)[
                                'hidelabel' => true,
                                'data'      => $customfield->data
                            ];

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
        PluginHelper::importPlugin('focalpoint');
        $app->triggerEvent('onBeforeRenderMap', [&$this->item]);

        parent::display($tpl);

        // Load FocalPoint Plugins. Trigger onAfterRenderMap
        $app->triggerEvent('onAfterRenderMap', [&$this->item]);
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
        $legends = [];

        $uniqueMarkers = array_filter(
            $markerData,
            function ($marker) {
                static $keys = [];
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
                $legends[$column] = (object)[
                    'alias'    => $marker->legendalias,
                    'title'    => $marker->legend,
                    'subtitle' => $marker->legendsubtitle,
                    'markers'  => []
                ];

                $hasSubtitles = $hasSubtitles || (bool)$marker->legendsubtitle;
            }
            $legends[$column]->markers[] = $marker;

            $lastLegend = $marker->legendalias;
        }

        return $legends;
    }
}
