
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
 * Console log
 *
 * This function only apply as console.log replacement for debug purposes
 *
 * @message => the message that you want to send to the console
 *
 * Notes:
 *
 * This function send a message to all clients and exists because in some cases
 * the console.log not apply for service workers, this is a simple and quick
 * solution for help in the debug process
 */
const console_log = (message) => {
    const black = 'color:white;background:dimgrey';
    const reset = 'color:inherit;background:inherit';
    const array = [`%c${message}%c`, black, reset];
    clients.matchAll().then((clients) => {
        clients.forEach((client) => {
            client.postMessage(array);
        });
    });
};

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
const debug = (action, url, type, duration) => {
    const black = 'color:white;background:dimgrey';
    const types = {
        'network': 'green',
        'cache': 'blue',
        'queue': 'orange',
        'error': 'red',
    };
    let temp = 'dimgrey';
    if (types.hasOwnProperty(type)) {
        temp = types[type];
    }
    const color = `color:white;background:${temp}`;
    const reset = 'color:inherit;background:inherit';
    const array = [
        `${action} ${url} type %c${type}%c duration %c${duration}ms%c`,
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
 *
 * This function can perform actions like fetch, cache, queue or error, in each
 * case, tries to execute each part using the order defined by the proxy header
 * that can be added from the layer application ajax call
 *
 * @request => the request that must to be processed
 */
const proxy = async request => {
    // Prepare new_request for cache usage
    const url = request.url;
    const method = request.method;
    const headers = JSON.stringify(Object.fromEntries([...request.headers]));
    const body = await request.clone().text();
    const new_request = new Request([url, method, md5(headers), md5(body)].join('/'));

    // Prepare the order list used to solve the request
    let order = request.headers.get('proxy');
    if (order === null) {
        order = 'network,cache';
    }
    order = order.split(',');

    let response = null;
    for (const i in order) {
        switch (order[i]) {
            case 'network':
                // Network feature
                try {
                    response = await fetch(request.clone(), {credentials: 'omit'});
                    (await caches.open('saltos')).put(new_request, response.clone());
                    return {
                        type: 'network',
                        response: response,
                    };
                } catch (error) {
                    //console.log(error);
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
    if (navigator.onLine) {
        response = new Response(JSON.stringify({
            'error': {
                'text': 'A network error occurred and the requested content is not cached.',
                'code': 'proxy.js:170',
            }
        }), {
            status: 200,
            headers: {'Content-Type': 'application/json'},
        });
    } else {
        response = new Response(JSON.stringify({
            'error': {
                'text': 'You are offline and the requested content is not cached',
                'code': 'proxy.js:180',
            }
        }), {
            status: 200,
            headers: {'Content-Type': 'application/json'},
        });
    }
    return {
        type: 'error',
        response: response,
    };
};

/**
 * Queue open
 *
 * This function returns a promise to the store object that can be used
 * in the add, delete or getAll features.
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
 * Queue push
 *
 * This function adds an entry to the queue system
 *
 * @data => the object that you want to store in the database
 */
const queue_push = data => {
    queue_open().then(store => {
        store.add(data);
    }).catch(error => {
        //console.log(error);
    });
};

/**
 * Queue getall
 *
 * This function returns all entries of the queue using the fifo
 * order
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
 * Queue delete
 *
 * This function allow to delete the entry identified by @key
 *
 * @key => the primaryKey that you want to delete
 */
const queue_delete = key => {
    queue_open().then(store => {
        store.delete(key);
    }).catch(error => {
        //console.log(error);
    });
};

/**
 * Request serialize
 *
 * This function allow to gets a request and returns an object
 * that can be stored in an indexedDB
 *
 * @request => the request that must to be converted into an object
 */
const request_serialize = async request => {
    const result = {
        url: request.url,
        method: request.method,
        headers: [...request.headers.entries()],
    };
    if (request.method == 'POST') {
        result.body = await request.clone().text();
    }
    return result;
};

/**
 * Request unserialize
 *
 * This function allow to convert an object stored in an indexedDB
 * into a valid request that can be used in fetch operations
 *
 * @request => the object that must to be converted into a request
 */
const request_unserialize = request => {
    const options = {
        method: request.method,
        headers: new Headers(request.headers),
    };
    if (request.method == 'POST') {
        options.body = request.body;
    }
    return new Request(request.url, options);
};

/**
 * Install binding
 *
 * This code implements the install feature
 */
self.addEventListener('install', event => {
    //console.log('install');
    skipWaiting(); // Skips waiting and activates the service worker immediately
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
    //console.log('fetch ' + event.request.url);
    const order = event.request.headers.get('proxy');
    if (['no', 'omit', 'cancel', 'bypass'].includes(order)) {
        if (event.clientId) {
            event.waitUntil(
                clients.get(event.clientId).then(client => {
                    if (client) {
                        client.postMessage(`fetch ${event.request.url}`);
                    }
                })
            );
        }
        return;
    }
    const start = Date.now();
    event.respondWith(
        proxy(event.request).then(result => {
            const end = Date.now();
            const array = debug('fetch', event.request.url, result.type, end - start);
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
 * Sync in progress flag
 *
 * Flag used to prevent concurrent execution of the sync operation in a service worker.
 * It ensures that only one sync process runs at a time by setting the flag to true
 * when the sync starts and resetting it to false once the process completes. If another
 * sync request is received while sync_in_progress is true, the new sync is ignored
 * until the current one finishes.
 */
let sync_in_progress = false;

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

    // Reset cache feature
    if (event.data == 'resetcache') {
        (await caches.keys()).forEach(key => {
            caches.delete(key);
        });
        event.source.postMessage('ok');
    }

    // Reset queue feature
    if (event.data == 'resetqueue') {
        queue_open().then(store => {
            store.clear();
        }).catch(error => {
            //console.log(error);
        });
        event.source.postMessage('ok');
    }

    // Stop feature
    if (event.data == 'stop') {
        registration.unregister();
        event.source.postMessage('ok');
    }

    // Hello feature
    if (event.data == 'hello') {
        event.source.postMessage('hello');
    }

    // Sync feature
    if (event.data == 'sync' && !sync_in_progress) {
        sync_in_progress = true;
        let total = 0;
        let count = 0;
        await queue_getall().then(async result => {
            total = result.length;
            for (const i in result) {
                const start = Date.now();
                const request = request_unserialize(result[i].value);
                let response = null;
                let type = 'error';
                try {
                    response = await fetch(request, {credentials: 'omit'});
                    if (response.ok) {
                        type = 'network';
                    }
                } catch (error) {
                    //console.log(error);
                }
                const end = Date.now();
                const array = debug('sync', request.url, type, end - start);
                event.source.postMessage(array);
                if (type == 'error') {
                    break;
                }
                queue_delete(result[i].key);
                count++;
            }
        }).catch(error => {
            //console.log(error);
        });
        event.source.postMessage(`sync ${count} of ${total}`);
        sync_in_progress = false;
    }
});
