
/**
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

/**
 * Show error helper
 *
 * This function allow to show a modal dialog with de details of an error
 */
saltos.show_error = function (error) {
    saltos.loading(0);
    console.log(error);
    if (typeof error != "object") {
        document.body.append(saltos.html(`<pre class="m-3">${error}</pre>`));
        return;
    }
    saltos.modal({
        title:"Error " + error.code,
        close:"Close",
        body:error.text,
        footer:function () {
            var obj = saltos.html("<div></div>");
            obj.append(saltos.form_field({
                type:"button",
                value:"Accept",
                class:"btn-primary",
                onclick:function () {
                    saltos.modal("close");
                }
            }));
            return obj;
        }()
    });
};

/**
 * Send request helper
 *
 * This function allow to send requests to the server and process the response
 */
saltos.send_request = function (data) {
    saltos.ajax({
        url:"index.php?" + data,
        success:function (response) {
            if (typeof response != "object") {
                saltos.show_error(response);
                return;
            }
            if (typeof response.error == "object") {
                saltos.show_error(response.error);
                return;
            }
            saltos.process_response(response);
        },
        error:function (request) {
            saltos.show_error({
                text:request.statusText,
                code:request.status,
            });
        },
        headers:{
            "token":saltos.token,
        }
    });
};

/**
 * Process response helper
 *
 * This function process the responses received by the send request
 */
saltos.process_response = function (response) {
    for (var key in response) {
        var val = response[key];
        var key = saltos.fix_key(key);
        if (typeof saltos.form[key] != "function") {
            console.log("type " + key + " not found");
            document.body.append(saltos.html("type " + key + " not found"));
            continue;
        }
        saltos.form[key](val);
    }
};

/**
 * Form constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.form = {};

/**
 * Form source helper
 *
 * This function redirects the request to the send request function, allow to define the source command
 */
saltos.form.source = function (data) {
    saltos.send_request(data)
}

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document
 */
saltos.form.data = function (data) {
    for (var key in data) {
        var val = data[key];
        var obj = document.getElementById(key);
        if (obj !== null) {
            if (obj.type == "checkbox") {
                obj.checked = val ? true : false;
            } else {
                obj.value = val;
            }
        }
    }
};

/**
 * Form layout helper
 *
 * This function process the layout command, its able to process nodes as container, row, col and div
 * and all form_field defined in the bootstrap file, too have 2 modes of work:
 *
 * 1) normal mode => requires that the user specify all layout, container, row, col and fields.
 *
 * 2) auto mode => only requires set auto="true" to the layout node, and with this, all childrens
 * of the node are created inside a container, a row, and each field inside a col.
 */
saltos.form.layout = function (layout) {
    saltos.loading(0);
    // Check for attr auto
    if (layout.hasOwnProperty("value") && layout.hasOwnProperty("#attr")) {
        var attr = layout["#attr"];
        var value = layout.value;
        saltos.check_params(attr, ["auto", "cols_per_row", "container_class", "row_class", "col_class"]);
        if (attr.cols_per_row == "") {
            attr.cols_per_row = Infinity;
        }
        if (attr.auto == "true") {
            // This trick convert all entries of the object in an array with the keys and values
            var temp = [];
            for (var key in value) {
                temp.push([key, value[key]]);
            }
            // This is the new layout object created with one container, rows, cols and all original
            // fields, too can specify what class use in each object created
            var layout = {
                container:{
                    "value":{},
                    "#attr":{
                        class:attr.container_class
                    }
                }
            };
            // this counters and flag are used to add rows using the cols_per_row parameter
            var numrow = 0;
            var numcol = 0;
            var addrow = 1;
            while (temp.length) {
                var item = temp.shift(temp);
                if (addrow) {
                    numrow++;
                    layout.container.value["row#" + numrow] = {
                        "value":{},
                        "#attr":{
                            class:attr.row_class
                        }
                    };
                }
                numcol++;
                var col_class = attr.col_class;
                if (item[1].hasOwnProperty("#attr") && item[1]["#attr"].hasOwnProperty("col_class")) {
                    col_class = item[1]["#attr"].col_class;
                }
                layout.container.value["row#" + numrow].value["col#" + numcol] = {
                    "value":{},
                    "#attr":{
                        class:col_class
                    }
                };
                layout.container.value["row#" + numrow].value["col#" + numcol].value[item[0]] = item[1];
                if (numcol >= attr.cols_per_row) {
                    numcol = 0;
                    addrow = 1;
                } else {
                    addrow = 0;
                }
            }
        } else {
            layout = value;
        }
    }
    // Continue with original idea of use a entire specified layout
    var arr = [];
    for (var key in layout) {
        var val = layout[key];
        key = saltos.fix_key(key);
        if (typeof val == "object" && val.hasOwnProperty("value") && val.hasOwnProperty("#attr")) {
            var attr = val["#attr"];
            var value = val.value;
        } else {
            var attr = {};
            var value = val;
        }
        if (!attr.hasOwnProperty("type")) {
            attr.type = key;
        }
        if (!attr.hasOwnProperty("value")) {
            attr.value = value;
        }
        if (["container", "col", "row", "div"].includes(key)) {
            var obj = saltos.form_field(attr);
            var temp = saltos.form.layout(value, 1);
            for (var i in temp) {
                obj.append(temp[i]);
            }
            arr.push(obj);
        } else {
            var obj = saltos.form_field(attr);
            arr.push(obj);
        }
    }
    if (arguments.length == 2) {
        return arr;
    }
    var div = saltos.html("<div></div>");
    for (var i in arr) {
        div.append(arr[i]);
    }
    div = saltos.optimize(div);
    document.body.append(div);
};

/**
 * Form style helper
 *
 * This function allow to specify styles, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 */
saltos.form.style = function (data) {
    for (var key in data) {
        var val = data[key];
        var key = saltos.fix_key(key);
        if (key == "inline") {
            document.body.append(saltos.html(`<style>${val}</style>`));
        }
        if (key == "file") {
            document.body.append(saltos.html(`<link href="${val}" rel="stylesheet">`));
        }
    }
};

/**
 * Form javascript helper
 *
 * This function allow to specify scripts, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 */
saltos.form.javascript = function (data) {
    for (var key in data) {
        var val = data[key];
        var key = saltos.fix_key(key);
        if (key == "inline") {
            var script = document.createElement("script");
            script.innerHTML = val;
            document.body.append(script);
        }
        if (key == "file") {
            var script = document.createElement("script");
            script.src = val;
            document.body.append(script);
        }
    }
};

/**
 * Hash change management
 *
 * This function allow to SaltOS to update the contents when hash change
 */
window.onhashchange = function (event) {
    var hash = document.location.hash;
    if (hash.substr(0, 1) == "#") {
        hash = hash.substr(1);
    }
    if (hash == "") {
        hash = "app/menu";
        history.replaceState(null, null, ".#" + hash)
    }
    // Reset the body interface
    saltos.modal("close");
    saltos.offcanvas("close");
    saltos.loading(1);
    // Do the request
    saltos.send_request(hash);
};

/**
 * Loading helper
 *
 * This function adds and removes the spinner to emulate the loading effect screen
 *
 * @on_off => if you want to show or hide the loading spinner, the function returns
 * true when can do the action, false otherwise
 *
 * The main
 */
saltos.loading = function (on_off) {
    var obj = document.getElementById("loading");
    if (on_off && !obj) {
        document.body.append(saltos.html(`
            <div id="loading" class="d-flex justify-content-center align-items-center vh-100">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `));
        window.scrollTo(0, window.scrollMaxY);
        return true;
    }
    if (!on_off && obj) {
        window.scrollTo(0, 0);
        var timer = setInterval(function () {
            if (window.scrollY == 0) {
                obj.remove();
                clearInterval(timer);
            }
        }, 1);
        return true;
    }
    return false;
};

/**
 * Main code
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
(function () {
    saltos.token = localStorage.getItem("token");
    if (saltos.token === null) {
        saltos.token = "e9f3ebd0-8e73-e4c4-0ebd-7056cf0e70fe";
        //~ saltos.send_request("app/login");
    }
    window.dispatchEvent(new HashChangeEvent("hashchange"));
}());
