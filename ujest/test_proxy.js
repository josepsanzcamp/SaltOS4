
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
Object.assign(global, myrequire(
    '../../code/web/js/proxy.js',
    `console_log,debug,proxy,queue_open,queue_push,queue_getall,
    queue_delete,request_serialize,request_unserialize,human_size`
));

/**
 * md5
 *
 * This function performs the test of the md5 function
 */
test('md5', () => {
    expect(md5('fortuna')).toBe('39e3c3d3cbf9064e35f1bee7dbd176f8');
});

/**
 * human_size
 *
 * This function performs the test of the human_size function
 */
test('human_size', () => {
    expect(human_size(1073741824, ' ', 'bytes')).toBe('1 Gbytes');
    expect(human_size(1073741823, ' ', 'bytes')).toBe('1024 Mbytes');
    expect(human_size(1048576, ' ', 'bytes')).toBe('1 Mbytes');
    expect(human_size(1048575, ' ', 'bytes')).toBe('1024 Kbytes');
    expect(human_size(1024, ' ', 'bytes')).toBe('1 Kbytes');
    expect(human_size(1023, ' ', 'bytes')).toBe('1023 bytes');

    expect(human_size(1073741824, ' ')).toBe('1 G');
    expect(human_size(1073741823, ' ')).toBe('1024 M');
    expect(human_size(1048576, ' ')).toBe('1 M');
    expect(human_size(1048575, ' ')).toBe('1024 K');
    expect(human_size(1024, ' ')).toBe('1 K');
    expect(human_size(1023, ' ')).toBe('1023 ');

    expect(human_size(1073741824)).toBe('1G');
    expect(human_size(1073741823)).toBe('1024M');
    expect(human_size(1048576)).toBe('1M');
    expect(human_size(1048575)).toBe('1024K');
    expect(human_size(1024)).toBe('1K');
    expect(human_size(1023)).toBe('1023');
});

describe('console_log', () => {
    const mockClients = [
        {postMessage: jest.fn()},
        {postMessage: jest.fn()}
    ];

    beforeEach(() => {
        global.clients = {
            matchAll: jest.fn().mockResolvedValue(mockClients),
        };
    });

    test('should post message to all clients with correct styling', async () => {
        const testMessage = 'Test message';
        console_log(testMessage);
        expect(clients.matchAll).toHaveBeenCalled();
        await Promise.resolve();
        const expectedArray = [
            `%c${testMessage}%c`,
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ];
        mockClients.forEach(client => {
            expect(client.postMessage).toHaveBeenCalledWith(expectedArray);
        });
    });

    test('should handle empty message', async () => {
        console_log('');
        expect(clients.matchAll).toHaveBeenCalled();
        await Promise.resolve();
        const expectedArray = [
            '%c%c',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ];
        mockClients.forEach(client => {
            expect(client.postMessage).toHaveBeenCalledWith(expectedArray);
        });
    });
});

describe('debug', () => {
    test('should return correct debug array for network type', () => {
        const result = debug('GET', 'https://example.com', 'network', 150, '2KB');
        expect(result).toEqual([
            'GET https://example.com type %cnetwork%c duration %c150ms%c size %c2KB%c',
            'color:white;background:green',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ]);
    });

    test('should return correct debug array for cache type', () => {
        const result = debug('GET', 'https://example.com', 'cache', 5, '1KB');
        expect(result).toEqual([
            'GET https://example.com type %ccache%c duration %c5ms%c size %c1KB%c',
            'color:white;background:blue',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ]);
    });

    test('should return correct debug array for error type', () => {
        const result = debug('GET', 'https://example.com', 'error', 0, '0KB');
        expect(result).toEqual([
            'GET https://example.com type %cerror%c duration %c0ms%c size %c0KB%c',
            'color:white;background:red',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ]);
    });

    test('should use dimgrey for unknown types', () => {
        const result = debug('GET', 'https://example.com', 'unknown', 100, '5KB');
        expect(result).toEqual([
            'GET https://example.com type %cunknown%c duration %c100ms%c size %c5KB%c',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ]);
    });

    test('should handle empty url and action', () => {
        const result = debug('', '', 'network', 0, '0KB');
        expect(result).toEqual([
            '  type %cnetwork%c duration %c0ms%c size %c0KB%c',
            'color:white;background:green',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit',
            'color:white;background:dimgrey',
            'color:inherit;background:inherit'
        ]);
    });
});

describe('proxy', () => {
    const makeServiceWorkerEnv = require('service-worker-mock');
    Object.assign(global, makeServiceWorkerEnv());

    const fetchMock = require('jest-fetch-mock');
    global.Headers = fetchMock.Headers;

    global.fetch = jest.fn();

    Object.defineProperty(global.navigator, 'onLine', {
        value: true,
        writable: true,
    });

    test('should fetch from network and cache response', async () => {
        fetch.mockResolvedValue(new Response('network data', {status: 200}));

        const request = new Request('https://example.com', {method: 'GET', headers: new Headers({})});
        const result = await proxy(request);

        expect(fetch).toHaveBeenCalledWith(request);
        expect(result.type).toBe('network');
    });

    test('should return cached response if network fails', async () => {
        fetch.mockRejectedValue(new Error('Network Error'));

        const request = new Request('https://example.com', {method: 'GET', headers: new Headers({})});
        const result = await proxy(request);

        expect(fetch).toHaveBeenCalled();
        expect(result.type).toBe('cache');
    });

    test('should return an error response if network and cache fail', async () => {
        fetch.mockRejectedValue(new Error('Network Error'));

        const request = new Request('https://example2.com', {method: 'GET', headers: new Headers({})});
        const result = await proxy(request);

        expect(result.type).toBe('error');
        await expect(result.response.json()).resolves.toHaveProperty(
            'error.text', 'There is an network issue and the requested content is not cached');
    });

    test('should return offline error if not online and no cache', async () => {
        global.navigator.onLine = false;
        fetch.mockRejectedValue(new Error('Network Error'));

        const request = new Request('https://example2.com', {method: 'GET', headers: new Headers({})});
        const result = await proxy(request);

        expect(result.type).toBe('error');
        await expect(result.response.json()).resolves.toHaveProperty(
            'error.text', 'You are offline and the requested content is not cached');
    });
});
