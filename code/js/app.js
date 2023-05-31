
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

saltos.show_error = function (error) {
    saltos.modal({
        title:"Error",
        close:"Cerrar",
        body:error.text + "<br/>Code " + error.code,
        footer:function () {
            var obj = saltos.html("<div></div>");
            obj.append(saltos.form_field({
                type:"button",
                value:"Aceptar",
                class:"btn-primary",
                onclick:function () {
                    saltos.modal("close");
                }
            }));
            return obj;
        }()
    });
};

saltos.send_request = function (arg) {
    saltos.ajax({
        url:"index.php?" + arg,
        success:function (response) {
            if (typeof response.error == "object") {
                saltos.show_error(response.error);
            } else {
                saltos.process_response(response);
            }
        },
        //~ headers:{
            //~ "token":saltos.token,
        //~ }
    });
};

saltos.process_response = function (response) {
    for (var key in response) {
        var val = response[key];
        var key = saltos.fix_key(key);
        if (key == "layout") {
            document.querySelector("body").append(saltos.form_layout(val));
        }
        if (key == "data") {
            for (var key2 in val) {
                var val2 = val[key2];
                var obj = document.getElementById(key2);
                if (obj !== null) {
                    if(obj.type == "checkbox") {
                        obj.checked = val2 ? true : false;
                    } else {
                        obj.value = val2;
                    }
                }
            }
        }
    }
};

saltos.form_layout = function (layout) {
    // Check for attr auto
    if (layout.hasOwnProperty("value") && layout.hasOwnProperty("#attr")) {
        var attr = layout["#attr"];
        var value = layout.value;
        saltos.check_params(attr,["auto","cols_per_row","container_class","row_class","col_class"]);
        if (attr.cols_per_row == "") {
            attr.cols_per_row = Infinity;
        }
        if (attr.auto == "true") {
            // This trick convert all entries of the object in an array with the keys and values
            var temp = [];
            for (var key in value) {
                temp.push([key,value[key]]);
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
                layout.container.value["row#" + numrow].value["col#" + numcol] = {
                    "value":{},
                    "#attr":{
                        class:attr.col_class
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
        attr.type = key;
        attr.value = value;
        if (["container","col","row","div"].includes(key)) {
            var obj = saltos.form_field(attr);
            var temp = saltos.form_layout(value,1);
            for (var i in temp) {
                obj.append(temp[i]);
            }
            arr.push(obj);
        } else {
            if (attr.hasOwnProperty("onclick") && typeof attr.onclick == "string") {
                attr.onclick = new Function(attr.onclick);
            }
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
    if (div.childNodes.length == 1) {
        return div.firstChild;
    }
    return div;
};

// Main code
(function () {

    saltos.hash = document.location.hash;
    if (saltos.hash.substr(0,1) == "#") {
        saltos.hash = saltos.hash.substr(1);
    }
    if (saltos.hash == "") {
        saltos.hash = "app/menu";
    }
    saltos.send_request(saltos.hash);

    //~ saltos.token = localStorage.getItem("token");
    //~ if (saltos.token === null) {
        //~ saltos.send_request("app/login");
    //~ }

}());
