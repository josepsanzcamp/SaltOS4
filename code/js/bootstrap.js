
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
 * Form fields constructor
 *
 * This function and their helpers, allow the creation of the interface using the bootstrap
 * widgets, the types that can be called are the follow:
 *
 * @container => container
 * @row => row
 * @col => col
 * @text => class, id, placeholder, value, disabled, readonly, required
 * @hidden => class, id, placeholder, value, disabled, readonly, required
 * @integer => class, id, placeholder, value, disabled, readonly, required
 * @float => class, id, placeholder, value, disabled, readonly, required
 * @color => class, id, placeholder, value, disabled, readonly, required
 * @date => class, id, placeholder, value, disabled, readonly, required
 * @time => class, id, placeholder, value, disabled, readonly, required
 * @datetime => class, id, placeholder, value, disabled, readonly, required
 * @textarea => class, id, placeholder, value, disabled, readonly, required, rows
 * @ckeditor => class, id, placeholder, value, disabled, readonly, required, rows
 * @codemirror => class, id, placeholder, value, disabled, readonly, required, rows, mode
 * @iframe => value, id, class, height
 * @select => class, id, disabled, required, rows, multiple, size, value
 * @multiselect => class, id, disabled, required, rows, multiple, size, value, multiple
 * @checkbox => id, disabled, readonly, label, value
 * @switch => id, disabled, readonly, label, value
 * @button => class, id, disabled, value, onclick
 * @password => class, id, placeholder, value, disabled, readonly, required
 * @file => class, id, disabled, required, multiple
 * @link => id, disabled, value, onclick
 * @label => id, label
 * @image => id, value, class, alt
 * @excel => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth, colWidths
 * @pdfjs => id, class, value
 * @table => class, id, header, data, footer, divider
 * @alert => class, value
 * @card => image, alt, header, footer, title, text, body
 *
 * Notes:
 *
 * The saltos.__form_field object is part of this constructor and act with the constructor
 * as a helper, the idea is that the user must to call the constructor and the helpers are
 * only for internal use.
 *
 * By default, the constructor try to check the parameters that are used commonly in the
 * helpers functions, too, try to convert some "booleans" as disabled, readonly and required to
 * the string that will be used in reality by the bootstrap objects.
 *
 * All widgets add an extra widget label using the label parameter if it is found, only some
 * widgets have the special case that not includes the label for logical reasons.
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
    if (["label","checkbox","switch"].includes(field.type)) {
        return saltos.__form_field[field.type](field);
    }
    if (field.label == "") {
        return saltos.__form_field[field.type](field);
    }
    var obj = $(`<div></div>`);
    $(obj).append(saltos.__form_field.label(field));
    $(obj).append(`<div></div>`);
    $("div:last", obj).append(saltos.__form_field[field.type](field));
    return obj;
};

/*
 * Form_field constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.__form_field = {};

/*
 * Container constructor helper
 *
 * This function returns an object of the container-fluid class by default, you can pass an argument
 * in the field object to specify what kind of container do you want to do.
 */
saltos.__form_field.container = function (field) {
    saltos.check_params(field,["container"]);
    if (field.container == "") {
        field.container = "container-fluid";
    }
    var obj = $(`<div class="${field.container}"></div>`);
    return obj;
};

/*
 * Row constructor helper
 *
 * This function returns an object of the row class by default, you can pass an argument in the field
 * object to specify what kind of row do you want to do.
 */
saltos.__form_field.row = function (field) {
    saltos.check_params(field,["row"]);
    if (field.row == "") {
        field.row = "row";
    }
    var obj = $(`<div class="${field.row}"></div>`);
    return obj;
};

/*
 * Col constructor helper
 *
 * This function returns an object of the col class by default, you can pass an argument in the field
 * object to specify what kind of col do you want to do.
 */
saltos.__form_field.col = function (field) {
    saltos.check_params(field,["col"]);
    if (field.col == "") {
        field.col = "col";
    }
    var obj = $(`<div class="${field.col}"></div>`);
    return obj;
};

/*
 * Text constructor helper
 *
 * This function returns an input object of type text, you can pass some arguments as:
 *
 * @class => allow to add more classes to the default form-control
 * @id => the id used by the object
 * @placeholder => the text used as placeholder parameter
 * @value => the value used as value parameter
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @required => this parameter raise the required flag
 */
saltos.__form_field.text = function (field) {
    var obj = $(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" ${field.disabled} ${field.readonly} ${field.required}>
    `);
    return obj;
};

/*
 * Hidden constructor helper
 *
 * This function returns an input object of type hidden, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.hidden = function (field) {
    field.type = "hidden";
    var obj = saltos.__form_field.text(field);
    return obj;
};

/*
 * Integer constructor helper
 *
 * This function returns an input object of type integer, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.integer = function (field) {
    field.type = "text";
    var obj = saltos.__form_field.text(field);
    var element = $(obj).get(0);
    IMask(element, {
        mask: Number,
        signed: true,
        scale: 0,
    });
    return obj;
};

/*
 * Float constructor helper
 *
 * This function returns an input object of type float, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.float = function (field) {
    field.type = "text";
    var obj = saltos.__form_field.text(field);
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
 * Color constructor helper
 *
 * This function returns an input object of type color, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.color = function (field) {
    field.class="form-control-color";
    var obj = saltos.__form_field.text(field);
    return obj;
};

/*
 * Date constructor helper
 *
 * This function returns an input object of type date, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.date = function (field) {
    var obj = saltos.__form_field.text(field);
    return obj;
};

/*
 * Time constructor helper
 *
 * This function returns an input object of type time, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.time = function (field) {
    var obj = saltos.__form_field.text(field);
    return obj;
};

/*
 * Datetime constructor helper
 *
 * This function returns an input object of type datetime, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.datetime = function (field) {
    field.type = "datetime-local";
    var obj = saltos.__form_field.text(field);
    return obj;
};

/*
 * Private textarea constructor helper
 *
 * This function returns a textarea object, you can pass the follow arguments:
 *
 * @class => allow to add more classes to the default form-control
 * @id => the id used by the object
 * @placeholder => the text used as placeholder parameter
 * @rows => the number used as rows parameter
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @required => this parameter raise the required flag
 * @value => the value used as value parameter
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.__form_field.__textarea = function (field) {
    saltos.check_params(field,["rows"]);
    var obj = $(`
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}" ${field.disabled} ${field.readonly} ${field.required}>${field.value}</textarea>
    `);
    return obj;
};

/*
 * Textarea constructor helper
 *
 * This function returns a textarea object with the autogrow plugin enabled
 */
saltos.__form_field.textarea = function (field) {
    var obj = saltos.__form_field.__textarea(field);
    var element = $(obj).get(0);
    saltos.when_visible(element ,function (element) {
        $(element).autogrow();
    },element);
    return obj;
};

/*
 * Ckeditor constructor helper
 *
 * This function returns a textarea object with the ckeditor plugin enabled
 */
saltos.__form_field.ckeditor = function (field) {
    var obj = saltos.__form_field.__textarea(field);
    var element = $(obj).get(0);
    saltos.when_visible(element ,function (element) {
        ClassicEditor.create(element).catch(error => {
            console.error(error);
        });
    },element);
    return obj;
};

/*
 * Codemirror constructor helper
 *
 * This function returns a textarea object with the codemirror plugin enabled, it has
 * the parameter mode that allow the caller to specify what kind of mode want use
 */
saltos.__form_field.codemirror = function (field) {
    saltos.check_params(field,["mode"]);
    var obj = saltos.__form_field.__textarea(field);
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
 * Iframe constructor helper
 *
 * This function returns an iframe object, you can pass the follow arguments:
 *
 * @value => the value used as src parameter
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-control
 * @height => the height used as height for the style parameter
 */
saltos.__form_field.iframe = function (field) {
    saltos.check_params(field,["height"]);
    var obj = $(`
        <iframe src="${field.value}" id="${field.id}" frameborder="0" class="form-control ${field.class}" style="height:${field.height}"></iframe>
    `);
    return obj;
};

/*
 * Select constructor helper
 *
 * This function returns a select object, you can pass the follow arguments:
 *
 * @class => allow to add more classes to the default form-select
 * @id => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @rows => this parameter contains the list of options, each option must be an object with label and value entries
 * @multiple => this parameter enables the multiple selection feature of the select
 * @size => this parameter allow to see the options list opened with n (size) entries
 * @value => the value used to detect the selected option
 */
saltos.__form_field.select = function (field) {
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
};

/*
 * Multiselect constructor helper
 *
 * This function returns a multiselect object, you can pass the follow arguments:
 *
 * @class => allow to add more classes to the default form-select
 * @id => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @rows => this parameter contains the list of options, each option must be an object with label and value entries
 * @size => this parameter allow to see the options list opened with n (size) entries
 * @value => the value used as src parameter
 *
 * Notes:
 *
 * This widget is created joinin 2 selects and 2 buttons, the user must get the value
 * using the hidden input that is builded using the original id passed by argument.
 */
saltos.__form_field.multiselect = function (field) {
    saltos.check_params(field,["rows","size"]);
    var obj = $(`
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
    `);
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
    $(".col:eq(0)",obj).append(saltos.__form_field.hidden(field));
    $(".col:eq(0)",obj).append(saltos.__form_field.select({
        class:field.class,
        id:field.id+"_a",
        disabled:field.disabled,
        multiple:true,
        size:field.size,
        rows:rows_a,
        value:"",
    }));
    $(".col:eq(1)",obj).append(saltos.__form_field.button({
        class:"btn-primary bi-chevron-double-right mb-3",
        id:field.id+"_c",
        disabled:field.disabled,
        value:"",
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
    $(".col:eq(1)",obj).append(saltos.__form_field.button({
        class:"btn-primary bi-chevron-double-left",
        id:field.id+"_d",
        disabled:field.disabled,
        value:"",
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
    $(".col:eq(2)",obj).append(saltos.__form_field.select({
        class:field.class,
        id:field.id+"_b",
        disabled:field.disabled,
        multiple:true,
        size:field.size,
        rows:rows_b,
        value:"",
    }));
    return obj;
};

/*
 * Checkbox constructor helper
 *
 * This function returns a checkbox object, you can pass the follow arguments:
 *
 * @id => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @label => this parameter is used as label for the checkbox/switch
 * @value => this parameter is used to check or unckeck the checkbox/switch, the value must contain a number that raise as true or false in the if condition
 *
 * Notes:
 *
 * This widget returns their value by setting a zero or one (0/1) value on the value of the input.
 *
 * Using the type argument, the function add some class and role to the object converting the widget
 * from the traditional checkbox into a switch, for some reasone, we decide to maintain the original
 * checkbox and add another function that uses immersion with this function to create the switch.
 */
saltos.__form_field.checkbox = function (field) {
    field.value = parseInt(field.value);
    if (isNaN(field.value)) {
        field.value = 0;
    }
    var checked = "";
    if (field.value) {
        checked = "checked";
    }
    var _class = "";
    var _role = "";
    if (field.type == "switch") {
        _class = "form-switch";
        _role = `role="switch"`;
    }
    var obj = $(`
        <div class="form-check ${_class}">
            <input class="form-check-input" type="checkbox" ${_role} id="${field.id}" value="${field.value}" ${field.disabled} ${field.readonly} ${checked}>
            <label class="form-check-label" for="${field.id}">${field.label}</label>
        </div>
    `);
    $("input",obj).on("change",function () {
        this.value = this.checked ? 1 : 0;
    });
    return obj;
};

/*
 * Switch constructor helper
 *
 * This function returns a switch object, you can pass the same arguments that for the checknbox object
 */
saltos.__form_field.switch = function (field) {
    return saltos.__form_field.checkbox(field);
};

/*
 * Button constructor helper
 *
 * This function returns a button object, you can pass the follow arguments:
 *
 * @class => allow to add more classes to the default form-select
 * @id => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @value => value to be used as text in the contents of the buttons
 * @onclick => callback function that is executed when the button is pressed
 *
 * Notes:
 *
 * You can add an icon before the text by addind the bi-icon class to the class argument
 */
saltos.__form_field.button = function (field) {
    saltos.check_params(field,["onclick"]);
    var obj = $(`<button type="button" class="btn ${field.class}" id="${field.id}" ${field.disabled}>${field.value}</button>`);
    $(obj).on("click",field.onclick);
    return obj;
};

/*
 * Password constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @class => allow to add more classes to the default form-control
 * @id => the id used by the object
 * @placeholder => the text used as placeholder parameter
 * @value => the value used as value parameter
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @required => this parameter raise the required flag
 *
 * Notes:
 *
 * This widget add an icon to the end of the widget with an slashed eye, this allow to
 * see the entered password to verify it, in reality, this button swaps the input between
 * password and text type, allowing to do visible or not the contents of the input
 */
saltos.__form_field.password = function (field) {
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
};

/*
 * File constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @class => allow to add more classes to the default form-control
 * @id => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @multiple => this parameter raise the multiple flag, intended to select more files at time
 *
 * Notes:
 *
 * This control allow to select file from the tradicional input, and automatically, send it
 * to the server using the addfiles action, add a row in the widget's table to show information
 * about the new file and allow too to delete it using the trash button.
 *
 * To get the data, the controls store each file information in each added row of the table and
 * in addition, too join all information in a data structure of the input of type file.
 *
 * The difference between this control and the older controls is that they send the files to
 * the server and store the information related to the file on the server to be processed after
 * the real upload action.
 */
saltos.__form_field.file = function (field) {
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
    // This helper programs the input file data update
    var __update_data_input_file = function (input) {
        var data = [];
        var tabla = $(input).next();
        $("tr",tabla).each(function () {
            data.push($(this).data("data"));
        });
        $(input).data("data",data);
    };
    // Program the automatic upload
    $("input",obj).on("change",async function () {
        var input = this;
        var files = this.files;
        var table = $(this).next();
        for (var i = 0; i < files.length; i++) {
            // Prepare the data to send
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
            // Show the table
            $(table).removeClass("d-none");
            // Add the row for the new file
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
            // Store the data in the row
            $(row).data("data",data.files[0]);
            // Program de remove button
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
                        // If server removes the file, i remove the row
                        if (data[0].file == "") {
                            row.remove();
                        }
                        // If not there are files, hide the table
                        if ($("tr",table).length == 0) {
                            $(table).addClass("d-none");
                        }
                        __update_data_input_file(input);
                    },
                    error:function (XMLHttpRequest,textStatus,errorThrown) {
                        console.log(XMLHttpRequest.statusText);
                        // TODO
                    },
                });
            });
            // Add the row
            $("tbody",table).append(row);
            __update_data_input_file(input);
            // Get the local file using syncronous techniques
            var reader = new FileReader();
            reader.readAsDataURL(files[i]);
            while (!reader.result && !reader.error) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
            // If there is a file
            if (reader.result) {
                data.files[0].data = reader.result;
                // This allow multiple uploads in parallel
                (function (data,row) {
                    $.ajax({
                        url:"index.php",
                        data:JSON.stringify(data),
                        type:"post",
                        success:function (data,textStatus,XMLHttpRequest) {
                            $(row).data("data",data[0]);
                            __update_data_input_file(input);
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
            // If there is an error
            if (reader.error) {
                data.files[0].error = reader.error.message;
                console.log(reader.error.message);
                // TODO
            }
        }
    });
    return obj;
};

/*
 * Link constructor helper
 *
 * This function creates a field similar of text but with the appearance of a link using a button,
 * the object can receive the follow arguments:
 *
 * @id => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @value => the value is conveted as label to be used in the button with the appearance of a link
 * @onclick => callback function that is executed when the button is pressed
 *
 * Notes:
 *
 * This object is not a real link, it's a button that uses the btn-link class to get the link
 * appearance
 */
saltos.__form_field.link = function (field) {
    field.class = "btn-link";
    var obj = saltos.__form_field.button(field);
    return obj;
};

/*
 * Label constructor helper
 *
 * This function returns a label object, you can pass some arguments as:
 *
 * @id => the id used to set the reference for to the object
 * @label => this parameter is used as text for the label
 */
saltos.__form_field.label = function (field) {
    var obj = $(`<label for="${field.id}" class="form-label">${field.label}</label>`);
    return obj;
};

/*
 * Image constructor helper
 *
 * This function returns an image object, you can pass some arguments as:
 *
 * @id => the id used to set the reference for to the object
 * @value => the value used as src parameter
 * @class => allow to add more classes to the default img-fluid
 * @alt => this parameter is used as text for the alt parameter
 */
saltos.__form_field.image = function (field) {
    saltos.check_params(field,["alt"]);
    var obj = $(`
        <img id="${field.id}" src="${field.value}" class="img-fluid ${field.class}" alt="${field.alt}">
    `);
    return obj;
};

/*
 * Excel constructor helper
 *
 * This function creates and returns an excel object, to do this they use the handsontable library,
 * currently this library uses a privative license, by this reason, we are using the version 6.2.2
 * that is the latest release published using the MIT license.
 *
 * This widget can receive the following arguments:
 *
 * @id => the id used to set the reference for to the object
 * @class => allow to set the class to the div object used to allocate the widget
 * @data => this parameter must contain a 2D matrix with the data that you want to show in the sheet
 * @rowHeaders => can be an array with the headers that you want to use instead the defaults numbers
 * @colHeaders => can be an array with the headers that you want to use instead the defaults letters
 * @minSpareRows => can be a number with the void rows at the end of the sheet
 * @contextMenu => can be a boolean with the desired value to allow or not the provided context menu of the widget
 * @rowHeaderWidth => can be a number with the width of the headers rows
 * @colWidths => can be an array with the widths of the headers cols
 *
 * Notes:
 *
 * You can get the values after to do changes by accessing to the data of the div used to create
 * the widget.
 */
saltos.__form_field.excel = function (field) {
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
};

/*
 * Pdfjs constructor helper
 *
 * This function creates and returns a pdfviewer object, to do this they use the pdf.js library.
 *
 * @id => the id used to set the reference for to the object
 * @class => allow to set the class to the div object used to allocate the widget
 * @value => the file or data that contains the pdf document
 */
saltos.__form_field.pdfjs = function (field) {
    var obj = $(`
        <div id="${field.id}" class="${field.class}"><div class="pdfViewer"></div></div>
    `);
    var element = $(obj).get(0);
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
};

/*
 * Table constructor helper
 *
 * Returns a table using the follow params:
 *
 * @class => allow to add more classes to the default table table-striped table-hover
 * @id => the id used to set the reference for to the object
 * @header => array with the header to use
 * @data => 2D array with the data used to mount the body table
 * @footer => array with the footer to use
 * @divider => array with three booleans to specify to add the divider in header, body and/or footer
 */
saltos.__form_field.table = function (field) {
    saltos.check_params(field,["header","data","footer","divider"]);
    var obj = $(`
        <table class="table table-striped table-hover ${field.class}" id="${field.id}">
        </table>
    `);
    if (field.header != "") {
        $(obj).append(`
            <thead>
                <tr>
                </tr>
            </thead>
        `);
        if (isset(field.divider[0]) && field.divider[0]) {
            $("thead",obj).addClass("table-group-divider");
        }
        for (var key in field.header) {
            $("thead tr",obj).append(`<th>${field.header[key]}</th>`);
        }
    }
    if (field.data != "") {
        $(obj).append(`
            <tbody>
            </tbody>
        `);
        if (isset(field.divider[1]) && field.divider[1]) {
            $("tbody",obj).addClass("table-group-divider");
        }
        for (var key in field.data) {
            var row = $(`<tr></tr>`);
            for (var key2 in field.data[key]) {
                row.append(`<td>${field.data[key][key2]}</td>`);
            }
            $("tbody",obj).append(row);
        }
    }
    if (field.footer != "") {
        $(obj).append(`
            <tfoot>
                <tr>
                </tr>
            </tfoot>
        `);
        if (isset(field.divider[2]) && field.divider[2]) {
            $("tfoot",obj).addClass("table-group-divider");
        }
        for (var key in field.footer) {
            $("tfoot tr",obj).append(`<td>${field.footer[key]}</td>`);
        }
    }
    return obj;
};

/*
 * Alert constructor helper
 *
 * This component allow to set boxes type alert in the contents, only requires:
 *
 * @class => allow to add more classes to the default alert
 * @value => this parameter is used as text for the alert
 */
saltos.__form_field.alert = function (field) {
    var obj = $(`
        <div class="alert ${field.class}" role="alert">${field.value}</div>
    `);
    return obj;
};

/*
 * Card constructor helper
 *
 * This functions creates a card with a lot of options:
 *
 * @image => image used as top image in the card, not used if void
 * @alt => alt text used in the top image if you specify an image
 * @header => text used in the header, not used if void
 * @footer => text used in the footer, not used if void
 * @title => title used in the body of the card, not used if void
 * @text => text used in the body of the card, not used if void
 * @body => this option allow to specify an specific html to the body of the card, intended to personalize the body's card
 */
saltos.__form_field.card = function (field) {
    saltos.check_params(field,["image","alt","header","footer","title","text","body"]);
    var obj = $(`
        <div class="card">
        </div>
    `);
    if (field.image != "") {
        obj.append(`
            <img src="${field.image}" class="card-img-top" alt="${field.alt}">
        `);
    }
    if (field.header != "") {
        obj.append(`
            <div class="card-header">${field.header}</div>
        `);
    }
    obj.append(`
        <div class="card-body">
        </div>
    `);
    if (field.title != "") {
        $(".card-body",obj).append(`
            <h5 class="card-title">${field.title}</h5>
        `)
    }
    if (field.text != "") {
        $(".card-body",obj).append(`
            <p class="card-text">${field.text}</p>
        `)
    }
    if (field.body != "") {
        $(".card-body",obj).append(`
            ${field.body}
        `);
    }
    if (field.footer != "") {
        obj.append(`
            <div class="card-footer">${field.footer}</div>
        `);
    }
    return obj;
};

/*
 * TODO
 */
saltos.__form_field.navbar = function (field) {
    var obj = $(`TODO`);
    return obj;
};

/*
 * TODO
 */
saltos.__form_field.modal = function (field) {
    var obj = $(`TODO`);
    return obj;
};

/*
 * TODO
 */
saltos.__form_field.offcanvas = function (field) {
    var obj = $(`TODO`);
    return obj;
};

/*
 * TODO
 */
saltos.__form_field.toasts = function (field) {
    var obj = $(`TODO`);
    return obj;
};

/*
 * TODO
 */
saltos.__form_field.chartjs = function (field) {
    var obj = $(`<canvas id="myChart"></canvas>`);
    return obj;
};

// tooltips ???
// input rollo multiples emails
