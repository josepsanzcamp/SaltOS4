
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
 * @button      => id, class, DS, AF, AK, label, onclick, tooltip, color, autoclose
 * @link        => id, DS, AK, value, onclick, tooltip, label, color
 */

/**
 * Button constructor helper
 *
 * This function returns a button object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @autofocus => this parameter raise the autofocus flag
 * @label     => label to be used as text in the contents of the buttons
 * @onclick   => callback function that is executed when the button is pressed
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @collapse  => a boolean to enable or disable the collapse feature in the button
 * @target    => the id of the element controlled by the collapse feature
 * @addbr     => this special feature adds a void label with a new line tag to align the button with
 *               the other elements that are label+widget
 *
 * Notes:
 *
 * The buttons adds the focus-ring classes to use this new feature that solves issues suck as
 * hidden focus when you try to focus a button inside a modal, for example.
 */
saltos.bootstrap.__field.button = field => {
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'autofocus', 'autoclose',
                                     'onclick', 'tooltip', 'icon', 'label', 'accesskey',
                                     'color', 'collapse', 'target', 'addbr', 'shadow', 'rounded']);
    let disabled = '';
    let opacity = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
        opacity = 'opacity-25';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let autoclose = '';
    if (saltos.core.eval_bool(field.autoclose)) {
        autoclose = 'autoclose';
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let collapse = '';
    if (saltos.core.eval_bool(field.collapse)) {
        collapse = `data-bs-toggle="collapse" data-bs-target="#${field.target}"
            aria-controls="${field.target}" aria-expanded="false"`;
    }
    let width = '';
    let height = '';
    if (field.class.includes('w-100')) {
        width = 'w-100';
    }
    if (field.class.includes('h-100')) {
        height = 'h-100';
    }
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded-pill';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    const obj = saltos.core.html(`
        <div class="${shadow} ${rounded} d-inline-block ${width} ${height}">
            <button type="button" id="${field.id}" ${disabled} ${autofocus} ${autoclose}
                class="btn btn-${color} ${rounded} focus-ring focus-ring-${color} ${field.class} ${opacity}"
                data-bs-accesskey="${field.accesskey}" ${collapse}
                data-bs-title="${field.tooltip}">${field.label}</button>
        </div>
    `);
    const button = obj.querySelector('button');
    if (field.icon !== '') {
        button.prepend(saltos.core.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.label !== '' && field.icon !== '') {
        button.querySelector('i').classList.add('me-1');
    }
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(button);
    }
    saltos.bootstrap.__onclick_helper(button, field.onclick);
    // Program the disabled feature
    button.set_disabled = bool => {
        if (bool) {
            button.setAttribute('disabled', '');
            button.classList.add('opacity-25');
        } else {
            button.removeAttribute('disabled');
            button.classList.remove('opacity-25');
        }
    };
    if (saltos.core.eval_bool(field.addbr)) {
        const temp = saltos.core.html(`<div><label class="form-label">&nbsp;</label><br/></div>`);
        temp.append(obj);
        return temp;
    }
    return obj;
};

/**
 * Link constructor helper
 *
 * This function creates a field similar of text but with the appearance of a link using a button,
 * the object can receive the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @autofocus => this parameter raise the autofocus flag
 * @label     => label to be used as text in the contents of the buttons
 * @onclick   => callback function that is executed when the button is pressed
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @collapse  => a boolean to enable or disable the collapse feature in the button
 * @target    => the id of the element controlled by the collapse feature
 * @addbr     => this special feature adds a void label with a new line tag to align the button with
 *               the other elements that are label+widget
 *
 * Notes:
 *
 * This object is not a real link, it's a button that uses the btn-link class to get the link
 * appearance
 */
saltos.bootstrap.__field.link = field => {
    field.color = 'link';
    const obj = saltos.bootstrap.__field.button(field).querySelector('button');
    return obj;
};
