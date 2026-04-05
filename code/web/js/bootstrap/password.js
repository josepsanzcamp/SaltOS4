
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
 * @password    => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 */

/**
 * Password constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id           => the id used by the object
 * @class        => allow to add more classes to the default form-control
 * @placeholder  => the text used as placeholder parameter
 * @value        => the value used as value parameter
 * @disabled     => this parameter raise the disabled flag
 * @readonly     => this parameter raise the readonly flag
 * @required     => this parameter raise the required flag
 * @autofocus    => this parameter raise the autofocus flag
 * @tooltip      => this parameter raise the title flag
 * @accesskey    => the key used as accesskey parameter
 * @label        => this parameter is used as text for the label
 * @color        => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter      => the function executed when enter key is pressed
 * @onchange     => the function executed when onchange event is detected
 * @autocomplete => set to false to enable the hiddens passwords trick
 *
 * Notes:
 *
 * This widget add an icon to the end of the widget with an slashed eye, this allow to
 * see the entered password to verify it, in reality, this button swaps the input between
 * password and text type, allowing to do visible or not the contents of the input
 *
 * Setting the field.autocomplete=false enable the feature that tries to disable the
 * autocomplete provided by the browsers password adding the autocomplete="new-password"
 */
saltos.bootstrap.__field.password = field => {
    saltos.core.check_params(field, ['label', 'class', 'id', 'placeholder', 'value', 'disabled',
                                     'onenter', 'onchange', 'readonly', 'required', 'autofocus',
                                     'tooltip', 'accesskey', 'color', 'shadow', 'rounded']);
    saltos.core.check_params(field, ['autocomplete'], true);
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
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    let autocomplete = '';
    if (!saltos.core.eval_bool(field.autocomplete)) {
        autocomplete = 'autocomplete="new-password"';
    }
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    let rounded_start = 'rounded-start';
    let rounded_end = 'rounded-end';
    if (field.rounded !== '') {
        rounded = field.rounded;
        rounded_start = field.rounded.replace('rounded', 'rounded-start');
        rounded_end = field.rounded.replace('rounded', 'rounded-end');
    }
    const obj = saltos.core.html(`
        <div>
            <div class="input-group ${shadow} ${rounded}">
                <input type="password" class="form-control ${rounded_start} ${border} ${field.class}"
                    id="${field.id}" placeholder="${field.placeholder}" value="${field.value}"
                    ${disabled} ${readonly} ${required} ${autofocus} ${autocomplete}
                    aria-label="${field.placeholder}" aria-describedby="${field.id}_button"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
                <button class="btn btn-${color} bi-eye-slash ${rounded_end}" type="button"
                    id="${field.id}_button" data-bs-title="${field.tooltip}"></button>
            </div>
        </div>
    `);
    // Continue
    const input = obj.querySelector('input');
    const button = obj.querySelector('button');
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(input);
    }
    if (field.onenter !== '') {
        saltos.bootstrap.__onenter_helper(input, field.onenter);
    }
    if (field.onchange !== '') {
        saltos.bootstrap.__onchange_helper(input, field.onchange);
    }
    // Program the disabled feature
    input.set_disabled = bool => {
        if (bool) {
            input.setAttribute('disabled', '');
        } else {
            input.removeAttribute('disabled');
        }
    };
    // Program the button feature
    button.addEventListener('click', event => {
        switch (input.type) {
            case 'password':
                input.type = 'text';
                button.classList.replace('bi-eye-slash', 'bi-eye');
                break;
            case 'text':
                input.type = 'password';
                button.classList.replace('bi-eye', 'bi-eye-slash');
                break;
        }
    });
    obj.prepend(saltos.bootstrap.__label_helper(field));
    return obj;
};
