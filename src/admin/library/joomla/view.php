<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019-2023 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class FocalpointView extends HtmlView
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->app = Factory::getApplication();

        if (method_exists($this->app, 'getParams')) {
            $this->params = $this->app->getParams('com_focalpoint');

        } else {
            $this->params = ComponentHelper::getParams('com_focalpoint');
        }
    }

    /**
     * @inheritDoc
     */
    public function setDocumentTitle($title = null)
    {
        if (method_exists(parent::class, 'setDocumentTitle')) {
            parent::setDocumentTitle($this->params->get('page_title') ?: $title);
        }
    }

    /**
     * @param ?string $default
     *
     * @return string
     */
    protected function getPageHeading(?string $default = ''): string
    {
        if ($this->params->get('show_page_heading')) {
            return $this->params->get('page_heading') ?: $default;
        }

        return '';
    }

    /**
     * @param string $base
     *
     * @return string
     */
    protected function getPageClass(string $base = ''): string
    {
        $suffix = (string)$this->params->get('pageclass_sfx');

        return trim($base . ' ' . $suffix);
    }

    /**
     * @param mixed $defaults
     *
     * @return void
     */
    protected function setDocumentMetadata($defaults)
    {
        if (!$defaults instanceof Registry) {
            $defaults = new Registry($defaults);
        }

        $description = $this->params->get('menu-meta_description') ?: $defaults->get('metadesc');
        if ($description) {
            $this->document->setDescription($description);
        }

        $keywords = $this->params->get('menu-meta_keywords') ?: $defaults->get('metakey');
        if ($keywords) {
            $this->document->setMetaData('keywords', $keywords);
        }

        $robots = $this->params->get('robots') ?: $defaults->get('robots');
        if ($robots) {
            $this->document->setMetaData('robots', $robots);
        }

        $rights = $defaults->get('rights');
        if ($rights) {
            $this->document->setMetaData('rights', $rights);
        }

        $author = $defaults->get('author');
        if ($author) {
            $this->document->setMetaData('author', $author);
        }
    }
}
