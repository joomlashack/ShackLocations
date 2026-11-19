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
 * Every script in media/js parses, and each has the minified copy the site loads
 */

import {describe, it} from 'node:test';
import assert from 'node:assert/strict';
import {readdirSync, readFileSync} from 'node:fs';
import {fileURLToPath} from 'node:url';
import vm from 'node:vm';

const jsFolder = fileURLToPath(new URL('../../src/media/js/', import.meta.url));
const files    = readdirSync(jsFolder).filter((file) => file.endsWith('.js'));

describe('media/js', () => {
    for (const file of files) {
        it(`${file} parses`, () => {
            assert.doesNotThrow(() => new vm.Script(readFileSync(jsFolder + file, 'utf8'), {filename: file}));
        });
    }

    for (const file of files.filter((file) => !file.endsWith('.min.js'))) {
        it(`${file} has a minified copy`, () => {
            assert.ok(files.includes(file.replace(/\.js$/, '.min.js')));
        });
    }
});
