
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * Filter module
 *
 * Implements all filters features
 */

/**
 * Filter object
 *
 * This object stores the functions used by the filter module
 */
saltos.filter = {};

/**
 * Filter cache
 *
 * This object stores the cache used by the filter module
 */
saltos.filter.__cache = {};

/**
 * Init filter
 *
 * This function initialize the filter module by loading the current filters for
 * the current app. The main idea is to load all filters to be used by load and
 * save.
 *
 * @app => the name of the app used to load the filters
 */
saltos.filter.init = () => {
    if (!Object.keys(saltos.filter.__cache).length) {
        const app = saltos.hash.get().split('/').at(1);
        saltos.app.ajax({
            url: `app/${app}/list/filter`,
            async: false,
            success: response => {
                saltos.filter.__cache = {};
                const temp = `app/${app}/list/filter/`;
                const len = temp.length;
                for (let key in response) {
                    const val = JSON.parse(response[key]);
                    key = key.substr(len);
                    saltos.filter.__cache[key] = val;
                }
            },
        });
    }
};

/**
 * Load filter
 *
 * This function gets the filter indentified by the app and name and restore
 * all values of the search form widgets from the cache for afterwards execute
 * the saltos.driver.search, that load the data in the list
 *
 * @app  => the name of the desired app to be used
 * @name => the name of the desired filter
 */
saltos.filter.load = (name) => {
    if (saltos.filter.__cache.hasOwnProperty(name)) {
        saltos.app.form.data(saltos.filter.__cache[name], false);
    }
    saltos.driver.search();
};

/**
 * Update filter
 *
 * This function only updates the cache filter
 *
 * @app  => the name of the desired app to be used
 * @name => the name of the desired filter that wants to update
 * @data => the data of the desired filter that wants to update
 */
saltos.filter.update = (name, data) => {
    saltos.filter.__cache[name] = data;
};

/**
 * Update filter
 *
 * This function save the data in the cache and in the server api
 *
 * @app  => the name of the desired app to be used
 * @name => the name of the desired filter that wants to save
 * @data => the data of the desired filter that wants to save
 *
 * Notes:
 *
 * This function allow to save and delete entries, using data as object or as null
 * you can modify the behaviour, and the actions are performed in the cache and
 * in the server api.
 *
 * Too this function checks if data has suffered changes to optimize the network
 * and prevent non needed ajax requests.
 */
saltos.filter.save = (name, data) => {
    // Check for detect if the filter is saved or deleted
    if (data !== null) {
        if (saltos.filter.__cache.hasOwnProperty(name)) {
            if (JSON.stringify(saltos.filter.__cache[name]) == JSON.stringify(data)) {
                // In this case, the filter exists and contains the same data, nothing to do
                return;
            }
        }
        saltos.filter.__cache[name] = data;
        data = JSON.stringify(data);
    } else {
        if (!saltos.filter.__cache.hasOwnProperty(name)) {
            // In this case, the filter does not exists, nothing to do
            return;
        }
        delete saltos.filter.__cache[name];
    }
    const app = saltos.hash.get().split('/').at(1);
    saltos.app.ajax({
        url: `app/${app}/list/filter`,
        data: {
            'name': name,
            'val': data,
        },
        async: false,
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.filter.button = arg => {
    const form = document.getElementById('filter_form');
    const select = form.querySelector('select');
    const input = form.querySelector('input');
    saltos.app.form.__backup.restore('top+one');
    const data = saltos.app.get_data(true);
    switch (arg) {
        case 'load':
            if (!select.value) {
                break;
            }
            saltos.filter.load(select.value);
            select.value = '';
            break;
        case 'update':
            if (!select.value) {
                break;
            }
            saltos.filter.save(select.value, data);
            select.value = '';
            break;
        case 'delete':
            if (!select.value) {
                break;
            }
            saltos.filter.save(select.value, null);
            select.value = '';
            saltos.filter.select();
            break;
        case 'create':
            if (!input.value) {
                break;
            }
            saltos.filter.save(input.value, data);
            input.value = '';
            saltos.filter.select();
            break;
        case 'rename':
            if (!input.value) {
                break;
            }
            if (!select.value) {
                break;
            }
            saltos.filter.save(input.value, data);
            input.value = '';
            saltos.filter.save(select.value, null);
            select.value = '';
            saltos.filter.select();
            break;
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.filter.select = arg => {
    const form = document.getElementById('filter_form');
    if (!form) {
        return;
    }
    const select = form.querySelector('select');
    if (!select) {
        return;
    }
    select.replaceChildren(saltos.core.html(`<option value=""></option>`));
    for (const key in saltos.filter.__cache) {
        if (key == 'last') {
            continue;
        }
        const val = saltos.filter.__cache[key];
        select.append(saltos.core.html(`<option value="${key}">${key}</option>`));
    }
    const jstree = document.getElementById('jstree');
    if (!jstree) {
        return;
    }
    const data = [];
    for (const key in saltos.filter.__cache) {
        if (key == 'last') {
            continue;
        }
        const val = saltos.filter.__cache[key];
        data.push({
            text: key,
        });
    }
    jstree.set(data);
};
