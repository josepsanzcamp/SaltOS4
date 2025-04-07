
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

        const controller = new AbortController();
        expect(fetch).toHaveBeenCalledWith(request, {signal: controller.signal});
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

describe('IndexedDB Queue Functions', () => {
    describe('queue_open', () => {
        test('should resolve with a store object when connection succeeds', async () => {
            const store = await queue_open();
            expect(store).toBeDefined();
            expect(store.name).toBe('saltos');
        });
    });

    describe('queue_push', () => {
        test('should add data to the store', async () => {
            const testData = {name: 'test', value: 123};
            await queue_push(testData);

            const result = await queue_getall();
            expect(result.length).toBe(1);
            expect(result[0].value).toEqual(testData);

            await queue_delete(result[0].key);
        });
    });

    describe('queue_getall', () => {
        test('should return an empty array when store is empty', async () => {
            const result = await queue_getall();
            expect(result).toEqual([]);
        });

        test('should return all items in FIFO order', async () => {
            const data1 = {id: 1, name: 'first'};
            const data2 = {id: 2, name: 'second'};

            await queue_push(data1);
            await queue_push(data2);

            const result = await queue_getall();
            expect(result.length).toBe(2);
            expect(result[0].value).toEqual(data1);
            expect(result[1].value).toEqual(data2);

            await queue_delete(result[0].key);
            await queue_delete(result[1].key);
        });
    });

    describe('queue_delete', () => {
        test('should delete an item by its key', async () => {
            const testData = {name: 'to delete'};
            await queue_push(testData);

            const result = await queue_getall();
            expect(result.length).toBe(1);
            const key = result[0].key;

            await queue_delete(key);

            const result2 = await queue_getall();
            expect(result2.length).toBe(0);
        });

        test('should not throw when deleting non-existent key', async () => {
            await expect(new Promise((resolve) => {
                queue_delete(999);
                setTimeout(resolve, 100);
            })).resolves.not.toThrow();
        });
    });
});

describe('Request Serialization', () => {
    describe('request_serialize', () => {
        test('should correctly serialize a GET request', async () => {
            const request = new Request('https://example.com/api', {
                method: 'GET',
                headers: {'Content-Type': 'application/json'},
                credentials: 'include',
                referrerPolicy: 'no-referrer',
                mode: 'cors'
            });

            const serialized = await request_serialize(request);

            expect(serialized).toEqual({
                url: 'https://example.com/api',
                method: 'GET',
                headers: [['content-type', 'application/json']],
                //~ credentials: 'include',
                //~ referrerPolicy: 'no-referrer',
                //~ mode: 'cors',
            });
        });

        test('should correctly serialize a POST request with body', async () => {
            const request = new Request('https://example.com/api', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({key: 'value'}),
                credentials: 'same-origin'
            });

            const serialized = await request_serialize(request);

            expect(serialized).toEqual({
                url: 'https://example.com/api',
                method: 'POST',
                headers: [['content-type', 'application/json']],
                //~ credentials: 'same-origin',
                //~ referrerPolicy: 'no-referrer-when-downgrade', // default
                //~ mode: 'cors', // default
                body: '{"key":"value"}',
            });
        });

        test('should handle empty headers', async () => {
            const request = new Request('https://example.com/api', {
                method: 'GET'
            });

            const serialized = await request_serialize(request);

            expect(serialized.headers).toEqual([]);
        });
    });

    describe('request_unserialize', () => {
        test('should correctly deserialize a GET request', () => {
            const serialized = {
                url: 'https://example.com/api',
                method: 'GET',
                headers: [['content-type', 'application/json']],
                credentials: 'include',
                referrerPolicy: 'no-referrer',
                mode: 'cors'
            };

            const request = request_unserialize(serialized);

            expect(request.url).toBe(serialized.url);
            expect(request.method).toBe(serialized.method);
            //~ expect(request.credentials).toBe(serialized.credentials);
            //~ expect(request.referrerPolicy).toBe(serialized.referrerPolicy);
            //~ expect(request.mode).toBe(serialized.mode);
            expect(request.headers.get('content-type')).toBe('application/json');
        });

        test('should correctly deserialize a POST request with body', () => {
            const serialized = {
                url: 'https://example.com/api',
                method: 'POST',
                headers: [['content-type', 'application/json']],
                body: '{"key":"value"}',
                credentials: 'same-origin'
            };

            const request = request_unserialize(serialized);

            expect(request.method).toBe('POST');
            expect(request.headers.get('content-type')).toBe('application/json');

            return request.text().then(body => {
                expect(body).toBe('{"key":"value"}');
            });
        });

        test('should handle empty headers', () => {
            const serialized = {
                url: 'https://example.com/api',
                method: 'GET',
                headers: []
            };

            const request = request_unserialize(serialized);

            expect(request.headers.get('content-type')).toBeNull();
        });

        test('should use default values for missing properties', () => {
            const serialized = {
                url: 'https://example.com/api',
                method: 'GET'
            };

            const request = request_unserialize(serialized);

            //~ expect(request.credentials).toBe('same-origin'); // default
            //~ expect(request.referrerPolicy).toBe('no-referrer-when-downgrade'); // default
            //~ expect(request.mode).toBe('cors'); // default
        });
    });

    describe('round-trip serialization', () => {
        test('should serialize and deserialize a GET request correctly', async () => {
            const originalRequest = new Request('https://example.com/api', {
                method: 'GET',
                headers: {'X-Custom': 'Value'},
                credentials: 'omit'
            });

            const serialized = await request_serialize(originalRequest);
            const deserialized = request_unserialize(serialized);

            expect(deserialized.url).toBe(originalRequest.url);
            expect(deserialized.method).toBe(originalRequest.method);
            expect(deserialized.credentials).toBe(originalRequest.credentials);
            expect(deserialized.headers.get('x-custom')).toBe('Value');
        });

        test('should serialize and deserialize a POST request with body correctly', async () => {
            const originalRequest = new Request('https://example.com/api', {
                method: 'POST',
                headers: {'Content-Type': 'text/plain'},
                body: 'Hello world'
            });

            const serialized = await request_serialize(originalRequest);
            const deserialized = request_unserialize(serialized);

            expect(deserialized.method).toBe('POST');
            expect(deserialized.headers.get('content-type')).toBe('text/plain');

            const body = await deserialized.text();
            expect(body).toBe('Hello world');
        });
    });
});
