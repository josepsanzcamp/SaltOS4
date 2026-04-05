
/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
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
 * @textarea    => id, class, PL, value, DS, RO, RQ, AF, AK, rows, tooltip, label, color, height, OC
 */

/**
 * Textarea constructor helper
 *
 * This function returns a textarea object with the autogrow plugin enabled
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
 * @height      => the height used as style.minHeight parameter
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget requires the autoheight library and can be loaded automatically using the require
 * feature:
 *
 * @lib/autoheight/autoheight.min.js
 */
saltos.bootstrap.__field.textarea = field => {
    saltos.core.check_params(field, ['height']);
    let obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(field));
    const element = obj.querySelector('textarea');
    saltos.core.require([
        'lib/autoheight/autoheight.min.js',
    ], () => {
        autoheight(element);
    });
    if (field.height !== '') {
        element.style.minHeight = field.height;
    }
    obj = saltos.core.optimize(obj);
    return obj;
};

/**
 * Private textarea constructor helper
 *
 * This function returns a textarea object, you can pass the follow arguments:
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
 * @onchange    => the function executed when onchange event is detected
 * @autosave    => allow to disable the autosave feature for this field, true by default
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__textarea_helper = field => {
    saltos.core.check_params(field, ['class', 'id', 'placeholder', 'value', 'onchange',
                                     'disabled', 'readonly', 'required', 'autofocus',
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
        <textarea class="form-control ${rounded} ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${disabled} ${readonly} ${required} ${autofocus} ${autosave}
            id="${field.id}" data-bs-title="${field.tooltip}">${field.value}</textarea>
    `);
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    if (field.onchange !== '') {
        saltos.bootstrap.__onchange_helper(obj, field.onchange);
    }
    // add shadow feature
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    const obj2 = saltos.core.html(`<div class="${shadow} ${rounded}"></div>`);
    obj2.append(obj);
    return obj2;
};
