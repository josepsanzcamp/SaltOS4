
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
 * Debug function
 *
 * This function returns an array with the needed things to print into a console.log
 * the fetch message with the url, the response type and the duration of the task
 *
 * @url      => url of the request
 * @type     => type of the request (network, cache or error)
 * @duration => duration of the entire task in milliseconds
 */
const debug = (url, type, duration) => {
    const black = 'color:white;background:dimgrey';
    let color = 'color:white;background:dimgrey';
    switch (type) {
        case 'network':
            color = 'color:white;background:green';
            break;
        case 'cache':
            color = 'color:white;background:blue';
            break;
        case 'queue':
            color = 'color:white;background:orange';
            break;
        case 'error':
            color = 'color:white;background:red';
            break;
    }
    const reset = 'color:inherit;background:inherit;';
    const array = [
        `fetch ${url} type %c${type}%c duration %c${duration}ms%c`,
        color, reset, black, reset,
    ];
    //console.log(...array);
    return array;
};

/**
 * Proxy function
 *
 * This function receives a request to do a fetch or try to use a cached result
 * if a network error occurs, if no response is available, a json error is returned
 * to the application layer
 */
const proxy = async request => {
    // Prepare new_request for cache usage
    const url = request.url;
    const method = request.method;
    const headers = JSON.stringify(Object.fromEntries([...request.headers]));
    const body = await request.clone().text();
    const array = [url, method];
    const is_api = url.includes('/api/?/');
    if (is_api) {
        array.push(md5(headers));
    }
    if (method == 'POST') {
        array.push(md5(body));
    }
    const new_request = new Request(array.join('/'));

    // Prepare the order list used to solve the request
    let order = request.headers.get('proxy');
    if (order === null) {
        const is_index = url.includes('/#/');
        if (is_api || is_index) {
            order = 'network,cache';
        } else {
            order = 'cache,network';
        }
    }
    order = order.split(',');

    for (let i in order) {
        switch (order[i]) {
            case 'network':
                // Network feature
                try {
                    var response = await fetch(request.clone());
                } catch (error) {
                    //console.log(error);
                }
                if (response) {
                    (await caches.open('saltos')).put(new_request, response.clone());
                    return {
                        type: 'network',
                        response: response,
                    };
                }
                break;

            case 'cache':
                // Cache feature
                response = await caches.match(new_request);
                if (response) {
                    return {
                        type: 'cache',
                        response: response,
                    };
                }
                break;

            case 'queue':
                // Queue feature
                queue_push(await request_serialize(request));
                response = new Response(JSON.stringify({
                    'status': 'ok',
                }), {
                    status: 200,
                    headers: {'Content-Type': 'application/json'},
                });
                return {
                    type: 'queue',
                    response: response,
                };
        }
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
    return {
        type: 'error',
        response: response,
    };
};

/**
 * TODO
 *
 * TODO
 */
const queue_open = () => {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('saltos', 1);

        request.onupgradeneeded = event => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('saltos')) {
                const objectStore = db.createObjectStore('saltos', {
                    autoIncrement: true,
                });
            }
        };

        request.onsuccess = event => {
            const db = event.target.result;
            const transaction = db.transaction('saltos', 'readwrite');
            const store = transaction.objectStore('saltos');
            resolve(store);
        };

        request.onerror = event => {
            reject(event);
        };
    });
};

/**
 * TODO
 *
 * TODO
 */
const queue_push = data => {
    queue_open().then(store => {
        store.add(data);
    }).catch(error => {
        //console.log(error);
    });
};

/**
 * TODO
 *
 * TODO
 */
const queue_getall = () => {
    return new Promise((resolve, reject) => {
        queue_open().then(store => {
            const items = store.getAll();
            const keys = store.getAllKeys();

            items.onsuccess = () => {
                keys.onsuccess = () => {
                    const result = keys.result.map((key, index) => ({
                        key: key,
                        value: items.result[index],
                    }));

                    resolve(result);
                };
            };

            items.onerror = (event) => reject(event);
            keys.onerror = (event) => reject(event);
        }).catch(error => {
            //console.log(error);
        });
    });
};

/**
 * TODO
 *
 * TODO
 */
const queue_delete = key => {
    queue_open().then(store => {
        store.delete(key);
    }).catch(error => {
        //console.log(error);
    });
};

/**
 * TODO
 *
 * TODO
 */
const request_serialize = async request => {
    return {
        url: request.url,
        method: request.method,
        headers: [...request.headers.entries()],
        body: await request.clone().text(),
    };
};

/**
 * TODO
 *
 * TODO
 */
const request_unserialize = request => {
    return new Request(request.url, {
        method: request.method,
        headers: new Headers(request.headers),
        body: request.body,
    });
};

/**
 * Install binding
 *
 * This code implements the install feature
 */
self.addEventListener('install', event => {
    //console.log('install');
    self.skipWaiting(); // Skips waiting and activates the service worker immediately
});

/**
 * Activate binding
 *
 * This code implements the activate feature
 */
self.addEventListener('activate', event => {
    //console.log('activate');
    event.waitUntil(clients.claim()); // Takes control of all open pages immediately
});

/**
 * Fetch binding
 *
 * This code implements the fetch feature
 */
self.addEventListener('fetch', event => {
    //~ console.log('fetch ' + event.request.url);
    const start = Date.now();
    event.respondWith(
        proxy(event.request).then(result => {
            const end = Date.now();
            const array = debug(event.request.url, result.type, end - start);
            if (event.clientId) {
                event.waitUntil(
                    clients.get(event.clientId).then(client => {
                        if (client) {
                            client.postMessage(array);
                        }
                    })
                );
            }
            return result.response;
        })
    );
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
    //console.log('message ' + event.data);

    // Reset feature
    if (event.data == 'reset') {
        (await caches.keys()).forEach(key => {
            caches.delete(key);
        });
        event.source.postMessage('ok');
    }

    // Stop feature
    if (event.data == 'stop') {
        self.registration.unregister();
        event.source.postMessage('ok');
    }

    // Hello feature
    if (event.data == 'hello') {
        event.source.postMessage('hello');
    }

    // Sync feature
    if (event.data == 'sync') {
        let total = 0;
        await queue_getall().then(async result => {
            for (let i in result) {
                try {
                    await fetch(request_unserialize(result[i].value));
                } catch (error) {
                    //console.log(error);
                    break;
                }
                queue_delete(result[i].key);
                total++;
            }
        }).catch(error => {
            //console.log(error);
        });
        event.source.postMessage(total);
    }
});
