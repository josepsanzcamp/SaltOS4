
/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Hash helper module
 *
 * This module includes the code used to manage the hash feature in the browser
 * and to provide an onhashchange function
 */

/**
 * Hash helper object
 *
 * This object stores all hash functions to get, set and trigger a change
 */
saltos.hash = {};

/**
 * Get hash
 *
 * Function intended to return the current hash without the pillow
 */
saltos.hash.get = () => {
    let hash = window.location.hash;
    hash = saltos.hash.__helper(hash);
    return hash;
};

/**
 * Set hash
 *
 * Function intended to replace the hash in the current url, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash => this must contain the hash with or without the pillow
 *
 * Notes:
 *
 * The # char is added by default if it is not found in the hash, additionally the dot is
 * added to force to remove all chars before the # char in the window.location
 *
 * The operation is cancelled if the current hash is the same that the new hash
 */
saltos.hash.set = hash => {
    hash = saltos.hash.__helper(hash);
    if (saltos.hash.get() === hash) {
        return false;
    }
    window.history.replaceState(null, null, '.#/' + hash);
    return true;
};

/**
 * Add hash
 *
 * Function intended to add a hash in the current history, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash => this must contain the hash with or without the pillow
 *
 * Notes:
 *
 * The # char is added by default if it is not found in the hash, additionally the dot is
 * added to force to remove all chars before the # char in the window.location
 *
 * The operation is cancelled if the current hash is the same that the new hash
 */
saltos.hash.add = hash => {
    hash = saltos.hash.__helper(hash);
    if (saltos.hash.get() === hash) {
        return false;
    }
    window.history.pushState(null, null, '.#/' + hash);
    return true;
};

/**
 * Hash helper
 *
 * This function is used by all other functions to clean the hash
 *
 * @hash => the hash that you want to check and clean
 */
saltos.hash.__helper = hash => {
    if (hash.length && hash.substr(0, 1) === '#') {
        hash = hash.substr(1);
    }
    if (hash.length && hash.substr(0, 1) === '/') {
        hash = hash.substr(1);
    }
    return hash;
};

/**
 * Url to hash
 *
 * This function allow to convert and url string into a hash string
 *
 * @url => the url string that contains the hash that you want to retrieve
 */
saltos.hash.url2hash = url => {
    return saltos.hash.__helper((new URL(url)).hash);
};

/**
 * Parse object
 *
 * This object stores the functions used to parse the hashes (public) and the internal
 * actions (private) that never are stored in the hash
 */
saltos.hash.parse = {};

/**
 * Parse public hash
 *
 * This function returns an object with the parts of the hash (app, action, subaction, id and file)
 * to prevent the usage of positions in the code that uses the hash
 *
 * @hash => the hash that you want to parse
 *
 * Notes:
 *
 * The empty parts are removed like in the backend, so app/X/ is the same that app/X
 *
 * Only the next hashes are allowed, otherwise an exception is thrown:
 *
 * app/X
 * app/X/create
 * app/X/create/reply/ID (and replyall and forward)
 * app/X/edit/ID
 * app/X/view/ID
 * app/X/view/viewpdf/ID (and version, log and source)
 * app/X/view/viewpdf/ID/FILE
 */
saltos.hash.parse.public = hash => {
    const arr = hash.split('/').filter(item => item !== '');
    if (arr[0] !== 'app' || !arr[1]) {
        throw new Error(`Unknown hash ${hash}`);
    }
    const route = {app: arr[1], action: 'list', subaction: '', id: '', file: ''};
    if (arr.length === 2) {
        return route;
    }
    route.action = arr[2];
    switch (route.action) {
        case 'create':
            // app/X/create
            if (arr.length === 3) {
                return route;
            }
            // app/X/create/reply/ID
            if (arr.length === 5 && ['reply', 'replyall', 'forward'].includes(arr[3])) {
                route.subaction = arr[3];
                route.id = arr[4];
                return route;
            }
            break;
        case 'edit':
            // app/X/edit/ID
            if (arr.length === 4) {
                route.id = arr[3];
                return route;
            }
            break;
        case 'view':
            // app/X/view/ID
            if (arr.length === 4) {
                route.id = arr[3];
                return route;
            }
            // app/X/view/viewpdf/ID
            if (arr.length === 5 && ['viewpdf', 'version', 'log', 'source'].includes(arr[3])) {
                route.subaction = arr[3];
                route.id = arr[4];
                return route;
            }
            // app/X/view/viewpdf/ID/FILE
            if (arr.length === 6 && arr[3] === 'viewpdf') {
                route.subaction = arr[3];
                route.id = arr[4];
                route.file = arr[5];
                return route;
            }
            break;
    }
    throw new Error(`Unknown hash ${hash}`);
};

/**
 * Parse private action
 *
 * This function returns an object with the parts of the action (app, action, subaction, id and
 * file) like the public parse, but for the internal actions that never are stored in the hash
 *
 * @url => the url of the action that you want to parse
 *
 * Notes:
 *
 * The empty parts are removed like in the backend, so app/X/delete/ID/ is the same that
 * app/X/delete/ID
 *
 * Only the next actions are allowed, otherwise an exception is thrown:
 *
 * app/X/delete/ID
 * app/X/delete/files/ID/FILE (and notes)
 */
saltos.hash.parse.private = url => {
    const arr = url.split('/').filter(item => item !== '');
    if (arr[0] !== 'app' || !arr[1]) {
        throw new Error(`Unknown action ${url}`);
    }
    const route = {app: arr[1], action: arr[2], subaction: '', id: '', file: ''};
    switch (route.action) {
        case 'delete':
            // app/X/delete/ID
            if (arr.length === 4) {
                route.id = arr[3];
                return route;
            }
            // app/X/delete/files/ID/FILE
            if (arr.length === 6 && ['files', 'notes'].includes(arr[3])) {
                route.subaction = arr[3];
                route.id = arr[4];
                route.file = arr[5];
                return route;
            }
            break;
    }
    throw new Error(`Unknown action ${url}`);
};

/**
 * Change trigger
 *
 * This function triggers the hashchange event to execute the onhashchange
 */
saltos.hash.trigger = () => {
    window.dispatchEvent(new HashChangeEvent('hashchange'));
};

/**
 * Hash change management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
window.addEventListener('hashchange', event => {
    if (event.oldURL !== '') {
        const hash = saltos.hash.url2hash(event.oldURL);
        saltos.autosave.save('two,one', hash);
        saltos.autosave.purge('two,one', hash);
    }
    // Ajax part
    saltos.core.__abort_all_ajax();
    // Autoclose part
    saltos.driver.__disable_all_autoclose();
    // Modal and offcanvas part
    saltos.bootstrap.modal('close');
    saltos.bootstrap.offcanvas('close');
    saltos.bootstrap.__tooltip_hide();
    // Token part
    if (!saltos.token.get()) {
        saltos.app.send_request('app/login');
        return;
    }
    // Hash part
    if (['', 'app/login'].includes(saltos.hash.get())) {
        saltos.hash.set('app/dashboard');
    }
    // Do the request
    saltos.app.send_request(saltos.hash.get());
});

/**
 * Before unload management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
window.addEventListener('beforeunload', event => {
    saltos.autosave.save('two,one');
    saltos.autosave.purge('two,one');
});
