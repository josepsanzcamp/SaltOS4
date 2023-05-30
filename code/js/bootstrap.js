
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
 * @div => id, class, style
 * @container => id, class, style
 * @row => id, class, style
 * @col => id, class, style
 * @text => id, class, placeholder, value, disabled, readonly, required, datalist, tooltip
 * @hidden => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @integer => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @float => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @color => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @date => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @time => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @datetime => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @textarea => id, class, placeholder, value, disabled, readonly, required, rows, tooltip
 * @ckeditor => id, class, placeholder, value, disabled, readonly, required, rows
 * @codemirror => id, class, placeholder, value, disabled, readonly, required, rows, mode
 * @iframe => value, id, class, height, tooltip
 * @select => id, class, disabled, required, rows, multiple, size, value, tooltip
 * @multiselect => id, class, disabled, required, rows, multiple, size, value, multiple, tooltip
 * @checkbox => id, class, disabled, readonly, label, value, tooltip
 * @switch => id, class, disabled, readonly, label, value, tooltip
 * @button => id, class, disabled, value, onclick, tooltip
 * @password => id, class, placeholder, value, disabled, readonly, required, tooltip
 * @file => id, class, disabled, required, multiple, tooltip
 * @link => id, disabled, value, onclick, tooltip
 * @label => id, class, label, tooltip, value
 * @image => id, class, value, alt, tooltip
 * @excel => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth, colWidths
 * @pdfjs => id, class, value
 * @table => id, class, header, data, footer, divider, source, value
 * @alert => id, class, title, text, body, source, value
 * @card => id, image, alt, header, footer, title, text, body, source, value
 * @chartjs => id, mode, data, source, value
 * @tags => id, class, placeholder, value, disabled, readonly, required, datalist, tooltip
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
    saltos.check_params(field,["id","label","type"]);
    if (field.id == "") {
        field.id = saltos.uniqid();
    }
    if (typeof saltos.__form_field[field.type] != "function") {
        console.log("type " + field.type + " not found");
        return saltos.html("type " + field.type + " not found");
    }
    if (["label","checkbox","switch"].includes(field.type)) {
        return saltos.__form_field[field.type](field);
    }
    if (field.label == "") {
        return saltos.__form_field[field.type](field);
    }
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__form_field.label(field));
    obj.append(saltos.html(`<div class="last"></div>`));
    obj.querySelector("div.last").append(saltos.__form_field[field.type](field));
    return obj;
};

/*
 * Form_field constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.__form_field = {};

/*
 * Div constructor helper
 *
 * This function returns an object of the type class by default, you can pass the class
 * argument in the field object to specify what kind of class do you want to use.
 *
 * @id => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.div = function (field) {
    saltos.check_params(field,["class","id","style"]);
    var obj = saltos.html(`<div class="${field.class}" id="${field.id}" style="${field.style}"></div>`);
    return obj;
};

/*
 * Container constructor helper
 *
 * This function returns an object of the container-fluid class by default, you can pass the class
 * argument in the field object to specify what kind of container do you want to do.
 *
 * @id => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.container = function (field) {
    saltos.check_params(field,["class"]);
    if (field.class == "") {
        field.class = "container-fluid";
    }
    var obj = saltos.__form_field.div(field);
    return obj;
};

/*
 * Row constructor helper
 *
 * This function returns an object of the row class by default, you can pass the class argument
 * in the field object to specify what kind of row do you want to do.
 *
 * @id => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.row = function (field) {
    saltos.check_params(field,["class"]);
    if (field.class == "") {
        field.class = "row";
    }
    var obj = saltos.__form_field.div(field);
    return obj;
};

/*
 * Col constructor helper
 *
 * This function returns an object of the col class by default, you can pass the class argument
 * in the field object to specify what kind of col do you want to do.
 *
 * @id => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.col = function (field) {
    saltos.check_params(field,["class"]);
    if (field.class == "") {
        field.class = "col";
    }
    var obj = saltos.__form_field.div(field);
    return obj;
};

/*
 * Private text constructor helper
 *
 * This function returns an input object of type text, you can pass some arguments as:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-control
 * @style => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value => the value used as value parameter
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @required => this parameter raise the required flag
 * @tooltip => this parameter raise the title flag
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.__form_field.__text = function (field) {
    saltos.check_params(field,["type","class","id","placeholder","value","disabled","readonly","required","tooltip","style"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.readonly) {
        field.readonly = "readonly";
    }
    if (field.required) {
        field.required = "required";
    }
    var obj = saltos.html(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}" style="${field.style}" placeholder="${field.placeholder}"
            value="${field.value}" ${field.disabled} ${field.readonly} ${field.required} data-bs-title="${field.tooltip}">
    `);
    if (field.tooltip != "") {
        new bootstrap.Tooltip(obj);
    }
    return obj;
};

/*
 * Text constructor helper
 *
 * This function returns an input object of type text, you can pass the same arguments
 * that for the input object of type text
 *
 * @datalist => array with options for the datalist, used as autocomplete for the text input
 */
saltos.__form_field.text = function (field) {
    saltos.check_params(field,["datalist"],[]);
    field.type = "text";
    if (!field.datalist.length) {
        return saltos.__form_field.__text(field);
    }
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__form_field.__text(field));
    obj.querySelector("input").setAttribute("list",field.id + "_datalist");
    obj.append(saltos.html(`<datalist id="${field.id}_datalist"></datalist>`));
    for (var key in field.datalist) {
        var val = field.datalist[key];
        obj.querySelector("datalist").append(saltos.html(`<option value="${val}">`));
    }
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
    var obj = saltos.__form_field.__text(field);
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
    var obj = saltos.__form_field.__text(field);
    IMask(obj, {
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
    var obj = saltos.__form_field.__text(field);
    IMask(obj, {
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
    field.type = "color";
    field.class = "form-control-color";
    var obj = saltos.__form_field.__text(field);
    return obj;
};

/*
 * Date constructor helper
 *
 * This function returns an input object of type date, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.date = function (field) {
    field.type = "date";
    var obj = saltos.__form_field.__text(field);
    return obj;
};

/*
 * Time constructor helper
 *
 * This function returns an input object of type time, you can pass the same arguments
 * that for the input object of type text
 */
saltos.__form_field.time = function (field) {
    field.type = "time";
    var obj = saltos.__form_field.__text(field);
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
    var obj = saltos.__form_field.__text(field);
    return obj;
};

/*
 * Private textarea constructor helper
 *
 * This function returns a textarea object, you can pass the follow arguments:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value => the value used as value parameter
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @required => this parameter raise the required flag
 * @tooltip => this parameter raise the title flag
 * @rows => the number used as rows parameter
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.__form_field.__textarea = function (field) {
    saltos.check_params(field,["class","id","placeholder","value","disabled","readonly","required","rows","tooltip"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.readonly) {
        field.readonly = "readonly";
    }
    if (field.required) {
        field.required = "required";
    }
    var obj = saltos.html(`
        <textarea class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" rows="${field.rows}"
            ${field.disabled} ${field.readonly} ${field.required} data-bs-title="${field.tooltip}">${field.value}</textarea>
    `);
    if (field.tooltip != "") {
        new bootstrap.Tooltip(obj);
    }
    return obj;
};

/*
 * Textarea constructor helper
 *
 * This function returns a textarea object with the autogrow plugin enabled
 */
saltos.__form_field.textarea = function (field) {
    var obj = saltos.__form_field.__textarea(field);
    saltos.when_visible(obj ,function () {
        autoheight(obj);
    });
    return obj;
};

/*
 * Ckeditor constructor helper
 *
 * This function returns a textarea object with the ckeditor plugin enabled
 */
saltos.__form_field.ckeditor = function (field) {
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__form_field.__textarea(field));
    var element = obj.querySelector("textarea");
    saltos.when_visible(element ,function () {
        ClassicEditor.create(element).catch(error => {
            console.error(error);
        });
    });
    return obj;
};

/*
 * Codemirror constructor helper
 *
 * This function returns a textarea object with the codemirror plugin enabled
 *
 * @mode => used to define the mode parameter of the codemirror
 */
saltos.__form_field.codemirror = function (field) {
    saltos.check_params(field,["mode"]);
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__form_field.__textarea(field));
    var element = obj.querySelector("textarea");
    saltos.when_visible(element ,function () {
        var cm = CodeMirror.fromTextArea(element,{
            mode: field.mode,
            styleActiveLine: true,
            lineNumbers: true,
            lineWrapping: true,
        });
        element.nextSibling.classList.add("form-control");
        element.nextSibling.classList.add("p-0");
        element.nextSibling.style.height = "auto";
        cm.on("change",cm.save);
    });
    return obj;
};

/*
 * Iframe constructor helper
 *
 * This function returns an iframe object, you can pass the follow arguments:
 *
 * @id => the id used by the object
 * @value => the value used as src parameter
 * @class => allow to add more classes to the default form-control
 * @height => the height used as height for the style parameter
 */
saltos.__form_field.iframe = function (field) {
    saltos.check_params(field,["value","id","class","height"]);
    var obj = saltos.html(`
        <iframe src="${field.value}" id="${field.id}" frameborder="0" class="form-control p-0 ${field.class}" style="height:${field.height}"></iframe>
    `);
    return obj;
};

/*
 * Select constructor helper
 *
 * This function returns a select object, you can pass the follow arguments:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-select
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @multiple => this parameter enables the multiple selection feature of the select
 * @size => this parameter allow to see the options list opened with n (size) entries
 * @value => the value used to detect the selected option
 * @tooltip => this parameter raise the title flag
 * @rows => this parameter contains the list of options, each option must be an object with label and value entries
 */
saltos.__form_field.select = function (field) {
    saltos.check_params(field,["class","id","disabled","required","multiple","size","value","tooltip"]);
    saltos.check_params(field,["rows"],[]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.required) {
        field.required = "required";
    }
    if (field.multiple) {
        field.multiple = "multiple";
    }
    if (field.size != "") {
        field.size = `size="${field.size}"`;
    }
    var obj = saltos.html(`
        <select class="form-select ${field.class}" id="${field.id}" ${field.disabled} ${field.required}
            ${field.multiple} ${field.size} data-bs-title="${field.tooltip}"></select>
    `);
    if (field.tooltip != "") {
        new bootstrap.Tooltip(obj);
    }
    for (var key in field.rows) {
        var val = field.rows[key];
        var selected = "";
        if (field.value.toString() == val.value.toString()) {
            selected = "selected";
        }
        obj.append(saltos.html(`<option value="${val.value}" ${selected}>${val.label}</option>`));
    }
    return obj;
};

/*
 * Multiselect constructor helper
 *
 * This function returns a multiselect object, you can pass the follow arguments:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-select
 * @disabled => this parameter raise the disabled flag
 * @size => this parameter allow to see the options list opened with n (size) entries
 * @value => the value used as src parameter
 * @tooltip => this parameter raise the title flag
 * @rows => this parameter contains the list of options, each option must be an object with label and value entries
 *
 * Notes:
 *
 * This widget is created joinin 2 selects and 2 buttons, the user must get the value
 * using the hidden input that is builded using the original id passed by argument.
 *
 * TODO: detected a bug with this widget in chrome in mobile browsers
 */
saltos.__form_field.multiselect = function (field) {
    saltos.check_params(field,["value","class","id","disabled","size","tooltip"]);
    saltos.check_params(field,["rows"],[]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    var obj = saltos.html(`
        <div class="container-fluid">
            <div class="row">
                <div class="col px-0 one">
                </div>
                <div class="col col-auto my-auto two">
                </div>
                <div class="col px-0 three">
                </div>
            </div>
        </div>
    `);
    var rows_abc = [];
    var rows_xyz = [];
    var values = field.value.split(",");
    for (var key in field.rows) {
        var val = field.rows[key];
        if (values.includes(val.value.toString())) {
            rows_xyz.push(val);
        } else {
            rows_abc.push(val);
        }
    }
    obj.querySelector(".one").append(saltos.__form_field.hidden(field));
    obj.querySelector(".one").append(saltos.__form_field.select({
        class:field.class,
        id:field.id + "_abc",
        disabled:field.disabled,
        tooltip:field.tooltip,
        multiple:true,
        size:field.size,
        rows:rows_abc,
    }));
    obj.querySelector(".two").append(saltos.__form_field.button({
        class:"btn-primary bi-chevron-double-right mb-3",
        disabled:field.disabled,
        //tooltip:field.tooltip,
        onclick:function () {
            document.querySelectorAll("#" + field.id + "_abc option").forEach(function (option) {
                if (option.selected) {
                    document.querySelector("#" + field.id + "_xyz").append(option);
                }
            });
            var val = [];
            document.querySelectorAll("#" + field.id + "_xyz option").forEach(function (option) {
                val.push(option.value);
            });
            document.querySelector("#" + field.id).value = val.join(",");
        },
    }));
    obj.querySelector(".two").append(saltos.html("<br/>"));
    obj.querySelector(".two").append(saltos.__form_field.button({
        class:"btn-primary bi-chevron-double-left",
        disabled:field.disabled,
        //tooltip:field.tooltip,
        onclick:function () {
            document.querySelectorAll("#" + field.id + "_xyz option").forEach(function (option) {
                if (option.selected) {
                    document.querySelector("#" + field.id + "_abc").append(option);
                }
            });
            var val = [];
            document.querySelectorAll("#" + field.id + "_xyz option").forEach(function (option) {
                val.push(option.value);
            });
            document.querySelector("#" + field.id).value = val.join(",");
        },
    }));
    obj.querySelector(".three").append(saltos.__form_field.select({
        class:field.class,
        id:field.id + "_xyz",
        disabled:field.disabled,
        tooltip:field.tooltip,
        multiple:true,
        size:field.size,
        rows:rows_xyz,
    }));
    saltos.when_visible(obj ,function () {
        document.querySelectorAll("label[for='" + field.id + "']").forEach(function (_this) {
            _this.setAttribute("for",field.id + "_abc");
        });
    });
    return obj;
};

/*
 * Checkbox constructor helper
 *
 * This function returns a checkbox object, you can pass the follow arguments:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-check
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @label => this parameter is used as label for the checkbox/switch
 * @value => this parameter is used to check or unckeck the checkbox/switch, the value must contain a number that raise as true or false in the if condition
 * @tooltip => this parameter raise the title flag
 * @type => this parameter allow to enable the switch feature
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
    saltos.check_params(field,["value","type","id","disabled","readonly","label","tooltip","class"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.readonly) {
        field.readonly = "readonly";
    }
    if (field.value) {
        field.value = 1;
    } else {
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
    var obj = saltos.html(`
        <div class="form-check ${field.class} ${_class}">
            <input class="form-check-input" type="checkbox" ${_role} id="${field.id}" value="${field.value}"
                ${field.disabled} ${field.readonly} ${checked} data-bs-title="${field.tooltip}">
            <label class="form-check-label" for="${field.id}" data-bs-title="${field.tooltip}">${field.label}</label>
        </div>
    `);
    if (field.tooltip != "") {
        obj.querySelectorAll("input,label").forEach(function (_this) {
            new bootstrap.Tooltip(_this);
        });
    }
    obj.querySelector("input").addEventListener("change",function () {
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
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-select
 * @disabled => this parameter raise the disabled flag
 * @value => value to be used as text in the contents of the buttons
 * @onclick => callback function that is executed when the button is pressed
 * @tooltip => this parameter raise the title flag
 *
 * Notes:
 *
 * You can add an icon before the text by addind the bi-icon class to the class argument
 */
saltos.__form_field.button = function (field) {
    saltos.check_params(field,["class","id","disabled","value","onclick","tooltip"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    var obj = saltos.html(`
        <button type="button" class="btn ${field.class}" id="${field.id}" ${field.disabled} data-bs-title="${field.tooltip}">${field.value}</button>
    `);
    if (field.tooltip != "") {
        new bootstrap.Tooltip(obj);
    }
    if (typeof field.onclick == "function") {
        obj.addEventListener("click",field.onclick);
    }
    return obj;
};

/*
 * Password constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value => the value used as value parameter
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @required => this parameter raise the required flag
 * @tooltip => this parameter raise the title flag
 *
 * Notes:
 *
 * This widget add an icon to the end of the widget with an slashed eye, this allow to
 * see the entered password to verify it, in reality, this button swaps the input between
 * password and text type, allowing to do visible or not the contents of the input
 *
 * This widgets have a problem with the password managers of the browser that convert the
 * previous field of this to a pair of login/password fields and autocomplete they with
 * default values, to fix it, I'm using two tricks:
 *
 * 1) add the input of type=text with the display:none to fix the bug in firefox
 * 2) add the autocomplete="new-password" to fix the problem in chrome browsers
 *
 * The double previousSibling is caused by the new line and tabs between the input and the button
 */
saltos.__form_field.password = function (field) {
    saltos.check_params(field,["class","id","placeholder","value","disabled","readonly","required","tooltip"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.readonly) {
        field.readonly = "readonly";
    }
    if (field.required) {
        field.required = "required";
    }
    var obj = saltos.html(`
        <div class="input-group">
            <input type="text" style="display:none"/>
            <input type="password" class="form-control ${field.class}" id="${field.id}" placeholder="${field.placeholder}" value="${field.value}" autocomplete="new-password"
                ${field.disabled} ${field.readonly} ${field.required} aria-label="${field.placeholder}" aria-describedby="${field.id}_button" data-bs-title="${field.tooltip}">
            <button class="btn btn-outline-secondary bi-eye-slash" type="button" id="${field.id}_button" data-bs-title="${field.tooltip}"></button>
        </div>
    `);
    if (field.tooltip != "") {
        obj.querySelectorAll("input,button").forEach(function (_this) {
            new bootstrap.Tooltip(_this);
        });
    }
    obj.querySelector("button").addEventListener("click",function () {
        var input = this.previousSibling.previousSibling;
        if (input.type == "password") {
            input.type = "text";
            this.classList.remove("bi-eye-slash");
            this.classList.add("bi-eye");
        } else if (input.type == "text") {
            input.type = "password";
            this.classList.remove("bi-eye");
            this.classList.add("bi-eye-slash");
        }
    });
    return obj;
};

/*
 * File constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default form-control
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @multiple => this parameter raise the multiple flag, intended to select more files at time
 * @tooltip => this parameter raise the title flag
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
    saltos.check_params(field,["class","id","value","disabled","required","multiple","tooltip"]);
    if (field.disabled) {
        field.disabled = "disabled";
    }
    if (field.required) {
        field.required = "required";
    }
    if (field.multiple) {
        field.multiple = "multiple";
    }
    var obj = saltos.html(`<div>
        <input type="file" class="form-control ${field.class}" id="${field.id}" ${field.disabled} ${field.required} ${field.multiple} data-bs-title="${field.tooltip}">
        <div class="overflow-auto">
            <table class="table table-striped table-hover d-none">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>`);
    if (field.tooltip != "") {
        obj.querySelectorAll("input").forEach(function (_this) {
            new bootstrap.Tooltip(_this);
        });
    }
    // This helper programs the input file data update
    var __update_data_input_file = function (input) {
        var data = [];
        var tabla = input.nextSibling.nextSibling.querySelector("table");
        tabla.querySelectorAll("tr").forEach(function (_this) {
            data.push(_this.saltos_data);
        });
        input.saltos_data = data;;
    };
    // Program the automatic upload
    obj.querySelector("input").addEventListener("change",async function () {
        var input = this;
        var files = this.files;
        var table = this.nextSibling.nextSibling.querySelector("table");
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
            table.classList.remove("d-none");
            // Add the row for the new file
            var row = saltos.html("tbody",`
                <tr id="${data.files[0].id}">
                    <td class="text-break">${data.files[0].name}</td>
                    <td class="w-25 align-middle">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-label="Example with label" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </td>
                    <td class="p-0 align-middle" style="width:1%"><button class="btn bi-trash" type="button"></button></td>
                </tr>
            `);
            // Store the data in the row
            row.saltos_data = data.files[0];
            // Program de remove button
            row.querySelector("button").addEventListener("click",function () {
                var row = this.parentNode.parentNode;
                var data = {
                    action:"delfiles",
                    files:[],
                };
                data.files[0] = row.saltos_data;
                saltos.ajax({
                    url:"index.php",
                    data:JSON.stringify(data),
                    method:"post",
                    content_type:"application/json",
                    success:function (data,textStatus,XMLHttpRequest) {
                        row.saltos_data = data[0];
                        // If server removes the file, i remove the row
                        if (data[0].file == "") {
                            row.remove();
                        }
                        // If not there are files, hide the table
                        if (table.querySelectorAll("tr").length == 0) {
                            table.classList.add("d-none");
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
            table.querySelector("tbody").append(row);
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
                    saltos.ajax({
                        url:"index.php",
                        data:JSON.stringify(data),
                        method:"post",
                        content_type:"application/json",
                        success:function (data,textStatus,XMLHttpRequest) {
                            row.saltos_data = data[0];
                            __update_data_input_file(input);
                        },
                        error:function (XMLHttpRequest,textStatus,errorThrown) {
                            console.log(XMLHttpRequest.statusText);
                            // TODO
                        },
                        progress:function (e) {
                            if (e.lengthComputable) {
                                var percent = parseInt((e.loaded / e.total) * 100);
                                row.querySelector(".progress-bar").style.width = percent + "%";
                                row.querySelector(".progress-bar").setAttribute("aria-valuenow",percent);
                            }
                        },
                    });
                }(data,row));
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
 * @class => allow to add more classes to the default form-label
 * @label => this parameter is used as text for the label
 * @tooltip => this parameter raise the title flag
 * @value => this parameter is used as label when label is void
 */
saltos.__form_field.label = function (field) {
    saltos.check_params(field,["id","class","label","tooltip","value"]);
    if (field.label == "") {
        field.label = field.value;
    }
    var obj = saltos.html(`
        <label for="${field.id}" class="form-label ${field.class}" data-bs-title="${field.tooltip}">${field.label}</label>
    `);
    if (field.tooltip != "") {
        new bootstrap.Tooltip(obj);
    }
    return obj;
};

/*
 * Image constructor helper
 *
 * This function returns an image object, you can pass some arguments as:
 *
 * @id => the id used to set the reference for to the object
 * @class => allow to add more classes to the default img-fluid
 * @value => the value used as src parameter
 * @alt => this parameter is used as text for the alt parameter
 * @tooltip => this parameter raise the title flag
 */
saltos.__form_field.image = function (field) {
    saltos.check_params(field,["id","class","value","alt","tooltip","width","height"]);
    if (field.class == "") {
        field.class = "img-fluid";
    }
    var obj = saltos.html(`
        <img id="${field.id}" src="${field.value}" class="${field.class}" alt="${field.alt}" data-bs-title="${field.tooltip}" width="${field.width}" height="${field.height}">
    `);
    if (field.tooltip != "") {
        new bootstrap.Tooltip(obj);
    }
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
    saltos.check_params(field,["id","class","data","rowHeaders","colHeaders","minSpareRows","contextMenu","rowHeaderWidth","colWidths"]);
    var obj = saltos.html(`
        <div style="width:100%;height:100%;overflow:auto">
            <div id="${field.id}" class="${field.class}"></div>
        </div>
    `);
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
    var element = obj.querySelector("div");
    saltos.when_visible(element,function () {
        new Handsontable(element,{
            data:field.data,
            rowHeaders:field.rowHeaders,
            colHeaders:field.colHeaders,
            minSpareRows:field.minSpareRows,
            contextMenu:field.contextMenu,
            rowHeaderWidth:field.rowHeaderWidth,
            colWidths:field.colWidths,
            afterChange:function (changes,source) {
                element.saltos_data = field.data;
            }
        });
    });
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
    saltos.check_params(field,["id","class","value"]);
    var obj = saltos.html(`
        <div id="${field.id}" class="${field.class}">
            <div class="pdfViewer"></div>
            <style>
                #${field.id} {
                    position: absolute;
                    width: calc(100% - 18px);
                }
                #${field.id} .canvasWrapper {
                    box-shadow:0 0 4px 4px rgba(0,0,0,0.1)!important;
                }
                #${field.id} *,
                #${field.id} *::before,
                #${field.id} *::after {
                    box-sizing: content-box;
                }
            </style>
        </div>
    `);
    saltos.when_visible(obj ,function () {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "lib/pdfjs/pdf.worker.min.js";
        pdfjsLib.getDocument(field.value).promise.then(function (pdfDocument) {
            if (!pdfDocument.numPages) {
                return;
            }
            var container = obj;
            var eventBus = new pdfjsViewer.EventBus();
            var pdfViewer = new pdfjsViewer.PDFViewer({
                container:container,
                eventBus:eventBus,
            });
            eventBus.on("pagesinit",function () {
                pdfViewer.currentScaleValue = "page-width";
            });
            eventBus.on("annotationlayerrendered",function () {
                container.querySelectorAll("a").forEach(function (_this) {
                    _this.setAttribute("target","_blank");
                });
            });
            pdfViewer.removePageBorders = true;
            pdfViewer.setDocument(pdfDocument);
            container.style.position = "relative";
            window.addEventListener("resize",function () {
                pdfViewer.currentScaleValue = pdfViewer.currentScale * 2;
                pdfViewer.currentScaleValue = "page-width";
            });
        }, function (message,exception) {
            console.log(message);
            // TODO
        });
    });
    return obj;
};

/*
 * Source helper
 *
 * This function is intended to provide multiple sources for a field, they have two modes of work:
 *
 * 1) using the source attribute, you can program an asynchronous ajax request to retrieve the data
 * used to create the field.
 *
 * 2) using the value attribute, you can put a lot of data from the value of a xml node to use in
 * a field as attribute.
 *
 * This function is used in the fields of type table, alert, card and chartjs, the call of this function
 * is private and is intended to be used as a helper from the builders of the previous types opening
 * another way to pass arguments.
 *
 * @id => the id used to set the reference for to the object
 * @type => the type used to set the type for to the object
 * @source => data source used to load asynchronously the contents of the table (header, data, footer and divider)
 * @value => data container used to get synchronously the contents of the table (header, data, footer and divider)
 */
saltos.__source_helper = function(field) {
    saltos.check_params(field,["id","type","source","value"]);
    // Check for asynchronous load using the source param
    if (field.source != "") {
        saltos.ajax({
            url:"index.php?" + field.source,
            success:function (response) {
                if (typeof response.error == "object") {
                    saltos.show_error(response.error);
                    return;
                }
                field.source = "";
                for (var key in response) {
                    field[key] = response[key];
                }
                document.getElementById(field.id).replaceWith(saltos.__form_field[field.type](field));
            },
            //~ headers:{
                //~ "token":saltos.token,
            //~ }
        });
    }
    // Check for syncronous load using the value param
    if (field.value != "") {
        for (var key in field.value) {
            field[key] = field.value[key];
        }
    }
}

/*
 * Table constructor helper
 *
 * Returns a table using the follow params:
 *
 * @id => the id used to set the reference for to the object
 * @class => allow to add more classes to the default table table-striped table-hover
 * @header => array with the header to use
 * @data => 2D array with the data used to mount the body table
 * @footer => array with the footer to use
 * @divider => array with three booleans to specify to add the divider in header, body and/or footer
 */
saltos.__form_field.table = function (field) {
    saltos.check_params(field,["class","id"]);
    saltos.check_params(field,["header","data","footer","divider"],[]);
    saltos.__source_helper(field);
    var obj = saltos.html(`
        <table class="table table-striped table-hover ${field.class}" id="${field.id}">
        </table>
    `);
    if (field.header.length) {
        obj.append(saltos.html("table",`
            <thead>
                <tr>
                </tr>
            </thead>
        `));
        if (typeof field.divider[0] == "boolean" && field.divider[0]) {
            obj.querySelector("thead").classList.add("table-group-divider");
        }
        for (var key in field.header) {
            obj.querySelector("thead tr").append(saltos.html("tr",`<th>${field.header[key]}</th>`));
        }
    }
    if (field.data.length) {
        obj.append(saltos.html("table",`
            <tbody>
            </tbody>
        `));
        if (typeof field.divider[1] == "boolean" && field.divider[1]) {
            obj.querySelector("tbody").classList.add("table-group-divider");
        }
        for (var key in field.data) {
            var row = saltos.html("tbody",`<tr></tr>`);
            for (var key2 in field.data[key]) {
                row.append(saltos.html("tr",`<td>${field.data[key][key2]}</td>`));
            }
            obj.querySelector("tbody").append(row);
        }
    }
    if (field.footer.length) {
        obj.append(saltos.html("table",`
            <tfoot>
                <tr>
                </tr>
            </tfoot>
        `));
        if (typeof field.divider[2] == "boolean" && field.divider[2]) {
            obj.querySelector("tfoot").classList.add("table-group-divider");
        }
        for (var key in field.footer) {
            obj.querySelector("tfoot tr").append(saltos.html("tr",`<td>${field.footer[key]}</td>`));
        }
    }
    return obj;
};

/*
 * Alert constructor helper
 *
 * This component allow to set boxes type alert in the contents, only requires:
 *
 * @id => the id used to set the reference for to the object
 * @class => allow to add more classes to the default alert
 * @title => title used in the body of the card, not used if void
 * @text => text used in the body of the card, not used if void
 * @body => this option allow to specify an specific html to the body of the card, intended to personalize the body's card
 */
saltos.__form_field.alert = function (field) {
    saltos.check_params(field,["class","id","title","text","body"]);
    saltos.__source_helper(field);
    var obj = saltos.html(`
        <div class="alert ${field.class}" role="alert" id="${field.id}"></div>
    `);
    if (field.title != "") {
        obj.append(saltos.html(`<h5>${field.title}</h5>`))
    }
    if (field.text != "") {
        obj.append(saltos.html(`<p>${field.text}</p>`))
    }
    if (field.body != "") {
        obj.append(saltos.html(field.body));
    }
    return obj;
};

/*
 * Card constructor helper
 *
 * This functions creates a card with a lot of options:
 *
 * @id => the id used to set the reference for to the object
 * @image => image used as top image in the card, not used if void
 * @alt => alt text used in the top image if you specify an image
 * @header => text used in the header, not used if void
 * @footer => text used in the footer, not used if void
 * @title => title used in the body of the card, not used if void
 * @text => text used in the body of the card, not used if void
 * @body => this option allow to specify an specific html to the body of the card, intended to personalize the body's card
 */
saltos.__form_field.card = function (field) {
    saltos.check_params(field,["id","image","alt","header","footer","title","text","body"]);
    saltos.__source_helper(field);
    var obj = saltos.html(`<div class="card" id="${field.id}"></div>`);
    if (field.image != "") {
        obj.append(saltos.html(`<img src="${field.image}" class="card-img-top" alt="${field.alt}">`));
    }
    if (field.header != "") {
        obj.append(saltos.html(`<div class="card-header">${field.header}</div>`));
    }
    obj.append(saltos.html(`<div class="card-body"></div>`));
    if (field.title != "") {
        obj.querySelector(".card-body").append(saltos.html(`<h5 class="card-title">${field.title}</h5>`));
    }
    if (field.text != "") {
        obj.querySelector(".card-body").append(saltos.html(`<p class="card-text">${field.text}</p>`));
    }
    if (field.body != "") {
        obj.querySelector(".card-body").append(saltos.html(field.body));
    }
    if (field.footer != "") {
        obj.append(saltos.html(`<div class="card-footer">${field.footer}</div>`));
    }
    return obj;
};

/*
 * Chart.js constructor helper
 *
 * This function creates a chart using the chart.js library, to do this requires de follow arguments:
 *
 * @id => the id used by the object
 * @mode => to specify what kind of plot do you want to do: can be bar, line, doughnut, pie
 * @data => the data used to plot the graph, see the data argument used by the graph.js library
 */
saltos.__form_field.chartjs = function (field) {
    saltos.check_params(field,["id","mode","data"]);
    saltos.__source_helper(field);
    var obj = saltos.html(`<canvas id="${field.id}"></canvas>`);
    saltos.when_visible(obj ,function () {
        new Chart(obj, {
            type: field.mode,
            data: field.data,
        });
    });
    window.addEventListener("resize",function () {
        obj.style.width = "100%";
        obj.style.height = "100%";
    });
    return obj;
};

/*
 * Tags constructor helper
 *
 * This function creates a text input that allow to manage tags, each tag is paint as a badge
 * and each tag can be deleted, the result is stored in a text using a comma separated values
 *
 * @id => the id used by the object
 * @value => comma separated values
 * @datalist => array with options for the datalist, used as autocomplete for the text input
 *
 * Notes:
 *
 * This object creates a hidden input, a text input with/without a datalist, and a badge for
 * each value, and requires the arguments of the specific widgets used in this widget
 */
saltos.__form_field.tags = function (field) {
    saltos.check_params(field,["id","value"]);
    saltos.check_params(field,["datalist"],[]);
    var obj = saltos.html(`<div></div>`);
    field.class = "first";
    obj.append(saltos.__form_field.hidden(field));
    field.id_old = field.id;
    field.id = field.id + "_tags";
    field.value_old = field.value.split(",");
    field.value = "";
    field.class = "last";
    obj.append(saltos.__form_field.text(field));
    var fn = function (val) {
        var span = saltos.html(`<span class="badge text-bg-primary mt-1 me-1 fs-6 fw-normal pe-2" saltos-data="${val}">
            ${val} <i class="bi bi-x-circle ps-1" style="cursor:pointer"></i>
        </span>`);
        obj.append(span);
        span.querySelector("i").addEventListener("click",function () {
            var a = this.parentNode;
            var b = a.getAttribute("saltos-data");
            var input = obj.querySelector("input.first");
            var val_old = input.value.split(",");
            var val_new = [];
            for (var key in val_old) {
                val_old[key] = val_old[key].trim();
                if (val_old[key] != b) {
                    val_new.push(val_old[key]);
                }
            }
            input.value = val_new.join(",");
            a.remove();
        });
    };
    obj.querySelector("input.last").addEventListener("keydown",function (event) {
        if (saltos.get_keycode(event) != 13) {
            return;
        }
        var input_old = obj.querySelector("input.first");
        var input_new = obj.querySelector("input.last");
        var val_old = input_old.value.split(",");
        var val = input_new.value;
        var val_new = [];
        for (var key in val_old) {
            val_old[key] = val_old[key].trim();
            if (val_old[key] == val) {
                return;
            }
            if (val_old[key] != "") {
                val_new.push(val_old[key]);
            }
        }
        fn(val);
        val_new.push(val);
        input_old.value = val_new.join(",");
        input_new.value = "";
    });
    for (var key in field.value_old) {
        var val = field.value_old[key].trim();
        fn(val);
    }
    saltos.when_visible(obj ,function () {
        document.querySelectorAll("label[for='" + field.id_old + "']").forEach(function (_this) {
            _this.setAttribute("for",field.id);
        });
    });
    return obj;
};

/*
 * Menu constructor helper
 *
 * This function creates a menu intended to be used in navbar, nabs and tabs
 *
 * @class => the class used in the main ul element
 * @menu => an array with the follow elements:
 *       @name => name of the menu
 *       @disabled => this boolean allow to disable this menu entry
 *       @active => this boolean marks the option as active
 *       @onclick => the callback used when the user select the menu
 *       @dropdown_menu_end => this trick allow to open the dropdown menu from the end to start
 *       @menu => with this option, you can specify an array with the contents of the dropdown menu
 *             @name => name of the menu
 *             @disabled => this boolean allow to disable this menu entry
 *             @active => this boolean marks the option as active
 *             @onclick => the callback used when the user select the menu
 *             @divider => you can set this boolean to true to convert the element into a divider
 */
saltos.menu = function (args) {
    saltos.check_params(args,["class"]);
    saltos.check_params(args,["menu"],[]);
    var obj = saltos.html(`<ul class="${args.class}"></ul>`);
    for (var key in args.menu) {
        var val = args.menu[key];
        saltos.check_params(val,["name","disabled","active","onclick","dropdown_menu_end"]);
        saltos.check_params(val,["menu"],[]);
        if (val.disabled) {
            val.disabled = "disabled";
        }
        if (val.active) {
            val.active = "active";
        }
        if (val.menu.length) {
            if (val.dropdown_menu_end) {
                val.dropdown_menu_end = "dropdown-menu-end";
            }
            var temp = saltos.html(`
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ${val.name}
                    </a>
                    <ul class="dropdown-menu ${val.dropdown_menu_end}">
                    </ul>
                </li>
            `);
            for (var key2 in val.menu) {
                var val2 = val.menu[key2];
                saltos.check_params(val2,["name","disabled","active","onclick","divider"]);
                if (val2.disabled) {
                    val2.disabled = "disabled";
                }
                if (val2.active) {
                    val2.active = "active";
                }
                if (val2.divider) {
                    var temp2 = saltos.html(`<li><hr class="dropdown-divider"></li>`);
                } else {
                    var temp2 = saltos.html(`<li><a class="dropdown-item ${val2.disabled} ${val2.active}" href="#">${val2.name}</a></li>`);
                    if (!val2.disabled) {
                        temp2.addEventListener("click",val2.onclick);
                    }
                }
                temp.querySelector("ul").append(temp2);
            }
        } else {
            var temp = saltos.html(`
                <li class="nav-item">
                    <a class="nav-link ${val.disabled} ${val.active}" href="#">${val.name}</a>
                </li>
            `);
            if (!val.disabled) {
                temp.addEventListener("click",val.onclick);
            }
        }
        obj.append(temp);
    }
    return obj;
};

/*
 * Navbar constructor helper
 *
 * This component creates a navbar intended to be used as header
 *
 * @id => the id used by the object
 * @brand => contains an object with the name, logo, width and height to be used
 *        @name => text used in the brand
 *        @logo => filename of the brand image
 *        @width => width of the brand image
 *        @height => height of the brand image
 * @items => contains an array with the objects that will be added to the collapse
 */
saltos.navbar = function (args) {
    saltos.check_params(args,["id"]);
    saltos.check_params(args,["brand"],{});
    saltos.check_params(args.brand,["name","logo","width","height"]);
    saltos.check_params(args,["items"],[]);
    var obj = saltos.html(`
        <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="${args.brand.logo}" alt="${args.brand.name}" width="${args.brand.width}" height="${args.brand.height}" class="d-inline-block align-text-top">
                    ${args.brand.name}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#${args.id}" aria-controls="${args.id}" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="${args.id}">
                </div>
            </div>
        </nav>
    `);
    for (var key in args.items) {
        var val = args.items[key];
        obj.querySelector(".collapse").append(val);
    }
    return obj;
};

/*
 * Modal constructor helper object
 *
 * This object is used to store the element and the instance of the modal
 */
saltos.__modal = {};

/*
 * Modal constructor helper
 *
 * This function creates a bootstrap modal and open it, offers two ways of usage:
 *
 * 1) you can pass an string to get a quick action
 *
 * @close => this string close the current modal
 * @isopen => this string is used to check if some modal is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new modal
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default dialog
 * @title => title used by the modal
 * @close => text used in the close button for aria purposes
 * @body => the content used in the modal's body
 * @footer => the content used in the modal's footer
 * @static => forces the modal to be static (prevent close by clicking outside the modal or by pressing the escape key)
 *
 * Returns a boolean that indicates if the modal can be open or not
 *
 * Notes:
 *
 * This modal will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one modal is allowed at each moment.
 */
saltos.modal = function (args) {
    // HELPER ACTIONS
    if (args == "close") {
        return typeof saltos.__modal.instance == "object" && saltos.__modal.instance.hide();
    }
    if (args == "isopen") {
        return typeof saltos.__modal.obj == "object" && saltos.__modal.obj.classList.contains("show");
    }
    // ADDITIONAL CHECK
    if (saltos.modal("isopen")) {
        return false;
    }
    // NORMAL OPERATION
    saltos.check_params(args,["id","class","title","close","body","footer","static"]);
    var temp = "";
    if (args.static) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    if (args.class == "") {
        args.class = "modal-dialog-centered";
    }
    var obj = saltos.html(`
        <div class="modal fade" id="${args.id}" tabindex="-1" aria-labelledby="${args.id}_label" aria-hidden="true" ${temp}>
            <div class="modal-dialog ${args.class}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="${args.id}_label">${args.title}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${args.close}"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
    `);
    document.querySelector("body").append(obj);
    obj.querySelector(".modal-body").append(saltos.html(args.body));
    obj.querySelector(".modal-footer").append(args.footer);
    var instance = new bootstrap.Modal(obj);
    saltos.__modal.obj = obj;
    saltos.__modal.instance = instance;
    obj.addEventListener("shown.bs.modal", event => {
        obj.querySelectorAll(".autofocus").forEach(function (_this) {
            _this.focus();
        });
    });
    obj.addEventListener("hidden.bs.modal", event => {
        instance.dispose();
        obj.remove();
    });
    instance.show();
    return true;
};

/*
 * Offcanvas constructor helper object
 *
 * This object is used to store the element and the instance of the offcanvas
 */
saltos.__offcanvas = {};

/*
 * Offcanvas constructor helper
 *
 * This function creates a bootstrap offcanvas and open it, offers two ways of usage:
 *
 * 1) you can pass an string to get a quick action
 *
 * @close => this string close the current modal
 * @isopen => this string is used to check if some modal is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new modal
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default offcanvas
 * @title => title used by the offcanvas
 * @close => text used in the close button for aria purposes
 * @body => the content used in the offcanvas's body
 * @static => forces the offcanvas to be static (prevent close by clicking outside the offcanvas or by pressing the escape key)
 *
 * Returns a boolean that indicates if the offcanvas can be open or not
 *
 * Notes:
 *
 * This offcanvas will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one offcanvas is allowed at each moment.
 */
saltos.offcanvas = function (args) {
    // HELPER ACTIONS
    if (args == "close") {
        return typeof saltos.__offcanvas.instance == "object" && saltos.__offcanvas.instance.hide();
    }
    if (args == "isopen") {
        return typeof saltos.__offcanvas.obj == "object" && saltos.__offcanvas.obj.classList.contains("show");
    }
    // ADDITIONAL CHECK
    if (saltos.offcanvas("isopen")) {
        return false;
    }
    // NORMAL OPERATION
    saltos.check_params(args,["id","class","title","close","body","static"]);
    var temp = "";
    if (args.static) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    var obj = saltos.html(`
        <div class="offcanvas ${args.class}" tabindex="-1" id="${args.id}" aria-labelledby="${args.id}_label" ${temp}>
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="${args.id}_label">${args.title}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="${args.close}"></button>
            </div>
            <div class="offcanvas-body">
            </div>
        </div>
    `);
    document.querySelector("body").append(obj);
    obj.querySelector(".offcanvas-body").append(saltos.html(args.body));
    var instance = new bootstrap.Offcanvas(obj);
    saltos.__offcanvas.obj = obj;
    saltos.__offcanvas.instance = instance;
    obj.addEventListener("shown.bs.offcanvas", event => {
        obj.querySelectorAll(".autofocus").forEach(function (_this) {
            _this.focus();
        });
    });
    obj.addEventListener("hidden.bs.offcanvas", event => {
        instance.dispose();
        obj.remove();
    });
    instance.show();
    return true;
};

/*
 * Toast constructor helper
 *
 * This function creates a bootstrap toast and show it, and can accept the follow params:
 *
 * @id => the id used by the object
 * @class => allow to add more classes to the default toast
 * @title => title used by the toast
 * @subtitle => small text used by the toast
 * @close => text used in the close button for aria purposes
 * @body => the content used in the toast's body
 *
 * Returns a boolean that indicates if the toast can be created (see the hash note)
 *
 * Notes:
 *
 * The toast will be destroyed (instance and element) when it closes.
 *
 * All toasts are added to a toast-container placed in the body of the document, this container
 * is created automatically if it not exists when the first toast need it.
 *
 * Each toast includes a hash to prevent the creation of repeated toasts.
 */
saltos.toast = function (args) {
    saltos.check_params(args,["id","class","close","title","subtitle","body"]);
    if (document.querySelectorAll(".toast-container").length == 0) {
        document.querySelector("body").append(saltos.html(`<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>`));
    }
    // CHECK FOR REPETITIONS
    var hash = md5(JSON.stringify(args));
    if (document.querySelector(`.toast[hash=${hash}]`)) {
        return false;
    }
    // CONTINUE
    var obj = saltos.html(`
        <div id="${args.id}" class="toast ${args.class}" role="alert" aria-live="assertive" aria-atomic="true" hash="${hash}">
            <div class="toast-header">
                <strong class="me-auto">${args.title}</strong>
                <small>${args.subtitle}</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="${args.close}"></button>
            </div>
            <div class="toast-body">
            </div>
        </div>
    `);
    document.querySelector(".toast-container").append(obj);
    obj.querySelector(".toast-body").append(args.body);
    var toast = new bootstrap.Toast(obj);
    obj.addEventListener("hidden.bs.toast", event => {
        toast.dispose();
        obj.remove();
    });
    toast.show();
    return true;
};
