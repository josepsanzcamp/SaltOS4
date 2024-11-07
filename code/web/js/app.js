
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
    if (extra === undefined) {
        extra = {};
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
            const obj = saltos.core.html('<div></div>');
            for (const key in extra.buttons) {
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
    if (extra === undefined) {
        extra = {};
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
    if (!saltos.app.modal('Error', error.text, {color: 'danger'})) {
        saltos.app.toast('Error', error.text, {color: 'danger'});
    }
    const red = 'color:white;background:red';
    const reset = 'color:inherit;background:inherit';
    const array = [`%c${error.code}%c ${error.text}`, red, reset];
    console.log(...array);
};

/**
 * Check response helper
 *
 * This function is intended to process the response received by ajax requests and returns
 * if an error is detected in the response.
 */
saltos.app.check_response = response => {
    if (typeof response != 'object') {
        saltos.app.show_error(response);
        return false;
    }
    let bool = true;
    if (response.hasOwnProperty('error') && typeof response.error == 'object') {
        saltos.app.show_error(response.error);
        bool = false;
    }
    if (response.hasOwnProperty('logout') && response.logout) {
        saltos.app.send_request('app/login');
        bool = false;
    }
    return bool;
};

/**
 * Send request helper
 *
 * This function allow to send requests to the server and process the response
 */
saltos.app.send_request = hash => {
    saltos.app.ajax({
        url: hash,
        success: response => {
            saltos.app.process_response(response);
        },
    });
};

/**
 * Process response helper
 *
 * This function process the responses received by the send request
 */
saltos.app.process_response = async response => {
    for (let key in response) {
        const val = response[key];
        key = saltos.core.fix_key(key);
        if (typeof saltos.app.form[key] != 'function') {
            throw new Error(`Response type ${key} not found`);
        }
        if (saltos.app.form[key].constructor.name == 'AsyncFunction') {
            await saltos.app.form[key](val);
        } else {
            saltos.app.form[key](val);
        }
    }
};

/**
 * Form constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each
 * helper
 */
saltos.app.form = {};

/**
 * Data helper object
 *
 * This object allow to the app to store the data of the fields map
 */
saltos.app.__form = {
    fields: [],
    templates: {},
    loading: 0,
    timer: null,
};

/**
 * Form backup helper
 *
 * This object stores the needed structure to allocate the forms backups with all their
 * data and the functions needed to do and restore the backups to the main __form object.
 *
 * @do      => this action performs a backup using the specified key
 * @restore => this action performs the restoration action using the specified key, if the
 *             key is not found, then empty fields and templates are used for the restoration.
 *
 * Notes:
 *
 * This object is intended to store more forms that one, usefull when driver uses the same
 * screen to allocate lists and forms that contains fields for the search engine or fields
 * for the create or edit features.
 *
 * The restore action is able to understand some expressions like comma and plus:
 * @ two,one => this example is intended to restore the two context if it is found, otherwise
 *   tries to restore the one context, otherwise a void context is set.
 * @ top+one => this example is intended to restore two contexts in one context, intender to
 *   load the context of the search list that can be contained in the top and one containers.
 */
saltos.app.__backup = {
    __forms: {},

    save: key => {
        saltos.app.__backup.__forms[key] = {};
        saltos.app.__backup.__forms[key].fields = saltos.app.__form.fields;
        saltos.app.__backup.__forms[key].templates = saltos.app.__form.templates;
    },

    restore: key => {
        saltos.app.__form.fields = [];
        saltos.app.__form.templates = {};
        key = saltos.app.__comma_plus_parser_helper(key);
        for (const i in key) {
            saltos.app.__form.fields = [
                ...saltos.app.__form.fields,
                ...saltos.app.__backup.__forms[key[i]].fields
            ];
            saltos.app.__form.templates = {
                ...saltos.app.__form.templates,
                ...saltos.app.__backup.__forms[key[i]].templates
            };
        }
        return key.length > 0;
    },
};

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document, too as bonus
 * extra, it tries to search the field spec in the array to update the value of the field spec to
 * allow that the get_data can differ between the original data and the modified data.
 */
saltos.app.form.data = (data, sync = true) => {
    // Check that data is found
    if (data === null) {
        return;
    }
    // Check for attr template_id
    if (data.hasOwnProperty('#attr') && data['#attr'].hasOwnProperty('template_id')) {
        const template_id = data['#attr'].template_id;
        if (!Array.isArray(data.value)) {
            throw new Error(`Data for template ${template_id} is not an array of rows`);
        }
        for (const key in data.value) {
            const val = data.value[key];
            if (parseInt(key)) {
                const temp1 = saltos.app.form.__layout_template_helper(template_id, key);
                const temp2 = saltos.app.form.layout(temp1, 'div');
                const temp3 = document.getElementById(template_id + '.' + (key - 1));
                temp3.after(temp2);
            }
            saltos.app.form.data(saltos.app.form.__data_template_helper(template_id, val, key));
        }
        return;
    }
    // Check for the correctness of the data
    if (Array.isArray(data)) {
        throw new Error(`Data is an array instead of an object of key and val pairs`);
    }
    // Continue with the normal behaviour
    for (const key in data) {
        let val = data[key];
        if (val === null) {
            val = '';
        }
        // This updates the object
        const obj = document.getElementById(key);
        if (!obj) {
            continue;
        }
        // Check to prevent objects in value
        if (typeof val != 'object') {
            obj.value = val;
        }
        // Special case for iframes
        if (obj.hasAttribute('src')) {
            obj.src = val;
        }
        if (obj.hasAttribute('srcdoc')) {
            obj.srcdoc = val;
        }
        // Special case for widgets with set
        if (obj.hasOwnProperty('set') && typeof obj.set == 'function') {
            obj.set(val);
        }
        if (!sync) {
            continue;
        }
        // This updates the field spec searching in all backups
        for (const i in saltos.app.__backup.__forms) {
            saltos.app.__backup.__forms[i].fields.forEach(_this => {
                if (_this.id == key) {
                    _this.value = val;
                }
            });
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
    for (const key in data) {
        const val = data[key];
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
    const template = saltos.core.copy_object(saltos.app.__form.templates[template_id]);
    template['#attr'].id = template_id + '.' + index;
    for (const key in template.value) {
        const val = template.value[key];
        if (val['#attr'].hasOwnProperty('id')) {
            const id = val['#attr'].id;
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
    if (extra === undefined) {
        saltos.app.__form.fields = [];
        saltos.app.__form.templates = {};
    }
    // This code fix a problem when layout contains the append element
    let append = '';
    if (saltos.core.is_attr_value(layout) && layout['#attr'].hasOwnProperty('append')) {
        append = layout['#attr'].append;
        layout = layout.value;
        const temp = append.split(',');
        let obj = null;
        for (const i in temp) {
            obj = document.getElementById(temp[i]);
            if (obj) {
                append = temp[i];
                break;
            }
        }
        if (!obj) {
            throw new Error(`Layout append ${append} not found`);
        }
    }
    // This code fix a problem when layout contains the content of a template
    if (saltos.core.is_attr_value(layout)) {
        layout = {[layout['#attr'].type]: layout};
    }
    // Continue
    let arr = [];
    for (let key in layout) {
        let val = layout[key];
        key = saltos.core.fix_key(key);
        let attr = {};
        let value = val;
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
            const template_id = attr.template_id;
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
            const temp = saltos.app.form.layout(val, 'arr');
            for (const i in temp) {
                arr.push(temp[i]);
            }
        } else if (['container', 'col', 'row', 'div'].includes(key)) {
            const obj = saltos.gettext.bootstrap.field(attr);
            const temp = saltos.app.form.layout(value, 'arr');
            for (const i in temp) {
                obj.append(temp[i]);
            }
            arr.push(obj);
        } else {
            if (typeof value == 'object') {
                for (const key2 in value) {
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
            let obj = null;
            if (attr.source != '') {
                obj = saltos.app.__source_helper(attr);
            } else {
                obj = saltos.gettext.bootstrap.field(attr);
            }
            arr.push(obj);
        }
    }
    // Some extra features to allow that returns only the array
    if (extra == 'arr') {
        return arr;
    }
    let div = saltos.core.html('<div></div>');
    for (const i in arr) {
        div.append(arr[i]);
    }
    div = saltos.core.optimize(div);
    // Some extra features to allow that returns only the div
    if (extra == 'div') {
        return div;
    }
    // Defaut feature that add the div to the body's document
    let obj = null;
    if (append != '') {
        // Do a backup of the fields and templates using the append key
        saltos.app.__backup.save(append);
        // Continue
        obj = document.getElementById(append);
        obj.replaceChildren(div);
    } else {
        obj = document.body;
        document.body.append(div);
    }
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
    const attr = layout['#attr'];
    saltos.core.check_params(attr, ['id', 'container_class', 'container_style',
        'row_class', 'row_style', 'col_class', 'col_style']);
    // Store and delete to prevent the id propagation to the next childrens
    const id = layout['#attr'].id;
    delete layout['#attr'].id;
    const temp = saltos.app.form.__layout_auto_helper.row(layout);
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
    for (const i in temp) {
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
    const attr = layout['#attr'];
    saltos.core.check_params(attr, ['id', 'row_class', 'row_style', 'col_class', 'col_style']);
    // Store and delete to prevent the id propagation to the next childrens
    const id = layout['#attr'].id;
    delete layout['#attr'].id;
    const temp = saltos.app.form.__layout_auto_helper.col(layout);
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
    for (const i in temp) {
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
    const attr = layout['#attr'];
    const value = layout.value;
    saltos.core.check_params(attr, ['col_class', 'col_style']);
    // This trick convert all entries of the object in an array with the keys and values
    const temp = [];
    for (const key in value) {
        temp.push([key, value[key]]);
    }
    // This is the new layout object created with one cols by each original field
    layout = {};
    let numcol = 0;
    while (temp.length) {
        const item = temp.shift();
        let col_class = attr.col_class;
        let col_style = attr.col_style;
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
 * @data => the object that contains the styles requirements (can be file or inline)
 */
saltos.app.form.style = async data => {
    for (let key in data) {
        const val = data[key];
        key = saltos.core.fix_key(key);
        if (key == 'inline') {
            const style = document.createElement('style');
            style.innerHTML = val;
            document.head.append(style);
        }
        if (key == 'file') {
            try {
                const response = await fetch(val);
                if (!response.ok) {
                    throw new Error(`${response.status} ${response.statusText} loading ${val}`);
                }
                const data = await response.text();
                const style = document.createElement('style');
                style.innerHTML = data;
                document.head.append(style);
            } catch (error) {
                throw new Error(`${error.name} ${error.message} loading ${val}`);
            }
        }
    }
};

/**
 * Form javascript helper
 *
 * This function allow to specify scripts, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 *
 * @data => the object that contains the javascript requirements (can be file or inline)
 */
saltos.app.form.javascript = async data => {
    for (let key in data) {
        const val = data[key];
        key = saltos.core.fix_key(key);
        if (key == 'inline') {
            const script = document.createElement('script');
            script.innerHTML = val;
            document.head.append(script);
        }
        if (key == 'file') {
            try {
                const response = await fetch(val);
                if (!response.ok) {
                    throw new Error(`${response.status} ${response.statusText} loading ${val}`);
                }
                const data = await response.text();
                const script = document.createElement('script');
                script.innerHTML = data;
                document.head.append(script);
            } catch (error) {
                throw new Error(`${error.name} ${error.message} loading ${val}`);
            }
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
    if (saltos.core.is_attr_value(title)) {
        const attr = title['#attr'];
        if (attr.hasOwnProperty('append')) {
            const append = attr.append.split(',');
            for (const i in append) {
                if (append[i] == 'modal') {
                    // Try for modal
                    const obj = document.querySelector('.modal-title');
                    if (obj) {
                        obj.innerHTML = T(title.value);
                        return;
                    }
                } else if (append[i] == 'offcanvas') {
                    // Try for offcanvas
                    const obj = document.querySelector('.offcanvas-title');
                    if (obj) {
                        obj.innerHTML = T(title.value);
                        return;
                    }
                } else {
                    // Try for element id
                    const obj = document.getElementById(append[i]);
                    if (obj) {
                        obj.innerHTML = T(title.value);
                        return;
                    }
                }
            }
            setTimeout(() => {
                saltos.app.form.title(title);
            }, 1);
            return;
        }
        throw new Error(`Unknown attr`);
    }
    // Continue with default behaviour
    if (saltos.core.hasOwnProperty('about')) {
        document.title = T(title) + ' - ' + saltos.core.about;
        return;
    }
    document.title = T(title);
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
        case 'loading': {
            clearTimeout(saltos.app.__form.timer);
            saltos.app.__form.loading++;
            const obj = document.getElementById('loading');
            if (obj) {
                return false;
            }
            document.body.append(saltos.core.html(`
                <div id="loading">
                    <div class="modal-backdrop show" style="z-index:202"></div>
                    <div class="position-fixed top-50 start-50 translate-middle" style="z-index:203">
                        <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;">
                        </div>
                    </div>
                </div>
            `));
            return true;
        }
        case 'unloading': {
            clearTimeout(saltos.app.__form.timer);
            saltos.app.__form.loading--;
            if (saltos.app.__form.loading < 0) {
                saltos.app.__form.loading = 0;
            }
            if (saltos.app.__form.loading > 0) {
                return false;
            }
            const obj = document.getElementById('loading');
            if (!obj) {
                return false;
            }
            // This setTimeout allow to prevent the blinking effect caused by
            // consecutives loadings and unloadings by adding a delay in the
            // real unloading code
            saltos.app.__form.timer = setTimeout(() => {
                obj.remove();
            }, 100);
            return true;
        }
        case 'isloading': {
            const obj = document.getElementById('loading');
            if (obj) {
                return true;
            }
            return false;
        }
        case 'clear':
            const obj = document.getElementById('screen');
            if (!obj) {
                return false;
            }
            obj.remove();
            return true;
    }
    if (saltos.driver.hasOwnProperty('__types')) {
        const only = saltos.hash.get().split('/').at(-1);
        if (only == 'only') {
            action = 'type1';
        }
        if (saltos.driver.__types.hasOwnProperty(action)) {
            const obj = document.getElementById('screen');
            if (obj) {
                return false;
            }
            document.body.append(saltos.driver.__types[action].template());
            return true;
        }
    }
    throw new Error(`Screen action ${action} not found`);
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
    navbar['#attr'] = saltos.app.__get_data_parser_helper(navbar['#attr']);
    navbar = saltos.core.join_attr_value(navbar);
    if (document.getElementById(navbar.id)) {
        return;
    }
    if (navbar.hasOwnProperty('items')) {
        for (const key in navbar.items) {
            let val = navbar.items[key];
            if (saltos.core.fix_key(key) == 'menu') {
                let _class = '';
                const menu = [];
                if (saltos.core.is_attr_value(val)) {
                    if (val['#attr'].hasOwnProperty('class')) {
                        _class = val['#attr'].class;
                    }
                    val = val.value;
                }
                for (const key2 in val) {
                    const val2 = val[key2];
                    // Trick to allow to put attr in the value node intended to use the eval=true
                    { // This curly brackets allow to create a block for the temp const
                        const temp = ['label', 'id', 'icon', 'disabled',
                                      'active', 'onclick', 'dropdown_menu_end'];
                        for (const i in temp) {
                            const j = temp[i];
                            if (val2.value.hasOwnProperty(j) && !val2['#attr'].hasOwnProperty(j)) {
                                val2['#attr'][j] = val2.value[j];
                                delete val2.value[j];
                            }
                        }
                    } // Here the temp const disapear!!!
                    // Continue
                    if (typeof val2.value == 'string') {
                        menu.push(val2['#attr']);
                    } else if (val2.value.hasOwnProperty('menu')) {
                        const menu2 = [];
                        for (const key3 in val2.value.menu) {
                            const val3 = val2.value.menu[key3];
                            // Trick to allow to put attr in the value node intended to use the eval=true
                            const temp = ['label', 'id', 'icon', 'disabled', 'active', 'onclick', 'divider'];
                            for (const i in temp) {
                                const j = temp[i];
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
                let _class = '';
                if (saltos.core.is_attr_value(val)) {
                    if (val['#attr'].hasOwnProperty('class')) {
                        _class = val['#attr'].class;
                    }
                    val = val.value;
                }
                const obj = saltos.core.html(`<form class='${_class}' onsubmit='return false'></form>`);
                for (const key2 in val) {
                    const val2 = val[key2];
                    val2['#attr'].type = saltos.core.fix_key(key2);
                    obj.append(saltos.gettext.bootstrap.field(val2['#attr']));
                }
                navbar.items[key] = obj;
            }
        }
    }
    const obj = saltos.bootstrap.navbar(navbar);
    if (navbar.hasOwnProperty('append')) {
        const obj2 = document.getElementById(navbar.append);
        if (!obj2) {
            throw new Error(`Navbar append ${navbar.append} not found`);
        }
        obj2.replaceChildren(obj);
    } else {
        document.body.append(obj);
    }
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
 * @height => the height used as style.height parameter
 * @label  => this parameter is used as text for the label
 *
 * Notes:
 *
 * At the end of the object replacement, the load event is triggered to the old object to notify
 * that the update was finished.
 */
saltos.app.__source_helper = field => {
    saltos.core.check_params(field, ['id', 'source', 'height', 'label']);
    // Check for asynchronous load using the source param
    saltos.app.ajax({
        url: field.source,
        success: response => {
            field.source = '';
            for (const key in response) {
                field[key] = response[key];
            }
            const obj = document.getElementById(field.id);
            obj.replaceWith(saltos.gettext.bootstrap.field(field));
        },
    });
    // Create the placeholder object with label
    const obj = saltos.gettext.bootstrap.field({
        type: 'placeholder',
        id: field.id,
        height: field.height,
        label: field.label,
    });
    field.label = ''; // Remove the label to prevent two labels
    return obj;
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
    let data = {};
    const types = ['text', 'hidden', 'integer', 'float', 'color', 'date', 'time',
        'datetime', 'textarea', 'ckeditor', 'codemirror', 'select', 'multiselect',
        'checkbox', 'switch', 'password', 'file', 'excel', 'tags', 'onetag'];
    for (const i in saltos.app.__form.fields) {
        const field = saltos.app.__form.fields[i];
        if (!types.includes(field.type)) {
            continue;
        }
        const obj = document.getElementById(field.id);
        if (!obj) {
            continue;
        }
        // This trick allow to ignore fields used only for presentation purposes
        if (field.hasOwnProperty('ignore') && saltos.core.eval_bool(field.ignore)) {
            continue;
        }
        // Continue
        let val = obj.value;
        let old = field.value.toString();
        switch (field.type) {
            case 'textarea':
            case 'ckeditor':
            case 'codemirror':
                val = val.replace(/\r\n|\r/g, '\n');
                old = old.replace(/\r\n|\r/g, '\n');
                break;
            case 'file':
                val = obj.data;
                old = field.data;
                break;
            case 'integer':
            case 'checkbox':
            case 'switch':
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
            case 'select':
                if (field.hasOwnProperty('multiple') && saltos.core.eval_bool(field.multiple)) {
                    if (field.hasOwnProperty('separator')) {
                        val = val.split(field.separator).sort().join(field.separator);
                        old = old.split(field.separator).sort().join(field.separator);
                    }
                }
                break;
            case 'multiselect':
                if (field.hasOwnProperty('separator')) {
                    val = val.split(field.separator).sort().join(field.separator);
                    old = old.split(field.separator).sort().join(field.separator);
                }
                break;
            case 'excel':
                val = obj.data;
                old = field.data;
                break;
        }
        if (typeof val == 'object' && typeof old == 'object') {
            if (JSON.stringify(val) != JSON.stringify(old) || full) {
                data[field.id] = val;
            }
        } else {
            if (val != old || full) {
                data[field.id] = val;
            }
        }
    }
    // This thick allow to add the id field of the template used
    data = saltos.app.__get_data_ids_helper(data);
    // This trick allow to do more pretty the structure of some composed fields
    data = saltos.app.__get_data_parser_helper(data);
    return data;
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
    for (const key in data) {
        let id = key.split('.');
        if (id.length == 3 && id[2] != 'id') {
            id[2] = 'id';
            id = id.join('.');
            if (!data.hasOwnProperty(id)) {
                const obj = document.getElementById(id);
                if (obj) {
                    const val = obj.value;
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
 * Get data parse helper
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
saltos.app.__get_data_parser_helper = data => {
    for (const key in data) {
        const id = key.split('.');
        if (id.length == 2) {
            const id0 = id[0];
            const id1 = id[1];
            const val = data[key];
            if (!data.hasOwnProperty(id0)) {
                data[id0] = {};
            }
            data[id0][id1] = val;
            delete data[key];
        }
        if (id.length == 3) {
            const id0 = id[0];
            const id1 = id[1];
            const id2 = id[2];
            const val = data[key];
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
    const values = [];
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
    let obj = null;
    const types = ['text', 'hidden', 'integer', 'float', 'color', 'date', 'time',
        'datetime', 'textarea', 'ckeditor', 'codemirror', 'select', 'multiselect',
        'checkbox', 'switch', 'password', 'file', 'excel', 'tags', 'onetag'];
    for (const i in saltos.app.__form.fields) {
        const field = saltos.app.__form.fields[i];
        if (!types.includes(field.type)) {
            continue;
        }
        if (!saltos.core.eval_bool(field.required)) {
            continue;
        }
        const _this = document.getElementById(field.id);
        if (!_this) {
            continue;
        }
        let value = _this.value;
        let obj_color = _this;
        let obj_focus = _this;
        // to detect the color and focus of the tags fields
        if (['tags', 'onetag'].includes(field.type)) {
            obj_color = _this.nextElementSibling;
            obj_focus = _this.nextElementSibling.querySelector('input');
        }
        // to detect the color and focus of the ckeditor fields
        if (field.type == 'ckeditor') {
            obj_color = _this.nextElementSibling;
            obj_focus = _this.ckeditor;
        }
        // to detect the color and focus of the codemirror fields
        if (field.type == 'codemirror') {
            obj_color = _this.nextElementSibling;
            obj_focus = _this.codemirror;
        }
        // to detect the value of the file fields
        if (field.type == 'file') {
            value = _this.data.length;
        }
        // to detect the color and focus of the multiselects fields
        if (field.type == 'multiselect') {
            const abc = document.getElementById(field.id + '_abc');
            obj_color = abc;
            obj_focus = abc;
        }
        // to detect the value of the checkbox or switch fields
        if (['checkbox', 'switch'].includes(field.type)) {
            value = parseInt(value);
        }
        // to detect the color, focus and value of the excel fields
        if (field.type == 'excel') {
            value = _this.data.join().replaceAll(',', '');
            obj_color = _this.parentElement;
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
        // to detect the color of the button in the password fields
        if (field.type == 'password') {
            const button = _this.nextElementSibling;
            button.classList.forEach(_this2 => {
                if (_this2.substr(0, 4) == 'btn-') {
                    button.classList.remove(_this2);
                }
            });
            if (value == '') {
                button.classList.add('btn-danger');
            } else {
                button.classList.add('btn-success');
            }
        }
        // to detect the color of the multiselects fields (the other select + buttons)
        if (field.type == 'multiselect') {
            const xyz = document.getElementById(field.id + '_xyz');
            obj_color = xyz;
            obj_color.classList.remove('is-valid');
            obj_color.classList.remove('is-invalid');
            obj_color.classList.remove('border');
            obj_color.classList.forEach(_this2 => {
                if (_this2.substr(0, 7) == 'border-') {
                    obj_color.classList.remove(_this2);
                }
            });
            if (value == '') {
                obj_color.classList.add('is-invalid');
            } else {
                obj_color.classList.add('is-valid');
            }
            const temp = document.getElementById(field.id).parentElement.parentElement;
            temp.querySelectorAll('button').forEach(_this2 => {
                _this2.classList.forEach(_this3 => {
                    if (_this3.substr(0, 4) == 'btn-') {
                        _this2.classList.remove(_this3);
                    }
                });
                if (value == '') {
                    _this2.classList.add('btn-danger');
                } else {
                    _this2.classList.add('btn-success');
                }
            });
        }
    }
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
    const types = ['text', 'hidden', 'integer', 'float', 'color', 'date', 'time',
        'datetime', 'textarea', 'ckeditor', 'codemirror', 'select', 'multiselect',
        'checkbox', 'switch', 'password', 'file', 'excel', 'tags', 'onetag'];
    for (const i in saltos.app.__form.fields) {
        const field = saltos.app.__form.fields[i];
        if (!types.includes(field.type)) {
            continue;
        }
        const obj = document.getElementById(field.id);
        if (!obj) {
            continue;
        }
        if (bool) {
            obj.setAttribute('disabled', '');
            //~ obj.setAttribute('readonly', '');
        } else {
            obj.removeAttribute('disabled');
            //~ obj.removeAttribute('readonly');
        }
        if (obj.hasOwnProperty('set_disabled')) {
            obj.set_disabled(bool);
        }
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.profile = () => {
    if (saltos.bootstrap.offcanvas('isopen')) {
        return;
    }
    saltos.gettext.bootstrap.offcanvas({
        pos: 'right',
        close: 'Close',
        //~ backdrop: true,
        //~ static: true,
        //~ keyboard: true,
        resize: true,
    });
    document.querySelector('.offcanvas-body').setAttribute('id', 'right');
    saltos.app.send_request('app/dashboard/config');
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.help = () => {
    if (saltos.bootstrap.modal('isopen')) {
        return;
    }
    saltos.gettext.bootstrap.modal({
        close: 'Close',
        class: 'modal-xl',
    });
    document.querySelector('.modal-body').setAttribute('id', 'four');
    const app = saltos.hash.get().split('/').at(1);
    saltos.app.send_request(`app/dashboard/help/${app}`);
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.logout = async () => {
    await saltos.authenticate.deauthtoken();
    saltos.app.send_request('app/login');
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.filter = () => {
    if (saltos.bootstrap.offcanvas('isopen')) {
        saltos.bootstrap.offcanvas('close');
        return;
    }
    saltos.gettext.bootstrap.offcanvas({
        pos: 'left',
        close: 'Close',
        //~ backdrop: true,
        //~ static: true,
        //~ keyboard: true,
        resize: true,
    });
    const filter = document.getElementById('filter');
    if (filter.hasOwnProperty('data-bs-title')) {
        document.querySelector('.offcanvas-title').innerHTML = T(filter['data-bs-title']);
    }
    const items = Array.prototype.slice.call(filter.childNodes);
    const parents = [];
    for (const i in items) {
        parents[i] = items[i].parentElement;
        document.querySelector('.offcanvas-body').append(items[i]);
    }
    const obj = saltos.bootstrap.__offcanvas.obj;
    obj.addEventListener('hide.bs.offcanvas', event => {
        for (const i in items) {
            parents[i].append(items[i]);
        }
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.download = file => {
    saltos.app.ajax({
        url: file,
        success: response => {
            const a = document.createElement('a');
            a.download = response.name;
            response.type = 'application/force-download'; // to force download dialog
            a.href = `data:${response.type};base64,${response.data}`;
            a.click();
        },
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.delete = file => {
    const row = document.getElementById('all' + file.split('/').slice(3, 6).join('/'));
    row.remove();
    const obj = document.getElementById('del' + file.split('/').at(3));
    let value = JSON.parse(obj.value);
    value.push(file.split('/').at(-1));
    obj.value = JSON.stringify(value);
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.ajax = args => {
    if (!args.hasOwnProperty('url')) {
        throw new Error(`Url not found`);
    }
    const temp = {
        url: 'api/?/' + args.url,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (typeof args.success == 'function') {
                args.success(response);
            }
        },
        error: error => {
            saltos.app.form.screen('unloading');
            let text = 'unknown';
            if (error.status !== undefined && error.statusText !== undefined) {
                text = error.status + ' ' + error.statusText;
            } else if (error.name !== undefined && error.message !== undefined) {
                text = error.name + ' ' + error.message;
            }
            saltos.app.show_error({
                text: text,
                code: saltos.core.__get_code_from_file_and_line(error.fileName, error.lineNumber),
            });
            if (typeof args.error == 'function') {
                args.error(error);
            }
        },
        abort: error => {
            saltos.app.form.screen('unloading');
            if (typeof args.abort == 'function') {
                args.abort(error);
            }
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    };
    if (args.hasOwnProperty('data')) {
        temp.data = JSON.stringify(args.data);
        temp.method = 'post';
        temp.content_type = 'application/json';
    }
    if (args.hasOwnProperty('proxy')) {
        temp.proxy = args.proxy;
    }
    saltos.app.form.screen('loading');
    return saltos.core.ajax(temp);
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.autosave = {
    init: (key, hash) => {
        key = saltos.app.__comma_plus_parser_helper(key);
        if (hash === undefined) {
            hash = saltos.hash.get();
        }
        for (const i in key) {
            if (saltos.storage.getItem(`saltos.app.autosave/${hash}/${key[i]}`)) {
                continue;
            }
            saltos.storage.setItem(`saltos.app.autosave/${hash}/${key[i]}`, '{}');
        }
        return key.length > 0;
    },

    save: (key, hash) => {
        key = saltos.app.__comma_plus_parser_helper(key);
        if (hash === undefined) {
            hash = saltos.hash.get();
        }
        for (const i in key) {
            if (!saltos.storage.getItem(`saltos.app.autosave/${hash}/${key[i]}`)) {
                continue;
            }
            saltos.app.__backup.restore(key[i]);
            const data = saltos.app.get_data();
            if (!Object.keys(data).length) {
                continue;
            }
            saltos.storage.setItem(`saltos.app.autosave/${hash}/${key[i]}`, JSON.stringify(data));
        }
        return key.length > 0;
    },

    restore: (key, hash) => {
        key = saltos.app.__comma_plus_parser_helper(key);
        if (hash === undefined) {
            hash = saltos.hash.get();
        }
        for (const i in key) {
            if (!saltos.storage.getItem(`saltos.app.autosave/${hash}/${key[i]}`)) {
                continue;
            }
            const data = JSON.parse(saltos.storage.getItem(`saltos.app.autosave/${hash}/${key[i]}`));
            if (!Object.keys(data).length) {
                continue;
            }
            saltos.app.__backup.restore(key[i]);
            saltos.app.form.data(data, false);
        }
        return key.length > 0;
    },

    clear: (key, hash) => {
        key = saltos.app.__comma_plus_parser_helper(key);
        if (hash === undefined) {
            hash = saltos.hash.get();
        }
        for (const i in key) {
            saltos.storage.removeItem(`saltos.app.autosave/${hash}/${key[i]}`);
        }
        return key.length > 0;
    },

    purge: (key, hash) => {
        key = saltos.app.__comma_plus_parser_helper(key);
        if (hash === undefined) {
            hash = saltos.hash.get();
        }
        for (const i in key) {
            if (!saltos.storage.getItem(`saltos.app.autosave/${hash}/${key[i]}`)) {
                continue;
            }
            const data = JSON.parse(saltos.storage.getItem(`saltos.app.autosave/${hash}/${key[i]}`));
            if (Object.keys(data).length) {
                continue;
            }
            saltos.storage.removeItem(`saltos.app.autosave/${hash}/${key[i]}`);
        }
        return key.length > 0;
    },
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__comma_plus_parser_helper = key => {
    if (key.includes('+')) {
        key = key.split('+');
        let result = [];
        for (const i in key) {
            if (saltos.app.__backup.__forms.hasOwnProperty(key[i])) {
                result.push(key[i]);
            }
        }
        return result;
    }
    key = key.split(',');
    for (const i in key) {
        if (saltos.app.__backup.__forms.hasOwnProperty(key[i])) {
            return [key[i]];
        }
    }
    return [];
};

/**
 * Online message
 *
 * This function show a message when navigator detects an online/offline change
 */
window.addEventListener('online', event => {
    saltos.app.toast(T('Warning'), T('Navigator is online'), {color: 'success'});
});

/**
 * Offline message
 *
 * This function show a message when navigator detects an online/offline change
 */
window.addEventListener('offline', event => {
    saltos.app.toast(T('Warning'), T('Navigator is offline'), {color: 'danger'});
});

/**
 * Main app code
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
window.addEventListener('load', async event => {
    // Theme part
    if (!saltos.bootstrap.get_bs_theme()) {
        saltos.bootstrap.set_bs_theme('auto');
    } else {
        saltos.bootstrap.set_bs_theme(saltos.bootstrap.get_bs_theme());
    }
    if (!saltos.bootstrap.get_css_theme()) {
        saltos.bootstrap.set_css_theme('default');
    } else {
        saltos.bootstrap.set_css_theme(saltos.bootstrap.get_css_theme());
    }
    // Lang part
    if (!saltos.gettext.get()) {
        saltos.gettext.set(navigator.language || navigator.systemLanguage);
    } else {
        saltos.gettext.set(saltos.gettext.get());
    }
    // Token part
    if (saltos.token.get()) {
        await saltos.authenticate.checktoken();
    }
    // Hash part
    saltos.hash.trigger();
});
