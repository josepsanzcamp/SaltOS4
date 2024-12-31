
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
 * Core helper module
 *
 * This fie contains useful functions related to the core application, provides the low level features
 * for manage errors, logs, manipulates html and DOM objects, manage ajax requests, and more things
 */

/**
 * Core helper object
 *
 * This object stores all core functions and data
 */
saltos.core = {};

/**
 * Error management
 *
 * This function allow to SaltOS to log in server the javascript errors produced in the
 * client's browser
 *
 * @message => the error message
 * @source  => the filename where the error was triggered
 * @lineno  => the line number where the error was triggered
 * @colno   => the column number where the error was triggered
 * @stack   => the backtrace stack of all execution until the error
 *
 * Notes:
 *
 * This function is called from some addEventListeners, the original was the error event
 * but when change the ajax requests from xhr to fetch to setup the proxy feature, the
 * errors was mapped by the event unhandledrejection.
 */
saltos.core.adderror = async (message, source, lineno, colno, stack) => {
    const data = {
        jserror: message,
        details: `Error on file ${source}:${lineno}:${colno}, userAgent is ${navigator.userAgent}`,
        backtrace: 'unknown',
    };
    let finished = false;
    window.sourceMappedStackTrace.mapStackTrace(stack, mappedStack => {
        mappedStack = mappedStack.map(line => line.trim());
        data.backtrace = mappedStack.join('\n');
        finished = true;
    }, {
        filter: line => !line.includes(' > '),
    });
    while (!finished) {
        await new Promise(resolve => setTimeout(resolve, 1));
    }
    saltos.core.ajax({
        url: 'api/?/add/error',
        data: JSON.stringify(data),
        method: 'post',
        content_type: 'application/json',
        proxy: 'network,queue',
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};

/**
 * Old error handler
 *
 * This code allow to capture the old errors triggered outside the fetch requests
 */
window.addEventListener('error', event => {
    let backtrace = 'unknown';
    if (event.error && 'stack' in event.error) {
        backtrace = event.error.stack;
    }
    saltos.core.adderror(
        event.message,
        event.filename,
        event.lineno,
        event.colno,
        backtrace
    );
});

/**
 * New error handler
 *
 * This code allow to capture the new errors triggered inside the fetch requests
 */
window.addEventListener('unhandledrejection', event => {
    let backtrace = 'unknown';
    if (event.reason && 'stack' in event.reason) {
        backtrace = event.reason.stack;
    }
    saltos.core.adderror(
        event.reason.name + ': ' + event.reason.message,
        event.reason.fileName,
        event.reason.lineNumber,
        event.reason.columnNumber,
        backtrace
    );
});

/**
 * Log management
 *
 * This function allow to send messages to the addlog of the server side, requires an argument:
 *
 * @msg => the message that do you want to log on the server log file
 */
saltos.core.addlog = msg => {
    const data = {
        'msg': msg,
    };
    saltos.core.ajax({
        url: 'api/?/add/log',
        data: JSON.stringify(data),
        method: 'post',
        content_type: 'application/json',
        proxy: 'network,queue',
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};

/**
 * Check params
 *
 * This function allow to prepare parameters to be used by other functions, the main idea
 * is that the other functions can access to properties of an object without getting errors
 * caused by the nonexistence, to do this, checks for the existence of all params in the obj
 * and if some param is not found, then define it using the default value passed:
 *
 * @obj    => the object that contains the arguments, for example
 * @params => an array with the arguments that must to exists
 * @value  => the default value used if an argument doesn't exists
 */
saltos.core.check_params = (obj, params, value = '') => {
    for (const key in params) {
        if (!(params[key] in obj)) {
            obj[params[key]] = value;
        } else if (obj[params[key]] === undefined) {
            obj[params[key]] = value;
        }
    }
};

/**
 * UniqID
 *
 * This function generates an unique id formed by the word 'id' and a number that can take
 * values between 0 and 999999, useful when some widget requires an id and the user don't
 * provide it to the widget constructor
 */
saltos.core.uniqid = () => {
    return 'id' + Math.random().toString(36).substr(2);
};

/**
 * When visible
 *
 * This function allow to execute some code when the object is visible, useful for third part
 * widgets as ckeditor or codemirror that requires a rendered environemt to initialize their
 * code and paint the widget correctly
 *
 * @obj  => the object that do you want to monitorize the visibility
 * @fn   => the callback that you want to execute
 * @args => the arguments passed to the callback when execute it
 *
 * Notes:
 *
 * As an extra feature, the object can be an string containing the id of the object, intended
 * to be used when the object not exists at the moment to call this function
 */
saltos.core.when_visible = (obj, fn, args) => {
    let id;
    if (typeof obj == 'object') {
        // Check for the id existence
        if (!obj.getAttribute('id')) {
            obj.setAttribute('id', saltos.core.uniqid());
        }
        id = obj.getAttribute('id');
    } else if (typeof obj == 'string') {
        id = obj;
    } else {
        throw new Error('Unknown when_visible obj typeof' + typeof obj);
    }
    // Launch the interval each millisecond, the idea is wait until found
    // the object and then, validate that not dissapear and wait until the
    // object is visible to execute the fn(args)
    let step = 1;
    const interval = setInterval(() => {
        const obj2 = document.getElementById(id);
        if (step == 1) {
            // Maintain the state machine in the first state until found
            // the object in the document
            if (obj2) {
                step++;
            }
        }
        if (step == 2) {
            // Here, the object is found in the document, we can continue
            if (!obj2) {
                // Here, the object has disappeared, we can stop the timer
                clearInterval(interval);
                throw new Error(`#${id} not found`);
            } else if (obj2.offsetParent) {
                // Here, the object is visible, we can finish our mission
                clearInterval(interval);
                fn(args);
            }
        }
    }, 1);
};

/**
 * Get keycode
 *
 * This function allow to get the keycode of a keyboard event detecting the browser
 *
 * @event => the event that contains the keyboard data
 */
saltos.core.get_keycode = event => {
    let keycode = 0;
    if (event.keyCode) {
        keycode = event.keyCode;
    } else if (event.which) {
        keycode = event.which;
    } else {
        keycode = event.charCode;
    }
    return keycode;
};

/**
 * HTML builder
 *
 * This function allow to create an DOM fragment from a string that contains html code, can
 * work with one or two arguments:
 *
 * @type => the type used when create the container element
 * @html => contains the html code that you want to use as template
 *
 * The main use is only using the html argument and omiting the type, in this case, the
 * type used will be a div, but if you want to create a fragment of object, for example
 * as tr or td, you need to specify that the coontainer type used to create the objects
 * must to be a table or tr, is you don't specify the type, the div container creates
 * a breaked portion of the element and they don't works as expected because the DOM
 * builded is bad, you can see this problem in action when work with tables and try to
 * create separate portions of the table as trs or tds.
 */
saltos.core.html = (...args) => {
    let type = 'div';
    let html = '';
    if (args.length == 1) {
        html = args[0];
    }
    if (args.length == 2) {
        type = args[0];
        html = args[1];
    }
    let obj = document.createElement(type);
    obj.innerHTML = html.trim();
    obj = saltos.core.optimize(obj);
    return obj;
};

/**
 * Ajax helper array
 *
 * This array allow to the ajax feature to manage the active request, intended to abort
 * if it is needed when onhashchange.
 */
saltos.core.__ajax = [];

/**
 * AJAX
 *
 * This function allow to use ajax using the same form that with jQuery without jQuery
 *
 * @url          => url of the ajax call
 * @data         => data used in the body of the request
 * @method       => the method of the request (can be GET or POST, GET by default)
 * @success      => callback function for the success action (optional)
 * @error        => callback function for the error action (optional)
 * @abort        => callback function for the abort action (optional)
 * @abortable    => boolean to enable or disable the abort feature (false by default)
 * @content_type => the content-type that you want to use in the transfer
 * @proxy        => add the Proxy header with the value passed, intended to be used by the SaltOS PROXY
 * @token        => add the Token header with the value passed, intended to be used by the SaltOS API
 * @lang         => add the Lang header with the value passed, intended to be used by the SaltOS API
 * @headers      => an object with the headers that you want to send
 *
 * The main idea of this function is to abstract the usage of the XMLHttpRequest in a simple
 * way as jQuery do but without using jQuery.
 *
 * The catch part is intended to control the errors caused during the ajax execution,
 * in this case is important to understand that the catch can be triggered by errors
 * caused by network errors or by other cases like a code error, a throw new error or
 * something similar like this, to fix it, the error and abort arguments will be used
 * only when abortError or typeError appear in the error.name
 */
saltos.core.ajax = args => {
    saltos.core.check_params(args, ['url', 'data', 'method', 'success', 'error',
        'abort', 'abortable', 'content_type', 'proxy', 'token', 'lang', 'headers']);
    if (args.data == '') {
        args.data = null;
    }
    if (args.method == '') {
        args.method = 'GET';
    }
    args.method = args.method.toUpperCase();
    if (!['GET', 'POST'].includes(args.method)) {
        throw new Error(`Unknown ${args.method} method`);
    }
    if (args.headers == '') {
        args.headers = {};
    }
    if (args.content_type != '') {
        args.headers['Content-Type'] = args.content_type;
    }
    if (args.proxy != '') {
        args.headers[`Proxy`] = args.proxy;
    }
    if (args.token != '') {
        args.headers[`Token`] = args.token;
    }
    if (args.lang != '') {
        args.headers[`Lang`] = args.lang;
    }
    const options = {
        method: args.method,
        headers: args.headers,
        credentials: 'omit',
        referrerPolicy: 'no-referrer',
        mode: 'same-origin',
    };
    let controller = null;
    if (saltos.core.eval_bool(args.abortable)) {
        controller = new AbortController();
        saltos.core.__ajax.push(controller);
        options.signal = controller.signal;
    }
    if (args.method == 'POST') {
        options.body = args.data;
    }
    const start = Date.now();
    return fetch(args.url, options).then(async response => {
        const end = Date.now();
        if (!response.ok) {
            args.error(response);
            return;
        }
        // Check for the about in the response header
        if (!('about' in saltos.core)) {
            const about = response.headers.get('about');
            if (about) {
                saltos.core.about = response.headers.get('about');
            }
        }
        // Process response
        let data;
        const type = response.headers.get('content-type').toUpperCase();
        if (type.includes('JSON')) {
            data = await response.json();
        } else if (type.includes('XML')) {
            data = await response.text();
            const parser = new DOMParser();
            data = parser.parseFromString(data, 'application/xml');
        } else {
            data = await response.text();
        }
        // Add the trace if no proxy is set
        if (!response.headers.get('proxy')) {
            const url = new URL(args.url, window.location.href).href;
            const duration = end - start;
            const size = saltos.core.human_size(JSON.stringify([url, options]).length);
            const headers = JSON.stringify(Object.fromEntries([...response.headers]));
            const size2 = saltos.core.human_size(JSON.stringify([headers, data]).length);
            const black = 'color:white;background:dimgrey';
            const reset = 'color:inherit;background:inherit';
            const array = [
                `%cCORE%c fetch ${url} duration %c${duration}ms%c size %c${size}/${size2}%c`,
                black, reset, black, reset, black, reset,
            ];
            console.log(...array);
        }
        // Finish with success or return;
        if (typeof args.success == 'function') {
            args.success(data, response);
        }
    }).catch(error => {
        if (error.name == 'AbortError') {
            if (typeof args.abort == 'function') {
                args.abort(error);
            }
        } else if (error.name == 'TypeError') {
            if (typeof args.error == 'function') {
                args.error(error);
            }
        } else {
            throw new Error(error);
        }
    }).finally(() => {
        // Remove the element of the ajax request list
        if (saltos.core.eval_bool(args.abortable)) {
            for (const i in saltos.core.__ajax) {
                if (saltos.core.__ajax[i] === controller) {
                    delete saltos.core.__ajax[i];
                }
            }
        }
    });
};

/**
 * Fix key
 *
 * This function is intended to fix the keys of the objects, this is caused because you can not
 * have 2 repeated keys in an object, to have more entries with the same name, SaltOS add a suffix
 * by adding #num, with this trick, SaltOS is able to process XML files with the same node name
 * and convert it to an array structure, and when convert this to json, the same problem appear and
 * for this reason, exists this function here
 *
 * @arg => can be an string or an array of strings and returns the same structure with the keys fixed
 */
saltos.core.fix_key = arg => {
    if (typeof arg == 'object') {
        for (const key in arg) {
            arg[key] = saltos.core.fix_key(arg[key]);
        }
        return arg;
    }
    let pos = arg.indexOf('#');
    if (pos != -1) {
        arg = arg.substr(0, pos);
    }
    return arg;
};

/**
 * Copy object
 *
 * This function is intended to do copies of objects using as intermediate a json file
 *
 * @arg => the object that you want to copy
 */
saltos.core.copy_object = arg => {
    return JSON.parse(JSON.stringify(arg));
};

/**
 * Optimizer object
 *
 * This function checks an object to see if only contains one children and in this case, returns
 * directly the children instead of the original object, otherwise nothing to do and returns the
 * original object
 *
 * @obj => the object to check and optimize
 */
saltos.core.optimize = obj => {
    if (obj.children.length == 1) {
        return obj.firstElementChild;
    }
    return obj;
};

/**
 * Require helper array
 *
 * This array allow to the require feature to control the loaded libraries
 */
saltos.core.__require = {};

/**
 * Require feature
 *
 * This function allow the other functions to declare their requirements to previously load the
 * desired file intead of create the object and throwing an error.
 *
 * @file => the file desired to be loaded
 *
 * Notes:
 *
 * This function is intended to load styles (css files) or javacript code (js files), in each
 * case, they uses a different technique, for css the load is asynchronous and for javascript
 * the load will be synchronous.
 */
saltos.core.require = (files, callback) => {
    files.reduce((promiseChain, file) => {
        return promiseChain.then(async () => {
            // To prevent duplicates
            if (file in saltos.core.__require) {
                while (saltos.core.__require[file] != 'load') {
                    await new Promise(resolve => setTimeout(resolve, 1));
                }
                return;
            }
            saltos.core.__require[file] = 'loading';
            // Continue
            try {
                const response = await fetch(file, {
                    credentials: 'omit',
                    referrerPolicy: 'no-referrer',
                    mode: 'same-origin',
                });
                if (!response.ok) {
                    throw new Error(`${response.status} ${response.statusText} loading ${file}`);
                }
                const data = await response.text();
                // Hash check if exists
                const pos = file.indexOf('?');
                if (pos != -1) {
                    const hash = file.substr(pos + 1);
                    if (md5(data) != hash) {
                        throw new Error(`Hash error loading ${file}`);
                    }
                }
                // Now, add the tag to load the resource (previously prefetched)
                if (file.substr(-4) == '.css' || file.includes('.css?')) {
                    const style = document.createElement('style');
                    style.innerHTML = data;
                    document.head.append(style);
                }
                if (file.substr(-3) == '.js' || file.includes('.js?')) {
                    const script = document.createElement('script');
                    script.innerHTML = data;
                    document.head.append(script);
                }
                if (file.substr(-4) == '.mjs' || file.includes('.mjs?')) {
                    const script = document.createElement('script');
                    script.type = 'module';
                    script.innerHTML = data;
                    document.head.append(script);
                }
                saltos.core.__require[file] = 'load';
            } catch (error) {
                throw new Error(`${error.name} ${error.message} loading ${file}`);
            }
        });
    }, Promise.resolve()).then(() => {
        callback();
    });
};

/**
 * Eval Bool
 *
 * This function returns a boolean depending on the input evaluation, the main idea
 * is to get an string, for example, and determine if must be considered true or false
 * otherwise returns the original argument and send a log message to the console.
 *
 * The valid inputs are the strings one, zero, void, true, false, on, off, yes and no
 *
 * @arg => the value that do you want to evaluates as boolean
 *
 * Notes:
 *
 * This function is the same feature that the same function proviced by the backend by the
 * php/autoload/xml2array.php file with more javascript details as type detection.
 */
saltos.core.eval_bool = arg => {
    if (arg === undefined) {
        return false;
    }
    if (arg === null) {
        return false;
    }
    if (typeof arg == 'boolean') {
        return arg;
    }
    if (typeof arg == 'number') {
        return arg ? true : false;
    }
    if (typeof arg == 'string') {
        if (arg == '') {
            return false;
        }
        const bools = {
            '1': true,
            '0': false,
            'true': true,
            'false': false,
            'on': true,
            'off': false,
            'yes': true,
            'no': false,
        };
        const bool = arg.toLowerCase();
        if (bool in bools) {
            return bools[bool];
        }
    }
    throw new Error('Unknown eval_bool typeof ' + typeof arg);
};

/**
 * toString function
 *
 * This function tries to convert to string from any other formats as boolean,
 * number, null, undefined or other type.
 */
saltos.core.toString = arg => {
    if (arg === undefined) {
        return 'undefined';
    }
    if (arg === null) {
        return 'null';
    }
    if (typeof arg == 'boolean') {
        return arg ? 'true' : 'false';
    }
    if (typeof arg == 'number') {
        return arg.toString();
    }
    if (typeof arg == 'string') {
        return arg;
    }
    throw new Error('Unknown toString typeof ' + typeof arg);
};

/**
 * Is attr value
 *
 * This function return true if the data argument is an object with #attr and value
 *
 * @data => the data that wants to check
 */
saltos.core.is_attr_value = data => {
    return typeof data == 'object' && '#attr' in data && 'value' in data;
};

/**
 * Join attr value
 *
 * This function return an object that contains all elements of the #attr and value
 *
 * @data => the data that wants to join
 *
 * Notes:
 *
 * If the content of value is only a string, then the result will be an array with the
 * elements of the #attr joined with a new element with the name value and with the contents
 * of the original value.
 */
saltos.core.join_attr_value = data => {
    if (saltos.core.is_attr_value(data)) {
        if (typeof data.value == 'string' && data.value != '') {
            data.value = {
                value: data.value,
            };
        }
        data = {
            ...data.value,
            ...data['#attr'],
        };
    }
    return data;
};

/**
 * Encode Bar Chars
 *
 * This function tries to replace accender chars and other extended chars into
 * an ascii chars, to do it, they define an array with the pairs of chars to
 * do a quick replace, too is converted all to lower and are removed all chars
 * that are out of range (valid range are from 0-9 and from a-z), the function
 * allow to specify an extra parameter to add extra chars that must to be
 * allowed in the output, all other chars will be converted to the padding
 * argument, as a bonus extra, all padding repetitions will be removed to
 * only allow one pading char at time
 *
 * @cad   => the input string to encode
 * @pad   => the padding char using to replace the bar chars
 * @extra => the list of chars allowed to appear in the output
 */
saltos.core.encode_bad_chars = (cad, pad = '_', extra = '') => {
    const orig = [
        'á', 'à', 'ä', 'â', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î',
        'ó', 'ò', 'ö', 'ô', 'ú', 'ù', 'ü', 'û', 'ñ', 'ç',
        'Á', 'À', 'Ä', 'Â', 'É', 'È', 'Ë', 'Ê', 'Í', 'Ì', 'Ï', 'Î',
        'Ó', 'Ò', 'Ö', 'Ô', 'Ú', 'Ù', 'Ü', 'Û', 'Ñ', 'Ç',
    ];
    const dest = [
        'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c',
        'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c',
    ];
    cad = cad.replace(new RegExp(orig.join('|'), 'g'), (match) => dest[orig.indexOf(match)]);
    cad = cad.toLowerCase();
    for (let i = 0; i < cad.length; i++) {
        const letter = cad[i];
        let replace = true;
        if (letter >= 'a' && letter <= 'z') {
            replace = false;
        }
        if (letter >= '0' && letter <= '9') {
            replace = false;
        }
        if (extra.includes(letter)) {
            replace = false;
        }
        if (replace) {
            cad = cad.substring(0, i) + pad + cad.substring(i + 1);
        }
    }
    cad = saltos.core.prepare_words(cad, pad);
    return cad;
};

/**
 * Prepare Words
 *
 * This function allow to prepare words removing repetitions in the padding char
 *
 * @cad => the input string to prepare
 * @pad => the padding char using to replace the repetitions
 *
 * Notes:
 *
 * Apart of remove repetitions of the padding char, the function will try to
 * remove padding chars in the start and in the end of the string
 */
saltos.core.prepare_words = (cad, pad = ' ') => {
    let len1;
    let len2;
    do {
        len1 = cad.length;
        cad = cad.replace(new RegExp(pad + pad, 'g'), (match) => pad);
        len2 = cad.length;
    } while (len1 - len2 > 0);
    if (cad.startsWith(pad)) {
        cad = cad.substring(pad.length);
    }
    if (cad.endsWith(pad)) {
        cad = cad.substring(0, cad.length - pad.length);
    }
    return cad;
};

/**
 * Main core code
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
document.addEventListener('DOMContentLoaded', event => {
    if ('serviceWorker' in navigator && window.location.protocol == 'https:') {
        navigator.serviceWorker.register('./proxy.js', {
            updateViaCache: 'none',
        }).then(async registration => {
            await registration.update();
        }).catch(async error => {
            if (!navigator.serviceWorker.controller) {
                return;
            }
            const check = await saltos.core.check_network();
            if (!check.http || check.https) {
                return;
            }
            // In this scope, a certificate issue was found and a reload is neeced
            saltos.core.proxy('stop');
            for (const i in saltos.core.__ajax) {
                saltos.core.__ajax[i].abort();
            }
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });

        navigator.serviceWorker.addEventListener('message', event => {
            const black = 'color:white;background:dimgrey';
            const reset = 'color:inherit;background:inherit';
            let array;
            if (typeof event.data == 'object') {
                array = ['%cPROXY%c ' + event.data[0], black, reset, ...event.data.slice(1)];
            } else {
                array = ['%cPROXY%c %s', black, reset, event.data];
            }
            console.log(...array);
        });
    }
});

/**
 * Proxy feature
 *
 * This function is intended to send messages to the proxy feature
 */
saltos.core.proxy = msg => {
    if (navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage(msg);
    }
};

/**
 * Online sync
 *
 * This function send a sync message to the proxy when navigator detects an online change
 */
window.addEventListener('online', event => {
    saltos.core.proxy('sync');
});

/**
 * Get code from file and line
 *
 * This function returns the string that contains the PATHINFO_FILENAME and the line to idenfify
 * the launcher of an error, for example
 *
 * @file => filename used to obtain the first part of the code
 * @line => line used to construct the last part of the code
 */
saltos.core.__get_code_from_file_and_line = (file = 'unknown', line = 'unknown') => {
    return file.split('/').pop().split('.').shift() + ':' + line;
};

/**
 * Timestamp helper
 *
 * This function is a helper to obtain the timestamp in seconds with the desidet offset
 *
 * @offset => offset added to the timestamp, negative to go back in the time
 */
saltos.core.timestamp = (offset = 0) => {
    return Date.now() / 1000 + offset;
};

/**
 * Human Size
 *
 * Return the human size (G, M, K or original value)
 *
 * @size  => the size that you want convert to human size
 */
saltos.core.human_size = size => {
    if (size >= 1073741824) {
        size = (Math.round(size / 1073741824 * 100) / 100) + 'G';
    } else if (size >= 1048576) {
        size = (Math.round(size / 1048576 * 100) / 100) + 'M';
    } else if (size >= 1024) {
        size = (Math.round(size / 1024 * 100) / 100) + 'K';
    }
    return size;
};

/**
 * Check network
 *
 * This function checks the network state by sending a request over https and http
 * channels, this is usefull to detect certificate issues
 *
 * Notes:
 *
 * The first version uses fetch to the img/logo_saltos.svg but for securiry reasons,
 * the browser never send the request to http from https or viceversa, the unique trick
 * that I found to do it is to open a new window for each protocol and wait for the
 * expected result
 */
saltos.core.check_network = async () => {
    const check = {};
    var protocols = ['https', 'http'];
    for (const i in protocols) {
        const protocol = protocols[i];
        const url = new URL(window.location);
        const uniqid = saltos.core.uniqid();
        url.protocol = protocol;
        url.pathname += 'api/';
        url.search = '/ping/' + uniqid;
        url.hash = '';
        const options = 'popup,width=100,height=100,left=9999,top=9999';
        const win = window.open(url.toString(), uniqid, options);
        if (!win) {
            check[protocol] = false;
            continue;
        }
        let iter = 10;
        const timer = setInterval(() => {
            if (win.closed) {
                check[protocol] = true;
                clearInterval(timer);
                return;
            }
            iter--;
            if (!iter) {
                win.close();
                check[protocol] = false;
                clearInterval(timer);
                return;
            }
        }, 100);
    }
    while (Object.keys(check).length < 2) {
        await new Promise(resolve => setTimeout(resolve, 1));
    }
    return check;
};
