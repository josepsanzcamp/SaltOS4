
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

saltos.form = function(layout) {
    console.log(arguments.length);
    var div = saltos.html("<div></div>");
    for (var key in layout) {
        var val = layout[key];
        if (["container","col","row"].includes(key)) {
            if (typeof val == "object" && val.hasOwnProperty("value") && val.hasOwnProperty("#attr")) {
                var attr = val["#attr"];
                attr.type = key;
                var obj = saltos.form_field(attr);
                obj.append(saltos.form(val.value,1));
                div.append(obj);
            } else {
                var attr = {};
                attr.type = key;
                var obj = saltos.form_field(attr);
                obj.append(saltos.form(val,1));
                div.append(obj);
            }
        } else {
            if (typeof val == "object" && val.hasOwnProperty("value") && val.hasOwnProperty("#attr")) {
                var attr = val["#attr"];
                attr.type = key;
                attr.value = val.value;
                var obj = saltos.form_field(attr);
                div.append(obj);
            } else {
                var attr = {};
                attr.type = key;
                attr.value = val;
                var obj = saltos.form_field(attr);
                div.append(obj);
            }
        }
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
            saltos.action = response.actions.action;
            saltos.ajax({
                url:"index.php?getapp/" + saltos.app + "/" + saltos.action,
                success:function (response) {
                    document.querySelector("body").append(saltos.form(response.layout));
                },
                headers:{
                    "token":saltos.token,
                }
            });
        },
        headers:{
            "token":saltos.token,
        }
    })

}());
