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
 * Rating widget example
 *
 * This file is an example of a widget provided by an application, each widget is registered
 * in saltos.bootstrap.__field using the name of the type, and since then, it can be used in
 * the layouts as any other widget of SaltOS, for example, <rating id="xxx"/>.
 *
 * The data input fields must store in element.saltos the object returned by the helper
 * saltos.bootstrap.__saltos_helper, and replace the features that need a different behavior,
 * in this case, the framework (get_data, check_required, form_disabled, form.data and reset)
 * only uses these features and does not need to know anything about the new widgets.
 */

/**
 * Rating constructor helper
 *
 * This function returns an object of the rating type, it is a data input field that shows
 * five stars and stores the selected value (from 1 to 5) in a hidden input.
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the stars container
 * @value    => the value used as value parameter (from 1 to 5, void or 0 for no stars)
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @tooltip  => this parameter raise the title flag
 * @label    => this parameter is used as text for the label
 * @color    => the color of the stars (primary by default)
 *
 * Notes:
 *
 * This widget programs all features of the data input fields: set_value paints the stars,
 * set_disabled disables the buttons of the stars, get_data normalizes the values as integers
 * and check_required colorizes the stars container and focuses the first star.
 */
saltos.bootstrap.__field.rating = field => {
    saltos.core.check_params(field, ['id', 'class', 'value', 'disabled', 'required',
                                     'tooltip', 'label', 'color']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    const obj = saltos.core.html(`
        <div>
            <input type="hidden" id="${field.id}" value="${field.value}" />
            <div>
                <div class="rating d-inline-flex gap-2 px-2 rounded ${field.class}"
                    data-bs-title="${field.tooltip}"></div>
            </div>
        </div>
    `);
    const input = obj.querySelector('input');
    const stars = obj.querySelector('.rating');
    // The hidden input is the data input field, and it receives the default features
    input.saltos = saltos.bootstrap.__saltos_helper(input, field);
    for (let i = 1; i <= 5; i++) {
        const star = saltos.core.html(`
            <button type="button" class="btn btn-link p-0 fs-4 text-${color} bi-star"
                aria-label="${i}"></button>
        `);
        star.addEventListener('click', () => {
            input.saltos.set_value(i);
        });
        stars.append(star);
    }
    // Program the set_value feature, that stores the value and paints the stars
    input.saltos.set_value = value => {
        value = parseInt(value, 10);
        if (!saltos.core.is_number(value)) {
            value = 0;
        }
        input.value = value ? value : '';
        stars.querySelectorAll('button').forEach((star, index) => {
            star.classList.toggle('bi-star-fill', index < value);
            star.classList.toggle('bi-star', index >= value);
        });
    };
    // Program the set_disabled feature, that disables the buttons of the stars
    input.saltos.set_disabled = bool => {
        stars.querySelectorAll('button').forEach(star => {
            star.disabled = bool;
        });
    };
    // Program the get_data feature, the void value and 0 are the same value
    input.saltos.get_data = () => {
        let val = parseInt(input.value, 10);
        let old = parseInt(input.saltos.old_value, 10);
        if (!saltos.core.is_number(val)) {
            val = 0;
        }
        if (!saltos.core.is_number(old)) {
            old = 0;
        }
        return [val, old];
    };
    // Program the check_required feature, the hidden input can not be colorized or focused
    input.saltos.check_required = () => ({
        value: input.value,
        color: stars,
        focus: stars.querySelector('button'),
    });
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(stars);
    }
    // Initialize the widget using the previous features
    input.saltos.set_value(field.value);
    if (saltos.core.eval_bool(field.disabled)) {
        input.saltos.set_disabled(true);
    }
    obj.prepend(saltos.bootstrap.__label_helper(field));
    return obj;
};
