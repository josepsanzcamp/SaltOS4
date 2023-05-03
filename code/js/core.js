
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

/* MAIN OBJECT */
var saltos = saltos || {};

/* ERROR MANAGEMENT */
saltos.init_error = function () {
    window.onerror = function (msg, file, line, column, error) {
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

/* LOG MANAGEMENT */
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

/* HELPERS DEL NUEVO SALTOS */
saltos.check_params = function (obj,params,valor) {
    if (!isset(valor)) {
        valor = "";
    }
    for (var key in params) {
        if (!isset(obj[params[key]])) {
            obj[params[key]] = valor;
        }
    }
};

saltos.uniqid = function () {
    return "id" + Math.floor(Math.random() * 1000000);
};

(function ($) {
    saltos.init_error();
    var container = saltos.form_field({
        type:"container",
    });
    var row = saltos.form_field({
        type:"row",
    });
    for (var i = 1; i <= 20; i++) {
        var col = saltos.form_field({
            type:"col",
            col:"col-md-" + (((i - 1) % 12) + 1) + " mb-3",
        });
        var campo = saltos.form_field({
            type:"text",
            id:"campo" + i,
            label:"Campo " + i,
            placeholder:"Escriba aqui",
        });
        $(col).append(campo);
        $(row).append(col);
    }
    container.append(row);
    $("body").append(container);

}(jQuery));
