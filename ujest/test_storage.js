
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
 * Storage unit tests
 *
 * This file contains the storage unit tests
 */

/**
 * Load all needed files of the project
 */
const files = `core,storage`.split(',');
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
 * saltos.storage
 *
 * This function performs the test of the storage functions
 */
test('saltos.storage', () => {
    expect(saltos.storage.getItem('someKey')).toBe(null);

    saltos.storage.setItem('someKey', 'someValue');
    expect(saltos.storage.getItem('someKey')).toBe('someValue');

    saltos.storage.removeItem('someKey');
    expect(saltos.storage.getItem('someKey')).toBe(null);

    saltos.storage.setItem('someKey', 'someValue');
    expect(saltos.storage.getItem('someKey')).toBe('someValue');

    saltos.storage.setItem('anotherKey', 'anotherValue');
    saltos.storage.setItem('someKey2', 'someValue');

    const old_pathname = saltos.storage.pathname;
    saltos.storage.pathname = 'temp';
    saltos.storage.setItem('anotherKey', 'anotherValue');
    expect(saltos.storage.getItem('anotherKey')).toBe('anotherValue');
    saltos.storage.pathname = old_pathname;

    saltos.storage.clear();
    expect(saltos.storage.getItem('someKey')).toBe(null);

    expect(saltos.storage.getItemWithTimestamp('someKey')).toBe(null);

    saltos.storage.setItemWithTimestamp('someKey', 'someValue');
    expect(saltos.storage.getItemWithTimestamp('someKey')).toBe('someValue');

    saltos.storage.setItemWithTimestamp('anotherKey', 'anotherValue');
    saltos.storage.setItem('someKey2', 'someValue');

    saltos.storage.purgeWithTimestamp('someKey', -10);
    expect(saltos.storage.getItemWithTimestamp('someKey')).toBe('someValue');

    saltos.storage.purgeWithTimestamp('someKey', 10);
    expect(saltos.storage.getItemWithTimestamp('someKey')).toBe(null);
});
