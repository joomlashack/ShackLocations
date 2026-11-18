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

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;

/**
 * Language files are complete, consistent between languages, and cover every key the
 * code uses
 */
class LanguageTest extends Unit
{
    /**
     * Keys the code uses that have no string yet. Remove entries as they are added.
     */
    protected const KNOWN_MISSING = [
        // Filter field descriptions in admin/models/forms/filter_*.xml
        'COM_FOCALPOINT_FILTER_LEGEND',
        'COM_FOCALPOINT_FILTER_LOCATIONTYPES',
    ];

    /**
     * @return string
     */
    protected static function src(): string
    {
        return dirname(__DIR__, 2) . '/src';
    }

    /**
     * @return string[]
     */
    protected static function iniFiles(): array
    {
        return glob(static::src() . '/{admin,site}/language/*/*.ini', GLOB_BRACE);
    }

    /**
     * Keys, by language, then by file ('admin', 'admin.sys', 'site')
     *
     * @return array[]
     */
    protected function keys(): array
    {
        $keys = [];
        foreach (static::iniFiles() as $file) {
            preg_match('#/(admin|site)/language/([a-z]{2}-[A-Z]{2})/[^/]+?(\.sys)?\.ini$#', $file, $match);
            $keys[$match[2]][$match[1] . ($match[3] ?? '')] = array_keys(parse_ini_file($file, false, INI_SCANNER_RAW));
        }

        return $keys;
    }

    /**
     * @return string[]
     */
    protected function englishKeys(): array
    {
        return array_unique(array_merge(...array_values($this->keys()['en-GB'])));
    }

    /**
     * Literal COM_FOCALPOINT_ keys in PHP and XML, with the files that use them
     *
     * @return array[]
     */
    protected function usedKeys(): array
    {
        $used     = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(static::src()));
        foreach ($iterator as $file) {
            if (in_array($file->getExtension(), ['php', 'xml'])) {
                preg_match_all('/\bCOM_FOCALPOINT_[A-Z0-9_]+\b/', file_get_contents($file->getPathname()), $matches);
                foreach ($matches[0] as $key) {
                    $used[$key][] = substr($file->getPathname(), strlen(static::src()) + 1);
                }
            }
        }

        return $used;
    }

    public static function iniFileProvider(): array
    {
        $files = [];
        foreach (static::iniFiles() as $file) {
            $files[substr($file, strlen(static::src()) + 1)] = [$file];
        }

        return $files;
    }

    #[DataProvider('iniFileProvider')]
    public function testIniFileParsesWithoutDuplicates(string $file)
    {
        $this->assertNotFalse(parse_ini_file($file, false, INI_SCANNER_RAW), 'Invalid ini syntax');

        preg_match_all('/^([A-Z0-9_]+)\s*=/m', file_get_contents($file), $matches);
        $duplicates = array_keys(array_filter(array_count_values($matches[1]), function (int $count) {
            return $count > 1;
        }));
        $this->assertSame([], $duplicates, 'Duplicate keys; only the last one is used');
    }

    public function testTranslationsHaveTheSameKeysAsEnglish()
    {
        $keys    = $this->keys();
        $english = $keys['en-GB'];

        $this->assertGreaterThan(1, count($keys), 'Translations exist');
        foreach ($keys as $language => $files) {
            $this->assertSame(array_keys($english), array_keys($files), $language . ' has the same files');
            foreach ($files as $file => $fileKeys) {
                $missing = array_values(array_diff($english[$file], $fileKeys));
                $extra   = array_values(array_diff($fileKeys, $english[$file]));

                $this->assertSame([], $missing, "$language $file missing keys");
                $this->assertSame([], $extra, "$language $file extra keys");
            }
        }
    }

    public function testEveryKeyUsedHasAnEnglishString()
    {
        $english = $this->englishKeys();
        $missing = [];
        foreach ($this->usedKeys() as $key => $files) {
            $isPrefix = substr($key, -1) == '_';
            $isKnown  = in_array($key, $english) || in_array($key, static::KNOWN_MISSING);
            if ($isPrefix == false && $isKnown == false) {
                $missing[$key] = implode(', ', array_unique($files));
            }
        }

        // Empty state layouts are given a prefix and add their own suffixes
        foreach (['MAPS', 'LEGENDS', 'LOCATIONTYPES'] as $view) {
            unset($missing['COM_FOCALPOINT_' . $view]);
        }

        $this->assertSame([], $missing);
    }

    public function testKnownMissingKeysAreStillMissing()
    {
        $this->assertSame(
            [],
            array_values(array_intersect(static::KNOWN_MISSING, $this->englishKeys())),
            'These now have strings; remove them from KNOWN_MISSING'
        );
    }

    public function testEmptyStateStringsExist()
    {
        $english = $this->englishKeys();
        foreach (['MAPS', 'LEGENDS', 'LOCATIONTYPES'] as $view) {
            foreach (['_EMPTYSTATE_TITLE', '_EMPTYSTATE_CONTENT', '_EMPTYSTATE_BUTTON_ADD'] as $suffix) {
                $this->assertContains('COM_FOCALPOINT_' . $view . $suffix, $english);
            }
        }
    }

    public function testBuiltKeysExist()
    {
        $english = $this->englishKeys();

        foreach (['textbox', 'link', 'email', 'textarea', 'image', 'selectlist', 'multiselect'] as $type) {
            $this->assertContains('COM_FOCALPOINT_CUSTOMFIELD_TYPE_' . strtoupper($type), $english);
        }

        foreach (['MAPS', 'LEGENDS', 'LOCATIONTYPES', 'LOCATIONS'] as $plural) {
            $this->assertContains('COM_FOCALPOINT_TITLE_' . $plural, $english);
        }
        foreach (['MAP', 'LEGEND', 'LOCATIONTYPE', 'LOCATION'] as $single) {
            $this->assertContains('COM_FOCALPOINT_TITLE_' . $single . '_ADD', $english);
            $this->assertContains('COM_FOCALPOINT_TITLE_' . $single . '_EDIT', $english);
        }

        // The zoom list stops at the first missing level
        for ($zoom = 1; $zoom <= 20; $zoom++) {
            $this->assertContains(sprintf('COM_FOCALPOINT_ZOOM_%02d', $zoom), $english);
        }
    }

    public function testCustomFieldTypeListMatchesTheLayouts()
    {
        $source = file_get_contents(static::src() . '/admin/models/fields/customfields.php');
        preg_match('/\$fieldTypes\s*=\s*\[(.*?)\]/s', $source, $match);
        preg_match_all("/'(\w+)'/", $match[1], $types);

        $layouts = array_map(function (string $file) {
            return basename($file, '.php');
        }, glob(static::src() . '/site/layouts/custom/field/*.php'));

        sort($layouts);
        $types = $types[1];
        sort($types);

        $this->assertSame($types, $layouts, 'Every custom field type has a site layout');
    }
}
