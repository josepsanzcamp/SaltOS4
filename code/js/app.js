
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

saltos.form_layout = function (layout) {
    var arr = [];
    for (var key in layout) {
        var val = layout[key];
        key = saltos.fix_key(key);
        if (["container","col","row","div"].includes(key)) {
            if (typeof val == "object" && val.hasOwnProperty("value") && val.hasOwnProperty("#attr")) {
                var attr = val["#attr"];
                attr.type = key;
                var obj = saltos.form_field(attr);
                var temp = saltos.form_layout(val.value,1);
                for (var i in temp) {
                    obj.append(temp[i]);
                }
                arr.push(obj);
            } else {
                var attr = {};
                attr.type = key;
                var obj = saltos.form_field(attr);
                var temp = saltos.form_layout(val,1);
                for (var i in temp) {
                    obj.append(temp[i]);
                }
                arr.push(obj);
            }
        } else {
            if (typeof val == "object" && val.hasOwnProperty("value") && val.hasOwnProperty("#attr")) {
                var attr = val["#attr"];
                attr.type = key;
                attr.value = val.value;
                if (attr.hasOwnProperty("onclick") && typeof attr.onclick == "string") {
                    attr.onclick = new Function(attr.onclick);
                }
                var obj = saltos.form_field(attr);
                arr.push(obj);
            } else {
                var attr = {};
                attr.type = key;
                attr.value = val;
                var obj = saltos.form_field(attr);
                arr.push(obj);
            }
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

    saltos.token = localStorage.getItem("token");
    if (saltos.token === null) {
        saltos.app = "login";
    }

    saltos.ajax({
        url:"index.php?getapp/" + saltos.app + "/default",
        success:function (response) {
            if (typeof response.error == "object") {
                saltos.show_error(response.error);
                return;
            }
            if (typeof response.layout == "undefined") {
                saltos.show_error({
                    text:"Internal error",
                    code:"app/124",
                });
                return;
            }
            document.querySelector("body").append(saltos.form_layout(response.layout));
        },
        headers:{
            "token":saltos.token,
        }
    })

}());
