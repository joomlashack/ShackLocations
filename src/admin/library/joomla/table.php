<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021-2023 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

abstract class FocalpointTable extends Table
{
    /**
     * @inheritDoc
     */
    public function bind($src, $ignore = [])
    {
        if (parent::bind($src, $ignore)) {
            if (property_exists($this, 'alias') && property_exists($this, 'title')) {
                if (empty($this->alias) && $this->title) {
                    $this->alias = $this->title;
                }
                $this->alias = ApplicationHelper::stringURLSafe($this->alias);
            }

            return true;
        }

        return false;
    }
}
