
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
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
 * Needed by all user interface features
 */
global.bootstrap = require('../../code/web/lib/bootstrap/bootstrap.bundle.min.js');

/**
 * Needed by bootstrap, core and proxy modules
 */
global.md5 = require('../../code/web/lib/md5/md5.min.js');

/**
 * Needed by bootstrap module
 */
global.window.matchMedia = function() {
    return {
        matches: false,
        addEventListener: function() {},
        removeEventListener: function() {}
    };
};

/**
 * This is the same that object.js for the global scope
 */
global.saltos = {};

/**
 * Load all files of the project
 */
/*const files = `core,bootstrap,storage,hash,token,auth,window,
    gettext,driver,filter,backup,form,push,common,app`.split(',');
for (const i in files) {
    const file = files[i].trim();
    require(`../../code/web/js/${file}.js`);
}*/

/**
 * My Require
 *
 * This function is intended to add the needed module.exports at the end of the
 * file that you want to process, this is a temporary action used only for the
 * require action, and at the end, before the return, the original code is saved
 * as a restore action to maintain the original code file
 */
global.myrequire = (file, fns) => {
    const fs = require('fs');
    const path = require('path');

    const original = path.resolve(__dirname, file);
    const maincode = fs.readFileSync(original, 'utf-8');

    const exports = `module.exports = { ${fns} };`;
    const wrapper = `${maincode}\n\n${exports}\n\n`;
    fs.writeFileSync(original, wrapper);

    const output = require(original);
    fs.writeFileSync(original, maincode);
    return output;
};
