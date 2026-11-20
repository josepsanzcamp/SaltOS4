
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
 *
 * @full => if true, returns an object with the hash and the params
 *
 * Notes:
 *
 * The params are stored after the hash using the format #/app/X/?/k1/v1/k2/v2 and contains
 * the state of the current screen
 */
saltos.hash.get = (full = false) => {
    const hash = saltos.hash.__helper_hash(window.location.hash);
    if (!full) {
        return hash;
    }
    const params = saltos.hash.__helper_params(window.location.hash);
    return {hash, params};
};

/**
 * Set hash
 *
 * Function intended to replace the hash in the current url, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash   => this must contain the hash with or without the pillow, null to keep the current hash
 * @params => the object with the params that you want to store
 *
 * Notes:
 *
 * The # char is added by default if it is not found in the hash, additionally the dot is
 * added to force to remove all chars before the # char in the window.location
 *
 * The operation is cancelled if the current hash and params are the same that the new ones
 */
saltos.hash.set = (hash, params = {}) => {
    hash = saltos.hash.__helper_build(hash, params);
    if (saltos.hash.__helper_build(null, saltos.hash.get(true).params) === hash) {
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
 * @hash   => this must contain the hash with or without the pillow, null to keep the current hash
 * @params => the object with the params that you want to store
 *
 * Notes:
 *
 * The # char is added by default if it is not found in the hash, additionally the dot is
 * added to force to remove all chars before the # char in the window.location
 *
 * The operation is cancelled if the current hash and params are the same that the new ones
 */
saltos.hash.add = (hash, params = {}) => {
    hash = saltos.hash.__helper_build(hash, params);
    if (saltos.hash.__helper_build(null, saltos.hash.get(true).params) === hash) {
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
 *
 * Notes:
 *
 * The params part (all from the ? char) is removed, to get the params you must use the
 * params helper
 */
saltos.hash.__helper_hash = hash => {
    if (hash.length && hash.substr(0, 1) === '#') {
        hash = hash.substr(1);
    }
    if (hash.length && hash.substr(0, 1) === '/') {
        hash = hash.substr(1);
    }
    hash = hash.replace(/\/?\?.*$/, '');
    return hash;
};

/**
 * Params helper
 *
 * This function returns an object with the params of the hash
 *
 * @hash => the hash that contains the params
 */
saltos.hash.__helper_params = hash => {
    const params = {};
    const pos = hash.indexOf('?');
    if (pos === -1) {
        return params;
    }
    const arr = hash.substr(pos + 1).split('/').filter(item => item !== '');
    for (let i = 0; i < arr.length; i += 2) {
        params[decodeURIComponent(arr[i])] = decodeURIComponent(arr[i + 1] || '');
    }
    return params;
};

/**
 * Build helper
 *
 * This function returns the hash with the params, it is used by set and add
 *
 * @hash   => the hash with or without the pillow, null to use the current hash
 * @params => the object with the params that you want to store
 *
 * Notes:
 *
 * The empty values are not added, and if no params remains, the ? char is not added
 */
saltos.hash.__helper_build = (hash, params) => {
    if (hash === null) {
        hash = saltos.hash.get();
    }
    hash = saltos.hash.__helper_hash(hash);
    const arr = [];
    for (const key in params) {
        const val = params[key];
        if (val === undefined || val === null || val === '') {
            continue;
        }
        arr.push(encodeURIComponent(key), encodeURIComponent(val));
    }
    if (arr.length) {
        hash += '/?/' + arr.join('/');
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
    return saltos.hash.__helper_hash((new URL(url)).hash);
};

/**
 * Decode object
 *
 * This object stores the functions used to decode the hashes (public) and the internal
 * actions (private) that never are stored in the hash
 */
saltos.hash.decode = {};

/**
 * Decode public hash
 *
 * This function returns an object with the parts of the hash (app, action, subaction, id and sub_id)
 * to prevent the usage of positions in the code that uses the hash
 *
 * @hash => the hash that you want to decode
 *
 * Notes:
 *
 * The empty parts are removed like in the backend, so app/X/ is the same that app/X
 *
 * Only the next hashes are allowed, otherwise an exception is thrown:
 *
 * app/X
 * app/X/list
 * app/X/create
 * app/X/create/reply/ID (and replyall and forward)
 * app/X/edit/ID
 * app/X/view/ID
 * app/X/view/viewpdf/ID (and version, log and source)
 * app/X/view/viewpdf/ID/SUB_ID
 */
saltos.hash.decode.public = hash => {
    const arr = hash.split('/').filter(item => item !== '');
    if (arr[0] !== 'app' || !arr[1]) {
        throw new Error(`Unknown hash ${hash}`);
    }
    const route = {app: arr[1], action: '', subaction: '', id: '', sub_id: ''};
    if (arr.length === 2) {
        return route;
    }
    route.action = arr[2];
    switch (route.action) {
        case 'list':
            // app/X/list
            if (arr.length === 3) {
                return route;
            }
            break;
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
            // app/X/view/viewpdf/ID/SUB_ID
            if (arr.length === 6 && arr[3] === 'viewpdf') {
                route.subaction = arr[3];
                route.id = arr[4];
                route.sub_id = arr[5];
                return route;
            }
            break;
    }
    throw new Error(`Unknown hash ${hash}`);
};

/**
 * Decode private action
 *
 * This function returns an object with the parts of the action (app, action, subaction, id and
 * sub_id) like the public decode, but for the internal actions that never are stored in the hash
 *
 * @url => the url of the action that you want to decode
 *
 * Notes:
 *
 * The empty parts are removed like in the backend, so app/X/delete/ID/ is the same that
 * app/X/delete/ID
 *
 * Only the next actions are allowed, otherwise an exception is thrown:
 *
 * app/X/list/data (and filter)
 * app/X/insert
 * app/X/update/ID
 * app/X/view/download/ID
 * app/X/view/download/ID/SUB_ID
 * app/X/view/body/ID (and setter)
 * app/X/sendmail
 * app/X/sendmail/reply/ID (and replyall and forward)
 * app/X/delete/ID
 * app/X/delete/files/ID/SUB_ID (and notes)
 * app/X/action/NAME
 * app/widgets/help/APP
 */
saltos.hash.decode.private = url => {
    const arr = url.split('/').filter(item => item !== '');
    if (arr[0] !== 'app' || !arr[1]) {
        throw new Error(`Unknown action ${url}`);
    }
    const route = {app: arr[1], action: arr[2], subaction: '', id: '', sub_id: ''};
    switch (route.action) {
        case 'list':
            // app/X/list/data
            if (arr.length === 4 && ['data', 'filter'].includes(arr[3])) {
                route.subaction = arr[3];
                return route;
            }
            break;
        case 'insert':
            // app/X/insert
            if (arr.length === 3) {
                return route;
            }
            break;
        case 'update':
            // app/X/update/ID
            if (arr.length === 4) {
                route.id = arr[3];
                return route;
            }
            break;
        case 'view':
            // app/X/view/download/ID
            if (arr.length === 5 && ['download', 'body', 'setter'].includes(arr[3])) {
                route.subaction = arr[3];
                route.id = arr[4];
                return route;
            }
            // app/X/view/download/ID/SUB_ID
            if (arr.length === 6 && arr[3] === 'download') {
                route.subaction = arr[3];
                route.id = arr[4];
                route.sub_id = arr[5];
                return route;
            }
            break;
        case 'sendmail':
            // app/X/sendmail
            if (arr.length === 3) {
                return route;
            }
            // app/X/sendmail/reply/ID
            if (arr.length === 5 && ['reply', 'replyall', 'forward'].includes(arr[3])) {
                route.subaction = arr[3];
                route.id = arr[4];
                return route;
            }
            break;
        case 'delete':
            // app/X/delete/ID
            if (arr.length === 4) {
                route.id = arr[3];
                return route;
            }
            // app/X/delete/files/ID/SUB_ID
            if (arr.length === 6 && ['files', 'notes'].includes(arr[3])) {
                route.subaction = arr[3];
                route.id = arr[4];
                route.sub_id = arr[5];
                return route;
            }
            break;
        case 'action':
            // app/X/action/NAME
            if (arr.length === 4) {
                route.subaction = arr[3];
                return route;
            }
            break;
        case 'help':
            // app/widgets/help/APP
            if (arr.length === 4 && route.app === 'widgets') {
                route.id = arr[3];
                return route;
            }
            break;
    }
    throw new Error(`Unknown action ${url}`);
};

/**
 * Encode object
 *
 * This object stores the functions used to encode the hashes (public) and the internal
 * actions (private), it is the inverse of the decode object
 */
saltos.hash.encode = {};

/**
 * Encode public hash
 *
 * This function returns the hash of the route object (app, action, subaction, id and sub_id),
 * the omitted or empty parts are not added
 *
 * @route => the route object that you want to encode
 *
 * Notes:
 *
 * The result is validated with the public decode, so an unknown route throws an exception
 */
saltos.hash.encode.public = route => {
    const arr = ['app', route.app, route.action, route.subaction, route.id, route.sub_id];
    const hash = arr.filter(item => item !== undefined && item !== '').join('/');
    saltos.hash.decode.public(hash);
    return hash;
};

/**
 * Encode private action
 *
 * This function returns the url of the route object (app, action, subaction, id and sub_id)
 * like the public encode, but for the internal actions that never are stored in the hash
 *
 * @route => the route object that you want to encode
 *
 * Notes:
 *
 * The result is validated with the private decode, so an unknown route throws an exception
 */
saltos.hash.encode.private = route => {
    const arr = ['app', route.app, route.action, route.subaction, route.id, route.sub_id];
    const url = arr.filter(item => item !== undefined && item !== '').join('/');
    saltos.hash.decode.private(url);
    return url;
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
 *
 * Notes:
 *
 * Only the params are applied if only the params of the hash change, the trigger function sends
 * an event without urls, so it always updates the contents
 */
window.addEventListener('hashchange', event => {
    // Params part
    if (event.oldURL !== '' && event.newURL !== '') {
        if (saltos.hash.url2hash(event.oldURL) === saltos.hash.url2hash(event.newURL)) {
            saltos.driver.params();
            return;
        }
    }
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
    // Screen part, the layout is only reused inside the same app
    if (event.oldURL !== '') {
        const app = hash => {
            try {
                return saltos.hash.decode.public(hash).app;
            } catch (error) {
                return '';
            }
        };
        if (app(saltos.hash.url2hash(event.oldURL)) !== app(saltos.hash.get())) {
            saltos.form.screen('clear');
        }
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
