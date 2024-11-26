
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
saltos.app.modal = (title, message, extra = {}) => {
    if (!extra.hasOwnProperty('buttons')) {
        extra.buttons = [{
            label: 'Close',
            color: 'success',
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
saltos.app.toast = (title, message, extra = {}) => {
    if (!extra.hasOwnProperty('color')) {
        extra.color = 'success';
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
        if (typeof saltos.form[key] != 'function') {
            throw new Error(`Response type ${key} not found`);
        }
        if (saltos.form[key].constructor.name == 'AsyncFunction') {
            await saltos.form[key](val);
        } else {
            saltos.form[key](val);
        }
    }
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
 * the saltos.form.__form.fields that contains the list of all used fields in the layout, this
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
    for (const i in saltos.form.__form.fields) {
        const field = saltos.form.__form.fields[i];
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
    for (const i in saltos.form.__form.fields) {
        const field = saltos.form.__form.fields[i];
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
    for (const i in saltos.form.__form.fields) {
        const field = saltos.form.__form.fields[i];
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
 * Profile screen
 *
 * This function allow to open the profile screen in a offcanvas widget
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
 * Help screen
 *
 * This function allow to open the help screen in a modal widget
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
 * Logout feature
 *
 * This function execute the deauthtoken action and jump to the login screen
 */
saltos.app.logout = async () => {
    await saltos.authenticate.deauthtoken();
    saltos.app.send_request('app/login');
};

/**
 * Filter screen
 *
 * This function allow to open the filter screen in a offcanvas widget
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
 * Download helper
 *
 * This function allow to download files, to do it, make the ajax request and
 * using the base64 data response, sets the href of an anchor created dinamically
 * to emulate the download action
 *
 * @file => the file data used to identify the desired file in the backend part
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
 * Delete helper
 *
 * This function allow to remove the files and notes in the files and notes widgets
 *
 * @file => the file or note string path
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
 * Ajax helper
 *
 * This function uses the saltos.core.ajax to implement the ajax feature, and in
 * this scope, tries to simplify the needed configuration by provide a compound of
 * defined features and code, intended to process the responses, the errors, to
 * add the token, the lang.
 *
 * @url     => url of the ajax call
 * @success => callback function for the success action (optional)
 * @error   => callback function for the error action (optional)
 * @abort   => callback function for the abort action (optional)
 * @data    => data used in the body of the request
 * @proxy   => add the Proxy header with the value passed, intended to be used by the SaltOS PROXY
 * @loading => enable or disable the loading feature, true by default
 */
saltos.app.ajax = args => {
    if (!args.hasOwnProperty('url')) {
        throw new Error(`Url not found`);
    }
    saltos.core.check_params(args, ['loading'], true);
    const temp = {
        url: 'api/?/' + args.url,
        success: response => {
            if (args.loading) {
                saltos.form.screen('unloading');
            }
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (typeof args.success == 'function') {
                args.success(response);
            }
        },
        error: error => {
            if (args.loading) {
                saltos.form.screen('unloading');
            }
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
            if (args.loading) {
                saltos.form.screen('unloading');
            }
            if (typeof args.abort == 'function') {
                args.abort(error);
            }
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
        abortable: true,
    };
    if (args.hasOwnProperty('data')) {
        temp.data = JSON.stringify(args.data);
        temp.method = 'post';
        temp.content_type = 'application/json';
    }
    if (args.hasOwnProperty('proxy')) {
        temp.proxy = args.proxy;
    }
    if (args.loading) {
        saltos.form.screen('loading');
    }
    return saltos.core.ajax(temp);
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

/**
 * TODO
 *
 * TODO
 */
saltos.app.push = {
    executing: false,
};

/**
 * TODO
 *
 * TODO
 */
saltos.app.push.fn = () => {
    if (saltos.app.push.executing) {
        return;
    }
    if (!saltos.token.get()) {
        return;
    }
    if (!navigator.onLine) {
        return;
    }
    saltos.app.push.executing = true;
    saltos.core.ajax({
        url: 'api/?/push/get/' + saltos.app.push.timestamp,
        success: response => {
            for (const key in response) {
                const val = response[key];
                if (['success', 'danger'].includes(val.type)) {
                    saltos.app.toast('Notification', val.message, {color: val.type});
                } else if (['event'].includes(val.type)) {
                    saltos.window.send(val.message);
                } else {
                    throw new Error(`Unknown response type ${val.type}`);
                }
                saltos.app.push.timestamp = Math.max(saltos.app.push.timestamp, val.timestamp);
            }
            saltos.app.push.executing = false;
        },
        error: error => {
            saltos.app.push.executing = false;
        },
        abort: error => {
            saltos.app.push.executing = false;
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
        proxy: 'no',
    });
};

/**
 * TODO
 *
 * TODO
 */
window.addEventListener('load', async event => {
    if (saltos.app.push.hasOwnProperty('timestamp')) {
        throw new Error('saltos.app.push.timestamp found');
    }
    saltos.app.push.timestamp = saltos.core.timestamp();
    if (saltos.app.push.hasOwnProperty('interval')) {
        throw new Error('saltos.app.push.interval found');
    }
    saltos.app.push.interval = setInterval(saltos.app.push.fn, 1000);
});
