
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
saltos.filter.init = app => {
    if (!Object.keys(saltos.filter.__cache).length) {
        saltos.app.form.screen('loading');
        saltos.core.ajax({
            url: `api/?app/${app}/list/filter`,
            method: 'get',
            async: false,
            success: response => {
                saltos.app.form.screen('unloading');
                if (!saltos.app.check_response(response)) {
                    return;
                }
                saltos.filter.__cache = {};
                for (var key in response) {
                    var val = JSON.parse(response[key]);
                    saltos.filter.__cache[key] = val;
                }
            },
            error: request => {
                saltos.app.form.screen('unloading');
                saltos.app.show_error({
                    text: request.statusText,
                    code: request.status,
                });
            },
            abort: request => {
                saltos.app.form.screen('unloading');
            },
            token: saltos.token.get(),
            lang: saltos.gettext.get(),
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
saltos.filter.load = (app, name) => {
    var key = `app/${app}/list/filter/${name}`;
    if (saltos.filter.__cache.hasOwnProperty(key)) {
        saltos.app.form.data(saltos.filter.__cache[key], false);
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
saltos.filter.update = (app, name, data) => {
    var key = `app/${app}/list/filter/${name}`;
    saltos.filter.__cache[key] = data;
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
saltos.filter.save = (app, name, data) => {
    var key = `app/${app}/list/filter/${name}`;
    // Check for detect if the filter is saved or deleted
    if (data !== null) {
        if (saltos.filter.__cache.hasOwnProperty(key)) {
            if (JSON.stringify(saltos.filter.__cache[key]) == JSON.stringify(data)) {
                // In this case, the filter exists and contains the same data, nothing to do
                return;
            }
        }
        saltos.filter.__cache[key] = data;
        data = JSON.stringify(data);
    } else {
        if (!saltos.filter.__cache.hasOwnProperty(key)) {
            // In this case, the filter does not exists, nothing to do
            return;
        }
        delete saltos.filter.__cache[key];
    }
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: `api/?app/${app}/list/filter`,
        data: JSON.stringify({
            'name': name,
            'val': data,
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        abort: request => {
            saltos.app.form.screen('unloading');
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};
