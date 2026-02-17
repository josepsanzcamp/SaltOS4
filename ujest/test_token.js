
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Token unit tests
 *
 * This file contains the token unit tests
 */

/**
 * Load all needed files of the project
 */
const files = `core,storage,token`.split(',');
for (const i in files) {
    const file = files[i].trim();
    require(`../code/web/js/${file}.js`);
}

/**
 * berofeEach used in this test
 */
beforeEach(() => {
    jest.resetAllMocks();
});

/**
 * afterEach used in this test
 */
afterEach(() => {
    jest.restoreAllMocks();
});

/**
 * saltos.token
 *
 * This function performs the test of the token functions
 */
test('saltos.token', () => {
    expect(saltos.token.get()).toBe(null);
    expect(saltos.token.get_expires_at()).toBe(null);

    saltos.token.set({
        token: 'someToken',
        expires_at: 'someTime',
    });

    expect(saltos.token.get()).toBe('someToken');
    expect(saltos.token.get_expires_at()).toBe('someTime');

    saltos.token.unset();

    expect(saltos.token.get()).toBe(null);
    expect(saltos.token.get_expires_at()).toBe(null);
});
