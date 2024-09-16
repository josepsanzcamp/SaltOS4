
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * invoices application
 *
 * This application implements the tipical features associated to invoices
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.invoices = {};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.init = arg => {
    if (arg == 'view') {
        document.querySelectorAll('.detail button, .footer button').forEach(_this => {
            _this.closest('.col-auto').remove();
        });
    }

    if (['create', 'edit'].includes(arg)) {
        document.querySelectorAll('[id*=unidades], [id*=precio], [id*=descuento]').forEach(_this => {
            _this.removeEventListener('change', saltos.invoices.compute_total);
            _this.addEventListener('change', saltos.invoices.compute_total);
        });
        document.querySelectorAll('[id*=total], #total').forEach(_this => {
            _this.setAttribute('disabled', '');
        });
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.compute_total = () => {
    let total = 0;
    document.querySelectorAll('.detail').forEach(_this => {
        if (_this.querySelector('[id*=unidades]') === null) {
            return;
        }
        const unidades = _this.querySelector('[id*=unidades]').value;
        const precio = _this.querySelector('[id*=precio]').value;
        const descuento = _this.querySelector('[id*=descuento]').value;
        let subtotal = unidades * precio * (1 - (descuento / 100));
        subtotal = Math.round(100 * subtotal) / 100;
        _this.querySelector('[id*=total]').value = subtotal;
        total += subtotal;
    });
    document.getElementById('total').value = total;
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.add_item = () => {
    saltos.app.form.__backup.restore('two,one');
    const layout = saltos.app.form.__layout_template_helper('detail', saltos.core.uniqid());
    const obj = saltos.app.form.layout(layout, 'div');
    document.querySelector('.footer').before(obj);
    saltos.invoices.init('edit');
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.remove_item = (obj) => {
    // This long line is to do a copy of the array to iterate and remove at the same time
    const items = Array.prototype.slice.call(obj.closest('.row').childNodes);
    items.forEach(_this => {
        if (_this.classList.contains('d-none') && _this.querySelector('input').value != '') {
            _this.querySelector('input').value *= -1;
        } else {
            _this.remove();
        }
    });
    saltos.invoices.compute_total();
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.viewpdf = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('table'));
    if (!ids.length) {
        saltos.app.modal(
            'Select invoices',
            'You must select the desired invoices that you want see in the PDF file',
            {
                color: 'warning',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.driver.open('app/invoices/view/viewpdf/' + ids);
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.download = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('table'));
    if (!ids.length) {
        saltos.app.modal(
            'Select invoices',
            'You must select the desired invoices that you want download in the PDF file',
            {
                color: 'warning',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.app.download('app/invoices/view/download/' + ids);
};
