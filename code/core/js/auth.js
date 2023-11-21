
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
 * Authentication helper module
 *
 * This file contains all code needed to manage the hash feature, includes the code to
 * process the onhashchange and too, includes the code to get and set the hash value, too
 * includes all code to manage tokens and to do authentications with all features suck as
 * the main authentication using a user and password pair, the reauthtoken, the deauthtoken
 * and the checktoken to control it.
 */

/**
 * Hash change management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
window.onhashchange = event => {
    // Reset the body interface
    saltos.modal('close');
    saltos.offcanvas('close');
    saltos.loading(true);
    // Do the request
    saltos.send_request(saltos.hash.get());
};

/**
 * Hash helper object
 *
 * This object stores all hash functions to get, set and trigger a change
 */
saltos.hash = {};

/**
 * Get hash
 *
 * Function intended to return the current hash without the pillow
 */
saltos.hash.get = () => {
    var hash = document.location.hash;
    if (hash.substr(0, 1) == '#') {
        hash = hash.substr(1);
    }
    return hash;
};

/**
 * Set hash
 *
 * Function intended to replace the hash in the current url, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash => this must contain the hash with or without the pillow
 */
saltos.hash.set = hash => {
    if (hash.substr(0, 1) != '#') {
        hash = '#' + hash;
    }
    history.replaceState(null, null, hash);
};

/**
 * Add hash
 *
 * Function intended to add a hash in the current history, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash => this must contain the hash with or without the pillow
 */
saltos.hash.add = hash => {
    if (hash.substr(0, 1) != '#') {
        hash = '#' + hash;
    }
    history.pushState(null, null, hash);
};

/**
 * Change trigger
 *
 * This function triggers the hashchange event to execute the onhashchange
 */
saltos.hash.trigger = () => {
    window.dispatchEvent(new HashChangeEvent('hashchange'));
};

/**
 * Token helper object
 *
 * This object stores all token functions to get and set data using the localStorage
 */
saltos.token = {};

/**
 * Get token function
 *
 * This function returns the token stored in the localStorage
 */
saltos.token.get = () => {
    return localStorage.getItem('saltos.token.token');
};

/**
 * Get expires function
 *
 * This function returns the expires stored in the localStorage
 */
saltos.token.get_expires = () => {
    return localStorage.getItem('saltos.token.expires');
};

/**
 * Get autorenew function
 *
 * This function returns the autorenew stored in the localStorage
 */
saltos.token.get_autorenew = () => {
    return localStorage.getItem('saltos.token.autorenew') * 1000;
};

/**
 * Get autocheck function
 *
 * This function returns the autocheck stored in the localStorage
 */
saltos.token.get_autocheck = () => {
    return localStorage.getItem('saltos.token.autocheck') * 1000;
};

/**
 * Set token and expires
 *
 * This function store the token and expires in the localStorage
 *
 * @response  => the object that contains the follow parameters:
 * @token     => the token that you want to store in the localStorage
 * @expires   => the expires of the token that you want to store in the localStorage
 * @autorenew => the autorenew of the token that you can use to force an autorenew
 * @autocheck => the autocheck of the token that you can use to poll the autorenew
 */
saltos.token.set = response => {
    localStorage.setItem('saltos.token.token', response.token);
    localStorage.setItem('saltos.token.expires', response.expires_at);
    localStorage.setItem('saltos.token.autorenew', response.autorenew_at);
    localStorage.setItem('saltos.token.autocheck', response.autocheck_at);
};

/**
 * Unset token and expires
 *
 * This function removes the token and expires in the localStorage
 */
saltos.token.unset = () => {
    localStorage.removeItem('saltos.token.token');
    localStorage.removeItem('saltos.token.expires');
    localStorage.removeItem('saltos.token.autorenew');
    localStorage.removeItem('saltos.token.autocheck');
};

/**
 * Authentication helper object
 *
 * This object stores all authentication functions to get access, renew tokens to maintain
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
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'authtoken',
            'user': user,
            'pass': pass,
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.set(response);
                saltos.authenticate.autorenew(true);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
                saltos.authenticate.autorenew();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        }
    });
};

/**
 * Re-authenticate token function
 *
 * This function uses the reauthtoken action to try to re-authenticate an user with the token
 * credentials.
 */
saltos.authenticate.reauthtoken = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'reauthtoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.set(response);
                saltos.authenticate.autorenew(true);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
                saltos.authenticate.autorenew();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get(),
        }
    });
};

/**
 * De-authenticate token function
 *
 * This function uses the deauthtoken action to try to de-authenticate an user with the token
 * credentials.
 */
saltos.authenticate.deauthtoken = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'deauthtoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.unset();
                saltos.authenticate.autorenew();
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
                saltos.authenticate.autorenew();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get(),
        }
    });
};

/**
 * Check token function
 *
 * This function uses the checktoken action to check the validity of the current token.
 */
saltos.authenticate.checktoken = () => {
    saltos.ajax({
        url: 'index.php',
        data: JSON.stringify({
            'action': 'checktoken',
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
            if (!saltos.check_response(response)) {
                return;
            }
            if (response.status == 'ok') {
                saltos.token.set(response);
                saltos.authenticate.autorenew(true);
                return;
            }
            if (response.status == 'ko') {
                saltos.token.unset();
                saltos.authenticate.autorenew();
                return;
            }
            saltos.show_error(response);
        },
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            'token': saltos.token.get(),
        }
    });
};

/**
 * Re-authenticate helper function
 *
 * This function checks the reminder of the token's expires and if is needed, execute the renew
 * token action.
 */
saltos.authenticate.checkrenew = () => {
    var t1 = new Date(saltos.token.get_expires()).getTime();
    var t2 = new Date().getTime();
    var t3 = t1 - t2;
    if (t3 < saltos.token.get_autorenew()) {
        saltos.authenticate.reauthtoken();
    }
};

/**
 * Variable used to store the timer
 *
 * This variable must contains the timer of the auto renew token feature
 */
saltos.authenticate.__autorenew_timer = null;

/**
 * Auto-renew helper function
 *
 * This function allow to enable or disable the auto renew token feature, can receive
 * an argument to specify if it must to enable or disable the feature, it is intended
 * to be used when set or unset the token.
 *
 * @on_off => the parameter to indicates if you want to enable or disable the feature
 */
saltos.authenticate.autorenew = on_off => {
    if (on_off && !saltos.authenticate.__autorenew_timer) {
        saltos.authenticate.checkrenew();
        saltos.authenticate.__autorenew_timer = setInterval(
            saltos.authenticate.checkrenew,
            saltos.token.get_autocheck()
        );
    }
    if (!on_off && saltos.authenticate.__autorenew_timer) {
        clearInterval(saltos.authenticate.__autorenew_timer);
        saltos.authenticate.__autorenew_timer = null;
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.tabs = {};

/**
 * TODO
 *
 * TODO
 */
saltos.tabs.listeners = {};

/**
 * TODO
 *
 * TODO
 */
saltos.tabs.set_listener = (name, fn) => {
    saltos.tabs.listeners[name] = fn;
};

/**
 * TODO
 *
 * TODO
 */
saltos.tabs.unset_listener = (name, fn) => {
    delete saltos.tabs.listeners[name];
};

/**
 * TODO
 *
 * TODO
 */
saltos.tabs.send = (name, data) => {
    localStorage.setItem('saltos.tabs.name', name);
    localStorage.setItem('saltos.tabs.data', data);
    localStorage.setItem('saltos.tabs.trigger', Math.random());
    window.dispatchEvent(new StorageEvent('storage', {
        storageArea: localStorage,
        key: 'saltos.tabs.trigger',
    }));
};

/**
 * TODO
 *
 * TODO
 */
window.onstorage = event => {
    if (event.storageArea != localStorage) {
        return;
    }
    if (event.key != 'saltos.tabs.trigger') {
        return;
    }
    var name = localStorage.getItem('saltos.tabs.name');
    var data = localStorage.getItem('saltos.tabs.data');
    if (!saltos.tabs.listeners.hasOwnProperty(name)) {
        return;
    }
    saltos.tabs.listeners[name](data);
};
