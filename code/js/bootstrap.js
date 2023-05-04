
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
 * - container
 * - row
 * - col
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
 * - iframe
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

saltos.form_field = function (field) {
    saltos.check_params(field,["type","id","label","class","placeholder","value","disabled","readonly","required"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.readonly) {
        field.readonly = "readonly";
    }
    if (field.required) {
        field.required = "required";
    }
    return saltos.__form_field[field.type](field);
};

saltos.__form_field = {};

saltos.__form_field["container"] = function(field) {
    saltos.check_params(field,["container"]);
    if (field.container == "") {
        field.container = "container-fluid";
    }
    var obj = $(`
        <div class="${field.container}">
        </div>
    `);
    return obj;
};

saltos.__form_field["row"] = function(field) {
    saltos.check_params(field,["row"]);
    if (field.row == "") {
        field.row = "row";
    }
    var obj = $(`
        <div class="${field.row}">
        </div>
    `);
    return obj;
};

saltos.__form_field["col"] = function(field) {
    saltos.check_params(field,["col"]);
    if (field.col == "") {
        field.col = "col";
    }
    var obj = $(`
        <div class="${field.col}">
        </div>
    `);
    return obj;
};

saltos.__form_field["text"] = function(field) {
    var obj = $(`<div>
        <label for="${field.id}" class="form-label">${field.label}</label>
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required}>
    </div>`);
    return obj;
};

saltos.__form_field["integer"] = function(field) {
    field.type = "text";
    var obj = saltos.__form_field["text"](field);
    var element = $("input", obj).get(0);
    var maskOptions = {
        mask:Number,
        signed:true,
        scale:0,
    };
    IMask(element, maskOptions);
    return obj;
};

saltos.__form_field["float"] = function(field) {
    field.type = "text";
    var obj = saltos.__form_field["text"](field);
    var element = $("input", obj).get(0);
    var maskOptions = {
        mask:Number,
        signed:true,
        radix:".",
        mapToRadix: [","],
        scale:99,
    };
    IMask(element, maskOptions);
    return obj;
};

saltos.__form_field["color"] = function(field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["date"] = function(field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["time"] = function(field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["datetime"] = function(field) {
    field.type = "datetime-local";
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["textarea"] = function(field) {
    saltos.check_params(field,["rows"]);
    var obj = $(`<div>
        <label for="${field.id}" class="form-label">${field.label}</label>
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    </div>`);
    var element = $("textarea", obj);
    saltos.when_visible(element ,function (args) {
        args.autogrow();
    },element);
    return obj;
};

saltos.__form_field["ckeditor"] = function(field) {
    var obj = $(`<div>
        <label for="${field.id}" class="form-label">${field.label}</label>
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    </div>`);
    var element = $("textarea", obj).get(0);
    saltos.when_visible(element ,function (args) {
        ClassicEditor.create(args).catch(error => {
            console.error( error );
        });
    },element);
    return obj;
};

saltos.__form_field["codemirror"] = function(field) {
    var obj = $(`<div>
        <label for="${field.id}" class="form-label">${field.label}</label>
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    </div>`);
    var element = $("textarea", obj).get(0);
    saltos.when_visible(element ,function (args) {
        var width = $(element).width();
        var height = $(element).height();
        var classes = $(element).attr("class");
        var cm = CodeMirror.fromTextArea(element,{
            lineNumbers:true
        });
        $(element).data("cm",cm);
        var fnresize = function (cm) {
            var height2 = Math.max(height,cm.doc.size * 24);
            if (cm.display.sizerWidth > cm.display.lastWrapWidth) {
                height2 += 24;
            }
            cm.setSize(width + 20,height2 + 20);
        }
        fnresize(cm);
        cm.on("viewportChange",fnresize);
        $(element).next().addClass(classes).css("margin","1px");
        cm.on("change",cm.save);
    },element);
    return obj;
};

/*
 * TODOS
 * - iframe
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
