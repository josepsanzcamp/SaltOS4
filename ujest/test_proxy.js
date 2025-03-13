
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
 * Proxy unit tests
 *
 * This file contains the proxy unit tests
 */

/**
 * TODO
 */
beforeEach(() => {
    jest.resetAllMocks();
});

/**
 * TODO
 */
afterEach(() => {
    jest.restoreAllMocks();
});

/**
 * Load the needed environment of the proxy part
 */
saltos.proxy = myrequire(
    '../../code/web/js/proxy.js',
    `console_log,debug,proxy,queue_open,queue_push,queue_getall,
    queue_delete,request_serialize,request_unserialize,human_size`
);

/**
 * saltos.proxy.md5
 *
 * This function performs the test of the md5 function
 */
test('saltos.proxy.md5', () => {
    expect(md5('fortuna')).toBe('39e3c3d3cbf9064e35f1bee7dbd176f8');
});

/**
 * saltos.proxy.human_size
 *
 * This function performs the test of the human_size function
 */
test('saltos.proxy.human_size', () => {
    expect(saltos.proxy.human_size(1073741824, ' ', 'bytes')).toBe('1 Gbytes');
    expect(saltos.proxy.human_size(1073741823, ' ', 'bytes')).toBe('1024 Mbytes');
    expect(saltos.proxy.human_size(1048576, ' ', 'bytes')).toBe('1 Mbytes');
    expect(saltos.proxy.human_size(1048575, ' ', 'bytes')).toBe('1024 Kbytes');
    expect(saltos.proxy.human_size(1024, ' ', 'bytes')).toBe('1 Kbytes');
    expect(saltos.proxy.human_size(1023, ' ', 'bytes')).toBe('1023 bytes');

    expect(saltos.proxy.human_size(1073741824, ' ')).toBe('1 G');
    expect(saltos.proxy.human_size(1073741823, ' ')).toBe('1024 M');
    expect(saltos.proxy.human_size(1048576, ' ')).toBe('1 M');
    expect(saltos.proxy.human_size(1048575, ' ')).toBe('1024 K');
    expect(saltos.proxy.human_size(1024, ' ')).toBe('1 K');
    expect(saltos.proxy.human_size(1023, ' ')).toBe('1023 ');

    expect(saltos.proxy.human_size(1073741824)).toBe('1G');
    expect(saltos.proxy.human_size(1073741823)).toBe('1024M');
    expect(saltos.proxy.human_size(1048576)).toBe('1M');
    expect(saltos.proxy.human_size(1048575)).toBe('1024K');
    expect(saltos.proxy.human_size(1024)).toBe('1K');
    expect(saltos.proxy.human_size(1023)).toBe('1023');
});
