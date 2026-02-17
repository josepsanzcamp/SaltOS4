
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
