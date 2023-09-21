
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
 * Show error helper
 *
 * This function allow to show a modal dialog with de details of an error
 */
saltos.show_error = error => {
    console.log(error);
    if (typeof error != "object") {
        document.body.append(saltos.html(`<pre class="m-3">${error}</pre>`));
        return;
    }
    saltos.modal({
        title: "Error " + error.code,
        close: "Close",
        body: error.text,
        footer: (() => {
            var obj = saltos.html("<div></div>");
            obj.append(saltos.form_field({
                type: "button",
                value: "Accept",
                class: "btn-primary",
                onclick: () => {
                    saltos.modal("close");
                }
            }));
            return obj;
        })()
    });
};

/**
 * Send request helper
 *
 * This function allow to send requests to the server and process the response
 */
saltos.send_request = data => {
    saltos.ajax({
        url: "index.php?" + data,
        success: response => {
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
        error: request => {
            saltos.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        headers: {
            "token": saltos.token,
        }
    });
};

/**
 * Process response helper
 *
 * This function process the responses received by the send request
 */
saltos.process_response = response => {
    for (var key in response) {
        var val = response[key];
        var key = saltos.fix_key(key);
        if (typeof saltos.form_app[key] != "function") {
            console.log("type " + key + " not found");
            document.body.append(saltos.html("type " + key + " not found"));
            continue;
        }
        saltos.form_app[key](val);
    }
};

/**
 * Form constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.form_app = {};

/**
 * Data helper object
 *
 * This object allow to the app to store the data of the fields map
 */
saltos.__form_app = {
    fields: [],
    data: {},
};

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document
 */
saltos.form_app.data = data => {
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
saltos.form_app.layout = (layout, extra) => {
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
                container: {
                    "value": {},
                    "#attr": {
                        class: attr.container_class
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
                        "value": {},
                        "#attr": {
                            class: attr.row_class
                        }
                    };
                }
                numcol++;
                var col_class = attr.col_class;
                if (item[1].hasOwnProperty("#attr") && item[1]["#attr"].hasOwnProperty("col_class")) {
                    col_class = item[1]["#attr"].col_class;
                }
                layout.container.value["row#" + numrow].value["col#" + numcol] = {
                    "value": {},
                    "#attr": {
                        class: col_class
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
        if (["container", "col", "row", "div"].includes(key)) {
            var obj = saltos.form_field(attr);
            var temp = saltos.form_app.layout(value, 1);
            for (var i in temp) {
                obj.append(temp[i]);
            }
            arr.push(obj);
        } else {
            if (typeof value == "object") {
                for (var key2 in value) {
                    if (!attr.hasOwnProperty(key2)) {
                        attr[key2] = value[key2];
                    }
                }
            } else if (!attr.hasOwnProperty("value")) {
                attr.value = value;
            }
            saltos.check_params(attr, ["id", "source"]);
            if (attr.id == "") {
                attr.id = saltos.uniqid();
            }
            saltos.__form_app.fields.push(attr);
            if (attr.source != "") {
                var obj = saltos.form_field({
                    type: "placeholder",
                    id: attr.id,
                });
                saltos.__source_helper(attr);
            } else {
                var obj = saltos.form_field(attr);
            }
            arr.push(obj);
        }
    }
    if (extra) {
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
saltos.form_app.style = data => {
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
saltos.form_app.javascript = data => {
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
window.onhashchange = event => {
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
 */
saltos.loading = on_off => {
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
        var timer = setInterval(() => {
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
 * Clear Screen
 *
 * This function remove all contents of the body
 */
saltos.clear_screen = () => {
    document.body.innerHTML = "";
};

/**
 * Source helper
 *
 * This function is intended to provide an asynchronous sources for a field, using the source attribute,
 * you can program an asynchronous ajax request to retrieve the data used to create the field.
 * *
 * This function is used in the fields of type table, alert, card and chartjs, the call of this function
 * is private and is intended to be used as a helper from the builders of the previous types opening
 * another way to pass arguments.
 *
 * @id     => the id used to set the reference for to the object
 * @type   => the type used to set the type for to the object
 * @source => data source used to load asynchronously the contents of the table (header, data,
 *            footer and divider)
 *
 * Notes:
 *
 * In some cases, the response for a source request can be an object that represents an xml node with
 * attributes and values, as for the example, the widget/2 used in the app.php, that returns an array
 * with all contents of the widget in the value entry and another entry used for the #attr that only
 * contains the id used to select the widget in the app.php, is this case, the unique data that we want
 * to use here is the contents of the value, and for this reason, the response is filtered to use only
 * the value key in the case of existence of the #attr and value keys
 */
saltos.__source_helper = field => {
    saltos.check_params(field, ["id", "source"]);
    // Check for asynchronous load using the source param
    if (field.source != "") {
        saltos.ajax({
            url: "index.php?" + field.source,
            success: response => {
                if (typeof response != "object") {
                    saltos.show_error(response);
                    return;
                }
                if (typeof response.error == "object") {
                    saltos.show_error(response.error);
                    return;
                }
                field.source = "";
                if (response.hasOwnProperty("value") && response.hasOwnProperty("#attr")) {
                    response = response.value;
                }
                for (var key in response) {
                    field[key] = response[key];
                }
                document.getElementById(field.id).replaceWith(saltos.form_field(field));
            },
            error: request => {
                saltos.show_error({
                    text: request.statusText,
                    code: request.status,
                });
            },
            headers: {
                "token": saltos.token,
            }
        });
    }
};

/**
 * Main code
 *
 * This is the code that must to be executed to initialize all requirements of this module
 */
(() => {
    // Dark theme part
    var window_match_media = window.matchMedia("(prefers-color-scheme: dark)");
    var set_data_bs_theme = e => {
        document.querySelector("html").setAttribute("data-bs-theme", e.matches ? "dark" : "");
    };
    set_data_bs_theme(window_match_media);
    window_match_media.addEventListener("change", set_data_bs_theme);
    // Token part
    saltos.token = localStorage.getItem("token");
    if (saltos.token === null) {
        saltos.token = "e9f3ebd0-8e73-e4c4-0ebd-7056cf0e70fe";
        //~ saltos.send_request("app/login");
    }
    // Init part
    window.dispatchEvent(new HashChangeEvent("hashchange"));
})();
