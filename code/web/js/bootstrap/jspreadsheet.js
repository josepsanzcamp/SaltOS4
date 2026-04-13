
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
 * @handsontable => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth,
 *                  colWidths, label, color
 */

/**
 * TODO
 */
saltos.bootstrap.__field.jspreadsheet = field => {
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
    //~ if (field.rowHeaders === '') {
        //~ field.rowHeaders = true;
    //~ }
    //~ if (field.colHeaders === '') {
        //~ field.colHeaders = true;
    //~ }
    //~ if (field.minSpareRows === '') {
        //~ field.minSpareRows = 0;
    //~ }
    //~ if (field.contextMenu === '') {
        //~ field.contextMenu = false;
    //~ }
    //~ if (field.rowHeaderWidth === '') {
        //~ field.rowHeaderWidth = undefined;
    //~ } else {
        //~ field.rowHeaderWidth = parseInt(field.rowHeaderWidth, 10);
    //~ }
    //~ if (typeof field.colWidths === 'string') {
        //~ if (field.colWidths === '') {
            //~ field.colWidths = undefined;
        //~ } else if (saltos.core.is_number(field.colWidths)) {
            //~ field.colWidths = parseInt(field.colWidths, 10);
        //~ } else if (saltos.core.is_function(field.colWidths)) {
            //~ field.colWidths = eval(field.colWidths);
        //~ }
    //~ }
    //~ if (typeof field.cells === 'string') {
        //~ if (field.cells === '') {
            //~ field.cells = undefined;
        //~ } else if (saltos.core.is_function(field.cells)) {
            //~ field.cells = eval(field.cells);
        //~ }
    //~ }
    //~ if (typeof field.afterChange === 'string') {
        //~ if (saltos.core.is_function(field.afterChange)) {
            //~ field.afterChange = eval(field.afterChange);
        //~ }
    //~ }
    input.data = saltos.core.copy_object(field.data);
    const element = obj.querySelector('div');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: color,
    });
    obj.append(placeholder);
    // Continue

    //~ const buildColumns = () => {
        //~ const cols = [];
        //~ const headers = field.colHeaders || [];
        //~ const widths = field.colWidths || [];

        //~ const totalCols = Math.max(
            //~ headers.length,
            //~ widths.length,
            //~ input.data[0] ? input.data[0].length : 0
        //~ );

        //~ for (let i = 0; i < totalCols; i++) {
            //~ cols.push({
                //~ title: headers[i] || '',
                //~ width: widths[i] || 120,
                //~ type: 'text',
            //~ });
        //~ }

        //~ if (Array.isArray(field.cell)) {
            //~ field.cell.forEach(cell => {
                //~ if (cell.type === 'dropdown') {
                    //~ cols[cell.col] = {
                        //~ ...cols[cell.col],
                        //~ type: 'dropdown',
                        //~ source: cell.source || [],
                    //~ };
                //~ }
            //~ });
        //~ }

        //~ return cols;
    //~ };

    //~ const applyCellMeta = spreadsheet => {
        //~ if (!Array.isArray(field.cell)) {
            //~ return;
        //~ }

        //~ field.cell.forEach(cell => {
            //~ const coord = jspreadsheet.helpers.getColumnNameFromCoords(
                //~ cell.col,
                //~ cell.row
            //~ );

            //~ if (cell.readOnly) {
                //~ spreadsheet.setReadOnly(coord, true);
            //~ }

            //~ if (cell.className) {
                //~ spreadsheet.setStyle(coord, cell.className);
            //~ }
        //~ });
    //~ };

    saltos.core.require([
        'lib/jspreadsheet/jspreadsheet.min.css',
        'lib/jspreadsheet/jspreadsheet.themes.min.css',
        'lib/jspreadsheet/jspreadsheet.min.js',
        'lib/jsuites/jsuites.min.css',
        'lib/jsuites/jsuites.min.js',
    ], () => {
        placeholder.remove();
        element.parentElement.classList.add('form-control', 'p-0', shadow, rounded, ...border);
        element.parentElement.style.height = height;
        element.parentElement.style.overflow = 'auto';
        const _jspreadsheet = jspreadsheet(element, {
            worksheets: [
                {
                    data: input.data,
                    //~ columns: buildColumns(),
                    tableOverflow: true,
                    tableHeight: height,
                }
            ],
            onload: function(spreadsheet) {
                console.log(spreadsheet);
            },
            onchange: (instance, cell, x, y, value) => {
                input.data = instance.getData();
                if (typeof field.afterChange === 'function') {
                    field.afterChange([
                        [y, x, null, value]
                    ]);
                }
            },
        });
        //~ applyCellMeta(_jspreadsheet);
        input.jspreadsheet = _jspreadsheet;
    });
    // Program the disabled feature
    input.set_disabled = bool => {
        if (!('jspreadsheet' in input)) {
            setTimeout(() => input.set_disabled(bool), 1);
            return;
        }

        const rows = input.data.length;
        const cols = input.data[0] ? input.data[0].length : 0;

        for (let y = 0; y < rows; y++) {
            for (let x = 0; x < cols; x++) {
                const coord = jspreadsheet.helpers.getColumnNameFromCoords(x, y);
                input.jspreadsheet.setReadOnly(coord, bool);
            }
        }
    };
    if (saltos.core.eval_bool(field.disabled)) {
        input.set_disabled(true);
    }
    // Program the set in the input first
    input.set = value => {
        if (!('jspreadsheet' in input)) {
            if (!('queue' in input)) {
                input.queue = [];
            }
            input.queue.push(value);
            if (!('timer' in input)) {
                input.timer = setInterval(() => {
                    if (!('jspreadsheet' in input)) {
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
        } else {
            input.data = saltos.core.copy_object(value.data);
        }
        input.jspreadsheet.setData(input.data);
    };
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
