
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

/*
 * FORM FIELDS CONSTRUCTOR
 *
 * This function and their helpers, allow the creation of the interface using the bootstrap
 * widgets, the types that can be called are the follow:
 *
 * - container => has the container argument and by default is container-fluid
 * - row => has the row argument and by default is row
 * - col => has the col argument and by default is col
 * - text => has the follow arguments: class, id, placeholder, value, disabled, readonly, required
 * - hidden => has the follow arguments:
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
 *
 * Notes:
 *
 * The saltos.__form_field object is part of this constructor and act with the constructor
 * as a helper, the idea is that the user must to call the constructor and the helpers are
 * only for internal use
 *
 * By default, the constructor try to check the parameters that are used commonly in the
 * helpers functions, too, try to convert some "booleans" as disabled, readonly and required to
 * the string that will be used in reality by the bootstrap objects
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

/*
 * FORM_FIELD CONSTRUCTOR HELPER OBJECT
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.__form_field = {};

/*
 * CONTAINER CONSTRUCTOR HELPER
 *
 * This function returns an object of the container-fluid class by default, you can pass an argument
 * in the field object to specify what kind of container do you want to do.
 */
saltos.__form_field["container"] = function (field) {
    saltos.check_params(field,["container"]);
    if (field.container == "") {
        field.container = "container-fluid";
    }
    var obj = $(`<div class="${field.container}"></div>`);
    return obj;
};

/*
 * ROW CONSTRUCTOR HELPER
 *
 * This function returns an object of the row class by default, you can pass an argument in the field
 * object to specify what kind of row do you want to do.
 */
saltos.__form_field["row"] = function (field) {
    saltos.check_params(field,["row"]);
    if (field.row == "") {
        field.row = "row";
    }
    var obj = $(`<div class="${field.row}"></div>`);
    return obj;
};

/*
 * COL CONSTRUCTOR HELPER
 *
 * This function returns an object of the col class by default, you can pass an argument in the field
 * object to specify what kind of col do you want to do.
 */
saltos.__form_field["col"] = function (field) {
    saltos.check_params(field,["col"]);
    if (field.col == "") {
        field.col = "col";
    }
    var obj = $(`<div class="${field.col}"></div>`);
    return obj;
};

/*
 * TEXT CONSTRUCTOR HELPER
 *
 * This function returns an input object of type text, you can pass some arguments as:
 * - class => allow to add more classes to the default form-control
 * - id => the id used by the object
 * - placeholder => the text used as placeholder parameter
 * - value => the value used as value parameter
 * - disabled => this parameter raise the disabled flag
 * - readonly => this parameter raise the readonly flag
 * - required => this parameter raise the required flag
 */
saltos.__form_field["text"] = function (field) {
    var obj = $(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required}>
    `);
    return obj;
};

/*
 * HIDDEN CONSTRUCTOR HELPER
 *
 * This function returns an input object of type hidden, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field["hidden"] = function (field) {
    field.type = "hidden";
    var obj = saltos.__form_field["text"](field);
    return obj;
};

/*
 * INTEGER CONSTRUCTOR HELPER
 *
 * This function returns an input object of type integer, you can pass the same arguments
 * that for the input object of type text
 */
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

/*
 * FLOAT CONSTRUCTOR HELPER
 *
 * This function returns an input object of type float, you can pass the same arguments
 * that for the input object of type text
 */
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

/*
 * COLOR CONSTRUCTOR HELPER
 *
 * This function returns an input object of type color, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field["color"] = function (field) {
    field.class="form-control-color";
    var obj = saltos.__form_field["text"](field);
    return obj;
};

/*
 * DATE CONSTRUCTOR HELPER
 *
 * This function returns an input object of type date, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field["date"] = function (field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

/*
 * TIME CONSTRUCTOR HELPER
 *
 * This function returns an input object of type time, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field["time"] = function (field) {
    var obj = saltos.__form_field["text"](field);
    return obj;
};

/*
 * DATETIME CONSTRUCTOR HELPER
 *
 * This function returns an input object of type datetime, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field["datetime"] = function (field) {
    field.type = "datetime-local";
    var obj = saltos.__form_field["text"](field);
    return obj;
};

/*
 * PRIVATE TEXTAREA CONSTRUCTOR HELPER
 *
 * This function returns a textarea object, you can pass the follow arguments:
 * - class => allow to add more classes to the default form-control
 * - id => the id used by the object
 * - placeholder => the text used as placeholder parameter
 * - rows => the number used as rows parameter
 * - disabled => this parameter raise the disabled flag
 * - readonly => this parameter raise the readonly flag
 * - required => this parameter raise the required flag
 * - value => the value used as value parameter
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.__form_field["__textarea"] = function (field) {
    saltos.check_params(field,["rows"]);
    var obj = $(`
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    `);
    return obj;
};

/*
 * TEXTAREA CONSTRUCTOR HELPER
 *
 * This function returns a textarea object with the autogrow plugin enabled
 */
saltos.__form_field["textarea"] = function (field) {
    var obj = saltos.__form_field["__textarea"](field);
    var element = $(obj).get(0);
    saltos.when_visible(element ,function (element) {
        $(element).autogrow();
    },element);
    return obj;
};

/*
 * CKEDITOR CONSTRUCTOR HELPER
 *
 * This function returns a textarea object with the ckeditor plugin enabled
 */
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

/*
 * CODEMIRROR CONSTRUCTOR HELPER
 *
 * This function returns a textarea object with the codemirror plugin enabled
 */
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

/*
 * IFRAME CONSTRUCTOR HELPER
 *
 * This function returns an iframe object, you can pass the follow arguments:
 * - value => the value used as src parameter
 * - id => the id used by the object
 * - class => allow to add more classes to the default form-control
 * - height => the height used as height for the style parameter
 */
saltos.__form_field["iframe"] = function (field) {
    saltos.check_params(field,["height"]);
    var obj = $(`
        <iframe src="${field.value}" id="${field.id}" frameborder="0" class="form-control ${field.class}" style="height:${field.height}"></iframe>
    `);
    return obj;
}

/*
 * SELECT CONSTRUCTOR HELPER
 *
 * This function ...
 */
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

/*
 * MULTISELECT CONSTRUCTOR HELPER
 *
 * This function ...
 */
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

/*
 * CHECKBOX CONSTRUCTOR HELPER
 *
 * This function ...
 */
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

/*
 * BUTTON CONSTRUCTOR HELPER
 *
 * This function ...
 */
saltos.__form_field["button"] = function (field) {
    saltos.check_params(field,["onclick"]);
    var obj = $(`<button type="button" class="btn ${field.class}" id="${field.id}" ${field.disabled}>${field.label}</button>`);
    $(obj).on("click",field.onclick);
    return obj;
}

/*
 * PASSWORD CONSTRUCTOR HELPER
 *
 * This function ...
 */
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

/*
 * FILE CONSTRUCTOR HELPER
 *
 * This function ...
 */
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
    // THIS HELPER HELPS TO UPDATE THE DATA OF THE INPUT FILE
    var __update_data = function (input) {
        var data = [];
        var tabla = $(input).next();
        $("tr",tabla).each(function () {
            data.push($(this).data("data"));
        });
        $(input).data("data",data);
    };
    // PROGRAM THE AUTOMATIC UPLOAD
    $("input",obj).on("change",async function () {
        var input = this;
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
            $("button",row).on("click",function () {
                var row = $(this).parent().parent();
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
                        // IF SERVER REMOVES THE FILE, I REMOVE THE ROW
                        if (data[0].file == "") {
                            row.remove();
                        }
                        // IF NOT THERE ARE FILES, HIDE THE TABLE
                        if ($("tr",table).length == 0) {
                            $(table).addClass("d-none");
                        }
                        __update_data(input);
                    },
                    error:function (XMLHttpRequest,textStatus,errorThrown) {
                        console.log(XMLHttpRequest.statusText);
                        // TODO
                    },
                });
            });
            // ADD THE ROW
            $("tbody",table).append(row);
            __update_data(input);
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
                (function (data,row) {
                    $.ajax({
                        url:"index.php",
                        data:JSON.stringify(data),
                        type:"post",
                        success:function (data,textStatus,XMLHttpRequest) {
                            $(row).data("data",data[0]);
                            __update_data(input);
                        },
                        error:function (XMLHttpRequest,textStatus,errorThrown) {
                            console.log(XMLHttpRequest.statusText);
                            // TODO
                        },
                        progress: function (e) {
                            if (e.lengthComputable) {
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

/*
 * LINK CONSTRUCTOR HELPER
 *
 * This function ...
 */
saltos.__form_field["link"] = function (field) {
    var obj = $(`<div></div>`);
    field.class = "btn-link";
    field.label = field.value;
    $(obj).append(saltos.__form_field["button"](field));
    return obj;
}

/*
 * LABEL CONSTRUCTOR HELPER
 *
 * This function ...
 */
saltos.__form_field["label"] = function (field) {
    var obj = $(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    return obj;
}

/*
 * IMAGE CONSTRUCTOR HELPER
 *
 * This function ...
 */
saltos.__form_field["image"] = function (field) {
    var obj = $(`<div>
        <img id="${field.id}" src="${field.value}" class="img-fluid ${field.class}" alt="${field.label}">
    </div>`);
    return obj;
}

/*
 * EXCEL CONSTRUCTOR HELPER
 *
 * This function ...
 */
saltos.__form_field["excel"] = function (field) {
    saltos.check_params(field,["data","rowHeaders","colHeaders","minSpareRows","contextMenu","rowHeaderWidth","colWidths"]);
    var obj = $(`<div style="width:100%;height:100%;overflow:auto">
        <div id="${field.id}" class="${field.class}"></div>
    </div>`);
    if (field.data == "") {
        field.data = [...Array(20)].map(e => Array(26));
    }
    if (field.rowHeaders == "") {
        field.rowHeaders = true;
    }
    if (field.colHeaders == "") {
        field.colHeaders = true;
    }
    if (field.minSpareRows == "") {
        field.minSpareRows = 0;
    }
    if (field.contextMenu == "") {
        field.contextMenu = true;
    }
    if (field.rowHeaderWidth == "") {
        field.rowHeaderWidth = undefined;
    }
    if (field.colWidths == "") {
        field.colWidths = undefined;
    }
    var element = $("div",obj).get(0);
    saltos.when_visible(element ,function (element) {
        $(element).handsontable({
            data:field.data,
            rowHeaders:field.rowHeaders,
            colHeaders:field.colHeaders,
            minSpareRows:field.minSpareRows,
            contextMenu:field.contextMenu,
            rowHeaderWidth:field.rowHeaderWidth,
            colWidths:field.colWidths,
            afterChange:function (changes,source) {
                $(element).data("data",field.data);
            }
        });
    },element);
    return obj;
}

/*
 * PDFJS CONSTRUCTOR HELPER
 *
 * This function ...
 */
saltos.__form_field["pdfjs"] = function (field) {
    var obj = $(`<div>
        <div id="${field.id}" class="${field.class}"><div class="pdfViewer"></div></div>
    </div>`);
    var element = $("div:first",obj).get(0);
    saltos.when_visible(element ,function (element) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "lib/pdfjs/pdf.worker.min.js";
        pdfjsLib.getDocument(field.value).promise.then(function (pdfDocument) {
            if (!pdfDocument.numPages) {
                return;
            }
            $(element).css("position","absolute");
            var container = element;
            var eventBus = new pdfjsViewer.EventBus();
            var pdfViewer = new pdfjsViewer.PDFViewer({
                container:container,
                eventBus:eventBus,
            });
            var fn1 = function () {
                pdfViewer.currentScaleValue = "page-width";
            };
            var fn2 = function () {
                $("a",container).each(function () {
                    $(this).attr("target","_blank");
                });
            };
            eventBus.on("pagesinit",fn1);
            eventBus.on("annotationlayerrendered",fn2);
            pdfViewer.removePageBorders = true;
            pdfViewer.setDocument(pdfDocument);
            $(element).css("position","relative");
            $(window).on("resize",function () {
                pdfViewer.currentScaleValue = pdfViewer.currentScale * 2;
                pdfViewer.currentScaleValue = "page-width";
            });
        },function (message,exception) {
            console.log(message);
            // TODO
        });
    },element);
    return obj;
}

// tables
// input rollo multiples emails
// modal
// alert
// card
// navbar
// offcanvas
// toasts
// tooltips ???
