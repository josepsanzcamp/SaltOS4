
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
 * Types application
 *
 * This application implements the tipical features associated to types
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.types = {};

/**
 * Initialize types
 *
 * This function initializes the types screen to improve the user experience.
 */
saltos.types.initialize = () => {
    var hash = saltos.hash.get();
    hash = hash.split('/').filter(x => x.length);
    if (hash.length >= 4) {
        hash.splice(2, 1);
        saltos.types.__open_helper('#' + hash.join('/'));
    }

    window.addEventListener('scroll', event => {
        if (document.getElementById('form').offsetLeft) {
            document.getElementById('form').style.marginTop = window.scrollY + 'px';
        } else {
            document.getElementById('form').style.marginTop = '0px';
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.types.search = () => {
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
saltos.types.reset = () => {
    document.getElementById('search').value = '';
    document.getElementById('page').value = '0';
    saltos.types.search();
};

/**
 * TODO
 *
 * TODO
 */
saltos.types.more = () => {
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
saltos.types.open = arg => {
    var temp = arg.split('/');
    temp.splice(0, 2);
    saltos.hash.add('app/types/list/' + temp.join('/'));
    saltos.types.__open_helper(arg);
};

/**
 * TODO
 *
 * TODO
 */
saltos.types.__open_helper = arg => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/index.php?' + arg.substr(1),
        method: 'get',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            var obj = document.getElementById('form');
            obj.innerHTML = '';
            for (var key in response) {
                var val = response[key];
                key = saltos.core.fix_key(key);
                // This is to prevent some attr that causes problems here
                if (['id', 'default'].includes(key)) {
                    continue;
                }
                // Continue
                if (typeof saltos.app.form[key] != 'function') {
                    throw new Error(`type ${key} not found`);
                }
                if (key == 'layout') {
                    obj.append(saltos.app.form.layout(val, 'div'));
                } else {
                    saltos.app.form[key](val);
                }
            }
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
saltos.types.cancel = () => {
    document.getElementById('form').innerHTML = '';
    saltos.hash.add('app/types');
};

/**
 * TODO
 *
 * TODO
 */
saltos.types.insert = arg => {
    if (!saltos.app.check_required()) {
        saltos.app.alert('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    var data = saltos.app.get_data();
    saltos.core.ajax({
        url: 'api/index.php',
        data: JSON.stringify({
            'action': 'insert',
            'app': arg.split('/').at(1),
            'data': data,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.types.search();
                saltos.types.cancel();
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
saltos.types.update = arg => {
    if (!saltos.app.check_required()) {
        saltos.app.alert('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    var data = saltos.app.get_data();
    if (!Object.keys(data).length) {
        saltos.app.alert('Warning', 'No changes detected', {color: 'danger'});
        return;
    }
    saltos.core.ajax({
        url: 'api/index.php',
        data: JSON.stringify({
            'action': 'update',
            'app': arg.split('/').at(1),
            'id': arg.split('/').at(3),
            'data': data,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.types.search();
                saltos.types.open('#app/types/view/' + arg.split('/').at(3));
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
saltos.types.delete = arg => {
    saltos.app.alert('Delete this register???', 'Do you want to delete this register???', {
        buttons: [{
            label: 'Yes',
            class: 'btn-success',
            icon: 'check-lg',
            onclick: () => {
                saltos.app.form.screen('loading');
                saltos.core.ajax({
                    url: 'api/index.php',
                    data: JSON.stringify({
                        'action': 'delete',
                        'app': arg.split('/').at(1),
                        'id': arg.split('/').at(3),
                    }),
                    method: 'post',
                    content_type: 'application/json',
                    success: response => {
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        if (response.status == 'ok') {
                            saltos.types.search();
                            saltos.types.cancel();
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
