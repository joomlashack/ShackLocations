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
 * jQuery.sloc.map.google.clusterRenderer() from media/js/googleMap.js, which draws marker
 * clusters (added by the Pro marker clusters plugin) with the plugin's icons. Each test
 * runs against both the source and the committed googleMap.min.js.
 */

import {describe, it} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import vm from 'node:vm';

const jsFolder = fileURLToPath(new URL('../../src/media/js/', import.meta.url));

const styles = [
    {url: '/plugins/focalpoint/markerclusters/assets/images/m1.png', width: 53, height: 52, textColor: '#000', textSize: 14, fontWeight: 'bold'},
    {url: '/plugins/focalpoint/markerclusters/assets/images/m2.png', width: 56, height: 55, textColor: '#fff', textSize: 12, fontWeight: 'normal'},
    {url: '/plugins/focalpoint/markerclusters/assets/images/m3.png', width: 66, height: 65}
];

/**
 * @param {string} file
 *
 * @return {function} jQuery.sloc.map.google.clusterRenderer
 */
function loadRenderer(file) {
    const jQuery  = {extend: (deep, target, ...sources) => Object.assign(target, ...sources)};
    const google  = {
        maps: {
            Marker: class {
                constructor(options) {
                    this.options = options;
                }
            },
            Size  : class {
                constructor(width, height) {
                    Object.assign(this, {width, height});
                }
            },
            Point : class {
                constructor(x, y) {
                    Object.assign(this, {x, y});
                }
            }
        }
    };
    google.maps.Marker.MAX_ZINDEX = 1000000;

    const context = vm.createContext({jQuery, google});
    vm.runInContext(readFileSync(jsFolder + file, 'utf8'), context, {filename: file});

    return jQuery.sloc.map.google.clusterRenderer;
}

for (const file of ['googleMap.js', 'googleMap.min.js']) {
    describe(file + ' clusterRenderer', () => {
        const render = (count, rendererStyles = styles) => loadRenderer(file)(rendererStyles)
            .render({count, position: {lat: 27.5, lng: -82.25}})
            .options;

        it('draws the cluster at its position with its count', () => {
            const marker = render(7);

            assert.deepEqual({...marker.position}, {lat: 27.5, lng: -82.25});
            assert.equal(marker.label.text, '7');
            assert.equal(marker.zIndex, 1000007, 'Larger clusters on top');
        });

        it('chooses the icon by the number of digits in the count', () => {
            assert.equal(render(9).icon.url, styles[0].url);
            assert.equal(render(10).icon.url, styles[1].url);
            assert.equal(render(99).icon.url, styles[1].url);
            assert.equal(render(100).icon.url, styles[2].url);
            assert.equal(render(12345).icon.url, styles[2].url, 'The last icon for anything larger');
        });

        it('centers the icon on the cluster', () => {
            const icon = render(25).icon;

            assert.deepEqual({...icon.size}, {width: 56, height: 55});
            assert.deepEqual({...icon.anchor}, {x: 28, y: 27.5});
        });

        it('styles the count', () => {
            assert.deepEqual({...render(25).label}, {text: '25', color: '#fff', fontSize: '12px', fontWeight: 'normal'});
            assert.deepEqual(
                {...render(250).label},
                {text: '250', color: '#000', fontSize: '14px', fontWeight: 'bold'},
                'Defaults'
            );
        });

        it('works with a single icon', () => {
            assert.equal(render(500, [styles[1]]).icon.url, styles[1].url);
        });
    });
}
