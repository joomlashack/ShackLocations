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

use Joomla\Database\DatabaseDriver;
use Joomla\Database\Mysqli\MysqliDriver;

/**
 * MySQL test database built from the component's real install.sql, so the tests fail
 * if code and schema drift apart. It is rebuilt once per test run, and each test runs
 * inside a transaction that TestCase rolls back.
 *
 * Connection settings come from the environment:
 *   SLOC_TEST_DB_HOST      default localhost
 *   SLOC_TEST_DB_USER      default root
 *   SLOC_TEST_DB_PASSWORD  default empty
 *   SLOC_TEST_DB_NAME      default shacklocations_test (dropped and recreated each run)
 */
abstract class TestDatabase
{
    public const PREFIX = 'jos_';

    /**
     * Core Joomla tables the component reads, reduced to the columns it and Joomla's
     * tables need
     */
    protected const CORE_SCHEMA = <<<SQL
CREATE TABLE `#__extensions` (
  `extension_id` INT NOT NULL AUTO_INCREMENT,
  `package_id` INT NOT NULL DEFAULT 0,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(20) NOT NULL,
  `element` VARCHAR(100) NOT NULL,
  `changelogurl` TEXT,
  `folder` VARCHAR(100) NOT NULL DEFAULT '',
  `client_id` TINYINT NOT NULL DEFAULT 0,
  `enabled` TINYINT NOT NULL DEFAULT 1,
  `access` INT UNSIGNED NOT NULL DEFAULT 1,
  `protected` TINYINT NOT NULL DEFAULT 0,
  `locked` TINYINT NOT NULL DEFAULT 0,
  `manifest_cache` TEXT NOT NULL,
  `params` TEXT NOT NULL,
  `custom_data` TEXT NOT NULL,
  `checked_out` INT UNSIGNED,
  `checked_out_time` DATETIME,
  `ordering` INT DEFAULT 0,
  `state` INT DEFAULT 0,
  `note` VARCHAR(255),
  PRIMARY KEY (`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `#__menu` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `menutype` VARCHAR(24) NOT NULL DEFAULT 'mainmenu',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `link` VARCHAR(1024) NOT NULL,
  `type` VARCHAR(16) NOT NULL DEFAULT 'component',
  `published` TINYINT NOT NULL DEFAULT 1,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

    /**
     * @var ?DatabaseDriver
     */
    protected static $db = null;

    /**
     * The shared connection, building the database on first use
     *
     * @return DatabaseDriver
     */
    public static function get(): DatabaseDriver
    {
        if (static::$db === null) {
            $name = getenv('SLOC_TEST_DB_NAME') ?: 'shacklocations_test';
            if (preg_match('/^\w+$/', $name) == false) {
                throw new \RuntimeException('Invalid SLOC_TEST_DB_NAME: ' . $name);
            }

            $db = new MysqliDriver([
                'driver'   => 'mysqli',
                'host'     => getenv('SLOC_TEST_DB_HOST') ?: 'localhost',
                'user'     => getenv('SLOC_TEST_DB_USER') ?: 'root',
                'password' => (string)getenv('SLOC_TEST_DB_PASSWORD'),
                'database' => '',
                'prefix'   => static::PREFIX,
            ]);

            $db->setQuery('DROP DATABASE IF EXISTS ' . $db->quoteName($name))->execute();
            $db->setQuery('CREATE DATABASE ' . $db->quoteName($name) . ' CHARACTER SET utf8mb4')->execute();
            $db->select($name);

            $installSql = file_get_contents(dirname(__DIR__, 2) . '/src/admin/sql/install/mysql/install.sql');
            foreach (DatabaseDriver::splitSql($installSql . "\n" . static::CORE_SCHEMA) as $statement) {
                if (trim($statement) !== '') {
                    $db->setQuery($statement)->execute();
                }
            }

            static::$db = $db;
        }

        return static::$db;
    }

    /**
     * Insert a row, filling NOT NULL columns the test doesn't care about
     *
     * @param DatabaseDriver $db
     * @param string         $table Without prefix, e.g. 'focalpoint_maps'
     * @param array          $row
     *
     * @return int The new id
     */
    public static function insert(DatabaseDriver $db, string $table, array $row): int
    {
        $defaults = [
            'focalpoint_maps'          => [
                'title'     => 'Map',
                'alias'     => 'map',
                'text'      => '',
                'tabsdata'  => '{}',
                'latitude'  => 0,
                'longitude' => 0,
                'metadata'  => '{}',
                'params'    => '{}',
            ],
            'focalpoint_legends'       => [
                'title'    => 'Legend',
                'alias'    => 'legend',
                'subtitle' => '',
            ],
            'focalpoint_locationtypes' => [
                'title'        => 'Type',
                'alias'        => 'type',
                'marker'       => '',
                'legend'       => 0,
                'customfields' => '',
            ],
            'focalpoint_locations'     => [
                'title'            => 'Location',
                'map_id'           => 0,
                'type'             => 0,
                'othertypes'       => '',
                'alias'            => 'location',
                'description'      => '',
                'fulldescription'  => '',
                'image'            => '',
                'latitude'         => 0,
                'longitude'        => 0,
                'marker'           => '',
                'customfieldsdata' => '',
                'params'           => '{}',
                'metadata'         => '{}',
            ],
            'extensions'               => [
                'type'           => 'component',
                'element'        => 'com_focalpoint',
                'name'           => 'com_focalpoint',
                'manifest_cache' => '',
                'params'         => '{}',
                'custom_data'    => '',
            ],
            'menu'                     => [
                'params' => '{}',
            ],
        ];

        $object = (object)array_merge($defaults[$table] ?? [], $row);
        $key    = $table == 'extensions' ? 'extension_id' : 'id';

        $db->insertObject('#__' . $table, $object, $key);

        return (int)$object->{$key};
    }
}
