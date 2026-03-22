
/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
 *
 * key => the desired key used to access to the local storage
 */
saltos.storage.get_key = key => {
    return `${saltos.storage.pathname}/${key}`;
};

/**
 * Get Item
 *
 * This function is the same that localStorage.getItem but using the get_key as key
 *
 * key => the desired key used to access to the local storage
 */
saltos.storage.getItem = key => {
    return window.localStorage.getItem(saltos.storage.get_key(key));
};

/**
 * Set Item
 *
 * This function is the same that localStorage.setItem but using the get_key as key
 *
 * key   => the desired key used to access to the local storage
 * value => the value that you want to set
 */
saltos.storage.setItem = (key, value) => {
    window.localStorage.setItem(saltos.storage.get_key(key), value);
};

/**
 * Remove Item
 *
 * This function is the same that localStorage.removeItem but using the get_key as key
 *
 * key => the desired key used to access to the local storage
 */
saltos.storage.removeItem = key => {
    window.localStorage.removeItem(saltos.storage.get_key(key));
},

/**
 * Clear
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

/**
 * Get Item With Timestamp
 *
 * This function tries to do the same thing that getItem but using a timestampd data
 * structure, to do it, only returns the value entrie
 *
 * key => the desired key used to access to the local storage
 */
saltos.storage.getItemWithTimestamp = key => {
    try {
        const data = JSON.parse(saltos.storage.getItem(key));
        return data.value;
    } catch (error) {
        return null;
    }
};

/**
 * Set Item With Timestamp
 *
 * This function tries to do the same thing that setItem but using a timestampd data
 * structure, to do it, sets an object with the timestamp and the original value.
 *
 * key => the desired key used to access to the local storage
 */
saltos.storage.setItemWithTimestamp = (key, value) => {
    return saltos.storage.setItem(key, JSON.stringify({
        timestamp: saltos.core.timestamp(),
        value: value,
    }));
};

/**
 * Purge With Timestamp
 *
 * This function tries to purge old data using the prefix and offset in the current
 * timestamp.
 *
 * @prefix => the prefix used in the search process
 * @offset => the offset used in the timestamp comparison
 */
saltos.storage.purgeWithTimestamp = (prefix, offset) => {
    prefix = saltos.storage.get_key(prefix);
    const timestamp = saltos.core.timestamp(offset);
    Object.keys(window.localStorage).forEach(key => {
        if (!key.startsWith(prefix)) {
            return;
        }
        try {
            const data = JSON.parse(window.localStorage.getItem(key));
            if (data.timestamp < timestamp) {
                window.localStorage.removeItem(key);
            }
        } catch (error) {
            return;
        }
    });
};
