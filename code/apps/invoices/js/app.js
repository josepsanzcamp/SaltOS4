
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
saltos.invoices.initialize_buttons = () => {
    document.querySelectorAll('.detail button, .footer button').forEach(_this => {
        saltos.app.parentNode_search(_this, 'col-auto').remove();
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.initialize_inputs = () => {
    document.querySelectorAll('[id*=unidades], [id*=precio], [id*=descuento]').forEach(_this => {
        _this.removeEventListener('change', saltos.invoices.compute_total);
        _this.addEventListener('change', saltos.invoices.compute_total);
    });
    document.querySelectorAll('[id*=total], #total').forEach(_this => {
        _this.setAttribute('disabled', '');
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.compute_total = () => {
    var total = 0;
    document.querySelectorAll('.detail').forEach(_this => {
        if (_this.querySelector('[id*=unidades]') === null) {
            return;
        }
        var unidades = _this.querySelector('[id*=unidades]').value;
        var precio = _this.querySelector('[id*=precio]').value;
        var descuento = _this.querySelector('[id*=descuento]').value;
        var subtotal = unidades * precio * (1 - (descuento / 100));
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
    var layout = saltos.app.form.__layout_template_helper('detail', saltos.core.uniqid());
    var obj = saltos.app.form.layout(layout, 'div');
    document.querySelector('.footer').before(obj);
    saltos.invoices.initialize_inputs();
};

/**
 * TODO
 *
 * TODO
 */
saltos.invoices.remove_item = (obj) => {
    // This long line is to do a copy of the array to iterate and remove at the same time
    var items = Array.prototype.slice.call(saltos.app.parentNode_search(obj, 'row').childNodes);
    items.forEach(_this => {
        if (_this.classList.contains('d-none') && _this.querySelector('input').value != '') {
            _this.querySelector('input').value *= -1;
        } else {
            _this.remove();
        }
    });
    saltos.invoices.compute_total();
};
