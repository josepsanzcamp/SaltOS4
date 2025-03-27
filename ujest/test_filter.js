
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
const files = `app,backup,core,driver,filter,form,gettext,hash,storage,token`.split(',');
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
describe('saltos.filter.init()', () => {
    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should not make ajax call if cache is already populated', async () => {
        await saltos.filter.init();
        expect(saltos.app.ajax).not.toHaveBeenCalled();
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.filter.load()', () => {
    beforeEach(() => {
        jest.spyOn(saltos.form, 'data').mockImplementation(jest.fn());
        jest.spyOn(saltos.driver, 'search').mockImplementation(jest.fn());
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should load filter data and trigger search', () => {
        saltos.filter.load('filter1');
        expect(saltos.form.data).toHaveBeenCalledWith({field1: 'value1'}, false);
        expect(saltos.driver.search).toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should only trigger search if filter not found', () => {
        saltos.filter.load('nonExistentFilter');
        expect(saltos.form.data).not.toHaveBeenCalled();
        expect(saltos.driver.search).toHaveBeenCalled();
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.filter.update()', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('should update filter cache', () => {
        saltos.filter.update('filter1', {field1: 'value2'});
        expect(saltos.filter.__cache.filter1).toEqual({field1: 'value2'});
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.filter.save()', () => {
    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should update existing filter if data changed', () => {
        saltos.filter.save('filter3', {field: 'newValue'});
        expect(saltos.filter.__cache.filter3).toEqual({field: 'newValue'});
        expect(saltos.app.ajax).toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should not make server call if data unchanged', () => {
        saltos.filter.save('filter3', {field: 'newValue'});
        expect(saltos.app.ajax).not.toHaveBeenCalled();
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should not delete non-existent filter', () => {
        saltos.filter.save('filter3', null);
        expect(saltos.app.ajax).not.toHaveBeenCalled();
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.filter.button()', () => {
    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should handle load action', () => {
        saltos.filter.button('load');
        expect(saltos.filter.load).toHaveBeenCalledWith('savedFilter');
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle update action', () => {
        saltos.filter.button('update');
        expect(saltos.filter.save).toHaveBeenCalledWith('savedFilter', saltos.app.get_data(true));
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle delete action', () => {
        saltos.filter.button('delete');
        expect(saltos.filter.save).toHaveBeenCalledWith('savedFilter', null);
        expect(saltos.filter.select).toHaveBeenCalled();
        expect(document.getElementById('filter_form').querySelector('select').value).toBe('');
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle create action', () => {
        saltos.filter.button('create');
        expect(saltos.filter.save).toHaveBeenCalledWith('newFilter', saltos.app.get_data(true));
        expect(saltos.filter.select).toHaveBeenCalled();
        expect(document.getElementById('filter_form').querySelector('input').value).toBe('');
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should do nothing for invalid actions', () => {
        const originalCache = {...saltos.filter.__cache};
        saltos.filter.button('invalid');
        expect(saltos.filter.__cache).toEqual(originalCache);
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('saltos.filter.select()', () => {
    /**
     * TODO
     *
     * TODO
     */
    beforeEach(() => {
        saltos.filter.__cache = {
            filter1: {field1: 'value1'},
            filter2: {field2: 'value2'},
            last: {field: 'value'} // Should be skipped
        };
    });

    /**
     * TODO
     *
     * TODO
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
     * TODO
     *
     * TODO
     */
    test('should handle missing form elements gracefully', () => {
        document.getElementById('jstree').remove();
        expect(() => saltos.filter.select()).not.toThrow();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle missing select elements gracefully', () => {
        document.querySelector('select').remove();
        expect(() => saltos.filter.select()).not.toThrow();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('should handle missing form elements gracefully', () => {
        document.body.innerHTML = ''; // Remove all elements
        expect(() => saltos.filter.select()).not.toThrow();
    });
});
