
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * Login application
 *
 * This application implements the tipical features associated to login
 */

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.login = {};

/**
 * Authenticate login function
 *
 * This function tries to authenticate the user using the user and pass fields of the form, to do
 * it uses the authenticate function that send data to the authtoken action
 */
saltos.login.authenticate = () => {
    if (!saltos.check_required()) {
        return;
    }
    var data = saltos.get_data(true);
    saltos.authenticate.authtoken(data.user, data.pass);
    if (saltos.token.get_token()) {
        saltos.hash.set('app/menu');
        saltos.hash.change();
    } else {
        saltos.login.access_denied();
        saltos.hash.change();
    }
};

/**
 * Initialize login
 *
 * This function initializes the login screen to improve the user experience.
 */
saltos.login.initialize = () => {
    document.getElementById('user').focus();
    document.getElementById('user').addEventListener('keydown', event => {
        if (saltos.get_keycode(event) != 13) {
            return;
        }
        document.getElementById('pass').focus();
    });
    document.getElementById('pass').addEventListener('keydown', event => {
        if (saltos.get_keycode(event) != 13) {
            return;
        }
        saltos.login.authenticate();
    });
};

/**
 * Access denied
 *
 * This function displays a modal dialog with the tipical access denied message
 */
saltos.login.access_denied = () => {
    saltos.alert('Access denied', 'Incorrect user or password, try again');
};
