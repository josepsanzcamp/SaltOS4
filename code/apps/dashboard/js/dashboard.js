
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
 * Dashboard application
 *
 * This module implements the typical features associated with a dashboard,
 * allowing dynamic interaction with various elements and functionalities.
 */

/**
 * Main object
 *
 * Contains all the logic and code for the SaltOS framework related to the dashboard.
 */
saltos.dashboard = {};

/**
 * Initialization of dashboard
 *
 * This method sets up listeners, configures the catalog layout, and initializes
 * the dashboard widgets based on user-defined or default configurations.
 */
saltos.dashboard.init = arg => {
    // Sets a listener to update dashboard-related elements on event triggers
    saltos.window.set_listener('saltos.dashboard.update', event => {
        saltos.hash.trigger();
    });

    /*
    // Example listener for customer updates, updating associated widgets dynamically
    saltos.window.set_listener('saltos.customers.update', event => {
        saltos.app.ajax({
            url: 'app/customers/widget/table1',
            success: response => {
                response.id = 'table1';
                const temp = saltos.bootstrap.field(response);
                document.getElementById('table1').replaceWith(temp);
            },
        });

        saltos.app.ajax({
            url: 'app/customers/widget/table2',
            success: response => {
                response.id = 'table2';
                const temp = saltos.bootstrap.field(response);
                document.getElementById('table2').replaceWith(temp);
            },
        });

        saltos.favicon.run();
    });
    */

    // Configures catalog properties
    const catalog = arg.catalog;
    catalog.row['#attr'].row_class = 'row mt-3';
    catalog.row['#attr'].row_style = '';

    // Retrieves dashboard widget configuration and sets it up
    const config = arg.config;
    const key = 'app/dashboard/widgets/default';
    let ids = [];
    if (key in config) {
        ids = JSON.parse(config[key]);
    }

    // Filters catalog rows to match the specified IDs in the configuration
    const original = catalog.row.value;
    if (ids.length) {
        catalog.row.value = {};
    }

    for (const i in ids) {
        for (const j in original) {
            if (original[j]['#attr'].id == ids[i]) {
                if (j in catalog.row.value) {
                    catalog.row.value[j + '#' + saltos.core.uniqid()] = original[j];
                } else {
                    catalog.row.value[j] = original[j];
                }
                break;
            }
        }
    }

    // Constructs the dashboard layout using the filtered catalog values
    saltos.form.layout({
        '#attr': {
            append: 'one',
        },
        value: catalog,
    });
};
