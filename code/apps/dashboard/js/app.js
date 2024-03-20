
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
saltos.dashboard.initialize = () => {
    saltos.window.set_listener('saltos.customers.update', event => {
        saltos.core.ajax({
            url: 'api/index.php?list/customers/widget/table1',
            success: response => {
                if (!saltos.app.check_response(response)) {
                    return;
                }
                var temp = saltos.bootstrap.field(response);
                document.getElementById('table1').parentNode.replaceWith(temp);
            },
            error: request => {
                saltos.app.show_error({
                    text: request.statusText,
                    code: request.status,
                });
            },
            token: saltos.token.get(),
        });
    });

    Sortable.create(document.querySelector('.row'), {
        animation: 150,
    });
};
