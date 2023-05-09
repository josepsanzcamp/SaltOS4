
/*
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

"use strict";

/*
 * Main object
 *
 * This object contains all SaltOS code
 */
var saltos = saltos || {};

/*
 * Error management
 *
 * This function allow to SaltOS to log in server the javascript errors produced in the client's browser
 */
saltos.init_error = function () {
    window.onerror = function (msg, file, line, column, error) {
        if (!isset(error) || !isset(error.stack)) {
            var error = { stack:"unknown"};
        }
        var data = {
            "action":"adderror",
            "jserror":msg,
            "details":"Error on file " + file + ":" + line + ":" + column + ", userAgent is " + navigator.userAgent,
            "backtrace":error.stack
        };
        $.ajax({
            url:"index.php",
            data:JSON.stringify(data),
            type:"post"
        });
    };
};

/*
 * Log management
 *
 * This function allow to send messages to the addlog of the server side, requires an argument:
 *
 * @msg => the message that do you want to log on the server log file
 */
saltos.addlog = function (msg) {
    var data = {
        "action":"addlog",
        "msg":msg,
    };
    $.ajax({
        url:"index.php",
        data:JSON.stringify(data),
        type:"post"
    });
};

/*
 * Helper of the new saltos
 *
 * This function allow to prepare parameters to be used by other functions, the main idea
 * is that the other functions can access to properties of an object without getting errors
 * caused by the nonexistence, to do this, checks for the existence of all params in the obj
 * and if some param is not found, then define it using the default value passed:
 *
 * @obj => the object that contains the arguments, for example
 * @params => an array with the arguments that must to exists
 * @value => the default value used if an argument doesn't exists
 */
saltos.check_params = function (obj,params,value) {
    if (!isset(value)) {
        value = "";
    }
    for (var key in params) {
        if (!isset(obj[params[key]])) {
            obj[params[key]] = value;
        }
    }
};

/*
 * Helper of the new saltos
 *
 * This function generates an unique id formed by the word "id" and a number that can take
 * values between 0 and 999999, useful when some widget requires an id and the user don't
 * provide it to the widget constructor
 */
saltos.uniqid = function () {
    return "id" + Math.floor(Math.random() * 1000000);
};

/*
 * HELPER OF THE NEW SALTOS
 *
 * This function allow to execute some code when the object is visible, useful for third part
 * widgets as ckeditor or codemirror that requires a rendered environemt to initialize their
 * code and paint the widget correctly
 *
 * @obj => the object that do you want to monitorize the visibility
 * @fn => the callback that you want to execute
 * @args => the arguments passed to the callback when execute it
 */
saltos.when_visible = function (obj,fn,args) {
    if (!$(obj).is("[id]")) {
        $(obj).attr("id","fix" + saltos.uniqid());
    }
    var id = "#" + $(obj).attr("id");
    var interval = setInterval(function () {
        var obj2 = $(id);
        if (!$(obj2).length) {
            clearInterval(interval);
        } else if ($(obj2).is(":visible")) {
            clearInterval(interval);
            fn(args);
        }
    },100);
};

/*
 * MAIN CODE
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
(function ($) {
    saltos.init_error();
}(jQuery));
