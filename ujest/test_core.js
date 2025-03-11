
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

describe('saltos.core.adderror/addlog', () => {
    beforeEach(() => {
        jest.spyOn(global.saltos.core, 'ajax').mockImplementation(jest.fn());
        jest.spyOn(global.saltos.token, 'get').mockReturnValue('dummyToken');
        jest.spyOn(global.saltos.gettext, 'get').mockReturnValue('en');
    });

    afterEach(() => {
        // Restores all mocked methods.
        jest.restoreAllMocks();
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

describe('window.addEventListener for error and unhandledrejection', () => {
    beforeEach(() => {
        jest.spyOn(global.saltos.core, 'adderror').mockImplementation(jest.fn());
    });

    afterEach(() => {
        // Restores all mocked methods.
        jest.restoreAllMocks();
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
 * saltos.core.get_keycode
 *
 * This function performs the tests of the uniqid function
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
 * This function performs the tests of the uniqid function
 */
test('saltos.core.get_keyname', () => {
    expect(saltos.core.get_keyname({keyCode: 8})).toBe('backspace');
    expect(saltos.core.get_keyname({keyCode: 9})).toBe('tab');
    expect(saltos.core.get_keyname({keyCode: 13})).toBe('enter');
    expect(saltos.core.get_keyname({keyCode: 27})).toBe('escape');
    expect(saltos.core.get_keyname({keyCode: 32})).toBe('space');
});

/**
 * HERE
 */

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
