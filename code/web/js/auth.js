
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
 * This file contains all code needed to manage the hash feature, includes the code to
 * process the onhashchange and too, includes the code to get and set the hash value, too
 * includes all code to manage tokens and to do authentications with all features suck as
 * the main authentication using a user and password pair, the checktoken and the deauthtoken
 * to control it.
 */

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
    if (hash.length && hash.substr(0, 1) == '#') {
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
 *
 * Notes:
 *
 * The # char is added by default if it is not found in the hash, additionally the dot is
 * added to force to remove all chars before the # char in the document.location
 */
saltos.hash.set = hash => {
    if (hash.length && hash.substr(0, 1) != '#') {
        hash = '#' + hash;
    }
    history.replaceState(null, null, '.' + hash);
};

/**
 * Add hash
 *
 * Function intended to add a hash in the current history, adds the pilow if it is not found
 * in the hash argument
 *
 * @hash => this must contain the hash with or without the pillow
 *
 * Notes:
 *
 * The # char is added by default if it is not found in the hash, additionally the dot is
 * added to force to remove all chars before the # char in the document.location
 */
saltos.hash.add = hash => {
    if (hash.length && hash.substr(0, 1) != '#') {
        hash = '#' + hash;
    }
    history.pushState(null, null, '.' + hash);
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
 * Hash change management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
saltos.hash.onhashchange = event => {
    // Token part
    if (!saltos.token.get()) {
        saltos.app.send_request('app/login');
        return;
    }
    // Reset the body interface
    saltos.bootstrap.modal('close');
    saltos.bootstrap.offcanvas('close');
    saltos.app.form.screen('loading');
    // Do the request
    saltos.app.send_request(saltos.hash.get());
};

/**
 * Hash change management
 *
 * Attach the hash change management function to the window
 */
window.onhashchange = saltos.hash.onhashchange;

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
 * Get expires_at function
 *
 * This function returns the expires_at stored in the localStorage
 */
saltos.token.get_expires_at = () => {
    return localStorage.getItem('saltos.token.expires_at');
};

/**
 * Set token and expires_at
 *
 * This function store the token and expires_at in the localStorage
 *
 * @response   => the object that contains the follow parameters:
 * @token      => the token that you want to store in the localStorage
 * @expires_at => the expires_at of the token that you want to store in the localStorage
 */
saltos.token.set = response => {
    localStorage.setItem('saltos.token.token', response.token);
    localStorage.setItem('saltos.token.expires_at', response.expires_at);
};

/**
 * Unset token and expires_at
 *
 * This function removes the token and expires_at in the localStorage
 */
saltos.token.unset = () => {
    localStorage.removeItem('saltos.token.token');
    localStorage.removeItem('saltos.token.expires_at');
};

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
    saltos.core.ajax({
        url: 'api/index.php?authtoken',
        data: JSON.stringify({
            'user': user,
            'pass': pass,
        }),
        method: 'post',
        content_type: 'application/json',
        async: false,
        success: response => {
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
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        }
    });
};

/**
 * Check token function
 *
 * This function uses the checktoken action to check the validity of the current token.
 */
saltos.authenticate.checktoken = () => {
    saltos.core.ajax({
        url: 'api/index.php?checktoken',
        async: false,
        success: response => {
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
            saltos.app.show_error({
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
    saltos.core.ajax({
        url: 'api/index.php?deauthtoken',
        async: false,
        success: response => {
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
            saltos.app.show_error({
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
 * Window communication helper object
 *
 * This object stores all windows communications functions to send and listen messages
 */
saltos.window = {};

/**
 * Open window
 *
 * This function is intended to open new tabs in the window, at the moment only is a wrapper to
 * the window.open but in a future, can add more features
 *
 * @url => the url of the page to load
 */
saltos.window.open = url => {
    window.open(url);
};

/**
 * Close window
 *
 * This function is intended to close the current window
 */
saltos.window.close = () => {
    window.close();
};

/**
 * Listeners helper object
 *
 * This object stores all listeners added by the set_listeners and used by the onstorage
 */
saltos.window.listeners = {};

/**
 * Set listener
 *
 * This function allos to set a listener function to the named event
 *
 * @name => the name of the event that you want to suscribe
 * @fn   => callback executed when event named is triggered
 */
saltos.window.set_listener = (name, fn) => {
    saltos.window.listeners[name] = fn;
};

/**
 * Unset listener
 *
 * This function removes a listener from the listeners object
 *
 * @name => the name of the event that you want to unsuscribe
 */
saltos.window.unset_listener = (name) => {
    delete saltos.window.listeners[name];
};

/**
 * Send message
 *
 * This function allow to send a message to all tabs using the name and data
 * as event and argument of the callback executed.
 *
 * @name  => the name of the event that you want to send
 * @data  => the arguments used by the callback function
 * @scope => the scope where the event must to be triggered (me, all, other)
 *
 * Notes:
 *
 * The usage of the localStorage causes the execution of the onstorage function
 * of the other tabs but not for the tab that send the message, to fix this we
 * are dispatching an event in the current window, this allow that all tabs
 * (including the source of the message sent) receives the notification and
 * executes the listeners if needed
 */
saltos.window.send = (name, data, scope) => {
    if (typeof scope == 'undefined') {
        var scope = 'all';
    }
    localStorage.setItem('saltos.window.name', name);
    localStorage.setItem('saltos.window.data', data);
    if (['all', 'other'].includes(scope)) {
        localStorage.setItem('saltos.window.trigger', Math.random());
    }
    if (['all', 'me'].includes(scope)) {
        window.dispatchEvent(new StorageEvent('storage', {
            storageArea: localStorage,
            key: 'saltos.window.trigger',
        }));
    }
};

/**
 * Storage management
 *
 * This function allow to SaltOS to receive the messages sended by other tabs
 * using the localStorage.
 */
saltos.window.onstorage = event => {
    if (event.storageArea != localStorage) {
        return;
    }
    if (event.key != 'saltos.window.trigger') {
        return;
    }
    var name = localStorage.getItem('saltos.window.name');
    var data = localStorage.getItem('saltos.window.data');
    if (!saltos.window.listeners.hasOwnProperty(name)) {
        return;
    }
    saltos.window.listeners[name](data);
};

/**
 * Storage management
 *
 * Attach the storage management function to the window
 */
window.onstorage = saltos.window.onstorage;
