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

namespace Tests\Admin;

use FocalpointHelper;
use Tests\Support\Fixtures\TestUser;
use Tests\Support\TestCase;

class HelperTest extends TestCase
{
    public function testGetActionsReflectsUserPermissionsOnComponent()
    {
        $this->createApplication(
            'administrator',
            [],
            new TestUser(42, ['core.manage:com_focalpoint', 'core.edit:com_focalpoint', 'core.delete:com_content'])
        );

        $actions = FocalpointHelper::getActions();

        $this->assertTrue($actions->get('core.manage'));
        $this->assertTrue($actions->get('core.edit'));
        $this->assertFalse($actions->get('core.delete'), 'Permission on another component must not leak');
        $this->assertFalse($actions->get('core.admin'));
        $this->assertFalse($actions->get('core.create'));
        $this->assertFalse($actions->get('core.edit.own'));
        $this->assertFalse($actions->get('core.edit.state'));
    }

    public function testDefaultMapCenterIsAValidCoordinate()
    {
        $this->assertEqualsWithDelta(27.66, (float)FocalpointHelper::HOME_LAT, 0.01);
        $this->assertEqualsWithDelta(-81.52, (float)FocalpointHelper::HOME_LNG, 0.01);
    }
}
