
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
 * Invoices application
 *
 * This application provides core functionalities for managing invoices, including creating,
 * editing, viewing, and exporting invoices in PDF format.
 */

/**
 * Main object
 *
 * This object contains all SaltOS code related to invoice operations.
 */
saltos.invoices = {};

/**
 * Fix tax dropdown visibility.
 *
 * Reset the parent's overflow property to prevent the tax dropdown
 * from being clipped or hidden inside the container.
 */
saltos.invoices.onload = instance => {
    instance.element.parentElement.style.overflow = '';
};

/**
 * Lines columns
 *
 * This object defines the position of each column in the lines matrix, the tax and the
 * withholding columns are always present in the matrix but are hidden when the mode
 * defined by the company is not by line.
 */
saltos.invoices.cols = {
    description: 0,
    quantity: 1,
    price: 2,
    discount: 3,
    tax: 4,
    withholding: 5,
    total: 6,
};

/**
 * Types
 *
 * This object defines the value used by each type in the taxes master, the modes defined
 * by the company for each type can be 0 (not used), 1 (one by document) or 2 (by line).
 */
saltos.invoices.types = {
    tax: 0,
    withholding: 1,
};

/**
 * Handle and recalculate invoice line totals and tax breakdowns after user edits.
 *
 * This function is triggered after the user modifies any cell in the "lines" matrix
 * (invoice lines). It performs the following operations:
 *
 * - Validates and normalizes quantity, price, and discount values:
 *     - Ensures quantity, price and discount are numeric.
 *     - Defaults discount to 0 if empty but qty and price are valid.
 * - Sets the tax and the withholding of the new lines:
 *     - Uses the value of the document select when the mode is one by document.
 *     - Uses the value of the default register when the mode is by line.
 *     - The lines that already have a total are never modified.
 * - Calculates the line total as: `qty × price × (1 - discount%)`, rounded to 2 decimals.
 * - Updates the lines grid to reflect computed values.
 * - Recomputes the taxes matrix:
 *     - Aggregates taxable bases per type (tax or withholding) and value.
 *     - Computes the amount for each group: `base × (value% / 100)`.
 * - Rounds all results to 2 decimals and updates the taxes grid.
 * - Computes the totals matrix:
 *     - `subtotal` = sum of all line totals
 *     - `tax` = sum of all tax amounts
 *     - `withholding` = sum of all withholding amounts
 *     - `total` = subtotal + tax - withholding
 *     - Results are rounded and shown in the totals grid.
 *
 * @instance => The spreadsheet instance that triggers the change
 * @cell     => The cell that has changed
 * @x        => The column of the cell
 * @y        => The row of the cell
 * @value    => The new value of the cell
 */
saltos.invoices.onchange = (instance, cell, x, y, value) => {
    const cols = saltos.invoices.cols;
    const types = saltos.invoices.types;
    const modes = document.getElementById('taxmodes').data;
    const alltaxes = document.getElementById('alltaxes').data;

    // Prepare the defaults used by the new lines
    const defaults = {};
    for (const type in types) {
        defaults[type] = null;
        if (modes[type] === 1) {
            const obj = document.getElementById(`${type}_document`);
            if (obj && saltos.core.is_number(obj.value)) {
                defaults[type] = parseFloat(obj.value);
            }
        }
        if (modes[type] === 2) {
            const temp = alltaxes.find(row => row.type === types[type] && row.default);
            if (temp) {
                defaults[type] = temp.value;
            }
        }
    }

    // Check, validate and compute the lines matrix
    const lines = document.getElementById('lines').data;
    for (const i in lines) {
        const line = lines[i];
        // Prepare quantity
        if (!saltos.core.is_number(line[cols.quantity])) {
            line[cols.total] = null;
            continue;
        }
        line[cols.quantity] = parseFloat(line[cols.quantity]);
        // Prepare price
        if (!saltos.core.is_number(line[cols.price])) {
            line[cols.total] = null;
            continue;
        }
        line[cols.price] = parseFloat(line[cols.price]);
        // Prepare discount
        if (!saltos.core.is_number(line[cols.discount])) {
            line[cols.discount] = 0;
        }
        line[cols.discount] = parseFloat(line[cols.discount]);
        // Prepare tax and withholding (for next step when compute the taxes matrix)
        const isnew = !saltos.core.is_number(line[cols.total]);
        for (const type in types) {
            const col = cols[type];
            if (isnew && !saltos.core.is_number(line[col])) {
                line[col] = defaults[type];
            }
            if (!saltos.core.is_number(line[col])) {
                line[col] = null;
                continue;
            }
            line[col] = parseFloat(line[col]);
        }
        // Computes the total for this line
        line[cols.total] = line[cols.quantity] * line[cols.price] * (1 - line[cols.discount] / 100);
        // Round using two decimals
        line[cols.total] = Math.round(line[cols.total] * 100) / 100;
    }
    // Update the entire matrix
    document.getElementById('lines').set(lines);

    // Compute the taxes matrix
    const taxes = {};
    const labels = {
        tax: T('Tax'),
        withholding: T('Withholding'),
    };
    let found = false;
    let subtotal = 0;
    // Create the taxes structure by type and value and compute all bases
    for (const i in lines) {
        const line = lines[i];
        const base = line[cols.total];
        if (!saltos.core.is_number(base)) {
            continue;
        }
        found = true;
        subtotal += base;
        for (const type in types) {
            const val = line[cols[type]];
            if (!saltos.core.is_number(val)) {
                continue;
            }
            const key = `${type}:${val}`;
            if (!(key in taxes)) {
                const temp = alltaxes.find(row => row.type === types[type] && row.value === val);
                taxes[key] = {
                    type: type,
                    value: val,
                    name: temp ? temp.name : `${val}%`,
                    base: 0,
                    total: 0,
                };
            }
            taxes[key].base += base;
        }
    }
    // Apply each value to each base and round using two decimals
    for (const i in taxes) {
        taxes[i].total = Math.round(taxes[i].base * taxes[i].value) / 100;
        taxes[i].base = Math.round(taxes[i].base * 100) / 100;
    }
    // Sort by type and value and update the entire matrix
    const rows = Object.values(taxes).sort((a, b) => {
        if (a.type !== b.type) {
            return a.type === 'tax' ? -1 : 1;
        }
        return a.value - b.value;
    }).map(row => [labels[row.type], row.name, row.base, row.total]);
    // The taxes box not exists when the taxes and the withholdings are not used
    const obj = document.getElementById('taxes');
    if (obj) {
        obj.set(rows.length ? rows : [[null, null, null, null]]);
    }

    // Compute the totals matrix
    const totals = {
        subtotal: subtotal,
        tax: 0,
        withholding: 0,
        total: 0,
    };
    for (const i in taxes) {
        totals[taxes[i].type] += taxes[i].total;
    }
    totals.total = totals.subtotal + totals.tax - totals.withholding;
    // Round using two decimals
    for (const i in totals) {
        totals[i] = Math.round(totals[i] * 100) / 100;
    }
    // Update the entire matrix
    let row = [null, null, null, null];
    if (found) {
        row = [totals.subtotal, totals.tax, totals.withholding, totals.total];
    }
    document.getElementById('totals').set([row]);
};

/**
 * Apply the tax or the withholding of the document to all lines
 *
 * This function is triggered when the user changes the select used by the one by document
 * mode, it copies the selected value to all computed lines and recalculates the taxes
 * and the totals, this allow to use the same structure of data for all modes.
 *
 * @type => The type of the select that has changed, can be tax or withholding
 */
saltos.invoices.onselect = type => {
    const cols = saltos.invoices.cols;
    const value = document.getElementById(`${type}_document`).value;
    const lines = document.getElementById('lines').data;
    for (const i in lines) {
        const line = lines[i];
        if (!saltos.core.is_number(line[cols.total])) {
            continue;
        }
        line[cols[type]] = saltos.core.is_number(value) ? parseFloat(value) : null;
    }
    document.getElementById('lines').set(lines);
    saltos.invoices.onchange();
};
