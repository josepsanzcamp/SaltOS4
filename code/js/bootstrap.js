
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

saltos.__form_field["text"] = function (field) {
    var obj = $(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required}>
    `);
    return obj;
};

saltos.__form_field["hidden"] = function (field) {
    field.type = "hidden";
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
        onclick:function () {
            $("#"+field.id+"_a option:selected").each(function () {
                $("#"+field.id+"_b").append(this);
            });
            var val = [];
            $("#campo19_b option").each(function () {
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
        onclick:function () {
            $("#"+field.id+"_b option:selected").each(function () {
                $("#"+field.id+"_a").append(this);
            });
            var val = [];
            $("#campo19_b option").each(function () {
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
    $("input",obj).on("change",function () {
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
    $("button",obj).on("click",function () {
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
    saltos.check_params(field,["multiple"]);
    if (field.multiple != "") {
        field.multiple = "multiple";
    }
    var obj = $(`<div>
        <input type="file" class="form-control ${field.class}" id="${field.id}" ${field.disabled} ${field.required} ${field.multiple}>
        <table class="table table-striped table-hover d-none">
            <tbody>
            </tbody>
        </table>
    </div>`);
    // PROGRAM THE AUTOMATIC UPLOAD
    $("input",obj).on("change",async function () {
        var files = this.files;
        var table = $(this).next();
        for (var i = 0; i < files.length; i++) {
            // PREPARE THE DATA TO SEND
            var data = {
                action:"addfiles",
                files:[],
            };
            data.files[0] = {
                id:saltos.uniqid(),
                name:files[i].name,
                size:files[i].size,
                type:files[i].type,
                data:"",
                error:"",
                file:"",
                hash:"",
            };
            // SHOW THE TABLE
            $(table).removeClass("d-none");
            // ADD THE ROW FOR THE NEW FILE
            var row = $(`
                <tr id="${data.files[0].id}">
                    <td>${data.files[0].name}</td>
                    <td class="w-25 align-middle">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-label="Example with label" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </td>
                    <td class="p-0" style="width: 1%"><button class="btn bi-trash" type="button"></button></td>
                </tr>
            `);
            // STORE THE DATA IN THE ROW
            $(row).data("data",data.files[0]);
            // PROGRAM DE REMOVE BUTTON
            $("button",row).on("click",function() {
                var row = $(this).parent().parent();
                var table = row.parent().parent();
                var data = {
                    action:"delfiles",
                    files:[],
                };
                data.files[0] = row.data("data");
                $.ajax({
                    url:"index.php",
                    data:JSON.stringify(data),
                    type:"post",
                    success:function (data,textStatus,XMLHttpRequest) {
                        $(row).data("data",data[0]);
                        // IF SERVER REMOVE THE FILE, I REMOVE THE ROW
                        if (data[0].file == "") {
                            row.remove();
                        }
                        // IF NOT THERE ARE FILES, HIDE THE TABLE
                        if ($("tr",table).length == 0) {
                            $(table).addClass("d-none");
                        }
                    },
                    error:function (XMLHttpRequest,textStatus,errorThrown) {
                        console.log(XMLHttpRequest.statusText);
                        // TODO
                    },
                });
            });
            // ADD THE ROW
            $("tbody",table).append(row);
            // GET THE LOCAL FILE USING SYNCRONOUS TECHNIQUES
            var reader = new FileReader();
            reader.readAsDataURL(files[i]);
            while (!reader.result && !reader.error) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
            // IF THERE IS A FILE
            if (reader.result) {
                data.files[0].data = reader.result;
                // THIS ALLOW MULTIPLE UPLOADS IN PARALLEL
                (function(data,row) {
                    $.ajax({
                        url:"index.php",
                        data:JSON.stringify(data),
                        type:"post",
                        success:function (data,textStatus,XMLHttpRequest) {
                            $(row).data("data",data[0]);
                        },
                        error:function (XMLHttpRequest,textStatus,errorThrown) {
                            console.log(XMLHttpRequest.statusText);
                            // TODO
                        },
                        progress: function(e) {
                            if(e.lengthComputable) {
                                var percent = parseInt((e.loaded / e.total) * 100);
                                $(".progress-bar",row).width(percent+"%").attr("aria-valuenow",percent);
                            }
                        },
                    });
                })(data,row);
            }
            // IF THERE IS AN ERROR
            if (reader.error) {
                data.files[0].error = reader.error.message;
                console.log(reader.error.message);
                // TODO
            }
        }
    });
    return obj;
}

saltos.__form_field["link"] = function (field) {
    field.class = "btn-link";
    field.label = field.value;
    return saltos.__form_field["button"](field);
}

saltos.__form_field["label"] = function (field) {
    var obj = $(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    return obj;
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
