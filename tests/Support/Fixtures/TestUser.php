<?php

/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2026 Joomlashack. All rights reserved
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

namespace Tests\Support\Fixtures;

use Joomla\CMS\User\User;

/**
 * User whose permissions are a fixed list instead of the ACL tables
 */
class TestUser extends User
{
    /**
     * @var string[] Granted actions, as "action:asset"
     */
    protected $granted = [];

    /**
     * @param int      $id
     * @param string[] $granted e.g. ['core.edit:com_focalpoint']
     */
    public function __construct(int $id = 42, array $granted = [])
    {
        parent::__construct();

        $this->id      = $id;
        $this->guest   = $id ? 0 : 1;
        $this->granted = $granted;
    }

    /**
     * @inheritDoc
     */
    public function authorise($action, $assetname = null)
    {
        return in_array($action . ':' . $assetname, $this->granted);
    }
}
