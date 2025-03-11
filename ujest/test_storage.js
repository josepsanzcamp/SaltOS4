
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
 * Storage unit tests
 *
 * This file contains the storage unit tests
 */

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
