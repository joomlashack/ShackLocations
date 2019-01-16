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

use Alledia\Installer\AbstractScript;

defined('_JEXEC') or die;

// Adapt for install and uninstall environments
if (file_exists(__DIR__ . '/admin/library/Installer/AbstractScript.php')) {
    require_once __DIR__ . '/admin/library/Installer/AbstractScript.php';
} else {
    require_once __DIR__ . '/library/Installer/AbstractScript.php';
}

class com_focalpointInstallerScript extends AbstractScript
{
    /**
     * @param JInstallerAdapter $parent
     *
     * @return bool
     */
    public function update($parent)
    {
        if (parent::update($parent)) {
            echo '<p>' . JText::sprintf('Shack Locations has been successfully updated.') . '</p>';
            echo '<p><strong>Please note: If you are upgrading from a version prior to 1.2.</strong> Shack Locations / FocalPoint v1.2 included a new batch of icon markers and cluster icons. If you are upgrading from version 1.0 or 1.1 these markers can\'t be moved to your images folder without overwriting the original images/markers directory which we do not wish to do. The new markers can be found on your server in the media/com_focalpoint folder. Alternatively, you can extract the installation archive on your local machine where you can find the markers in the media folder. You are free to use these new markers as you wish. There are over 200 of them. You can upload or move them via FTP or through your hosting control panel. For new installations, the new markers have been moved to images/markers.</p>';

            return true;
        }

        return false;
    }

    /**
     * @param string            $type
     * @param JInstallerAdapter $parent
     *
     * @throws Exception
     */
    public function postflight($type, $parent)
    {
        switch ($type) {
            case 'install':
            case 'discover_install':
                $this->moveMarkers();
                break;

            case 'update':
                break;
        }

        parent::postFlight($type, $parent);
    }

    /**
     * Move the markers to the images folder (on new install only)
     */
    protected function moveMarkers()
    {
        $source      = $this->installer->getPath('source') . '/assets/markers';
        $destination = JPATH_SITE . '/images/markers';

        if (JFolder::move($source, $destination)) {
            $this->setMessage('Successully copied markers to ' . $destination, 'notice');

        } else {
            $message = array(
                '<p>Unable to move the markers folder to your /images folder. This is usaully due to;</p>',
                '<ol>',
                '<li>incorrect file permission settings. Please go to System &gt; System Information &gt; Directory Permissions and check that the images, media and tmp folders are writable.</li>',
                '<li>You already have an /images/markers folder.</li>',
                '</ol>'
            );

            $this->setMessage(join('', $message), 'notice');
        }
    }
}
