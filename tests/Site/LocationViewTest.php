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

namespace Tests\Site;

use FocalpointModelLocation;
use FocalpointViewLocation;
use Joomla\CMS\Language\Text;
use Tests\Support\TestCase;

class LocationViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication('site', ['option' => 'com_focalpoint']);

        if (defined('JPATH_COMPONENT_ADMINISTRATOR') == false) {
            define('JPATH_COMPONENT_ADMINISTRATOR', SLOC_ADMIN);
        }
        require_once SLOC_SITE . '/models/location.php';
        require_once SLOC_SITE . '/views/location/view.html.php';
    }

    public function testMissingLocationIsNotFound()
    {
        $this->assertNotFound(999);
    }

    public function testUnpublishedLocationIsNotFound()
    {
        $this->assertNotFound($this->insert('focalpoint_locations', ['title' => 'Closed', 'state' => 0]));
    }

    /**
     * @param int $id
     *
     * @return void
     */
    protected function assertNotFound(int $id): void
    {
        $this->createApplication('site', ['option' => 'com_focalpoint', 'id' => $id]);

        $view = new FocalpointViewLocation(['name' => 'location', 'base_path' => SLOC_SITE]);
        $view->setDocument($this->app->getDocument());
        $view->setModel(new FocalpointModelLocation(['dbo' => $this->db]), true);

        try {
            $view->display();
            $this->fail('No exception');

        } catch (\Exception $error) {
            $this->assertSame(404, $error->getCode());
            $this->assertSame(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), $error->getMessage());
        }
    }
}
