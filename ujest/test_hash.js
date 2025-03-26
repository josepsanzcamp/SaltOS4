
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
 * Hash unit tests
 *
 * This file contains the hash unit tests
 */

/**
 * Load all needed files of the project
 */
const files = `hash`.split(',');
for (const i in files) {
    const file = files[i].trim();
    require(`../code/web/js/${file}.js`);
}

/**
 * TODO
 */
beforeEach(() => {
    jest.resetAllMocks();
    jest.spyOn(window.history, 'replaceState').mockImplementation(jest.fn());
    jest.spyOn(window.history, 'pushState').mockImplementation(jest.fn());
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
describe('saltos.hash.__helper', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should remove leading #', () => {
        expect(saltos.hash.__helper('#test')).toBe('test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should remove leading /', () => {
        expect(saltos.hash.__helper('/test')).toBe('test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should remove both leading # and /', () => {
        expect(saltos.hash.__helper('#/test')).toBe('test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should return empty string for empty input', () => {
        expect(saltos.hash.__helper('')).toBe('');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should not modify clean hash', () => {
        expect(saltos.hash.__helper('test')).toBe('test');
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.hash.get', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should return current hash without #', () => {
        window.location.hash = '#test';
        expect(saltos.hash.get()).toBe('test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should return empty string when no hash', () => {
        window.location.hash = '';
        expect(saltos.hash.get()).toBe('');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should remove leading /', () => {
        window.location.hash = '#/test';
        expect(saltos.hash.get()).toBe('test');
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.hash.set', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should set new hash with proper format', () => {
        window.location.hash = '#old';
        const result = saltos.hash.set('new');
        expect(result).toBe(true);
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/new');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should not set same hash', () => {
        window.location.hash = '#same';
        const result = saltos.hash.set('same');
        expect(result).toBe(false);
        expect(window.history.replaceState).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle hash with #', () => {
        saltos.hash.set('#test');
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle hash with /', () => {
        saltos.hash.set('/test');
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle empty hash', () => {
        saltos.hash.set('');
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/');
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.hash.add', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should add new hash with proper format', () => {
        window.location.hash = '#old';
        const result = saltos.hash.add('new');
        expect(result).toBe(true);
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/new');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should not add same hash', () => {
        window.location.hash = '#same';
        const result = saltos.hash.add('same');
        expect(result).toBe(false);
        expect(window.history.pushState).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle hash with #', () => {
        saltos.hash.add('#test');
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle hash with /', () => {
        saltos.hash.add('/test');
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle empty hash', () => {
        saltos.hash.add('');
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/');
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.hash.url2hash', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should extract hash from URL', () => {
        const url = 'http://example.com/#test';
        expect(saltos.hash.url2hash(url)).toBe('test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle URL with / after #', () => {
        const url = 'http://example.com/#/test';
        expect(saltos.hash.url2hash(url)).toBe('test');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should return empty string when no hash', () => {
        const url = 'http://example.com/';
        expect(saltos.hash.url2hash(url)).toBe('');
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.hash.trigger', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should dispatch hashchange event', () => {
        window.dispatchEvent = jest.fn();
        saltos.hash.trigger();
        expect(window.dispatchEvent).toHaveBeenCalledWith(expect.any(HashChangeEvent));
    });
});
