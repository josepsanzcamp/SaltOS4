
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
    if (!('buttons' in extra)) {
        extra.buttons = [{
            label: 'Close',
            color: 'success',
            icon: 'x-lg',
            autofocus: true,
            onclick: () => {},
        }];
    }
    if (!('color' in extra)) {
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
    if (!('color' in extra)) {
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
    if ('error' in response && typeof response.error == 'object') {
        saltos.app.show_error(response.error);
        bool = false;
    }
    if ('logout' in response && saltos.core.eval_bool(response.logout)) {
        saltos.window.send('saltos.app.logout');
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
            saltos.app.prefetch_cache([response]);
        },
    });
};

/**
 * Data helper object
 *
 * This object allow to the app to store the data of the prefetch feature
 */
saltos.app.__cache = {};

/**
 * Process response helper
 *
 * This function process the responses received by the send request
 */
saltos.app.prefetch_cache = (responses) => {
    for (const i in responses) {
        const response = responses[i];
        for (const key in response) {
            const key2 = saltos.core.fix_key(key);
            if (key2 == 'cache') {
                const val = response[key];
                if (!(val in saltos.app.__cache)) {
                    saltos.app.ajax({
                        url: val,
                        success: response2 => {
                            saltos.app.__cache[val] = response2;
                            responses.push(response2);
                            saltos.app.prefetch_cache(responses);
                        },
                    });
                    return;
                }
            }
        }
    }
    saltos.app.process_response(responses[0]);
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
        if ('ignore' in field && saltos.core.eval_bool(field.ignore)) {
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
                val = parseInt(val, 10);
                old = parseInt(old, 10);
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
                if ('multiple' in field && saltos.core.eval_bool(field.multiple)) {
                    if ('separator' in field) {
                        val = val.split(field.separator).sort().join(field.separator);
                        old = old.split(field.separator).sort().join(field.separator);
                    }
                }
                if (val == '0' && old == '') {
                    old = val;
                }
                if (val == '' && old == '0') {
                    val = old;
                }
                break;
            case 'multiselect':
                if ('separator' in field) {
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
    // This trick allow to do more pretty the structure of some composed fields
    data = saltos.app.__get_data_parser_helper(data);
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
            if (!(id0 in data)) {
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
            if (!(id0 in data)) {
                data[id0] = {};
            }
            if (!(id1 in data[id0])) {
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
    obj.querySelectorAll('input[type=checkbox]:checked[value]').forEach(item => {
        values.push(item.value);
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
        const item = document.getElementById(field.id);
        if (!item) {
            continue;
        }
        let value = item.value;
        let obj_color = item;
        let obj_focus = item;
        // to detect the color and focus of the tags fields
        if (['tags', 'onetag'].includes(field.type)) {
            obj_color = item.nextElementSibling;
            obj_focus = item.nextElementSibling.querySelector('input');
        }
        // to detect the color and focus of the ckeditor fields
        if (field.type == 'ckeditor') {
            obj_color = item.nextElementSibling;
            obj_focus = item.ckeditor;
        }
        // to detect the color and focus of the codemirror fields
        if (field.type == 'codemirror') {
            obj_color = item.nextElementSibling;
            obj_focus = item.codemirror;
        }
        // to detect the value of the file fields
        if (field.type == 'file') {
            value = item.data.length;
        }
        // to detect the color and focus of the multiselects fields
        if (field.type == 'multiselect') {
            const abc = document.getElementById(field.id + '_abc');
            obj_color = abc;
            obj_focus = abc;
        }
        // to detect the value of the checkbox or switch fields
        if (['checkbox', 'switch'].includes(field.type)) {
            value = parseInt(value, 10);
        }
        // to detect the color, focus and value of the excel fields
        if (field.type == 'excel') {
            value = item.data.join().replaceAll(',', '');
            obj_color = item.parentElement;
        }
        // continue;
        obj_color.classList.remove('is-valid');
        obj_color.classList.remove('is-invalid');
        obj_color.classList.remove('border');
        obj_color.classList.forEach(item2 => {
            if (item2.substr(0, 7) == 'border-') {
                obj_color.classList.remove(item2);
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
            const button = item.nextElementSibling;
            button.classList.forEach(item2 => {
                if (item2.substr(0, 4) == 'btn-') {
                    button.classList.remove(item2);
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
            obj_color.classList.forEach(item2 => {
                if (item2.substr(0, 7) == 'border-') {
                    obj_color.classList.remove(item2);
                }
            });
            if (value == '') {
                obj_color.classList.add('is-invalid');
            } else {
                obj_color.classList.add('is-valid');
            }
            const temp = document.getElementById(field.id).parentElement.parentElement;
            temp.querySelectorAll('button').forEach(item2 => {
                item2.classList.forEach(item3 => {
                    if (item3.substr(0, 4) == 'btn-') {
                        item2.classList.remove(item3);
                    }
                });
                if (value == '') {
                    item2.classList.add('btn-danger');
                } else {
                    item2.classList.add('btn-success');
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
        if ('set_disabled' in obj) {
            obj.set_disabled(bool);
        }
    }
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
    if (!('url' in args)) {
        throw new Error(`Url not found`);
    }
    saltos.core.check_params(args, ['loading'], true);
    const temp = {
        url: 'api/?/' + args.url,
        success: (data, response) => {
            if (args.loading) {
                saltos.form.screen('unloading');
            }
            const proxy = response.headers.get('x-proxy-type');
            if (proxy == 'cache') {
                if (navigator.onLine) {
                    saltos.app.toast(T('Warning'),
                        T('Content served from cache because there is an issue with the server'),
                        {color: 'danger'});
                } else {
                    saltos.app.toast(T('Warning'),
                        T('Content served from cache because navigator is offline'),
                        {color: 'danger'});
                }
            }
            if (!saltos.app.check_response(data)) {
                return;
            }
            if (typeof args.success == 'function') {
                args.success(data);
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
    if ('data' in args) {
        temp.data = JSON.stringify(args.data);
        temp.method = 'post';
        temp.content_type = 'application/json';
    }
    if ('proxy' in args) {
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
    if (!saltos.bootstrap.check_bs_theme(saltos.bootstrap.get_bs_theme())) {
        saltos.bootstrap.set_bs_theme('auto');
    } else {
        saltos.bootstrap.set_bs_theme(saltos.bootstrap.get_bs_theme());
    }
    if (!saltos.bootstrap.check_css_theme(saltos.bootstrap.get_css_theme())) {
        saltos.bootstrap.set_css_theme('blue');
    } else {
        saltos.bootstrap.set_css_theme(saltos.bootstrap.get_css_theme());
    }
    // Lang part
    if (!saltos.gettext.get()) {
        saltos.gettext.set(navigator.language);
    } else {
        saltos.gettext.set(saltos.gettext.get());
    }
    // Token part
    if (saltos.token.get()) {
        await saltos.authenticate.checktoken();
    }
    // Add the auto logout feature
    saltos.window.set_listener('saltos.app.logout', event => {
        saltos.autosave.save('two,one');
        saltos.autosave.purge('two,one');
        saltos.token.unset();
        saltos.app.send_request('app/login');
        saltos.favicon.run();
    });
    // Add the auto login feature
    saltos.window.set_listener('saltos.app.login', event => {
        saltos.form.screen('clear');
        saltos.hash.trigger();
        saltos.favicon.run();
    });
    // Hash part
    saltos.hash.trigger();
});
