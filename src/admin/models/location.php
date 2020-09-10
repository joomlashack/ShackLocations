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

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

class FocalpointModellocation extends JModelAdmin
{
    protected $text_prefix = 'COM_FOCALPOINT';

    /**
     * @var CMSObject
     */
    protected $item = null;

    public function getTable($type = 'Location', $prefix = 'FocalpointTable', $config = [])
    {
        return Table::getInstance($type, $prefix, $config);
    }

    /**
     * @inheritDoc
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_focalpoint.location',
            'location',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return $form;
    }

    /**
     * @inheritDoc
     */
    protected function loadFormData()
    {
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_focalpoint.edit.location.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            $item->description = trim($item->fulldescription) == ''
                ? $item->description
                : $item->description . '<hr id="system-readmore" />' . $item->fulldescription;

            $item->metadata         = json_decode($item->metadata, true);
            $item->othertypes       = json_decode($item->othertypes, true);
            $item->customfieldsdata = json_decode($item->customfieldsdata, true);
        }

        if (empty($item->id)) {
            $item->created_by = Factory::getUser()->id;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    protected function prepareTable($table)
    {
        $table->alias = OutputFilter::stringURLSafe($table->alias ?: $table->title);

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
     * @inheritDoc
     */
    public function save($data)
    {
        $app = JFactory::getApplication();

        if (empty($data['othertypes'])) {
            $data['othertypes'] = [];
        }

        if ($app->input->getCmd('task') == 'save2copy') {
            $original = clone $this->getTable();
            $original->load($app->input->getInt('id'));

            if ($data['title'] == $original->title) {
                list($title, $alias) = $this->generateNewTitle($data['type'], $data['alias'], $data['title']);

                $data['title'] = $title;
                $data['alias'] = $alias;

            } else {
                if ($data['alias'] == $original->alias) {
                    $data['alias'] = '';
                }
            }

            $data['state'] = 0;

        }

        if (parent::save($data)) {
            $id = $data['id'] ?: $this->getDbo()->insertid();
            $this->updateTypes($id, $data);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function validate($form, $data, $group = null)
    {
        if (parent::validate($form, $data, $group)) {
            switch ($data['linktype']) {
                case 2:
                    if (empty($data['maplinkid'])) {
                        $this->setError(Text::_('COM_FOCALPOINT_ERROR_LINKTYPE_MAPLINKID_REQUIRED'));
                        return false;
                    }
                    break;

                case 4:
                    // Menu link
                    if (empty($data['menulink'])) {
                        $this->setError(Text::_('COM_FOCALPOINT_ERROR_LINKTYPE_MENULINK_REQUIRED'));
                        return false;
                    }
                    break;

            }

            return $data;
        }

        return false;
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return void
     */
    protected function updateTypes($id, array $data)
    {
        $db = $this->getDbo();

        // Remove existing xrefs
        $db->setQuery(
            $db->getQuery(true)
                ->delete('#__focalpoint_location_type_xref')
                ->where('location_id = ' . $id)
        )
            ->execute();

        // normalize/filter selected ids between type and othertypes
        $typeIds    = array_merge(
            [$data['type']],
            empty($data['othertypes']) ? [] : $data['othertypes']
        );
        $typeValues = array_map(
            function ($typeId) use ($id) {
                return sprintf('(%s, %s)', $id, $typeId);
            },
            array_filter(array_unique($typeIds))
        );

        // Insert the new xrefs
        $db->setQuery(
            'INSERT #__focalpoint_location_type_xref '
            . ' (location_id, locationtype_id)'
            . ' VALUES ' . join(',', $typeValues)
        )
            ->execute();
    }
}
