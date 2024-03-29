/**
 * @package   ShackLocations
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2022-2024 Joomlashack. All rights reserved
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
(function($) {
    $.sloc = $.extend(true, {map: {instance: {}}}, $.sloc);

    $.extend(true, $.sloc, {
        /**
         * @param {string}              string
         * @param {...string|string[]}  replacements
         *
         * @return {string}
         */
        sprintf: function(string, replacements) {
            let result = string.toString().replace(/%%/g, '%');

            if (arguments.length > 2) {
                let trimmed  = Object.keys(arguments).map((key) => arguments[key]);
                replacements = trimmed.slice(1);

            } else if (replacements.constructor === String) {
                replacements = [replacements]
            }

            for (let i = 0; i < replacements.length; i++) {
                let ordered = '%' + (i + 1) + '$s';

                if (result.indexOf(ordered) === -1) {
                    result = result.replace('%s', replacements[i]);

                } else {
                    result = result.replace(ordered, replacements[i]);
                }
            }

            return result;
        },

        /**
         * @param {string}  mapId
         * @param {string=} containerId
         *
         * @return void
         */
        tabs: function(mapId, containerId) {
            if (!mapId) {
                return;
            }

            let displayTab = function(link, show) {
                show = show || false;

                let tab     = link.closest('li'),
                    content = document.querySelector(link.getAttribute('href')),
                    showId  = link.dataset.show;

                if (show) {
                    tab.classList.add('active');
                    content.style.display = 'block';
                    if (showId) {
                        document.getElementById(showId).style.display = 'block'
                    }

                } else {
                    tab.classList.remove('active');
                    content.style.display = 'none';
                    if (showId) {
                        document.getElementById(showId).style.display = 'none'
                    }
                }
            }

            $(document).ready(function() {
                containerId = containerId || '#slocTabs';

                let links = document.querySelectorAll(containerId + ' li > a');

                links.forEach(function(activeLink) {
                    activeLink.dataset.content = activeLink.getAttribute('href');

                    activeLink.addEventListener('click', function(evt) {
                        evt.preventDefault();

                        links.forEach(function(link) {
                            displayTab(link);
                        });

                        displayTab(activeLink, true);
                    });
                });
            });
        },

        map: {
            /**
             * @var {object}
             */
            destination: null,

            /**
             * @param {string} mapId
             * @param {object} map
             *
             * @return void
             */
            register: function(mapId, map) {
                $.sloc.map.instance[mapId] = map;
            },

            /**
             * @param {string} mapId
             *
             * @return {object|null}
             */
            get: function(mapId) {
                return $.sloc.map.instance[mapId] || null;
            },

            /**
             * @param {string} mapId
             *
             * @return void
             */
            update: function(mapId) {
                let instance = $.sloc.map.instance[mapId] || null;
                if (instance && instance.update && instance.update.constructor === Function) {
                    instance.update();
                }
            }
        }
    });
})(jQuery);
