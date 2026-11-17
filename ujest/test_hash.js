
/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Hash unit tests
 *
 * This file contains unit tests for hash management functionality
 * including hash parsing, manipulation, and event triggering
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
 * Reset mocks before each test
 *
 * Initializes mock implementations for history API functions
 * and resets all mocks between test cases
 */
beforeEach(() => {
    jest.resetAllMocks();
    jest.spyOn(window.history, 'replaceState').mockImplementation(jest.fn());
    jest.spyOn(window.history, 'pushState').mockImplementation(jest.fn());
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
 * Test suite for hash helper function
 *
 * Contains tests for the internal hash normalization function
 * that cleans hash strings by removing special characters
 */
describe('saltos.hash.__helper', () => {
    /**
     * Test hash with leading #
     *
     * Verifies the helper removes the # character from the beginning
     */
    test('should remove leading #', () => {
        expect(saltos.hash.__helper('#test')).toBe('test');
    });

    /**
     * Test hash with leading /
     *
     * Verifies the helper removes the / character from the beginning
     */
    test('should remove leading /', () => {
        expect(saltos.hash.__helper('/test')).toBe('test');
    });

    /**
     * Test hash with leading #/
     *
     * Verifies the helper removes both # and / characters from the beginning
     */
    test('should remove both leading # and /', () => {
        expect(saltos.hash.__helper('#/test')).toBe('test');
    });

    /**
     * Test empty hash
     *
     * Verifies the helper returns empty string for empty input
     */
    test('should return empty string for empty input', () => {
        expect(saltos.hash.__helper('')).toBe('');
    });

    /**
     * Test clean hash
     *
     * Verifies the helper returns unchanged input when no special characters
     */
    test('should not modify clean hash', () => {
        expect(saltos.hash.__helper('test')).toBe('test');
    });
});

/**
 * Test suite for hash getter function
 *
 * Contains tests for retrieving the current window hash
 * with proper formatting
 */
describe('saltos.hash.get', () => {
    /**
     * Test getting current hash
     *
     * Verifies the function returns hash without # prefix
     */
    test('should return current hash without #', () => {
        window.location.hash = '#test';
        expect(saltos.hash.get()).toBe('test');
    });

    /**
     * Test getting empty hash
     *
     * Verifies the function returns empty string when no hash exists
     */
    test('should return empty string when no hash', () => {
        window.location.hash = '';
        expect(saltos.hash.get()).toBe('');
    });

    /**
     * Test getting hash with leading /
     *
     * Verifies the function removes leading / from the hash
     */
    test('should remove leading /', () => {
        window.location.hash = '#/test';
        expect(saltos.hash.get()).toBe('test');
    });
});

/**
 * Test suite for hash setter function
 *
 * Contains tests for replacing the current hash
 * using history.replaceState
 */
describe('saltos.hash.set', () => {
    /**
     * Test setting new hash
     *
     * Verifies the function updates hash with proper format
     * and uses replaceState
     */
    test('should set new hash with proper format', () => {
        window.location.hash = '#old';
        const result = saltos.hash.set('new');
        expect(result).toBe(true);
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/new');
    });

    /**
     * Test setting same hash
     *
     * Verifies the function doesn't update history when hash doesn't change
     */
    test('should not set same hash', () => {
        window.location.hash = '#same';
        const result = saltos.hash.set('same');
        expect(result).toBe(false);
        expect(window.history.replaceState).not.toHaveBeenCalled();
    });

    /**
     * Test setting hash with #
     *
     * Verifies the function properly handles input containing #
     */
    test('should handle hash with #', () => {
        saltos.hash.set('#test');
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * Test setting hash with /
     *
     * Verifies the function properly handles input containing /
     */
    test('should handle hash with /', () => {
        saltos.hash.set('/test');
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * Test setting empty hash
     *
     * Verifies the function properly handles empty hash input
     */
    test('should handle empty hash', () => {
        saltos.hash.set('');
        expect(window.history.replaceState).toHaveBeenCalledWith(null, null, '.#/');
    });
});

/**
 * Test suite for hash adder function
 *
 * Contains tests for adding new hash entries
 * using history.pushState
 */
describe('saltos.hash.add', () => {
    /**
     * Test adding new hash
     *
     * Verifies the function updates hash with proper format
     * and uses pushState
     */
    test('should add new hash with proper format', () => {
        window.location.hash = '#old';
        const result = saltos.hash.add('new');
        expect(result).toBe(true);
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/new');
    });

    /**
     * Test adding same hash
     *
     * Verifies the function doesn't update history when hash doesn't change
     */
    test('should not add same hash', () => {
        window.location.hash = '#same';
        const result = saltos.hash.add('same');
        expect(result).toBe(false);
        expect(window.history.pushState).not.toHaveBeenCalled();
    });

    /**
     * Test adding hash with #
     *
     * Verifies the function properly handles input containing #
     */
    test('should handle hash with #', () => {
        saltos.hash.add('#test');
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * Test adding hash with /
     *
     * Verifies the function properly handles input containing /
     */
    test('should handle hash with /', () => {
        saltos.hash.add('/test');
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/test');
    });

    /**
     * Test adding empty hash
     *
     * Verifies the function properly handles empty hash input
     */
    test('should handle empty hash', () => {
        saltos.hash.add('');
        expect(window.history.pushState).toHaveBeenCalledWith(null, null, '.#/');
    });
});

/**
 * Test suite for URL hash extraction
 *
 * Contains tests for extracting hash fragments from URLs
 */
describe('saltos.hash.url2hash', () => {
    /**
     * Test extracting hash from URL
     *
     * Verifies the function extracts hash fragment correctly
     */
    test('should extract hash from URL', () => {
        const url = 'http://example.com/#test';
        expect(saltos.hash.url2hash(url)).toBe('test');
    });

    /**
     * Test extracting hash with /
     *
     * Verifies the function handles hashes containing /
     */
    test('should handle URL with / after #', () => {
        const url = 'http://example.com/#/test';
        expect(saltos.hash.url2hash(url)).toBe('test');
    });

    /**
     * Test URL without hash
     *
     * Verifies the function returns empty string when no hash exists
     */
    test('should return empty string when no hash', () => {
        const url = 'http://example.com/';
        expect(saltos.hash.url2hash(url)).toBe('');
    });
});

/**
 * Test suite for public decode function
 *
 * Contains tests for decoding the hashes that can be stored in the url
 */
describe('saltos.hash.decode.public', () => {
    /**
     * Test list hash
     *
     * Verifies the function returns the app with the default action and with the list action
     */
    test('should decode list hashes', () => {
        expect(saltos.hash.decode.public('app/customers')).toEqual({
            app: 'customers', action: '', subaction: '', id: '', sub_id: '',
        });
        expect(saltos.hash.decode.public('app/customers/list')).toEqual({
            app: 'customers', action: 'list', subaction: '', id: '', sub_id: '',
        });
    });

    /**
     * Test empty parts
     *
     * Verifies the function removes the empty parts like the backend
     */
    test('should ignore empty parts', () => {
        expect(saltos.hash.decode.public('app/customers/list/').action).toBe('list');
        expect(saltos.hash.decode.public('app//customers/view//5/').id).toBe('5');
        expect(saltos.hash.decode.private('app/customers/delete/5/').id).toBe('5');
    });

    /**
     * Test create hashes
     *
     * Verifies the function decodes the create action with and without subaction
     */
    test('should decode create hashes', () => {
        expect(saltos.hash.decode.public('app/customers/create')).toEqual({
            app: 'customers', action: 'create', subaction: '', id: '', sub_id: '',
        });
        expect(saltos.hash.decode.public('app/emails/create/reply/5')).toEqual({
            app: 'emails', action: 'create', subaction: 'reply', id: '5', sub_id: '',
        });
        expect(saltos.hash.decode.public('app/emails/create/replyall/5').subaction).toBe('replyall');
        expect(saltos.hash.decode.public('app/emails/create/forward/5').subaction).toBe('forward');
    });

    /**
     * Test edit hash
     *
     * Verifies the function returns the id of the edit action
     */
    test('should decode edit hash', () => {
        expect(saltos.hash.decode.public('app/customers/edit/5')).toEqual({
            app: 'customers', action: 'edit', subaction: '', id: '5', sub_id: '',
        });
    });

    /**
     * Test view hashes
     *
     * Verifies the function decodes the view action with and without subaction
     */
    test('should decode view hashes', () => {
        expect(saltos.hash.decode.public('app/customers/view/5')).toEqual({
            app: 'customers', action: 'view', subaction: '', id: '5', sub_id: '',
        });
        expect(saltos.hash.decode.public('app/customers/view/viewpdf/1,2,3')).toEqual({
            app: 'customers', action: 'view', subaction: 'viewpdf', id: '1,2,3', sub_id: '',
        });
        expect(saltos.hash.decode.public('app/customers/view/viewpdf/5/7')).toEqual({
            app: 'customers', action: 'view', subaction: 'viewpdf', id: '5', sub_id: '7',
        });
        expect(saltos.hash.decode.public('app/customers/view/version/5').subaction).toBe('version');
        expect(saltos.hash.decode.public('app/customers/view/log/5').subaction).toBe('log');
        expect(saltos.hash.decode.public('app/emails/view/source/5').subaction).toBe('source');
    });

    /**
     * Test unknown hashes
     *
     * Verifies the function throws an exception for the hashes that are not allowed
     */
    test('should throw for unknown hashes', () => {
        const hashes = [
            '',
            'app',
            'test/customers',
            'app/customers/page',
            'app/customers/list/5',
            'app/customers/create/5',
            'app/customers/edit',
            'app/customers/edit/5/7',
            'app/customers/view',
            'app/customers/view/version/5/7',
            'app/customers/view/unknown/5',
            'app/customers/view/viewpdf/5/7/9',
            'app/customers/delete/5',
        ];
        for (const hash of hashes) {
            expect(() => saltos.hash.decode.public(hash)).toThrow(`Unknown hash ${hash}`);
        }
    });
});

/**
 * Test suite for private decode function
 *
 * Contains tests for decoding the internal actions that never are stored in the url
 */
describe('saltos.hash.decode.private', () => {
    /**
     * Test delete actions
     *
     * Verifies the function decodes the delete action with and without subaction
     */
    test('should decode delete actions', () => {
        expect(saltos.hash.decode.private('app/customers/delete/5')).toEqual({
            app: 'customers', action: 'delete', subaction: '', id: '5', sub_id: '',
        });
        expect(saltos.hash.decode.private('app/customers/delete/files/5/7')).toEqual({
            app: 'customers', action: 'delete', subaction: 'files', id: '5', sub_id: '7',
        });
        expect(saltos.hash.decode.private('app/customers/delete/notes/5/7')).toEqual({
            app: 'customers', action: 'delete', subaction: 'notes', id: '5', sub_id: '7',
        });
    });

    /**
     * Test other actions
     *
     * Verifies the function decodes the list, insert, update, view, action and help actions
     */
    test('should decode other actions', () => {
        expect(saltos.hash.decode.private('app/customers/list/data')).toEqual({
            app: 'customers', action: 'list', subaction: 'data', id: '', sub_id: '',
        });
        expect(saltos.hash.decode.private('app/customers/insert')).toEqual({
            app: 'customers', action: 'insert', subaction: '', id: '', sub_id: '',
        });
        expect(saltos.hash.decode.private('app/customers/update/5')).toEqual({
            app: 'customers', action: 'update', subaction: '', id: '5', sub_id: '',
        });
        expect(saltos.hash.decode.private('app/customers/view/download/5/7')).toEqual({
            app: 'customers', action: 'view', subaction: 'download', id: '5', sub_id: '7',
        });
        expect(saltos.hash.decode.private('app/emails/action/move')).toEqual({
            app: 'emails', action: 'action', subaction: 'move', id: '', sub_id: '',
        });
        expect(saltos.hash.decode.private('app/widgets/help/customers')).toEqual({
            app: 'widgets', action: 'help', subaction: '', id: 'customers', sub_id: '',
        });
    });

    /**
     * Test unknown actions
     *
     * Verifies the function throws an exception for the actions that are not allowed
     */
    test('should throw for unknown actions', () => {
        const urls = [
            '',
            'app',
            'test/customers/delete/5',
            'app/customers',
            'app/customers/delete',
            'app/customers/delete/files/5',
            'app/customers/delete/unknown/5/7',
            'app/customers/view/5',
            'app/customers/list',
            'app/customers/list/unknown',
            'app/customers/insert/5',
            'app/customers/update',
            'app/customers/view/download',
            'app/customers/view/body/5/7',
            'app/customers/create',
            'app/customers/action',
            'app/customers/help/customers',
            'app/emails/sendmail/5',
            'app/emails/sendmail/unknown/5',
        ];
        for (const url of urls) {
            expect(() => saltos.hash.decode.private(url)).toThrow(`Unknown action ${url}`);
        }
    });
});

/**
 * Test suite for public encode function
 *
 * Contains tests for encoding the hashes that can be stored in the url
 */
describe('saltos.hash.encode.public', () => {
    /**
     * Test omitted parts
     *
     * Verifies the function skips the omitted and empty parts
     */
    test('should skip omitted parts', () => {
        expect(saltos.hash.encode.public({app: 'customers'})).toBe('app/customers');
        expect(saltos.hash.encode.public({app: 'customers', action: 'list'})).toBe('app/customers/list');
        expect(saltos.hash.encode.public({app: 'customers', action: 'edit', subaction: '', id: 5}))
            .toBe('app/customers/edit/5');
    });

    /**
     * Test round trip
     *
     * Verifies the function is the inverse of the public decode
     */
    test('should be the inverse of decode', () => {
        const hashes = [
            'app/customers',
            'app/customers/list',
            'app/customers/create',
            'app/emails/create/reply/5',
            'app/customers/edit/5',
            'app/customers/view/5',
            'app/customers/view/viewpdf/1,2,3',
            'app/customers/view/viewpdf/5/7',
            'app/customers/view/version/5',
        ];
        for (const hash of hashes) {
            expect(saltos.hash.encode.public(saltos.hash.decode.public(hash))).toBe(hash);
        }
    });

    /**
     * Test unknown routes
     *
     * Verifies the function throws an exception for the routes that are not allowed
     */
    test('should throw for unknown routes', () => {
        expect(() => saltos.hash.encode.public({app: 'customers', action: 'edit'}))
            .toThrow('Unknown hash app/customers/edit');
        expect(() => saltos.hash.encode.public({app: 'customers', action: 'delete', id: 5}))
            .toThrow('Unknown hash app/customers/delete/5');
    });
});

/**
 * Test suite for private encode function
 *
 * Contains tests for encoding the internal actions that never are stored in the url
 */
describe('saltos.hash.encode.private', () => {
    /**
     * Test round trip
     *
     * Verifies the function is the inverse of the private decode
     */
    test('should be the inverse of decode', () => {
        const urls = [
            'app/customers/delete/5',
            'app/customers/delete/files/5/7',
            'app/customers/delete/notes/5/7',
            'app/customers/list/data',
            'app/customers/list/filter',
            'app/customers/insert',
            'app/customers/update/5',
            'app/customers/view/download/1,2,3',
            'app/customers/view/download/5/7',
            'app/emails/view/body/5',
            'app/emails/view/setter/5',
            'app/emails/sendmail',
            'app/emails/sendmail/reply/5',
            'app/emails/sendmail/replyall/5',
            'app/emails/sendmail/forward/5',
            'app/emails/action/move',
            'app/widgets/help/customers',
        ];
        for (const url of urls) {
            expect(saltos.hash.encode.private(saltos.hash.decode.private(url))).toBe(url);
        }
    });

    /**
     * Test unknown routes
     *
     * Verifies the function throws an exception for the routes that are not allowed
     */
    test('should throw for unknown routes', () => {
        expect(() => saltos.hash.encode.private({app: 'customers', action: 'view', id: 5}))
            .toThrow('Unknown action app/customers/view/5');
    });
});

/**
 * Test suite for hash change events
 *
 * Contains tests for triggering hashchange events
 */
describe('saltos.hash.trigger', () => {
    /**
     * Test triggering hash change
     *
     * Verifies the function dispatches a hashchange event
     */
    test('should dispatch hashchange event', () => {
        window.dispatchEvent = jest.fn();
        saltos.hash.trigger();
        expect(window.dispatchEvent).toHaveBeenCalledWith(expect.any(HashChangeEvent));
    });
});
