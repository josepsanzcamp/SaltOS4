
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
 * Handle and recalculate invoice line totals and tax breakdowns after user edits.
 *
 * This function is triggered after the user modifies any cell in the "lines" matrix
 * (invoice lines). It performs the following operations:
 *
 * - Ignores changes triggered by internal actions (non-`edit` sources).
 * - Validates and normalizes quantity, price, and discount values:
 *     - Ensures `line[1]`, `line[2]`, `line[3]` (qty, price, discount) are numeric.
 *     - Defaults discount to 0 if empty but qty and price are valid.
 * - Calculates the line total as: `qty × price × (1 - discount%)`, rounded to 2 decimals.
 * - Updates the lines grid to reflect computed values.
 * - Recomputes the taxes matrix:
 *     - Aggregates taxable bases per tax value (`line[4]`).
 *     - Computes tax amount for each group: `base × (tax% / 100)`.
 * - Rounds all tax results to 2 decimals and updates the taxes grid.
 * - Computes the totals matrix:
 *     - `subtotal` = sum of all taxable bases
 *     - `tax` = sum of all tax amounts
 *     - `total` = subtotal + tax
 *     - Results are rounded and shown in the totals grid.
 *
 * @changes => Array of cell changes from the spreadsheet component
 * @source  => Source of the change event (only `'edit'` triggers processing)
 */
saltos.invoices.onchange = (instance, cell, x, y, value) => {
    // Check, validate and compute the lines matrix
    const lines = document.getElementById('lines').data;
    for (const i in lines) {
        const line = lines[i];
        // Prepare quantity
        if (!saltos.core.is_number(line[1])) {
            line[5] = null;
            continue;
        }
        line[1] = parseFloat(line[1]);
        // Prepare price
        if (!saltos.core.is_number(line[2])) {
            line[5] = null;
            continue;
        }
        line[2] = parseFloat(line[2]);
        // Prepare discount
        if (!saltos.core.is_number(line[3])) {
            line[3] = 0;
        }
        line[3] = parseFloat(line[3]);
        // Prepare tax (for next step when compute the taxes matrix)
        if (!saltos.core.is_number(line[4])) {
            line[4] = 0;
        }
        line[4] = parseFloat(line[4]);
        // Computes the total for this line
        line[5] = line[1] * line[2] * (1 - line[3] / 100);
        // Round using two decimals
        line[5] = Math.round(line[5] * 100) / 100;
    }
    // Update the entire matrix
    document.getElementById('lines').set(lines);

    // Compute the taxes matrix
    const taxes = {};
    const alltaxes = document.getElementById('alltaxes').data;
    // Create the taxes structure by tax and compute all bases
    for (const i in lines) {
        const line = lines[i];
        const tax = line[4];
        if (!saltos.core.is_number(tax)) {
            continue;
        }
        const base = line[5];
        if (!saltos.core.is_number(base)) {
            continue;
        }
        if (!(tax in taxes)) {
            const temp = alltaxes.find(row => row.value === tax);
            if (!temp) {
                continue;
            }
            taxes[tax] = [temp.name, 0, 0];
        }
        taxes[tax][1] += base;
    }
    // Apply each tax to each base
    for (const i in taxes) {
        taxes[i][2] = taxes[i][1] * i / 100;
    }
    // Round using two decimals
    for (const i in taxes) {
        taxes[i][1] = Math.round(taxes[i][1] * 100) / 100;
        taxes[i][2] = Math.round(taxes[i][2] * 100) / 100;
    }
    // Update the entire matrix
    if (Object.keys(taxes).length) {
        document.getElementById('taxes').set(Object.values(taxes));
    } else {
        document.getElementById('taxes').set([[null, null, null]]);
    }

    // Compute the totals matrix
    const totals = [0, 0, 0];
    for (const i in taxes) {
        totals[0] += taxes[i][1];
        totals[1] += taxes[i][2];
    }
    totals[2] = totals[0] + totals[1];
    // Round using two decimals
    totals[0] = Math.round(totals[0] * 100) / 100;
    totals[1] = Math.round(totals[1] * 100) / 100;
    totals[2] = Math.round(totals[2] * 100) / 100;
    // Update the entire matrix
    if (Object.keys(taxes).length) {
        document.getElementById('totals').set([totals]);
    } else {
        document.getElementById('totals').set([[null, null, null]]);
    }

};
