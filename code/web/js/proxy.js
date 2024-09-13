
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
 * Proxy module
 *
 * This module provides a service worker that acts as a proxy, it uses the headers
 * requests to understand what kind of thing the request needs and tries to do
 * it with the more better manner.
 */

/**
 * TODO
 *
 * TODO
 */
var __debug = false;

/**
 * TODO
 *
 * TODO
 */
var log = msg => {
    if (!__debug) {
        return;
    }
    console.log(msg);
};

/**
 * TODO
 *
 * TODO
 */
var debug = (url, type, start) => {
    if (!__debug) {
        return;
    }
    var used = Date.now() - start;
    var black = 'color:white;background:dimgrey';
    var color = 'color:white;background:dimgrey';
    switch (type) {
        case 'network':
            color = 'color:white;background:green';
            break;
        case 'cache':
            color = 'color:white;background:blue';
            break;
        case 'error':
            color = 'color:white;background:red';
            break;
    }
    var reset = 'color:inherit;background:inherit;';
    console.log(
        `fetch %c${url}%c type %c${type}%c duration %c${used}ms%c`,
        black, reset, color, reset, black, reset,
    );
};

/**
 * Proxy function
 *
 * This function receives a request to do a fetch or try to use a cached result
 * if a network error occurs, if no response is available, a json error is returned
 * to the application layer
 */
var proxy = async request => {
    var start = Date.now();

    // Prepare new_request for cache usage
    var url = request.url;
    var method = request.method;
    var headers = {};
    request.headers.forEach((value, key) => {
        headers[key] = value;
    });
    headers = JSON.stringify(headers);
    var body = await request.clone().text();
    var new_request = new Request([url, method, md5(headers), md5(body)].join('/'));

    // Network feature
    try {
        response = await fetch(request);
    } catch (error) {
        //console.log(error);
    }
    if (response) {
        debug(url, 'network', start);
        (await caches.open('saltos')).put(new_request, response.clone());
        return response;
    }

    // Cache feature
    var response = await caches.match(new_request);
    if (response) {
        debug(url, 'cache', start);
        return response;
    }

    // Error feature
    response = new Response(JSON.stringify({
        'error': {
            'text': 'You are offline and the requested content is not cached',
            'code': 'offline'
        }
    }), {
        status: 200,
        headers: {'Content-Type': 'application/json'},
    });
    debug(url, 'error', start);
    return response;
};

/**
 * Install binding
 *
 * This code implements the install feature
 */
self.addEventListener('install', event => {
    log('install');
    self.skipWaiting(); // Skips waiting and activates the service worker immediately
});

/**
 * Activate binding
 *
 * This code implements the activate feature
 */
self.addEventListener('activate', event => {
    log('activate');
    event.waitUntil(clients.claim()); // Takes control of all open pages immediately
});

/**
 * Fetch binding
 *
 * This code implements the fetch feature
 */
self.addEventListener('fetch', event => {
    log('fetch ' + event.request.url);
    event.respondWith(proxy(event.request));
});

/**
 * Message binding
 *
 * This code implements the message feature
 *
 * Notes:
 *
 * If the message contains the reserved word reset, then all caches are deleted
 * and the service worker is released, too can receive a hello word that is intended
 * to test the comunication between the proxy and the app layer
 */
self.addEventListener('message', async event => {
    log('message ' + event.data);

    // Reset feature
    if (event.data == 'reset') {
        (await caches.keys()).forEach(key => {
            caches.delete(key);
        });
        self.registration.unregister();
        event.source.postMessage('ok');
    }

    // Hello feature
    if (event.data == 'hello') {
        event.source.postMessage('hello');
    }

    // Debug on feature
    if (event.data == 'debug=on') {
        if (!__debug) {
            __debug = true;
            event.source.postMessage('ok');
        } else {
            event.source.postMessage('ko');
        }
    }

    // Debug off feature
    if (event.data == 'debug=off') {
        if (__debug) {
            __debug = false;
            event.source.postMessage('ok');
        } else {
            event.source.postMessage('ko');
        }
    }
});
