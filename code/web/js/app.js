
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
 * Modal function
 *
 * This function tries to implement a modal box, the main difference between the tipical alert
 * is that this alert allow to you to specify the title and a more complex message, but it only
 * shows one button to close it.
 *
 * @title   => title of the modal dialog
 * @message => message of the modal dialog
 * @extra   => object with array of buttons and color
 */
saltos.app.modal = (title, message, extra) => {
    if (typeof extra == 'undefined') {
        var extra = {};
    }
    if (!extra.hasOwnProperty('buttons')) {
        extra.buttons = [{
            label: 'Close',
            color: 'primary',
            icon: 'x-lg',
            autofocus: true,
            onclick: () => {},
        }];
    }
    if (!extra.hasOwnProperty('color')) {
        extra.color = 'primary';
    }
    return saltos.gettext.bootstrap.modal({
        title: title,
        close: 'Close',
        body: message,
        footer: (() => {
            var obj = saltos.core.html('<div></div>');
            for (var key in extra.buttons) {
                (button => {
                    saltos.core.check_params(button, ['label', 'class',
                        'color', 'icon', 'autofocus', 'onclick']);
                    obj.append(saltos.gettext.bootstrap.field({
                        type: 'button',
                        label: button.label,
                        class: `${button.class} ms-1`,
                        color: button.color,
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
    return saltos.gettext.bootstrap.toast({
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
        return;
    }
    if (!saltos.app.modal('Error ' + error.code, error.text, {color: 'danger'})) {
        saltos.app.toast('Error ' + error.code, error.text, {color: 'danger'});
    }
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
saltos.app.send_request = hash => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?' + hash,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            saltos.app.process_response(response);
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
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
        // This is to prevent some attr that causes problems here
        if (['id', 'default', 'check'].includes(key)) {
            continue;
        }
        // Continue
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
    loading: 0,
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__form_backup = {
    fields: [],
    templates: {},
    do: () => {
        saltos.app.__form_backup.fields = saltos.app.__form.fields;
        saltos.app.__form_backup.templates = saltos.app.__form.templates;
    },
    restore: () => {
        saltos.app.__form.fields = saltos.app.__form_backup.fields;
        saltos.app.__form.templates = saltos.app.__form_backup.templates;
    },
};

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document, too as bonus
 * extra, it tries to search the field spec in the array to update the value of the field spec to
 * allow that the get_data can differ between the original data and the modified data.
 */
saltos.app.form.data = data => {
    // Check that data is found
    if (data === null) {
        return;
    }
    // Check for attr template_id
    if (data.hasOwnProperty('#attr') && data['#attr'].hasOwnProperty('template_id')) {
        var template_id = data['#attr'].template_id;
        if (!Array.isArray(data.value)) {
            throw new Error(`data for template ${template_id} is not an array of rows`);
        }
        for (var key in data.value) {
            var val = data.value[key];
            if (parseInt(key)) {
                var temp1 = saltos.app.form.__layout_template_helper(template_id, key);
                var temp2 = saltos.app.form.layout(temp1, 'div');
                var temp3 = document.getElementById(template_id + '.' + (key - 1));
                temp3.after(temp2);
            }
            saltos.app.form.data(saltos.app.form.__data_template_helper(template_id, val, key));
        }
        return;
    }
    // Check for the correctness of the data
    if (Array.isArray(data)) {
        throw new Error(`data is an array instead of an object of key and val pairs`);
    }
    // Continue with the normal behaviour
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
    template['#attr'].id = template_id + '.' + index;
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
 * and all form_field defined in the bootstrap file.
 *
 * Each objects as container, rows and cols, have two modes of works:
 *
 * 1) normal mode => requires that the user specify all layout, container, row, col and fields.
 *
 * 2) auto mode => only requires set auto='true' to the container, row or col, and with this, all childrens
 * of the node are created inside the correct element, if the auto appear in the container then a container,
 * a row and one col for each field are created, if the auto appear in the row then a row with one col for
 * each field are created, if the auto appear in the col then one col for each field are created, this mode
 * allow to optimize and speedup the creation of each screen by allow to reduce the amount of xml needed
 * to define each screen but allowing to be a lot of specific and define from all to zero auto features.
 *
 * Notes:
 *
 * This function add the fields to the saltos.app.__form.fields, this allow to the saltos.app.get_data
 * can retrieve the desired information of the fields.
 */
saltos.app.form.layout = (layout, extra) => {
    if (typeof extra == 'undefined') {
        saltos.app.__form.fields = [];
        saltos.app.__form.templates = {};
    }
    // This code fix a problem when layout contains the append element id
    var append = '';
    if (saltos.core.is_attr_value(layout) && layout['#attr'].hasOwnProperty('append')) {
        append = layout['#attr'].append;
        layout = layout.value;
    }
    // This code fix a problem when layout contains the content of a template
    if (saltos.core.is_attr_value(layout)) {
        layout = {[layout['#attr'].type]: layout};
    }
    // Continue
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
        // Check for template_id attr
        if (attr.hasOwnProperty('template_id')) {
            // Store it in the templates container
            var template_id = attr.template_id;
            delete val['#attr'].template_id;
            saltos.app.__form.templates[template_id] = val;
            // Modify the id of the first elements to convert it to the format TEMPLATE_ID#ID#0
            // Note: the follow line returns a copy of the object!!!
            val = saltos.app.form.__layout_template_helper(template_id, 0);
        }
        // Continue with original idea of use an entire specified layout
        if (
            ['container', 'col', 'row'].includes(key) &&
            attr.hasOwnProperty('auto') && saltos.core.eval_bool(attr.auto)
        ) {
            val = saltos.app.form.__layout_auto_helper[key](val);
            var temp = saltos.app.form.layout(val, 'arr');
            for (var i in temp) {
                arr.push(temp[i]);
            }
        } else if (['container', 'col', 'row', 'div'].includes(key)) {
            var obj = saltos.gettext.bootstrap.field(attr);
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
                var obj = saltos.gettext.bootstrap.field({
                    type: 'placeholder',
                    id: attr.id,
                });
                saltos.app.__source_helper(attr);
            } else {
                var obj = saltos.gettext.bootstrap.field(attr);
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
    var obj = document.body;
    if (append) {
        var temp = append.split(',');
        for (var i in temp) {
            obj = document.getElementById(temp[i]);
            if (obj) {
                break;
            }
        }
        if (!obj) {
            throw new Error(`append ${append} not found`);
        }
        obj.innerHTML = '';
    }
    obj.append(div);
    obj.querySelectorAll('[autofocus]').forEach(_this => {
        _this.focus();
    });
};

/**
 * Form layout auto helper object
 *
 * This object stores the functions used as layout_auto_helper for containers, rows and cols
 */
saltos.app.form.__layout_auto_helper = {};

/**
 * Form layout auto helper for containers
 *
 * This functions implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @id              => defines the id used by the container element
 * @container_class => defines the class used by the container element
 * @container_style => defines the style used by the container element
 * @row_class       => defines the class used by the row element
 * @row_style       => defines the style used by the row element
 * @col_class       => defines the class used by the col element
 * @col_style       => defines the style used by the col element
 */
saltos.app.form.__layout_auto_helper.container = layout => {
    var attr = layout['#attr'];
    saltos.core.check_params(attr, ['id', 'container_class', 'container_style',
        'row_class', 'row_style', 'col_class', 'col_style']);
    // Store and delete to prevent the id propagation to the next childrens
    var id = layout['#attr'].id;
    delete layout['#attr'].id;
    var temp = saltos.app.form.__layout_auto_helper.row(layout);
    // This is the new layout object created with one container and the row inside
    layout = {
        container: {
            'value': {},
            '#attr': {
                id: id,
                class: attr.container_class,
                style: attr.container_style,
            }
        }
    };
    for (var i in temp) {
        layout.container.value[i] = temp[i];
    }
    return layout;
};

/**
 * Form layout auto helper for rows
 *
 * This functions implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @id              => defines the id used by the container element
 * @row_class       => defines the class used by the row element
 * @row_style       => defines the style used by the row element
 * @col_class       => defines the class used by the col element
 * @col_style       => defines the style used by the col element
 */
saltos.app.form.__layout_auto_helper.row = layout => {
    var attr = layout['#attr'];
    saltos.core.check_params(attr, ['id', 'row_class', 'row_style', 'col_class', 'col_style']);
    // Store and delete to prevent the id propagation to the next childrens
    var id = layout['#attr'].id;
    delete layout['#attr'].id;
    var temp = saltos.app.form.__layout_auto_helper.col(layout);
    // This is the new layout object created with one row and the cols inside
    layout = {
        row: {
            'value': {},
            '#attr': {
                id: id,
                class: attr.row_class,
                style: attr.row_style,
            }
        }
    };
    for (var i in temp) {
        layout.row.value[i] = temp[i];
    }
    return layout;
};

/**
 * Form layout auto helper for cols
 *
 * This functions implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @col_class       => defines the class used by the col element
 * @col_style       => defines the style used by the col element
 *
 * Notes:
 *
 * This function not allow to specify the id because in this functions, each object of the
 * layout is embedded inside of a col object and is not suitable to put an id attribute
 * to each col without repetitions.
 */
saltos.app.form.__layout_auto_helper.col = layout => {
    var attr = layout['#attr'];
    var value = layout.value;
    saltos.core.check_params(attr, ['col_class', 'col_style']);
    // This trick convert all entries of the object in an array with the keys and values
    var temp = [];
    for (var key in value) {
        temp.push([key, value[key]]);
    }
    // This is the new layout object created with one cols by each original field
    layout = {};
    var numcol = 0;
    while (temp.length) {
        var item = temp.shift();
        var col_class = attr.col_class;
        var col_style = attr.col_style;
        if (saltos.core.is_attr_value(item[1])) {
            if (item[1]['#attr'].hasOwnProperty('col_class')) {
                col_class = item[1]['#attr'].col_class;
            }
            if (item[1]['#attr'].hasOwnProperty('col_style')) {
                col_style = item[1]['#attr'].col_style;
            }
        }
        // This trick allow to hide the hidden fields
        if (saltos.core.fix_key(item[0]) == 'hidden') {
            col_class = 'col d-none';
            col_style = '';
        }
        layout['col#' + numcol] = {
            'value': {},
            '#attr': {
                class: col_class,
                style: col_style,
            }
        };
        layout['col#' + numcol].value[item[0]] = item[1];
        numcol++;
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
 *
 * @data => the object that contains the styles requirements (can be file or inline)
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
 *
 * @data => the object that contains the javascript requirements (can be file or inline)
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
 * This function sets the document title, too it checks the existence of the About
 * header received in the ajax calls and stored in the saltos object to be used in the
 * last part of the title.
 *
 * @title => The title that you want to set in the page
 */
saltos.app.form.title = title => {
    if (!saltos.core.hasOwnProperty('about')) {
        document.title = T(title);
        return;
    }
    document.title = T(title) + ' - ' + saltos.core.about;
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
    switch (action) {
        case 'loading':
            saltos.app.__form.loading++;
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
        case 'unloading':
            saltos.app.__form.loading--;
            if (saltos.app.__form.loading < 0) {
                saltos.app.__form.loading = 0;
            }
            if (saltos.app.__form.loading > 0) {
                return false;
            }
            var obj = document.getElementById('loading');
            if (!obj) {
                return false;
            }
            obj.remove();
            return true;
        case 'clear':
            document.body.innerHTML = '';
            document.body.removeAttribute('screen');
            return true;
    }
    if (saltos.driver.hasOwnProperty('__types')) {
        if (saltos.driver.__types.hasOwnProperty(action)) {
            if (document.body.hasAttribute('screen')) {
                return false;
            }
            document.body.innerHTML = '';
            document.body.append(saltos.driver.__types[action].template());
            document.body.setAttribute('screen', action);
            return true;
        }
    }
    throw new Error(`action ${action} not found`);
};

/**
 * Form navbar helper
 *
 * This function create a navbar with their menus on the top of the page, more
 * info about this feature in the bootstrap documentation
 *
 * @navbar
 */
saltos.app.form.navbar = navbar => {
    navbar['#attr'] = saltos.app.parse_data(navbar['#attr']);
    navbar = saltos.core.join_attr_value(navbar);
    if (document.getElementById(navbar.id)) {
        return;
    }
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
                    // Trick to allow to put attr in the value node intended to use the eval=true
                    var temp = ['name', 'id', 'icon', 'disabled', 'active', 'onclick', 'dropdown_menu_end'];
                    for (var i in temp) {
                        var j = temp[i];
                        if (val2.value.hasOwnProperty(j) && !val2['#attr'].hasOwnProperty(j)) {
                            val2['#attr'][j] = val2.value[j];
                            delete val2.value[j];
                        }
                    }
                    // Continue
                    if (typeof val2.value == 'string') {
                        menu.push(val2['#attr']);
                    } else if (val2.value.hasOwnProperty('menu')) {
                        var menu2 = [];
                        for (var key3 in val2.value.menu) {
                            var val3 = val2.value.menu[key3];
                            // Trick to allow to put attr in the value node intended to use the eval=true
                            var temp = ['name', 'id', 'icon', 'disabled', 'active', 'onclick', 'divider'];
                            for (var i in temp) {
                                var j = temp[i];
                                if (val3.value.hasOwnProperty(j) && !val3['#attr'].hasOwnProperty(j)) {
                                    val3['#attr'][j] = val3.value[j];
                                    delete val3.value[j];
                                }
                            }
                            // Continue
                            menu2.push(val3['#attr']);
                        }
                        menu.push({
                            ...val2['#attr'],
                            menu: menu2,
                        });
                    }
                }
                navbar.items[key] = saltos.gettext.bootstrap.menu({
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
                    obj.append(saltos.gettext.bootstrap.field(val2['#attr']));
                }
                navbar.items[key] = obj;
            }
        }
    }
    var obj = saltos.bootstrap.navbar(navbar);
    var obj2 = document.body;
    if (navbar.append) {
        obj2 = document.getElementById(navbar.append);
        if (!obj2) {
            throw new Error(`append ${navbar.append} not found`);
        }
    }
    obj2.append(obj);
};

/**
 * Load gettext helper
 *
 * This function load the gettext data from the api to the cli sapi
 */
saltos.app.form.gettext = array => {
    saltos.gettext.cache = array;
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
        saltos.app.form.screen('loading');
        saltos.core.ajax({
            url: 'api/?' + field.source,
            success: response => {
                saltos.app.form.screen('unloading');
                if (!saltos.app.check_response(response)) {
                    return;
                }
                field.source = '';
                delete response.id;
                for (var key in response) {
                    field[key] = response[key];
                }
                var obj = document.getElementById(field.id);
                obj.replaceWith(saltos.gettext.bootstrap.field(field));
            },
            error: request => {
                saltos.app.form.screen('unloading');
                saltos.app.show_error({
                    text: request.statusText,
                    code: request.status,
                });
            },
            token: saltos.token.get(),
            lang: saltos.gettext.get(),
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
                switch (obj.type) {
                    case 'textarea':
                        val = val.replace(/\r\n|\r/g, '\n');
                        old = old.replace(/\r\n|\r/g, '\n');
                        break;
                    case 'file':
                        val = obj.saltos_data;
                        old = [];
                        break;
                }
                switch (field.type) {
                    case 'integer':
                        val = parseInt(val);
                        old = parseInt(old);
                        if (isNaN(val)) {
                            val = 0;
                        }
                        if (isNaN(old)) {
                            old = 0;
                        }
                        break;
                    case 'float':
                        val = parseFloat(val);
                        old = parseFloat(old);
                        if (isNaN(val)) {
                            val = 0;
                        }
                        if (isNaN(old)) {
                            old = 0;
                        }
                        break;
                }
                if (typeof val == 'object' && typeof old == 'object') {
                    if (JSON.stringify(val) != JSON.stringify(old) || full) {
                        saltos.app.__form.data[field.id] = val;
                    }
                } else {
                    if (val != old || full) {
                        saltos.app.__form.data[field.id] = val;
                    }
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
 * Retrieve the selected checkboxes contained in an obj, useful for the checkboxes
 * that appear in the bootstrap table widget.
 *
 * @obj => the object that contains the checkboxes, generally the table widget
 */
saltos.app.checkbox_ids = obj => {
    var values = [];
    obj.querySelectorAll('input[type=checkbox]:checked[value]').forEach(_this => {
        values.push(_this.value);
    });
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
        var value = _this.value;
        var obj_color = _this;
        var obj_focus = _this;
        // to detect the value of the tags fields
        if (_this.type == 'text' && _this.id.substr(-5) == '_tags' && _this.classList.contains('last')) {
            var id2 = _this.id.substr(0, _this.id.length - 5);
            var obj2 = document.getElementById(id2);
            if (obj2 && obj2.type == 'hidden' && obj2.classList.contains('first')) {
                value = obj2.value;
            }
        }
        // to detect the color and focus of the ckeditor fields
        if (_this.type == 'textarea' && _this.hasOwnProperty('ckeditor')) {
            obj_color = _this.nextElementSibling;
            obj_focus = _this.ckeditor;
        }
        // to detect the color and focus of the codemirror fields
        if (_this.type == 'textarea' && _this.hasOwnProperty('codemirror')) {
            obj_color = _this.nextElementSibling;
            obj_focus = _this.codemirror;
        }
        // to detect the value of the file fields
        if (_this.type == 'file') {
            value = _this.saltos_data.length;
        }
        // continue;
        obj_color.classList.remove('is-valid');
        obj_color.classList.remove('is-invalid');
        obj_color.classList.remove('border');
        obj_color.classList.forEach(_this2 => {
            if (_this2.substr(0, 7) == 'border-') {
                obj_color.classList.remove(_this2);
            }
        });
        if (value == '') {
            if (obj_color === obj_focus) {
                obj_color.classList.add('is-invalid');
            } else {
                obj_color.classList.add('border');
                obj_color.classList.add('border-danger');
            }
            if (!obj) {
                obj = obj_focus;
            }
        } else {
            if (obj_color === obj_focus) {
                obj_color.classList.add('is-valid');
            } else {
                obj_color.classList.add('border');
                obj_color.classList.add('border-success');
            }
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
 * Set bs theme
 *
 * This function sets the bs theme
 *
 * @theme => Can be auto, light or dark
 *
 * Notes:
 *
 * This function is an immersion to the same bootstrap function, and it's intended
 * to be used and update the navbar menu buttons
 */
saltos.app.set_bs_theme = theme => {
    saltos.core.when_visible('bs_theme', () => {
        var button = document.getElementById('bs_theme');
        button.parentElement.querySelectorAll('.active').forEach(_this => {
            _this.classList.remove('active');
        });
        var active = document.getElementById(`bs_theme_${theme}`);
        active.classList.add('active');
        button.innerHTML = active.querySelector('i').outerHTML;
    });
    saltos.bootstrap.set_bs_theme(theme);
};

/**
 * Set css theme
 *
 * This function sets the css theme
 *
 * @theme => Can be default or one of the bootswatch themes
 *
 * Notes:
 *
 * This function is an immersion to the same bootstrap function, and it's intended
 * to be used and update the navbar menu buttons
 */
saltos.app.set_css_theme = theme => {
    saltos.core.when_visible('css_theme', () => {
        var button = document.getElementById('css_theme');
        button.parentElement.querySelectorAll('.active').forEach(_this => {
            _this.classList.remove('active');
        });
        var active = document.getElementById(`css_theme_${theme}`);
        active.classList.add('active');
        button.innerHTML = active.innerHTML;
    });
    saltos.bootstrap.set_css_theme(theme);
};

/**
 * Set locale
 *
 * This function sets the locale
 *
 * @lang => Can be one of the valid locales
 */
saltos.app.set_locale = lang => {
    saltos.core.when_visible('locale', () => {
        var button = document.getElementById('locale');
        button.parentElement.querySelectorAll('.active').forEach(_this => {
            _this.classList.remove('active');
        });
        var temp = saltos.core.encode_bad_chars(lang);
        var active = document.getElementById(`locale_${temp}`);
        active.classList.add('active');
        button.innerHTML = active.innerHTML;
    });
    saltos.gettext.set(lang);
    saltos.hash.trigger();
};

/**
 * Main code
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
(() => {
    // Theme part
    if (!saltos.bootstrap.get_bs_theme()) {
        saltos.app.set_bs_theme('auto');
    } else {
        saltos.app.set_bs_theme(saltos.bootstrap.get_bs_theme());
    }
    if (!saltos.bootstrap.get_css_theme()) {
        saltos.app.set_css_theme('default');
    } else {
        saltos.app.set_css_theme(saltos.bootstrap.get_css_theme());
    }
    // Lang part
    if (!saltos.gettext.get()) {
        saltos.app.set_locale(navigator.language || navigator.systemLanguage);
    } else {
        saltos.app.set_locale(saltos.gettext.get());
    }
    // Token part
    if (saltos.token.get()) {
        saltos.authenticate.checktoken();
    }
    // Hash part
    saltos.hash.trigger();
})();
