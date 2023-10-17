
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
 * Application helper module
 *
 * This fie contains useful functions related to the main application, at the end of this file you can
 * see the main code that launch the application to be executed from the browser
 */

/**
 * Show error helper
 *
 * This function allow to show a modal dialog with de details of an error
 */
saltos.show_error = error => {
    console.log(error);
    if (typeof error != 'object') {
        document.body.append(saltos.html(`<pre class='m-3'>${error}</pre>`));
        return;
    }
    saltos.modal({
        title: 'Error ' + error.code,
        close: 'Close',
        body: error.text,
        footer: (() => {
            var obj = saltos.html('<div></div>');
            obj.append(saltos.form_field({
                type: 'button',
                value: 'Close',
                class: 'btn-primary',
                onclick: () => {
                    saltos.modal('close');
                }
            }));
            return obj;
        })()
    });
};

/**
 * Check response helper
 *
 * This function is intended to process the response received by saltos.ajax and returns
 * if an error is detected in the response.
 */
saltos.check_response = response => {
    if (typeof response != 'object') {
        saltos.show_error(response);
        return false;
    }
    if (typeof response.error == 'object') {
        saltos.show_error(response.error);
        return false;
    }
    return true;
};

/**
 * Send request helper
 *
 * This function allow to send requests to the server and process the response
 */
saltos.send_request = data => {
    saltos.ajax({
        url: 'index.php?' + data,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            saltos.process_response(response);
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
 * Process response helper
 *
 * This function process the responses received by the send request
 */
saltos.process_response = response => {
    for (var key in response) {
        var val = response[key];
        key = saltos.fix_key(key);
        if (typeof saltos.form_app[key] != 'function') {
            console.log('type ' + key + ' not found');
            document.body.append(saltos.html('type ' + key + ' not found'));
            continue;
        }
        saltos.form_app[key](val);
    }
};

/**
 * Form constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.form_app = {};

/**
 * Data helper object
 *
 * This object allow to the app to store the data of the fields map
 */
saltos.__form_app = {
    fields: [],
    data: {},
};

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document
 */
saltos.form_app.data = data => {
    for (var key in data) {
        var val = data[key];
        var obj = document.getElementById(key);
        if (obj !== null) {
            if (obj.type == 'checkbox') {
                obj.checked = val ? true : false;
            } else {
                obj.value = val;
            }
        }
    }
};

/**
 * Form layout helper
 *
 * This function process the layout command, its able to process nodes as container, row, col and div
 * and all form_field defined in the bootstrap file, too have 2 modes of work:
 *
 * 1) normal mode => requires that the user specify all layout, container, row, col and fields.
 *
 * 2) auto mode => only requires set auto='true' to the layout node, and with this, all childrens
 * of the node are created inside a container, a row, and each field inside a col.
 *
 * Notes:
 *
 * This function add the fields to the saltos.__form_app.fields, this allow to the saltos.get_data
 * can retrieve the desired information of the fields.
 */
saltos.form_app.layout = (layout, extra) => {
    // Check for attr auto
    layout = saltos.form_app.__layout_auto_helper(layout);
    // Continue with original idea of use a entire specified layout
    var arr = [];
    for (var key in layout) {
        var val = layout[key];
        key = saltos.fix_key(key);
        var attr = {};
        var value = val;
        if (typeof val == 'object' && val.hasOwnProperty('value') && val.hasOwnProperty('#attr')) {
            attr = val['#attr'];
            value = val.value;
        }
        if (!attr.hasOwnProperty('type')) {
            attr.type = key;
        }
        if (key == 'layout') {
            var obj = saltos.form_app.layout({
                'value': value,
                '#attr': attr,
            }, 'div');
            arr.push(obj);
        } else if (['container', 'col', 'row', 'div'].includes(key)) {
            var obj = saltos.form_field(attr);
            var temp = saltos.form_app.layout(value, 'arr');
            for (var i in temp) {
                obj.append(temp[i]);
            }
            arr.push(obj);
        } else {
            if (typeof value == 'object') {
                for (var key2 in value) {
                    if (!attr.hasOwnProperty(key2)) {
                        attr[key2] = value[key2];
                    }
                }
            } else if (!attr.hasOwnProperty('value')) {
                attr.value = value;
            }
            saltos.check_params(attr, ['id', 'source']);
            if (attr.id == '') {
                attr.id = saltos.uniqid();
            }
            saltos.__form_app.fields.push(attr);
            if (attr.source != '') {
                var obj = saltos.form_field({
                    type: 'placeholder',
                    id: attr.id,
                });
                saltos.__source_helper(attr);
            } else {
                var obj = saltos.form_field(attr);
            }
            arr.push(obj);
        }
    }
    // Some extra features to allow that returns only the array
    if (extra == 'arr') {
        return arr;
    }
    var div = saltos.html('<div></div>');
    for (var i in arr) {
        div.append(arr[i]);
    }
    div = saltos.optimize(div);
    // Some extra features to allow that returns only the div
    if (extra == 'div') {
        return div;
    }
    // Defaut feature that all the div to the body's document
    document.body.append(div);
};

/**
 * Form layout auto helper
 *
 * This function implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @auto            => this boolean allow to enable or not this feature
 * @cols_per_row    => specify the number of cols inside of each row
 * @container_class => defines the class used by the container element
 * @row_class       => defines the class used by the row element
 * @col_class       => defines the class used by the col element
 * @container_style => defines the style used by the container element
 * @row_style       => defines the style used by the row element
 * @col_style       => defines the style used by the col element
 */
saltos.form_app.__layout_auto_helper = layout => {
    if (layout.hasOwnProperty('value') && layout.hasOwnProperty('#attr')) {
        var attr = layout['#attr'];
        var value = layout.value;
        saltos.check_params(attr, ['auto', 'cols_per_row']);
        saltos.check_params(attr, ['container_class', 'row_class', 'col_class']);
        saltos.check_params(attr, ['container_style', 'row_style', 'col_style']);
        if (attr.cols_per_row == '') {
            attr.cols_per_row = Infinity;
        }
        if (attr.auto == 'true') {
            // This trick convert all entries of the object in an array with the keys and values
            var temp = [];
            for (var key in value) {
                temp.push([key, value[key]]);
            }
            // This is the new layout object created with one container, rows, cols and all original
            // fields, too can specify what class use in each object created
            layout = {
                container: {
                    'value': {},
                    '#attr': {
                        class: attr.container_class,
                        style: attr.container_style,
                    }
                }
            };
            // this counters and flag are used to add rows using the cols_per_row parameter
            var numrow = 0;
            var numcol = 0;
            var addrow = 1;
            while (temp.length) {
                var item = temp.shift();
                if (addrow) {
                    numrow++;
                    layout.container.value['row#' + numrow] = {
                        'value': {},
                        '#attr': {
                            class: attr.row_class,
                            style: attr.row_style,
                        }
                    };
                }
                numcol++;
                var col_class = attr.col_class;
                var col_style = attr.col_style;
                if (item[1].hasOwnProperty('#attr')) {
                    if (item[1]['#attr'].hasOwnProperty('col_class')) {
                        col_class = item[1]['#attr'].col_class;
                    }
                    if (item[1]['#attr'].hasOwnProperty('col_style')) {
                        col_style = item[1]['#attr'].col_style;
                    }
                }
                layout.container.value['row#' + numrow].value['col#' + numcol] = {
                    'value': {},
                    '#attr': {
                        class: col_class,
                        style: col_style,
                    }
                };
                layout.container.value['row#' + numrow].value['col#' + numcol].value[item[0]] = item[1];
                if (numcol >= attr.cols_per_row) {
                    numcol = 0;
                    addrow = 1;
                } else {
                    addrow = 0;
                }
            }
        } else {
            layout = value;
        }
    }
    return layout;
};

/**
 * Form style helper
 *
 * This function allow to specify styles, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 *
 * Note that as some part of this code appear in the core.require function, we have decided
 * to replace it by a call to the saltos.require
 */
saltos.form_app.style = data => {
    for (var key in data) {
        var val = data[key];
        key = saltos.fix_key(key);
        if (key == 'inline') {
            var style = document.createElement('style');
            style.innerHTML = val;
            document.head.append(style);
        }
        if (key == 'file') {
            saltos.require(val);
        }
    }
};

/**
 * Form javascript helper
 *
 * This function allow to specify scripts, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 *
 * Note that as some part of this code appear in the core.require function, we have decided
 * to replace it by a call to the saltos.require
 */
saltos.form_app.javascript = data => {
    for (var key in data) {
        var val = data[key];
        key = saltos.fix_key(key);
        if (key == 'inline') {
            var script = document.createElement('script');
            script.innerHTML = val;
            document.body.append(script);
        }
        if (key == 'file') {
            saltos.require(val);
        }
    }
};

/**
 * Hash change management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
window.onhashchange = event => {
    var hash = document.location.hash;
    if (hash.substr(0, 1) == '#') {
        hash = hash.substr(1);
    }
    if (hash == '') {
        hash = 'app/menu';
        history.replaceState(null, null, '.#' + hash);
    }
    // Reset the body interface
    saltos.modal('close');
    saltos.offcanvas('close');
    saltos.loading(true);
    // Do the request
    saltos.send_request(hash);
};

/**
 * Loading helper
 *
 * This function adds and removes the spinner to emulate the loading effect screen
 *
 * @on_off => if you want to show or hide the loading spinner, the function returns
 * true when can do the action, false otherwise
 */
saltos.loading = on_off => {
    var obj = document.getElementById('loading');
    if (on_off && !obj) {
        document.body.append(saltos.html(`
            <div id='loading' class='d-flex justify-content-center align-items-center vh-100'>
                <div class='spinner-border' role='status'>
                    <span class='visually-hidden'>Loading...</span>
                </div>
            </div>
        `));
        window.scrollTo(0, 1 << 30);
        return true;
    }
    if (!on_off && obj) {
        window.scrollTo(0, 0);
        var timer = setInterval(() => {
            if (window.scrollY == 0) {
                obj.remove();
                clearInterval(timer);
            }
        }, 1);
        return true;
    }
    return false;
};

/**
 * Clear Screen
 *
 * This function remove all contents of the body
 */
saltos.clear_screen = () => {
    document.body.innerHTML = '';
};

/**
 * Source helper
 *
 * This function is intended to provide an asynchronous sources for a field, using the source attribute,
 * you can program an asynchronous ajax request to retrieve the data used to create the field.
 * *
 * This function is used in the fields of type table, alert, card and chartjs, the call of this function
 * is private and is intended to be used as a helper from the builders of the previous types opening
 * another way to pass arguments.
 *
 * @id     => the id used to set the reference for to the object
 * @type   => the type used to set the type for to the object
 * @source => data source used to load asynchronously the contents of the table (header, data,
 *            footer and divider)
 *
 * Notes:
 *
 * In some cases, the response for a source request can be an object that represents an xml node with
 * attributes and values, as for the example, the widget/2 used in the app.php, that returns an array
 * with all contents of the widget in the value entry and another entry used for the #attr that only
 * contains the id used to select the widget in the app.php, is this case, the unique data that we want
 * to use here is the contents of the value, and for this reason, the response is filtered to use only
 * the value key in the case of existence of the #attr and value keys
 */
saltos.__source_helper = field => {
    saltos.check_params(field, ['id', 'source']);
    // Check for asynchronous load using the source param
    if (field.source != '') {
        saltos.ajax({
            url: 'index.php?' + field.source,
            success: response => {
                if (!saltos.check_response(response)) {
                    return;
                }
                field.source = '';
                if (response.hasOwnProperty('value') && response.hasOwnProperty('#attr')) {
                    response = response.value;
                }
                for (var key in response) {
                    field[key] = response[key];
                }
                document.getElementById(field.id).replaceWith(saltos.form_field(field));
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
    }
};

/**
 * Get data
 *
 * This function retrieves the data of the fields in the current layout. to do this it uses
 * the saltos.__form_app.fields that contains the list of all used fields in the layout, this
 * function can retrieve all fields or only the fields that contains differences between the
 * original data and the current data.
 *
 * @full => boolean to indicate if you want the entire form or only the differences
 */
saltos.get_data = full => {
    saltos.__form_app.data = {};
    for (var i in saltos.__form_app.fields) {
        var field = saltos.__form_app.fields[i];
        var obj = document.getElementById(field.id);
        if (obj !== null) {
            if (['hidden','text','password','checkbox','textarea'].includes(obj.type)) {
                var val = obj.value;
                if (field.value != val || full) {
                    saltos.__form_app.data[field.id] = val;
                }
            }
        }
    }
    return saltos.__form_app.data;
};

/**
 * Token helper object
 *
 * This object stores all token functions to get and set data using the localStorage
 */
saltos.token = {};

/**
 * Get token function
 *
 * This function returns the token stored in the localStorage
 */
saltos.token.get_token = () => {
    return localStorage.getItem('saltos.token');
};

/**
 * Get expires function
 *
 * This function returns the expires stored in the localStorage
 */
saltos.token.get_expires = () => {
    return localStorage.getItem('saltos.expires');
};

/**
 * Set token and expires
 *
 * This function store the token and expires in the localStorage
 */
saltos.token.set = (token, expires) => {
    localStorage.setItem('saltos.token', token);
    localStorage.setItem('saltos.expires', expires);
};

/**
 * Unset token and expires
 *
 * This function removes the token and expires in the localStorage
 */
saltos.token.unset = () => {
    localStorage.removeItem('saltos.token');
    localStorage.removeItem('saltos.expires');
};

/**
 * Authentication helper object
 *
 * This object stores all authentication functions to get access, renew tokens to maintain
 * the access and the deauthtoken to close the access
 */
saltos.authenticate = {};

/**
 * Authenticate token function
 *
 * This function uses the authtoken action to try to authenticate an user with the user/pass
 * credentials passed by argument.
 *
 * @user => username used to the authentication process
 * @pass => password used to the authentication process
 */
saltos.authenticate.authtoken = (user, pass) => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'user': user,
            'pass': pass,
            'action': 'authtoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.set(response.token, response.expires_at);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        }
    });
};

/**
 * Re-authenticate token function
 *
 * This function uses the reauthtoken action to try to re-authenticate an user with the token
 * credentials.
 */
saltos.authenticate.reauthtoken = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'reauthtoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.set(response.token, response.expires_at);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
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
 * De-authenticate token function
 *
 * This function uses the deauthtoken action to try to de-authenticate an user with the token
 * credentials.
 */
saltos.authenticate.deauthtoken = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'deauthtoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.unset();
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
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
 * Check token function
 *
 * This function uses the checktoken action to check the validity of the current token.
 */
saltos.authenticate.checktoken = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'checktoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok' && response.token) {
                saltos.token.set(response.token, response.expires_at);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
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
 * Main code
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
(() => {
    // Dark theme part
    var window_match_media = window.matchMedia('(prefers-color-scheme: dark)');
    var set_data_bs_theme = e => {
        document.querySelector('html').setAttribute('data-bs-theme', e.matches ? 'dark' : '');
    };
    set_data_bs_theme(window_match_media);
    window_match_media.addEventListener('change', set_data_bs_theme);
    // Token part
    if (saltos.token.get_token() !== null) {
        saltos.authenticate.checktoken();
    }
    if (saltos.token.get_token() === null) {
        history.replaceState(null, null, '.#app/login');
    }
    // Init part
    window.dispatchEvent(new HashChangeEvent('hashchange'));
})();
