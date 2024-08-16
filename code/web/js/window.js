
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
 * Window helper module
 *
 * This function provides all needed code to manage the windows (open and close) and too
 * to provide a tool to send messages between tabs inside the same browser.
 */

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
    if (url.substr(0, 4) == 'app/') {
        window.open('.#/' + url);
        return;
    }
    if (url.substr(0, 7) == 'http://') {
        window.open(url);
        return;
    }
    if (url.substr(0, 8) == 'https://') {
        window.open(url);
        return;
    }
    throw new Error(`unknown url ${url}`);
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
    if (typeof data == 'undefined') {
        var data = '';
    }
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
