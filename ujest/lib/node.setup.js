
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

'use strict';

/**
 * Setup file for the unit tests
 *
 * This file contains the code that initialize the unit tests
 */

/**
 * This is the same that object.js for the global scope
 */
global.saltos = {};

/**
 * Needed by core module
 */
global.window = {
    addEventListener: function() {},
};

/**
 * Needed by core module
 */
global.document = {
    addEventListener: function() {},
};

/**
 * Load all files of the project
 */
require(`../../code/web/js/core.js`);

/**
 * My Pause
 *
 * This function is intended to do a pause inside the browser, to do it, we
 * use a string instead of real code because istanbul tries to inject code
 * and fails in runtime, one solution can be to put "istanbul ignore next"
 * in a comment before the next page.evaluate, but I prefer to use a string
 * in the page.evaluate because it is more simple for me
 */
global.mypause = (page, delay) => {
    return page.evaluate(`
        new Promise(resolve => {
            setTimeout(resolve, ${delay});
        });
    `, delay);
};
