
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
 * Initialize the invoices application
 *
 * This method handles initialization tasks based on the type of operation (`view`, `create`, or `edit`).
 * It updates UI elements and attaches appropriate event listeners.
 *
 * @arg => Specifies the type of operation to initialize.
 */
saltos.invoices.init = arg => {
    if (['edit'].includes(arg)) {
        // Remove the original data to force that get_data get this fields
        for (const i in saltos.backup.__forms) {
            for (const j in saltos.backup.__forms[i].fields) {
                const k = saltos.backup.__forms[i].fields[j];
                if (['lines', 'taxes', 'totals'].includes(k.id)) {
                    k.data = [];
                }
            }
        }
    }
};

/**
 * Calculate the dynamic width for each column in the "lines" table.
 *
 * This function returns the width for each column. For the first column (index 0),
 * it dynamically calculates the remaining space based on the container width minus
 * a fixed width (500px) reserved for other columns. For all other columns, it returns 100px.
 *
 * @index => Column index
 *
 * Returns the width in pixels
 */
saltos.invoices.colWidths_lines = index => {
    if (index == 0) {
        const width = document.getElementById('lines').parentElement.offsetWidth - 2;
        return Math.floor(width - 500);
    }
    return 100;
};

/**
 * Define cell configuration for each column in the "lines" table.
 *
 * This function provides per-column behavior:
 * - Column 4: renders a dropdown with available tax values (from #alltaxes).
 * - Column 5: sets the cell as read-only (used for calculated values).
 * - Other columns: no special config.
 *
 * @row    => Row index
 * @column => Column index
 * @prop   => Column property name
 *
 * Returns the configuration object
 */
saltos.invoices.cells_lines = (row, column, prop) => {
    if (column == 4) {
        return {
            type: 'dropdown',
            source: document.getElementById('alltaxes').data.map(row => row.value),
        };
    }
    if (column == 5) {
        return {
            readOnly: true,
        };
    }
    return {};
};

/**
 * Calculate the dynamic width for each column in the "taxes" table.
 *
 * The first column (index 0) fills the available space minus 200px reserved for other columns.
 * All other columns get a fixed width of 100px.
 *
 * @index => Column index
 *
 * Returns the width in pixels
 */
saltos.invoices.colWidths_taxes = index => {
    if (index == 0) {
        const width = document.getElementById('taxes').parentElement.offsetWidth - 2;
        return Math.floor(width - 200);
    }
    return 100;
};

/**
 * Define cell configuration for each column in the "taxes" table.
 *
 * All cells in this table are read-only.
 *
 * @row    => Row index
 * @column => Column index
 * @prop   => Column property name
 *
 * Returns the configuration object
 */
saltos.invoices.cells_taxes = (row, column, prop) => {
    return {
        readOnly: true,
    };
};

/**
 * Calculate equal column widths for the "totals" table.
 *
 * The table is divided into 3 equal columns, based on the container width.
 *
 * @index => Column index
 *
 * Returns the width in pixels
 */
saltos.invoices.colWidths_totals = index => {
    const width = document.getElementById('taxes').parentElement.offsetWidth - 2;
    return Math.floor(width / 3);
};

/**
 * Define cell configuration for the "totals" table.
 *
 * All cells in this table are read-only.
 *
 * @row    => Row index
 * @column => Column index
 * @prop   => Column property name
 *
 * Returns the configuration object
 */
saltos.invoices.cells_totals = (row, column, prop) => {
    return {
        readOnly: true,
    };
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
saltos.invoices.afterChange_lines = (changes, source) => {
    if (source == 'loadData') {
        return;
    }

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
        // Computes the total for this line
        line[5] = line[1] * line[2] * (1 - line[3] / 100);
        // Round using two decimals
        line[5] = Math.round(line[5] * 100) / 100;
    }

    // This trigger a lines refresh
    document.getElementById('lines').excel.updateSettings({});

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
            const temp = alltaxes.find(row => row.value == tax);
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

/**
 * View selected invoices in PDF format
 *
 * This method opens a PDF viewer to display the selected invoices.
 * If no invoices are selected, it prompts the user to select invoices first.
 */
saltos.invoices.viewpdf = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('table'));
    if (!ids.length) {
        saltos.app.modal(
            'Select invoices',
            'You must select the desired invoices that you want see in the PDF file',
            {
                color: 'danger',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.driver.open('app/invoices/view/viewpdf/' + ids);
};

/**
 * Download selected invoices as PDF
 *
 * This method downloads the selected invoices in PDF format.
 * If no invoices are selected, it prompts the user to select invoices first.
 */
saltos.invoices.download = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('table'));
    if (!ids.length) {
        saltos.app.modal(
            'Select invoices',
            'You must select the desired invoices that you want download in the PDF file',
            {
                color: 'danger',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.common.download('app/invoices/view/download/' + ids);
};
