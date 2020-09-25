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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

FormHelper::loadFieldClass('List');

class ShacklocationsFormFieldLegend extends JFormFieldList
{
    /**
     * @inheritdoc
     */
    public $type = 'legend';

    /**
     * @var object[]
     */
    protected $options = null;

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('id,title')
                ->from($db->quoteName('#__focalpoint_legends'))
                ->where($db->quoteName('state') . ' > -1');

            $this->options = [];

            $legends = $db->setQuery($query)->loadObjectList();
            foreach ($legends as $legend) {
                $this->options[] = HTMLHelper::_('select.option', $legend->id, $legend->title);
            }

        }

        return array_merge(parent::getOptions(), $this->options);
    }
}
