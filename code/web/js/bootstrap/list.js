
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
 * @list        => id, class, header, extra, data, footer, onclick, active, disabled, label
 */

/**
 * List widget constructor helper
 *
 * Returns a list widget using the follow params:
 *
 * @id       => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default list-group
 * @onclick  => this parameter allow to enable or disable the buttons in the list
 * @data     => 2D array with the data used to mount the list
 * @truncate => this parameter add the text-truncate to all texts of the items
 * @checkbox => add a checkbox in the first cell of each row, for mono or multi selection
 * @nodata   => text used when no data is found
 * @label    => this parameter is used as text for the label
 *
 * Each item in the data can contain:
 *
 * @header   => string with the header to use
 * @body     => string with the data to use
 * @footer   => string with the footer to use
 * @onclick  => the onclick function that receives as argument the url to access the action
 * @url      => this parameter is used as argument for the onclick function
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 * @actions  => this parameter allow to recicle the actions feature of the list action
 * @id       => the id used to set the reference for each checkbox
 *
 * As an extra fields, the widget allow to provide multiple texts and icons
 *
 * @header_text  => an small text added at the end of the same line of the header
 * @header_icon  => an small icon added at the end of the same line of the header
 * @header_color => the color used in the previous small text and icon
 * @body_text    => an small text added at the end of the same line of the body
 * @body_icon    => an small icon added at the end of the same line of the body
 * @body_color   => the color used in the previous small text and icon
 * @footer_text  => an small text added at the end of the same line of the footer
 * @footer_icon  => an small icon added at the end of the same line of the footer
 * @footer_color => the color used in the previous small text and icon
 *
 * Notes:
 *
 * The first onclick parameter is used to raise the construction of the widget and items,
 * depending of this parameter, the function uses a dir or an ul element to do the list
 */
saltos.bootstrap.__field.list = field => {
    saltos.core.check_params(field, ['class', 'id', 'onclick', 'truncate',
                                     'checkbox', 'nodata', 'shadow', 'rounded']);
    saltos.core.check_params(field, ['data', 'actions'], []);
    // Check for data not found
    if (!field.data.length) {
        const obj = saltos.bootstrap.__field.alert({
            id: field.id,
            title: field.nodata,
            label: field.label,
        });
        obj.set = value => {
            const temp = saltos.bootstrap.__field.list({...field, ...value});
            obj.replaceWith(temp);
        };
        obj.add = value => {
            const suma = {...field, ...value, data: [...field.data, ...value.data]};
            const temp = saltos.bootstrap.__field.list(suma);
            obj.replaceWith(temp);
        };
        return obj;
    }
    // Continue
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    let rounded_bottom = 'rounded-bottom';
    if (field.rounded !== '') {
        rounded = field.rounded;
        rounded_bottom = field.rounded.replace('rounded', 'rounded-bottom');
    }
    let obj;
    if (saltos.core.eval_bool(field.onclick)) {
        obj = saltos.core.html(`
            <div id="${field.id}" class="list-group ${rounded} ${shadow} ${field.class}"></div>
        `);
    } else {
        obj = saltos.core.html(`
            <ul id="${field.id}" class="list-group ${rounded} ${shadow} ${field.class}"></ul>
        `);
    }
    for (const key in field.data) {
        const val = field.data[key];
        saltos.core.check_params(val, ['header', 'body', 'footer', 'class',
            'header_text', 'header_icon', 'header_color',
            'body_text', 'body_icon', 'body_color',
            'footer_text', 'footer_icon', 'footer_color',
            'onclick', 'arg', 'active', 'disabled', 'actions', 'id']);
        let item;
        if (saltos.core.eval_bool(field.onclick)) {
            item = saltos.core.html(`<button
                class="list-group-item list-group-item-action ${val.class}"></button>`);
            if (Object.keys(field.actions).length) {
                if (!('actions' in val)) {
                    val.actions = {};
                }
                const action = {
                    ...saltos.core.join_attr_value(Object.values(field.actions)[0]),
                    ...Object.values(val.actions)[0],
                };
                if ('onclick' in action && 'arg' in action) {
                    val.onclick = action.onclick;
                    val.arg = action.arg;
                }
            }
            if (val.arg !== '') {
                val.onclick = `${val.onclick}("${val.arg}")`;
            }
            saltos.bootstrap.__onclick_helper(item, val.onclick);
            if (saltos.core.eval_bool(field.checkbox)) {
                if (val.id === '') {
                    val.id = saltos.core.uniqid();
                }
                item.setAttribute('id', `button_${val.id}`);
            }
        } else {
            item = saltos.core.html(`<li class="list-group-item ${val.class}"></li>`);
        }
        if (val.header !== '') {
            const temp = saltos.core.html(`
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1 fw-normal ${val.class}"></h5>
                </div>
            `);
            temp.querySelector('h5').append(val.header);
            if (saltos.core.eval_bool(field.truncate)) {
                temp.querySelector('h5').classList.add('text-truncate');
            }
            let color = 'bg-secondary-subtle text-secondary-emphasis fw-normal';
            if (val.header_color !== '') {
                color = `text-bg-${val.header_color}`;
            }
            if (val.header_text !== '' && val.header_icon !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            ${val.header_text}
                            <i class="bi bi-${val.header_icon}"></i>
                        </span>
                    </div>
                `));
            } else if (val.header_text !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            ${val.header_text}
                        </span>
                    </div>
                `));
            } else if (val.header_icon !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            <i class="bi bi-${val.header_icon}"></i>
                        </span>
                    </div>
                `));
            }
            item.append(temp);
        }
        if (val.body !== '') {
            const temp = saltos.core.html(`
                <div class="d-flex w-100 justify-content-between">
                    <p class="mb-1"></p>
                </div>
            `);
            temp.querySelector('p').append(val.body);
            if (saltos.core.eval_bool(field.truncate)) {
                temp.querySelector('p').classList.add('text-truncate');
            }
            let color = 'bg-secondary-subtle text-secondary-emphasis fw-normal';
            if (val.body_color !== '') {
                color = `text-bg-${val.body_color}`;
            }
            if (val.body_text !== '' && val.body_icon !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            ${val.body_text}
                            <i class="bi bi-${val.body_icon}"></i>
                        </span>
                    </div>
                `));
            } else if (val.body_text !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            ${val.body_text}
                        </span>
                    </div>
                `));
            } else if (val.body_icon !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            <i class="bi bi-${val.body_icon}"></i>
                        </span>
                    </div>
                `));
            }
            item.append(temp);
        }
        if (val.footer !== '') {
            const temp = saltos.core.html(`
                <div class="d-flex w-100 justify-content-between">
                    <small></small>
                </div>
            `);
            temp.querySelector('small').append(val.footer);
            if (saltos.core.eval_bool(field.truncate)) {
                temp.querySelector('small').classList.add('text-truncate');
            }
            let color = 'bg-secondary-subtle text-secondary-emphasis fw-normal';
            if (val.footer_color !== '') {
                color = `text-bg-${val.footer_color}`;
            }
            if (val.footer_text !== '' && val.footer_icon !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            ${val.footer_text}
                            <i class="bi bi-${val.footer_icon}"></i>
                        </span>
                    </div>
                `));
            } else if (val.footer_text !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            ${val.footer_text}
                        </span>
                    </div>
                `));
            } else if (val.footer_icon !== '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap ms-1">
                        <span class="badge rounded-pill ${color}">
                            <i class="bi bi-${val.footer_icon}"></i>
                        </span>
                    </div>
                `));
            }
            item.append(temp);
        }
        if (saltos.core.eval_bool(val.active)) {
            item.classList.add('active');
            item.setAttribute('aria-current', 'true');
        }
        if (saltos.core.eval_bool(val.disabled)) {
            item.classList.add('disabled');
            item.setAttribute('aria-disabled', 'true');
        }
        obj.append(item);
    }
    if (obj.lastElementChild) {
        obj.lastElementChild.classList.add(rounded_bottom);
    }
    // The follow code allow to colorize the hover and active rows of the list
    // The --bs-body-color is used as main color here from bootstrap 5.3.5
    obj.append(saltos.core.html(`
        <style>
            .list-group {
                --bs-list-group-action-hover-bg: #fbec88;
                --bs-list-group-action-active-bg: #fbec88;
                --bs-list-group-action-hover-color: #373a3c;
                --bs-list-group-action-active-color: #373a3c;
                --bs-list-group-action-color: --var(--bs-body-color);
                --bs-list-group-active-bg: #fbec88;
                --bs-list-group-active-color: #373a3c;
                --bs-list-group-active-border-color: var(--bs-list-group-border-color);
            }
        </style>
    `));
    // The follow code colorize the rows of the table using the main color subtle class
    obj.append(saltos.core.html(`
        <style>
            .list-group-item:nth-child(odd) {
                --bs-list-group-bg: rgba(var(--bs-emphasis-color-rgb), 0.05);
            }
        </style>
    `));
    // The follow code allow to fix the color buttons in dark mode
    obj.append(saltos.core.html(`
        <style>
            .list-group-item.active h5 {
                color: inherit;
            }
            .list-group-item.active [class^="text-"] {
                color: inherit!important;
            }
        </style>
    `));
    if (saltos.core.eval_bool(field.checkbox)) {
        saltos.core.when_visible(obj, () => {
            obj.classList.add('position-relative');
            for (const key in field.data) {
                const val = field.data[key];
                obj.append(saltos.core.html(`
                    <div class="position-absolute p-2">
                        <input class="form-check-input" type="checkbox"
                            value="${val.id}" id="checkbox_${val.id}">
                    </div>
                `));
                const button = obj.querySelector(`#button_${val.id}`);
                const checkbox = obj.querySelector(`#checkbox_${val.id}`);
                checkbox.parentElement.style.height = button.offsetHeight + 'px';
                checkbox.parentElement.style.top = button.offsetTop + 'px';
                const width = checkbox.parentElement.offsetWidth;
                button.style.paddingLeft = width + 'px';
                checkbox.parentElement.style.zIndex = 201;
                button.style.zIndex = 200;
                checkbox.addEventListener('change', event => {
                    const button = event.target.id.replace('checkbox', 'button');
                    if (event.target.checked) {
                        document.getElementById(button).style.background =
                            'var(--bs-list-group-action-active-bg)';
                        document.getElementById(button).style.color =
                            'var(--bs-list-group-action-active-color)';
                    } else {
                        document.getElementById(button).style.background = '';
                        document.getElementById(button).style.color = '';
                    }
                });
                checkbox.addEventListener('click', event => {
                    // Here program the multiple selection feature using the ctrlKey
                    if (!event.altKey && !event.ctrlKey && !event.shiftKey) {
                        // First state, sets the id1
                        saltos.bootstrap.__checkbox_id1 = event.target.value;
                        saltos.bootstrap.__checkbox_id2 = null;
                    } else {
                        // Second state, sets the id2
                        saltos.bootstrap.__checkbox_id2 = event.target.value;
                    }
                    if (saltos.bootstrap.__checkbox_id1 && saltos.bootstrap.__checkbox_id2) {
                        const obj = event.target.parentElement.parentElement;
                        const nodes = obj.querySelectorAll('input[type=checkbox][value]');
                        const ids = [saltos.bootstrap.__checkbox_id1, saltos.bootstrap.__checkbox_id2];
                        // Check that the two ids are presents
                        let count = 0;
                        nodes.forEach(item => {
                            if (ids.includes(item.value)) {
                                count++;
                            }
                        });
                        // If the two ids are present, then apply
                        if (count === 2) {
                            let found = false;
                            nodes.forEach(item => {
                                if (ids.includes(item.value)) {
                                    found = !found;
                                }
                                if (found) {
                                    if (!item.checked) {
                                        item.click();
                                    }
                                }
                            });
                        }
                        // Reset the ids to restart the state machine
                        saltos.bootstrap.__checkbox_id1 = null;
                        saltos.bootstrap.__checkbox_id2 = null;
                    }
                    event.stopPropagation();
                });
                checkbox.parentElement.addEventListener('click', event => {
                    const obj = event.target.querySelector('input[type=checkbox]');
                    if (obj) {
                        // ctrlKey propagation is important to allow the multiple selection feature
                        obj.dispatchEvent(new MouseEvent('click', {
                            altKey: event.altKey,
                            ctrlKey: event.ctrlKey,
                            shiftKey: event.shiftKey,
                        }));
                        // The next focus allow to continue navigating by the other checkboxes
                        obj.focus();
                    }
                    event.stopPropagation();
                });
            }
        });
    }
    // Program the set and the add in the table
    obj.set = value => {
        const temp = saltos.bootstrap.__field.list({...field, ...value});
        obj.replaceWith(temp);
    };
    obj.add = value => {
        const suma = {...field, ...value, data: [...field.data, ...value.data]};
        const temp = saltos.bootstrap.__field.list(suma);
        obj.replaceWith(temp);
    };
    // Continue
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
