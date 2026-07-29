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
 * Puppeteer loader
 *
 * puppeteer is loaded here via dynamic import because since v25 it ships as
 * an ESM-only package and require() can no longer load it, this file is the
 * only place using that syntax because jscs still relies on an old parser
 * that can not tokenize the dynamic import() expression
 */
module.exports = async () => {
    return (await import('puppeteer')).default;
};
