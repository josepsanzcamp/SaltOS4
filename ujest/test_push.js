
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
 * This file contains the push unit tests
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
 * TODO
 *
 * TODO
 */
describe('saltos.push.fn', () => {
    Object.defineProperty(global.navigator, 'onLine', {
        value: true,
        writable: true,
    });

    /**
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should return early if executing is true', () => {
        saltos.push.executing = true;
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should return early if token is not available', () => {
        saltos.token.get.mockReturnValue(null);
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should return early if navigator is offline', () => {
        navigator.onLine = false;
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should return early if count is greater than or equal to 0', () => {
        saltos.push.count = 10;
        saltos.push.fn();
        expect(saltos.core.ajax).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
 * TODO
 *
 * TODO
 */
describe('saltos.favicon.fn', () => {
    Object.defineProperty(document, 'visibilityState', {
        value: 'visible',
        writable: true,
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should start interval if bool is true and executing is false', () => {
        document.visibilityState = 'unknown';
        saltos.favicon.run();
        expect(saltos.favicon.executing).toBe(true);
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should clear interval if bool is false and executing is true', () => {
        document.visibilityState = 'unknown';
        document.querySelector = jest.fn();
        document.querySelector.mockReturnValue({href: 'nada'});
        saltos.favicon.fn(false);
        expect(saltos.favicon.executing).toBe(false);
    });
});
