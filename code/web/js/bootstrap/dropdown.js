
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
 */

/**
 * Dropdown constructor helper
 *
 * This function returns a dropdown object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default btn-group
 * @disabled  => this parameter raise the disabled flag
 * @label     => label to be used as text in the contents of the buttons
 * @onclick   => callback function that is executed when the button is pressed
 * @split     => to use a split button instead of single button
 * @tooltip   => this parameter raise the title flag
 * @icon      => the icon used in the main button
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @menu      => with this option, you can specify an array with the contents of the dropdown menu
 *
 * @label     => label of the menu
 * @icon      => icon of the menu
 * @disabled  => this boolean allow to disable this menu entry
 * @active    => this boolean marks the option as active
 * @onclick   => the callback used when the user select the menu
 * @divider   => you can set this boolean to true to convert the element into a divider
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the item (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * The tooltip can not be applied to the dropdown button because causes an internal error,
 * in this case, the tooltip only are applied in the first button of the split button and
 * all items of the menu, as brief, tootip only can be applied in all real actions buttons
 * and not in the dropdown button that opens the real dropdown menu
 */
saltos.bootstrap.__field.dropdown = field => {
    saltos.core.check_params(field, ['id', 'class', 'disabled', 'label', 'onclick', 'split',
                                     'tooltip', 'icon', 'accesskey', 'color', 'shadow', 'rounded']);
    saltos.core.check_params(field, ['menu'], []);
    // Check for main attributes
    let disabled = '';
    let opacity = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
        opacity = 'opacity-25';
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    // Create the main object
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded-pill';
    let rounded_start = 'rounded-start-pill';
    let rounded_end = 'rounded-end-pill';
    let rounded_menu = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
        rounded_start = field.rounded.replace('rounded', 'rounded-start');
        rounded_end = field.rounded.replace('rounded', 'rounded-end');
        rounded_menu = field.rounded.replace('rounded-pill', 'rounded');
    }
    let obj;
    if (!saltos.core.eval_bool(field.split)) {
        obj = saltos.core.html(`
            <div class="btn-group ${rounded} ${shadow} ${field.class}" id="${field.id}">
                <button type="button" ${disabled}
                    class="btn ${rounded} btn-${color} ${opacity} dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}">
                        ${field.label}
                </button>
            </div>
        `);
    } else {
        obj = saltos.core.html(`
            <div class="btn-group ${rounded} ${shadow} ${field.class}" id="${field.id}">
                <button type="button" ${disabled}
                    class="btn ${rounded_start} btn-${color} ${opacity}"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}">
                        ${field.label}
                </button>
                <button type="button" ${disabled}
                    class="btn ${rounded_end} btn-${color} ${opacity} dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                </button>
            </div>
        `);
        saltos.bootstrap.__onclick_helper(obj.querySelector('button'), field.onclick);
        if (field.tooltip !== '') {
            saltos.bootstrap.__tooltip_helper(obj.querySelector('button'));
        }
    }
    // Add the icon and tooltip
    if (field.icon !== '') {
        obj.querySelector('button').prepend(saltos.core.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.label !== '' && field.icon !== '') {
        obj.querySelector('i').classList.add('me-1');
    }
    obj.append(saltos.core.html(`<ul class="dropdown-menu ${rounded_menu} ${shadow}"></ul>`));
    // Add the dropdown items
    for (const key in field.menu) {
        const val = field.menu[key];
        saltos.core.check_params(val, ['id', 'label', 'icon', 'disabled', 'active',
                                       'onclick', 'divider', 'tooltip', 'accesskey', 'color']);
        let disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        let active = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
        }
        let color = '';
        if (val.color  !== '') {
            color = `text-${val.color}`;
        }
        let temp;
        if (saltos.core.eval_bool(val.divider)) {
            temp = saltos.core.html(`<li><hr class="dropdown-divider"></li>`);
        } else {
            temp = saltos.core.html(`
                <li><button id="${val.id}" class="dropdown-item ${disabled} ${active} ${color}"
                    data-bs-accesskey="${val.accesskey}" data-bs-title="${val.tooltip}">
                        ${val.label}
                </button></li>`);
            if (val.icon) {
                temp.querySelector('button').prepend(
                    saltos.core.html(`<i class="bi bi-${val.icon}"></i>`));
            }
            if (val.label && val.icon) {
                temp.querySelector('i').classList.add('me-1');
            }
            if (val.tooltip !== '') {
                saltos.bootstrap.__tooltip_helper(temp.querySelector('button'));
            }
            if (!saltos.core.eval_bool(val.disabled)) {
                saltos.bootstrap.__onclick_helper(temp.querySelector('button'), val.onclick);
            }
        }
        obj.querySelector('ul').append(temp);
    }
    return obj;
};
