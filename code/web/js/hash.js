
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
    if (saltos.hash.get() == hash) {
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
    if (saltos.hash.get() == hash) {
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
    if (hash.length && hash.substr(0, 1) == '#') {
        hash = hash.substr(1);
    }
    if (hash.length && hash.substr(0, 1) == '/') {
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
    if (event.oldURL != '') {
        const hash = saltos.hash.url2hash(event.oldURL);
        saltos.autosave.save('two,one', hash);
        saltos.autosave.purge('two,one', hash);
    }
    // Ajax part
    for (const i in saltos.core.__ajax) {
        saltos.core.__ajax[i].abort();
    }
    // Autoclose part
    document.querySelectorAll('[autoclose]').forEach(item => {
        item.removeAttribute('autoclose');
    });
    // Modal and offcanvas part
    saltos.bootstrap.modal('close');
    saltos.bootstrap.offcanvas('close');
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
