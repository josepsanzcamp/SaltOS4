
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
 * This file contains the window unit tests
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
describe('saltos.window.open/close', () => {
    /**
     * TODO
     */
    beforeEach(() => {
        jest.spyOn(window, 'open').mockImplementation(jest.fn());
        jest.spyOn(window, 'close').mockImplementation(jest.fn());
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should call window.open with the app prefix', () => {
        saltos.window.open('app/emails');
        expect(window.open).toHaveBeenCalledWith('.#/app/emails');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should call window.open with the http prefix', () => {
        saltos.window.open('http://www.saltos.org');
        expect(window.open).toHaveBeenCalledWith('http://www.saltos.org');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should call window.open with the https prefix', () => {
        saltos.window.open('https://www.saltos.org');
        expect(window.open).toHaveBeenCalledWith('https://www.saltos.org');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should throw an error when call window.open with non supported protocol', () => {
        expect(() => { saltos.window.open('proto://www.saltos.org'); }).toThrow(Error);
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should call window.close', () => {
        saltos.window.close();
        expect(window.close).toHaveBeenCalled();
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.window.listeners', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('set_listener should add a listener for a specific event', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        expect(saltos.window.listeners[`testEvent`]).toBe(mockCallback);
    });

    /**
     * TODO
     *
     * TODO
     */
    test('unset_listener should remove a listener for a specific event', () => {
        saltos.window.unset_listener('testEvent');
        expect(saltos.window.listeners[`testEvent`]).toBeUndefined();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('send should trigger the listener in the same tab when scope is "me"', () => {
        const mockCallback = jest.fn();
        saltos.window.set_listener('testEvent', mockCallback);
        saltos.window.send('testEvent', 'testData', 'me');
        expect(mockCallback).toHaveBeenCalledWith('testData');
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
