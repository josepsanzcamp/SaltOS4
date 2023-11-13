
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * Email application
 *
 * This application implements the tipical features associated to emails
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.emails = {};

/**
 * Initialize emails app
 *
 * This function initializes the emails app screen to improve the user experience.
 */
saltos.emails.initialize = () => {
    document.getElementById('search').addEventListener('keydown', event => {
        if (saltos.get_keycode(event) != 13) {
            return;
        }
        saltos.emails.search();
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.search = () => {
    document.getElementById('page').value = '0';
    saltos.loading(true);
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'list',
            'app': 'emails',
            'subapp': 'table',
            'search': document.getElementById('search').value,
            'page': document.getElementById('page').value,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.loading();
            if (!saltos.check_response(response)) {
                return;
            }
            document.querySelector('table').replaceWith(saltos.form_field(response));
        },
        error: request => {
            saltos.loading();
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get(),
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.clear_filter = () => {
    document.getElementById('search').value = '';
    document.getElementById('page').value = '0';
    saltos.emails.search();
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.read_more = () => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value) + 1,
    saltos.loading(true);
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'list',
            'app': 'emails',
            'subapp': 'table',
            'search': document.getElementById('search').value,
            'page': document.getElementById('page').value,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.loading();
            if (!saltos.check_response(response)) {
                return;
            }
            var obj = document.querySelector('table').querySelector('tbody');
            var temp = saltos.form_field(response);
            temp.querySelectorAll('table tbody tr').forEach(_this => obj.append(_this));
        },
        error: request => {
            saltos.loading();
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get(),
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.send_and_get = () => {
    // TODO
    console.log('saltos.emails.send_and_get');
};
