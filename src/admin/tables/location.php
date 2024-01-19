<?php
/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2013-2017 John Pitchers <john@viperfish.com.au> - http://viperfish.com.au
 * @copyright 2018-2024 Joomlashack. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore

class FocalpointTablelocation extends FocalpointTable
{
    /**
     * @inheritdoc
     */
    protected $_jsonEncode = [
        'metadata',
        'params',
        'othertypes',
        'customfieldsdata',
    ];

    /**
     * @inheritdoc
     */
    protected $_columnAlias = [
        'published' => 'state',
        'catid'     => 'type',
    ];

    /**
     * @inheritDoc
     */
    public function __construct(&$db)
    {
        parent::__construct('#__focalpoint_locations', 'id', $db);
    }
}
