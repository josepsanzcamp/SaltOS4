
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * Token helper module
 *
 * This module provides the needed tools to manage the tokens
 */

/**
 * Storage helper object
 *
 * This object manage the localStorage using a prefix to prevent collisions
 */
saltos.storage = {};

/**
 * Pathname helper string
 *
 * This string contains the pathname for the current execution
 */
saltos.storage.pathname = saltos.core.encode_bad_chars(window.location.pathname);

/**
 * Get key
 *
 * This function returns the real key used by localStorage to store a retrieve
 * the data.
 */
saltos.storage.get_key = key => {
    return `${saltos.storage.pathname}/${key}`;
};

/**
 * Get Item
 *
 * This function is the same that localStorage.getItem but using the get_key as key
 */
saltos.storage.getItem = key => {
    return window.localStorage.getItem(saltos.storage.get_key(key));
};

/**
 * Set Item
 *
 * This function is the same that localStorage.setItem but using the get_key as key
 */
saltos.storage.setItem = (key, value) => {
    window.localStorage.setItem(saltos.storage.get_key(key), value);
};

/**
 * Remove Item
 *
 * This function is the same that localStorage.removeItem but using the get_key as key
 */
saltos.storage.removeItem = key => {
    window.localStorage.removeItem(saltos.storage.get_key(key));
},

/**
 * Get Item
 *
 * This function is the same that localStorage.clear but only removes the entries that
 * are using the same prefix that the current context, to do it, this code checks all
 * keys and removes all items that starts with the prefix returned by get_key
 */
saltos.storage.clear = () => {
    const prefix = saltos.storage.get_key('');
    Object.keys(window.localStorage).forEach(key => {
        if (key.startsWith(prefix)) {
            window.localStorage.removeItem(key);
        }
    });
};
