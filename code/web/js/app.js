
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
 * Application helper module
 *
 * This fie contains useful functions related to the main application, at the end of this file you
 * can see the main code that launch the application to be executed from the browser
 */

/**
 * Application helper object
 *
 * This object stores all application functions and data
 */
saltos.app = {};

/**
 * Alert function
 *
 * This function tries to implement an alert box, the main difference between the tipical alert
 * is that this alert allow to you to specify the title and a more complex message, but it only
 * shows one button to close it.
 *
 * @title   => title of the alert modal dialog
 * @message => message of the alert modal dialog
 * @extra   => object with array of buttons and color
 */
saltos.app.alert = (title, message, extra) => {
    if (typeof extra == 'undefined') {
        var extra = {};
    }
    if (!extra.hasOwnProperty('buttons')) {
        extra.buttons = [{
            label: 'Close',
            class: 'btn-primary',
            icon: 'x-lg',
            autofocus: true,
            onclick: () => {},
        }];
    }
    if (!extra.hasOwnProperty('color')) {
        extra.color = 'primary';
    }
    saltos.bootstrap.modal({
        title: title,
        close: 'Close',
        body: message,
        footer: (() => {
            var obj = saltos.core.html('<div></div>');
            for (var key in extra.buttons) {
                (button => {
                    saltos.core.check_params(button, ['label', 'class', 'icon', 'autofocus', 'onclick']);
                    obj.append(saltos.bootstrap.field({
                        type: 'button',
                        value: button.label,
                        class: `${button.class} ms-1`,
                        icon: button.icon,
                        autofocus: button.autofocus,
                        onclick: () => {
                            button.onclick();
                            saltos.bootstrap.modal('close');
                        }
                    }));
                })(extra.buttons[key]);
            }
            return obj;
        })(),
        color: extra.color,
    });
};

/**
 * Toast function
 *
 * This function tries to implement a toast notice.
 *
 * @title   => title of the toast
 * @message => message of the toast
 * @extra   => object with array of buttons and color
 */
saltos.app.toast = (title, message, extra) => {
    if (typeof extra == 'undefined') {
        var extra = {};
    }
    if (!extra.hasOwnProperty('color')) {
        extra.color = 'primary';
    }
    saltos.bootstrap.toast({
        title: title,
        body: message,
        color: extra.color,
    });
};

/**
 * Show error helper
 *
 * This function allow to show a modal dialog with de details of an error
 */
saltos.app.show_error = error => {
    if (typeof error != 'object') {
        document.body.append(saltos.core.html(`<pre class="m-3">${error}</pre>`));
        saltos.app.form.screen('unloading');
        return;
    }
    saltos.app.alert('Error ' + error.code, error.text, {color: 'danger'});
};

/**
 * Check response helper
 *
 * This function is intended to process the response received by saltos.core.ajax and returns
 * if an error is detected in the response.
 */
saltos.app.check_response = response => {
    if (typeof response != 'object') {
        saltos.app.show_error(response);
        return false;
    }
    if (typeof response.error == 'object') {
        saltos.app.show_error(response.error);
        return false;
    }
    return true;
};

/**
 * Send request helper
 *
 * This function allow to send requests to the server and process the response
 */
saltos.app.send_request = data => {
    saltos.core.ajax({
        url: 'api/index.php?' + data,
        success: response => {
            if (!saltos.app.check_response(response)) {
                return;
            }
            saltos.app.process_response(response);
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
 * Process response helper
 *
 * This function process the responses received by the send request
 */
saltos.app.process_response = response => {
    for (var key in response) {
        var val = response[key];
        key = saltos.core.fix_key(key);
        if (typeof saltos.app.form[key] != 'function') {
            throw new Error(`type ${key} not found`);
        }
        saltos.app.form[key](val);
    }
};

/**
 * Form constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.app.form = {};

/**
 * Data helper object
 *
 * This object allow to the app to store the data of the fields map
 */
saltos.app.__form = {
    fields: [],
    data: {},
    templates: {},
};

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document, too as bonus
 * extra, it tries to search the field spec in the array to update the value of the field spec to
 * allow that the get_data can differ between the original data and the modified data.
 */
saltos.app.form.data = data => {
    // Check for attr template_id
    if (data.hasOwnProperty('#attr') && data['#attr'].hasOwnProperty('template_id')) {
        var template_id = data['#attr'].template_id;
        if (!Array.isArray(data.value)) {
            throw new Error(`data for template ${template_id} is not an array of rows`);
        }
        for (var key in data.value) {
            var val = data.value[key];
            if (parseInt(key)) {
                saltos.app.form.layout(saltos.app.form.__layout_template_helper(template_id, key));
            }
            saltos.app.form.data(saltos.app.form.__data_template_helper(template_id, val, key));
        }
        return;
    }
    // Continue with the normal behaviour
    if (Array.isArray(data)) {
        throw new Error(`data is an array instead of an object of key and val pairs`);
    }
    for (var key in data) {
        var val = data[key];
        if (val === null) {
            val = '';
        }
        // This updates the object
        var obj = document.getElementById(key);
        if (obj) {
            obj.value = val;
            // Special case for checkboxes
            if (obj.type == 'checkbox') {
                obj.checked = val ? true : false;
            }
            // Special case for iframes
            if (obj.hasAttribute('src')) {
                obj.src = val;
            }
            if (obj.hasAttribute('srcdoc')) {
                obj.srcdoc = val;
            }
        }
        // This updates the field spec
        var obj2 = saltos.app.__form.fields.find(elem => elem.id == key);
        if (typeof obj2 != 'undefined') {
            obj2.value = val;
        }
    }
};

/**
 * Data template helper
 *
 * This function allow to convert the data object that contains the values for the fields idenfidied
 * by the keys of the associative array into a data object with the keys ready to be used by the
 * fields of a template, this fields are of the follow structure: TEMPLATE_ID#ID#INDEX
 *
 * @template_id => the template identity used in the spec
 * @data        => the object that contains the data associated to the row
 * @index       => the index used in all fields of the template
 */
saltos.app.form.__data_template_helper = (template_id, data, index) => {
    for (var key in data) {
        var val = data[key];
        delete data[key];
        data[template_id + '.' + index + '.' + key] = val;
    }
    return data;
};

/**
 * Layout template helper
 *
 * This function returns the template identified by the template_id for the specified index, ready
 * to be used by the saltos.app.form.layout function.
 *
 * @template_id => the template identity used in the spec
 * @index       => the index used in all fields of the template
 */
saltos.app.form.__layout_template_helper = (template_id, index) => {
    var template = saltos.core.copy_object(saltos.app.__form.templates[template_id]);
    for (var key in template.value) {
        var val = template.value[key];
        if (val['#attr'].hasOwnProperty('id')) {
            var id = val['#attr'].id;
            val['#attr'].id = template_id + '.' + index + '.' + id;
        }
    }
    return template;
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
 * This function add the fields to the saltos.app.__form.fields, this allow to the saltos.app.get_data
 * can retrieve the desired information of the fields.
 */
saltos.app.form.layout = (layout, extra) => {
    // Check for template_id attr
    if (layout.hasOwnProperty('#attr') && layout['#attr'].hasOwnProperty('template_id')) {
        // Store the copy in the templates container
        var template_id = layout['#attr'].template_id;
        var temp = saltos.core.copy_object(layout);
        delete temp['#attr'].template_id;
        saltos.app.__form.templates[template_id] = temp;
        // Modify the id of all elements to convert it to the format TEMPLATE_ID#ID#0
        layout = saltos.app.form.__layout_template_helper(template_id, 0);
    }
    // Check for auto attr
    layout = saltos.app.form.__layout_auto_helper(layout);
    // Continue with original idea of use a entire specified layout
    var arr = [];
    for (var key in layout) {
        var val = layout[key];
        key = saltos.core.fix_key(key);
        var attr = {};
        var value = val;
        if (saltos.core.is_attr_value(val)) {
            attr = val['#attr'];
            value = val.value;
        }
        if (!attr.hasOwnProperty('type')) {
            attr.type = key;
        }
        if (key == 'layout') {
            var obj = saltos.app.form.layout({
                'value': value,
                '#attr': attr,
            }, 'div');
            arr.push(obj);
        } else if (['container', 'col', 'row', 'div'].includes(key)) {
            var obj = saltos.bootstrap.field(attr);
            var temp = saltos.app.form.layout(value, 'arr');
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
            saltos.core.check_params(attr, ['id', 'source']);
            if (attr.id == '') {
                attr.id = saltos.core.uniqid();
            }
            saltos.app.__form.fields.push(attr);
            if (attr.source != '') {
                var obj = saltos.bootstrap.field({
                    type: 'placeholder',
                    id: attr.id,
                });
                saltos.app.__source_helper(attr);
            } else {
                var obj = saltos.bootstrap.field(attr);
            }
            arr.push(obj);
        }
    }
    // Some extra features to allow that returns only the array
    if (extra == 'arr') {
        return arr;
    }
    var div = saltos.core.html('<div></div>');
    for (var i in arr) {
        div.append(arr[i]);
    }
    div = saltos.core.optimize(div);
    // Some extra features to allow that returns only the div
    if (extra == 'div') {
        return div;
    }
    // Defaut feature that all the div to the body's document
    document.body.append(div);
    var obj = document.querySelector('[autofocus]');
    if (obj) {
        obj.focus();
    }
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
saltos.app.form.__layout_auto_helper = layout => {
    if (!layout.hasOwnProperty('value') || !layout.hasOwnProperty('#attr')) {
        return layout;
    }
    var attr = layout['#attr'];
    var value = layout.value;
    saltos.core.check_params(attr, ['auto', 'cols_per_row']);
    saltos.core.check_params(attr, ['container_class', 'row_class', 'col_class']);
    saltos.core.check_params(attr, ['container_style', 'row_style', 'col_style']);
    if (!saltos.core.eval_bool(attr.auto)) {
        return value;
    }
    if (attr.cols_per_row == '') {
        attr.cols_per_row = Infinity;
    }
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
        // This trick allow to hide the hidden fields
        if (saltos.core.fix_key(item[0]) == 'hidden') {
            col_class = 'd-none';
            col_style = '';
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
    return layout;
};

/**
 * Form style helper
 *
 * This function allow to specify styles, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 *
 * Note that as some part of this code appear in the core.require function, we have decided
 * to replace it by a call to the saltos.core.require
 */
saltos.app.form.style = data => {
    for (var key in data) {
        var val = data[key];
        key = saltos.core.fix_key(key);
        if (key == 'inline') {
            var style = document.createElement('style');
            style.innerHTML = val;
            document.head.append(style);
        }
        if (key == 'file') {
            saltos.core.require(val);
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
 * to replace it by a call to the saltos.core.require
 */
saltos.app.form.javascript = data => {
    for (var key in data) {
        var val = data[key];
        key = saltos.core.fix_key(key);
        if (key == 'inline') {
            var script = document.createElement('script');
            script.innerHTML = val;
            document.body.append(script);
        }
        if (key == 'file') {
            saltos.core.require(val);
        }
    }
};

/**
 * Form title helper
 *
 * This function sets the document title, too it checks the existence of the x-powered-by
 * header received in the ajax calls and stored in the saltos object to be used in the
 * last part of the title.
 *
 * @title => The title that you want to set in the page
 */
saltos.app.form.title = title => {
    if (!saltos.core.hasOwnProperty('x_powered_by')) {
        document.title = title;
        return;
    }
    document.title = title + ' - ' + saltos.core.x_powered_by;
};

/**
 * Form screen helper
 *
 * This function adds and removes the spinner to emulate the loading effect screen, too is able
 * to clear the screen by removing all contents of the body
 *
 * @action => use loading, unloading or clear to execute the desired action
 */
saltos.app.form.screen = action => {
    if (action == 'loading') {
        var obj = document.getElementById('loading');
        if (obj) {
            return false;
        }
        obj = saltos.core.html(`
            <div id="loading" class="w-100 h-100 position-fixed top-0 start-0 opacity-75">
                <div class="spinner-border position-fixed top-50 start-50" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        var dark = document.querySelector('html').getAttribute('data-bs-theme');
        if (!dark) {
            obj.classList.add('bg-light');
            obj.classList.add('text-dark');
        } else {
            obj.classList.add('bg-dark');
            obj.classList.add('text-light');
        }
        document.body.append(obj);
        return true;
    }
    if (action == 'unloading') {
        var obj = document.getElementById('loading');
        if (!obj) {
            return false;
        }
        obj.remove();
        return true;
    }
    if (action == 'clear') {
        document.body.innerHTML = '';
        return true;
    }
    return false;
};

/**
 * Form navbar helper
 *
 * This function create a navbar with their menus on the top of the page
 *
 * TODO
 */
saltos.app.form.navbar = navbar => {
    navbar['#attr'] = saltos.app.parse_data(navbar['#attr']);
    navbar = saltos.core.join_attr_value(navbar);
    if (navbar.hasOwnProperty('items')) {
        var items = [];
        for (var key in navbar.items) {
            var val = navbar.items[key];
            if (saltos.core.fix_key(key) == 'menu') {
                var _class = '';
                var menu = [];
                if (saltos.core.is_attr_value(val)) {
                    if (val['#attr'].hasOwnProperty('class')) {
                        _class = val['#attr'].class;
                    }
                    val = val.value;
                }
                for (var key2 in val) {
                    var val2 = val[key2];
                    if (typeof val2.value == 'string') {
                        menu.push(val2['#attr']);
                    } else if (val2.value.hasOwnProperty('menu')) {
                        var menu2 = [];
                        for (var key3 in val2.value.menu) {
                            var val3 = val2.value.menu[key3];
                            menu2.push(val3['#attr']);
                        }
                        menu.push({
                            ...val2['#attr'],
                            menu: menu2,
                        });
                    }
                }
                navbar.items[key] = saltos.bootstrap.menu({
                    class: _class,
                    menu: menu,
                });
            } else if (saltos.core.fix_key(key) == 'form') {
                var _class = '';
                if (saltos.core.is_attr_value(val)) {
                    if (val['#attr'].hasOwnProperty('class')) {
                        _class = val['#attr'].class;
                    }
                    val = val.value;
                }
                var obj = saltos.core.html(`<form class='${_class}' onsubmit='return false'></form>`);
                for (var key2 in val) {
                    var val2 = val[key2];
                    val2['#attr'].type = saltos.core.fix_key(key2);
                    obj.append(saltos.bootstrap.field(val2['#attr']));
                }
                navbar.items[key] = obj;
            }
        }
    }
    var obj = saltos.bootstrap.navbar(navbar);
    document.body.append(obj);
};

/**
 * Source helper
 *
 * This function is intended to provide an asynchronous sources for a field, using the source attribute,
 * you can program an asynchronous ajax request to retrieve the data used to create the field.
 *
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
 * At the end of the object replacement, the load event is triggered to the old object to notify
 * that the update was finished.
 */
saltos.app.__source_helper = field => {
    saltos.core.check_params(field, ['id', 'source']);
    // Check for asynchronous load using the source param
    if (field.source != '') {
        saltos.core.ajax({
            url: 'api/index.php?' + field.source,
            success: response => {
                if (!saltos.app.check_response(response)) {
                    return;
                }
                field.source = '';
                for (var key in response) {
                    field[key] = response[key];
                }
                var obj = document.getElementById(field.id);
                obj.replaceWith(saltos.bootstrap.field(field));
                obj.dispatchEvent(new Event('load'));
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
    }
};

/**
 * Get data
 *
 * This function retrieves the data of the fields in the current layout. to do this it uses
 * the saltos.app.__form.fields that contains the list of all used fields in the layout, this
 * function can retrieve all fields or only the fields that contains differences between the
 * original data and the current data.
 *
 * @full => boolean to indicate if you want the entire form or only the differences
 */
saltos.app.get_data = full => {
    saltos.app.__form.data = {};
    var types = ['text', 'color', 'date', 'time', 'datetime-local', 'hidden',
                 'textarea', 'checkbox', 'password', 'file', 'select-one'];
    for (var i in saltos.app.__form.fields) {
        var field = saltos.app.__form.fields[i];
        // This trick allow to ignore fields used only for presentation purposes
        if (field.hasOwnProperty('ignore') && field.ignore == 'true') {
            continue;
        }
        var obj = document.getElementById(field.id);
        if (obj) {
            if (types.includes(obj.type)) {
                var val = obj.value;
                var old = field.value.toString();
                if (obj.type == 'textarea') {
                    val = val.replace(/\r\n|\r/g, '\n');
                    old = old.replace(/\r\n|\r/g, '\n');
                } else if (field.type == 'integer') {
                    val = parseInt(val);
                    old = parseInt(old);
                    if (isNaN(val)) {
                        val = 0;
                    }
                    if (isNaN(old)) {
                        old = 0;
                    }
                } else if (field.type == 'float') {
                    val = parseFloat(val);
                    old = parseFloat(old);
                    if (isNaN(val)) {
                        val = 0;
                    }
                    if (isNaN(old)) {
                        old = 0;
                    }
                }
                if (val != old || full) {
                    saltos.app.__form.data[field.id] = val;
                }
            }
        }
    }
    // This thick allow to add the id field of the template used
    saltos.app.__form.data = saltos.app.__get_data_ids_helper(saltos.app.__form.data);
    // This trick allow to do more pretty the structure of some composed fields
    saltos.app.__form.data = saltos.app.parse_data(saltos.app.__form.data);
    return saltos.app.__form.data;
};

/**
 * Get data ids helper
 *
 * This function allow to retrieve the needed ids in the fields used as template
 *
 * @data => the contents of the object with data
 *
 * Notes:
 *
 * The main idea of this function is to add the id fields if it is needed, for example
 * if some field of the template is modified and their value contains a different value
 * that void.
 */
saltos.app.__get_data_ids_helper = data => {
    for (var key in data) {
        var id = key.split('.');
        if (id.length == 3 && id[2] != 'id') {
            id[2] = 'id';
            id = id.join('.');
            if (!data.hasOwnProperty(id)) {
                var obj = document.getElementById(id);
                if (obj) {
                    var val = obj.value;
                    if (val != '') {
                        data[id] = val;
                    }
                }
            }
        }
    }
    return data;
};

/**
 * Parse data
 *
 * This function allow to join in an object the values that share the same prefix part of
 * the key, for example, if you have an object with two ventries (a.a and a.b), then the
 * resulted value will be an object a with two entries (a and b).
 *
 * @data => the contents of the object with data
 *
 * Notes:
 *
 * This function is used to allow the specification of multiples parameters, for example,
 * in the navbar widget where the widget expects an object for the brand configuration, too
 * is used by get_data to separate in a more pretty structure some fields as the details used
 * in the invoices.
 */
saltos.app.parse_data = data => {
    for (var key in data) {
        var id = key.split('.');
        if (id.length == 2) {
            var id0 = id[0];
            var id1 = id[1];
            var val = data[key];
            if (!data.hasOwnProperty(id0)) {
                data[id0] = {};
            }
            data[id0][id1] = val;
            delete data[key];
        }
        if (id.length == 3) {
            var id0 = id[0];
            var id1 = id[1];
            var id2 = id[2];
            var val = data[key];
            if (!data.hasOwnProperty(id0)) {
                data[id0] = {};
            }
            if (!data[id0].hasOwnProperty(id1)) {
                data[id0][id1] = {};
            }
            data[id0][id1][id2] = val;
            delete data[key];
        }
    }
    return data;
};

/**
 * Checkbox ids
 *
 * Retrieve the selected checkboxes contained in an obj, usefull for the checkboxes
 * that appear in the bootstrap table widget.
 *
 * @obj => the object that contains the checkboxes, generally the table widget
 */
saltos.app.checkbox_ids = obj => {
    var nodes = obj.querySelectorAll('input[type=checkbox]:checked[value]');
    var values = [];
    for (var i = 0; i < nodes.length; i++) {
        values.push(nodes[i].value);
    }
    return values;
};

/**
 * Check form
 *
 * This function tries to check if all required fields contain data, if the required field are
 * right, the is-valid class will be applied to all required elements and true is returned,
 * otherwise the is-invalid class will be added to the void required elements and false is
 * returned.
 */
saltos.app.check_required = () => {
    var obj = null;
    document.querySelectorAll('[required]').forEach(_this => {
        _this.classList.remove('is-valid');
        _this.classList.remove('is-invalid');
        _this.classList.remove('border');
        _this.classList.forEach(_this2 => {
            if (_this2.substr(0, 7) == 'border-') {
                _this.classList.remove(_this2);
            }
        });
        if (_this.value == '') {
            _this.classList.add('is-invalid');
            if (!obj) {
                obj = _this;
            }
        } else {
            _this.classList.add('is-valid');
        }
    });
    if (obj) {
        obj.focus();
        return false;
    }
    return true;
};

/**
 * Form disabled
 *
 * This function disables all elements of the form, it is intended to be used when you need
 * to do screen for view mode.
 */
saltos.app.form_disabled = bool => {
    saltos.app.__form_helper('disabled', bool);
};

/**
 * Form readonly
 *
 * This function set all elements of the form as readonly, it is intended to be used when you need
 * to do screen for view mode.
 */
saltos.app.form_readonly = bool => {
    saltos.app.__form_helper('readonly', bool);
};

/**
 * Form helper
 *
 * This function is a helper used by the form_disabled and form_readonly functions
 */
saltos.app.__form_helper = (attr, bool) => {
    var types = ['text', 'color', 'date', 'time', 'datetime-local', 'hidden',
                 'textarea', 'checkbox', 'password', 'file', 'select-one'];
    for (var i in saltos.app.__form.fields) {
        var field = saltos.app.__form.fields[i];
        var obj = document.getElementById(field.id);
        if (obj) {
            if (types.includes(obj.type)) {
                if (bool) {
                    obj.setAttribute(attr, '');
                } else {
                    obj.removeAttribute(attr);
                }
            }
        }
    }
};

/**
 * ParentNode Search helper
 *
 * This function helps the user interface to search for parentNodes that can be identified
 * by some class in the classList, it is intended to prevent the call of the parentNode in
 * locations where sometimes can contains a different structure, for example, when you want
 * to get the col div that contains a button with and without labels, depending on the
 * usage of the label, the component can contains a different structure and you may need
 * more or less parentNode calls, thanks to this function, the calls can be automated
 * returning the correct object of the structure with independence of the source of the
 * search.
 *
 * @obj    => the initial obj where do you want to do the search
 * @search => the class name that the parentNode destination must contains
 *
 * Notes:
 *
 * As you can see, this search can be performed only by 100 times to prevent infinites loop
 * in case of not found the search pattern.
 */
saltos.app.parentNode_search = (obj, search) => {
    for (var i = 0; i < 100; i++) {
        obj = obj.parentNode;
        if (obj.classList.contains(search)) {
            break;
        }
    }
    return obj;
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
    if (saltos.token.get()) {
        saltos.authenticate.checktoken();
    }
    if (!saltos.token.get()) {
        saltos.app.send_request('app/login');
        return;
    }
    // Hash part
    if (['', 'app/login'].includes(saltos.hash.get())) {
        saltos.hash.set('app/dashboard');
    }
    saltos.hash.trigger();
})();
