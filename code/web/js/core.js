
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
 */
saltos.core.onerror = async (msg, file, line, col, error) => {
    var data = {
        jserror: msg,
        details: `Error on file ${file}:${line}:${col}, userAgent is ${navigator.userAgent}`,
        backtrace: 'unknown',
    };
    if (error !== null && typeof error == 'object' && typeof error.stack == 'string') {
        window.sourceMappedStackTrace.mapStackTrace(error.stack, mappedStack => {
            data.backtrace = mappedStack.join('\n');
        });
        while (data.backtrace == 'unknown') {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
    }
    saltos.core.ajax({
        url: 'api/?adderror',
        data: JSON.stringify(data),
        method: 'post',
        content_type: 'application/json',
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};

/**
 * Error management
 *
 * Attach the error management function to the window
 */
window.onerror = saltos.core.onerror;

/**
 * Log management
 *
 * This function allow to send messages to the addlog of the server side, requires an argument:
 *
 * @msg => the message that do you want to log on the server log file
 */
saltos.core.addlog = msg => {
    var data = {
        'msg': msg,
    };
    saltos.core.ajax({
        url: 'api/?addlog',
        data: JSON.stringify(data),
        method: 'post',
        content_type: 'application/json',
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
saltos.core.check_params = (obj, params, value) => {
    if (typeof value == 'undefined') {
        value = '';
    }
    for (var key in params) {
        if (!obj.hasOwnProperty(params[key])) {
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
    if (typeof obj == 'object') {
        // Check for the id existence
        if (!obj.getAttribute('id')) {
            obj.setAttribute('id', saltos.core.uniqid());
        }
        var id = obj.getAttribute('id');
    } else if (typeof obj == 'string') {
        var id = obj;
    } else {
        throw new Error(`unknown typeof obj`);
    }
    // Launch the interval each millisecond, the idea is wait until found
    // the object and then, validate that not dissapear and wait until the
    // object is visible to execute the fn(args)
    var step = 1;
    var interval = setInterval(() => {
        var obj2 = document.getElementById(id);
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
    var keycode = 0;
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
    var type = 'div';
    var html = '';
    if (args.length == 1) {
        html = args[0];
    }
    if (args.length == 2) {
        type = args[0];
        html = args[1];
    }
    var obj = document.createElement(type);
    obj.innerHTML = html.trim();
    obj = saltos.core.optimize(obj);
    return obj;
};

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
 * @progress     => callback function to monitorize the progress of the upload/download (optional)
 * @async        => boolean to use the ajax call asynchronously or not, by default is true
 * @content_type => the content-type that you want to use in the transfer
 * @headers      => an object with the headers that you want to send
 *
 * The main idea of this function is to abstract the usage of the XMLHttpRequest in a simple
 * way as jQuery do but without using jQuery.
 */
saltos.core.ajax = args => {
    saltos.core.check_params(args, ['url', 'data', 'method', 'success', 'error',
        'progress', 'async', 'content_type', 'token', 'lang', 'headers']);
    if (args.data == '') {
        args.data = null;
    }
    if (args.method == '') {
        args.method = 'GET';
    }
    if (args.async === '') {
        args.async = true;
    }
    if (args.headers == '') {
        args.headers = {};
    }
    args.method = args.method.toUpperCase();
    if (!['GET', 'POST'].includes(args.method)) {
        throw new Error(`unknown ${args.method} method`);
    }
    var ajax = new XMLHttpRequest();
    ajax.onreadystatechange = () => {
        if (ajax.readyState == 4) {
            if (ajax.status == 200) {
                if (typeof args.success == 'function') {
                    if (!saltos.core.hasOwnProperty('about')) {
                        if (ajax.getResponseHeader('about')) {
                            saltos.core.about = ajax.getResponseHeader('about');
                        }
                    }
                    var data = ajax.response;
                    if (ajax.getResponseHeader('content-type').toUpperCase().includes('JSON')) {
                        data = JSON.parse(ajax.responseText);
                    }
                    if (ajax.getResponseHeader('content-type').toUpperCase().includes('XML')) {
                        data = ajax.responseXML;
                    }
                    args.success(data, ajax.statusText, ajax);
                }
            } else {
                if (typeof args.error == 'function') {
                    args.error(ajax, ajax.status, ajax);
                }
            }
        }
    };
    if (typeof args.progress == 'function') {
        ajax.onprogress = args.progress;
        ajax.upload.onprogress = args.progress;
    }
    ajax.open(args.method, args.url, args.async);
    if (args.content_type != '') {
        ajax.setRequestHeader('Content-Type', args.content_type);
    }
    if (args.token != '') {
        ajax.setRequestHeader('Token', args.token);
    }
    if (args.lang != '') {
        ajax.setRequestHeader('Lang', args.lang);
    }
    for (var i in args.headers) {
        ajax.setRequestHeader(i, args.headers[i]);
    }
    ajax.send(args.data);
    return ajax;
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
        for (var key in arg) {
            arg[key] = saltos.core.fix_key(arg[key]);
        }
        return arg;
    }
    var pos = arg.indexOf('#');
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
saltos.core.__require = [];

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
saltos.core.require = file => {
    // To prevent duplicates
    if (saltos.core.__require.includes(file)) {
        return;
    }
    saltos.core.__require.push(file);
    // The next call serve as prefetch
    var ajax = saltos.core.ajax({
        url: file,
        async: false,
    });
    if (ajax.status != 200) {
        throw new Error(`${ajax.status} ${ajax.statusText} loading ${file}`);
    }
    // Hash check if exists
    var pos = file.indexOf('?');
    if (pos != -1) {
        var hash = file.substr(pos + 1);
        if (md5(ajax.response) != hash) {
            throw new Error(`Hash error loading ${file}`);
        }
    }
    // Now, add the tag to load the resource (previously prefetched)
    if (file.substr(-4) == '.css' || file.includes('.css?')) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = file;
        document.head.append(link);
    }
    if (file.substr(-3) == '.js' || file.includes('.js?')) {
        var script = document.createElement('script');
        script.innerHTML = ajax.response;
        document.body.append(script);
    }
    if (file.substr(-4) == '.mjs' || file.includes('.mjs?')) {
        var script = document.createElement('script');
        script.type = 'module';
        //~ script.src = file;
        //~ script.async = false;
        script.innerHTML = ajax.response;
        document.body.append(script);
    }
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
    if (arg === null) {
        return false;
    }
    if (typeof arg == 'undefined') {
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
        var bools = {
            '1': true,
            '0': false,
            'true': true,
            'false': false,
            'on': true,
            'off': false,
            'yes': true,
            'no': false,
        };
        var bool = arg.toLowerCase();
        if (bools.hasOwnProperty(bool)) {
            return bools[bool];
        }
    }
    throw new Error(`Unknown typeof ${arg}`);
};

/**
 * toString function
 *
 * This function tries to convert to string from any other formats as boolean,
 * number, null, undefined or other type.
 */
saltos.core.toString = arg => {
    if (arg === null) {
        return 'null';
    }
    if (typeof arg == 'undefined') {
        return 'undefined';
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
    throw new Error(`Unknown typeof ${arg}`);
};

/**
 * Is attr value
 *
 * This function return true if the data argument is an object with #attr and value
 *
 * @data => the data that wants to check
 */
saltos.core.is_attr_value = data => {
    return typeof data == 'object' && data.hasOwnProperty('#attr') && data.hasOwnProperty('value');
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
    var orig = [
        'á', 'à', 'ä', 'â', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î',
        'ó', 'ò', 'ö', 'ô', 'ú', 'ù', 'ü', 'û', 'ñ', 'ç',
        'Á', 'À', 'Ä', 'Â', 'É', 'È', 'Ë', 'Ê', 'Í', 'Ì', 'Ï', 'Î',
        'Ó', 'Ò', 'Ö', 'Ô', 'Ú', 'Ù', 'Ü', 'Û', 'Ñ', 'Ç',
    ];
    var dest = [
        'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c',
        'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c',
    ];
    cad = cad.replace(new RegExp(orig.join('|'), 'g'), (match) => dest[orig.indexOf(match)]);
    cad = cad.toLowerCase();
    for (var i = 0; i < cad.length; i++) {
        var letter = cad[i];
        var replace = true;
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
 * Delay helper
 *
 * This function allow to apply a delay to an event, the main idea of this
 * code is to program a timer to execute a callback and in each call to the
 * function, the old timer is removed and a new timer is programmed, allowing
 * to call repeteadly times the function and only executing the latest call
 * after a delay
 *
 * You can see the original code here: *
 * - https://stackoverflow.com/questions/1909441/#answer-1909508
 *
 * @fn => the callback function to execute after delay
 * @ms => the delay to apply
 */
saltos.core.delay = (fn, ms) => {
    var timer = 0;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(fn.bind(this, ...args), ms || 0);
    };
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
    do {
        var len1 = cad.length;
        cad = cad.replace(new RegExp(pad + pad, 'g'), (match) => pad);
        var len2 = cad.length;
    } while (len1 - len2 > 0);
    if (cad.startsWith(pad)) {
        cad = cad.substring(pad.length);
    }
    if (cad.endsWith(pad)) {
        cad = cad.substring(0, cad.length - pad.length);
    }
    return cad;
};
