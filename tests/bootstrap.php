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

use Alledia\Framework\AutoLoader;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects

/*
 * The component runs against real Joomla and Joomlashack framework classes taken from
 * local checkouts. Override the defaults (the same paths PhpStorm uses) with:
 *   JOOMLA_PATH           Joomla 4, 5 or 6 source tree
 *   SHACK_FRAMEWORK_PATH  AllediaFramework repository
 */
$repoRoot  = dirname(__DIR__);
$joomla    = realpath(getenv('JOOMLA_PATH') ?: $repoRoot . '/../../../../Common/Joomla.6');
$framework = realpath(getenv('SHACK_FRAMEWORK_PATH') ?: $repoRoot . '/../../Builders/AllediaFramework');

if ($joomla === false || is_file($joomla . '/libraries/src/Version.php') == false) {
    fwrite(STDERR, "Joomla source not found. Set JOOMLA_PATH to a Joomla 4, 5 or 6 checkout.\n");
    exit(1);
}
if ($framework === false || is_file($framework . '/src/Framework/AutoLoader.php') == false) {
    fwrite(STDERR, "Joomlashack framework not found. Set SHACK_FRAMEWORK_PATH to the AllediaFramework repo.\n");
    exit(1);
}

/*
 * Build a throwaway site root laid out like an installed site, so path constants,
 * layouts and include paths resolve exactly as they do in Joomla.
 */
$site = __DIR__ . '/_output/site';
if (is_link($site . '/libraries')) {
    unlink($site . '/libraries');
}
$links = [
    'libraries/allediaframework'             => $framework . '/src',
    'layouts'                                => $joomla . '/layouts',
    'language'                               => $joomla . '/language',
    'administrator/language'                 => $joomla . '/administrator/language',
    'components/com_focalpoint'              => $repoRoot . '/src/site',
    'administrator/components/com_focalpoint' => $repoRoot . '/src/admin',
    'media/com_focalpoint'                   => $repoRoot . '/src/media',
];
if (is_link($site . '/media')) {
    unlink($site . '/media');
}
foreach (array_diff(scandir($joomla . '/media'), ['.', '..', 'com_focalpoint']) as $entry) {
    $links['media/' . $entry] = $joomla . '/media/' . $entry;
}
foreach (array_diff(scandir($joomla . '/libraries'), ['.', '..']) as $entry) {
    $links['libraries/' . $entry] = $joomla . '/libraries/' . $entry;
}
foreach ($links as $link => $target) {
    $path = $site . '/' . $link;
    if (is_link($path) && readlink($path) !== $target) {
        unlink($path);
    }
    if (file_exists($path) == false) {
        if (is_dir(dirname($path)) == false) {
            mkdir(dirname($path), 0777, true);
        }
        symlink($target, $path);
    }
}
foreach (['cache', 'images', 'tmp', 'logs', 'templates'] as $folder) {
    if (is_dir($site . '/' . $folder) == false) {
        mkdir($site . '/' . $folder, 0777, true);
    }
}

define('_JEXEC', 1);
define('JDEBUG', false);
define('JPATH_BASE', $site);
define('JPATH_ROOT', $site);
define('JPATH_SITE', $site);
define('JPATH_PUBLIC', $site);
define('JPATH_ADMINISTRATOR', $site . '/administrator');
define('JPATH_API', $site . '/api');
define('JPATH_CLI', $site . '/cli');
define('JPATH_LIBRARIES', $site . '/libraries');
define('JPATH_PLATFORM', JPATH_LIBRARIES);
define('JPATH_PLUGINS', $site . '/plugins');
define('JPATH_INSTALLATION', $site . '/installation');
define('JPATH_THEMES', $site . '/templates');
define('JPATH_CACHE', $site . '/cache');
define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
define('JPATH_CONFIGURATION', $site);

/*
 * Joomla's vendor packages load after tests/vendor's, so where both ship a package
 * (Symfony in particular) Codeception keeps the version it was installed with.
 * Joomla's whole vendor autoloader isn't used for the same reason.
 *
 * PSR interfaces are the exception: Joomla's classes implement the versions Joomla
 * ships (Joomla 5 has psr/container 1, Codeception installs 2), and Symfony's
 * implementations accept either, so Joomla's copies load first.
 */
$joomlaPsr4 = require $joomla . '/libraries/vendor/composer/autoload_psr4.php';
$joomlaPsr4 = array_diff_key($joomlaPsr4, array_flip(['Joomla\\Tests\\', 'Joomla\\Test\\', 'Joomla\\PHPStan\\']));
$psr4Loader = function (array $map): Closure {
    return function (string $class) use ($map) {
        foreach ($map as $prefix => $paths) {
            if (strpos($class, $prefix) === 0) {
                $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                foreach ($paths as $path) {
                    if (is_file($path . '/' . $relative)) {
                        require $path . '/' . $relative;

                        return;
                    }
                }
            }
        }
    };
};
$isPsr = function (string $prefix): bool {
    return strpos($prefix, 'Psr\\') === 0;
};
spl_autoload_register($psr4Loader(array_filter($joomlaPsr4, $isPsr, ARRAY_FILTER_USE_KEY)), true, true);
spl_autoload_register(
    $psr4Loader(array_filter($joomlaPsr4, fn($prefix) => $isPsr($prefix) == false, ARRAY_FILTER_USE_KEY))
);

// Function files from Joomla's own packages, e.g. joomla/string's phputf8 in Joomla 4 and 5
$joomlaFiles = require $joomla . '/libraries/vendor/composer/autoload_files.php';
foreach ($joomlaFiles as $file) {
    if (strpos($file, '/vendor/joomla/') !== false) {
        require_once $file;
    }
}

require_once JPATH_LIBRARIES . '/loader.php';
JLoader::setup();

// Joomla 4's legacy class aliases (JHtml, JTableExtension, ...), which Joomla 5 dropped
if (is_file(JPATH_LIBRARIES . '/classmap.php')) {
    require_once JPATH_LIBRARIES . '/classmap.php';
}

require_once $framework . '/src/Framework/AutoLoader.php';
AutoLoader::register('Alledia\\Framework', $framework . '/src/Framework');
define('ALLEDIA_FRAMEWORK_LOADED', 1);

// What admin/include.php sets up when the component boots
define('SLOC_ADMIN', JPATH_ADMINISTRATOR . '/components/com_focalpoint');
define('SLOC_SITE', JPATH_SITE . '/components/com_focalpoint');
define('SLOC_LIBRARY', SLOC_ADMIN . '/library');
define('SLOC_LOADED', 1);

require_once SLOC_ADMIN . '/helpers/focalpoint.php';
AutoLoader::registerCamelBase('Focalpoint', SLOC_LIBRARY . '/joomla');
HTMLHelper::addIncludePath(SLOC_LIBRARY . '/html');
