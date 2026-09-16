
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
 *
 * If the response of the authtoken action is 'expired', this function switches the screen to
 * the renew box instead of showing the usual access denied message, allowing the user to set
 * a new password without needing a valid session
 */
saltos.login.authenticate = async () => {
    if (!saltos.app.check_required()) {
        return;
    }
    const data = saltos.app.get_data(true);
    const response = await saltos.authenticate.authtoken(data.user, data.pass);
    if (response && response.status === 'ok') {
        // Hash part
        if (['', 'app/login'].includes(saltos.hash.get())) {
            saltos.hash.set('app/dashboard');
        }
        saltos.window.send('saltos.app.login');
        return;
    }
    if (response && response.status === 'expired') {
        document.getElementById('login-box').classList.add('d-none');
        document.getElementById('renew-box').classList.remove('d-none');
        document.getElementById('newpass').focus();
        return;
    }
    saltos.app.modal('Access denied', 'Incorrect user or password, try again', {color: 'danger'});
};

/**
 * Renew function
 *
 * This function is intended to be used by the renew box shown after an expired login
 * attempt, it collects the new password and its confirmation and, together with the user
 * and the expired password (still held by the hidden user/pass fields of the login box),
 * tries to renew the password using the authrenew action, that returns a valid token on
 * success
 *
 * Note: the success message uses saltos.app.toast instead of saltos.app.modal, because it
 * is immediately followed by the saltos.app.login event, which fires a hashchange, and
 * every hashchange unconditionally closes any open modal (see hash.js) - a modal here
 * would close itself before ever being seen. The error case above has no screen change
 * after it, so a modal is fine there
 */
saltos.login.renew = async () => {
    const data = saltos.app.get_data(true);
    const response = await saltos.authenticate.authrenew(data.user, data.pass, data.newpass, data.renewpass);
    if (!response || response.status !== 'ok') {
        return;
    }
    saltos.app.toast('Response', 'Password updated successfully');
    if (['', 'app/login'].includes(saltos.hash.get())) {
        saltos.hash.set('app/dashboard');
    }
    saltos.window.send('saltos.app.login');
};
