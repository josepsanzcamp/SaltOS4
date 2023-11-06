
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

'use strict';

/**
 * Bootstrap helper module
 *
 * This fie contains useful functions related to the bootstrap widgets, allow to create widgets and
 * other plugins suck as plots or rich editors
 */

/**
 * Form fields constructor
 *
 * This function and their helpers, allow the creation of the interface using the bootstrap
 * widgets, the types that can be called are the follow:
 *
 * @div         => id, class, style
 * @container   => id, class, style
 * @row         => id, class, style
 * @col         => id, class, style
 * @text        => id, class, PL, value, DS, RO, RQ, AF, datalist, tooltip, label
 * @hidden      => id, class, PL, value, DS, RO, RQ, AF, tooltip
 * @integer     => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @float       => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @color       => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @date        => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @time        => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @datetime    => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @textarea    => id, class, PL, value, DS, RO, RQ, AF, rows, tooltip, label
 * @ckeditor    => id, class, PL, value, DS, RO, RQ, AF, rows, label
 * @codemirror  => id, class, PL, value, DS, RO, RQ, AF, rows, mode, label
 * @iframe      => id, class, value, height, tooltip, label
 * @select      => id, class, DS, RQ, AF, rows, multiple, size, value, tooltip, label
 * @multiselect => id, class, DS, RQ, AF, rows, multiple, size, value, multiple, tooltip, label
 * @checkbox    => id, class, DS, RO, label, value, tooltip
 * @switch      => id, class, DS, RO, label, value, tooltip
 * @button      => id, class, DS, value, onclick, tooltip
 * @password    => id, class, PL, value, DS, RO, RQ, AF, tooltip, label
 * @file        => id, class, DS, RQ, AF, multiple, tooltip, label
 * @link        => id, DS, value, onclick, tooltip, label
 * @label       => id, class, label, tooltip, value
 * @image       => id, class, value, alt, tooltip, label
 * @excel       => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth,
 *                 colWidths, label
 * @pdfjs       => id, class, value, label
 * @table       => id, class, header, data, footer, value, label
 * @alert       => id, class, title, text, body, value, label
 * @card        => id, image, alt, header, footer, title, text, body, value, label
 * @chartjs     => id, mode, data, value, label
 * @tags        => id, class, PL, value, DS, RO, RQ, AF, datalist, tooltip, label
 * @gallery     => id, class, label, images
 * @PL => id
 *
 * Notes:
 *
 * To do more small the previous list, we have used the follow abreviations:
 *
 * @PL => placeholder
 * @DS => disabled
 * @RO => readonly
 * @RQ => required
 * @AF => autofocus
 *
 * The saltos.__form_field object is part of this constructor and act with the constructor
 * as a helper, the idea is that the user must to call the constructor and the helpers are
 * only for internal use.
 */
saltos.form_field = field => {
    saltos.check_params(field, ['id', 'type']);
    if (field.id == '') {
        field.id = saltos.uniqid();
    }
    if (typeof saltos.__form_field[field.type] != 'function') {
        console.log('type ' + field.type + ' not found');
        return saltos.html('type ' + field.type + ' not found');
    }
    return saltos.__form_field[field.type](field);
};

/**
 * Form_field constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.__form_field = {};

/**
 * Div constructor helper
 *
 * This function returns an object of the type class by default, you can pass the class
 * argument in the field object to specify what kind of class do you want to use.
 *
 * @id    => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.div = field => {
    saltos.check_params(field, ['class', 'id', 'style']);
    var obj = saltos.html(`<div class="${field.class}" id="${field.id}" style="${field.style}"></div>`);
    return obj;
};

/**
 * Container constructor helper
 *
 * This function returns an object of the container-fluid class by default, you can pass the class
 * argument in the field object to specify what kind of container do you want to do.
 *
 * @id    => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.container = field => {
    saltos.check_params(field, ['class']);
    if (field.class == '') {
        field.class = 'container-fluid';
    }
    var obj = saltos.__form_field.div(field);
    return obj;
};

/**
 * Row constructor helper
 *
 * This function returns an object of the row class by default, you can pass the class argument
 * in the field object to specify what kind of row do you want to do.
 *
 * @id    => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.row = field => {
    saltos.check_params(field, ['class']);
    if (field.class == '') {
        field.class = 'row';
    }
    var obj = saltos.__form_field.div(field);
    return obj;
};

/**
 * Col constructor helper
 *
 * This function returns an object of the col class by default, you can pass the class argument
 * in the field object to specify what kind of col do you want to do.
 *
 * @id    => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.__form_field.col = field => {
    saltos.check_params(field, ['class']);
    if (field.class == '') {
        field.class = 'col';
    }
    var obj = saltos.__form_field.div(field);
    return obj;
};

/**
 * Text constructor helper
 *
 * This function returns an input object of type text, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 * @datalist    => array with options for the datalist, used as autocomplete for the text input
 */
saltos.__form_field.text = field => {
    saltos.check_params(field, ['datalist'], []);
    field.type = 'text';
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__label_helper(field));
    obj.append(saltos.__text_helper(field));
    if (field.datalist.length) {
        obj.querySelector('input').setAttribute('list', field.id + '_datalist');
        obj.append(saltos.html(`<datalist id="${field.id}_datalist"></datalist>`));
        for (var key in field.datalist) {
            var val = field.datalist[key];
            obj.querySelector('datalist').append(saltos.html(`<option value="${val}">`));
        }
    }
    obj = saltos.optimize(obj);
    return obj;
};

/**
 * Hidden constructor helper
 *
 * This function returns an input object of type hidden, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 *
 * Notes:
 *
 * This function allow the previous parameters but for hidden inputs, only id
 * and value are usually used, in some cases can be interesting to use the
 * class to identify a group of hidden input
 */
saltos.__form_field.hidden = field => {
    field.type = 'hidden';
    var obj = saltos.__text_helper(field);
    return obj;
};

/**
 * Integer constructor helper
 *
 * This function returns an input object of type integer, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the imask library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/imaskjs/imask.min.js
 */
saltos.__form_field.integer = field => {
    saltos.require('core/lib/imaskjs/imask.min.js');
    field.type = 'text';
    var obj = saltos.__text_helper(field);
    var element = obj;
    IMask(element, {
        mask: Number,
        scale: 0,
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Float constructor helper
 *
 * This function returns an input object of type float, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the imask library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/imaskjs/imask.min.js
 */
saltos.__form_field.float = field => {
    saltos.require('core/lib/imaskjs/imask.min.js');
    field.type = 'text';
    var obj = saltos.__text_helper(field);
    var element = obj;
    IMask(element, {
        mask: Number,
        radix: '.',
        mapToRadix: [','],
        scale: 99,
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Color constructor helper
 *
 * This function returns an input object of type color, you can pass the same
 * arguments that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * Ths color input launch a warning if value is not in the format #rrggbb,
 * for this reason it is set to #000000 if value is void
 */
saltos.__form_field.color = field => {
    saltos.check_params(field, ['value']);
    if (field.value == '') {
        field.value = '#000000';
    }
    field.type = 'color';
    field.class = 'form-control-color';
    var obj = saltos.__text_helper(field);
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Date constructor helper
 *
 * This function returns an input object of type date, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 */
saltos.__form_field.date = field => {
    field.type = 'date';
    var obj = saltos.__text_helper(field);
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Time constructor helper
 *
 * This function returns an input object of type time, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 */
saltos.__form_field.time = field => {
    field.type = 'time';
    var obj = saltos.__text_helper(field);
    obj.step = 1; // this enable the seconds
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Datetime constructor helper
 *
 * This function returns an input object of type datetime, you can pass the same arguments
 * that for the input object of type text
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 */
saltos.__form_field.datetime = field => {
    field.type = 'datetime-local';
    var obj = saltos.__text_helper(field);
    obj.step = 1; // this enable the seconds
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Textarea constructor helper
 *
 * This function returns a textarea object with the autogrow plugin enabled
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @rows        => the number used as rows parameter
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the autoheight library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/autoheight/autoheight.min.js
 */
saltos.__form_field.textarea = field => {
    saltos.require('core/lib/autoheight/autoheight.min.js');
    var obj = saltos.__textarea_helper(field);
    var element = obj;
    saltos.when_visible(element, () => {
        autoheight(element);
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Ckeditor constructor helper
 *
 * This function returns a textarea object with the ckeditor plugin enabled
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @rows        => the number used as rows parameter
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the ckeditor library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/ckeditor/ckeditor.min.js
 */
saltos.__form_field.ckeditor = field => {
    saltos.require('core/lib/ckeditor/ckeditor.min.js');
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__label_helper(field));
    obj.append(saltos.__textarea_helper(field));
    var element = obj.querySelector('textarea');
    saltos.when_visible(element, () => {
        ClassicEditor.create(element).catch(error => {
            console.error(error);
        });
    });
    return obj;
};

/**
 * Codemirror constructor helper
 *
 * This function returns a textarea object with the codemirror plugin enabled
 *
 * @mode        => used to define the mode parameter of the codemirror
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @rows        => the number used as rows parameter
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the codemirror library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/codemirror/codemirror.min.css
 * @core/lib/codemirror/codemirror.min.js
 */
saltos.__form_field.codemirror = field => {
    saltos.require('core/lib/codemirror/codemirror.min.css');
    saltos.require('core/lib/codemirror/codemirror.min.js');
    saltos.check_params(field, ['mode']);
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__label_helper(field));
    obj.append(saltos.__textarea_helper(field));
    var element = obj.querySelector('textarea');
    saltos.when_visible(element, () => {
        var cm = CodeMirror.fromTextArea(element, {
            mode: field.mode,
            styleActiveLine: true,
            lineNumbers: true,
            lineWrapping: true,
        });
        element.nextElementSibling.classList.add('form-control');
        element.nextElementSibling.classList.add('p-0');
        element.nextElementSibling.style.height = 'auto';
        cm.on('change', cm.save);
    });
    return obj;
};

/**
 * Iframe constructor helper
 *
 * This function returns an iframe object, you can pass the follow arguments:
 *
 * @id     => the id used by the object
 * @value  => the value used as src parameter
 * @class  => allow to add more classes to the default form-control
 * @height => the height used as height for the style parameter
 * @label  => this parameter is used as text for the label
 */
saltos.__form_field.iframe = field => {
    saltos.check_params(field, ['value', 'id', 'class', 'height']);
    var obj = saltos.html(`
        <iframe src="${field.value}" id="${field.id}" frameborder="0"
            class="form-control p-0 ${field.class}" style="height: ${field.height}"></iframe>
    `);
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Select constructor helper
 *
 * This function returns a select object, you can pass the follow arguments:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default form-select
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @multiple => this parameter enables the multiple selection feature of the select
 * @size     => this parameter allow to see the options list opened with n (size) entries
 * @value    => the value used to detect the selected option
 * @tooltip  => this parameter raise the title flag
 * @rows     => this parameter contains the list of options, each option must be an object
 *              with label and value entries
 * @label    => this parameter is used as text for the label
 */
saltos.__form_field.select = field => {
    saltos.check_params(field, ['class', 'id', 'disabled', 'required', 'autofocus',
                                'multiple', 'size', 'value', 'tooltip']);
    saltos.check_params(field, ['rows'], []);
    if (field.disabled) {
        field.disabled = 'disabled';
    }
    if (field.required) {
        field.required = 'required';
    }
    if (field.autofocus) {
        field.autofocus = 'autofocus';
    }
    if (field.multiple) {
        field.multiple = 'multiple';
    }
    if (field.size != '') {
        field.size = `size="${field.size}"`;
    }
    var obj = saltos.html(`
        <select class="form-select ${field.class}" id="${field.id}" ${field.disabled} ${field.required}
            ${field.autofocus} ${field.multiple} ${field.size} data-bs-title="${field.tooltip}"></select>
    `);
    var element = obj;
    if (field.tooltip != '') {
        saltos.__tooltip_helper(element);
    }
    for (var key in field.rows) {
        var val = field.rows[key];
        var selected = '';
        if (field.value.toString() == val.value.toString()) {
            selected = 'selected';
        }
        element.append(saltos.html(`<option value="${val.value}" ${selected}>${val.label}</option>`));
    }
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Multiselect constructor helper
 *
 * This function returns a multiselect object, you can pass the follow arguments:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default form-select
 * @disabled => this parameter raise the disabled flag
 * @size     => this parameter allow to see the options list opened with n (size) entries
 * @value    => the value used as src parameter
 * @tooltip  => this parameter raise the title flag
 * @rows     => this parameter contains the list of options, each option must be an object
 *              with label and value entries
 * @label    => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget is created joinin 2 selects and 2 buttons, the user must get the value
 * using the hidden input that is builded using the original id passed by argument.
 *
 * TODO: detected a bug with this widget in chrome in mobile browsers
 */
saltos.__form_field.multiselect = field => {
    saltos.check_params(field, ['value', 'class', 'id', 'disabled', 'size', 'tooltip']);
    saltos.check_params(field, ['rows'], []);
    if (field.disabled) {
        field.disabled = 'disabled';
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
    var values = field.value.split(',');
    for (var key in field.rows) {
        var val = field.rows[key];
        if (values.includes(val.value.toString())) {
            rows_xyz.push(val);
        } else {
            rows_abc.push(val);
        }
    }
    obj.querySelector('.one').append(saltos.__form_field.hidden(field));
    obj.querySelector('.one').append(saltos.__form_field.select({
        class: field.class,
        id: field.id + '_abc',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rows: rows_abc,
    }));
    obj.querySelector('.two').append(saltos.__form_field.button({
        class: 'btn-primary bi-chevron-double-right mb-3',
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            document.querySelectorAll('#' + field.id + '_abc option').forEach(option => {
                if (option.selected) {
                    document.getElementById(field.id + '_xyz').append(option);
                }
            });
            var val = [];
            document.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            document.getElementById(field.id).value = val.join(',');
        },
    }));
    obj.querySelector('.two').append(saltos.html('<br/>'));
    obj.querySelector('.two').append(saltos.__form_field.button({
        class: 'btn-primary bi-chevron-double-left',
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            document.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                if (option.selected) {
                    document.getElementById(field.id + '_abc').append(option);
                }
            });
            var val = [];
            document.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            document.getElementById(field.id).value = val.join(',');
        },
    }));
    obj.querySelector('.three').append(saltos.__form_field.select({
        class: field.class,
        id: field.id + '_xyz',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rows: rows_xyz,
    }));
    saltos.when_visible(obj, () => {
        document.querySelectorAll('label[for=' + field.id + ']').forEach(_this => {
            _this.setAttribute('for', field.id + '_abc');
        });
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Checkbox constructor helper
 *
 * This function returns a checkbox object, you can pass the follow arguments:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default form-check
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @label    => this parameter is used as label for the checkbox
 * @value    => this parameter is used to check or unckeck the checkbox, the value
 *              must contain a number that raise as true or false in the if condition
 * @tooltip  => this parameter raise the title flag
 *
 * Notes:
 *
 * This widget returns their value by setting a zero or one (0/1) value on the value of the input.
 */
saltos.__form_field.checkbox = field => {
    saltos.check_params(field, ['value', 'id', 'disabled', 'readonly', 'label', 'tooltip', 'class']);
    if (field.disabled) {
        field.disabled = 'disabled';
    }
    if (field.readonly) {
        field.readonly = 'readonly';
    }
    if (field.value) {
        field.value = 1;
    } else {
        field.value = 0;
    }
    var checked = '';
    if (field.value) {
        checked = 'checked';
    }
    var obj = saltos.html(`
        <div class="form-check ${field.class}">
            <input class="form-check-input" type="checkbox" id="${field.id}" value="${field.value}"
                ${field.disabled} ${field.readonly} ${checked} data-bs-title="${field.tooltip}">
            <label class="form-check-label" for="${field.id}"
                data-bs-title="${field.tooltip}">${field.label}</label>
        </div>
    `);
    if (field.tooltip != '') {
        obj.querySelectorAll('input, label').forEach(_this => {
            saltos.__tooltip_helper(_this);
        });
    }
    obj.querySelector('input').addEventListener('change', event => {
        event.target.value = event.target.checked ? 1 : 0;
    });
    return obj;
};

/**
 * Switch constructor helper
 *
 * This function returns a switch object, you can pass the follow arguments:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default form-check and form-switch
 * @disabled => this parameter raise the disabled flag
 * @readonly => this parameter raise the readonly flag
 * @label    => this parameter is used as label for the switch
 * @value    => this parameter is used to check or unckeck the switch, the value
 *              must contain a number that raise as true or false in the if condition
 * @tooltip  => this parameter raise the title flag
 *
 * Notes:
 *
 * This widget uses the checkbox constructor
 */
saltos.__form_field.switch = field => {
    var obj = saltos.__form_field.checkbox(field);
    obj.classList.add('form-switch');
    obj.querySelector('input').setAttribute('role', 'switch');
    return obj;
};

/**
 * Button constructor helper
 *
 * This function returns a button object, you can pass the follow arguments:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default form-select
 * @disabled => this parameter raise the disabled flag
 * @value    => value to be used as text in the contents of the buttons
 * @onclick  => callback function that is executed when the button is pressed
 * @tooltip  => this parameter raise the title flag
 *
 * Notes:
 *
 * You can add an icon before the text by addind the bi-icon class to the class argument
 */
saltos.__form_field.button = field => {
    saltos.check_params(field, ['class', 'id', 'disabled', 'value', 'onclick', 'tooltip', 'icon']);
    if (field.disabled) {
        field.disabled = 'disabled';
        field.class += ' opacity-25';
    }
    var obj = saltos.html(`
        <button type="button" class="btn ${field.class}" id="${field.id}"
            ${field.disabled} data-bs-title="${field.tooltip}">${field.value}</button>
    `);
    if (field.icon) {
        obj.prepend(saltos.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.value && field.icon) {
        obj.querySelector('i').classList.add('me-1');
    }
    if (field.tooltip != '') {
        saltos.__tooltip_helper(obj);
    }
    if (typeof field.onclick == 'string') {
        obj.addEventListener('click', new Function(field.onclick));
    }
    if (typeof field.onclick == 'function') {
        obj.addEventListener('click', field.onclick);
    }
    return obj;
};

/**
 * Password constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @label       => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget add an icon to the end of the widget with an slashed eye, this allow to
 * see the entered password to verify it, in reality, this button swaps the input between
 * password and text type, allowing to do visible or not the contents of the input
 *
 * I'm using previousElementSibling instead of previousSibling because between the input and the
 * button, exists a new line that is identified as another previousSibling, but not as an element
 *
 */
saltos.__form_field.password = field => {
    saltos.check_params(field, ['label', 'class', 'id', 'placeholder', 'value', 'disabled',
                                'readonly', 'required', 'autofocus', 'tooltip']);
    if (field.disabled) {
        field.disabled = 'disabled';
    }
    if (field.readonly) {
        field.readonly = 'readonly';
    }
    if (field.required) {
        field.required = 'required';
    }
    if (field.autofocus) {
        field.autofocus = 'autofocus';
    }
    var obj = saltos.html(`
        <div>
            <div class="input-group">
                <input type="password" class="form-control ${field.class}" id="${field.id}"
                    placeholder="${field.placeholder}" value="${field.value}"
                    ${field.disabled} ${field.readonly} ${field.required} ${field.autofocus}
                    aria-label="${field.placeholder}" aria-describedby="${field.id}_button"
                    data-bs-title="${field.tooltip}">
                <button class="btn btn-primary bi-eye-slash" type="button" id="${field.id}_button"
                data-bs-title="${field.tooltip}"></button>
            </div>
        </div>
    `);
    if (field.tooltip != '') {
        obj.querySelectorAll('input[type=password]').forEach(_this => {
            saltos.__tooltip_helper(_this);
        });
    }
    obj.querySelector('button').addEventListener('click', event => {
        var input = event.target.parentElement.querySelector('input[type=password], input[type=text]');
        if (input.type == 'password') {
            input.type = 'text';
            event.target.classList.remove('bi-eye-slash');
            event.target.classList.add('bi-eye');
        } else if (input.type == 'text') {
            input.type = 'password';
            event.target.classList.remove('bi-eye');
            event.target.classList.add('bi-eye-slash');
        }
    });
    obj.prepend(saltos.__label_helper(field));
    return obj;
};

/**
 * File constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default form-control
 * @disabled => this parameter raise the disabled flag
 * @required => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @multiple => this parameter raise the multiple flag, intended to select more files at time
 * @tooltip  => this parameter raise the title flag
 * @label    => this parameter is used as text for the label
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
saltos.__form_field.file = field => {
    saltos.check_params(field, ['class', 'id', 'value', 'disabled', 'required',
                                'autofocus', 'multiple', 'tooltip']);
    if (field.disabled) {
        field.disabled = 'disabled';
    }
    if (field.required) {
        field.required = 'required';
    }
    if (field.autofocus) {
        field.autofocus = 'autofocus';
    }
    if (field.multiple) {
        field.multiple = 'multiple';
    }
    var obj = saltos.html(`
        <div>
            <input type="file" class="form-control ${field.class}" id="${field.id}" ${field.disabled}
                ${field.required} ${field.autofocus} ${field.multiple} data-bs-title="${field.tooltip}">
            <div class="overflow-auto">
                <table class="table table-striped table-hover d-none">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    obj.append(saltos.html(`
        <style>
            .table {
                --bs-table-hover-bg: #fbec88;
                --bs-table-active-bg: #fbec88;
                --bs-table-hover-color: #373a3c;
                --bs-table-active-color: #373a3c;
            }
        </style>
    `));
    if (field.tooltip != '') {
        obj.querySelectorAll('input').forEach(_this => {
            saltos.__tooltip_helper(_this);
        });
    }
    // This helper programs the input file data update
    var __update_data_input_file = input => {
        var data = [];
        var tabla = input.nextElementSibling.querySelector('table');
        tabla.querySelectorAll('tr').forEach(_this => {
            data.push(_this.saltos_data);
        });
        input.saltos_data = data;;
    };
    // This helper programs the delete file button
    var __button_remove_file = event => {
        var row = event.target.parentNode.parentNode;
        var table = row.parentNode.parentNode;
        var input = table.parentNode.previousElementSibling;
        var data = {
            action: 'delfiles',
            files: [],
        };
        data.files[0] = row.saltos_data;
        saltos.ajax({
            url: 'index.php',
            data: JSON.stringify(data),
            method: 'post',
            content_type: 'application/json',
            success: response => {
                if (!saltos.check_response(response)) {
                    return;
                }
                row.saltos_data = response[0];
                // If server removes the file, i remove the row
                if (response[0].file == '') {
                    row.remove();
                }
                // If not there are files, hide the table
                if (table.querySelectorAll('tr').length == 0) {
                    table.classList.add('d-none');
                }
                __update_data_input_file(input);
            },
            error: request => {
                saltos.show_error({
                    text: request.statusText,
                    code: request.status,
                });
            },
            headers: {
                'token': saltos.token.get_token(),
            }
        });
    };
    // Program the automatic upload
    obj.querySelector('input').addEventListener('change', async event => {
        var input = event.target;
        var files = event.target.files;
        var table = event.target.nextElementSibling.querySelector('table');
        for (var i = 0; i < files.length; i++) {
            // Prepare the data to send
            var data = {
                action: 'addfiles',
                files: [],
            };
            data.files[0] = {
                id: saltos.uniqid(),
                name: files[i].name,
                size: files[i].size,
                type: files[i].type,
                data: '',
                error: '',
                file: '',
                hash: '',
            };
            // Show the table
            table.classList.remove('d-none');
            // Add the row for the new file
            var row = saltos.html('tbody', `
                <tr id="${data.files[0].id}">
                    <td class="text-break">${data.files[0].name}</td>
                    <td class="w-25 align-middle">
                        <div class="progress" role="progressbar" aria-label="Upload percent"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </td>
                    <td class="p-0 align-middle" style="width: 1%"><button class="btn bi-trash border-0"
                        type="button"></button></td>
                </tr>
            `);
            // Store the data in the row
            row.saltos_data = data.files[0];
            // Program de remove button
            row.querySelector('button').addEventListener('click', __button_remove_file);
            // Add the row
            table.querySelector('tbody').append(row);
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
                ((data, row) => {
                    saltos.ajax({
                        url: 'index.php',
                        data: JSON.stringify(data),
                        method: 'post',
                        content_type: 'application/json',
                        success: response => {
                            if (!saltos.check_response(response)) {
                                return;
                            }
                            row.saltos_data = response[0];
                            __update_data_input_file(input);
                        },
                        error: request => {
                            saltos.show_error({
                                text: request.statusText,
                                code: request.status,
                            });
                        },
                        progress: event => {
                            if (event.lengthComputable) {
                                var percent = Math.round((event.loaded / event.total) * 100);
                                row.querySelector('.progress-bar').style.width = percent + '%';
                                row.querySelector('.progress').setAttribute('aria-valuenow', percent);
                            }
                        },
                        headers: {
                            'token': saltos.token.get_token(),
                        }
                    });
                })(data, row);
            }
            // If there is an error
            if (reader.error) {
                data.files[0].error = reader.error.message;
                saltos.show_error({
                    text: reader.error.message,
                    code: 0,
                });
            }
        }
    });
    obj.prepend(saltos.__label_helper(field));
    return obj;
};

/**
 * Link constructor helper
 *
 * This function creates a field similar of text but with the appearance of a link using a button,
 * the object can receive the follow arguments:
 *
 * @label    => this parameter is used as text for the label
 * @id       => the id used by the object
 * @disabled => this parameter raise the disabled flag
 * @value    => the value is conveted as label to be used in the button with the appearance of a link
 * @onclick  => callback function that is executed when the button is pressed
 *
 * Notes:
 *
 * This object is not a real link, it's a button that uses the btn-link class to get the link
 * appearance
 */
saltos.__form_field.link = field => {
    saltos.check_params(field, ['label']);
    field.class = 'btn-link';
    if (field.label == '') {
        return saltos.__form_field.button(field);
    }
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__label_helper(field));
    obj.append(saltos.html('<br/>'));
    obj.append(saltos.__form_field.button(field));
    return obj;
};

/**
 * Label constructor helper
 *
 * This function returns a label object, you can pass some arguments as:
 *
 * @id      => the id used to set the reference for to the object
 * @class   => allow to add more classes to the default form-label
 * @label   => this parameter is used as text for the label
 * @tooltip => this parameter raise the title flag
 * @value   => this parameter is used as label when label is void
 */
saltos.__form_field.label = field => {
    saltos.check_params(field, ['id', 'class', 'label', 'tooltip', 'value']);
    if (field.label == '') {
        field.label = field.value;
    }
    var obj = saltos.html(`
        <label for="${field.id}" class="form-label ${field.class}"
            data-bs-title="${field.tooltip}">${field.label}</label>
    `);
    if (field.tooltip != '') {
        saltos.__tooltip_helper(obj);
    }
    return obj;
};

/**
 * Image constructor helper
 *
 * This function returns an image object, you can pass some arguments as:
 *
 * @id      => the id used to set the reference for to the object
 * @class   => allow to add more classes to the default img-fluid
 * @value   => the value used as src parameter
 * @alt     => this parameter is used as text for the alt parameter
 * @tooltip => this parameter raise the title flag
 * @label   => this parameter is used as text for the label
 */
saltos.__form_field.image = field => {
    saltos.check_params(field, ['id', 'class', 'value', 'alt', 'tooltip', 'width', 'height']);
    if (field.class == '') {
        field.class = 'img-fluid';
    }
    var obj = saltos.html(`
        <img id="${field.id}" src="${field.value}" class="${field.class}" alt="${field.alt}"
            data-bs-title="${field.tooltip}" width="${field.width}" height="${field.height}">
    `);
    if (field.tooltip != '') {
        saltos.__tooltip_helper(obj);
    }
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Excel constructor helper
 *
 * This function creates and returns an excel object, to do this they use the handsontable library,
 * currently this library uses a privative license, by this reason, we are using the version 6.2.2
 * that is the latest release published using the MIT license.
 *
 * This widget can receive the following arguments:
 *
 * @id             => the id used to set the reference for to the object
 * @class          => allow to set the class to the div object used to allocate the widget
 * @data           => this parameter must contain a 2D matrix with the data that you want to show
 *                    in the sheet
 * @rowHeaders     => can be an array with the headers that you want to use instead the def numbers
 * @colHeaders     => can be an array with the headers that you want to use instead the def letters
 * @minSpareRows   => can be a number with the void rows at the end of the sheet
 * @contextMenu    => can be a boolean with the desired value to allow or not the provided
 *                    context menu of the widget
 * @rowHeaderWidth => can be a number with the width of the headers rows
 * @colWidths      => can be an array with the widths of the headers cols
 * @label          => this parameter is used as text for the label
 *
 * Notes:
 *
 * You can get the values after to do changes by accessing to the data of the div used to create
 * the widget.
 *
 * This widget requires the handsontable library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/handsontable/handsontable.full.min.css
 * @core/lib/handsontable/handsontable.full.min.js
 */
saltos.__form_field.excel = field => {
    saltos.require('core/lib/handsontable/handsontable.full.min.css');
    saltos.require('core/lib/handsontable/handsontable.full.min.js');
    saltos.check_params(field, ['id', 'class', 'data', 'rowHeaders', 'colHeaders', 'minSpareRows',
                                'contextMenu', 'rowHeaderWidth', 'colWidths']);
    var obj = saltos.html(`
        <div style="width: 100%; height: 100%; overflow: auto">
            <div id="${field.id}" class="${field.class}"></div>
        </div>
    `);
    if (field.data == '') {
        field.data = [...Array(20)].map(e => Array(26));
    }
    if (field.rowHeaders == '') {
        field.rowHeaders = true;
    }
    if (field.colHeaders == '') {
        field.colHeaders = true;
    }
    if (field.minSpareRows == '') {
        field.minSpareRows = 0;
    }
    if (field.contextMenu == '') {
        field.contextMenu = true;
    }
    if (field.rowHeaderWidth == '') {
        field.rowHeaderWidth = undefined;
    }
    if (field.colWidths == '') {
        field.colWidths = undefined;
    }
    var element = obj.querySelector('div');
    saltos.when_visible(element, () => {
        new Handsontable(element, {
            data: field.data,
            rowHeaders: field.rowHeaders,
            colHeaders: field.colHeaders,
            minSpareRows: field.minSpareRows,
            contextMenu: field.contextMenu,
            rowHeaderWidth: field.rowHeaderWidth,
            colWidths: field.colWidths,
            afterChange: (changes, source) => {
                element.saltos_data = field.data;
            }
        });
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Pdfjs constructor helper
 *
 * This function creates and returns a pdfviewer object, to do this they use the pdf.js library.
 *
 * @id    => the id used to set the reference for to the object
 * @class => allow to set the class to the div object used to allocate the widget
 * @value => the file or data that contains the pdf document
 * @label => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the pdfjs library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/pdfjs/pdf_viewer.min.css,
 * @core/lib/pdfjs/pdf.min.mjs
 * @core/lib/pdfjs/pdf_viewer.min.mjs
 * @core/lib/pdfjs/pdf.worker.min.mjs
 *
 * The last file (the worker) is loaded by the library and not by SaltOS, is for this reason
 * that this file not appear in the next requires
 */
saltos.__form_field.pdfjs = field => {
    saltos.require('core/lib/pdfjs/pdf_viewer.min.css');
    saltos.require('core/lib/pdfjs/pdf.min.mjs');
    saltos.require('core/lib/pdfjs/pdf_viewer.min.mjs');
    saltos.check_params(field, ['id', 'class', 'value']);
    var obj = saltos.html(`
        <div id="${field.id}" class="${field.class}">
            <div class="viewerContainer">
                <div class="pdfViewer"></div>
            </div>
        </div>
    `);
    obj.append(saltos.html(`
        <style>
            .viewerContainer {
                position: absolute;
                width: 100%;
                left: -9px;
                top: -9px;
            }
            .viewerContainer .canvasWrapper {
                box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1)!important;
            }
            .viewerContainer *,
            .viewerContainer *::before,
            .viewerContainer *::after {
                box-sizing: content-box;
            }
        </style>
    `));
    var element = obj.querySelector('.viewerContainer');
    saltos.when_visible(element, () => {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'core/lib/pdfjs/pdf.worker.min.mjs';
        pdfjsLib.getDocument(field.value).promise.then(pdfDocument => {
            if (!pdfDocument.numPages) {
                return;
            }
            var container = element;
            var eventBus = new pdfjsViewer.EventBus();
            var pdfViewer = new pdfjsViewer.PDFViewer({
                container: container,
                eventBus: eventBus,
            });
            eventBus.on('pagesinit', () => {
                pdfViewer.currentScaleValue = 'page-width';
            });
            eventBus.on('annotationlayerrendered', () => {
                container.querySelectorAll('a').forEach(_this => {
                    _this.setAttribute('target', '_blank');
                });
            });
            pdfViewer.removePageBorders = true;
            pdfViewer.setDocument(pdfDocument);
            container.style.position = 'relative';
            window.addEventListener('resize', () => {
                pdfViewer.currentScaleValue = pdfViewer.currentScale * 2;
                pdfViewer.currentScaleValue = 'page-width';
            });
        },
        (message, exception) => {
            saltos.show_error({
                text: message,
                code: 0,
            });
        });
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Table constructor helper
 *
 * Returns a table using the follow params:
 *
 * @id       => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default table table-striped table-hover
 * @header   => array with the header to use
 * @data     => 2D array with the data used to mount the body table
 * @footer   => array with the footer to use
 * @checkbox => add a checkbox at the first of each row, for mono or multi selection
 * @actions  => each row of the data can contain an array with the actions of each row
 * @label    => this parameter is used as text for the label
 *
 * Notes:
 *
 * This function defines the yellow color used for the hover and active rows.
 *
 * The header field must be an object with the labels, types, aligns, ..., of each field,
 * if the header is ommited, then the data will be painted using the default order of the
 * data without filters, the recomendation is to use header to specify which fields must
 * to be painted, the order, the type and the alignment.
 *
 * This widget requires the locutus library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/locutos/locutus.min.js
 *
 * The divider will be added dynamically depending the contents of the table, the main idea
 * is to use the divider to separate each block of the table (header, data and footer)
 *
 * The actions will be added using a dropdown menu if more than one action appear in the
 * the row data, the idea of this feature is to prevent that the icons uses lot of space
 * of the row data, and for this reason, it will define the dropdown variable that enables
 * or not the contraction feature
 *
 * The elements of the data cells can contains an object with the field specification used
 * to the saltos.form_field constructor, it is usefull to convert some fields to inputs
 * instead of simple text, too is able to use the type attribute in the header specification
 * to identify if you want to use a column with some special type as for example, the icons
 */
saltos.__form_field.table = field => {
    saltos.require('core/lib/locutus/locutus.min.js');
    saltos.check_params(field, ['class', 'id', 'checkbox']);
    saltos.check_params(field, ['header', 'data', 'footer'], []);
    var obj = saltos.html(`
        <table class="table table-striped table-hover ${field.class}"
            id="${field.id}" style="margin-bottom: 0">
        </table>
    `);
    if (Object.keys(field.header).length) {
        obj.append(saltos.html('table', `
            <thead>
                <tr>
                </tr>
            </thead>
        `));
        if (field.checkbox) {
            obj.querySelector('thead tr').append(saltos.html(
                'tr',
                `<th style="width: 1%"><input type="checkbox"/></th>`
            ));
            obj.querySelector('thead input[type=checkbox]').addEventListener('change', event => {
                var _this = event.target;
                obj.querySelectorAll('tbody input[type=checkbox]').forEach(_this2 => {
                    if (_this2.checked != _this.checked) {
                        _this2.click();
                    }
                });
            });
            obj.querySelector('thead input[type=checkbox]').addEventListener('click', event => {
                event.stopPropagation();
            });
            obj.querySelector('thead input[type=checkbox]').parentNode.addEventListener('click', event => {
                event.target.querySelector('input[type=checkbox]').click();
                event.stopPropagation();
            });
        }
        for (var key in field.header) {
            var temp = htmlentities(field.header[key].label);
            var th = saltos.html('tr', `<th>${temp}</th>`);
            if (field.header[key].hasOwnProperty('align')) {
                th.classList.add('text-' + field.header[key].align);
            }
            obj.querySelector('thead tr').append(th);
        }
        if (field.data.length && field.data[0].hasOwnProperty('actions')) {
            obj.querySelector('thead tr').append(saltos.html('tr', `<th style="width: 1%"></th>`));
        }
    }
    if (field.data.length) {
        obj.append(saltos.html('table', `
            <tbody>
            </tbody>
        `));
        if (Object.keys(field.header).length) {
            obj.querySelector('tbody').classList.add('table-group-divider');
        }
        for (var key in field.data) {
            var row = saltos.html('tbody', `<tr></tr>`);
            if (field.checkbox) {
                row.append(saltos.html('tr', `<td><input type="checkbox"/></td>`));
                row.querySelector('input[type=checkbox]').addEventListener('change', event => {
                    if (event.target.checked) {
                        event.target.parentNode.parentNode.classList.add('table-active');
                    } else {
                        event.target.parentNode.parentNode.classList.remove('table-active');
                    }
                });
                row.querySelector('input[type=checkbox]').addEventListener('click', event => {
                    event.stopPropagation();
                });
                row.addEventListener('click', event => {
                    var obj = event.target.parentNode.querySelector('input[type=checkbox]');
                    if (obj) {
                        obj.click();
                    }
                    event.stopPropagation();
                });
            }
            // This is to allow to use tables with data and without header
            var iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = field.data[key];
            }
            for (var key2 in iterator) {
                var val2 = field.data[key][key2];
                var td = saltos.html('tr', `<td></td>`);
                if (typeof val2 == 'object') {
                    if (val2.hasOwnProperty('type')) {
                        var temp = saltos.form_field(val2);
                        td.append(temp);
                    } else {
                        var temp = `object without type`;
                        td.append(temp);
                    }
                } else {
                    var type = 'text';
                    if (iterator[key2].hasOwnProperty('type')) {
                        type = iterator[key2].type;
                    }
                    if (type == 'icon') {
                        var temp = saltos.html(`<i class="bi bi-${val2}"></i>`);
                        td.append(temp);
                    } else if (type == 'text') {
                        var temp = htmlentities(val2);
                        td.append(temp);
                    } else {
                        var temp = `unknown type ${type}`;
                        td.append(temp);
                    }
                }
                if (iterator[key2].hasOwnProperty('align')) {
                    td.classList.add('text-' + iterator[key2].align);
                }
                row.append(td);
            }
            if (field.data[key].hasOwnProperty('actions')) {
                var td = saltos.html('tr', `<td class="p-0 align-middle text-nowrap"></td>`);
                var dropdown = field.data[key].actions.length > 1;
                if (dropdown) {
                    td.append(saltos.html(`
                        <div>
                            <button class="btn border-0 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            </button>
                            <ul class="dropdown-menu">
                            </ul>
                        </div>
                    `));
                    td.querySelector('ul').parentElement.addEventListener('show.bs.dropdown', event => {
                        obj.querySelectorAll('.show').forEach(_this => {
                            _this.classList.remove('show');
                        });
                    });
                }
                var first_action = true;
                for (var key2 in field.data[key].actions) {
                    var val2 = field.data[key].actions[key2];
                    if (val2.url == '') {
                        val2.disabled = true;
                    } else {
                        val2.onclick = `saltos.open_window("#${val2.url}")`;
                    }
                    if (first_action) {
                        if (val2.onclick) {
                            row.setAttribute('_onclick', val2.onclick);
                            row.addEventListener('dblclick', event => {
                                eval(event.target.parentElement.getAttribute('_onclick'));
                            });
                        }
                        first_action = false;
                    }
                    var button = saltos.__form_field.button(val2);
                    if (dropdown) {
                        button.classList.remove('btn');
                        button.classList.add('dropdown-item');
                        button.addEventListener('click', event => {
                            obj.querySelectorAll('.show').forEach(_this => {
                                _this.classList.remove('show');
                            });
                        });
                        var li = saltos.html(`<li></li>`);
                        li.append(button);
                        td.querySelector('ul').append(li);
                    } else {
                        button.classList.add('border-0');
                        td.append(button);
                    }
                }
                row.append(td);
            }
            obj.querySelector('tbody').append(row);
        }
    }
    if (Object.keys(field.footer).length) {
        obj.append(saltos.html('table', `
            <tfoot class="table-group-divider">
                <tr>
                </tr>
            </tfoot>
        `));
        if (field.data.length) {
            obj.querySelector('tfoot').classList.add('table-group-divider');
        }
        if (typeof field.footer == 'object') {
            if (Object.keys(field.header).length != Object.keys(field.footer).length) {
                console.log('field.header.length != field.footer.length');
            }
            if (field.checkbox) {
                obj.querySelector('tfoot tr').append(saltos.html('tr', `<td></td>`));
            }
            // This is to allow to use tables with footer and without header
            var iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = field.footer;
            }
            for (var key in iterator) {
                var val = field.footer[key].value;
                var td = saltos.html('tr', `<td></td>`);
                if (typeof val == 'object') {
                    if (val.hasOwnProperty('type')) {
                        var temp = saltos.form_field(val);
                        td.append(temp);
                    } else {
                        var temp = `object without type`;
                        td.append(temp);
                    }
                } else {
                    var type = 'text';
                    if (iterator[key].hasOwnProperty('type')) {
                        type = iterator[key].type;
                    }
                    if (type == 'icon') {
                        var temp = saltos.html(`<i class="bi bi-${val}"></i>`);
                        td.append(temp);
                    } else if (type == 'text') {
                        var temp = htmlentities(val);
                        td.append(temp);
                    } else {
                        var temp = `unknown type ${type}`;
                        td.append(temp);
                    }
                }
                if (iterator[key].hasOwnProperty('align')) {
                    td.classList.add('text-' + iterator[key].align);
                }
                obj.querySelector('tfoot tr').append(td);
            }
            if (field.data.length && field.data[0].hasOwnProperty('actions')) {
                obj.querySelector('tfoot tr').append(saltos.html('tr', `<td></td>`));
            }
        }
        if (typeof field.footer == 'string') {
            var num = field.header.length;
            if (!num) {
                num = Object.keys(field.data[0]).length;
            }
            if (field.checkbox) {
                num++;
            }
            if (field.data.length && field.data[0].hasOwnProperty('actions')) {
                num++;
            }
            var temp = htmlentities(field.footer);
            obj.querySelector('tfoot tr').append(saltos.html(
                'tr',
                `<td colspan="${num}" class="text-center">${temp}</td>`
            ));
        }
    }
    // Convert the previous table in a responsive table
    // We are using the same div to put inside the styles instead of the table
    var old = obj;
    obj = saltos.html(`<div class="table-responsive"></div>`);
    obj.append(old);
    obj.append(saltos.html(`
        <style>
            .table {
                --bs-table-hover-bg: #fbec88;
                --bs-table-active-bg: #fbec88;
                --bs-table-hover-color: #373a3c;
                --bs-table-active-color: #373a3c;
            }
        </style>
    `));
    // Continue
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Alert constructor helper
 *
 * This component allow to set boxes type alert in the contents, only requires:
 *
 * @id    => the id used to set the reference for to the object
 * @class => allow to add more classes to the default alert
 * @title => title used in the body of the card, not used if void
 * @text  => text used in the body of the card, not used if void
 * @body  => this option allow to specify an specific html to the body of the card, intended
 *           to personalize the body's card
 * @close => boolean to specify if you want to add the dismissible option to the alert
 * @label => this parameter is used as text for the label
 *
 * Note:
 *
 * I have added the dismissible option using the close attribute, too I have added a modification
 * for the style to allow the content to use the original size of the alert, in a future, I don't
 * know if I maintain this or I remove it, but at the moment, this is added by default
 */
saltos.__form_field.alert = field => {
    saltos.check_params(field, ['class', 'id', 'title', 'text', 'body', 'close']);
    var obj = saltos.html(`
        <div class="alert ${field.class}" role="alert" id="${field.id}"></div>
    `);
    if (field.title != '') {
        obj.append(saltos.html(`<h4>${field.title}</h4>`));
    }
    if (field.text != '') {
        obj.append(saltos.html(`<p>${field.text}</p>`));
    }
    if (field.body != '') {
        obj.append(saltos.html(field.body));
    }
    if (field.close) {
        obj.classList.add('alert-dismissible');
        obj.classList.add('fade');
        obj.classList.add('show');
        obj.append(saltos.html(`
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `));
        obj.append(saltos.html(`
            <style>
                .alert-dismissible {
                    padding-right: var(--bs-alert-padding-x);
                }
            </style>
        `));
    }
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Card constructor helper
 *
 * This functions creates a card with a lot of options:
 *
 * @id     => the id used to set the reference for to the object
 * @image  => image used as top image in the card, not used if void
 * @alt    => alt text used in the top image if you specify an image
 * @header => text used in the header, not used if void
 * @footer => text used in the footer, not used if void
 * @title  => title used in the body of the card, not used if void
 * @text   => text used in the body of the card, not used if void
 * @body   => this option allow to specify an specific html to the body of the card, intended
 *            to personalize the body's card
 * @label  => this parameter is used as text for the label
 */
saltos.__form_field.card = field => {
    saltos.check_params(field, ['id', 'image', 'alt', 'header', 'footer', 'title', 'text', 'body']);
    var obj = saltos.html(`<div class="card" id="${field.id}"></div>`);
    if (field.image != '') {
        obj.append(saltos.html(`<img src="${field.image}" class="card-img-top" alt="${field.alt}">`));
    }
    if (field.header != '') {
        obj.append(saltos.html(`<div class="card-header">${field.header}</div>`));
    }
    obj.append(saltos.html(`<div class="card-body"></div>`));
    if (field.title != '') {
        obj.querySelector('.card-body').append(saltos.html(`<h5 class="card-title">${field.title}</h5>`));
    }
    if (field.text != '') {
        obj.querySelector('.card-body').append(saltos.html(`<p class="card-text">${field.text}</p>`));
    }
    if (field.body != '') {
        obj.querySelector('.card-body').append(saltos.html(field.body));
    }
    if (field.footer != '') {
        obj.append(saltos.html(`<div class="card-footer">${field.footer}</div>`));
    }
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Chart.js constructor helper
 *
 * This function creates a chart using the chart.js library, to do this requires de follow arguments:
 *
 * @id    => the id used by the object
 * @mode  => to specify what kind of plot do you want to do: can be bar, line, doughnut, pie
 * @data  => the data used to plot the graph, see the data argument used by the graph.js library
 * @label => this parameter is used as text for the label
 *
 * Notes:
 *
 * To be more practice and for stetic reasons, I'm adding to all datasets the borderWidth = 1
 *
 * This widget requires the chartjs library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/chartjs/chart.umd.min.js
 */
saltos.__form_field.chartjs = field => {
    saltos.require('core/lib/chartjs/chart.umd.min.js');
    saltos.check_params(field, ['id', 'mode', 'data']);
    var obj = saltos.html(`<canvas id="${field.id}"></canvas>`);
    for (var key in field.data.datasets) {
        field.data.datasets[key].borderWidth = 1;
    }
    var element = obj;
    saltos.when_visible(element, () => {
        new Chart(element, {
            type: field.mode,
            data: field.data,
        });
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Tags constructor helper
 *
 * This function creates a text input that allow to manage tags, each tag is paint as a badge
 * and each tag can be deleted, the result is stored in a text using a comma separated values
 *
 * @id          => the id used by the object
 * @value       => comma separated values
 * @datalist    => array with options for the datalist, used as autocomplete for the text input
 * @label       => this parameter is used as text for the label
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 *
 * Notes:
 *
 * This object creates a hidden input, a text input with/without a datalist, and a badge for
 * each value, and requires the arguments of the specific widgets used in this widget
 */
saltos.__form_field.tags = field => {
    saltos.check_params(field, ['id', 'value']);
    // This container must have the hidden input and the text input used by the
    // user to write the tags
    var obj = saltos.html(`<div></div>`);
    // The first field is the hidden input
    field.class = 'first';
    obj.append(saltos.__form_field.hidden(field));
    // The last field is the text input used to write the tags
    field.id_old = field.id;
    field.id = field.id + '_tags';
    field.value_old = field.value.split(',');
    field.value = '';
    field.class = 'last';
    obj.append(saltos.__form_field.text(field));
    // This function draws a tag and programs the delete of the same tag
    var fn = val => {
        var span = saltos.html(`<span class="badge text-bg-primary mt-1 me-1 fs-6 fw-normal pe-2"
            saltos-data="${val}">
            ${val} <i class="bi bi-x-circle ps-1" style="cursor: pointer"></i>
        </span>`);
        obj.append(span);
        span.querySelector('i').addEventListener('click', event => {
            var a = event.target.parentNode;
            var b = a.getAttribute('saltos-data');
            var input = obj.querySelector('input.first');
            var val_old = input.value.split(',');
            var val_new = [];
            for (var key in val_old) {
                val_old[key] = val_old[key].trim();
                if (val_old[key] != b) {
                    val_new.push(val_old[key]);
                }
            }
            input.value = val_new.join(',');
            a.remove();
        });
    };
    // This function program the enter event that adds tags to the hidden and
    // draw the new tag using the previous function
    obj.querySelector('input.last').addEventListener('keydown', event => {
        if (saltos.get_keycode(event) != 13) {
            return;
        }
        var input_old = obj.querySelector('input.first');
        var input_new = obj.querySelector('input.last');
        var val_old = input_old.value.split(',');
        var val = input_new.value;
        var val_new = [];
        for (var key in val_old) {
            val_old[key] = val_old[key].trim();
            if (val_old[key] == val) {
                return;
            }
            if (val_old[key] != '') {
                val_new.push(val_old[key]);
            }
        }
        fn(val);
        val_new.push(val);
        input_old.value = val_new.join(',');
        input_new.value = '';
    });
    // This part of the code adds the initials tags using the fn function
    {
        for (var key in field.value_old) {
            var val = field.value_old[key].trim();
            fn(val);
        }
    }
    // This part of the code is a trick to allow that labels previously created
    // will be linked to the input type text instead of the input type hidden,
    // remember that the hidden contains the original id and the visible textbox
    // contains the id with the _tags ending
    saltos.when_visible(obj, () => {
        document.querySelectorAll('label[for=' + field.id_old + ']').forEach(_this => {
            _this.setAttribute('for', field.id);
        });
    });
    return obj;
};

/**
 * Gallery constructor helper
 *
 * This function returns a gallery object, you can pass some arguments as:
 *
 * @id     => the id used to set the reference for to the object
 * @class  => allow to add more classes to the default img-fluid
 * @label  => this parameter is used as text for the label
 * @images => the array with images, each image can be an string or object
 *
 * This widget requires venobox, masonry and imagesloaded
 *
 * This widget requires the venobox, masonry and imagesloaded libraries and can be loaded
 * automatically using the require feature:
 *
 * @core/lib/venobox/venobox.min.css
 * @core/lib/venobox/venobox.min.js
 * @core/lib/masonry/masonry.pkgd.min.js
 * @core/lib/imagesloaded/imagesloaded.pkgd.min.js
 */
saltos.__form_field.gallery = field => {
    saltos.require('core/lib/venobox/venobox.min.css');
    saltos.require('core/lib/venobox/venobox.min.js');
    saltos.require('core/lib/masonry/masonry.pkgd.min.js');
    saltos.require('core/lib/imagesloaded/imagesloaded.pkgd.min.js');
    saltos.check_params(field, ['id', 'class', 'images']);
    if (field.class == '') {
        field.class = 'col';
    }
    var obj = saltos.html(`
        <div id="${field.id}" class="container-fluid">
            <div class="row">
            </div>
        </div>
    `);
    if (typeof field.images == 'object') {
        for (var key in field.images) {
            var val = field.images[key];
            if (typeof val == 'string') {
                val = {image: val};
            }
            saltos.check_params(val, ['image', 'title']);
            var img = saltos.html(`
                <div class="${field.class} p-1">
                    <a href="${val.image}" class="venobox" data-gall="${field.id}" title="${val.title}">
                        <img src="${val.image}" class="img-fluid img-thumbnail" />
                    </a>
                </div>
            `);
            obj.querySelector('.row').append(img);
        }
    }
    var element = obj.querySelector('.row');
    saltos.when_visible(element, () => {
        var msnry = new Masonry(element, {
            percentPosition: true,
        });
        imagesLoaded(element).on('progress', () => {
            msnry.layout();
        });
        new VenoBox();
    });
    obj = saltos.__label_combine(field, obj);
    return obj;
};

/**
 * Placeholder helper
 *
 * This function returns a grey area that uses all space with the placeholder glow effect
 *
 * @id => id used in the original object, it must be replaced when the data will be available
 */
saltos.__form_field.placeholder = field => {
    saltos.check_params(field, ['id']);
    var obj = saltos.html(`
        <div id="${field.id}" class="w-100 h-100 placeholder-glow" aria-hidden="true">
            <span class="w-100 h-100 placeholder"></span>
        </div>
    `);
    return obj;
};

/**
 * Private text constructor helper
 *
 * This function returns an input object of type text, you can pass some arguments as:
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @style       => the style used in the div object
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.__text_helper = field => {
    saltos.check_params(field, ['type', 'class', 'id', 'placeholder', 'value', 'disabled',
                                'readonly', 'required', 'autofocus', 'tooltip', 'style']);
    if (field.disabled) {
        field.disabled = 'disabled';
    }
    if (field.readonly) {
        field.readonly = 'readonly';
    }
    if (field.required) {
        field.required = 'required';
    }
    if (field.autofocus) {
        field.autofocus = 'autofocus';
    }
    var obj = saltos.html(`
        <input type="${field.type}" class="form-control ${field.class}" id="${field.id}"
            style="${field.style}" placeholder="${field.placeholder}"
            value="${field.value}" ${field.disabled} ${field.readonly} ${field.required} ${field.autofocus}
                data-bs-title="${field.tooltip}">
    `);
    if (field.tooltip != '') {
        saltos.__tooltip_helper(obj);
    }
    return obj;
};

/**
 * Private textarea constructor helper
 *
 * This function returns a textarea object, you can pass the follow arguments:
 *
 * @id          => the id used by the object
 * @class       => allow to add more classes to the default form-control
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @rows        => the number used as rows parameter
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.__textarea_helper = field => {
    saltos.check_params(field, ['class', 'id', 'placeholder', 'value', 'disabled', 'readonly',
                                'required', 'autofocus', 'rows', 'tooltip']);
    if (field.disabled) {
        field.disabled = 'disabled';
    }
    if (field.readonly) {
        field.readonly = 'readonly';
    }
    if (field.required) {
        field.required = 'required';
    }
    if (field.autofocus) {
        field.autofocus = 'autofocus';
    }
    var obj = saltos.html(`
        <textarea class="form-control ${field.class}" id="${field.id}"
            placeholder="${field.placeholder}" rows="${field.rows}"
            ${field.disabled} ${field.readonly} ${field.required} ${field.autofocus}
            data-bs-title="${field.tooltip}">${field.value}</textarea>
    `);
    if (field.tooltip != '') {
        saltos.__tooltip_helper(obj);
    }
    return obj;
};

/**
 * Private tooltip constructor helper
 *
 * This function is intended to enable the tooltip in the object, too it try to do some
 * extra features: program that only show the tooltip when hover and hide when will get
 * the focus or get the click event
 *
 * @obj => the object that you want to enable the tooltip feature
 */
saltos.__tooltip_helper = obj => {
    var instance = new bootstrap.Tooltip(obj, {
        trigger: 'hover'
    });
    obj.addEventListener('focus', () => {
        instance.hide();
    });
    obj.addEventListener('click', () => {
        instance.hide();
    });
};

/**
 * Label helper
 *
 * This function is a helper for label field, it is intended to returns the
 * label object or a void string, this is because if no label is present in
 * the field argument, then an empty string is returned, in the reception
 * of the result, generally this is added to an object and it is ignored
 * because an empty string is not an element, this thing is used by the
 * optimizer to removes the unnecessary envelopment
 *
 * @field => the field that contains the label to be added if needed
 */
saltos.__label_helper = field => {
    saltos.check_params(field, ['label']);
    if (field.label == '') {
        return '';
    }
    var temp = saltos.copy_object(field);
    delete temp.class;
    return saltos.__form_field.label(temp);
};

/**
 * Label Combine
 *
 * This function combine the label with the object, to do it, tries to create a new
 * container object to put the label and the passed object, and then tries to optimize
 * to detect if the label is void
 *
 * @field => the field that contains the label
 * @old   => the object
 *
 * Notes:
 *
 * This function acts as helper to add a label by the constructors that not implement
 * any specific label container, in the other cases, each constructor must to implement
 * their code because each case is different
 */
saltos.__label_combine = (field, old) => {
    var obj = saltos.html(`<div></div>`);
    obj.append(saltos.__label_helper(field));
    obj.append(old);
    obj = saltos.optimize(obj);
    return obj;
};

/**
 * Menu constructor helper
 *
 * This function creates a menu intended to be used in navbar, nabs and tabs
 *
 * @class => the class used in the main ul element
 * @menu  => an array with the follow elements:
 *
 * @name              => name of the menu
 * @disabled          => this boolean allow to disable this menu entry
 * @active            => this boolean marks the option as active
 * @onclick           => the callback used when the user select the menu
 * @dropdown_menu_end => this trick allow to open the dropdown menu from the end to start
 * @menu              => with this option, you can specify an array with the contents of the dropdown menu
 *
 * @name     => name of the menu
 * @disabled => this boolean allow to disable this menu entry
 * @active   => this boolean marks the option as active
 * @onclick  => the callback used when the user select the menu
 * @divider  => you can set this boolean to true to convert the element into a divider
 */
saltos.menu = args => {
    saltos.check_params(args, ['class']);
    saltos.check_params(args, ['menu'], []);
    var obj = saltos.html(`<ul class="${args.class}"></ul>`);
    for (var key in args.menu) {
        var val = args.menu[key];
        saltos.check_params(val, ['name', 'disabled', 'active', 'onclick', 'dropdown_menu_end']);
        saltos.check_params(val, ['menu'], []);
        if (val.disabled) {
            val.disabled = 'disabled';
        }
        if (val.active) {
            val.active = 'active';
        }
        if (val.menu.length) {
            if (val.dropdown_menu_end) {
                val.dropdown_menu_end = 'dropdown-menu-end';
            }
            var temp = saltos.html(`
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        ${val.name}
                    </button>
                    <ul class="dropdown-menu ${val.dropdown_menu_end}">
                    </ul>
                </li>
            `);
            for (var key2 in val.menu) {
                var val2 = val.menu[key2];
                saltos.check_params(val2, ['name', 'disabled', 'active', 'onclick', 'divider']);
                if (val2.disabled) {
                    val2.disabled = 'disabled';
                }
                if (val2.active) {
                    val2.active = 'active';
                }
                if (val2.divider) {
                    var temp2 = saltos.html(`<li><hr class="dropdown-divider"></li>`);
                } else {
                    var temp2 = saltos.html(`<li><button
                        class="dropdown-item ${val2.disabled} ${val2.active}">${val2.name}</button></li>`);
                    if (!val2.disabled) {
                        temp2.addEventListener('click', val2.onclick);
                    }
                }
                temp.querySelector('ul').append(temp2);
            }
        } else {
            var temp = saltos.html(`
                <li class="nav-item">
                    <button class="nav-link ${val.disabled} ${val.active}">${val.name}</button>
                </li>
            `);
            if (!val.disabled) {
                temp.addEventListener('click', val.onclick);
            }
        }
        obj.append(temp);
    }
    return obj;
};

/**
 * Navbar constructor helper
 *
 * This component creates a navbar intended to be used as header
 *
 * @id    => the id used by the object
 * @brand => contains an object with the name, logo, width and height to be used
 *
 * @name   => text used in the brand
 * @logo   => filename of the brand image
 * @width  => width of the brand image
 * @height => height of the brand image
 *
 * @items => contains an array with the objects that will be added to the collapse
 */
saltos.navbar = args => {
    saltos.check_params(args, ['id']);
    saltos.check_params(args, ['brand'], {});
    saltos.check_params(args.brand, ['name', 'logo', 'width', 'height']);
    saltos.check_params(args, ['items'], []);
    var obj = saltos.html(`
        <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
            <div class="container-fluid">
                <div class="navbar-brand">
                    <img src="${args.brand.logo}" alt="${args.brand.name}" width="${args.brand.width}"
                        height="${args.brand.height}" class="d-inline-block align-text-top">
                    ${args.brand.name}
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#${args.id}" aria-controls="${args.id}" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="${args.id}">
                </div>
            </div>
        </nav>
    `);
    for (var key in args.items) {
        var val = args.items[key];
        obj.querySelector('.collapse').append(val);
    }
    return obj;
};

/**
 * Modal constructor helper object
 *
 * This object is used to store the element and the instance of the modal
 */
saltos.__modal = {};

/**
 * Modal constructor helper
 *
 * This function creates a bootstrap modal and open it, offers two ways of usage:
 *
 * 1) you can pass an string to get a quick action
 *
 * @close  => this string close the current modal
 * @isopen => this string is used to check if some modal is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new modal
 *
 * @id     => the id used by the object
 * @class  => allow to add more classes to the default dialog
 * @title  => title used by the modal
 * @close  => text used in the close button for aria purposes
 * @body   => the content used in the modal's body
 * @footer => the content used in the modal's footer
 * @static => forces the modal to be static (prevent close by clicking outside the modal or
 *            by pressing the escape key)
 *
 * Returns a boolean that indicates if the modal can be open or not
 *
 * Notes:
 *
 * This modal will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one modal is allowed at each moment.
 */
saltos.modal = args => {
    // HELPER ACTIONS
    if (args == 'close') {
        return typeof saltos.__modal.instance == 'object' && saltos.__modal.instance.hide();
    }
    if (args == 'isopen') {
        return typeof saltos.__modal.obj == 'object' && saltos.__modal.obj.classList.contains('show');
    }
    // ADDITIONAL CHECK
    if (saltos.modal('isopen')) {
        return false;
    }
    // NORMAL OPERATION
    saltos.check_params(args, ['id', 'class', 'title', 'close', 'body', 'footer', 'static']);
    var temp = '';
    if (args.static) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    //if (args.class == '') {
    //    args.class = 'modal-dialog-centered';
    //}
    var obj = saltos.html(`
        <div class="modal fade" id="${args.id}" tabindex="-1" aria-labelledby="${args.id}_label"
            aria-hidden="true" ${temp}>
            <div class="modal-dialog ${args.class}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="${args.id}_label">${args.title}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="${args.close}"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
    `);
    document.body.append(obj);
    obj.querySelector('.modal-body').append(saltos.html(args.body));
    obj.querySelector('.modal-footer').append(args.footer);
    var instance = new bootstrap.Modal(obj);
    saltos.__modal.obj = obj;
    saltos.__modal.instance = instance;
    obj.addEventListener('shown.bs.modal', event => {
        obj.querySelectorAll('[autofocus]').forEach(_this => {
            _this.focus();
        });
    });
    obj.addEventListener('hidden.bs.modal', event => {
        instance.dispose();
        obj.remove();
    });
    instance.show();
    return true;
};

/**
 * Offcanvas constructor helper object
 *
 * This object is used to store the element and the instance of the offcanvas
 */
saltos.__offcanvas = {};

/**
 * Offcanvas constructor helper
 *
 * This function creates a bootstrap offcanvas and open it, offers two ways of usage:
 *
 * 1) you can pass an string to get a quick action
 *
 * @close  => this string close the current modal
 * @isopen => this string is used to check if some modal is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new modal
 *
 * @id     => the id used by the object
 * @class  => allow to add more classes to the default offcanvas
 * @title  => title used by the offcanvas
 * @close  => text used in the close button for aria purposes
 * @body   => the content used in the offcanvas's body
 * @static => forces the offcanvas to be static (prevent close by clicking outside the
 *            offcanvas or by pressing the escape key)
 *
 * Returns a boolean that indicates if the offcanvas can be open or not
 *
 * Notes:
 *
 * This offcanvas will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one offcanvas is allowed at each moment.
 */
saltos.offcanvas = args => {
    // HELPER ACTIONS
    if (args == 'close') {
        return typeof saltos.__offcanvas.instance == 'object' && saltos.__offcanvas.instance.hide();
    }
    if (args == 'isopen') {
        return typeof saltos.__offcanvas.obj == 'object' && saltos.__offcanvas.obj.classList.contains('show');
    }
    // ADDITIONAL CHECK
    if (saltos.offcanvas('isopen')) {
        return false;
    }
    // NORMAL OPERATION
    saltos.check_params(args, ['id', 'class', 'title', 'close', 'body', 'static']);
    var temp = '';
    if (args.static) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    var obj = saltos.html(`
        <div class="offcanvas ${args.class}" tabindex="-1" id="${args.id}"
            aria-labelledby="${args.id}_label" ${temp}>
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="${args.id}_label">${args.title}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                    aria-label="${args.close}"></button>
            </div>
            <div class="offcanvas-body">
            </div>
        </div>
    `);
    document.body.append(obj);
    obj.querySelector('.offcanvas-body').append(saltos.html(args.body));
    var instance = new bootstrap.Offcanvas(obj);
    saltos.__offcanvas.obj = obj;
    saltos.__offcanvas.instance = instance;
    obj.addEventListener('shown.bs.offcanvas', event => {
        obj.querySelectorAll('[autofocus]').forEach(_this => {
            _this.focus();
        });
    });
    obj.addEventListener('hidden.bs.offcanvas', event => {
        instance.dispose();
        obj.remove();
    });
    instance.show();
    return true;
};

/**
 * Toast constructor helper
 *
 * This function creates a bootstrap toast and show it, and can accept the follow params:
 *
 * @id       => the id used by the object
 * @class    => allow to add more classes to the default toast
 * @title    => title used by the toast
 * @subtitle => small text used by the toast
 * @close    => text used in the close button for aria purposes
 * @body     => the content used in the toast's body
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
 *
 * This widget requires the md5 library and can be loaded automatically using the require
 * feature:
 *
 * @core/lib/md5/md5.min.js
 */
saltos.toast = args => {
    saltos.require('core/lib/md5/md5.min.js');
    saltos.check_params(args, ['id', 'class', 'close', 'title', 'subtitle', 'body']);
    if (document.querySelectorAll('.toast-container').length == 0) {
        document.body.append(saltos.html(`<div
            class="toast-container position-fixed bottom-0 end-0 p-3"></div>`));
    }
    // CHECK FOR REPETITIONS
    var hash = md5(JSON.stringify(args));
    if (document.querySelector(`.toast[hash=${hash}]`)) {
        return false;
    }
    // CONTINUE
    var obj = saltos.html(`
        <div id="${args.id}" class="toast ${args.class}" role="alert" aria-live="assertive"
            aria-atomic="true" hash="${hash}">
            <div class="toast-header">
                <strong class="me-auto">${args.title}</strong>
                <small>${args.subtitle}</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast"
                    aria-label="${args.close}"></button>
            </div>
            <div class="toast-body">
            </div>
        </div>
    `);
    document.querySelector('.toast-container').append(obj);
    obj.querySelector('.toast-body').append(args.body);
    var toast = new bootstrap.Toast(obj);
    obj.addEventListener('hidden.bs.toast', event => {
        toast.dispose();
        obj.remove();
    });
    toast.show();
    return true;
};
