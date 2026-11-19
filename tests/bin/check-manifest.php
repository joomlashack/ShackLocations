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

/*
 * Compare the manifest's site, administrator and media whitelists with what is actually
 * under src/. Files come from git (tracked, plus untracked files that aren't ignored),
 * so empty or ignored local folders don't count.
 */

$root     = dirname(__DIR__, 2);
$manifest = simplexml_load_file($root . '/src/focalpoint.xml');

if ($manifest === false) {
    fwrite(STDERR, "Unable to read src/focalpoint.xml\n");
    exit(1);
}

exec(
    'git -C ' . escapeshellarg($root) . ' ls-files --cached --others --exclude-standard -- src',
    $paths,
    $status
);
if ($status !== 0) {
    fwrite(STDERR, "Unable to list files with git\n");
    exit(1);
}

$paths = array_filter($paths, fn($path) => is_file($root . '/' . $path));

/*
 * <alledia><include> repos are copied into the package by the build, e.g. ShackDefaultFiles
 * supplies admin/form, so their files count as present. Their paths come from
 * SHACK_<NAME>_PATH, defaulting to ../../Builders/<name>.
 */
foreach ($manifest->alledia->include ?? [] as $include) {
    $name     = trim((string)$include);
    $envName  = 'SHACK_' . strtoupper(preg_replace('/^Shack/', '', $name)) . '_PATH';
    $included = realpath(getenv($envName) ?: $root . '/../../Builders/' . $name);
    if ($included === false || is_dir($included . '/src') == false) {
        fwrite(STDERR, sprintf("%s not found. Set %s to the %s repo.\n", $name, $envName, $name));
        exit(1);
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($included . '/src', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($files as $file) {
        if (strpos($file->getFilename(), '.') !== 0) {
            $paths[] = 'src/' . substr($file->getPathname(), strlen($included . '/src/'));
        }
    }
}

// Top level folders and files found under $base
$findEntries = function (string $base) use ($paths): array {
    $entries = ['folder' => [], 'filename' => []];
    foreach ($paths as $path) {
        if (strpos($path, $base) === 0) {
            $parts = explode('/', substr($path, strlen($base)), 2);
            $type  = isset($parts[1]) ? 'folder' : 'filename';

            $entries[$type][$parts[0]] = true;
        }
    }

    return array_map('array_keys', $entries);
};

// Folders and files listed in a manifest element
$listedEntries = function (?SimpleXMLElement $element): array {
    $entries = ['folder' => [], 'filename' => []];
    foreach (array_keys($entries) as $type) {
        foreach ($element ? $element->{$type} : [] as $entry) {
            $entries[$type][] = trim((string)$entry);
        }
    }

    return $entries;
};

$site  = $manifest->files;
$admin = $manifest->administration->files;
$media = $manifest->media;

$checks = [
    'src/' . ($site['folder'] ?? 'site') . '/'   => [
        'listed' => $listedEntries($site),
        'skip'   => [],
    ],
    'src/' . ($admin['folder'] ?? 'admin') . '/' => [
        'listed' => $listedEntries($admin),
        'skip'   => [],
    ],
    'src/' . ($media['folder'] ?? 'media') . '/' => [
        'listed' => $listedEntries($media),
        'skip'   => [],
    ],
];

$problems = [];
foreach ($checks as $base => $check) {
    $found = $findEntries($base);

    foreach ($found as $type => $names) {
        $missing = array_diff($names, $check['listed'][$type], $check['skip']);
        $stale   = array_diff($check['listed'][$type], $names);

        foreach ($missing as $name) {
            $problems[] = sprintf('Not in manifest: %s%s (<%s>)', $base, $name, $type);
        }
        foreach ($stale as $name) {
            $problems[] = sprintf('In manifest but not found: %s%s (<%s>)', $base, $name, $type);
        }
    }
}

// The install script must exist
$script = trim((string)$manifest->scriptfile);
if ($script && is_file($root . '/src/' . $script) == false) {
    $problems[] = 'Install script not found: src/' . $script;
}

if ($problems) {
    fwrite(STDERR, implode("\n", $problems) . "\n");
    exit(1);
}

echo "Manifest matches src/\n";
