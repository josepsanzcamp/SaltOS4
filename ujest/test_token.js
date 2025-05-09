
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
