<?php
/**
 * @version     1.0.0
 * @package     com_focalpoint
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      2013-2017 - John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @author      2018 - Joomlashack <help@joomlashack.com> - https://www.joomlashack.com
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
        parent::postFlight($type, $parent);

        //Move the markers to the images folder on new install only
        if ($type == 'install') {
            $markers_moved = JFile::move(JPATH_SITE . "/media/com_focalpoint/markers", JPATH_SITE . "/images/markers");
            if ($markers_moved) {
                echo "<p>Successully moved markers to " . JPATH_SITE . "/images/markers/.";
            } else {
                echo "<p>Unable to move the markers folder to your /images folder. This is usaully due to;</p><ol><li>incorrect file permission settings. Please go to System > System Information > Directory Permissions and check that the images, media and tmp folders are writable.</li><li>You already have an /images/markers folder.</li></ol> ";
            }
        }
    }
}
