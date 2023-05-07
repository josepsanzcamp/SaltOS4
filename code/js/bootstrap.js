
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
 * - hidden
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
 * - pdfjs
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
    if (["container","row","col","label","button","checkbox"].includes(field.type)) {
        return saltos.__form_field[field.type](field);
    }
    var obj = $(`<div></div>`);
    if (field.label != "") {
        $(obj).append(saltos.__form_field["label"](field));
    }
    $(obj).append(saltos.__form_field[field.type](field));
    return obj;
};

saltos.__form_field = {};

saltos.__form_field["container"] = function (field) {
    saltos.check_params(field,["container"]);
    if (field.container == "") {
        field.container = "container-fluid";
    }
    var obj = $(`<div class="${field.container}"></div>`);
    return obj;
};

saltos.__form_field["row"] = function (field) {
    saltos.check_params(field,["row"]);
    if (field.row == "") {
        field.row = "row";
    }
    var obj = $(`<div class="${field.row}"></div>`);
    return obj;
};

saltos.__form_field["col"] = function (field) {
    saltos.check_params(field,["col"]);
    if (field.col == "") {
        field.col = "col";
    }
    var obj = $(`<div class="${field.col}"></div>`);
    return obj;
};

saltos.__form_field["label"] = function (field) {
    var obj = $(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    return obj;
}

saltos.__form_field["text"] = function (field) {
    var obj = $(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required}>
    `);
    return obj;
};

saltos.__form_field["hidden"] = function (field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["integer"] = function (field) {
    field.type = "text";
    var obj = saltos.__form_field["text"](field);
    var element = $(obj).get(0);
    IMask(element, {
        mask: Number,
        signed: true,
        scale: 0,
    });
    return obj;
};

saltos.__form_field["float"] = function (field) {
    field.type = "text";
    var obj = saltos.__form_field["text"](field);
    var element = $(obj).get(0);
    IMask(element, {
        mask: Number,
        signed: true,
        radix: ".",
        mapToRadix: [","],
        scale: 99,
    });
    return obj;
};

saltos.__form_field["color"] = function (field) {
    field.class="form-control-color";
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["date"] = function (field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["time"] = function (field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["datetime"] = function (field) {
    field.type = "datetime-local";
    var obj = saltos.__form_field["text"](field);
    return obj;
};

saltos.__form_field["__textarea"] = function (field) {
    saltos.check_params(field,["rows"]);
    var obj = $(`
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    `);
    return obj;
};

saltos.__form_field["textarea"] = function (field) {
    var obj = saltos.__form_field["__textarea"](field);
    var element = $(obj).get(0);
    saltos.when_visible(element ,function (element) {
        $(element).autogrow();
    },element);
    return obj;
};

saltos.__form_field["ckeditor"] = function (field) {
    var obj = saltos.__form_field["__textarea"](field);
    var element = $(obj).get(0);
    saltos.when_visible(element ,function (element) {
        ClassicEditor.create(element).catch(error => {
            console.error(error);
        });
    },element);
    return obj;
};

saltos.__form_field["codemirror"] = function (field) {
    saltos.check_params(field,["mode"]);
    var obj = saltos.__form_field["__textarea"](field);
    var element = $(obj).get(0);
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

saltos.__form_field["iframe"] = function (field) {
    var obj = $(`
        <iframe src="${field.value}" id="${field.id}" frameborder="0" class="form-control ${field.class}"></iframe>
    `);
    return obj;
}

saltos.__form_field["select"] = function (field) {
    saltos.check_params(field,["rows","multiple","size"]);
    if (field.multiple != "") {
        field.multiple = "multiple";
    }
    if (field.size != "") {
        field.size = `size="${field.size}"`;
    }
    var obj = $(`
        <select class="form-select ${field.class}" id="${field.id}" ${field.disabled} ${field.required} ${field.multiple} ${field.size}></select>
    `);
    for (var key in field.rows) {
        var val = field.rows[key];
        var selected = "";
        if (field.value.toString() == val.value.toString()) {
            selected = "selected";
        }
        $(obj).append(`<option value="${val.value}" ${selected}>${val.label}</option>`);
    }
    return obj;
}

saltos.__form_field["multiselect"] = function (field) {
    saltos.check_params(field,["rows","size"]);
    var obj = $(`<div>
        <div class="container-fluid">
            <div class="row">
                <div class="col px-0">
                </div>
                <div class="col col-auto my-auto">
                </div>
                <div class="col px-0">
                </div>
            </div>
        </div>
    </div>`);
    var rows_a = [];
    var rows_b = [];
    var values = field.value.split(",");
    for (var key in field.rows) {
        var val = field.rows[key];
        if (values.includes(val.value.toString())) {
            rows_b.push(val);
        } else {
            rows_a.push(val);
        }
    }
    field.type = "hidden";
    $(".col:eq(0)",obj).append(saltos.__form_field["hidden"](field));
    $(".col:eq(0)",obj).append(saltos.__form_field["select"]({
        class:field.class,
        id:field.id+"_a",
        disabled:field.disabled,
        multiple:true,
        size:field.size,
        rows:rows_a,
        value:"",
    }));
    $(".col:eq(1)",obj).append(saltos.__form_field["button"]({
        class:"btn-primary bi-chevron-double-right mb-3",
        id:field.id+"_c",
        disabled:field.disabled,
        label:"",
        onclick:function() {
            $("#"+field.id+"_a option:selected").each(function() {
                $("#"+field.id+"_b").append(this);
            });
            var val = [];
            $("#campo19_b option").each(function() {
                val.push($(this).val());
            });
            $("#"+field.id).val(val.join(","));
        },
    }));
    $(".col:eq(1)",obj).append("<br/>");
    $(".col:eq(1)",obj).append(saltos.__form_field["button"]({
        class:"btn-primary bi-chevron-double-left",
        id:field.id+"_d",
        disabled:field.disabled,
        label:"",
        onclick:function() {
            $("#"+field.id+"_b option:selected").each(function() {
                $("#"+field.id+"_a").append(this);
            });
            var val = [];
            $("#campo19_b option").each(function() {
                val.push($(this).val());
            });
            $("#"+field.id).val(val.join(","));
        },
    }));
    $(".col:eq(2)",obj).append(saltos.__form_field["select"]({
        class:field.class,
        id:field.id+"_b",
        disabled:field.disabled,
        multiple:true,
        size:field.size,
        rows:rows_b,
        value:"",
    }));
    // TODO
    return obj;
}

saltos.__form_field["checkbox"] = function (field) {
    field.value = parseInt(field.value);
    if (isNaN(field.value)) {
        field.value = 0;
    }
    var checked = "";
    if (field.value) {
        checked = "checked";
    }
    var obj = $(`
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="${field.id}" value="${field.value}" ${field.disabled} ${field.readonly} ${checked}>
            <label class="form-check-label" for="${field.id}">${field.label}</label>
        </div>
    `);
    $("input",obj).on("change",function() {
        this.value = this.checked ? 1 : 0;
    });
    return obj;
}

saltos.__form_field["button"] = function (field) {
    saltos.check_params(field,["onclick"]);
    var obj = $(`<button type="button" class="btn ${field.class}" id="${field.id}" ${field.disabled}>${field.label}</button>`);
    $(obj).on("click",field.onclick);
    return obj;
}

saltos.__form_field["password"] = function (field) {
    var obj = $(`
        <div class="input-group">
            <input type="password" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required} aria-label="${field.placeholder}" aria-describedby="${field.id}_b">
            <button class="btn btn-outline-secondary bi-eye-slash" type="button" id="${field.id}_b"></button>
        </div>
    `);
    $("button",obj).on("click",function() {
        var input = $(this).prev().get(0);
        if (input.type == "password") {
            input.type = "text";
            $(this).removeClass("bi-eye-slash").addClass("bi-eye");
        } else if (input.type == "text") {
            input.type = "password";
            $(this).removeClass("bi-eye").addClass("bi-eye-slash");
        }
    });
    return obj;
}

saltos.__form_field["file"] = function (field) {
    field.type = "text";
    return saltos.__form_field["text"](field);
}

saltos.__form_field["link"] = function (field) {
    field.class = "btn-link";
    field.label = field.value;
    return saltos.__form_field["button"](field);
}

saltos.__form_field["image"] = function (field) {
    field.type = "text";
    return saltos.__form_field["text"](field);
}

saltos.__form_field["excel"] = function (field) {
    field.type = "text";
    return saltos.__form_field["text"](field);
}

saltos.__form_field["pdfjs"] = function (field) {
    field.type = "text";
    return saltos.__form_field["text"](field);
}

// TODO DATALIST ???
