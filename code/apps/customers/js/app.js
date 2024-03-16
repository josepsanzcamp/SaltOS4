
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
 * Customers application
 *
 * This application implements the tipical features associated to customers
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.customers = {};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.initialize_update_list = () => {
    saltos.window.set_listener('saltos.customers.update', event => {
        saltos.customers.search();
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.initialize_update_view = () => {
    saltos.window.set_listener('saltos.customers.update', event => {
        saltos.hash.trigger();
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.search = () => {
    document.getElementById('page').value = '0';
    saltos.app.form.screen('loading');
    var app = saltos.hash.get().split('/').at(1);
    saltos.core.ajax({
        url: `api/index.php?list/${app}/table`,
        data: JSON.stringify({
            'search': document.getElementById('search').value,
            'page': document.getElementById('page').value,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            response.id = 'table';
            var temp = saltos.bootstrap.field(response);
            document.getElementById('table').parentNode.replaceWith(temp);
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.reset = () => {
    document.getElementById('search').value = '';
    document.getElementById('page').value = '0';
    saltos.customers.search();
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.more = () => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value) + 1,
    saltos.app.form.screen('loading');
    var app = saltos.hash.get().split('/').at(1);
    saltos.core.ajax({
        url: `api/index.php?list/${app}/table`,
        data: JSON.stringify({
            'search': document.getElementById('search').value,
            'page': document.getElementById('page').value,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (!response.data.length) {
                saltos.app.toast('Response', 'There is no more data', {color: 'warning'});
                return;
            }
            var obj = document.getElementById('table').querySelector('tbody');
            var temp = saltos.bootstrap.field(response);
            temp.querySelectorAll('table tbody tr').forEach(_this => obj.append(_this));
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.insert = () => {
    if (!saltos.app.check_required()) {
        saltos.app.modal('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    var data = saltos.app.get_data();
    var app = saltos.hash.get().split('/').at(1);
    saltos.core.ajax({
        url: `api/index.php?insert/${app}`,
        data: JSON.stringify({
            'data': data,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.window.send('saltos.customers.update');
                saltos.window.close();
                return;
            }
            saltos.app.show_error(response);
        },
        error: request => {
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.update = () => {
    if (!saltos.app.check_required()) {
        saltos.app.modal('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    var data = saltos.app.get_data();
    if (!Object.keys(data).length) {
        saltos.app.modal('Warning', 'No changes detected', {color: 'danger'});
        return;
    }
    var app = saltos.hash.get().split('/').at(1);
    var id = saltos.hash.get().split('/').at(3);
    saltos.core.ajax({
        url: `api/index.php?update/${app}/${id}`,
        data: JSON.stringify({
            'data': data,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.window.send('saltos.customers.update');
                saltos.window.close();
                return;
            }
            saltos.app.show_error(response);
        },
        error: request => {
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.delete1 = arg => {
    saltos.app.modal('Delete this customer???', 'Do you want to delete this customer???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                saltos.app.form.screen('loading');
                var app = arg.split('/').at(1);
                var id = arg.split('/').at(3);
                saltos.core.ajax({
                    url: `api/index.php?delete/${app}/${id}`,
                    success: response => {
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        if (response.status == 'ok') {
                            saltos.window.send('saltos.customers.update');
                            return;
                        }
                        saltos.app.show_error(response);
                    },
                    error: request => {
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    token: saltos.token.get(),
                });
            },
        },{
            label: 'No',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
        color: 'danger',
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.customers.delete2 = () => {
    saltos.app.modal('Delete this customer???', 'Do you want to delete this customer???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                saltos.app.form.screen('loading');
                var app = saltos.hash.get().split('/').at(1);
                var id = saltos.hash.get().split('/').at(3);
                saltos.core.ajax({
                    url: `api/index.php?delete/${app}/${id}`,
                    success: response => {
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        if (response.status == 'ok') {
                            saltos.window.send('saltos.customers.update');
                            saltos.window.close();
                            return;
                        }
                        saltos.app.show_error(response);
                    },
                    error: request => {
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    token: saltos.token.get(),
                });
            },
        },{
            label: 'No',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
        color: 'danger',
    });
};
