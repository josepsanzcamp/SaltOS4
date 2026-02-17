
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
 * @user => username used to the authentication process
 * @pass => password used to the authentication process
 */
saltos.authenticate.authtoken = async (user, pass) => {
    await saltos.app.ajax({
        url: 'auth/login',
        data: {
            user: user,
            pass: pass,
        },
        success: response => {
            if (response.status == 'ok') {
                saltos.token.set(response);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
                return;
            }
            saltos.app.show_error(response);
        },
    });
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
            if (response.status == 'ok') {
                saltos.token.set(response);
                return;
            }
            if (response.status == 'ko') {
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
            if (response.status == 'ok') {
                saltos.token.unset();
                return;
            }
            if (response.status == 'ko') {
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
 * by the user.
 *
 * @oldpass   => old password used to validate the correctness of the transaction
 * @newpass   => new password used to update the old password
 * @renewpass => repite the new password used to update the old password
 */
saltos.authenticate.authupdate = (oldpass, newpass, renewpass) => {
    saltos.app.ajax({
        url: 'auth/update',
        data: {
            oldpass: oldpass,
            newpass: newpass,
            renewpass: renewpass,
        },
        success: response => {
            if (response.status == 'ok') {
                saltos.app.modal('Response', 'Password updated successfully');
                saltos.hash.trigger();
                return;
            }
            saltos.app.show_error(response);
        },
    });
};
