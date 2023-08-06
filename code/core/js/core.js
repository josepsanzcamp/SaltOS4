
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

"use strict";

/**
 * Main object
 *
 * This object contains all SaltOS code
 */
var saltos = saltos || {};

/**
 * Error management
 *
 * This function allow to SaltOS to log in server the javascript errors produced in the client's browser
 */
window.onerror = (event, source, lineno, colno, error) => {
    if (typeof error == "undefined" || typeof error.stack == "undefined") {
        var error = {
            stack: "unknown"
        };
    }
    var data = {
        "action": "adderror",
        "jserror": event,
        "details": "Error on file " + source + ":" + lineno + ":" + colno + ", userAgent is " + navigator.userAgent,
        "backtrace": error.stack
    };
    saltos.ajax({
        url: "index.php",
        data: JSON.stringify(data),
        method: "post",
        content_type: "application/json",
        headers: {
            "token": saltos.token,
        }
    });
};

/**
 * Log management
 *
 * This function allow to send messages to the addlog of the server side, requires an argument:
 *
 * @msg => the message that do you want to log on the server log file
 */
saltos.addlog = msg => {
    var data = {
        "action": "addlog",
        "msg": msg,
    };
    saltos.ajax({
        url: "index.php",
        data: JSON.stringify(data),
        method: "post",
        content_type: "application/json",
        headers: {
            "token": saltos.token,
        }
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
saltos.check_params = (obj, params, value) => {
    if (typeof value == "undefined") {
        value = "";
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
 * This function generates an unique id formed by the word "id" and a number that can take
 * values between 0 and 999999, useful when some widget requires an id and the user don't
 * provide it to the widget constructor
 */
saltos.uniqid = () => {
    return "id" + Math.floor(Math.random() * 1000000);
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
 */
saltos.when_visible = (obj, fn, args) => {
    // Check for the id existence
    if (!obj.getAttribute("id")) {
        obj.setAttribute("id", saltos.uniqid());
    }
    var id = obj.getAttribute("id");
    // Launch the interval each millisecond, the idea is wait until found
    // the object and then, validate that not dissapear and wait until the
    // object is visible to execute the fn(args)
    var step = 1;
    var interval = setInterval(() => {
        var obj2 = document.getElementById(id);
        if (step == 1) {
            // Maintain the state machine in the first state until found
            // the object in the document
            if (obj2 !== null) {
                step++;
            }
        }
        if (step == 2) {
            // Here, the object is found in the document, we can continue
            if (obj2 === null) {
                // Here, the object has disappeared, we can stop the timer
                clearInterval(interval);
                console.log("#" + id + " not found");
            } else if (obj2.offsetParent !== null) {
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
saltos.get_keycode = event => {
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
saltos.html = (...args) => {
    var type = "div";
    var html = "";
    if (args.length == 1) {
        html = args[0];
    }
    if (args.length == 2) {
        type = args[0];
        html = args[1];
    }
    var obj = document.createElement(type);
    obj.innerHTML = html.trim();
    obj = saltos.optimize(obj);
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
saltos.ajax = args => {
    saltos.check_params(args, ["url", "data", "method", "success", "error", "progress", "async", "content_type", "headers"]);
    if (args.data == "") {
        args.data = null;
    }
    if (args.method == "") {
        args.method = "GET";
    }
    if (args.async === "") {
        args.async = true;
    }
    if (args.headers == "") {
        args.headers = {};
    }
    args.method = args.method.toUpperCase();
    if (!["GET", "POST"].includes(args.method)) {
        console.log("unknown " + args.method + " method");
        return null;
    }
    var ajax = new XMLHttpRequest();
    ajax.onreadystatechange = () => {
        if (ajax.readyState == 4) {
            if (ajax.status == 200) {
                if (typeof args.success == "function") {
                    var data = ajax.response;
                    if (ajax.getResponseHeader("content-type").toUpperCase().includes("JSON")) {
                        data = JSON.parse(ajax.responseText);
                    }
                    if (ajax.getResponseHeader("content-type").toUpperCase().includes("XML")) {
                        data = ajax.responseXML;
                    }
                    args.success(data, ajax.statusText, ajax);
                }
            } else {
                if (typeof args.error == "function") {
                    args.error(ajax, ajax.status, ajax);
                }
            }
        }
    }
    if (typeof args.progress == "function") {
        ajax.onprogress = args.progress;
        ajax.upload.onprogress = args.progress;
    }
    ajax.open(args.method, args.url, args.async);
    if (args.content_type != "") {
        ajax.setRequestHeader("Content-Type", args.content_type);
    }
    for (var i in args.headers) {
        ajax.setRequestHeader(i, args.headers[i]);
    }
    ajax.send(args.data);
    return ajax;
};

/**
 * Key cleaner
 *
 * This function is intended to fix the keys of the objects, this is caused because you can not
 * have 2 repeated keys in an object, to have more entries with the same name, SaltOS add a suffix
 * by adding #num, with this trick, SaltOS is able to process XML files with the same node name
 * and convert it to an array structure, and when convert this to json, the same problem appear and
 * for this reason, exists this function here
 *
 * @arg => can be an string or an array of strings and returns the same structure with the keys fixed
 */
saltos.fix_key = arg => {
    if (typeof arg == "object") {
        for (var key in arg) {
            arg[key] = saltos.fix_key(arg[key]);
        }
        return arg;
    }
    var pos = arg.indexOf("#");
    if (pos != -1) {
        arg = arg.substr(0, pos);
    }
    return arg;
};

/**
 * Open window
 *
 * This function is intended to open new tabs in the window, at the moment only is a wrapper to
 * the window.open but in a future, can add more features
 *
 * @url => the url of the page to load
 */
saltos.open = url => {
    window.open(url);
};

/**
 * Copy object
 *
 * This function is intended to do copies of objects using as intermediate a json file
 *
 * @arg => the object that you want to copy
 */
saltos.copy_object = arg => {
    return JSON.parse(JSON.stringify(arg));
};

/**
 * Optimizer object
 *
 * This function checks an object to see if only contains one children and
 * in this case, returns directly the children instead of the original object,
 * otherwise nothing to do and returns the original object
 *
 * @obj => the object to check and optimize
 */
saltos.optimize = obj => {
    if (obj.children.length == 1) {
        return obj.firstElementChild;
    }
    return obj;
}
