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

namespace Tests\Support;

/**
 * Loads src/install.focalpoint.php against the real ShackInstaller AbstractScript.
 *
 * The script requires library/Installer/include.php, which the build copies into the
 * package from ShackInstaller. That file also installs its own error handler, which
 * would hide deprecations from Codeception, so the AbstractScript classes are
 * autoloaded directly and a copy of the script without that require is loaded.
 */
abstract class InstallerLoader
{
    public const CLASS_NAME = 'com_focalpointInstallerScript';

    public static function load(): void
    {
        if (class_exists(static::CLASS_NAME, false)) {
            return;
        }

        $repoRoot  = dirname(__DIR__, 2);
        $installer = realpath(
            getenv('SHACK_INSTALLER_PATH') ?: $repoRoot . '/../../Builders/ShackInstaller'
        );
        $library = $installer . '/src/library/Installer';
        if ($installer === false || is_file($library . '/AbstractScript.php') == false) {
            throw new \RuntimeException(
                'ShackInstaller not found. Set SHACK_INSTALLER_PATH to the ShackInstaller repo.'
            );
        }

        if (defined('SHACK_INSTALLER_BASE') == false) {
            define('SHACK_INSTALLER_BASE', $library);
        }
        spl_autoload_register(function (string $class) use ($library) {
            $prefix = 'Alledia\\Installer\\';
            if (strpos($class, $prefix) === 0) {
                $file = $library . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                if (is_file($file)) {
                    require $file;
                }
            }
        });

        $source  = file_get_contents($repoRoot . '/src/install.focalpoint.php');
        $require = "require_once \$installPath . '/library/Installer/include.php';";
        if (strpos($source, $require) === false) {
            throw new \RuntimeException('install.focalpoint.php no longer requires the installer the expected way');
        }

        $copy = $repoRoot . '/tests/_output/install.focalpoint.php';
        file_put_contents($copy, str_replace($require, '', $source));

        require $copy;
    }
}
