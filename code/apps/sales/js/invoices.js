
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
    // Handle initialization for viewing invoices
    if (arg == 'view') {
        document.querySelectorAll('.detail button, .footer button').forEach(item => {
            item.closest('.col-auto').remove();
        });
    }

    // Handle initialization for creating or editing invoices
    if (['create', 'edit'].includes(arg)) {
        document.querySelectorAll('[id*=quantity], [id*=price], [id*=discount], [id*=tax]').forEach(item => {
            item.removeEventListener('change', saltos.invoices.compute_total);
            item.addEventListener('change', saltos.invoices.compute_total);
        });
        document.querySelectorAll('[id*=\\.total], #subtotal, #tax, #total').forEach(item => {
            item.setAttribute('disabled', '');
        });
    }
};

/**
 * Compute total invoice amount
 *
 * This method calculates the subtotal for each invoice item and the grand total for all items.
 * It handles discounts, rounds values to two decimal places, and updates the total fields.
 */
saltos.invoices.compute_total = () => {
    let subtotal = 0;
    let tax = 0;
    let total = 0;
    document.querySelectorAll('.detail').forEach(item => {
        if (item.querySelector('[id*=quantity]') === null) {
            return;
        }
        const quantity = item.querySelector('[id*=quantity]').value;
        const price = item.querySelector('[id*=price]').value;
        const discount = item.querySelector('[id*=discount]').value;
        const _tax = item.querySelector('[id*=tax]').value;
        let _total = quantity * price * (1 - (discount / 100));
        _total = Math.round(100 * _total) / 100;
        item.querySelector('[id*=total]').value = _total;
        subtotal += _total;
        tax += _total * (_tax / 100);
        total += _total * (1 + (_tax / 100))
    });
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('tax').value = tax;
    document.getElementById('total').value = total;
};

/**
 * Add a new item to the invoice
 *
 * This method creates a new invoice item and initializes its layout and event listeners.
 * It ensures consistent behavior for the form fields and recomputes the totals after addition.
 */
saltos.invoices.add_item = () => {
    saltos.backup.restore('two,one');
    const layout = saltos.form.__layout_template_helper('detail', saltos.core.uniqid());
    const obj = saltos.form.layout(layout, 'div');
    // Important notice: this function modify the layout and is important to do the
    // same that saltos.form.layout at the end when append is used, without this the
    // next calls to get_data will restore the old two,one layout to the used array
    // in saltos.form.__form.fields, without this two lines, only works in r1427 or
    // earlier, this feature breaks in r1428 and was complex to be fixed
    const key = saltos.backup.__selector_helper('two,one');
    saltos.backup.save(key[0]);
    document.querySelector('.footer').before(obj);
    saltos.invoices.init('edit');
};

/**
 * Remove an item from the invoice
 *
 * This method deletes an invoice item or marks it with a negative value if it is hidden.
 * It updates the invoice totals after removal.
 *
 * @obj => Reference to the item to remove.
 */
saltos.invoices.remove_item = (obj) => {
    // This long line is to do a copy of the array to iterate and remove at the same time
    const items = Array.prototype.slice.call(obj.closest('.row').childNodes);
    items.forEach(item => {
        if (item.classList.contains('d-none') && item.querySelector('input').value != '') {
            item.querySelector('input').value *= -1;
        } else {
            item.remove();
        }
    });
    saltos.invoices.compute_total();
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
