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

use Codeception\Test\Unit;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Service\Provider\CacheController;
use Joomla\CMS\Service\Provider\Form;
use Joomla\CMS\Service\Provider\HTMLRegistry;
use Joomla\CMS\Service\Provider\WebAssetRegistry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Event\Dispatcher;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Input\Input as CmsInput;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Tests\Support\Fixtures\TestApplication;
use Tests\Support\Fixtures\TestMenu;

/**
 * Base for tests that run component code inside a minimal Joomla environment:
 * a test application, an in-memory database, a document and the en-GB language.
 */
abstract class TestCase extends Unit
{
    /**
     * @var DatabaseDriver
     */
    protected $db = null;

    /**
     * @var TestApplication
     */
    protected $app = null;

    /**
     * @var Container
     */
    protected $container = null;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['HTTP_HOST']   = 'www.example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF']    = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';
        Uri::reset();

        // Everything a test writes is rolled back in tearDown()
        $this->db = TestDatabase::get();
        $this->db->transactionStart();

        $this->container = new Container();
        $this->container->registerServiceProvider(new HTMLRegistry());
        $this->container->registerServiceProvider(new WebAssetRegistry());
        $this->container->registerServiceProvider(new Form());
        $this->container->registerServiceProvider(new CacheController());
        $this->container->share(DatabaseInterface::class, $this->db);
        $this->container->share(DatabaseDriver::class, $this->db);
        $this->container->share('DatabaseDriver', $this->db);
        $dispatcher = new Dispatcher();
        $this->container->share(DispatcherInterface::class, $dispatcher);
        // Joomla 4 also looks the dispatcher up by this name
        $this->container->share('dispatcher', $dispatcher);

        // Plain, non-SEF routers so Route::_() returns the query URL it was given
        $this->container->share('SiteRouter', new Router());
        $this->container->share('AdministratorRouter', new Router());
        $routers = new \ReflectionProperty(Route::class, '_router');
        $routers->setValue(null, []);

        Factory::$container = $this->container;
        Factory::$database  = $this->db;
        Factory::$config    = new Registry(['live_site' => '', 'sef' => 0]);
        $this->container->share('config', Factory::$config);

        // No plugins installed, so PluginHelper doesn't go looking for them through the cache
        $plugins = new \ReflectionProperty(PluginHelper::class, 'plugins');
        $plugins->setValue(null, []);

        $this->setComponentParams([]);
        $this->createApplication('site');
    }

    /**
     * Global options for com_focalpoint, as ComponentHelper::getParams() returns them
     *
     * @param array $params
     *
     * @return void
     */
    protected function setComponentParams(array $params): void
    {
        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setValue(null, [
            'com_focalpoint' => new ComponentRecord([
                'id'      => 1,
                'option'  => 'com_focalpoint',
                'enabled' => 1,
                'params'  => new Registry($params),
            ]),
        ]);
    }

    protected function tearDown(): void
    {
        $this->db->transactionRollback();

        Factory::$application = null;
        Factory::$container   = null;
        Factory::$database    = null;
        Factory::$config      = null;
        Factory::$document    = null;
        Factory::$language    = null;
        Factory::$session     = null;

        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setValue(null, []);

        parent::tearDown();
    }

    /**
     * Replace the current application
     *
     * @param string $client 'site' or 'administrator'
     * @param array  $input  Request variables
     * @param ?User  $user
     *
     * @return TestApplication
     */
    protected function createApplication(string $client, array $input = [], ?User $user = null): TestApplication
    {
        $language = new Language('en-GB');
        $language->load('com_focalpoint', SLOC_ADMIN);
        if ($client == 'site') {
            // Joomla loads the component's site language when it dispatches the component
            $language->load('com_focalpoint', SLOC_SITE);
        }

        $document = new HtmlDocument();

        // Joomla 4 and 5 applications need the CMS Input subclass, which Joomla 6 removed
        $inputClass = class_exists(CmsInput::class) ? CmsInput::class : Input::class;

        $this->app = new TestApplication(
            $client,
            $this->container,
            new $inputClass($input),
            $document,
            $language,
            $user
        );
        $this->app->setMenu(new TestMenu());

        Factory::$application = $this->app;
        Factory::$language    = $language;
        Factory::$document    = $document;

        return $this->app;
    }

    /**
     * @param string $table
     * @param array  $row
     *
     * @return int
     */
    protected function insert(string $table, array $row): int
    {
        return TestDatabase::insert($this->db, $table, $row);
    }

    /**
     * Call a protected or private method
     *
     * @param object|string $target Object, or class name for a static method
     * @param string        $method
     * @param array         $args
     *
     * @return mixed
     */
    protected function invoke($target, string $method, array $args = [])
    {
        $reflection = new \ReflectionMethod($target, $method);

        return $reflection->invokeArgs(is_object($target) ? $target : null, $args);
    }

    /**
     * Create an object without running its constructor, setting the given properties
     *
     * @param string $class
     * @param array  $properties
     *
     * @return object
     */
    protected function instance(string $class, array $properties = []): object
    {
        $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        foreach ($properties as $name => $value) {
            $this->setProperty($object, $name, $value);
        }

        return $object;
    }

    /**
     * @param object $object
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    protected function setProperty(object $object, string $name, $value): void
    {
        $class = new \ReflectionClass($object);
        while ($class && $class->hasProperty($name) == false) {
            $class = $class->getParentClass();
        }

        if ($class) {
            $property = $class->getProperty($name);
            $property->setValue($object, $value);

        } else {
            $object->{$name} = $value;
        }
    }
}
