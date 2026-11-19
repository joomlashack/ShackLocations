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
 * The inline script JHtmlSlocGoogle::map() adds to load Google Maps and start each map
 * (jQuery.sloc.google). It is read from slocgoogle.php, so this tests what ships, and
 * run against stand-ins for jQuery.sloc and Google's loader.
 */

import {describe, it} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../src/admin/library/html/slocgoogle.php', import.meta.url), 'utf8');
const script = source.match(/<<<JSCRIPT\n([\s\S]*?)\nJSCRIPT;/)[1]
    .replace('{$scripts}', JSON.stringify(['/media/com_focalpoint/js/infobox.min.js']));

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/**
 * A page with the scripts slocgoogle.php expects to have loaded before it
 *
 * @param {number}  apiDelay   ms until the Maps API appears; 0 = already loaded
 * @param {boolean} failScript Whether loading infobox.min.js fails
 */
function createPage({apiDelay = 0, failScript = false} = {}) {
    const log    = {imports: [], scripts: [], maps: [], errors: []};
    const window = {};
    const api    = {
        maps: {
            importLibrary: async (name) => {
                log.imports.push(name);
                return {};
            }
        }
    };

    const jQuery = {
        sloc: {
            loadScript: (uri) => {
                log.scripts.push(uri);
                return failScript ? Promise.reject(new Error('404')) : Promise.resolve();
            },
            map       : {
                google  : function() {
                    this.init = (options) => log.maps.push(options.name);
                },
                register: () => {
                }
            }
        }
    };

    const context = vm.createContext({
        window,
        jQuery,
        setTimeout,
        Promise,
        console: {error: (...args) => log.errors.push(args.join(' '))}
    });

    const loadApi = () => {
        context.google = api;
        window.google  = api;
    };
    if (apiDelay === 0) {
        loadApi();
    } else {
        setTimeout(loadApi, apiDelay);
    }

    return {log, run: (code) => vm.runInContext(code, context)};
}

describe('jQuery.sloc.google', () => {
    it('starts maps right away when the API is already loaded', async () => {
        const page = createPage();

        page.run(script);
        page.run("jQuery.sloc.google.initMap('fp_1', {name: 'one'});");
        await sleep(20);

        assert.deepEqual(page.log.maps, ['one']);
    });

    it('loads the libraries and InfoBox once for all maps', async () => {
        const page = createPage();

        page.run(script);
        page.run("jQuery.sloc.google.initMap('fp_1', {name: 'one'});");
        page.run("jQuery.sloc.google.initMap('fp_2', {name: 'two'});");
        await sleep(20);

        assert.deepEqual(page.log.maps, ['one', 'two']);
        assert.deepEqual(page.log.imports, ['maps', 'marker', 'geocoding', 'routes']);
        assert.deepEqual(page.log.scripts, ['/media/com_focalpoint/js/infobox.min.js']);
    });

    it('waits for an API that loads late, e.g. after cookie consent', async () => {
        const page = createPage({apiDelay: 600});

        page.run(script);
        page.run("jQuery.sloc.google.initMap('fp_1', {name: 'late'});");

        await sleep(300);
        assert.deepEqual(page.log.maps, []);

        await sleep(500);
        assert.deepEqual(page.log.maps, ['late']);
        assert.deepEqual(page.log.errors, []);
    });

    it('logs a failed load for each map without throwing', async () => {
        const page = createPage({failScript: true});

        page.run(script);
        page.run("jQuery.sloc.google.initMap('fp_1', {name: 'one'});");
        page.run("jQuery.sloc.google.initMap('fp_2', {name: 'two'});");
        await sleep(20);

        assert.deepEqual(page.log.maps, []);
        assert.equal(page.log.errors.length, 2);
        assert.match(page.log.errors[0], /Shack Locations: unable to load Google Maps/);
    });

    it('ignores the script being added twice', async () => {
        const page = createPage();

        page.run(script + '\n' + script);
        page.run("jQuery.sloc.google.initMap('fp_1', {name: 'one'});");
        await sleep(20);

        assert.deepEqual(page.log.maps, ['one']);
        assert.equal(page.log.scripts.length, 1);
    });

    it('defines no globals', () => {
        const page = createPage();

        page.run(script);

        assert.equal(page.run('typeof initMap'), 'undefined');
        assert.equal(page.run('typeof initGoogle'), 'undefined');
        assert.equal(page.run('typeof ready'), 'undefined');
    });
});
