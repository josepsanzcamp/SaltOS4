
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
 * @excel       => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth,
 *                 colWidths, label, color
 */

/**
 * Excel constructor helper
 *
 * This function creates and returns an excel object, to do this they use the handsontable library,
 * currently this library uses a privative license, by this reason, we are using the version 6.2.2
 * that is the latest release published using the MIT license.
 *
 * This widget can receive the following arguments:
 *
 * @id             => the id used to set the reference for to the object
 * @class          => allow to set the class to the div object used to allocate the widget
 * @data           => this parameter must contain a 2D matrix with the data that you want to show
 *                    in the sheet
 * @rowHeaders     => can be an array with the headers that you want to use instead the def numbers
 * @colHeaders     => can be an array with the headers that you want to use instead the def letters
 * @minSpareRows   => can be a number with the void rows at the end of the sheet
 * @contextMenu    => can be a boolean with the desired value to allow or not the provided
 *                    context menu of the widget
 * @rowHeaderWidth => can be a number with the width of the headers rows
 * @colWidths      => can be an array with the widths of the headers cols
 * @label          => this parameter is used as text for the label
 * @color          => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @afterChange    => the afterChange function that receives one argument (changes), a 2D array containing
 *                    information about each of the edited cells [[row, prop, oldVal, newVal], ...],
 *                    you can do something like changes.forEach(([row, prop, oldValue, newValue])
 * @autoWrapCol    => used as autoWrapCol in the handsontable widget
 * @autoWrapRow    => used as autoWrapRow in the handsontable widget
 *
 * Notes:
 *
 * You can get the values after to do changes by accessing to the data of the div used to create
 * the widget.
 *
 * This widget requires the handsontable library and can be loaded automatically using the require
 * feature:
 *
 * @lib/handsontable/handsontable.full.min.css
 * @lib/handsontable/handsontable.full.min.js
 */
saltos.bootstrap.__field.excel = field => {
    saltos.core.check_params(field, ['id', 'class', 'value', 'data', 'required', 'disabled',
                                     'rowHeaders', 'colHeaders', 'minSpareRows', 'height',
                                     'contextMenu', 'rowHeaderWidth', 'colWidths', 'color',
                                     'numcols', 'numrows', 'cell', 'cells', 'afterChange',
                                     'autoWrapCol', 'autoWrapRow', 'shadow', 'rounded']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = ['border', `border-${color}-subtle`];
    if (field.color === 'none') {
        border = ['border-0'];
    }
    let height = '100%';
    if (field.height !== '') {
        height = field.height;
    }
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let obj = saltos.core.html(`
        <div>
            <div></div>
        </div>
    `);
    obj.prepend(saltos.bootstrap.__field.hidden(saltos.core.copy_object(field)));
    const input = obj.querySelector('input');
    if (field.numcols === '') {
        field.numcols = 26;
    } else {
        field.numcols = parseInt(field.numcols, 10);
    }
    if (field.numrows === '') {
        field.numrows = 20;
    } else {
        field.numrows = parseInt(field.numrows, 10);
    }
    if (field.data === '') {
        field.data = [...Array(field.numrows)].map(e => Array(field.numcols));
    }
    if (field.rowHeaders === '') {
        field.rowHeaders = true;
    }
    if (field.colHeaders === '') {
        field.colHeaders = true;
    }
    if (field.minSpareRows === '') {
        field.minSpareRows = 0;
    }
    if (field.contextMenu === '') {
        field.contextMenu = false;
    }
    if (field.rowHeaderWidth === '') {
        field.rowHeaderWidth = undefined;
    } else {
        field.rowHeaderWidth = parseInt(field.rowHeaderWidth, 10);
    }
    if (typeof field.colWidths === 'string') {
        if (field.colWidths === '') {
            field.colWidths = undefined;
        } else if (saltos.core.is_number(field.colWidths)) {
            field.colWidths = parseInt(field.colWidths, 10);
        } else if (saltos.core.is_function(field.colWidths)) {
            field.colWidths = eval(field.colWidths);
        }
    }
    if (typeof field.cells === 'string') {
        if (field.cells === '') {
            field.cells = undefined;
        } else if (saltos.core.is_function(field.cells)) {
            field.cells = eval(field.cells);
        }
    }
    if (typeof field.afterChange === 'string') {
        if (saltos.core.is_function(field.afterChange)) {
            field.afterChange = eval(field.afterChange);
        }
    }
    input.data = saltos.core.copy_object(field.data);
    const element = obj.querySelector('div');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: color,
    });
    obj.append(placeholder);
    // Continue
    let options = {
        data: input.data, // This links the data
        rowHeaders: field.rowHeaders,
        colHeaders: field.colHeaders,
        minSpareRows: field.minSpareRows,
        contextMenu: field.contextMenu,
        rowHeaderWidth: field.rowHeaderWidth,
        colWidths: field.colWidths,
        autoWrapCol: field.autoWrapCol,
        autoWrapRow: field.autoWrapRow,
        cell: field.cell,
        cells: field.cells,
        afterChange: field.afterChange,
    };
    saltos.core.require([
        'lib/handsontable/handsontable.full.min.css',
        'lib/handsontable/handsontable.full.min.js',
        'lib/handsontable/handsontable.dark.min.css',
    ], () => {
        placeholder.remove();
        element.parentElement.classList.add('form-control', 'p-0', shadow, rounded, ...border);
        element.parentElement.style.height = height;
        element.parentElement.style.overflow = 'auto';
        const excel = new Handsontable(element, options);
        input.excel = excel;
    });
    // Program the disabled feature
    input.set_disabled = bool => {
        if (!('excel' in input)) {
            setTimeout(() => input.set_disabled(bool), 1);
            return;
        }
        input.excel.updateSettings({
            cells: (row, col, prop) => {
                let cell = {};
                for (let key in field.cell) {
                    const val = field.cell[key];
                    if (val.row === row && val.col === col) {
                        cell = saltos.core.copy_object(val);
                    }
                }
                if ('readOnly' in cell) {
                    // Nothing to do
                } else {
                    cell.readOnly = bool;
                }
                /*if ('className' in cell) {
                    // Nothing to do
                } else if ('readOnlyCellClassName' in cell) {
                    // Nothing to do
                } else if (bool) {
                    cell.readOnlyCellClassName = 'bg-body-secondary';
                } else {
                    cell.readOnlyCellClassName = '';
                }*/
                return cell;
            },
        });
    };
    if (saltos.core.eval_bool(field.disabled)) {
        input.set_disabled(true);
    }
    // Program the set in the input first
    input.set = value => {
        if (!('excel' in input)) {
            if (!('queue' in input)) {
                input.queue = [];
            }
            input.queue.push(value);
            if (!('timer' in input)) {
                input.timer = setInterval(() => {
                    if (!('excel' in input)) {
                        return;
                    }
                    clearInterval(input.timer);
                    while (input.queue.length) {
                        const item = input.queue.shift();
                        input.set(item);
                    }
                }, 1);
            }
            return;
        }
        if (Array.isArray(value)) {
            input.data = saltos.core.copy_object(value);
            options = {...options, data: input.data};
            input.excel.updateSettings(options);
        } else {
            input.data = saltos.core.copy_object(value.data);
            options = {...options, ...value, data: input.data};
            input.excel.updateSettings(options);
        }
    };
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
