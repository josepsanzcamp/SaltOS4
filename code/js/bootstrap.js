
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
    var obj = $(`<div class="${field.container}"></div>`);
    return obj;
};

saltos.__form_field["row"] = function(field) {
    saltos.check_params(field,["row"]);
    if (field.row == "") {
        field.row = "row";
    }
    var obj = $(`<div class="${field.row}"></div>`);
    return obj;
};

saltos.__form_field["col"] = function(field) {
    saltos.check_params(field,["col"]);
    if (field.col == "") {
        field.col = "col";
    }
    var obj = $(`<div class="${field.col}"></div>`);
    return obj;
};

saltos.__form_field["text"] = function(field) {
    var obj = $(`<div></div>`);
    if (field.label != "") {
        $(obj).append(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    }
    $(obj).append(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required}>
    `);
    return obj;
};

saltos.__form_field["integer"] = function(field) {
    field.type = "text";
    var obj = saltos.__form_field["text"](field);
    var element = $("input", obj).get(0);
    IMask(element, {
        mask: Number,
        signed: true,
        scale: 0,
    });
    return obj;
};

saltos.__form_field["float"] = function(field) {
    field.type = "text";
    var obj = saltos.__form_field["text"](field);
    var element = $("input", obj).get(0);
    IMask(element, {
        mask: Number,
        signed: true,
        radix: ".",
        mapToRadix: [","],
        scale: 99,
    });
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
    var obj = $(`<div></div>`);
    if (field.label != "") {
        $(obj).append(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    }
    $(obj).append(`
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    `);
    if (field.type == "textarea") {
        var element = $("textarea", obj);
        saltos.when_visible(element ,function (element) {
            element.autogrow();
        },element);
    }
    return obj;
};

saltos.__form_field["ckeditor"] = function(field) {
    var obj = saltos.__form_field["textarea"](field);
    var element = $("textarea", obj).get(0);
    saltos.when_visible(element ,function (element) {
        ClassicEditor.create(element).catch(error => {
            console.error( error );
        });
    },element);
    return obj;
};

saltos.__form_field["codemirror"] = function(field) {
    saltos.check_params(field,["mode"]);
    var obj = saltos.__form_field["textarea"](field);
    var element = $("textarea", obj).get(0);
    saltos.when_visible(element ,function (element) {
        var cm = CodeMirror.fromTextArea(element,{
            mode: field.mode,
            styleActiveLine: true,
            lineNumbers: true,
            lineWrapping: true,
        });
        $(element).next().addClass("form-control").height("auto");
        cm.on("change",cm.save);
    },element);
    return obj;
};

saltos.__form_field["iframe"] = function(field) {
    var obj = $(`<div></div>`);
    if (field.label != "") {
        $(obj).append(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    }
    $(obj).append(`
        <iframe src="${field.value}" id="${field.id}" frameborder="0" class="form-control ${field.class}"></iframe>
    `);
    return obj;
}

saltos.__form_field["select"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["multiselect"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["checkbox"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["button"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["password"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["file"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["link"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["label"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["image"] = function(field) {
    return saltos.__form_field["text"](field);
}

saltos.__form_field["excel"] = function(field) {
    return saltos.__form_field["text"](field);
}
