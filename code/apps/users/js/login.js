
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
saltos.login.authenticate = async () => {
    if (!saltos.app.check_required()) {
        return;
    }
    const data = saltos.app.get_data(true);
    await saltos.authenticate.authtoken(data.user, data.pass);
    if (!saltos.token.get()) {
        saltos.app.toast('Access denied', 'Incorrect user or password, try again', {color: 'danger'});
        return;
    }
    // Hash part
    if (['', 'app/login'].includes(saltos.hash.get())) {
        saltos.hash.set('app/dashboard');
    }
    saltos.window.send('saltos.app.login');
};
