
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
 * This file contains the core unit tests
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
 * TODO
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
 * TODO
 */
afterEach(() => {
    jest.restoreAllMocks();
});

/**
 * saltos.core.adderror/addlog
 *
 * This function performs the tests of the adderror and adlog functions
 */
describe('saltos.core.adderror/addlog', () => {
    beforeEach(() => {
        jest.spyOn(global.saltos.core, 'ajax').mockImplementation(jest.fn());
        jest.spyOn(global.saltos.token, 'get').mockReturnValue('dummyToken');
        jest.spyOn(global.saltos.gettext, 'get').mockReturnValue('en');
    });

    /**
     * saltos.core.adderror
     *
     * This function performs the tests of the adderror function
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
     * saltos.core.addlog
     *
     * This function performs the tests of the addlog function
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
 * TODO
 *
 * TODO
 */
describe('window.addEventListener for error and unhandledrejection', () => {
    /**
     * TODO
     */
    beforeEach(() => {
        jest.spyOn(global.saltos.core, 'adderror').mockImplementation(jest.fn());
    });

    /**
     * error
     *
     * This function performs the tests of the error feature
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
     * unhandledrejection
     *
     * This function performs the tests of the unhandledrejection feature
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
 * saltos.core.check_params
 *
 * This function performs the test of the check_params function
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
 * saltos.core.uniqid
 *
 * This function performs the tests of the uniqid function
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
 * saltos.core.when_visible
 *
 * This function performs the tests of the when_visible function
 */
describe('when_visible', () => {
    /**
     * TODO
     */
    beforeEach(() => {
        jest.useFakeTimers();
        document.body.innerHTML = ''; // Clears the DOM before each test
    });

    /**
     * TODO
     */
    afterEach(() => {
        jest.clearAllTimers();
        jest.useRealTimers();
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('throws an error for unsupported obj type', () => {
        const invalidObj = 123; // Passing a number instead of a string or object
        const mockFn = jest.fn();
        expect(() => {
            saltos.core.when_visible(invalidObj, mockFn);
        }).toThrowError('Unknown when_visible obj typeof number');
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
 * saltos.core.get_keycode
 *
 * This function performs the tests of the get_keycode function
 */
test('saltos.core.get_keycode', () => {
    expect(saltos.core.get_keycode({keyCode: 23})).toBe(23);
    expect(saltos.core.get_keycode({which: 34})).toBe(34);
    expect(saltos.core.get_keycode({charCode: 45})).toBe(45);
    expect(saltos.core.get_keycode({nada: 56})).toBe(0);
});

/**
 * saltos.core.get_keyname
 *
 * This function performs the tests of the get_keyname function
 */
test('saltos.core.get_keyname', () => {
    expect(saltos.core.get_keyname({keyCode: 8})).toBe('backspace');
    expect(saltos.core.get_keyname({keyCode: 9})).toBe('tab');
    expect(saltos.core.get_keyname({keyCode: 13})).toBe('enter');
    expect(saltos.core.get_keyname({keyCode: 27})).toBe('escape');
    expect(saltos.core.get_keyname({keyCode: 32})).toBe('space');
});

/**
 * saltos.core.html
 *
 * This function performs the tests of the html function
 */
describe('saltos.core.html', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('creates a div with inner HTML when only one argument is passed', () => {
        const result = saltos.core.html('<p>Hello</p>');
        // Since there is only one child, optimize returns <p> instead of the <div>
        expect(result).toBeInstanceOf(HTMLElement);
        expect(result.tagName.toLowerCase()).toBe('p');
        expect(result.innerHTML).toBe('Hello');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('creates the specified element with inner HTML when two arguments are passed', () => {
        const result = saltos.core.html('span', 'Test content');
        // No optimization occurs because there is not a single direct child
        expect(result).toBeInstanceOf(HTMLElement);
        expect(result.tagName.toLowerCase()).toBe('span');
        expect(result.innerHTML).toBe('Test content');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('trims the inner HTML before setting it', () => {
        const result = saltos.core.html('div', '   <b>Trimmed</b>   ');
        // Since there is only one child (<b>), optimize returns <b> instead of the <div>
        expect(result.tagName.toLowerCase()).toBe('b');
        expect(result.innerHTML).toBe('Trimmed');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('does not optimize if there are multiple children', () => {
        const result = saltos.core.html('div', '<span>One</span><span>Two</span>');
        // Since there are multiple children, optimization does not apply, and the <div> remains
        expect(result.tagName.toLowerCase()).toBe('div');
        expect(result.children.length).toBe(2);
    });

    /**
     * TODO
     *
     * TODO
     */
    test('optimizes and returns the single child if present', () => {
        const result = saltos.core.html('div', '<p>Only Child</p>');
        // Since there is only one child (<p>), optimize returns <p> instead of the <div>
        expect(result.tagName.toLowerCase()).toBe('p');
        expect(result.innerHTML).toBe('Only Child');
    });
});

/**
 * saltos.core.ajax
 *
 * This function performs the tests of the ajax function
 */
describe('saltos.core.ajax', () => {
    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('calls abort callback when request is aborted', async () => {
        const mockAbort = jest.fn();
        global.fetch.mockRejectedValue(new DOMException('Aborted', 'AbortError'));

        await saltos.core.ajax({url: '/test', abort: mockAbort, abortable: true});

        expect(mockAbort).toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('calls error callback on network failure', async () => {
        const mockError = jest.fn();
        global.fetch.mockRejectedValue(new TypeError('Network Error'));

        await saltos.core.ajax({url: '/test', error: mockError});

        expect(mockError).toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('throws an error for unsupported HTTP method', () => {
        expect(() => saltos.core.ajax({url: '/test', method: 'PUT'}))
            .toThrowError('Unknown PUT method');
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
 * saltos.core.fix_key
 *
 * This function performs the test of the fix_key function
 */
test('saltos.core.fix_key', () => {
    expect(saltos.core.fix_key('item')).toBe('item');
    expect(saltos.core.fix_key('item#1')).toBe('item');
    expect(saltos.core.fix_key(['item#1', 'item#2'])).toStrictEqual(['item', 'item']);
    expect(saltos.core.fix_key({'item#1': 'item#1', 'item#2': 'item#2'}))
        .toStrictEqual({'item#1': 'item', 'item#2': 'item'});
});

/**
 * saltos.core.copy_object
 *
 * This function performs the test of the copy_object function
 */
test('saltos.core.copy_object', () => {
    expect(saltos.core.copy_object('item')).toBe('item');
    expect(saltos.core.copy_object('item#1')).toBe('item#1');
    expect(saltos.core.copy_object(['item#1', 'item#2'])).toStrictEqual(['item#1', 'item#2']);
    expect(saltos.core.copy_object({'item#1': 'item#1', 'item#2': 'item#2'}))
        .toStrictEqual({'item#1': 'item#1', 'item#2': 'item#2'});
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.core.require', () => {
    /**
     * TODO
     */
    beforeEach(() => {
        jest.useFakeTimers();
    });

    /**
     * TODO
     */
    afterEach(() => {
        jest.useRealTimers();
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('waits when file is already loading', async () => {
        saltos.core.__require['/test.js'] = 'loading';
        const promise = saltos.core.require(['/test.js'], jest.fn());
        jest.advanceTimersByTime(1);
        await promise;
        expect(saltos.core.__require['/test.js']).toBe('loading');
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
 * saltos.core.eval_bool
 *
 * This function performs the test of the eval_bool function
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
 * saltos.core.toString
 *
 * This function performs the test of the toString function
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
 * saltos.core.is_attr_value
 *
 * This function performs the test of the is_attr_value function
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
 * saltos.core.join_attr_value
 *
 * This function performs the test of the join_attr_value function
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
 * TODO
 *
 * TODO
 */
describe('Core Module Tests', () => {
    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('Proxy function sends message to service worker', () => {
        navigator.serviceWorker = {controller: {postMessage: jest.fn()}};
        saltos.core.proxy('test_message');
        expect(navigator.serviceWorker.controller.postMessage).toHaveBeenCalledWith('test_message');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Triggers proxy sync on online event', () => {
        saltos.core.proxy = jest.fn();
        window.dispatchEvent(new Event('online'));
        expect(saltos.core.proxy).toHaveBeenCalledWith('sync');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Check network detects protocols correctly', async () => {
        window.open = jest.fn().mockReturnValue({close: jest.fn(), closed: true});
        const result = await saltos.core.check_network();
        expect(result).toEqual({http: true, https: true});
    });
});
