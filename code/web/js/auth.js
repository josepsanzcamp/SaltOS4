
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
 * Authentication helper module
 *
 * This file contains all needed code to do authentications with all features suck as the
 * main authentication using a user and password pair, the checktoken and the deauthtoken
 * to control it.
 */

/**
 * Authentication helper object
 *
 * This object stores all authentication functions to get access, check tokens to maintain
 * the access and the deauthtoken to close the access
 */
saltos.authenticate = {};

/**
 * Authenticate token function
 *
 * This function uses the authtoken action to try to authenticate an user with the user/pass
 * credentials passed by argument.
 *
 * This function returns the response of the action, useful to detect the expired flag
 * of a 'ko' response, that requires a different treatment than a plain access denied
 *
 * @user => username used to the authentication process
 * @pass => password used to the authentication process
 */
saltos.authenticate.authtoken = async (user, pass) => {
    let result = null;
    await saltos.app.ajax({
        url: 'auth/login',
        data: {
            user: user,
            pass: pass,
        },
        success: response => {
            result = response;
            if (response.status === 'ok') {
                saltos.token.set(response);
                return;
            }
            if (response.status === 'ko') {
                saltos.token.unset();
                return;
            }
            saltos.app.show_error(response);
        },
    });
    return result;
};

/**
 * Check token function
 *
 * This function uses the checktoken action to check the validity of the current token.
 */
saltos.authenticate.checktoken = async () => {
    await saltos.app.ajax({
        url: 'auth/check',
        success: response => {
            if (response.status === 'ok') {
                saltos.token.set(response);
                return;
            }
            if (response.status === 'ko') {
                saltos.token.unset();
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * De-authenticate token function
 *
 * This function uses the deauthtoken action to try to de-authenticate an user with the token
 * credentials.
 */
saltos.authenticate.deauthtoken = async () => {
    await saltos.app.ajax({
        url: 'auth/logout',
        success: response => {
            if (response.status === 'ok') {
                saltos.token.unset();
                return;
            }
            if (response.status === 'ko') {
                saltos.token.unset();
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * Authenticate update function
 *
 * This function is intended to be used in the profile feature to allow the password change
 * by the user. On success, the response contains a fresh valid token, exactly as the
 * authtoken and authrenew functions do, so that changing the password always ends in the
 * same atomic outcome: a new session issued for the new password.
 *
 * @oldpass   => old password used to validate the correctness of the transaction
 * @newpass   => new password used to update the old password
 * @renewpass => repite the new password used to update the old password
 */
saltos.authenticate.authupdate = async (oldpass, newpass, renewpass) => {
    let result = null;
    await saltos.app.ajax({
        url: 'auth/update',
        data: {
            oldpass: oldpass,
            newpass: newpass,
            renewpass: renewpass,
        },
        success: response => {
            result = response;
            if (response.status === 'ok') {
                saltos.token.set(response);
                return;
            }
            saltos.app.show_error(response);
        },
    });
    return result;
};

/**
 * Authenticate renew function
 *
 * This function is intended to be used in the login screen, exclusively after a login
 * attempt returns the expired flag, to allow setting a new password without an active
 * session, using the expired password as the proof of identity.
 *
 * On success, the response contains a valid token, exactly as the authtoken function does.
 *
 * @user      => username used in the authentication process
 * @oldpass   => the expired password used to validate the transaction
 * @newpass   => new password used to update the old password
 * @renewpass => repeats the new password used to update the old password
 */
saltos.authenticate.authrenew = async (user, oldpass, newpass, renewpass) => {
    let result = null;
    await saltos.app.ajax({
        url: 'auth/renew',
        data: {
            user: user,
            oldpass: oldpass,
            newpass: newpass,
            renewpass: renewpass,
        },
        success: response => {
            result = response;
            if (response.status === 'ok') {
                saltos.token.set(response);
                return;
            }
            saltos.app.show_error(response);
        },
    });
    return result;
};
