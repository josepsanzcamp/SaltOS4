
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
    saltos.window.set_listener('saltos.emails.update', event => {
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
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/index.php',
        data: JSON.stringify({
            'action': 'list',
            'app': saltos.hash.get().split('/').at(1),
            'subapp': 'table',
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
saltos.emails.reset = () => {
    document.getElementById('search').value = '';
    document.getElementById('page').value = '0';
    saltos.emails.search();
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.more = () => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value) + 1,
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/index.php',
        data: JSON.stringify({
            'action': 'list',
            'app': saltos.hash.get().split('/').at(1),
            'subapp': 'table',
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
saltos.emails.getmail = () => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/index.php',
        data: JSON.stringify({
            'action': 'app',
            'app': saltos.hash.get().split('/').at(1),
            'subapp': 'action',
            'id': 'getmail',
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            for (var key in response.array) {
                saltos.app.toast('Response', response.array[key]);
            }
            saltos.window.send('saltos.emails.update');
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
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
saltos.emails.delete1 = () => {
    var ids = saltos.app.checkbox_ids(document.getElementById('table'));
    if (!ids.length) {
        saltos.app.alert(
            'Select emails',
            'You must select the desired emails to be deleted',
            {
                color: 'warning',
            },
        );
        return;
    }
    saltos.app.alert('Delete emails???', 'Do you want to delete the selected emails???', {
        buttons: [{
            label: 'Yes',
            class: 'btn-success',
            icon: 'check-lg',
            onclick: () => {
                saltos.app.form.screen('loading');
                saltos.core.ajax({
                    url: 'api/index.php',
                    data: JSON.stringify({
                        'action': 'app',
                        'app': saltos.hash.get().split('/').at(1),
                        'subapp': 'action',
                        'id': 'delete',
                        'ids': ids,
                    }),
                    method: 'post',
                    content_type: 'application/json',
                    success: response => {
                        saltos.app.form.screen('unloading');
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        saltos.app.toast('Response', response.text);
                        saltos.window.send('saltos.emails.update');
                    },
                    error: request => {
                        saltos.app.form.screen('unloading');
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    headers: {
                        'token': saltos.token.get(),
                    }
                });
            },
        },{
            label: 'No',
            class: 'btn-danger',
            icon: 'x-lg',
            autofocus: true,
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
saltos.emails.delete2 = () => {
    saltos.app.alert('Delete this email???', 'Do you want to delete this email???', {
        buttons: [{
            label: 'Yes',
            class: 'btn-success',
            icon: 'check-lg',
            onclick: () => {
                saltos.app.form.screen('loading');
                saltos.core.ajax({
                    url: 'api/index.php',
                    data: JSON.stringify({
                        'action': 'app',
                        'app': saltos.hash.get().split('/').at(1),
                        'subapp': 'action',
                        'id': 'delete',
                        'ids': [saltos.hash.get().split('/').at(3)],
                    }),
                    method: 'post',
                    content_type: 'application/json',
                    success: response => {
                        saltos.app.form.screen('unloading');
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        saltos.window.send('saltos.emails.update');
                        saltos.window.close();
                    },
                    error: request => {
                        saltos.app.form.screen('unloading');
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    headers: {
                        'token': saltos.token.get(),
                    }
                });
            },
        },{
            label: 'No',
            class: 'btn-danger',
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
saltos.emails.send = () => {
    // TODO
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.download = (file) => {
    saltos.core.ajax({
        url: 'api/index.php?' + file,
        method: 'get',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            var a = document.createElement('a');
            a.download = response.file.name;
            response.file.type = 'application/force-download'; // to force download dialog
            a.href = `data:${response.file.type};base64,${response.file.data}`;
            a.click();
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
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
saltos.emails.setter = what => {
    saltos.core.ajax({
        url: 'api/index.php',
        data: JSON.stringify({
            'action': 'app',
            'app': saltos.hash.get().split('/').at(1),
            'subapp': 'action',
            'id': 'setter',
            'ids': [saltos.hash.get().split('/').at(3)],
            'what': what,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            saltos.window.send('saltos.emails.update');
            saltos.hash.trigger();
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get(),
        }
    });
};
