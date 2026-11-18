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
 * Run with: node --test tests/js
 *
 * jQuery.sloc helpers from media/js/sloc.js. Each test runs against both the source
 * and the committed sloc.min.js, so a stale minified file fails too.
 */

import {describe, it} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import vm from 'node:vm';

const jsFolder = fileURLToPath(new URL('../../src/media/js/', import.meta.url));

/**
 * Just enough of jQuery for sloc.js: $.extend() (deep) and $(document).ready()
 */
function createJQuery() {
    // Like jQuery, only plain objects are merged; anything else is copied by reference
    // (from any realm: the page's objects come from the vm context)
    const isObject = (value) => {
        if (value === null || typeof value !== 'object') {
            return false;
        }
        const proto = Object.getPrototypeOf(value);

        return proto === null || Object.getPrototypeOf(proto) === null;
    };

    const extend = (deep, target, ...sources) => {
        for (const source of sources) {
            for (const [key, value] of Object.entries(source || {})) {
                if (deep && isObject(value)) {
                    target[key] = extend(true, isObject(target[key]) ? target[key] : {}, value);
                } else if (value !== undefined) {
                    target[key] = value;
                }
            }
        }

        return target;
    };

    const $ = () => ({ready: (callback) => callback()});
    $.extend = (deep, ...args) => (deep === true ? extend(true, ...args) : extend(false, deep, ...args));

    return $;
}

function loadSloc(file, context = null) {
    const jQuery = createJQuery();
    context      = context || vm.createContext({});

    Object.assign(context, {jQuery, document: {}, Promise});
    vm.runInContext(readFileSync(jsFolder + file, 'utf8'), context, {filename: file});

    return jQuery.sloc;
}

for (const file of ['sloc.js', 'sloc.min.js']) {
    describe(file, () => {
        describe('sprintf', () => {
            it('replaces placeholders in order', () => {
                const sloc = loadSloc(file);

                assert.equal(sloc.sprintf('Showing %s of %s', '3', '10'), 'Showing 3 of 10');
            });

            it('accepts replacements as an array', () => {
                const sloc = loadSloc(file);

                assert.equal(sloc.sprintf('Showing %s of %s', ['3', '10']), 'Showing 3 of 10');
            });

            it('accepts a single string replacement', () => {
                const sloc = loadSloc(file);

                assert.equal(sloc.sprintf('Geocode was not successful: %s', 'ZERO_RESULTS'), 'Geocode was not successful: ZERO_RESULTS');
            });

            it('supports numbered placeholders in any order', () => {
                const sloc = loadSloc(file);

                assert.equal(
                    sloc.sprintf('Showing %1$s locations (within %2$skm of %3$s).', '4', '15', 'Tampa'),
                    'Showing 4 locations (within 15km of Tampa).'
                );
                assert.equal(sloc.sprintf('%2$s before %1$s', 'one', 'two'), 'two before one');
            });

            it('turns %% into a literal percent sign', () => {
                const sloc = loadSloc(file);

                assert.equal(sloc.sprintf('%s%% done', ['50']), '50% done');
            });

            it('leaves extra placeholders when replacements run out', () => {
                const sloc = loadSloc(file);

                assert.equal(sloc.sprintf('%s and %s', ['one']), 'one and %s');
            });
        });

        describe('map registry', () => {
            it('returns registered maps by id', () => {
                const sloc = loadSloc(file);
                const map  = {name: 'downtown'};

                sloc.map.register('fp_1', map);

                assert.equal(sloc.map.get('fp_1'), map);
                assert.equal(sloc.map.get('fp_2'), null);
            });

            it('updates a registered map', () => {
                const context = vm.createContext({});
                const sloc    = loadSloc(file, context);
                // Created inside the page's context, as sloc.js checks for its Function
                const map     = vm.runInContext('({updated: 0, update() { this.updated++; }})', context);

                sloc.map.register('fp_1', map);
                sloc.map.update('fp_1');

                assert.equal(map.updated, 1);
            });

            it('ignores updates for unknown maps or maps without update()', () => {
                const sloc = loadSloc(file);

                sloc.map.register('fp_1', {update: 'not a function'});

                assert.doesNotThrow(() => sloc.map.update('fp_1'));
                assert.doesNotThrow(() => sloc.map.update('missing'));
            });

            it('keeps maps registered before sloc.js loaded', () => {
                class GoogleMap {
                }

                const jQuery  = createJQuery();
                const early   = new GoogleMap();
                jQuery.sloc   = {map: {instance: {fp_0: early}}};
                const context = vm.createContext({jQuery, document: {}, Promise});

                vm.runInContext(readFileSync(jsFolder + file, 'utf8'), context);

                assert.equal(jQuery.sloc.map.get('fp_0'), early);
            });
        });

        describe('tabs', () => {
            it('does nothing without a map id', () => {
                const sloc = loadSloc(file);

                assert.doesNotThrow(() => sloc.tabs(''));
            });
        });
    });
}
