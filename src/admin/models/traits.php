<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020-2021 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Factory;
use Joomla\String\StringHelper;

defined('_JEXEC') or die();

trait FocalpointModelTraits
{
    /**
     * Handle situations where category doesn't apply.
     * We're overriding a parent method to (hopefully) avoid confusion
     *
     * @inheritDoc
     *
     * @param int    $categoryId
     * @param string $alias
     * @param string $title
     *
     * @return array
     * @throws Exception
     */
    protected function generateNewTitle($categoryId, $alias, $title)
    {
        if ($categoryId) {
            return parent::generateNewTitle($categoryId, $alias, $title);
        }

        $table      = $this->getTable();
        $aliasField = $table->getColumnAlias('alias');
        $titleField = $table->getColumnAlias('title');

        while ($table->load([$aliasField => $alias])) {
            if ($title === $table->$titleField) {
                $title = StringHelper::increment($title);
            }

            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }

    /**
     * Expects $data to have title and alias array keys and updates
     * them to avoid title/alias clashes with previously saved items.
     *
     * @param array $data
     * @param int   $categoryId
     *
     * @return void
     * @throws Exception
     */
    protected function checkSave2copy(&$data, $categoryId = null)
    {
        $app = Factory::getApplication();
        if ($app->input->getCmd('task') == 'save2copy') {
            $original = clone $this->getTable();
            $original->load($app->input->getInt('id'));

            if ($data['title'] == $original->title) {
                list($title, $alias) = $this->generateNewTitle($categoryId, $data['alias'], $data['title']);

                $data['title'] = $title;
                $data['alias'] = $alias;

            } elseif ($data['alias'] == $original->alias) {
                $data['alias'] = '';
            }

            $data['state'] = 0;
        }
    }
}
