
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
 * Global prototype extensions and polyfills for the SaltOS framework.
 */

/**
 * Polyfill for Array.prototype.at() to support older environments.
 *
 * Takes an integer value and returns the item at that index, allowing for
 * positive and negative integers.
 */
if (!Array.prototype.at) {
    Array.prototype.at = function(n) {
        n = Math.trunc(n) || 0;
        if (n < 0) {
            n += this.length;
        }
        if (n < 0 || n >= this.length) {
            return undefined;
        }
        return this[n];
    };
}
