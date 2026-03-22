
/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Bootstrap helper module
 *
 * This fie contains useful functions related to the bootstrap widgets, allow to create widgets and
 * other plugins suck as plots or rich editors
 *
 * @tags        => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, OC
 * @onetag      => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, OC
 */

/**
 * Tags constructor helper
 *
 * This function creates a text input that allow to manage tags, each tag is paint as a badge
 * and each tag can be deleted, the result is stored in a text using a comma separated values
 *
 * @id          => the id used by the object
 * @value       => comma separated values
 * @datalist    => array with options for the datalist, used as autocomplete for the text input
 * @label       => this parameter is used as text for the label
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @separator   => the separator string used to split and join the tags
 * @onchange    => the function executed when onchange event is detected
 * @create      => allow to specify if the widget can create new items
 *
 * Notes:
 *
 * This widget contains a datalist with ajax autoload, this allow to send requests
 * to the desired path to retrieve the contents of the datalist for the autocomplete,
 * this request uses an historical keyword that can be retrieved in the json/term
 *
 * This widget uses the tom-select plugin, more info in their project website:
 * - https://tom-select.js.org/
 */
saltos.bootstrap.__field.tags = field => {
    saltos.core.check_params(field, ['separator'], ',');
    saltos.core.check_params(field, ['datalist', 'color', 'rounded']);
    saltos.core.check_params(field, ['create'], true);
    field.create = saltos.core.eval_bool(field.create);
    field.value = saltos.bootstrap.__value_helper(field.value, field.separator);
    const obj = saltos.bootstrap.__field.text(field);
    field.type = 'tags';
    const element = obj.querySelector('input');
    element.style.display = 'none';
    const fn = saltos.bootstrap.__datalist_helper(field.datalist);
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Prepare rounded
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    // Fix rounded-pill to the desired rounded
    obj.querySelectorAll('.rounded-pill').forEach(item => {
        item.classList.replace('rounded-pill', rounded);
    });
    // Continue
    saltos.core.require([
        'lib/tomselect/tom-select.bootstrap5.min.css',
        'lib/tomselect/tom-select.complete.min.js',
        'lib/tomselect/tom-select.dark.min.css',
    ], () => {
        placeholder.remove();
        const tags = new TomSelect(element, {
            delimiter: field.separator,
            preload: true,
            create: field.create,
            createOnBlur: true,
            persist: false,
            sortField: [{field: '$order'}, {field: '$score'}],
            closeAfterSelect: true,
            selectOnTab: true,
            openOnFocus: false,
            load: fn,
            plugins: [
                'remove_button',
                'clear_button',
                'caret_position',
                'input_autogrow',
            ],
            onInitialize() {
                //~ this.wrapper.classList.add(rounded);
                this.control.classList.add(rounded);
            },
        });
        element.tomselect = tags;
    });
    // Program the set in the input first
    element.set = value => {
        if (!('tomselect' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('tomselect' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        value = saltos.bootstrap.__value_helper(value, field.separator);
        element.value = value;
        element.tomselect.sync();
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('tomselect' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        if (bool) {
            element.tomselect.disable();
        } else {
            element.tomselect.enable();
        }
    };
    return obj;
};

/**
 * One tag constructor helper
 *
 * This function creates a select that allow to be used as a text input like a select widget and allow
 * to create new items writing directly inside of the widget.
 *
 * @id          => the id used by the object
 * @value       => the value of this field
 * @datalist    => array with options for the datalist, used as autocomplete for the text input
 * @label       => this parameter is used as text for the label
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onchange    => the function executed when onchange event is detected
 * @create      => allow to specify if the widget can create new items
 *
 * Notes:
 *
 * This widget contains a datalist with ajax autoload, this allow to send requests
 * to the desired path to retrieve the contents of the datalist for the autocomplete,
 * this request uses an historical keyword that can be retrieved in the json/term
 *
 * This widget uses the tom-select plugin, more info in their project website:
 * - https://tom-select.js.org/
 */
saltos.bootstrap.__field.onetag = field => {
    saltos.core.check_params(field, ['datalist', 'color', 'value', 'rounded']);
    saltos.core.check_params(field, ['create'], true);
    field.create = saltos.core.eval_bool(field.create);
    if (field.value !== '')  {
        field.rows = [field.value];
    }
    const obj = saltos.bootstrap.__field.select(field);
    field.type = 'onetag';
    const element = obj.querySelector('select');
    element.style.display = 'none';
    const fn = saltos.bootstrap.__datalist_helper(field.datalist);
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Prepare rounded
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    // Continue
    saltos.core.require([
        'lib/tomselect/tom-select.bootstrap5.min.css',
        'lib/tomselect/tom-select.complete.min.js',
        'lib/tomselect/tom-select.dark.min.css',
    ], () => {
        placeholder.remove();
        const tags = new TomSelect(element, {
            preload: true,
            create: field.create,
            createOnBlur: true,
            persist: false,
            sortField: [{field: '$order'}, {field: '$score'}],
            closeAfterSelect: true,
            selectOnTab: true,
            openOnFocus: false,
            load: fn,
            plugins: [
                'clear_button',
                'input_autogrow',
            ],
            onInitialize() {
                //~ this.wrapper.classList.add(rounded);
                this.control.classList.add(rounded);
            },
        });
        element.tomselect = tags;
    });
    // Program the set in the input first
    element.set = value => {
        if (!('tomselect' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('tomselect' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        element.tomselect.addOption({
            text: value,
            value: value,
        });
        element.tomselect.addItem(value);
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('tomselect' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        if (bool) {
            element.tomselect.disable();
        } else {
            element.tomselect.enable();
        }
    };
    return obj;
};

/**
 * Datalist helper
 *
 * This function is a helper function used by the tags and onetag widgets
 * and is intended to be used as load function by the tomselect library.
 *
 * @datalist => the original datalist that can be an string or an object
 */
saltos.bootstrap.__datalist_helper = datalist => {
    let fn = null;
    if (typeof datalist === 'string' && datalist !== '') {
        fn = (query, callback) => {
            if (!query) {
                callback([]);
                return;
            }
            saltos.core.ajax({
                url: 'api/?/' + datalist,
                data: JSON.stringify({term: query}),
                method: 'post',
                content_type: 'application/json',
                success: response => {
                    if (!saltos.app.check_response(response)) {
                        return;
                    }
                    const array = [];
                    for (const key in response.data) {
                        const val = response.data[key];
                        if (typeof val === 'object') {
                            const temp = {};
                            if ('text' in val) {
                                temp.text = val.text;
                            } else if ('label' in val) {
                                temp.text = val.label;
                            } else if ('value' in val) {
                                temp.text = val.value;
                            }
                            if ('value' in val) {
                                temp.value = val.value;
                            } else if ('label' in val) {
                                temp.value = val.label;
                            } else if ('text' in val) {
                                temp.value = val.text;
                            }
                            array.push(temp);
                        } else {
                            array.push({
                                text: val,
                                value: val,
                            });
                        }
                    }
                    callback(array);
                },
                token: saltos.token.get(),
                lang: saltos.gettext.get(),
                abortable: true,
            });
        };
    }
    if (typeof datalist === 'object') {
        fn = (query, callback) => {
            const array = [];
            for (const key in datalist) {
                const val = datalist[key];
                if (typeof val === 'object') {
                    const temp = {};
                    if ('text' in val) {
                        temp.text = val.text;
                    } else if ('label' in val) {
                        temp.text = val.label;
                    } else if ('value' in val) {
                        temp.text = val.value;
                    }
                    if ('value' in val) {
                        temp.value = val.value;
                    } else if ('label' in val) {
                        temp.value = val.label;
                    } else if ('text' in val) {
                        temp.value = val.text;
                    }
                    array.push(temp);
                } else {
                    array.push({
                        text: val,
                        value: val,
                    });
                }
            }
            callback(array);
        };
    }
    return fn;
};

/**
 * Value helper
 *
 * This function is a helper function used by the tags widget and is intended
 * to be used to convert a string into an array using the separator for split.
 *
 * @value     => the original value that must to be processed
 * @separator => the separator string used in the split
 */
saltos.bootstrap.__value_helper = (value, separator) => {
    value = value.toString().split(separator);
    let array = [];
    for (const key in value) {
        const val = value[key].trim();
        if (val.length) {
            array.push(val);
        }
    }
    value = array.join(separator);
    return value;
};
