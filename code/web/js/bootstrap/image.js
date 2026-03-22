
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
 * @image       => id, class, value, alt, tooltip, width, height, label, color
 */

/**
 * Image constructor helper
 *
 * This function returns an image object, you can pass some arguments as:
 *
 * @id      => the id used to set the reference for to the object
 * @class   => allow to add more classes to the default img-fluid
 * @value   => the value used as src parameter
 * @alt     => this parameter is used as text for the alt parameter
 * @tooltip => this parameter raise the title flag
 * @label   => this parameter is used as text for the label
 * @width   => this parameter is used as width for the image
 * @height  => this parameter is used as height for the image
 * @color   => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @invert  => enable the invert feature in the widget contents
 */
saltos.bootstrap.__field.image = field => {
    saltos.core.check_params(field, ['id', 'class', 'value', 'alt', 'tooltip', 'invert',
                                     'width', 'height', 'color', 'shadow', 'rounded']);
    let _class = 'img-fluid';
    if (field.class !== '') {
        _class = field.class;
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <div class="form-control ${rounded} p-0 ${shadow} ${border}">
            <img id="${field.id}" src="${field.value}" alt="${field.alt}" class="${rounded} ${_class}"
                width="${field.width}" height="${field.height}" data-bs-title="${field.tooltip}" />
        </div>
    `);
    const element = obj.querySelector('img');
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(element);
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
    // Fix for dark mode
    if (saltos.core.eval_bool(field.invert)) {
        const button_id = field.id + '_dark';
        const button_value = saltos.bootstrap.__button_value_helper(field.id);
        if (button_value) {
            element.style.filter = 'invert(.9)';
        }
        const button = saltos.bootstrap.field({
            id: button_id,
            type: 'switch',
            class: 'float-end',
            color: color,
            value: button_value,
            onchange: event => {
                const bool = button.querySelector('input').checked;
                if (bool) {
                    element.style.filter = 'invert(.9)';
                } else {
                    element.style.filter = '';
                }
                if (event.isTrusted) {
                    const button_key = saltos.bootstrap.__button_key_helper(field.id);
                    saltos.storage.setItem(button_key, bool);
                }
            },
        });
        button.querySelector('input').style.marginLeft = '0px';
        obj.prepend(button);
        new MutationObserver(() => {
            const button_value = saltos.bootstrap.__button_value_helper(field.id);
            button.querySelector('input').checked = button_value;
            button.querySelector('input').dispatchEvent(new Event('change'));
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });
    }
    return obj;
};
