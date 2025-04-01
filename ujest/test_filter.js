
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
 * Filter unit tests
 *
 * This file contains unit tests for the filter management system
 * including initialization, loading, saving, and UI interactions
 */

/**
 * Load all needed files of the project
 */
const files = `app,backup,core,driver,filter,form,gettext,hash,storage,token`.split(',');
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
 * Test suite for filter initialization
 *
 * Contains tests for initializing the filter cache from server data
 */
describe('saltos.filter.init()', () => {
    /**
     * Setup before each test in this suite
     *
     * Mocks window location and sets up AJAX response for filter data
     */
    beforeEach(() => {
        window.location.hash = '#app/testapp/page';

        saltos.app.ajax = jest.fn((options) => {
            if (options.success) {
                options.success({
                    'app/testapp/filter/filter1': '{"field1":"value1"}',
                    'app/testapp/filter/filter2': '{"field2":"value2"}'
                });
            }
            return Promise.resolve();
        });
    });

    /**
     * Test filter cache initialization
     *
     * Verifies that the init function makes an AJAX call to load filters
     * and properly populates the cache
     */
    test('should initialize filter cache', async () => {
        await saltos.filter.init();
        expect(saltos.app.ajax).toHaveBeenCalledWith({
            url: 'app/testapp/list/filter',
            success: expect.any(Function)
        });
        expect(saltos.filter.__cache).toEqual({
            filter1: {field1: 'value1'},
            filter2: {field2: 'value2'}
        });
    });

    /**
     * Test cache reuse
     *
     * Verifies that subsequent init calls don't make AJAX requests
     * when cache is already populated
     */
    test('should not make ajax call if cache is already populated', async () => {
        await saltos.filter.init();
        expect(saltos.app.ajax).not.toHaveBeenCalled();
    });
});

/**
 * Test suite for filter loading
 *
 * Contains tests for loading filter data into forms and triggering searches
 */
describe('saltos.filter.load()', () => {
    /**
     * Setup before each test in this suite
     *
     * Mocks form data and driver search functions
     */
    beforeEach(() => {
        jest.spyOn(saltos.form, 'data').mockImplementation(jest.fn());
        jest.spyOn(saltos.driver, 'search').mockImplementation(jest.fn());
    });

    /**
     * Test loading existing filter
     *
     * Verifies that loading a filter populates form data
     * and triggers a search
     */
    test('should load filter data and trigger search', () => {
        saltos.filter.load('filter1');
        expect(saltos.form.data).toHaveBeenCalledWith({field1: 'value1'}, false);
        expect(saltos.driver.search).toHaveBeenCalled();
    });

    /**
     * Test loading non-existent filter
     *
     * Verifies that loading an unknown filter only triggers search
     * without modifying form data
     */
    test('should only trigger search if filter not found', () => {
        saltos.filter.load('nonExistentFilter');
        expect(saltos.form.data).not.toHaveBeenCalled();
        expect(saltos.driver.search).toHaveBeenCalled();
    });
});

/**
 * Test suite for filter updates
 *
 * Contains tests for updating the local filter cache
 */
describe('saltos.filter.update()', () => {
    /**
     * Test updating filter cache
     *
     * Verifies that update modifies the filter cache correctly
     */
    test('should update filter cache', () => {
        saltos.filter.update('filter1', {field1: 'value2'});
        expect(saltos.filter.__cache.filter1).toEqual({field1: 'value2'});
    });
});

/**
 * Test suite for filter saving
 *
 * Contains tests for saving filters to both cache and server
 */
describe('saltos.filter.save()', () => {
    /**
     * Test saving new filter
     *
     * Verifies that new filters are added to cache and saved to server
     */
    test('should save new filter to cache and server', () => {
        saltos.filter.save('filter3', {field: 'value3'});
        expect(saltos.filter.__cache.filter3).toEqual({field: 'value3'});
        expect(saltos.app.ajax).toHaveBeenCalledWith({
            url: 'app/testapp/list/filter',
            data: {
                name: 'filter3',
                val: '{"field":"value3"}'
            }
        });
    });

    /**
     * Test updating existing filter
     *
     * Verifies that modified filters are updated in cache and server
     */
    test('should update existing filter if data changed', () => {
        saltos.filter.save('filter3', {field: 'newValue'});
        expect(saltos.filter.__cache.filter3).toEqual({field: 'newValue'});
        expect(saltos.app.ajax).toHaveBeenCalled();
    });

    /**
     * Test unchanged filter
     *
     * Verifies that unchanged filters don't trigger server updates
     */
    test('should not make server call if data unchanged', () => {
        saltos.filter.save('filter3', {field: 'newValue'});
        expect(saltos.app.ajax).not.toHaveBeenCalled();
    });

    /**
     * Test filter deletion
     *
     * Verifies that filters are removed from cache and server when set to null
     */
    test('should delete filter when data is null', () => {
        saltos.filter.save('filter3', null);
        expect(saltos.filter.__cache).not.toHaveProperty('filter3');
        expect(saltos.app.ajax).toHaveBeenCalledWith({
            url: 'app/testapp/list/filter',
            data: {
                name: 'filter3',
                val: null
            }
        });
    });

    /**
     * Test deleting non-existent filter
     *
     * Verifies that attempting to delete unknown filters doesn't trigger server calls
     */
    test('should not delete non-existent filter', () => {
        saltos.filter.save('filter3', null);
        expect(saltos.app.ajax).not.toHaveBeenCalled();
    });
});

/**
 * Test suite for filter UI buttons
 *
 * Contains tests for all filter-related UI actions
 */
describe('saltos.filter.button()', () => {
    /**
     * Setup before each test in this suite
     *
     * Sets up DOM elements and mocks filter functions
     */
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="filter_form">
                <select><option value="savedFilter" selected></option></select>
                <input type="text" value="newFilter">
            </div>
            <div id="jstree"></div>
        `;

        jest.spyOn(saltos.filter, 'load').mockImplementation(jest.fn());
        jest.spyOn(saltos.filter, 'save').mockImplementation(jest.fn());
        jest.spyOn(saltos.filter, 'select').mockImplementation(jest.fn());
    });

    /**
     * Test load action
     *
     * Verifies that load button loads selected filter and resets selection
     */
    test('should handle load action', () => {
        saltos.filter.button('load');
        expect(saltos.filter.load).toHaveBeenCalledWith('savedFilter');
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
    });

    /**
     * Test update action
     *
     * Verifies that update button saves current form data to selected filter
     */
    test('should handle update action', () => {
        saltos.filter.button('update');
        expect(saltos.filter.save).toHaveBeenCalledWith('savedFilter', saltos.app.get_data(true));
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
    });

    /**
     * Test delete action
     *
     * Verifies that delete button removes selected filter and refreshes UI
     */
    test('should handle delete action', () => {
        saltos.filter.button('delete');
        expect(saltos.filter.save).toHaveBeenCalledWith('savedFilter', null);
        expect(saltos.filter.select).toHaveBeenCalled();
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
    });

    /**
     * Test create action
     *
     * Verifies that create button saves current form data as new filter
     */
    test('should handle create action', () => {
        saltos.filter.button('create');
        expect(saltos.filter.save).toHaveBeenCalledWith('newFilter', saltos.app.get_data(true));
        expect(saltos.filter.select).toHaveBeenCalled();
        expect(document.getElementById('filter_form').querySelector('input').value).toBe('');
    });

    /**
     * Test rename action
     *
     * Verifies that rename button moves filter data to new name
     * and removes old filter entry
     */
    test('should handle rename action', () => {
        saltos.filter.button('rename');
        expect(saltos.filter.save).toHaveBeenCalledWith('newFilter', saltos.app.get_data(true));
        expect(saltos.filter.save).toHaveBeenCalledWith('savedFilter', null);
        expect(saltos.filter.select).toHaveBeenCalled();
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
        expect(document.getElementById('filter_form').querySelector('input').value).toBe('');
    });

    /**
     * Test invalid action
     *
     * Verifies that unknown actions don't modify filter cache
     */
    test('should do nothing for invalid actions', () => {
        const originalCache = {...saltos.filter.__cache};
        saltos.filter.button('invalid');
        expect(saltos.filter.__cache).toEqual(originalCache);
    });
});

/**
 * Test suite for filter selection UI
 *
 * Contains tests for updating filter dropdown and tree view
 */
describe('saltos.filter.select()', () => {
    /**
     * Setup before each test in this suite
     *
     * Populates filter cache with test data
     */
    beforeEach(() => {
        saltos.filter.__cache = {
            filter1: {field1: 'value1'},
            filter2: {field2: 'value2'},
            last: {field: 'value'} // Should be skipped
        };
    });

    /**
     * Test UI update
     *
     * Verifies that select updates dropdown options and tree view
     */
    test('should update select options and jstree', () => {
        const jstreeMock = {
            set: jest.fn()
        };
        document.getElementById('jstree').set = jstreeMock.set;

        saltos.filter.select();

        const select = document.getElementById('filter_form').querySelector('select');
        expect(select.children.length).toBe(3); // Empty option + 2 filters
        expect(jstreeMock.set).toHaveBeenCalledWith([
            {text: 'filter1'},
            {text: 'filter2'}
        ]);
    });

    /**
     * Test missing tree element
     *
     * Verifies graceful handling when tree element is missing
     */
    test('should handle missing form elements gracefully', () => {
        document.getElementById('jstree').remove();
        expect(() => saltos.filter.select()).not.toThrow();
    });

    /**
     * Test missing select element
     *
     * Verifies graceful handling when select element is missing
     */
    test('should handle missing select elements gracefully', () => {
        document.querySelector('select').remove();
        expect(() => saltos.filter.select()).not.toThrow();
    });

    /**
     * Test missing form elements
     *
     * Verifies graceful handling when all form elements are missing
     */
    test('should handle missing form elements gracefully', () => {
        document.body.innerHTML = ''; // Remove all elements
        expect(() => saltos.filter.select()).not.toThrow();
    });
});
