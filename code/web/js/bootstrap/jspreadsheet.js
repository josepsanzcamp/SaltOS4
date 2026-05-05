
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
 * @handsontable => id, class, data, label, required, disabled, height, contextMenu, color,
 *                  numcols, numrows, shadow, rounded
 */

/**
 * TODO
 */
saltos.bootstrap.__field.jspreadsheet = field => {
    saltos.core.check_params(field, ['id', 'class', 'value', 'data', 'required', 'disabled', 'columns',
                                     'color', 'height', 'shadow', 'rounded', 'autoWidth', 'rowHeaders',
                                     'numcols', 'numrows', 'minSpareRows', 'contextMenu',
                                     'parseFormulas', 'columnResize', 'columnSorting', 'allowComments']);
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
    // data parameters
    const input = obj.querySelector('input');
    let numcols = 26;
    if (field.numcols !== '') {
        numcols = parseInt(field.numcols, 10);
    }
    let numrows = 20;
    if (field.numrows !== '') {
        numrows = parseInt(field.numrows, 10);
    }
    if (field.data !== '') {
        input.data = saltos.core.copy_object(field.data);
    } else {
        input.data = [...Array(numrows)].map(e => Array(numcols));
    }
    // jspreadsheet parameters
    let minSpareRows = 0;
    if (field.minSpareRows !== '') {
        minSpareRows = field.minSpareRows;
    }
    let contextMenu = false;
    if (field.contextMenu !== '') {
        contextMenu = field.contextMenu;
    }
    let parseFormulas = false;
    if (field.parseFormulas !== '') {
        parseFormulas = field.parseFormulas;
    }
    let columnResize = false;
    if (field.columnResize !== '') {
        columnResize = field.columnResize;
    }
    let columnSorting = false;
    if (field.columnSorting !== '') {
        columnSorting = field.columnSorting;
    }
    let allowComments = false;
    if (field.allowComments !== '') {
        allowComments = field.allowComments;
    }
    let autoWidth = true;
    if (field.autoWidth !== '') {
        autoWidth = saltos.core.eval_bool(field.autoWidth);
    }
    let rowHeaders = true;
    if (field.rowHeaders !== '') {
        rowHeaders = saltos.core.eval_bool(field.rowHeaders);
    }
    const element = obj.querySelector('div');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: color,
    });
    obj.append(placeholder);
    // Define auto_width helper
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const style = window.getComputedStyle(document.body);
    ctx.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
    input._ctx = ctx;
    const _autoWidth_helper = index => {
        if (saltos.core.is_number(index)) {
            let maxwidth = 50;
            for (let j = 0; j < input.data.length; j++) {
                const text = String(input.data[j][index]);
                const width = input._ctx.measureText(text).width + 10;
                maxwidth = Math.max(maxwidth, width);
            }
            input.jspreadsheet[0].setWidth(index, maxwidth);
            return;
        }
        let total = 0;
        if ('0' in input.data) {
            total = input.data[0].length;
        }
        for (let i = 0; i < total; i++) {
            let maxwidth = 50;
            for (let j = 0; j < input.data.length; j++) {
                const text = String(input.data[j][i]);
                const width = input._ctx.measureText(text).width + 10;
                maxwidth = Math.max(maxwidth, width);
            }
            input.jspreadsheet[0].setWidth(i, maxwidth);
        }
    };
    // Continue
    saltos.core.require([
        'lib/jspreadsheet/jspreadsheet.min.css',
        'lib/jspreadsheet/jspreadsheet.themes.min.css',
        'lib/jspreadsheet/jspreadsheet.dark.min.css',
        'lib/jspreadsheet/jspreadsheet.min.js',
        'lib/jsuites/jsuites.min.css',
        'lib/jsuites/jsuites.min.js',
    ], () => {
        placeholder.remove();
        element.parentElement.classList.add('form-control', 'p-0', shadow, rounded, ...border);
        element.parentElement.style.height = height;
        element.parentElement.style.overflow = 'auto';
        const _jspreadsheet = jspreadsheet(element, {
            tabs: false,
            toolbar: false,
            parseFormulas: parseFormulas,
            worksheets: [{
                data: input.data,
                tableHeight: height,
                minSpareRows: minSpareRows,
                columnResize: columnResize,
                columnSorting: columnSorting,
                allowComments: allowComments,
                defaultColAlign: 'left',
                columns: (() => {
                    if (Array.isArray(field.columns)) {
                        for (const i in field.columns) {
                            if ('width' in field.columns[i] &&
                                saltos.core.is_function(field.columns[i].width)) {
                                field.columns[i].width = eval(field.columns[i].width)();
                            }
                        }
                    }
                    return field.columns;
                })(),
            }],
            contextMenu: () => {
                return contextMenu;
            },
            onload: () => {
                const content = input.jspreadsheet[0].content;
                content.querySelectorAll('table thead td').forEach(item => {
                    if ('textAlign' in item.style) {
                        item.style.textAlign = 'center';
                    }
                });
                if (!rowHeaders) {
                    content.querySelector('table col').width = 0;
                }
                if (autoWidth) {
                    _autoWidth_helper();
                }
            },
            onchange: (instance, cell, x, y, value) => {
                if (autoWidth) {
                    _autoWidth_helper(x);
                }
            },
        });
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
                const coord = input.jspreadsheet[0].getCellFromCoords(x, y);
                input.jspreadsheet[0].setReadOnly(coord, bool);
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
        input.jspreadsheet[0].setData(input.data);
        if (autoWidth) {
            _autoWidth_helper();
        }
    };
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
