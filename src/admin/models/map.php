<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

require_once __DIR__ . '/traits.php';

class FocalpointModelmap extends FocalpointModelAdmin
{
    use FocalpointModelTraits;

    /**
     * @inheritdoc
     */
    protected $text_prefix = 'COM_FOCALPOINT';

    /**
     * @inheritDoc
     */
    public function getTable($name = 'Map', $prefix = 'FocalpointTable', $options = [])
    {
        return Table::getInstance($name, $prefix, $options);
    }

    /**
     * @inheritDoc
     */
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_focalpoint.map',
            'map',
            [
                'control'   => 'jform',
                'load_data' => $loadData
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        try {
            $proForm = Form::getInstance('map.pro', 'map.pro');
            $form->load($proForm->getXml());

        } catch (Throwable $error) {
            // ignore
        }

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_focalpoint.edit.map.data', []);

        if (empty($data)) {
            $data = $this->getItem();

        }

        return $data;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function save($data)
    {
        PluginHelper::importPlugin('focalpoint');
        Factory::getApplication()->triggerEvent('onSlocmapBeforeSave', [&$data]);

        $this->checkSave2copy($data);

        return parent::save($data);
    }

    /**
     * @inheritDoc
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            $item->tabsdata = json_decode($item->tabsdata, true);
            $item->metadata = json_decode($item->metadata, true);
        }

        return $item;
    }
}
