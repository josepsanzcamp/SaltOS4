
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
 * @table       => id, class, header, data, footer, value, label, color
 */

/**
 * Table constructor helper
 *
 * Returns a table using the follow params:
 *
 * @id       => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default table table-striped table-hover
 * @header   => array with the header to use
 * @data     => 2D array with the data used to mount the body table
 * @footer   => array with the footer to use
 * @checkbox => add a checkbox in the first cell of each row, for mono or multi selection
 * @dropdown => a boolean value to force the usage of the dropdown feature, void for auto detection
 * @label    => this parameter is used as text for the label
 * @color    => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @nodata   => text used when no data is found
 * @actions  => each row of the data can contain an array with the actions of each row
 *
 * Each action can contain:
 *
 * @app     => the application that must be used to check the permissions
 * @action  => the accion that must to be used to check the permissions
 * @value   => the text used as label in the button of the action
 * @icon    => the icon used in the button of the action
 * @tooltip => the tooltip used in the button of the action
 * @onclick => the onclick function that receives as argument the arg to access the action
 *
 * Notes:
 *
 * This function defines the yellow color used for the hover and active rows.
 *
 * The header field must be an object with the labels, types, aligns, ..., of each field,
 * if the header is ommited, then the data will be painted using the default order of the
 * data without filters, the recomendation is to use header to specify which fields must
 * to be painted, the order, the type and the alignment.
 *
 * The actions will be added using a dropdown menu if more than one action appear in the
 * the row data, the idea of this feature is to prevent that the icons uses lot of space
 * of the row data, and for this reason, it will define the dropdown variable that enables
 * or not the contraction feature
 *
 * The elements of the data cells can contains an object with the field specification used
 * to the saltos.bootstrap.field constructor, it is useful to convert some fields to inputs
 * instead of simple text, too is able to use the type attribute in the header specification
 * to identify if you want to use a column with some special type as for example, the icons
 */
saltos.bootstrap.__field.table = field => {
    saltos.core.check_params(field, ['class', 'id', 'checkbox', 'dropdown',
                                     'color', 'nodata', 'shadow', 'rounded']);
    saltos.core.check_params(field, ['header', 'data', 'footer', 'actions'], []);
    saltos.core.check_params(field, ['first_action'], true);
    if (field.checkbox !== '') {
        field.checkbox = saltos.core.eval_bool(field.checkbox);
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    // This creates a responsive table (a table inside a div with table-responsive class)
    // We are using the same div to put inside the overlodaded styles of the table
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="form-control ${rounded} p-0 border-0 ${shadow} table-responsive">
            <table class="table table-striped table-hover border-${color}-subtle ${field.class} mb-0">
            </table>
        </div>
    `);
    obj.querySelector('table').append(saltos.core.html('table', `
        <thead>
            <tr>
            </tr>
        </thead>
    `));
    if (Object.keys(field.header).length) {
        if (field.checkbox) {
            obj.querySelector('thead tr').append(saltos.core.html(
                'tr',
                `<th class="bg-${color}-subtle" style="width: 1%"><input type="checkbox" /></th>`
            ));
            obj.querySelector('thead input[type=checkbox]').addEventListener('change', event => {
                const item = event.target;
                obj.querySelectorAll('tbody input[type=checkbox]').forEach(item2 => {
                    if (item2.checked !== item.checked) {
                        item2.click();
                    }
                });
            });
            obj.querySelector('thead input[type=checkbox]').addEventListener('click', event => {
                event.stopPropagation();
            });
            obj.querySelector('thead input[type=checkbox]').parentElement.addEventListener('click', event => {
                event.target.querySelector('input[type=checkbox]').click();
                event.stopPropagation();
            });
        }
        for (const key in field.header) {
            field.header[key] = saltos.core.join_attr_value(field.header[key]);
            const val = field.header[key];
            let th;
            if (typeof val === 'object' && val !== null) {
                th = saltos.core.html('tr', `<th class="bg-${color}-subtle">${val.label}</th>`);
                if ('align' in val) {
                    th.classList.add('text-' + val.align);
                }
                if ('order' in val && saltos.core.eval_bool(val.order)) {
                    th.classList.add('text-nowrap');
                    let caret_asc = 'bi-caret-up';
                    let active_asc = 'false';
                    if (field.order === `${key} ASC`) {
                        caret_asc = 'bi-caret-up-fill';
                        active_asc = 'true';
                    }
                    let caret_desc = 'bi-caret-down';
                    let active_desc = 'false';
                    if (field.order === `${key} DESC`) {
                        caret_desc = 'bi-caret-down-fill';
                        active_desc = 'true';
                    }
                    th.append(saltos.core.html(`
                        <button class="btn border-0 p-0 ms-1">
                            <i class="bi ${caret_asc}" data-active="${active_asc}"></i></button>`));
                    th.append(saltos.core.html(`
                        <button class="btn border-0 p-0">
                            <i class="bi ${caret_desc}" data-active="${active_desc}"></i></button>`));
                    th.querySelectorAll('i').forEach(icon => {
                        if (icon.dataset.active === 'true') {
                            return;
                        }
                        icon.addEventListener('mouseenter', () => {
                            icon.classList.forEach(cls => {
                                if (cls.includes('caret') && !cls.includes('-fill')) {
                                    icon.classList.replace(cls, cls + '-fill');
                                }
                            });
                        });
                        icon.addEventListener('mouseleave', () => {
                            icon.classList.forEach(cls => {
                                if (cls.includes('caret') && cls.includes('-fill')) {
                                    icon.classList.replace(cls, cls.replace('-fill', ''));
                                }
                            });
                        });
                    });
                    const buttons = th.querySelectorAll('button');
                    buttons[0].addEventListener('click', () => {
                        const order = document.getElementById('order');
                        if (!order) {
                            return;
                        }
                        order.value = `${key} ASC`;
                        order.dispatchEvent(new Event('change'));
                    });
                    buttons[1].addEventListener('click', () => {
                        const order = document.getElementById('order');
                        if (!order) {
                            return;
                        }
                        order.value = `${key} DESC`;
                        order.dispatchEvent(new Event('change'));
                    });
                }
                if ('width' in val) {
                    th.style.width = val.width;
                }
            } else {
                th = saltos.core.html('tr', `<th class="bg-${color}-subtle">${val}</th>`);
            }
            obj.querySelector('thead tr').append(th);
        }
        if (Object.keys(field.actions).length) {
            const th = saltos.core.html('tr', `<th class="bg-${color}-subtle" style="width: 1%"></th>`);
            obj.querySelector('thead tr').append(th);
        }
    } else {
        obj.querySelector('thead tr').append(saltos.core.html(
            'tr',
            `<th class="bg-${color}-subtle text-center" colspan="100">&nbsp;</th>`
        ));
    }
    obj.querySelector('table').append(saltos.core.html('table', `
        <tbody>
        </tbody>
    `));
    if (field.data.length) {
        // This function close all dropdowns
        const dropdown_close = () => {
            obj.querySelectorAll('.show').forEach(item => {
                item.classList.remove('show');
            });
        };
        for (const key in field.data) {
            const val = field.data[key];
            const row = saltos.core.html('tbody', `<tr class="align-middle"></tr>`);
            const id2 = `${field.id}_${val.id}`.replaceAll('/', '_');
            row.setAttribute('id', id2);
            if (field.checkbox) {
                row.append(saltos.core.html('tr', `<td><input type="checkbox" value="${val.id}" /></td>`));
                row.querySelector('input[type=checkbox]').addEventListener('change', event => {
                    event.target.parentElement.parentElement.querySelectorAll('td').forEach(item => {
                        if (event.target.checked) {
                            item.classList.add('table-active');
                        } else {
                            item.classList.remove('table-active');
                        }
                    });
                    dropdown_close();
                });
                row.querySelector('input[type=checkbox]').addEventListener('click', event => {
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
                        const obj = event.target.parentElement.parentElement.parentElement;
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
                row.addEventListener('click', event => {
                    const obj = event.target.parentElement.querySelector('input[type=checkbox]');
                    if (obj) {
                        // ctrlKey propagation is important to allow the multiple selection feature
                        obj.dispatchEvent(new MouseEvent('click', {
                            altKey: event.altKey,
                            ctrlKey: event.ctrlKey,
                            shiftKey: event.shiftKey,
                        }));
                    }
                    event.stopPropagation();
                });
            }
            // This is to allow to use tables with data and without header
            let iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = val;
            }
            for (const key2 in iterator) {
                let val2 = val[key2];
                const td = saltos.core.html('tr', `<td></td>`);
                if (typeof val2 === 'object' && val2 !== null) {
                    if ('type' in val2) {
                        const temp = saltos.bootstrap.field(val2);
                        td.append(temp);
                    } else {
                        const temp = `object without type`;
                        td.append(temp);
                    }
                } else {
                    if (val2 !== null) {
                        val2 = saltos.core.toString(val2);
                    }
                    let type = 'text';
                    if (typeof iterator[key2] === 'object' && 'type' in iterator[key2]) {
                        type = iterator[key2].type;
                    }
                    switch (type) {
                        case 'icon':
                            if (val2) {
                                const temp = saltos.core.html(`<i class="bi bi-${val2}"></i>`);
                                td.append(temp);
                            }
                            break;
                        case 'html':
                            if (val2) {
                                const temp = saltos.core.html(val2);
                                td.append(temp);
                            }
                            break;
                        case 'link':
                            if (val2) {
                                const temp = saltos.core.html(`<a href="${val2}">${val2}</a>`);
                                temp.setAttribute('target', '_blank');
                                td.append(temp);
                            }
                            break;
                        case 'text':
                            if (val2) {
                                td.append(val2);
                            }
                            break;
                        default:
                            const temp = `unknown type ${type}`;
                            td.append(temp);
                            break;
                    }
                }
                if (typeof iterator[key2] === 'object' && 'align' in iterator[key2]) {
                    td.classList.add('text-' + iterator[key2].align);
                }
                if (typeof iterator[key2] === 'object' && 'class' in iterator[key2]) {
                    if (iterator[key2].class in val) {
                        if (val[iterator[key2].class] !== '') {
                            td.classList.add('bg-' + val[iterator[key2].class] + '-subtle');
                        }
                    }
                }
                row.append(td);
            }
            if (Object.keys(field.actions).length) {
                const td = saltos.core.html('tr', `<td class="p-0 text-nowrap"></td>`);
                let dropdown = Object.keys(field.actions).length > 1;
                if (field.dropdown !== '') {
                    dropdown = saltos.core.eval_bool(field.dropdown);
                }
                if (dropdown) {
                    td.append(saltos.core.html(`
                        <div>
                            <button class="btn border-0 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            </button>
                            <ul class="dropdown-menu ${rounded} ${shadow}">
                            </ul>
                        </div>
                    `));
                    // This close all dropdowns when a new dropdown appear
                    td.querySelector('ul').parentElement.addEventListener('show.bs.dropdown', dropdown_close);
                }
                let first_action = saltos.core.eval_bool(field.first_action);
                if (!('actions' in val)) {
                    val.actions = {};
                }
                for (const key2 in field.actions) {
                    const val2 = {
                        ...saltos.core.join_attr_value(field.actions[key2]),
                        ...val.actions[key2],
                    };
                    if (!('arg' in val2) || val2.arg === '') {
                        val2.disabled = true;
                    } else {
                        if (!('onclick' in val2)) {
                            throw new Error('Table onclick not found');
                        }
                        val2.onclick = `${val2.onclick}("${val2.arg}")`;
                    }
                    if (first_action) {
                        if (val2.onclick) {
                            row.dataset.onclick = val2.onclick;
                            row.addEventListener('dblclick', event => {
                                (new Function(
                                    event.target.parentElement.dataset.onclick
                                )).call(event.target);
                                if (window.getSelection) {
                                    window.getSelection().removeAllRanges();
                                }
                            });
                        }
                        first_action = false;
                    }
                    if ('color' in val2) {
                        val2.class = `text-${val2.color}`;
                    }
                    val2.color = 'none';
                    val2.rounded = 'rounded-0';
                    const button = saltos.bootstrap.__field.button(val2).querySelector('button');
                    if (dropdown) {
                        button.classList.replace('btn', 'dropdown-item');
                        // This close all dropdowns when click an option inside a dropdown
                        button.addEventListener('click', dropdown_close);
                        const li = saltos.core.html(`<li></li>`);
                        li.append(button);
                        td.querySelector('ul').append(li);
                    } else {
                        button.classList.add('border-0');
                        td.append(button);
                    }
                }
                row.append(td);
            }
            obj.querySelector('tbody').append(row);
        }
    } else {
        if (field.nodata === '') {
            field.nodata = '&nbsp;';
        }
        obj.querySelector('tbody').append(saltos.core.html('tbody', `
            <tr class="align-middle text-center">
                <td colspan="100">${field.nodata}</td>
            </tr>
        `));
    }
    if (Object.keys(field.footer).length) {
        obj.querySelector('table').append(saltos.core.html('table', `
            <tfoot>
                <tr>
                </tr>
            </tfoot>
        `));
        if (typeof field.footer === 'object') {
            if (Object.keys(field.header).length !== Object.keys(field.footer).length) {
                throw new Error('Table field.header.length !== field.footer.length');
            }
            if (field.checkbox) {
                obj.querySelector('tfoot tr').append(saltos.core.html(
                    'tr',
                    `<td class="bg-${color}-subtle border-0"></td>`
                ));
            }
            // This is to allow to use tables with footer and without header
            let iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = field.footer;
            }
            for (const key in iterator) {
                field.footer[key] = saltos.core.join_attr_value(field.footer[key]);
                const val = field.footer[key];
                let td;
                if (typeof val === 'object' && val !== null) {
                    td = saltos.core.html(
                        'tr',
                        `<td class="bg-${color}-subtle border-0">${val.value}</td>`
                    );
                } else {
                    td = saltos.core.html(
                        'tr',
                        `<td class="bg-${color}-subtle border-0">${val}</td>`
                    );
                }
                if (typeof iterator[key] === 'object' && 'align' in iterator[key]) {
                    td.classList.add('text-' + iterator[key].align);
                }
                obj.querySelector('tfoot tr').append(td);
            }
            if (Object.keys(field.actions).length) {
                obj.querySelector('tfoot tr').append(saltos.core.html(
                    'tr',
                    `<td class="bg-${color}-subtle border-0"></td>`
                ));
            }
        }
        if (typeof field.footer === 'string') {
            obj.querySelector('tfoot tr').append(saltos.core.html(
                'tr',
                `<td colspan="100" class="text-center bg-${color}-subtle border-0">${field.footer}</td>`
            ));
        }
    }
    // The follow code allow to colorize the hover and active rows of the table
    obj.append(saltos.core.html(`
        <style>
            .table td {
                --bs-table-hover-bg: #fbec88;
                --bs-table-active-bg: #fbec88;
                --bs-table-hover-color: #373a3c;
                --bs-table-active-color: #373a3c;
            }
        </style>
    `));
    // The follow code allow to fix the color buttons in dark mode
    obj.append(saltos.core.html(`
        <style>
            .table tr:hover td > button,
            .table tr:active td > button,
            .table td.table-active > button,
            .table tr:hover td > div > button,
            .table tr:active td > div > button,
            .table td.table-active > div > button {
                color: #373a3c !important;
                transition: none !important;
            }
        </style>
    `));
    // The follow code allow to fix a button size issue with small tables
    obj.append(saltos.core.html(`
        <style>
            .table-sm button {
                padding-top: 0;
                padding-bottom: 0;
            }
        </style>
    `));
    // Program the set and the add in the table
    obj.set = value => {
        const temp = saltos.bootstrap.__field.table({...field, ...value});
        obj.replaceWith(temp);
    };
    obj.add = value => {
        const suma = {...field, ...value, data: [...field.data, ...value.data]};
        const temp = saltos.bootstrap.__field.table(suma);
        obj.replaceWith(temp);
    };
    // Continue
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
