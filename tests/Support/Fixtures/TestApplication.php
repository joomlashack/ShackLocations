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

namespace Tests\Support\Fixtures;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\User\User;
use Joomla\DI\Container;
use Joomla\Event\Dispatcher;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Session\Session;
use Joomla\Session\Storage\RuntimeStorage;

/**
 * A real CMSApplication with the parts that need a running site (session, menus,
 * templates, the message queue) replaced by values the test controls.
 */
class TestApplication extends CMSApplication
{
    /**
     * @var array[]
     */
    public $messages = [];

    /**
     * @var array[] Events triggered, as [name, arguments]
     */
    public $events = [];

    /**
     * @var ?AbstractMenu
     */
    protected $testMenu = null;

    /**
     * @var Registry[]
     */
    protected $componentParams = [];

    public function __construct(
        string $name,
        Container $container,
        Input $input,
        ?Document $document = null,
        ?Language $language = null,
        ?User $identity = null
    ) {
        $this->name = $name;

        parent::__construct($input, new Registry(), null, $container);

        $this->setDispatcher(new Dispatcher());
        $this->document = $document;
        $this->language = $language;
        $this->loadIdentity($identity ?: new User());

        // Factory::getUser() reads the user from the session
        $session = new Session(new RuntimeStorage());
        $session->set('user', $this->getIdentity());
        $this->setSession($session);
    }

    /**
     * @inheritDoc
     */
    protected function doExecute()
    {
    }

    /**
     * @inheritDoc
     */
    public function enqueueMessage($msg, $type = self::MSG_INFO)
    {
        $this->messages[] = ['message' => $msg, 'type' => $type];
    }

    /**
     * @inheritDoc
     */
    public function getMessageQueue($clear = false)
    {
        $queue = $this->messages;
        if ($clear) {
            $this->messages = [];
        }

        return $queue;
    }

    /**
     * @inheritDoc
     */
    public function getMenu($name = null, $options = [])
    {
        return $this->testMenu;
    }

    public function setMenu(?AbstractMenu $menu): void
    {
        $this->testMenu = $menu;
    }

    /**
     * SiteApplication::getParams() equivalent
     *
     * @param string $option
     *
     * @return Registry
     */
    public function getParams($option = null)
    {
        $option = $option ?: 'com_focalpoint';
        if (isset($this->componentParams[$option]) == false) {
            $this->componentParams[$option] = new Registry();
        }

        return $this->componentParams[$option];
    }

    public function setParams(string $option, Registry $params): void
    {
        $this->componentParams[$option] = $params;
    }

    /**
     * @inheritDoc
     */
    public function getTemplate($params = false)
    {
        if ($params) {
            return (object)[
                'template'    => 'testtemplate',
                'params'      => new Registry(),
                'inheritable' => 0,
                'parent'      => '',
            ];
        }

        return 'testtemplate';
    }

    /**
     * @inheritDoc
     */
    public function triggerEvent($eventName, $args = [])
    {
        $this->events[] = [$eventName, $args];

        return parent::triggerEvent($eventName, $args);
    }
}
