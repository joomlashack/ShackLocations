<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2023 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

require_once __DIR__ . '/traits.php';

// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class FocalpointModellocation extends FocalpointModelAdmin
{
    use FocalpointModelTraits;

    /**
     * @inheritdoc
     */
    protected $text_prefix = 'COM_FOCALPOINT';

    /**
     * @inheritDoc
     */
    public function getTable($name = 'Location', $prefix = 'FocalpointTable', $options = [])
    {
        return Table::getInstance($name, $prefix, $options);
    }

    /**
     * @inheritDoc
     */
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_focalpoint.location',
            'location',
            [
                'control'   => 'jform',
                'load_data' => $loadData
            ]
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function loadFormData()
    {
        $app  = Factory::getApplication();
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
            $item->description = trim((string)$item->get('fulldescription')) == ''
                ? $item->description
                : $item->description . '<hr id="system-readmore" />' . $item->get('fulldescription');

            $item->metadata         = json_decode((string)$item->get('metadata'), true);
            $item->othertypes       = json_decode((string)$item->get('othertypes'), true);
            $item->customfieldsdata = json_decode((string)$item->get('customfieldsdata'), true);
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    protected function prepareTable($table)
    {
        parent::prepareTable($table);

        $parts = preg_split('#(<hr\s+id="system-readmore"\s*/>)#', $table->get('description'));
        if (count($parts) == 2) {
            $table->fulldescription = trim(array_pop($parts));
            $table->description     = trim(array_pop($parts));

        } else {
            $table->fulldescription = '';
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function save($data)
    {
        $data['othertypes']       = $data['othertypes'] ?? [];
        $data['customfieldsdata'] = $data['customfieldsdata'] ?? '{}';

        $this->checkSave2copy($data);

        if (parent::save($data)) {
            $id = $data['id'] ?: $this->getState('location.id');

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
    protected function updateTypes(int $id, array $data): void
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

    /**
     * @inheritDoc
     */
    protected function getReorderConditions($table)
    {
        return ['map_id = ' . (int)$table->map_id];
    }
}
