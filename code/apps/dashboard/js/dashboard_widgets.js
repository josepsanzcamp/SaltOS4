
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
 * This application implements the tipical features associated to dashboard
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.dashboard_widgets = {};

/**
 * TODO
 *
 * TODO
 */
saltos.dashboard_widgets.init = arg => {
    setTimeout(() => {
        document.querySelectorAll('.fs-1').forEach(item => {
            item.classList.replace('fs-1', 'fs-2');
        });
        document.querySelectorAll('table').forEach(item => {
            item.classList.add('table-sm');
            item.classList.add('small');
        });
    }, 1);

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
                url: `app/dashboard_widgets/config`,
                data: {
                    'name': 'default',
                    'val': JSON.stringify(ids),
                },
            });
        },
    });

    let sleep = 100;
    saltos.app.ajax({
        url: `app/dashboard_widgets/config`,
        success: response => {
            const key = 'app/dashboard/widgets/default';
            let ids = [];
            if (key in response) {
                ids = JSON.parse(response[key]);
            }

            for (const i in ids) {
                let obj = document.getElementById(ids[i]);
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
