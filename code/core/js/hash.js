
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * This file contains all code needed to manage the hash feature, includes the code to process
 * the onhashchange and too, includes the code to get and set the hash value.
 */

/**
 * Hash change management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
window.onhashchange = event => {
    // Reset the body interface
    saltos.modal('close');
    saltos.offcanvas('close');
    saltos.loading(true);
    // Do the request
    saltos.send_request(saltos.hash.get());
};

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
    var hash = document.location.hash;
    if (hash.substr(0, 1) == '#') {
        hash = hash.substr(1);
    }
    return hash;
};

/**
 * Set hash
 *
 * Function intended to set the hash in the current url, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash => this must contain the hash with or without the pillow
 */
saltos.hash.set = hash => {
    if (hash.substr(0, 1) != '#') {
        hash = '#' + hash;
    }
    history.replaceState(null, null, hash);
};

/**
 * Change trigger
 *
 * This function triggers the hashchange event to execute the onhashchange
 */
saltos.hash.change = () => {
    window.dispatchEvent(new HashChangeEvent('hashchange'));
};

