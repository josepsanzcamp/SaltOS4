
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
 * Customers application
 *
 * This application implements the tipical features associated to customers
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.clientes = {};

/**
 * Initialize customers
 *
 * This function initializes the customers screen to improve the user experience.
 */
saltos.clientes.initialize = () => {
    document.getElementById('search').focus();
    document.getElementById('search').addEventListener('keydown', event => {
        if (saltos.get_keycode(event) != 13) {
            return;
        }
        saltos.clientes.search();
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.search = () => {
    document.getElementById('page').value = '0';
    saltos.loading(true);
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'list',
            'app': 'clientes',
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
            document.getElementById('table').replaceWith(saltos.form_field(response));
        },
        error: request => {
            saltos.loading();
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get_token(),
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.clear_filter = () => {
    document.getElementById('search').value = '';
    document.getElementById('page').value = '0';
    saltos.clientes.search();
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.read_more = () => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value) + 1,
    saltos.loading(true);
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'list',
            'app': 'clientes',
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
            var obj = document.getElementById('table').querySelector('tbody');
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
            'token': saltos.token.get_token(),
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.cancel = () => {
    saltos.close_window();
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.insert = () => {
    if (!saltos.check_required()) {
        return;
    }
    var data = saltos.get_data();
    if (!Object.keys(data).length) {
        saltos.alert('Warning', 'No changes detected');
        return;
    }
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'insert',
            'app': saltos.hash.get().split('/').at(1),
            'data': data,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.close_window();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get_token(),
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.update = () => {
    if (!saltos.check_required()) {
        return;
    }
    var data = saltos.get_data();
    if (!Object.keys(data).length) {
        saltos.alert('Warning', 'No changes detected');
        return;
    }
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'update',
            'app': saltos.hash.get().split('/').at(1),
            'id': saltos.hash.get().split('/').at(3),
            'data': data,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.close_window();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get_token(),
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.clientes.delete = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'delete',
            'app': saltos.hash.get().split('/').at(1),
            'id': saltos.hash.get().split('/').at(3),
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.close_window();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get_token(),
        }
    });
};
