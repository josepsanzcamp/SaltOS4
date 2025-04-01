
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
 * Window unit tests
 *
 * This file contains unit tests for window management functionality
 * including window opening/closing and cross-tab communication
 */

/**
 * Load all needed files of the project
 */
const files = `core,storage,window`.split(',');
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
 * Test suite for window open/close functionality
 *
 * Contains tests for opening new windows with different URL types
 * and closing the current window
 */
describe('saltos.window.open/close', () => {
    /**
     * Setup before each test in this suite
     *
     * Mocks window.open and window.close functions
     */
    beforeEach(() => {
        jest.spyOn(window, 'open').mockImplementation(jest.fn());
        jest.spyOn(window, 'close').mockImplementation(jest.fn());
    });

    /**
     * Test opening app URLs
     *
     * Verifies that app URLs are properly prefixed with .#/
     * when opening new windows
     */
    test('should call window.open with the app prefix', () => {
        saltos.window.open('app/emails');
        expect(window.open).toHaveBeenCalledWith('.#/app/emails');
    });

    /**
     * Test opening HTTP URLs
     *
     * Verifies that HTTP URLs are passed through unchanged
     * when opening new windows
     */
    test('should call window.open with the http prefix', () => {
        saltos.window.open('http://www.saltos.org');
        expect(window.open).toHaveBeenCalledWith('http://www.saltos.org');
    });

    /**
     * Test opening HTTPS URLs
     *
     * Verifies that HTTPS URLs are passed through unchanged
     * when opening new windows
     */
    test('should call window.open with the https prefix', () => {
        saltos.window.open('https://www.saltos.org');
        expect(window.open).toHaveBeenCalledWith('https://www.saltos.org');
    });

    /**
     * Test unsupported URL protocols
     *
     * Verifies that attempting to open URLs with unsupported protocols
     * throws an error
     */
    test('should throw an error when call window.open with non supported protocol', () => {
        expect(() => { saltos.window.open('proto://www.saltos.org'); }).toThrow(Error);
    });

    /**
     * Test window closing
     *
     * Verifies that the close function properly calls window.close
     */
    test('should call window.close', () => {
        saltos.window.close();
        expect(window.close).toHaveBeenCalled();
    });
});

/**
 * Test suite for window event listeners
 *
 * Contains tests for cross-tab communication functionality
 * including setting listeners and sending events between tabs
 */
describe('saltos.window.listeners', () => {
    /**
     * Test setting event listeners
     *
     * Verifies that listeners can be registered for specific events
     */
    test('set_listener should add a listener for a specific event', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        expect(saltos.window.listeners[`testEvent`]).toBe(mockCallback);
    });

    /**
     * Test removing event listeners
     *
     * Verifies that listeners can be removed for specific events
     */
    test('unset_listener should remove a listener for a specific event', () => {
        saltos.window.unset_listener('testEvent');
        expect(saltos.window.listeners[`testEvent`]).toBeUndefined();
    });

    /**
     * Test sending events to current tab
     *
     * Verifies that events with "me" scope only trigger callbacks
     * in the current tab
     */
    test('send should trigger the listener in the same tab when scope is "me"', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        saltos.window.send('testEvent', 'testData', 'me');
        expect(mockCallback).toHaveBeenCalledWith('testData');
    });

    /**
     * Test sending events to other tabs
     *
     * Verifies that events with "other" scope trigger callbacks
     * in other tabs through localStorage events
     */
    test('send should trigger the listener in other tabs when scope is "other"', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        saltos.window.send('testEvent', 'testData', 'other');
        // Simulate the storage event
        const storageEvent = new StorageEvent('storage', {
            storageArea: window.localStorage,
            key: saltos.storage.get_key('saltos.window.trigger'),
        });
        window.dispatchEvent(storageEvent);
        expect(mockCallback).toHaveBeenCalledWith('testData');
    });

    /**
     * Test sending events to all tabs
     *
     * Verifies that events with "all" scope trigger callbacks
     * in all tabs including the current one
     */
    test('send should trigger the listener in all tabs when scope is "all"', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        saltos.window.send('testEvent', 'testData', 'all');
        // Simulate the storage event
        const storageEvent = new StorageEvent('storage', {
            storageArea: window.localStorage,
            key: saltos.storage.get_key('saltos.window.trigger'),
        });
        window.dispatchEvent(storageEvent);
        expect(mockCallback).toHaveBeenCalledWith('testData');
    });

    /**
     * Test sessionStorage event filtering
     *
     * Verifies that events from sessionStorage don't trigger
     * the cross-tab communication callbacks
     */
    test('storage event listener should not trigger if the event is not from localStorage', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        const storageEvent = new StorageEvent('storage', {
            storageArea: window.sessionStorage,
            key: saltos.storage.get_key('saltos.window.trigger'),
        });
        window.dispatchEvent(storageEvent);
        expect(mockCallback).not.toHaveBeenCalled();
    });

    /**
     * Test event key filtering
     *
     * Verifies that events with incorrect keys don't trigger
     * the cross-tab communication callbacks
     */
    test('storage event listener should not trigger if the key does not match', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        const storageEvent = new StorageEvent('storage', {
            storageArea: window.localStorage,
            key: 'wrongKey',
        });
        window.dispatchEvent(storageEvent);
        expect(mockCallback).not.toHaveBeenCalled();
    });

    /**
     * Test unregistered event filtering
     *
     * Verifies that events for unregistered event names don't trigger
     * any callbacks
     */
    test('storage event listener should not trigger if the event name is not in listeners', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        saltos.storage.getItem = jest.fn();
        saltos.storage.getItem.mockReturnValueOnce('nonExistentEvent');
        const storageEvent = new StorageEvent('storage', {
            storageArea: window.localStorage,
            key: saltos.storage.get_key('saltos.window.trigger'),
        });
        window.dispatchEvent(storageEvent);
        expect(mockCallback).not.toHaveBeenCalled();
    });
});
