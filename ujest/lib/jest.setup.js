
// Needed by bootstrap, core and proxy modules
global.md5 = require('../../code/web/lib/md5/md5.min.js');

// Needed by bootstrap module
global.window.matchMedia = function() {
    return {
        matches: false,
        addListener: function() {},
        removeListener: function() {}
    };
};

// This is the same that object.js for the global scope
global.saltos = {};

// Load all files of the project
const files = [
    'object',
    'core',
    'bootstrap',
    'storage',
    'hash',
    'token',
    'auth',
    'window',
    'gettext',
    'driver',
    'filter',
    'backup',
    'form',
    'push',
    'common',
    'app',
];
for (const i in files) {
    require(`../../code/web/js/${files[i]}.js`);
}

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

// Load the needed environment of the proxy part
saltos.proxy = myrequire(
    '../../code/web/js/proxy.js',
    `console_log,debug,proxy,
    queue_open,queue_push,queue_getall,queue_delete,
    request_serialize,request_unserialize,human_size`
);
