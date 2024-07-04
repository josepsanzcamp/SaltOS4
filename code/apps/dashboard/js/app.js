
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
 * Dashboard application
 *
 * This application implements the tipical features associated to dashboard
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.dashboard = {};

/**
 * TODO
 *
 * TODO
 */
saltos.dashboard.init = arg => {
    if (arg == 'menu') {
        saltos.window.set_listener('saltos.customers.update', event => {
            saltos.core.ajax({
                url: 'api/?list/customers/widget/table1',
                success: response => {
                    if (!saltos.app.check_response(response)) {
                        return;
                    }
                    var temp = saltos.bootstrap.field(response);
                    document.getElementById('table1').replaceWith(temp);
                },
                error: request => {
                    saltos.app.show_error({
                        text: request.statusText,
                        code: request.status,
                    });
                },
                token: saltos.token.get(),
                lang: saltos.gettext.get(),
            });
        });
        Sortable.create(document.querySelector('.items'), {
            animation: 150,
        });
    }
    if (arg == 'config') {
        document.getElementById('bs_theme').value = saltos.bootstrap.get_bs_theme();
        document.getElementById('css_theme').value = saltos.bootstrap.get_css_theme();
        document.getElementById('lang').value = saltos.gettext.get();
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.dashboard.authupdate = () => {
    if (!saltos.app.check_required()) {
        return;
    }
    var data = saltos.app.get_data(true);
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?authupdate',
        data: JSON.stringify({
            'oldpass': data.oldpass,
            'newpass': data.newpass,
            'renewpass': data.renewpass,
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.app.modal('Response', 'Password updated successfully');
                saltos.hash.trigger();
                return;
            }
            saltos.app.show_error(response);
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};
