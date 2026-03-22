
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
 * @checkbox    => id, class, DS, RO, AK, label, value, tooltip, color, OC
 * @switch      => id, class, DS, RO, AK, label, value, tooltip, color, OC
 */

/**
 * Checkbox constructor helper
 *
 * This function returns a checkbox object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-check
 * @disabled  => this parameter raise the disabled flag
 * @readonly  => this parameter raise the readonly flag
 * @required  => this parameter raise the required flag
 * @label     => this parameter is used as label for the checkbox
 * @value     => this parameter is used to check or unckeck the checkbox, the value
 *               must contain a number that raise as true or false in the if condition
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onchange  => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget returns their value by setting a zero or one (0/1) value on the value of the input.
 */
saltos.bootstrap.__field.checkbox = field => {
    saltos.core.check_params(field, ['value', 'id', 'disabled', 'readonly', 'required', 'onchange',
                                     'label', 'tooltip', 'class', 'accesskey', 'color']);
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
    let value = 0;
    if (saltos.core.eval_bool(field.value)) {
        value = 1;
    }
    let checked = '';
    if (value) {
        checked = 'checked';
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    const obj = saltos.core.html(`
        <div class="form-check ${field.class}">
            <input class="form-check-input ${border} ${color}" type="checkbox" id="${field.id}"
                value="${value}" ${disabled} ${readonly} ${required} ${checked}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            <label class="form-check-label" for="${field.id}"
                data-bs-title="${field.tooltip}">${field.label}</label>
        </div>
    `);
    const element = obj.querySelector('input');
    if (field.tooltip !== '') {
        obj.querySelectorAll('input, label').forEach(item => {
            saltos.bootstrap.__tooltip_helper(item);
        });
    }
    if (field.onchange !== '') {
        saltos.bootstrap.__onchange_helper(element, field.onchange);
    }
    element.addEventListener('change', event => {
        event.target.value = event.target.checked ? 1 : 0;
    });
    element.set = bool => {
        if (saltos.core.eval_bool(bool)) {
            element.checked = true;
            element.value = 1;
        } else {
            element.checked = false;
            element.value = 0;
        }
    };
    // This add the colorized feature to the checkbox and switch
    obj.append(saltos.core.html(`
        <style>
            .form-check-input:checked.primary {
                background-color: var(--bs-primary);
            }
            .form-check-input:checked.secondary {
                background-color: var(--bs-secondary);
            }
            .form-check-input:checked.success {
                background-color: var(--bs-success);
            }
            .form-check-input:checked.danger {
                background-color: var(--bs-danger);
            }
            .form-check-input:checked.warning {
                background-color: var(--bs-warning);
            }
            .form-check-input:checked.info {
                background-color: var(--bs-info);
            }
            .form-check-input:disabled,
            .form-check-input[disabled],
            .form-check-input:disabled ~ .form-check-label,
            .form-check-input[disabled] ~ .form-check-label {
                opacity: 1;
            }
        </style>
    `));
    return obj;
};

/**
 * Switch constructor helper
 *
 * This function returns a switch object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-check and form-switch
 * @disabled  => this parameter raise the disabled flag
 * @readonly  => this parameter raise the readonly flag
 * @label     => this parameter is used as label for the switch
 * @value     => this parameter is used to check or unckeck the switch, the value
 *               must contain a number that raise as true or false in the if condition
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onchange  => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget uses the checkbox constructor
 */
saltos.bootstrap.__field.switch = field => {
    const obj = saltos.bootstrap.__field.checkbox(field);
    obj.classList.add('form-switch');
    obj.querySelector('input').setAttribute('role', 'switch');
    return obj;
};
