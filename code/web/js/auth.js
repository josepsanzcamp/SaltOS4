
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
saltos.authenticate.authtoken = (user, pass) => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?auth/login',
        data: JSON.stringify({
            'user': user,
            'pass': pass,
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
                saltos.token.set(response);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
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
        lang: saltos.gettext.get(),
    });
};

/**
 * Check token function
 *
 * This function uses the checktoken action to check the validity of the current token.
 */
saltos.authenticate.checktoken = () => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?auth/check',
        async: false,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
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

/**
 * De-authenticate token function
 *
 * This function uses the deauthtoken action to try to de-authenticate an user with the token
 * credentials.
 */
saltos.authenticate.deauthtoken = () => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?auth/logout',
        async: false,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
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

/**
 * TODO
 *
 * TODO
 */
saltos.authenticate.authupdate = (oldpass, newpass, renewpass) => {
    saltos.app.form.screen('loading');
    saltos.core.ajax({
        url: 'api/?auth/update',
        data: JSON.stringify({
            'oldpass': oldpass,
            'newpass': newpass,
            'renewpass': renewpass,
        }),
        method: 'post',
        content_type: 'application/json',
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
