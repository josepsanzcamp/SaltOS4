
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
 * Push unit tests
 *
 * This file contains unit tests for the push notification functionality
 * and favicon status updates in the SaltOS framework.
 */

/**
 * Load all needed files of the project
 */
const files = `app,bootstrap,core,gettext,push,storage,token`.split(',');
for (const i in files) {
    const file = files[i].trim();
    require(`../code/web/js/${file}.js`);
}

/**
 * Reset mocks before each test
 *
 * Ensures all Jest mocks are reset before each test case runs
 */
beforeEach(() => {
    jest.resetAllMocks();
});

/**
 * Restore mocks after each test
 *
 * Ensures all Jest mocks are restored to their original implementations
 * after each test case completes
 */
afterEach(() => {
    jest.restoreAllMocks();
});

/**
 * Test suite for saltos.push.fn functionality
 *
 * Contains tests for the push notification system's main function,
 * including various execution conditions and response handling
 */
describe('saltos.push.fn', () => {
    Object.defineProperty(global.navigator, 'onLine', {
        value: true,
        writable: true,
    });

    /**
     * Setup before each test in this suite
     *
     * Initializes mock implementations and resets push notification state
     */
    beforeEach(() => {
        saltos.push.executing = false;
        saltos.push.count = 60;
        saltos.token.get = jest.fn();
        saltos.token.get.mockReturnValue('fake-token');
        navigator.onLine = true;
        jest.spyOn(global.saltos.core, 'ajax').mockImplementation(jest.fn());
    });

    /**
     * Test push function when already executing
     *
     * Verifies that the push function exits early when a push operation
     * is already in progress
     */
    test('should return early if executing is true', () => {
        saltos.push.executing = true;
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * Test push function without token
     *
     * Verifies that the push function exits early when no authentication
     * token is available
     */
    test('should return early if token is not available', () => {
        saltos.token.get.mockReturnValue(null);
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * Test push function when offline
     *
     * Verifies that the push function exits early when the browser
     * is offline
     */
    test('should return early if navigator is offline', () => {
        navigator.onLine = false;
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * Test push function with positive count
     *
     * Verifies that the push function exits early when the countdown
     * counter hasn't reached zero
     */
    test('should return early if count is greater than or equal to 0', () => {
        saltos.push.count = 10;
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * Test push function with successful response
     *
     * Verifies that the push function makes an AJAX call and properly
     * handles a successful response
     */
    test('should call ajax and handle success response', () => {
        saltos.push.count = -1;
        saltos.core.ajax.mockImplementation(({success}) => {
            success({
                key1: {type: 'success', message: 'Test message', timestamp: 1234567891}
            });
        });
        saltos.app.check_response = jest.fn();
        saltos.app.check_response.mockReturnValue(true);
        saltos.push.fn();
        expect(saltos.core.ajax).toHaveBeenCalled();
        expect(saltos.push.count).toBe(60);
        expect(saltos.push.executing).toBe(false);
    });

    /**
     * Test push function with error response
     *
     * Verifies that the push function properly handles an error response
     * from the server
     */
    test('should handle error response', () => {
        saltos.push.count = -1;
        saltos.core.ajax.mockImplementation(({error}) => {
            error('Test error');
        });
        saltos.push.fn();
        expect(saltos.core.ajax).toHaveBeenCalled();
        expect(saltos.push.count).toBe(60);
        expect(saltos.push.executing).toBe(false);
    });
});

/**
 * Test suite for saltos.favicon.fn functionality
 *
 * Contains tests for the favicon status update functionality,
 * including visibility state handling
 */
describe('saltos.favicon.fn', () => {
    Object.defineProperty(document, 'visibilityState', {
        value: 'visible',
        writable: true,
    });

    /**
     * Test favicon function activation
     *
     * Verifies that the favicon update interval starts when
     * the function is activated
     */
    test('should start interval if bool is true and executing is false', () => {
        document.visibilityState = 'unknown';
        saltos.favicon.run();
        expect(saltos.favicon.executing).toBe(true);
    });

    /**
     * Test favicon function deactivation
     *
     * Verifies that the favicon update interval stops when
     * the function is deactivated
     */
    test('should clear interval if bool is false and executing is true', () => {
        document.visibilityState = 'unknown';
        document.querySelector = jest.fn();
        document.querySelector.mockReturnValue({href: 'nada'});
        saltos.favicon.fn(false);
        expect(saltos.favicon.executing).toBe(false);
    });
});
