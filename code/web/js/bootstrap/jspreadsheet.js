
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
 * @handsontable => id, class, label, required, disabled, height, color, shadow, rounded,
 *                  data, numcols, numrows, autoWidth, rowHeaders, minSpareRows, minSpareCols
 */

/**
 * TODO
 */
saltos.bootstrap.__field.jspreadsheet = field => {
    saltos.core.check_params(field, ['id', 'class', 'data', 'required', 'disabled', 'columns', 'rows',
                                     'color', 'height', 'shadow', 'rounded', 'numcols', 'numrows',
                                     'autoWidth', 'fitWidth', 'rowHeaders', 'rowHeaderWidth',
                                     'minSpareRows', 'minSpareCols']);
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
    let minSpareCols = 0;
    if (field.minSpareCols !== '') {
        minSpareCols = field.minSpareCols;
    }
    let autoWidth = false;
    if (field.autoWidth !== '') {
        autoWidth = saltos.core.eval_bool(field.autoWidth);
    }
    let fitWidth = false;
    if (field.fitWidth !== '') {
        fitWidth = saltos.core.eval_bool(field.fitWidth);
    }
    let rowHeaders = true;
    if (field.rowHeaders !== '') {
        rowHeaders = saltos.core.eval_bool(field.rowHeaders);
    }
    let rowHeaderWidth = 50;
    if (field.rowHeaderWidth !== '') {
        rowHeaderWidth = field.rowHeaderWidth;
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
    const _fitWidth_helper = () => {
        const content = input.jspreadsheet[0].content;
        let width = content.parentElement.offsetWidth - 3;
        if (rowHeaders) {
            width -= content.querySelector('table col').width;
        }
        let total = 0;
        if ('0' in input.data) {
            total = input.data[0].length;
        }
        let hiddens = 0;
        for (let i = 0; i < total; i++) {
            if (input.jspreadsheet[0].getConfig().columns[i].type === 'hidden') {
                hiddens++;
            }
        }
        total -= hiddens;
        for (let i = 0; i < total; i++) {
            const percentSize = input.jspreadsheet[0].getConfig().columns[i].percentSize;
            if (percentSize) {
                input.jspreadsheet[0].setWidth(i, width * parseFloat(percentSize, 10) * 0.01);
            } else {
                input.jspreadsheet[0].setWidth(i, width / total);
            }
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
            parseFormulas: false,
            worksheets: [{
                data: input.data,
                minSpareRows: minSpareRows,
                minSpareCols: minSpareCols,
                //tableHeight: height,
                tableOverflow: false,
                columnResize: false,
                columnSorting: false,
                allowComments: false,
                defaultColAlign: 'left',
                //allowDeleteColumn: false,
                rowResize: false,
                allowDeleteRow: false,
                allowManualInsertColumn: false,
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
                rows: (() => {
                    return field.rows;
                })(),
            }],
            contextMenu: () => {
                return false;
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
                } else if (rowHeaderWidth) {
                    content.querySelector('table col').width = rowHeaderWidth;
                }
                if (autoWidth) {
                    _autoWidth_helper();
                }
                if (fitWidth) {
                    _fitWidth_helper();
                }
            },
            onchange: (instance, cell, x, y, value) => {
                if (autoWidth) {
                    _autoWidth_helper(x);
                }
            },
        });
        input.jspreadsheet = _jspreadsheet;
        if (fitWidth) {
            const resizeObserver = new ResizeObserver(() => {
                if (input.jspreadsheet && input.jspreadsheet[0]) {
                    window.requestAnimationFrame(() => {
                        _fitWidth_helper();
                    });
                }
            });
            resizeObserver.observe(element.parentElement);
            input._resizeObserver = resizeObserver;
        }
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
    // Some fixes
    obj.append(saltos.core.html(`
        <style>
            .jss_content {
                padding-right: 0;
                padding-bottom: 0;
            }
        </style>
    `));
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
