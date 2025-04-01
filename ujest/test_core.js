
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
 * Core unit tests
 *
 * This file contains comprehensive unit tests for the core functionality
 * of the SaltOS framework, including error handling, AJAX operations,
 * utility functions, and service worker integration.
 */

/**
 * Load all needed files of the project
 */
const files = `core,gettext,token`.split(',');
for (const i in files) {
    const file = files[i].trim();
    require(`../code/web/js/${file}.js`);
}

/**
 * Reset mocks before each test
 *
 * Initializes mock implementations for console.log and fetch API
 * before each test case runs
 */
beforeEach(() => {
    jest.resetAllMocks();
    jest.spyOn(console, 'log').mockImplementation(() => {});
    global.fetch = jest.fn(() =>
        Promise.resolve({
            json: () => Promise.resolve({message: 'Success'}),
        })
    );
});

/**
 * Restore mocks after each test
 *
 * Restores original implementations of mocked functions
 * after each test case completes
 */
afterEach(() => {
    jest.restoreAllMocks();
});

/**
 * Test suite for error and log reporting
 *
 * Contains tests for core error handling and logging functionality
 */
describe('saltos.core.adderror/addlog', () => {
    /**
     * Setup before each test in this suite
     *
     * Mocks core dependencies including AJAX, token, and gettext functions
     */
    beforeEach(() => {
        jest.spyOn(global.saltos.core, 'ajax').mockImplementation(jest.fn());
        jest.spyOn(global.saltos.token, 'get').mockReturnValue('dummyToken');
        jest.spyOn(global.saltos.gettext, 'get').mockReturnValue('en');
    });

    /**
     * Test error reporting functionality
     *
     * Verifies that error details including stack traces are properly
     * processed and sent to the server
     */
    test('saltos.core.adderror should send error details to the server', async () => {
        const mockMessage = 'Error message';
        const mockSource = 'test.js';
        const mockLineno = 42;
        const mockColno = 21;
        const mockStack = 'Error stack trace';

        // Mock for sourceMappedStackTrace
        global.window.sourceMappedStackTrace = {
            mapStackTrace: jest.fn((stack, callback) => {
                callback(['mapped line 1', 'mapped line 2']);
            }),
        };

        // Call to the tested function
        await global.saltos.core.adderror(mockMessage, mockSource, mockLineno, mockColno, mockStack);

        // Verify that sourceMappedStackTrace.mapStackTrace was called
        expect(global.window.sourceMappedStackTrace.mapStackTrace).toHaveBeenCalledWith(
            mockStack,
            expect.any(Function),
            {filter: expect.any(Function)}
        );

        // Verify that was called with the expected data
        expect(global.saltos.core.ajax).toHaveBeenCalledWith({
            url: 'api/?/add/error',
            data: expect.any(String),
            method: 'post',
            content_type: 'application/json',
            proxy: 'network,queue',
            token: expect.any(String),
            lang: expect.any(String),
        });
    });

    /**
     * Test log reporting functionality
     *
     * Verifies that log messages are properly formatted and sent to the server
     */
    test('saltos.core.addlog should send log message to the server', () => {
        const mockMessage = 'Test log message';

        // Call addlog
        saltos.core.addlog(mockMessage);

        // Check if ajax was called with the correct data
        expect(saltos.core.ajax).toHaveBeenCalledWith({
            url: 'api/?/add/log',
            data: JSON.stringify({msg: mockMessage}),
            method: 'post',
            content_type: 'application/json',
            proxy: 'network,queue',
            token: 'dummyToken',
            lang: 'en',
        });
    });
});

/**
 * Test suite for global error handling
 *
 * Contains tests for window-level error and unhandled promise rejection handlers
 */
describe('window.addEventListener for error and unhandledrejection', () => {
    /**
     * Setup before each test in this suite
     *
     * Mocks the core error reporting function
     */
    beforeEach(() => {
        jest.spyOn(global.saltos.core, 'adderror').mockImplementation(jest.fn());
    });

    /**
     * Test global error handling
     *
     * Verifies that window error events are properly captured and reported
     */
    test('should call saltos.core.adderror when an error occurs', () => {
        // Create a fake error event
        const fakeError = {
            message: 'Test error message',
            filename: 'test.js',
            lineno: 10,
            colno: 5,
            error: {
                stack: 'Test error stack trace',
            },
        };

        // Trigger the event
        window.dispatchEvent(new ErrorEvent('error', fakeError));

        // Check if adderror was called
        expect(saltos.core.adderror).toHaveBeenCalledWith(
            'Test error message',
            'test.js',
            10,
            5,
            'Test error stack trace'
        );
    });

    /**
     * Test unhandled promise rejection handling
     *
     * Verifies that unhandled promise rejections are properly captured and reported
     */
    test('should call saltos.core.adderror when an unhandledrejection occurs', () => {
        // Create a fake rejection event with all necessary fields
        const fakeRejection = {
            name: 'TypeError',
            message: 'Test rejection message',
            fileName: 'test.js',
            lineNumber: 20,
            columnNumber: 10,
            stack: 'Test rejection stack trace',
        };

        // Create the CustomEvent without reason
        const fakeRejectionEvent = new CustomEvent('unhandledrejection', {
            bubbles: true,
            cancelable: true,
        });

        // Assign the reason object to the event
        fakeRejectionEvent.reason = fakeRejection;

        // Trigger the event
        window.dispatchEvent(fakeRejectionEvent);

        // Check if adderror was called
        expect(saltos.core.adderror).toHaveBeenCalledWith(
            'TypeError: Test rejection message',
            'test.js',
            20,
            10,
            'Test rejection stack trace'
        );
    });
});

/**
 * Test parameter validation functionality
 *
 * Verifies that missing parameters are properly initialized with default values
 */
test('saltos.core.check_params', () => {
    let field = {nada: 'x', id: undefined};
    saltos.core.check_params(field, ['id', 'name']);
    expect(field).toStrictEqual({nada: 'x', id: '', name: ''});
    saltos.core.check_params(field, ['count'], 0);
    expect(field).toStrictEqual({nada: 'x', id: '', name: '', count: 0});
    saltos.core.check_params(field, ['data'], []);
    expect(field).toStrictEqual({nada: 'x', id: '', name: '', count: 0, data: []});
    saltos.core.check_params(field, ['header'], {});
    expect(field).toStrictEqual({nada: 'x', id: '', name: '', count: 0, data: [], header: {}});
});

/**
 * Test unique ID generation
 *
 * Verifies that generated IDs are unique and follow expected format
 */
test('saltos.core.uniqid', () => {
    const total = 1000000;
    const array = [];
    for (let i = 0; i < total; i++) {
        array.push(saltos.core.uniqid());
    }
    expect(array.length).toBe(total);
    const copia = new Set(array);
    expect(array.length).toBe(copia.size);
    expect(array[0].length).toBeGreaterThanOrEqual(10);
    expect(array[0].startsWith('id')).toBe(true);
    expect(typeof array[0]).toBe('string');
});

/**
 * Test suite for visibility detection
 *
 * Contains tests for the when_visible utility function
 */
describe('when_visible', () => {
    /**
     * Setup before each test in this suite
     *
     * Configures fake timers and clears DOM
     */
    beforeEach(() => {
        jest.useFakeTimers();
        document.body.innerHTML = ''; // Clears the DOM before each test
    });

    /**
     * Cleanup after each test in this suite
     *
     * Clears timers and restores real timer implementation
     */
    afterEach(() => {
        jest.clearAllTimers();
        jest.useRealTimers();
    });

    /**
     * Test basic visibility detection
     *
     * Verifies callback is executed when element becomes visible
     */
    test('executes the callback when the object becomes visible', () => {
        const mockFn = jest.fn();
        const div = document.createElement('div');
        document.body.appendChild(div);
        saltos.core.when_visible(div, mockFn);
        jest.advanceTimersByTime(50);
        expect(mockFn).not.toHaveBeenCalled();
        Object.defineProperty(div, 'offsetParent', {get: () => div}); // Simulates visibility
        jest.advanceTimersByTime(50);
        expect(mockFn).toHaveBeenCalledTimes(1);
    });

    /**
     * Test visibility detection with ID
     *
     * Verifies function works with elements that have IDs
     */
    test('works setting the id as test-div', () => {
        const mockFn = jest.fn();
        const div = document.createElement('div');
        div.setAttribute('id', 'test-div');
        document.body.appendChild(div);
        saltos.core.when_visible(div, mockFn);
        jest.advanceTimersByTime(50);
        expect(mockFn).not.toHaveBeenCalled();
        Object.defineProperty(div, 'offsetParent', {get: () => div}); // Simulates visibility
        jest.advanceTimersByTime(50);
        expect(mockFn).toHaveBeenCalledTimes(1);
    });

    /**
     * Test visibility detection with string ID
     *
     * Verifies function works when passed an element ID string
     */
    test('works with an string id instead of an object', () => {
        const mockFn = jest.fn();
        const div = document.createElement('div');
        div.setAttribute('id', 'test-div');
        document.body.appendChild(div);
        saltos.core.when_visible('test-div', mockFn);
        jest.advanceTimersByTime(50);
        expect(mockFn).not.toHaveBeenCalled();
        Object.defineProperty(div, 'offsetParent', {get: () => div}); // Simulates visibility
        jest.advanceTimersByTime(50);
        expect(mockFn).toHaveBeenCalledTimes(1);
    });

    /**
     * Test invalid input handling
     *
     * Verifies function throws error for unsupported input types
     */
    test('throws an error for unsupported obj type', () => {
        const invalidObj = 123; // Passing a number instead of a string or object
        const mockFn = jest.fn();
        expect(() => {
            saltos.core.when_visible(invalidObj, mockFn);
        }).toThrowError('Unknown when_visible obj typeof number');
    });

    /**
     * Test delayed element attachment
     *
     * Verifies function works when element is added to DOM after initialization
     */
    test('append the object after some iterations', () => {
        const mockFn = jest.fn();
        const div = document.createElement('div');
        saltos.core.when_visible(div, mockFn);
        jest.advanceTimersByTime(50);
        document.body.appendChild(div);
        expect(mockFn).not.toHaveBeenCalled();
        Object.defineProperty(div, 'offsetParent', {get: () => div}); // Simulates visibility
        jest.advanceTimersByTime(50);
        expect(mockFn).toHaveBeenCalledTimes(1);
    });

    /**
     * Test element removal handling
     *
     * Verifies function throws error if element is removed before becoming visible
     */
    test('throws an error if the object disappears before being visible', () => {
        const mockFn = jest.fn();
        const div = document.createElement('div');
        div.setAttribute('id', 'test-div');
        document.body.appendChild(div);
        const promise = new Promise((resolve, reject) => {
            try {
                saltos.core.when_visible(div, mockFn);
            } catch (error) {
                reject(error);
            }
        });
        jest.advanceTimersByTime(50);
        expect(mockFn).not.toHaveBeenCalled();
        document.body.removeChild(div);
        expect(async () => {
            jest.advanceTimersByTime(50);
            await promise;
        }).rejects.toThrowError('#test-div not found');
    });
});

/**
 * Test key code extraction
 *
 * Verifies function correctly extracts key codes from different event properties
 */
test('saltos.core.get_keycode', () => {
    expect(saltos.core.get_keycode({keyCode: 23})).toBe(23);
    expect(saltos.core.get_keycode({which: 34})).toBe(34);
    expect(saltos.core.get_keycode({charCode: 45})).toBe(45);
    expect(saltos.core.get_keycode({nada: 56})).toBe(0);
});

/**
 * Test key name resolution
 *
 * Verifies function correctly maps key codes to common key names
 */
test('saltos.core.get_keyname', () => {
    expect(saltos.core.get_keyname({keyCode: 8})).toBe('backspace');
    expect(saltos.core.get_keyname({keyCode: 9})).toBe('tab');
    expect(saltos.core.get_keyname({keyCode: 13})).toBe('enter');
    expect(saltos.core.get_keyname({keyCode: 27})).toBe('escape');
    expect(saltos.core.get_keyname({keyCode: 32})).toBe('space');
});

/**
 * Test suite for HTML element creation
 *
 * Contains tests for the html utility function
 */
describe('saltos.core.html', () => {
    /**
     * Test single argument usage
     *
     * Verifies function creates element from HTML string with optimization
     */
    test('creates a div with inner HTML when only one argument is passed', () => {
        const result = saltos.core.html('<p>Hello</p>');
        // Since there is only one child, optimize returns <p> instead of the <div>
        expect(result).toBeInstanceOf(HTMLElement);
        expect(result.tagName.toLowerCase()).toBe('p');
        expect(result.innerHTML).toBe('Hello');
    });

    /**
     * Test two argument usage
     *
     * Verifies function creates specified element type with content
     */
    test('creates the specified element with inner HTML when two arguments are passed', () => {
        const result = saltos.core.html('span', 'Test content');
        // No optimization occurs because there is not a single direct child
        expect(result).toBeInstanceOf(HTMLElement);
        expect(result.tagName.toLowerCase()).toBe('span');
        expect(result.innerHTML).toBe('Test content');
    });

    /**
     * Test HTML trimming
     *
     * Verifies function trims whitespace from HTML content
     */
    test('trims the inner HTML before setting it', () => {
        const result = saltos.core.html('div', '   <b>Trimmed</b>   ');
        // Since there is only one child (<b>), optimize returns <b> instead of the <div>
        expect(result.tagName.toLowerCase()).toBe('b');
        expect(result.innerHTML).toBe('Trimmed');
    });

    /**
     * Test multiple children handling
     *
     * Verifies function preserves container element when multiple children exist
     */
    test('does not optimize if there are multiple children', () => {
        const result = saltos.core.html('div', '<span>One</span><span>Two</span>');
        // Since there are multiple children, optimization does not apply, and the <div> remains
        expect(result.tagName.toLowerCase()).toBe('div');
        expect(result.children.length).toBe(2);
    });

    /**
     * Test single child optimization
     *
     * Verifies function optimizes by returning single child element directly
     */
    test('optimizes and returns the single child if present', () => {
        const result = saltos.core.html('div', '<p>Only Child</p>');
        // Since there is only one child (<p>), optimize returns <p> instead of the <div>
        expect(result.tagName.toLowerCase()).toBe('p');
        expect(result.innerHTML).toBe('Only Child');
    });
});

/**
 * Test suite for AJAX functionality
 *
 * Contains comprehensive tests for the core AJAX implementation
 */
describe('saltos.core.ajax', () => {
    /**
     * Test successful GET request
     *
     * Verifies proper request construction and success callback handling
     */
    test('makes a successful GET request and calls success callback', async () => {
        const mockSuccess = jest.fn();
        const mockResponse = {
            ok: true,
            json: jest.fn().mockResolvedValue({message: 'Success'}),
            headers: new Map([
                ['content-type', 'application/json'],
                ['x-about', 'SaltOS 4.0'],
                ['x-proxy-type', 'network'],
            ]),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await saltos.core.ajax({
            url: '/test',
            method: 'GET',
            success: mockSuccess,
            headers: {},
            content_type: 'application/json',
            proxy: 'network,cache',
            token: 'someToken',
            lang: 'en_US',
        });

        expect(global.fetch).toHaveBeenCalledWith('/test', expect.objectContaining({
            method: 'GET',
        }));
        expect(mockSuccess).toHaveBeenCalledWith({message: 'Success'}, mockResponse);
    });

    /**
     * Test error response handling
     *
     * Verifies proper error callback invocation for non-200 responses
     */
    test('handles non-200 responses and calls error callback', async () => {
        const mockError = jest.fn();
        const mockResponse = {
            ok: false,
            headers: new Map(),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await saltos.core.ajax({url: '/test', method: 'GET', error: mockError});

        expect(mockError).toHaveBeenCalledWith(mockResponse);
    });

    /**
     * Test POST request handling
     *
     * Verifies proper construction of POST requests with body content
     */
    test('makes a POST request with a body', async () => {
        const mockSuccess = jest.fn();
        const mockResponse = {
            ok: true,
            json: jest.fn().mockResolvedValue({message: 'Posted'}),
            headers: new Map([['content-type', 'application/json']]),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await saltos.core.ajax({url: '/test', method: 'POST', data: 'payload', success: mockSuccess});

        expect(global.fetch).toHaveBeenCalledWith('/test', expect.objectContaining({
            method: 'POST',
            body: 'payload',
        }));
    });

    /**
     * Test request abortion
     *
     * Verifies proper handling of aborted requests
     */
    test('calls abort callback when request is aborted', async () => {
        const mockAbort = jest.fn();
        global.fetch.mockRejectedValue(new DOMException('Aborted', 'AbortError'));

        await saltos.core.ajax({url: '/test', abort: mockAbort, abortable: true});

        expect(mockAbort).toHaveBeenCalled();
    });

    /**
     * Test network failure handling
     *
     * Verifies proper error callback invocation for network failures
     */
    test('calls error callback on network failure', async () => {
        const mockError = jest.fn();
        global.fetch.mockRejectedValue(new TypeError('Network Error'));

        await saltos.core.ajax({url: '/test', error: mockError});

        expect(mockError).toHaveBeenCalled();
    });

    /**
     * Test unexpected error handling
     *
     * Verifies proper propagation of unexpected errors
     */
    test('throws an error for unexpected failures', async () => {
        global.fetch.mockRejectedValue(new Error('Unexpected failure'));

        await expect(
            saltos.core.ajax({
                url: '/test',
                data: '',
                method: 'GET',
                success: jest.fn(),  // Mock de success, pero no se ejecutará
                error: (err) => { throw err; },  // Re-lanza el error para que Jest lo capture
            })
        ).rejects.toThrow('Unexpected failure');
    });

    /**
     * Test unsupported method handling
     *
     * Verifies proper error throwing for unsupported HTTP methods
     */
    test('throws an error for unsupported HTTP method', () => {
        expect(() => saltos.core.ajax({url: '/test', method: 'PUT'}))
            .toThrowError('Unknown PUT method');
    });

    /**
     * Test XML response handling
     *
     * Verifies proper parsing of XML response content
     */
    test('handles XML response correctly', async () => {
        const mockSuccess = jest.fn();
        const mockResponse = {
            ok: true,
            text: jest.fn().mockResolvedValue('<root><message>XML Data</message></root>'),
            headers: new Map([['content-type', 'application/xml']]),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await saltos.core.ajax({url: '/xml', success: mockSuccess});

        expect(mockSuccess).toHaveBeenCalled();
        const parsedXML = mockSuccess.mock.calls[0][0]; // First argument of the first call
        expect(parsedXML).toBeInstanceOf(Document);
        expect(parsedXML.documentElement.tagName).toBe('root');
    });

    /**
     * Test plain text response handling
     *
     * Verifies proper handling of plain text responses
     */
    test('handles plain text response correctly', async () => {
        const mockSuccess = jest.fn();
        const mockResponse = {
            ok: true,
            text: jest.fn().mockResolvedValue('Plain text response'),
            headers: new Map([['content-type', 'text/plain']]),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await saltos.core.ajax({url: '/text', success: mockSuccess});

        expect(mockSuccess).toHaveBeenCalledWith('Plain text response', mockResponse);
    });
});

/**
 * Test key normalization
 *
 * Verifies function properly removes suffixes from keys
 */
test('saltos.core.fix_key', () => {
    expect(saltos.core.fix_key('item')).toBe('item');
    expect(saltos.core.fix_key('item#1')).toBe('item');
    expect(saltos.core.fix_key(['item#1', 'item#2'])).toStrictEqual(['item', 'item']);
    expect(saltos.core.fix_key({'item#1': 'item#1', 'item#2': 'item#2'}))
        .toStrictEqual({'item#1': 'item', 'item#2': 'item'});
});

/**
 * Test object copying
 *
 * Verifies function creates proper shallow copies of objects
 */
test('saltos.core.copy_object', () => {
    expect(saltos.core.copy_object('item')).toBe('item');
    expect(saltos.core.copy_object('item#1')).toBe('item#1');
    expect(saltos.core.copy_object(['item#1', 'item#2'])).toStrictEqual(['item#1', 'item#2']);
    expect(saltos.core.copy_object({'item#1': 'item#1', 'item#2': 'item#2'}))
        .toStrictEqual({'item#1': 'item#1', 'item#2': 'item#2'});
});

/**
 * Test suite for resource loading
 *
 * Contains tests for the require functionality
 */
describe('saltos.core.require', () => {
    /**
     * Setup before each test in this suite
     *
     * Configures fake timers for testing asynchronous operations
     */
    beforeEach(() => {
        jest.useFakeTimers();
    });

    /**
     * Cleanup after each test in this suite
     *
     * Restores real timer implementation
     */
    afterEach(() => {
        jest.useRealTimers();
    });

    /**
     * Test JavaScript file loading
     *
     * Verifies proper loading and injection of JavaScript files
     */
    test('loads a JavaScript file successfully', async () => {
        const mockCallback = jest.fn();
        const mockResponse = {
            ok: true,
            text: jest.fn().mockResolvedValue('console.log("Loaded JS");'),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await new Promise(resolve => {
            saltos.core.require(['/test.js'], () => {
                resolve();
            });
        });

        expect(fetch).toHaveBeenCalledWith('/test.js', expect.any(Object));
        expect(document.head.innerHTML).toContain('<script');
        expect(mockCallback).not.toHaveBeenCalled(); // Execute only after everything has loaded
    });

    /**
     * Test CSS file loading
     *
     * Verifies proper loading and injection of CSS files
     */
    test('loads a CSS file successfully', async () => {
        const mockCallback = jest.fn();
        const mockResponse = {
            ok: true,
            text: jest.fn().mockResolvedValue('body { background: red; }'),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await new Promise(resolve => {
            saltos.core.require(['/test.css'], () => {
                resolve();
            });
        });

        expect(fetch).toHaveBeenCalledWith('/test.css', expect.any(Object));
        expect(document.head.innerHTML).toContain('<style');
    });

    /**
     * Test cached resource handling
     *
     * Verifies that already loaded resources are not reloaded
     */
    test('does not reload already loaded files', async () => {
        const mockCallback = jest.fn();
        saltos.core.__require['/alreadyLoaded.js'] = 'load';

        await new Promise(resolve => {
            saltos.core.require(['/alreadyLoaded.js'], () => {
                resolve();
            });
        });

        expect(fetch).not.toHaveBeenCalled();
    });

    /**
     * Test pending resource handling
     *
     * Verifies proper waiting behavior for resources already being loaded
     */
    test('waits when file is already loading', async () => {
        saltos.core.__require['/test.js'] = 'loading';
        const promise = saltos.core.require(['/test.js'], jest.fn());
        jest.advanceTimersByTime(1);
        await promise;
        expect(saltos.core.__require['/test.js']).toBe('loading');
    });

    /**
     * Test module file loading
     *
     * Verifies proper handling of JavaScript module files
     */
    test('loads a JavaScript module file successfully', async () => {
        const mockCallback = jest.fn();
        const mockResponse = {
            ok: true,
            text: jest.fn().mockResolvedValue('console.log("Loaded Module");'),
        };

        global.fetch.mockResolvedValue(mockResponse);

        await new Promise(resolve => {
            saltos.core.require(['/test.mjs'], () => {
                resolve();
            });
        });

        expect(fetch).toHaveBeenCalledWith('/test.mjs', expect.any(Object));
        expect(document.head.innerHTML).toContain('<script');
        expect(mockCallback).not.toHaveBeenCalled(); // Execute only after everything has loaded
    });

    /**
     * Test hashed resource loading
     *
     * Verifies proper handling of resources with cache-busting hashes
     */
    test('loads a CSS file with hash successfully', async () => {
        const mockCallback = jest.fn();
        const mockResponse = {
            ok: true,
            text: jest.fn().mockResolvedValue('body { background: red; }'),
        };
        const hash = md5('body { background: red; }');

        global.fetch.mockResolvedValue(mockResponse);

        await new Promise(resolve => {
            saltos.core.require(['/test.css?' + hash], () => {
                resolve();
            });
        });

        expect(fetch).toHaveBeenCalledWith('/test.css?' + hash, expect.any(Object));
        expect(document.head.innerHTML).toContain('<style');
    });
});

/**
 * Test boolean evaluation
 *
 * Verifies proper conversion of various values to boolean
 */
test('saltos.core.eval_bool', () => {
    expect(saltos.core.eval_bool(undefined)).toBe(false);
    expect(saltos.core.eval_bool(null)).toBe(false);
    expect(saltos.core.eval_bool(true)).toBe(true);
    expect(saltos.core.eval_bool(false)).toBe(false);
    expect(saltos.core.eval_bool(1)).toBe(true);
    expect(saltos.core.eval_bool(-1)).toBe(true);
    expect(saltos.core.eval_bool(0)).toBe(false);
    expect(saltos.core.eval_bool('')).toBe(false);
    expect(saltos.core.eval_bool('1')).toBe(true);
    expect(saltos.core.eval_bool('0')).toBe(false);
    expect(saltos.core.eval_bool('on')).toBe(true);
    expect(saltos.core.eval_bool('off')).toBe(false);
    expect(saltos.core.eval_bool('yes')).toBe(true);
    expect(saltos.core.eval_bool('no')).toBe(false);
    expect(() => { saltos.core.eval_bool('-1'); }).toThrow(Error);
    expect(() => { saltos.core.eval_bool('-1'); }).toThrow('Unknown eval_bool typeof string');
    expect(() => { saltos.core.eval_bool([]); }).toThrow(Error);
    expect(() => { saltos.core.eval_bool([]); }).toThrow('Unknown eval_bool typeof object');
    expect(() => { saltos.core.eval_bool({}); }).toThrow(Error);
    expect(() => { saltos.core.eval_bool({}); }).toThrow('Unknown eval_bool typeof object');
});

/**
 * Test string conversion
 *
 * Verifies proper string conversion of various values
 */
test('saltos.core.toString', () => {
    expect(saltos.core.toString(undefined)).toBe('undefined');
    expect(saltos.core.toString(null)).toBe('null');
    expect(saltos.core.toString(true)).toBe('true');
    expect(saltos.core.toString(false)).toBe('false');
    expect(saltos.core.toString(1)).toBe('1');
    expect(saltos.core.toString(-1)).toBe('-1');
    expect(saltos.core.toString(0)).toBe('0');
    expect(saltos.core.toString('')).toBe('');
    expect(saltos.core.toString('1')).toBe('1');
    expect(saltos.core.toString('0')).toBe('0');
    expect(() => { saltos.core.toString([]); }).toThrow(Error);
    expect(() => { saltos.core.toString([]); }).toThrow('Unknown toString typeof object');
    expect(() => { saltos.core.toString({}); }).toThrow(Error);
    expect(() => { saltos.core.toString({}); }).toThrow('Unknown toString typeof object');
});

/**
 * Test attribute value detection
 *
 * Verifies proper identification of attribute-value objects
 */
test('saltos.core.is_attr_value', () => {
    expect(saltos.core.is_attr_value(undefined)).toBe(false);
    expect(saltos.core.is_attr_value(null)).toBe(false);
    expect(saltos.core.is_attr_value(true)).toBe(false);
    expect(saltos.core.is_attr_value(false)).toBe(false);
    expect(saltos.core.is_attr_value(1)).toBe(false);
    expect(saltos.core.is_attr_value(-1)).toBe(false);
    expect(saltos.core.is_attr_value(0)).toBe(false);
    expect(saltos.core.is_attr_value('')).toBe(false);
    expect(saltos.core.is_attr_value('1')).toBe(false);
    expect(saltos.core.is_attr_value('0')).toBe(false);
    expect(saltos.core.is_attr_value([])).toBe(false);
    expect(saltos.core.is_attr_value(['#attr', 'value'])).toBe(false);
    expect(saltos.core.is_attr_value({})).toBe(false);
    expect(saltos.core.is_attr_value({'#attr': '', 'value': ''})).toBe(true);
});

/**
 * Test attribute-value joining
 *
 * Verifies proper merging of attribute and value objects
 */
test('saltos.core.join_attr_value', () => {
    expect(saltos.core.join_attr_value(undefined)).toBe(undefined);
    expect(saltos.core.join_attr_value(null)).toBe(null);
    expect(saltos.core.join_attr_value(true)).toBe(true);
    expect(saltos.core.join_attr_value(false)).toBe(false);
    expect(saltos.core.join_attr_value(1)).toBe(1);
    expect(saltos.core.join_attr_value(-1)).toBe(-1);
    expect(saltos.core.join_attr_value(0)).toBe(0);
    expect(saltos.core.join_attr_value('')).toBe('');
    expect(saltos.core.join_attr_value('1')).toBe('1');
    expect(saltos.core.join_attr_value('0')).toBe('0');
    expect(saltos.core.join_attr_value([])).toStrictEqual([]);
    expect(saltos.core.join_attr_value(['#attr', 'value'])).toStrictEqual(['#attr', 'value']);
    expect(saltos.core.join_attr_value({})).toStrictEqual({});
    expect(saltos.core.join_attr_value({'#attr': '', 'value': ''})).toStrictEqual({});
    expect(saltos.core.join_attr_value({'#attr': '', 'value': 'val3'})).toStrictEqual({value: 'val3'});
    expect(saltos.core.join_attr_value({'#attr': '', 'value': {key3: 'val3', key4: 'val4'}}))
        .toStrictEqual({key3: 'val3', key4: 'val4'});
    expect(saltos.core.join_attr_value({'#attr': {key1: 'val1', key2: 'val2'}, 'value': ''}))
        .toStrictEqual({key1: 'val1', key2: 'val2'});
    expect(saltos.core.join_attr_value({'#attr': {key1: 'val1', key2: 'val2'}, 'value': 'val3'}))
        .toStrictEqual({key1: 'val1', key2: 'val2', value: 'val3'});
    expect(saltos.core
        .join_attr_value({'#attr': {key1: 'val1', key2: 'val2'}, 'value': {key3: 'val3', key4: 'val4'}}))
        .toStrictEqual({key1: 'val1', key2: 'val2', key3: 'val3', key4: 'val4'});
});

/**
 * saltos.core.encode_bad_chars
 *
 * This function performs the test of the encode_bad_chars function
 */
test('saltos.core.encode_bad_chars', () => {
    expect(saltos.core.encode_bad_chars('')).toBe('');
    expect(saltos.core.encode_bad_chars('abc')).toBe('abc');
    expect(saltos.core.encode_bad_chars('ABC')).toBe('abc');
    expect(saltos.core.encode_bad_chars('ABC123')).toBe('abc123');
    expect(saltos.core.encode_bad_chars('ABC-123')).toBe('abc_123');
    expect(saltos.core.encode_bad_chars('---ABC---123---')).toBe('abc_123');
    expect(saltos.core.encode_bad_chars('---ABC---123---', '-')).toBe('abc-123');
    expect(saltos.core.encode_bad_chars('---ABC---123---', ' ')).toBe('abc 123');
    expect(saltos.core.encode_bad_chars('ÁÀáà')).toBe('aaaa');
    expect(saltos.core.encode_bad_chars('asd@#~$%&¡!¿?+-*/', '_', '+-*/')).toBe('asd_+-*/');
});

/**
 * saltos.core.__get_code_from_file_and_line
 *
 * This function performs the test of the __get_code_from_file_and_line function
 */
test('saltos.core.__get_code_from_file_and_line', () => {
    expect(saltos.core.__get_code_from_file_and_line()).toBe('unknown:unknown');
    expect(saltos.core.__get_code_from_file_and_line('pepe', 123)).toBe('pepe:123');
    expect(saltos.core.__get_code_from_file_and_line('pepe.js', 123)).toBe('pepe:123');
    expect(saltos.core.__get_code_from_file_and_line('nada/nada/pepe.js', 123)).toBe('pepe:123');
});

/**
 * saltos.core.timestamp
 *
 * This function performs the test of the timestamp function
 */
test('saltos.core.timestamp', () => {
    const timestamp = saltos.core.timestamp();
    expect(timestamp + 1).toBeGreaterThanOrEqual(saltos.core.timestamp());
    expect(timestamp + 2).toBeGreaterThanOrEqual(saltos.core.timestamp(1));
    expect(timestamp + 61).toBeGreaterThanOrEqual(saltos.core.timestamp(60));
    expect(timestamp + 3601).toBeGreaterThanOrEqual(saltos.core.timestamp(3600));
    expect(timestamp + 86401).toBeGreaterThanOrEqual(saltos.core.timestamp(86400));
    expect(saltos.core.timestamp().toString().length).toBeGreaterThanOrEqual(10);
    expect(saltos.core.is_number(saltos.core.timestamp())).toBe(true);
});

/**
 * saltos.core.human_size
 *
 * This function performs the test of the human_size function
 */
test('saltos.core.human_size', () => {
    expect(saltos.core.human_size(1073741824, ' ', 'bytes')).toBe('1 Gbytes');
    expect(saltos.core.human_size(1073741823, ' ', 'bytes')).toBe('1024 Mbytes');
    expect(saltos.core.human_size(1048576, ' ', 'bytes')).toBe('1 Mbytes');
    expect(saltos.core.human_size(1048575, ' ', 'bytes')).toBe('1024 Kbytes');
    expect(saltos.core.human_size(1024, ' ', 'bytes')).toBe('1 Kbytes');
    expect(saltos.core.human_size(1023, ' ', 'bytes')).toBe('1023 bytes');

    expect(saltos.core.human_size(1073741824, ' ')).toBe('1 G');
    expect(saltos.core.human_size(1073741823, ' ')).toBe('1024 M');
    expect(saltos.core.human_size(1048576, ' ')).toBe('1 M');
    expect(saltos.core.human_size(1048575, ' ')).toBe('1024 K');
    expect(saltos.core.human_size(1024, ' ')).toBe('1 K');
    expect(saltos.core.human_size(1023, ' ')).toBe('1023 ');

    expect(saltos.core.human_size(1073741824)).toBe('1G');
    expect(saltos.core.human_size(1073741823)).toBe('1024M');
    expect(saltos.core.human_size(1048576)).toBe('1M');
    expect(saltos.core.human_size(1048575)).toBe('1024K');
    expect(saltos.core.human_size(1024)).toBe('1K');
    expect(saltos.core.human_size(1023)).toBe('1023');
});

/**
 * saltos.core.is_number
 *
 * This function performs the test of the is_number function
 */
test('saltos.core.is_number', () => {
    expect(saltos.core.is_number(123)).toBe(true);
    expect(saltos.core.is_number(123.456)).toBe(true);
    expect(saltos.core.is_number('123')).toBe(true);
    expect(saltos.core.is_number('123.456')).toBe(true);
    expect(saltos.core.is_number('asd123')).toBe(false);
    expect(saltos.core.is_number('123asd')).toBe(false);
    expect(saltos.core.is_number(Infinity)).toBe(false);
    expect(saltos.core.is_number(-Infinity)).toBe(false);
    expect(saltos.core.is_number(NaN)).toBe(false);
});

/**
 * Core Module Tests
 *
 * This test suite validates the core functionalities of the SaltOS framework,
 * ensuring that essential features operate correctly under different scenarios.
 */
describe('Core Module Tests', () => {
    /**
     * Test service worker registration
     *
     * This test checks if the service worker is registered properly when the
     * browser supports service workers and the application is running over HTTPS.
     */
    test('Registers service worker if supported and on HTTPS', async () => {
        Object.defineProperty(window, 'location', {
            value: {
                protocol: 'https:',
                href: 'https://127.0.0.1/saltos/code4',
            },
            writable: true,
        });

        navigator.serviceWorker = {
            addEventListener: jest.fn(),
            removeEventListener: jest.fn(),
            register: jest.fn().mockResolvedValue({update: jest.fn().mockResolvedValue()}),
            controller: true,
        };

        document.dispatchEvent(new Event('DOMContentLoaded'));

        expect(navigator.serviceWorker.register).toHaveBeenCalledWith('./proxy.js', {
            updateViaCache: 'none',
        });
    });

    /**
     * Test proxy function messaging
     *
     * This test ensures that the proxy function sends the correct message
     * to the service worker's controller using the `postMessage` method.
     */
    test('Proxy function sends message to service worker', () => {
        navigator.serviceWorker = {controller: {postMessage: jest.fn()}};
        saltos.core.proxy('test_message');
        expect(navigator.serviceWorker.controller.postMessage).toHaveBeenCalledWith('test_message');
    });

    /**
     * Test proxy synchronization on online events
     *
     * This test verifies that the proxy synchronization is triggered correctly
     * when the browser's online event is fired.
     */
    test('Triggers proxy sync on online event', () => {
        saltos.core.proxy = jest.fn();
        window.dispatchEvent(new Event('online'));
        expect(saltos.core.proxy).toHaveBeenCalledWith('sync');
    });

    /**
     * Test network protocol detection
     *
     * This test checks that the `check_network` function accurately detects
     * HTTP and HTTPS protocols by simulating network conditions.
     */
    test('Check network detects protocols correctly', async () => {
        window.open = jest.fn().mockReturnValue({close: jest.fn(), closed: true});
        const result = await saltos.core.check_network();
        expect(result).toEqual({http: true, https: true});
    });
});
