
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
    // Nothing to do at the moment
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.colWidths_concepts = index => {
    if (index == 0) {
        const width = document.getElementById('concepts').parentElement.offsetWidth - 2;
        return Math.floor(width - 500);
    }
    return 100;
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.cells_concepts = (row, column, prop) => {
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
 * TODO
 *
 * TODO
 */
saltos.invoices.colWidths_taxes = index => {
    if (index == 0) {
        const width = document.getElementById('taxes').parentElement.offsetWidth - 2;
        return Math.floor(width - 200);
    }
    return 100;
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.cells_taxes = (row, column, prop) => {
    return {
        readOnly: true,
    };
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.colWidths_totals = index => {
    const width = document.getElementById('taxes').parentElement.offsetWidth - 2;
    return Math.floor(width / 3);
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.cells_totals = (row, column, prop) => {
    return {
        readOnly: true,
    };
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.afterChange_concepts = (changes, source) => {
    console.log(source);
    if (source != 'edit') {
        return;
    }

    // Check, validate and compute the concepts matrix
    const concepts = document.getElementById('concepts').data;
    for (const i in concepts) {
        const line = concepts[i];
        let ok = true;
        for (let j = 1; j <= 3; j++) {
            if (!saltos.core.is_number(line[j])) {
                // This case detect when discount (pos 3) is void but
                // quantity (pos 1) and price (pos 2) have valid data
                if (j == 3 && ok) {
                    line[3] = 0;
                    continue;
                }
                // here, non valid data was detected
                ok = false;
                continue;
            }
            // here, it tries to normalice the number
            line[j] = parseFloat(line[j]);
        }
        if (ok) {
            // Computes the total for this line
            line[5] = line[1] * line[2] * (1 - line[3] / 100);
            // Round using two decimals
            line[5] = Math.round(line[5] * 100) / 100;
        }
    }

    // This trigger a concepts refresh
    document.getElementById('concepts').excel.updateSettings({});

    // Compute the taxes matrix
    const taxes = {};
    const alltaxes = document.getElementById('alltaxes').data;
    // Create the taxes structure by tax and compute all bases
    for (const i in concepts) {
        const line = concepts[i];
        const tax = line[4];
        const base = line[5];
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
    document.getElementById('taxes').set(Object.values(taxes));

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
    document.getElementById('totals').set([totals]);
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
