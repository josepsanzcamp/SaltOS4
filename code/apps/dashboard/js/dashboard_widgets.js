
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
 * Dashboard widgets application
 *
 * This module implements the typical features associated with a widget dashboard,
 * allowing interaction and customization of elements in a dynamic environment.
 */

/**
 * Main object
 *
 * Contains all the logic and code for the SaltOS framework related to the dashboard widgets.
 */
saltos.dashboard_widgets = {};

/**
 * Initialization of dashboard widgets
 *
 * This method sets up the interactive elements of the dashboard, including assigning style classes
 * and configuring drag-and-drop functionality between different containers.
 */
saltos.dashboard_widgets.init = arg => {
    setTimeout(() => {
        // Modify style classes to adjust font sizes
        for (let i = 4; i > 0; i--) {
            const j = i + 2;
            document.querySelectorAll(`.fs-${i}`).forEach(item => {
                item.classList.replace(`fs-${i}`, `fs-${j}`);
            });
        }
        document.querySelectorAll('table').forEach(item => {
            item.classList.add('table-sm');
            item.classList.add('small');
        });
    }, 1);

    // Configure drag-and-drop functionality for the widget catalog
    new Sortable(document.getElementById('catalog'), {
        group: {
            name: 'widgets',
            pull: (to, from, dragEl) => {
                return dragEl.classList.contains('clonable') ? 'clone' : true;
            },
            put: true,
        },
        onAdd: event => {
            let item = event.item;
            if (item.classList.contains('clonable')) {
                item.remove();
            }
        }
    });

    // Configure drag-and-drop functionality for the widget dashboard
    new Sortable(document.getElementById('dashboard'), {
        group: 'widgets',
        onSort: () => {
            const ids = [];
            document.querySelectorAll('#dashboard [id]').forEach(item => {
                if (item.id.startsWith('id')) {
                    return;
                }
                if (item.id == '') {
                    return;
                }
                ids.push(item.id);
            });
            saltos.app.ajax({
                url: 'app/dashboard_widgets/config',
                data: {
                    'name': 'default',
                    'val': JSON.stringify(ids),
                },
                success: response => {
                    saltos.window.send('saltos.dashboard.update');
                },
            });
        },
    });

    let sleep = 100;
    saltos.app.ajax({
        url: 'app/dashboard_widgets/config',
        success: response => {
            const key = 'app/dashboard/widgets/default';
            let ids = [];
            if (key in response) {
                ids = JSON.parse(response[key]);
            }

            for (const i in ids) {
                let obj = document.getElementById(ids[i]);
                if (!obj) {
                    continue;
                }
                let parent = obj.parentElement;
                while (parent && !parent.classList.contains('row')) {
                    obj = parent;
                    parent = parent.parentElement;
                }

                setTimeout(() => {
                    if (obj.classList.contains('clonable')) {
                        document.getElementById('dashboard').appendChild(obj.cloneNode(true));
                    } else {
                        document.getElementById('dashboard').appendChild(obj);
                    }
                }, sleep);
                sleep += 50;
            }
        },
    });
};
