
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
 * TODO
 *
 * TODO
 */

/**
 * Driver object
 *
 * This object stores the functions used by the layouts widgets and must work with all screens
 */
saltos.app.driver = {};

/**
 * Driver code
 *
 * This is the code that defines all methods of the driver object, the main idea of this object
 * is that only contains a forwarder functions to the driver used at each moment, to do it, it
 * is using the screen attribute placed in the document.body element.
 */
(() => {
    var fns = ['init', 'open', 'close'];
    for (var key in fns) {
        let val = fns[key];
        saltos.app.driver[val] = arg => {
            var screen = document.body.getAttribute('screen');
            if (!screen) {
                throw new Error(`screen not found`);
            }
            if (!saltos.app.__driver.hasOwnProperty(screen)) {
                throw new Error(`driver ${screen} not found`);
            }
            if (!saltos.app.__driver[screen].hasOwnProperty(val)) {
                throw new Error(`function ${val} for driver ${screen} not found`);
            }
            saltos.app.__driver[screen][val](arg);
        };
    }
})();

/**
 * Driver internal object
 *
 * This object stores the functions used by the main driver
 */
saltos.app.__driver = {};

/**
 * TODO
 *
 * TODO
 */
saltos.app.driver.search = arg => {
    document.getElementById('page').value = '0';
    saltos.app.form.screen('loading');
    var app = saltos.hash.get().split('/').at(1);
    saltos.core.ajax({
        url: `api/?list/${app}/table`,
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
saltos.app.driver.reset = arg => {
    document.getElementById('search').value = '';
    document.getElementById('page').value = '0';
    saltos.app.driver.search();
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.driver.more = arg => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value) + 1,
    saltos.app.form.screen('loading');
    var app = saltos.hash.get().split('/').at(1);
    saltos.core.ajax({
        url: `api/?list/${app}/table`,
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
saltos.app.driver.insert = arg => {
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
                if (document.getElementById('two')) {
                    saltos.app.driver.search();
                } else {
                    saltos.window.send(`saltos.${app}.update`);
                }
                saltos.app.driver.close();
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
saltos.app.driver.update = arg => {
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
    var id = saltos.hash.get().split('/').at(-1);
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
                if (document.getElementById('two')) {
                    saltos.app.driver.search();
                } else {
                    saltos.window.send(`saltos.${app}.update`);
                }
                saltos.app.driver.close();
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
saltos.app.driver.delete = arg => {
    saltos.app.modal('Delete this register???', 'Do you want to delete this register???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                saltos.app.form.screen('loading');
                var app = saltos.hash.get().split('/').at(1);
                var id = saltos.hash.get().split('/').at(-1);
                if (typeof arg == 'string') {
                    app = arg.split('/').at(1);
                    id = arg.split('/').at(-1);
                }
                saltos.core.ajax({
                    url: `api/index.php?delete/${app}/${id}`,
                    success: response => {
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        if (response.status == 'ok') {
                            if (document.getElementById('two')) {
                                saltos.app.driver.search();
                            } else {
                                saltos.window.send(`saltos.${app}.update`);
                            }
                            if (typeof arg == 'undefined') {
                                saltos.app.driver.close();
                            }
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
 * Driver type0 object
 *
 * This object stores the functions used by the type0 driver
 */
saltos.app.__driver.type0 = {};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type0.template = `
    <div id="top"></div>
    <div class="container">
        <div class="row">
            <div id="left" class="col-auto p-0 overflow-auto-xl d-flex"></div>
            <div id="one" class="col-xl py-3 overflow-auto-xl"></div>
            <div id="right" class="col-auto p-0 overflow-auto-xl d-flex"></div>
        </div>
    </div>
    <div id="bottom"></div>
`;

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type0.init = arg => {
    if (arg == 'list') {
        var app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.app.driver.search();
        });
    }
    if (arg == 'view') {
        var app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.hash.trigger();
        });
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.app.form_disabled(true);
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type0.open = arg => {
    saltos.window.open(arg);
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type0.close = arg => {
    saltos.window.close(arg);
};

/**
 * Driver type1 object
 *
 * This object stores the functions used by the type1 driver
 */
saltos.app.__driver.type1 = {};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type1.template = `
    <div id="top"></div>
    <div class="container-fluid">
        <div class="row">
            <div id="left" class="col-auto p-0 overflow-auto-xl d-flex"></div>
            <div id="one" class="col-xl py-3 overflow-auto-xl"></div>
            <div id="right" class="col-auto p-0 overflow-auto-xl d-flex"></div>
        </div>
    </div>
    <div id="bottom"></div>
`;

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type1.init = saltos.app.__driver.type0.init;
saltos.app.__driver.type1.open = saltos.app.__driver.type0.open;
saltos.app.__driver.type1.close = saltos.app.__driver.type0.close;

/**
 * Driver type2 object
 *
 * This object stores the functions used by the type2 driver
 */
saltos.app.__driver.type2 = {};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type2.template = `
    <div id="top"></div>
    <div class="container-fluid">
        <div class="row">
            <div id="left" class="col-auto p-0 overflow-auto-xl d-flex"></div>
            <div id="one" class="col-xl py-3 overflow-auto-xl"></div>
            <div id="two" class="col-xl py-3 overflow-auto-xl"></div>
            <div id="right" class="col-auto p-0 overflow-auto-xl d-flex"></div>
        </div>
    </div>
    <div id="bottom"></div>
`;

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type2.init = arg => {
    if (arg == 'list') {
        if (!document.getElementById('two').innerHTML.length) {
            saltos.app.driver.close();
            // This reset the form fields to allow the form_disabled
            saltos.app.__form.fields = [];
        }
    }
    if (['create','view','edit'].includes(arg)) {
        if (!document.getElementById('one').innerHTML.length) {
            var temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.__driver.type2.__open_helper('#' + temp);
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.app.form_disabled(true);
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type2.open = arg => {
    saltos.hash.add(arg);
    saltos.app.__driver.type2.__open_helper(arg);
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type2.__open_helper = arg => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?' + arg.substr(1),
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            for (var key in response) {
                var val = response[key];
                key = saltos.core.fix_key(key);
                // This is to prevent some attr that causes problems here
                if (['id', 'default', 'check'].includes(key)) {
                    continue;
                }
                // Continue
                if (typeof saltos.app.form[key] != 'function') {
                    throw new Error(`type ${key} not found`);
                }
                if (['title', 'layout', 'data', 'javascript'].includes(key)) {
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
        token: saltos.token.get(),
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type2.close = arg => {
    document.getElementById('two').innerHTML = '';
    document.getElementById('two').append(saltos.bootstrap.field({
        'type': 'div',
        'class': 'bg-primary-subtle h-100',
    }));
    // HASH PART
    var temp = saltos.hash.get().split('/').slice(0, 2).join('/');
    saltos.hash.add(temp);
};
