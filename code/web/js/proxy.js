
'use strict';

/**
 * Proxy
 *
 * This function receives a request to do a fetch or try to use a cached result
 * if a network error occurs, if no response is available, a json error is returned
 * to the application layer
 */
var proxy = async request => {
    var url = request.url;
    var method = request.method;
    var headers = {};
    request.headers.forEach((value, key) => {
        headers[key] = value;
    });
    headers = JSON.stringify(headers);
    var body = await request.clone().text();
    var new_request = new Request([url, method, md5(headers), md5(body)].join('/'));

    try {
        response = await fetch(request);
    } catch (error) {
        console.log(error);
    }
    if (response) {
        console.log('using network response for ' + url);
        (await caches.open('saltos')).put(new_request, response.clone());
        return response;
    }

    var response = await caches.match(new_request);
    if (response) {
        console.log('using cache response for ' + url);
        return response;
    }

    response = new Response(JSON.stringify({
        "error": {
            "text": "You are offline and the requested content is not cached",
            "code": "offline"
        }
    }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
    });
    console.log('using error response for ' + url);
    return response;
};

/**
 * Install binding
 *
 * This code implements the install feature
 */
self.addEventListener('install', event => {
    console.log('install');
    self.skipWaiting();
});

/**
 * Activate binding
 *
 * This code implements the activate feature
 */
self.addEventListener('activate', event => {
    console.log('activate');
});

/**
 * Fetch binding
 *
 * This code implements the fetch feature
 */
self.addEventListener('fetch', event => {
    console.log('fetch ' + event.request.url);
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
    console.log('message ' + event.data);
    //~ event.source.postMessage(event.data);
    if (event.data == 'reset') {
        (await caches.keys()).forEach(key => {
            caches.delete(key);
        });
        self.registration.unregister();
        event.source.postMessage('ok');
    }
    if (event.data == 'hello') {
        event.source.postMessage('hello');
    }
});
