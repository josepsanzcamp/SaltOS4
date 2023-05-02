
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

/*
 * LIST OF SUPPORTED TYPES:
 * - text
 * - integer
 * - float
 * - color
 * - date
 * - time
 * - datetime
 * - textarea
 * - ckeditor
 * - codemirror
 * - select
 * - multiselect
 * - checkbox
 * - button
 * - password
 * - file
 * - link
 * - label
 * - image
 * - excel
 */

saltos.__form_field = {};

saltos.__form_field["text"] = function(field) {
    var obj = $(`
        <label for="${field.id}" class="form-label">${field.label}</label>
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}">
    `);
    return obj;
}

saltos.form_field = function (field) {
    saltos.check_params(field,["type"]);
    if (field.type == "textarea" && isset(field.ckeditor) && field.ckeditor == "true") {
        field.type = "ckeditor";
    }
    if (field.type == "textarea" && isset(field.codemirror) && field.codemirror == "true") {
        field.type = "codemirror";
    }
    return saltos.__form_field[field.type](field);
};
