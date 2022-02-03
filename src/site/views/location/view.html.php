<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2022 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();

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
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        /** @var FocalpointModelLocation $model */
        $model = $this->getModel();

        $user = Factory::getUser();

        $this->state = $model->getState();
        $this->item  = $model->getData();

        // Check for errors.
        if ($errors = $model->getErrors()) {
            throw new Exception(implode("\n", $errors));
        }

        if ($this->_layout == 'edit') {
            $authorised = $user->authorise('core.create', 'com_focalpoint');
            if ($authorised !== true) {
                throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
            }
        }

        PluginHelper::importPlugin('focalpoint');
        Factory::getApplication()->triggerEvent('onSlocmapPrepareRender', [&$this->item]);

        $this->params->merge($this->item->params);

        PluginHelper::importPlugin('content');
        $this->item->text = $this->item->description;
        Factory::getApplication()->triggerEvent(
            'onContentPrepare',
            [
                'com_focalpoint.location',
                &$this->item,
                &$this->params
            ]
        );
        $this->item->description = $this->item->text;

        $this->item->text = $this->item->fulldescription;
        Factory::getApplication()->triggerEvent(
            'onContentPrepare',
            [
                'com_focalpoint.location',
                &$this->item,
                &$this->params,
                0
            ]
        );
        $this->item->fulldescription = $this->item->text;
        unset($this->item->text);

        $this->item->description     = $this->replaceFieldTokens(
            $this->item->description,
            $this->item->customfields
        );
        $this->item->fulldescription = $this->replaceFieldTokens(
            $this->item->fulldescription,
            $this->item->customfields
        );

        $this->setDocumentTitle($this->item->title);
        $this->setDocumentMetadata($this->item->metadata);

        parent::display($tpl);
    }
}
