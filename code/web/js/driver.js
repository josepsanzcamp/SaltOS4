
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
 * Driver module
 *
 * Intended to be used as an abstraction layer to allow multiple screens configurations
 */

/**
 * Driver object
 *
 * This object stores the functions used by the layouts widgets and must work with all screens
 */
saltos.driver = {};

/**
 * Driver init
 *
 * This function initialize the driver screen, detects and replaces the styles, compute
 * the paddings checking the contents of the top and bottom layouts, initialize the filter
 * and the autosave features, and executes the specific init function.
 *
 * @arg => the desired action to do
 */
saltos.driver.init = async arg => {
    if (document.getElementById('saltos-driver-styles')) {
        document.getElementById('saltos-driver-styles').remove();
    }
    const obj = saltos.driver.styles();
    obj.setAttribute('id', 'saltos-driver-styles');
    document.getElementById('screen').append(obj);
    // Detect needed padding
    const has_top = document.getElementById('top').innerHTML.length;
    const has_bottom = document.getElementById('bottom').innerHTML.length;
    let new_class;
    if (!has_top && !has_bottom) {
        new_class = 'py-3';
    } else if (!has_top) {
        new_class = 'pt-3';
    } else if (!has_bottom) {
        new_class = 'pb-3';
    } else {
        new_class = '';
    }
    // Remove and apply the old and new paddings
    document.querySelectorAll('#one, #two, #three').forEach(_this => {
        ['py-3', 'pt-3', 'pb-3'].forEach(_this2 => {
            _this.classList.remove(_this2);
        });
        if (new_class != '') {
            _this.classList.add(new_class);
        }
    });
    // To check the list preferences
    if (arg == 'list') {
        await saltos.filter.init();
        saltos.filter.select();
        saltos.filter.load('last');
    }
    // To check the autosave feature
    if (['create', 'edit'].includes(arg)) {
        if (saltos.autosave.restore('two,one')) {
            saltos.app.modal('Attention', 'Data restored from the previous session', {color: 'danger'});
        }
        saltos.autosave.init('two,one');
    }
    // Old feature
    const type = document.getElementById('screen').getAttribute('type');
    saltos.driver.__types[type].init(arg);
};

/**
 * Driver open
 *
 * This function launch the specific open action for the screen type
 *
 * @arg => this argument is bypassed to the destination
 */
saltos.driver.open = arg => {
    const type = document.getElementById('screen').getAttribute('type');
    saltos.driver.__types[type].open(arg);
};

/**
 * Driver close
 *
 * This function close the current app using the history back if it is available,
 * otherwise use the specific close action for the screen type
 *
 * @arg => this argument forces to execute the specific driver close
 */
saltos.driver.close = arg => {
    if (arg !== undefined && saltos.core.eval_bool(arg)) {
        // Old feature
        const type = document.getElementById('screen').getAttribute('type');
        saltos.driver.__types[type].close(arg);
        return;
    }
    // Disable all autoclose
    document.querySelectorAll('[autoclose]').forEach(_this => {
        _this.removeAttribute('autoclose');
    });
    // Continue
    const url1 = window.location.href.toString();
    window.history.back();
    setTimeout(() => {
        const url2 = window.location.href.toString();
        if (url1 == url2) {
            // Old feature
            const type = document.getElementById('screen').getAttribute('type');
            saltos.driver.__types[type].close(arg);
        }
    }, 100);
};

/**
 * Driver cancel
 *
 * This function works in conjuntion with the autosave module, and checks if the
 * current screen contains new data, in this case ask to the user if they want
 * continue.
 *
 * This function checks that modal is close, otherwise an old confirm is used
 * to ask to the user.
 *
 * If the user decides continue to close, then the saltos.driver.close is executed
 * bypassing the arg argument.
 *
 * @arg => this argument is bypassed to the destination
 */
saltos.driver.cancel = arg => {
    saltos.backup.restore('two,one');
    const data = saltos.app.get_data();
    if (Object.keys(data).length) {
        if (saltos.bootstrap.modal('isopen')) {
            const bool = confirm('Do you want to close this screen???');
            if (bool) {
                saltos.autosave.clear('two,one');
                saltos.driver.close(arg);
            }
            return;
        }
        saltos.app.modal('Close this screen???', 'Do you want to close this screen???', {
            buttons: [{
                label: 'Yes',
                color: 'success',
                icon: 'check-lg',
                onclick: () => {
                    saltos.autosave.clear('two,one');
                    saltos.driver.close(arg);
                },
            },{
                label: 'No',
                color: 'danger',
                icon: 'x-lg',
                autofocus: true,
                onclick: () => {},
            }],
            color: 'danger',
        });
        return;
    }
    saltos.autosave.clear('two,one');
    saltos.driver.close(arg);
};

/**
 * Driver search
 *
 * This function implement the search feature associated to tables and lists
 * using the filters fields
 *
 * @arg => unused at this scope
 */
saltos.driver.search = arg => {
    document.getElementById('page').value = '0';
    saltos.backup.restore('top+one');
    const data = saltos.app.get_data(true);
    saltos.filter.update('last', data);
    const app = saltos.hash.get().split('/').at(1);
    let type = '';
    if (document.getElementById('table')) {
        type = 'table';
    }
    if (document.getElementById('list')) {
        type = 'list';
    }
    if (!type) {
        throw new Error('Unknown list type');
    }
    // Restore the more button
    const obj = document.getElementById('more');
    if (obj && 'set_disabled' in obj && typeof obj.set_disabled == 'function') {
        obj.set_disabled(false);
    }
    // Continue
    saltos.app.ajax({
        url: `app/${app}/list/${type}`,
        data: data,
        success: response => {
            response.id = type;
            const temp = saltos.gettext.bootstrap.field(response);
            if (type == 'table') {
                document.getElementById('table').replaceWith(temp);
            }
            if (type == 'list') {
                document.querySelectorAll('.list-group:not([id=list])').forEach(_this => {
                    _this.remove();
                });
                document.getElementById('list').replaceWith(temp);
            }
            document.getElementById('one').scrollTop = 0;
        },
    });
};

/**
 * Driver search
 *
 * This function implement the reset feature associated to the filters fields
 *
 * @arg => unused at this scope
 */
saltos.driver.reset = arg => {
    saltos.backup.restore('top+one');
    const types = ['text', 'hidden', 'integer', 'float', 'color', 'date', 'time',
        'datetime', 'textarea', 'ckeditor', 'codemirror', 'select', 'multiselect',
        'checkbox', 'switch', 'password', 'file', 'excel', 'tags', 'onetag'];
    for (const i in saltos.form.__form.fields) {
        const field = saltos.form.__form.fields[i];
        if (!types.includes(field.type)) {
            continue;
        }
        const obj = document.getElementById(field.id);
        if (!obj) {
            continue;
        }
        // Check to prevent objects in value
        if (typeof field.value != 'object') {
            obj.value = field.value;
        }
        // Special case for widgets with set
        if ('set' in obj && typeof obj.set == 'function') {
            obj.set(field.value);
        }
    }
    saltos.driver.search();
};

/**
 * Driver more
 *
 * This function implement the more feature associated to tables and lists
 * using the filters fields
 *
 * @arg => unused at this scope
 */
saltos.driver.more = arg => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value) + 1;
    saltos.backup.restore('top+one');
    const data = saltos.app.get_data(true);
    const app = saltos.hash.get().split('/').at(1);
    let type = '';
    if (document.getElementById('table')) {
        type = 'table';
    }
    if (document.getElementById('list')) {
        type = 'list';
    }
    if (!type) {
        throw new Error('Unknown list type');
    }
    saltos.app.ajax({
        url: `app/${app}/list/${type}`,
        data: data,
        success: response => {
            if (!response.data.length) {
                saltos.app.toast('Response', 'There is no more data', {color: 'warning'});
                // Disable the more button
                const obj = document.getElementById('more');
                if (obj && 'set_disabled' in obj && typeof obj.set_disabled == 'function') {
                    obj.set_disabled(true);
                }
                // Continue
                return;
            }
            const temp = saltos.gettext.bootstrap.field(response);
            if (type == 'table') {
                const obj = document.getElementById('table').querySelector('tbody');
                temp.querySelectorAll('table tbody tr').forEach(_this => {
                    obj.append(_this);
                });
            }
            if (type == 'list') {
                const obj = document.getElementById('list').parentElement;
                obj.append(temp);
            }
        },
    });
};

/**
 * Driver insert
 *
 * This function implement the insert feature associated to the current app fields
 *
 * @arg => unused at this scope
 */
saltos.driver.insert = arg => {
    saltos.backup.restore('two,one');
    if (!saltos.app.check_required()) {
        saltos.app.toast('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    const data = saltos.app.get_data();
    if (!Object.keys(data).length) {
        saltos.app.toast('Warning', 'No data found', {color: 'danger'});
        return;
    }
    const app = saltos.hash.get().split('/').at(1);
    saltos.app.ajax({
        url: `app/${app}/insert`,
        data: data,
        proxy: 'network,queue',
        success: response => {
            if (response.status == 'ok') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text);
                }
                saltos.window.send(`saltos.${app}.update`);
                saltos.autosave.clear('two,one');
                saltos.driver.close();
                return;
            }
            if (response.status == 'ko') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text, {color: 'danger'});
                }
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * Driver update
 *
 * This function implement the update feature associated to the current app fields
 *
 * @arg => unused at this scope
 */
saltos.driver.update = arg => {
    saltos.backup.restore('two,one');
    if (!saltos.app.check_required()) {
        saltos.app.toast('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    const data = saltos.app.get_data();
    if (!Object.keys(data).length) {
        saltos.app.toast('Warning', 'No changes detected', {color: 'danger'});
        return;
    }
    const app = saltos.hash.get().split('/').at(1);
    const id = saltos.hash.get().split('/').at(-1);
    saltos.app.ajax({
        url: `app/${app}/update/${id}`,
        data: data,
        proxy: 'network,queue',
        success: response => {
            if (response.status == 'ok') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text);
                }
                saltos.window.send(`saltos.${app}.update`);
                saltos.autosave.clear('two,one');
                saltos.driver.close();
                return;
            }
            if (response.status == 'ko') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text, {color: 'danger'});
                }
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * Driver delete
 *
 * This function implement the delete feature associated to the current app register
 *
 * @arg => this field can contain the hash of the deletion
 */
saltos.driver.delete = async arg => {
    if (saltos.bootstrap.modal('isopen')) {
        // Disable all autoclose
        document.querySelectorAll('[autoclose]').forEach(_this => {
            _this.removeAttribute('autoclose');
        });
        // Continue
        saltos.bootstrap.modal('close');
        while (saltos.bootstrap.modal('isopen')) {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
    }
    saltos.app.modal('Delete???', 'Do you want to delete this register???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                let app = saltos.hash.get().split('/').at(1);
                let id = saltos.hash.get().split('/').at(-1);
                if (typeof arg == 'string') {
                    app = arg.split('/').at(1);
                    id = arg.split('/').at(-1);
                }
                saltos.app.ajax({
                    url: `app/${app}/delete/${id}`,
                    proxy: 'network',
                    success: response => {
                        if (response.status == 'ok') {
                            if ('text' in response) {
                                saltos.app.toast('Response', response.text);
                            }
                            saltos.window.send(`saltos.${app}.update`);
                            // arg has valid data when is called from the list, and in
                            // this case, it is improtant to don't close the current view
                            if (arg === undefined) {
                                saltos.driver.close();
                            }
                            return;
                        }
                        if (response.status == 'ko') {
                            if ('text' in response) {
                                saltos.app.toast('Response', response.text, {color: 'danger'});
                            }
                            return;
                        }
                        saltos.app.show_error(response);
                    },
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
 * Driver placeholder
 *
 * This function sets a placeholder object in the element identified by the arg
 *
 * @arg => the id of the element where do you want to put the placeholder
 */
saltos.driver.placeholder = arg => {
    const obj = saltos.core.html(`
        <div class="bg-primary-subtle h-100 driver-placeholder"></div>
    `);
    obj.append(saltos.core.html(`
        <style>
            .driver-placeholder {
                background-image: url(img/logo_white.svg);
                background-repeat: no-repeat;
                background-position: center;
                background-size: 75% 75%;
            }
            html[data-bs-theme=dark] .driver-placeholder {
                background-image: url(img/logo_black.svg);
            }
        </style>
    `));
    document.getElementById(arg).replaceChildren(obj);
};

/**
 * Driver styles
 *
 * This function returns the style object that contains the tricks to do
 * that the screen with verticals scrolls runs as expected
 *
 * @arg => the break-size used in the driver screen, xl as default
 */
saltos.driver.styles = (arg = 'xl') => {
    const sizes = {
        xs: 0,
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200,
        xxl: 1400,
    };
    const size = sizes[arg];
    const height = document.getElementById('header').offsetHeight +
        document.getElementById('top').offsetHeight +
        document.getElementById('bottom').offsetHeight +
        document.getElementById('footer').offsetHeight;
    return saltos.core.html(`
        <style>
            @media (min-width: ${size}px) {
                .overflow-auto-${arg} {
                    height: calc(100vh - ${height}px);
                    overflow: auto;
                }
            }
        </style>
    `);
};

/**
 * Driver search if needed
 *
 * This function launch the saltos.driver.search action if the action is the
 * same before and after the setTimeout, too is launched if action source and
 * the action destination complains with some pair of actions defined in the
 * arg argument.
 *
 * @arg => an array with pairs of actions
 */
saltos.driver.search_if_needed = arg => {
    const action1 = saltos.hash.get().split('/').at(2);
    setTimeout(() => {
        const action2 = saltos.hash.get().split('/').at(2);
        //~ console.log(action1 + ' => ' + action2);
        if (action1 == action2) {
            // Old feature
            saltos.driver.search();
            saltos.favicon.run();
            return;
        }
        for (const key in arg) {
            const val = arg[key];
            if (action1 == val[0] && action2 == val[1]) {
                // Old feature
                saltos.driver.search();
                saltos.favicon.run();
                return;
            }
        }
    }, 100);
};

/**
 * Driver internal object
 *
 * This object stores the functions used by the main driver
 */
saltos.driver.__types = {};

/**
 * Driver type1 object
 *
 * This object stores the functions used by the type1 driver
 */
saltos.driver.__types.type1 = {};

/**
 * Driver type1 template
 *
 * This function returns the type1 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type1.template = arg => {
    const obj = saltos.core.html(`
        <div id="screen" type="type1">
            <div id="header" class="sticky-top"></div>
            <div class="container-xl">
                <div class="row">
                    <div id="top" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="one" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="bottom" class="col-12"></div>
                </div>
            </div>
            <div id="footer" class="sticky-bottom"></div>
        </div>
    `);
    return obj;
};

/**
 * Driver type1 init
 *
 * This function initialize the type1 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type1.init = arg => {
    if (arg == 'list') {
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search();
            saltos.favicon.run();
        });
    }
    if (arg == 'view') {
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.hash.trigger();
            saltos.favicon.run();
        });
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type1 open
 *
 * This function open a new window
 *
 * @arg => the desired url
 */
saltos.driver.__types.type1.open = arg => {
    const only1 = saltos.hash.get().split('/').at(-1);
    const only2 = arg.split('/').at(-1);
    if (only1 == 'only' && only2 != 'only') {
        arg += '/only';
    }
    saltos.window.open(arg);
};

/**
 * Driver type1 close
 *
 * This function close the window
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type1.close = arg => {
    saltos.window.close();
};

/**
 * Driver type2 object
 *
 * This object stores the functions used by the type2 driver
 */
saltos.driver.__types.type2 = {};

/**
 * Driver type2 template
 *
 * This function returns the type2 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type2.template = arg => {
    const obj = saltos.core.html(`
        <div id="screen" type="type2">
            <div id="header"></div>
            <div class="container-fluid">
                <div class="row">
                    <div id="top" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="one" class="col-xl overflow-auto-xl"></div>
                    <div id="two" class="col-xl overflow-auto-xl"></div>
                </div>
                <div class="row">
                    <div id="bottom" class="col-12"></div>
                </div>
            </div>
            <div id="footer"></div>
        </div>
    `);
    return obj;
};

/**
 * Driver type2 init
 *
 * This function initialize the type2 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type2.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.driver.placeholder('two');
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['create', 'view'],
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type2 open
 *
 * This function open a new content
 *
 * @arg => the desired url
 */
saltos.driver.__types.type2.open = arg => {
    saltos.hash.add(arg);
    saltos.app.send_request(arg);
};

/**
 * Driver type2 close
 *
 * This function close the two zone of the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type2.close = arg => {
    saltos.driver.placeholder('two');
    // Hash part
    const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
    saltos.hash.add(temp);
};

/**
 * Driver type3 object
 *
 * This object stores the functions used by the type3 driver
 */
saltos.driver.__types.type3 = {};

/**
 * Driver type3 template
 *
 * This function returns the type3 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type3.template = arg => {
    const obj = saltos.core.html(`
        <div id="screen" type="type3">
            <div id="header"></div>
            <div class="container-fluid">
                <div class="row">
                    <div id="top" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="one" class="col-xl overflow-auto-xl"></div>
                    <div id="two" class="col-xl overflow-auto-xl"></div>
                    <div id="three" class="col-xl overflow-auto-xl"></div>
                </div>
                <div class="row">
                    <div id="bottom" class="col-12"></div>
                </div>
            </div>
            <div id="footer"></div>
        </div>
    `);
    return obj;
};

/**
 * Driver type3 init
 *
 * This function initialize the type3 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type3.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.driver.placeholder('two');
            saltos.driver.placeholder('three');
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['create', 'view'],
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
        if (!document.getElementById('two').textContent.trim().length) {
            let temp = saltos.hash.get().split('/');
            temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
            saltos.app.send_request(temp);
        }
        const arr = saltos.hash.get().split('/');
        if (arr.length < 5) {
            saltos.driver.placeholder('three');
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type3 open
 *
 * This function bypass to the type2 driver
 *
 * @arg => the desired url
 */
saltos.driver.__types.type3.open = saltos.driver.__types.type2.open;

/**
 * Driver type3 close
 *
 * This function close the three and/or two zone of the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type3.close = arg => {
    const arr = saltos.hash.get().split('/');
    const action = saltos.hash.get().split('/').at(2);
    if (arr.length >= 5 && action == 'view') {
        saltos.driver.placeholder('three');
        // Hash part
        let temp = saltos.hash.get().split('/');
        temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
        saltos.hash.add(temp);
    } else {
        saltos.driver.placeholder('two');
        // Hash part
        const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
        saltos.hash.add(temp);
    }
};

/**
 * Driver type4 object
 *
 * This object stores the functions used by the type4 driver
 */
saltos.driver.__types.type4 = {};

/**
 * Driver type4 template
 *
 * This function returns the type4 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type4.template = arg => {
    const obj = saltos.driver.__types.type1.template();
    obj.setAttribute('type', 'type4');
    const div = saltos.core.html(`<div id="two" class="d-none"></div>`);
    obj.querySelector('#one').after(div);
    return obj;
};

/**
 * Driver type4 init
 *
 * This function initialize the type4 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type4.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.bootstrap.modal('close');
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
        if (document.getElementById('two').textContent.trim().length) {
            const obj = document.getElementById('two').firstElementChild;
            if (!saltos.bootstrap.modal('isopen')) {
                saltos.gettext.bootstrap.modal({
                    close: 'Close',
                    body: obj,
                    class: 'modal-xl',
                });
            } else {
                document.querySelector('.modal-body').replaceChildren(obj);
            }
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type4 open
 *
 * This function bypass to the type2 driver
 *
 * @arg => the desired url
 */
saltos.driver.__types.type4.open = saltos.driver.__types.type2.open;

/**
 * Driver type4 close
 *
 * This function close the modal
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type4.close = arg => {
    saltos.bootstrap.modal('close');
    // Hash part
    const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
    saltos.hash.add(temp);
};

/**
 * Driver type5 object
 *
 * This object stores the functions used by the type5 driver
 */
saltos.driver.__types.type5 = {};

/**
 * Driver type5 template
 *
 * This function returns the type5 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type5.template = arg => {
    const obj = saltos.driver.__types.type2.template();
    obj.setAttribute('type', 'type5');
    const div = saltos.core.html(`<div id="three" class="d-none"></div>`);
    obj.querySelector('#two').after(div);
    return obj;
};

/**
 * Driver type5 init
 *
 * This function initialize the type5 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type5.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.driver.placeholder('two');
            saltos.bootstrap.modal('close');
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['create', 'view'],
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
        if (!document.getElementById('two').textContent.trim().length) {
            let temp = saltos.hash.get().split('/');
            temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
            saltos.app.send_request(temp);
        }
        if (document.getElementById('three').textContent.trim().length) {
            const obj = document.getElementById('three').firstElementChild;
            if (!saltos.bootstrap.modal('isopen')) {
                saltos.gettext.bootstrap.modal({
                    close: 'Close',
                    body: obj,
                    class: 'modal-xl',
                });
            } else {
                document.querySelector('.modal-body').replaceChildren(obj);
            }
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type5 open
 *
 * This function bypass to the type2 driver
 *
 * @arg => the desired url
 */
saltos.driver.__types.type5.open = saltos.driver.__types.type2.open;

/**
 * Driver type5 close
 *
 * This function close the modal and/or two zone of the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type5.close = arg => {
    const arr = saltos.hash.get().split('/');
    const action = saltos.hash.get().split('/').at(2);
    if (arr.length >= 5 && action == 'view') {
        saltos.bootstrap.modal('close');
        // Hash part
        let temp = saltos.hash.get().split('/');
        temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
        saltos.hash.add(temp);
    } else {
        saltos.driver.placeholder('two');
        // Hash part
        const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
        saltos.hash.add(temp);
    }
};
