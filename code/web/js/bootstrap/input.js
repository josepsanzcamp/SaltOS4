
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
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
 * @text        => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, OE, OC
 * @hidden      => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, color, OE, OC
 * @integer     => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @float       => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @color       => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @date        => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @time        => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @datetime    => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 */

/**
 * Text constructor helper
 *
 * This function returns an input object of type text, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @datalist    => array with options for the datalist, used as autocomplete for the text input
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 */
saltos.bootstrap.__field.text = field => {
    saltos.core.check_params(field, ['datalist'], []);
    field.type = 'text';
    let obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__text_helper(field));
    if (field.datalist.length) {
        obj.querySelector('input').setAttribute('list', field.id + '_datalist');
        obj.append(saltos.core.html(`<datalist id="${field.id}_datalist"></datalist>`));
        for (const key in field.datalist) {
            const val = field.datalist[key];
            obj.querySelector('datalist').append(saltos.core.html(`<option value="${val}" />`));
        }
    }
    obj = saltos.core.optimize(obj);
    return obj;
};

/**
 * Hidden constructor helper
 *
 * This function returns an input object of type hidden, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @color       => the color of the widget (primary, secondary, success, danger, warning,
 *                 info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 * @data        => this widget can store data in the data attribute, usefull to store
 *                 an array or object with data
 *
 * Notes:
 *
 * This function allow the previous parameters but for hidden inputs, only id
 * and value are usually used, in some cases can be interesting to use the
 * class to identify a group of hidden input
 */
saltos.bootstrap.__field.hidden = field => {
    field.type = 'hidden';
    const obj = saltos.bootstrap.__text_helper(field);
    if ('data' in field) {
        obj.data = field.data;
    }
    return obj;
};

/**
 * Integer constructor helper
 *
 * This function returns an input object of type integer, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget requires the imask library and can be loaded automatically using the require
 * feature:
 *
 * @lib/imaskjs/imask.min.js
 */
saltos.bootstrap.__field.integer = field => {
    field.type = 'text';
    let obj = saltos.bootstrap.__text_helper(field);
    field.type = 'integer';
    const element = obj.querySelector('input');
    element.setAttribute('inputmode', 'numeric');
    saltos.core.require([
        'lib/imaskjs/imask.min.js',
    ], () => {
        IMask(element, {
            mask: Number,
            scale: 0,
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Float constructor helper
 *
 * This function returns an input object of type float, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget requires the imask library and can be loaded automatically using the require
 * feature:
 *
 * @lib/imaskjs/imask.min.js
 */
saltos.bootstrap.__field.float = field => {
    field.type = 'text';
    let obj = saltos.bootstrap.__text_helper(field);
    field.type = 'float';
    const element = obj.querySelector('input');
    element.setAttribute('inputmode', 'numeric');
    saltos.core.require([
        'lib/imaskjs/imask.min.js',
    ], () => {
        IMask(element, {
            mask: Number,
            radix: '.',
            mapToRadix: [','],
            scale: 99,
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Color constructor helper
 *
 * This function returns an input object of type color, you can pass the same
 * arguments that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * Ths color input launch a warning if value is not in the format #rrggbb,
 * for this reason it is set to #000000 if value is void
 */
saltos.bootstrap.__field.color = field => {
    saltos.core.check_params(field, ['value']);
    if (field.value === '') {
        field.value = '#000000';
    }
    field.type = 'color';
    field.class = 'form-control-color';
    let obj = saltos.bootstrap.__text_helper(field);
    obj.classList.add('d-inline-block');
    obj = saltos.bootstrap.__label_combine(field, obj);
    if (obj.children.length > 1) {
        const br = saltos.core.html('<br/>');
        obj.insertBefore(br, obj.children[1]);
    }
    return obj;
};

/**
 * Date constructor helper
 *
 * This function returns an input object of type date, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 */
saltos.bootstrap.__field.date = field => {
    field.type = 'date';
    let obj = saltos.bootstrap.__text_helper(field);
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Time constructor helper
 *
 * This function returns an input object of type time, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 */
saltos.bootstrap.__field.time = field => {
    field.type = 'time';
    let obj = saltos.bootstrap.__text_helper(field);
    obj.querySelector('input').step = 1; // This enable the seconds
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Datetime constructor helper
 *
 * This function returns an input object of type datetime, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 */
saltos.bootstrap.__field.datetime = field => {
    field.type = 'datetime-local';
    let obj = saltos.bootstrap.__text_helper(field);
    field.type = 'datetime';
    obj.querySelector('input').step = 1; // This enable the seconds
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Private text constructor helper
 *
 * This function returns an input object of type text, you can pass some arguments as:
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 * @autosave    => allow to disable the autosave feature for this field, true by default
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__text_helper = field => {
    saltos.core.check_params(field, ['type', 'class', 'id', 'placeholder', 'value', 'disabled',
                                     'onenter', 'onchange', 'readonly', 'required', 'autofocus',
                                     'tooltip', 'accesskey', 'color', 'shadow', 'rounded']);
    saltos.core.check_params(field, ['autosave'], true);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let autosave = '';
    if (!saltos.core.eval_bool(field.autosave)) {
        autosave = 'autosave="false"';
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    const obj = saltos.core.html(`
        <input type="${field.type}" class="form-control ${rounded} ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${disabled} ${readonly} ${required} ${autofocus} ${autosave}
            id="${field.id}" data-bs-title="${field.tooltip}" value="${field.value}" />
    `);
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    if (field.onenter !== '') {
        saltos.bootstrap.__onenter_helper(obj, field.onenter);
    }
    if (field.onchange !== '') {
        saltos.bootstrap.__onchange_helper(obj, field.onchange);
    }
    if (field.type === 'hidden') {
        return obj;
    }
    // add shadow feature
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    const obj2 = saltos.core.html(`<div class="${shadow} ${rounded}"></div>`);
    obj2.append(obj);
    return obj2;
};
