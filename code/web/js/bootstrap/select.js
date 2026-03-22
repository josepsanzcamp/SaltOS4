
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
 * @select      => id, class, DS, RQ, AF, AK, rows, multiple, size, value, tooltip, label, color, OC
 * @multiselect => id, class, DS, RQ, AF, AK, rows, multiple, size, value, multiple, tooltip, label, color
 */

/**
 * Select constructor helper
 *
 * This function returns a select object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @required  => this parameter raise the required flag
 * @autofocus => this parameter raise the autofocus flag
 * @multiple  => this parameter enables the multiple selection feature of the select
 * @size      => this parameter allow to see the options list opened with n (size) entries
 * @value     => the value used to detect the selected option
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @rows      => this parameter contains the list of options, each option must be an object
 *               with label and value entries
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onchange  => the function executed when onchange event is detected
 */
saltos.bootstrap.__field.select = field => {
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'required', 'onchange',
                                     'autofocus', 'multiple', 'size', 'value', 'tooltip',
                                     'accesskey', 'color', 'separator', 'shadow', 'rounded']);
    saltos.core.check_params(field, ['rows'], []);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let multiple = '';
    let width = '';
    let height = '';
    if (saltos.core.eval_bool(field.multiple)) {
        multiple = 'multiple';
        width = 'w-100';
        height = 'h-100';
    }
    let size = '';
    if (field.size !== '') {
        size = `size="${field.size}"`;
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let obj = saltos.core.html(`
        <div class="${shadow} ${rounded} ${width}">
            <select class="form-select ${rounded} ${border} ${height} ${field.class}" id="${field.id}"
                ${disabled} ${required} ${autofocus} ${multiple} ${size}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}"></select>
        </div>
    `);
    const element = obj.querySelector('select');
    if (field.onchange !== '') {
        saltos.bootstrap.__onchange_helper(element, field.onchange);
    }
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(element);
    }
    if (!field.separator) {
        field.separator = ',';
    }
    const values = field.value.toString().split(field.separator);
    for (const key in values) {
        values[key] = values[key].trim();
    }
    for (const key in field.rows) {
        const val = saltos.core.join_attr_value(field.rows[key]);
        if (typeof val === 'object') {
            saltos.core.check_params(val, ['label', 'value']);
            let selected = '';
            if (values.includes(val.value.toString())) {
                selected = 'selected';
            }
            const option = saltos.core.html(`<option value="${val.value}" ${selected}></option>`);
            option.append(val.label);
            element.append(option);
        } else {
            let selected = '';
            if (values.includes(val.toString())) {
                selected = 'selected';
            }
            const option = saltos.core.html(`<option value="${val}" ${selected}></option>`);
            option.append(val);
            element.append(option);
        }
    }
    // Program the disabled feature
    element.set_disabled = bool => {
        if (bool) {
            element.setAttribute('disabled', '');
        } else {
            element.removeAttribute('disabled');
        }
    };
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Multiselect constructor helper
 *
 * This function returns a multiselect object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @required  => this parameter raise the required flag
 * @size      => this parameter allow to see the options list opened with n (size) entries
 * @value     => the value used as src parameter
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @rows      => this parameter contains the list of options, each option must be an object
 *               with label and value entries
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @separator => the separator string used to split and join the values
 * @onchange  => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget is created joinin 2 selects and 2 buttons, the user must get the value
 * using the hidden input that is builded using the original id passed by argument.
 *
 * Warning:
 *
 * Detected a bug with this widget in chrome in mobile browsers
 */
saltos.bootstrap.__field.multiselect = field => {
    saltos.core.check_params(field, ['value', 'class', 'id', 'disabled', 'required',
                                     'size', 'tooltip', 'color', 'separator']);
    saltos.core.check_params(field, ['rows'], []);
    if (!field.separator) {
        field.separator = ',';
    }
    let obj = saltos.core.html(`
        <div class="container-fluid">
            <div class="row">
                <div class="col px-0 one d-flex">
                </div>
                <div class="col col-auto my-auto two">
                </div>
                <div class="col px-0 three d-flex">
                </div>
            </div>
        </div>
    `);
    obj.querySelector('.one').append(saltos.bootstrap.__field.hidden(saltos.core.copy_object(field)));
    obj.querySelector('.one').append(saltos.bootstrap.__field.select({
        color: field.color,
        id: field.id + '_abc',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rows: field.rows,
        rounded: 'rounded',
    }));
    obj.querySelector('.two').append(saltos.bootstrap.__field.button({
        class: `bi-chevron-double-right`,
        color: field.color,
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            obj.querySelectorAll('#' + field.id + '_abc option').forEach(option => {
                if (option.selected) {
                    obj.querySelector('#' + field.id + '_xyz').append(option);
                }
            });
            const val = [];
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            obj.querySelector('#' + field.id).value = val.join(field.separator);
        },
    }));
    obj.querySelector('.two').append(saltos.core.html('<div class="mb-3"></div>'));
    obj.querySelector('.two').append(saltos.bootstrap.__field.button({
        class: `bi-chevron-double-left`,
        color: field.color,
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                if (option.selected) {
                    obj.querySelector('#' + field.id + '_abc').append(option);
                }
            });
            const val = [];
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            obj.querySelector('#' + field.id).value = val.join(field.separator);
        },
    }));
    obj.querySelector('.three').append(saltos.bootstrap.__field.select({
        color: field.color,
        id: field.id + '_xyz',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rounded: 'rounded',
    }));
    saltos.core.when_visible(obj, () => {
        document.querySelectorAll('label[for=' + field.id + ']').forEach(item => {
            item.setAttribute('for', field.id + '_abc');
        });
    });
    // Program the set feature
    const element = obj.querySelector('input[type=hidden]');
    element.set = value => {
        const values = value.toString().split(field.separator);
        for (const key in values) {
            values[key] = values[key].trim();
        }
        obj.querySelectorAll('#' + field.id + '_abc option').forEach(option => {
            if (values.includes(option.value)) {
                obj.querySelector('#' + field.id + '_xyz').append(option);
            }
        });
        obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
            if (!values.includes(option.value)) {
                obj.querySelector('#' + field.id + '_abc').append(option);
            }
        });
        const val = [];
        obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
            val.push(option.value);
        });
        element.value = val.join(field.separator);
    };
    element.set(field.value);
    // Program the disabled feature
    element.set_disabled = bool => {
        const temp = obj.querySelector('#' + field.id).closest('.row');
        temp.querySelectorAll('select, button').forEach(item => {
            item.set_disabled(bool);
        });
    };
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
