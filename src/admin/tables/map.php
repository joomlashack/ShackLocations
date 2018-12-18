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

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Asset;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

class FocalpointTablemap extends JTable
{
    protected $_jsonEncode = array(
        'tabsdata',
        'metadata',
        'params'
    );

    public function __construct(&$db)
    {
        parent::__construct('#__focalpoint_maps', 'id', $db);
    }

    /**
     * @param array|object $array
     * @param string       $ignore
     *
     * @return bool
     * @throws Exception
     */
    public function bind($array, $ignore = '')
    {
        $input = JFactory::getApplication()->input;
        $task  = $input->getCmd('task', '');
        if (($task == 'save' || $task == 'apply')
            && (!JFactory::getUser()->authorise('core.edit.state', 'com_focalpoint') && $array['state'] == 1)
        ) {
            $array['state'] = 0;
        }

        if (!JFactory::getUser()->authorise('core.admin', 'com_focalpoint.map.' . $array['id'])) {
            $actions        = JFactory::getACL()->getActions('com_focalpoint', 'map');
            $defaultActions = JFactory::getACL()->getAssetRules('com_focalpoint.map.' . $array['id'])->getData();
            $access         = array();
            foreach ($actions as $action) {
                $access[$action->name] = $defaultActions[$action->name];
            }
            $array['rules'] = $this->JAccessRulestoArray($access);
        }

        if (isset($array['rules']) && is_array($array['rules'])) {
            $this->setRules($array['rules']);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * This function convert an array of JAccessRule objects into an rules array.
     *
     * @param Rules $accessRules
     *
     * @return array
     */
    private function JAccessRulestoArray($accessRules)
    {
        $rules = array();
        foreach ($accessRules as $action => $access) {
            $actions = array();
            foreach ($access->getData() as $group => $allow) {
                $actions[$group] = ((bool)$allow);
            }
            $rules[$action] = $actions;
        }
        return $rules;
    }

    /**
     * @return bool
     */
    public function check()
    {

        //If there is an ordering column and this is a new row then get the next ordering value
        if (!$this->ordering && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }

        return parent::check();
    }

    /**
     * @return string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_focalpoint.map.' . (int)$this->$k;
    }

    /**
     * @param JTable|null $table
     * @param null        $id
     *
     * @return int
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        /** @var Asset $assetParent */
        $assetParent   = JTable::getInstance('Asset');
        $assetParentId = $assetParent->getRootId();
        $assetParent->loadByName('com_focalpoint');
        if ($assetParent->id) {
            $assetParentId = $assetParent->id;
        }

        return $assetParentId;
    }
}
