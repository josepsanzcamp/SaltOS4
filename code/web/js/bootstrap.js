
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * Bootstrap helper object
 *
 * This object stores all bootstrap functions and data
 */
saltos.bootstrap = {};

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
 * @text        => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, onenter
 * @hidden      => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, color, onenter
 * @integer     => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @float       => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @color       => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @date        => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @time        => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @datetime    => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @textarea    => id, class, PL, value, DS, RO, RQ, AF, AK, rows, tooltip, label, color, height
 * @ckeditor    => id, class, PL, value, DS, RO, RQ, AF, AK, rows, label, color, height
 * @codemirror  => id, class, PL, value, DS, RO, RQ, AF, AK, rows, mode, label, color, height
 * @iframe      => id, class, src, srcdoc, height, label, color
 * @select      => id, class, DS, RQ, AF, AK, rows, multiple, size, value, tooltip, label, color
 * @multiselect => id, class, DS, RQ, AF, AK, rows, multiple, size, value, multiple, tooltip, label, color
 * @checkbox    => id, class, DS, RO, AK, label, value, tooltip, color
 * @switch      => id, class, DS, RO, AK, label, value, tooltip, color
 * @button      => id, class, DS, AK, value, onclick, tooltip, color
 * @password    => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, onenter
 * @file        => id, class, DS, RQ, AF, AK, multiple, tooltip, label, color
 * @link        => id, DS, AK, value, onclick, tooltip, label, color
 * @label       => id, class, label, tooltip, value
 * @image       => id, class, value, alt, tooltip, width, height, label, color
 * @excel       => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth,
 *                 colWidths, label, color
 * @pdfjs       => id, class, value, label, color
 * @table       => id, class, header, data, footer, value, label, color
 * @alert       => id, class, title, text, body, value, label, color
 * @card        => id, image, alt, header, footer, title, text, body, value, label, color
 * @chartjs     => id, mode, data, value, label, color
 * @tags        => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color
 * @gallery     => id, class, label, images, color
 * @placeholder => id, color
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
 * @AK => accesskey
 *
 * The saltos.bootstrap.__field object is part of this constructor and act with the constructor
 * as a helper, the idea is that the user must to call the constructor and the helpers are
 * only for internal use.
 */
saltos.bootstrap.field = field => {
    saltos.core.check_params(field, ['id', 'type']);
    if (field.id == '') {
        field.id = saltos.core.uniqid();
    }
    if (typeof saltos.bootstrap.__field[field.type] != 'function') {
        throw new Error(`type ${field.type} not found`);
    }
    return saltos.bootstrap.__field[field.type](field);
};

/**
 * Form_field constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each helper
 */
saltos.bootstrap.__field = {};

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
saltos.bootstrap.__field.div = field => {
    saltos.core.check_params(field, ['class', 'id', 'style']);
    var obj = saltos.core.html(`<div class="${field.class}" id="${field.id}" style="${field.style}"></div>`);
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
saltos.bootstrap.__field.container = field => {
    saltos.core.check_params(field, ['class']);
    if (field.class == '') {
        field.class = 'container-fluid';
    }
    var obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some container class is found
    var found = false;
    obj.classList.forEach(_this => {
        if (_this == 'container' || _this.substr(0, 10) == 'container-') {
            found = true;
        }
    });
    if (!found) {
        throw new Error('container class not found in a container node');
    }
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
saltos.bootstrap.__field.row = field => {
    saltos.core.check_params(field, ['class']);
    if (field.class == '') {
        field.class = 'row';
    }
    var obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some row class is found
    var found = false;
    obj.classList.forEach(_this => {
        if (_this == 'row' || _this.substr(0, 4) == 'row-') {
            found = true;
        }
    });
    if (!found) {
        throw new Error('row class not found in a row node');
    }
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
saltos.bootstrap.__field.col = field => {
    saltos.core.check_params(field, ['class']);
    if (field.class == '') {
        field.class = 'col';
    }
    var obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some col class is found
    var found = false;
    obj.classList.forEach(_this => {
        if (_this == 'col' || _this.substr(0, 4) == 'col-') {
            found = true;
        }
    });
    if (!found) {
        throw new Error('col class not found in a col node');
    }
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @datalist    => array with options for the datalist, used as autocomplete for the text input
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 */
saltos.bootstrap.__field.text = field => {
    saltos.core.check_params(field, ['datalist'], []);
    field.type = 'text';
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__text_helper(field));
    if (field.datalist.length) {
        obj.querySelector('input').setAttribute('list', field.id + '_datalist');
        obj.append(saltos.core.html(`<datalist id="${field.id}_datalist"></datalist>`));
        for (var key in field.datalist) {
            var val = field.datalist[key];
            obj.querySelector('datalist').append(saltos.core.html(`<option value="${val}" />`));
        }
    }
    obj = saltos.core.optimize(obj);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 *
 * Notes:
 *
 * This function allow the previous parameters but for hidden inputs, only id
 * and value are usually used, in some cases can be interesting to use the
 * class to identify a group of hidden input
 */
saltos.bootstrap.__field.hidden = field => {
    field.type = 'hidden';
    var obj = saltos.bootstrap.__text_helper(field);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 *
 * Notes:
 *
 * This widget requires the imask library and can be loaded automatically using the require
 * feature:
 *
 * @lib/imaskjs/imask.min.js
 */
saltos.bootstrap.__field.integer = field => {
    saltos.core.require('lib/imaskjs/imask.min.js');
    field.type = 'text';
    var obj = saltos.bootstrap.__text_helper(field);
    field.type = 'integer';
    var element = obj;
    saltos.core.when_visible(element, () => {
        IMask(element, {
            mask: Number,
            scale: 0,
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 *
 * Notes:
 *
 * This widget requires the imask library and can be loaded automatically using the require
 * feature:
 *
 * @lib/imaskjs/imask.min.js
 */
saltos.bootstrap.__field.float = field => {
    saltos.core.require('lib/imaskjs/imask.min.js');
    field.type = 'text';
    var obj = saltos.bootstrap.__text_helper(field);
    field.type = 'float';
    var element = obj;
    saltos.core.when_visible(element, () => {
        IMask(element, {
            mask: Number,
            radix: '.',
            mapToRadix: [','],
            scale: 99,
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 *
 * Notes:
 *
 * Ths color input launch a warning if value is not in the format #rrggbb,
 * for this reason it is set to #000000 if value is void
 */
saltos.bootstrap.__field.color = field => {
    saltos.core.check_params(field, ['value']);
    if (field.value == '') {
        field.value = '#000000';
    }
    field.type = 'color';
    field.class = 'form-control-color';
    var obj = saltos.bootstrap.__text_helper(field);
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 */
saltos.bootstrap.__field.date = field => {
    field.type = 'date';
    var obj = saltos.bootstrap.__text_helper(field);
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 */
saltos.bootstrap.__field.time = field => {
    field.type = 'time';
    var obj = saltos.bootstrap.__text_helper(field);
    obj.step = 1; // This enable the seconds
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 */
saltos.bootstrap.__field.datetime = field => {
    field.type = 'datetime-local';
    var obj = saltos.bootstrap.__text_helper(field);
    field.type = 'datetime';
    obj.step = 1; // This enable the seconds
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @height      => the height used as style.minHeight parameter
 *
 * Notes:
 *
 * This widget requires the autoheight library and can be loaded automatically using the require
 * feature:
 *
 * @lib/autoheight/autoheight.min.js
 */
saltos.bootstrap.__field.textarea = field => {
    saltos.core.require('lib/autoheight/autoheight.min.js');
    saltos.core.check_params(field, ['height']);
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(field));
    var element = obj.querySelector('textarea');
    saltos.core.when_visible(element, () => {
        autoheight(element);
    });
    if (field.height) {
        element.style.minHeight = field.height;
    }
    obj = saltos.core.optimize(obj);
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
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @height      => the height used as style.minHeight parameter
 *
 * Notes:
 *
 * This widget requires the ckeditor library and can be loaded automatically using the require
 * feature:
 *
 * @lib/ckeditor/ckeditor.min.js
 */
saltos.bootstrap.__field.ckeditor = field => {
    saltos.core.require('lib/ckeditor/ckeditor.min.js');
    saltos.core.check_params(field, ['height', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(field));
    var element = obj.querySelector('textarea');
    saltos.core.when_visible(element, () => {
        ClassicEditor.create(element, {
            // Nothing to do
        }).then(editor => {
            if (field.color != 'none') {
                element.nextElementSibling.classList.add('border');
                element.nextElementSibling.classList.add('border-' + field.color);
            }
        }).catch(error => {
            throw new Error(error);
        });
    });
    if (field.height) {
        // The follow code allow to sets the min-height for this widget
        obj.append(saltos.core.html(`
            <style>
                textarea[id=${field.id}]+div .ck-editor__editable {
                    min-height: ${field.height};
                }
            </style>
        `));
    }
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
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @height      => the height used as style.minHeight parameter
 *
 * Notes:
 *
 * This widget requires the codemirror library and can be loaded automatically using the require
 * feature:
 *
 * @lib/codemirror/codemirror.min.css
 * @lib/codemirror/codemirror.min.js
 */
saltos.bootstrap.__field.codemirror = field => {
    saltos.core.require('lib/codemirror/codemirror.min.css');
    saltos.core.require('lib/codemirror/codemirror.min.js');
    saltos.core.check_params(field, ['mode', 'height', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(field));
    var element = obj.querySelector('textarea');
    saltos.core.when_visible(element, () => {
        var cm = CodeMirror.fromTextArea(element, {
            mode: field.mode,
            styleActiveLine: true,
            lineNumbers: true,
            lineWrapping: true,
        });
        if (field.color != 'none') {
            element.nextElementSibling.classList.add('border');
            element.nextElementSibling.classList.add('border-' + field.color);
        }
        element.nextElementSibling.style.height = 'auto';
        cm.on('change', cm.save);
        if (field.height) {
            element.nextElementSibling.querySelector('.CodeMirror-scroll').style.minHeight = field.height;
        }
    });
    return obj;
};

/**
 * Iframe constructor helper
 *
 * This function returns an iframe object, you can pass the follow arguments:
 *
 * @id     => the id used by the object
 * @src    => the value used as src parameter
 * @srcdoc => the value used as srcdoc parameter
 * @class  => allow to add more classes to the default form-control
 * @height => the height used as style.minHeight parameter
 * @label  => this parameter is used as text for the label
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 */
saltos.bootstrap.__field.iframe = field => {
    saltos.core.check_params(field, ['src', 'srcdoc', 'id', 'class', 'height', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <iframe id="${field.id}" frameborder="0"
            class="form-control p-0 ${border} ${field.class}"></iframe>
    `);
    if (field.src) {
        obj.src = field.src;
    }
    if (field.srcdoc) {
        obj.srcdoc = field.srcdoc;
    }
    if (field.height) {
        obj.style.minHeight = field.height;
    }
    obj.addEventListener('load', event => {
        var _this = event.target;
        window.addEventListener('resize', event => {
            if (_this.contentWindow) {
                var size = _this.contentWindow.document.documentElement.offsetHeight + 2;
                _this.style.height = size + 'px';
            }
        });
        window.dispatchEvent(new Event('resize'));
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Select constructor helper
 *
 * This function returns a select object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @required  => this parameter raise the required flag
 * @autofocus => this parameter raise the autofocus flag
 * @multiple  => this parameter enables the multiple selection feature of the select
 * @size      => this parameter allow to see the options list opened with n (size) entries
 * @value     => the value used to detect the selected option
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @rows      => this parameter contains the list of options, each option must be an object
 *               with label and value entries
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 */
saltos.bootstrap.__field.select = field => {
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'required', 'autofocus',
                                    'multiple', 'size', 'value', 'tooltip', 'accesskey', 'color']);
    saltos.core.check_params(field, ['rows'], []);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (saltos.core.eval_bool(field.required)) {
        field.required = 'required';
    }
    if (saltos.core.eval_bool(field.autofocus)) {
        field.autofocus = 'autofocus';
    }
    if (saltos.core.eval_bool(field.multiple)) {
        field.multiple = 'multiple';
    }
    if (field.size != '') {
        field.size = `size="${field.size}"`;
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <select class="form-select ${border} ${field.class}" id="${field.id}"
            ${field.disabled} ${field.required} ${field.autofocus} ${field.multiple}
            data-bs-accesskey="${field.accesskey}" ${field.size}
            data-bs-title="${field.tooltip}"></select>
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    for (var key in field.rows) {
        var val = field.rows[key];
        var selected = '';
        if (field.value.toString() == val.value.toString()) {
            selected = 'selected';
        }
        obj.append(saltos.core.html(`<option value="${val.value}" ${selected}>${val.label}</option>`));
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Multiselect constructor helper
 *
 * This function returns a multiselect object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @size      => this parameter allow to see the options list opened with n (size) entries
 * @value     => the value used as src parameter
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @rows      => this parameter contains the list of options, each option must be an object
 *               with label and value entries
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This widget is created joinin 2 selects and 2 buttons, the user must get the value
 * using the hidden input that is builded using the original id passed by argument.
 *
 * TODO: detected a bug with this widget in chrome in mobile browsers
 */
saltos.bootstrap.__field.multiselect = field => {
    saltos.core.check_params(field, ['value', 'class', 'id', 'disabled', 'size', 'tooltip', 'color']);
    saltos.core.check_params(field, ['rows'], []);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`
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
    obj.querySelector('.one').append(saltos.bootstrap.__field.hidden(field));
    field.type = 'multiselect';
    obj.querySelector('.one').append(saltos.bootstrap.__field.select({
        color: field.color,
        id: field.id + '_abc',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rows: rows_abc,
    }));
    obj.querySelector('.two').append(saltos.bootstrap.__field.button({
        class: `bi-chevron-double-right mb-3`,
        color: field.color,
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
    obj.querySelector('.two').append(saltos.core.html('<br />'));
    obj.querySelector('.two').append(saltos.bootstrap.__field.button({
        class: `bi-chevron-double-left`,
        color: field.color,
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
    obj.querySelector('.three').append(saltos.bootstrap.__field.select({
        color: field.color,
        id: field.id + '_xyz',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rows: rows_xyz,
    }));
    saltos.core.when_visible(obj, () => {
        document.querySelectorAll('label[for=' + field.id + ']').forEach(_this => {
            _this.setAttribute('for', field.id + '_abc');
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Checkbox constructor helper
 *
 * This function returns a checkbox object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-check
 * @disabled  => this parameter raise the disabled flag
 * @readonly  => this parameter raise the readonly flag
 * @label     => this parameter is used as label for the checkbox
 * @value     => this parameter is used to check or unckeck the checkbox, the value
 *               must contain a number that raise as true or false in the if condition
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This widget returns their value by setting a zero or one (0/1) value on the value of the input.
 */
saltos.bootstrap.__field.checkbox = field => {
    saltos.core.check_params(field, ['value', 'id', 'disabled', 'readonly',
                                    'label', 'tooltip', 'class', 'accesskey', 'color']);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (saltos.core.eval_bool(field.readonly)) {
        field.readonly = 'readonly';
    }
    if (saltos.core.eval_bool(field.value)) {
        field.value = 1;
    } else {
        field.value = 0;
    }
    var checked = '';
    if (field.value) {
        checked = 'checked';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <div class="form-check ${field.class}">
            <input class="form-check-input ${border}" type="checkbox" id="${field.id}"
                value="${field.value}" ${field.disabled} ${field.readonly} ${checked}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            <label class="form-check-label" for="${field.id}"
                data-bs-title="${field.tooltip}">${field.label}</label>
        </div>
    `);
    if (field.tooltip != '') {
        obj.querySelectorAll('input, label').forEach(_this => {
            saltos.bootstrap.__tooltip_helper(_this);
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
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-check and form-switch
 * @disabled  => this parameter raise the disabled flag
 * @readonly  => this parameter raise the readonly flag
 * @label     => this parameter is used as label for the switch
 * @value     => this parameter is used to check or unckeck the switch, the value
 *               must contain a number that raise as true or false in the if condition
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This widget uses the checkbox constructor
 */
saltos.bootstrap.__field.switch = field => {
    var obj = saltos.bootstrap.__field.checkbox(field);
    obj.classList.add('form-switch');
    obj.querySelector('input').setAttribute('role', 'switch');
    obj.querySelector('input').classList.add('rounded-pill'); // to do more pretty with cosmo
    return obj;
};

/**
 * Button constructor helper
 *
 * This function returns a button object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-select
 * @disabled  => this parameter raise the disabled flag
 * @autofocus => this parameter raise the autofocus flag
 * @value     => value to be used as text in the contents of the buttons
 * @onclick   => callback function that is executed when the button is pressed
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * The buttons adds the focus-ring classes to use this new feature that solves issues suck as
 * hidden focus when you try to focus a button inside a modal, for example.
 */
saltos.bootstrap.__field.button = field => {
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'autofocus', 'value', 'onclick',
                                    'tooltip', 'icon', 'label', 'accesskey', 'color']);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
        field.class += ' opacity-25';
    }
    if (saltos.core.eval_bool(field.autofocus)) {
        field.autofocus = 'autofocus';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`
        <button type="button" id="${field.id}" ${field.disabled} ${field.autofocus}
            class="btn btn-${field.color} focus-ring focus-ring-${field.color} ${field.class}"
            data-bs-accesskey="${field.accesskey}"
            data-bs-title="${field.tooltip}">${field.value}</button>
    `);
    if (field.icon) {
        obj.prepend(saltos.core.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.value && field.icon) {
        obj.querySelector('i').classList.add('me-1');
    }
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    saltos.bootstrap.__onclick_helper(obj, field.onclick);
    if (field.label != '') {
        // Special case, that adds the label to the button forcing a new line
        var obj2 = saltos.core.html(`<div></div>`);
        obj2.append(saltos.bootstrap.__label_helper(field));
        obj2.append(saltos.core.html('<br />'));
        obj2.append(obj);
        return obj2;
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
 * @accesskey   => the key used as accesskey parameter
 * @label       => this parameter is used as text for the label
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
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
saltos.bootstrap.__field.password = field => {
    saltos.core.check_params(field, ['label', 'class', 'id', 'placeholder', 'value', 'disabled', 'onenter',
                                    'readonly', 'required', 'autofocus', 'tooltip', 'accesskey', 'color']);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (saltos.core.eval_bool(field.readonly)) {
        field.readonly = 'readonly';
    }
    if (saltos.core.eval_bool(field.required)) {
        field.required = 'required';
    }
    if (saltos.core.eval_bool(field.autofocus)) {
        field.autofocus = 'autofocus';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <div>
            <div class="input-group">
                <input type="password" class="form-control ${border} ${field.class}"
                    id="${field.id}" placeholder="${field.placeholder}" value="${field.value}"
                    ${field.disabled} ${field.readonly} ${field.required} ${field.autofocus}
                    aria-label="${field.placeholder}" aria-describedby="${field.id}_button"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
                <button class="btn btn-${field.color} bi-eye-slash" type="button"
                    id="${field.id}_button" data-bs-title="${field.tooltip}"></button>
            </div>
        </div>
    `);
    if (field.tooltip != '') {
        obj.querySelectorAll('input[type=password]').forEach(_this => {
            saltos.bootstrap.__tooltip_helper(_this);
        });
    }
    if (field.onenter != '') {
        obj.querySelectorAll('input[type=password]').forEach(_this => {
            saltos.bootstrap.__onenter_helper(_this, field.onenter);
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
    obj.prepend(saltos.bootstrap.__label_helper(field));
    return obj;
};

/**
 * File constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-control
 * @disabled  => this parameter raise the disabled flag
 * @required  => this parameter raise the required flag
 * @autofocus => this parameter raise the autofocus flag
 * @multiple  => this parameter raise the multiple flag, intended to select more files at time
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
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
saltos.bootstrap.__field.file = field => {
    saltos.core.check_params(field, ['class', 'id', 'value', 'disabled', 'required',
                                    'autofocus', 'multiple', 'tooltip', 'accesskey', 'color']);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (saltos.core.eval_bool(field.required)) {
        field.required = 'required';
    }
    if (saltos.core.eval_bool(field.autofocus)) {
        field.autofocus = 'autofocus';
    }
    if (saltos.core.eval_bool(field.multiple)) {
        field.multiple = 'multiple';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border1 = `border border-${field.color}`;
    var border2 = `border-${field.color}`;
    if (field.color == 'none') {
        border1 = 'border-0';
        border2 = '';
    }
    var obj = saltos.core.html(`
        <div>
            <input type="file" class="form-control ${border1} ${field.class}" id="${field.id}"
                ${field.disabled} ${field.required} ${field.autofocus} ${field.multiple}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            <div class="overflow-auto">
                <table class="table table-striped table-hover ${border2} d-none">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    // The follow code allow to colorize the hover and active rows of the table
    obj.append(saltos.core.html(`
        <style>
            .table td:not([class*="text-bg-"]) {
                --bs-table-hover-bg: #fbec88;
                --bs-table-active-bg: #fbec88;
                --bs-table-hover-color: #373a3c;
                --bs-table-active-color: #373a3c;
            }
        </style>
    `));
    if (field.tooltip != '') {
        obj.querySelectorAll('input').forEach(_this => {
            saltos.bootstrap.__tooltip_helper(_this);
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
            files: [],
        };
        data.files[0] = row.saltos_data;
        saltos.core.ajax({
            url: 'api/?delfiles',
            data: JSON.stringify(data),
            method: 'post',
            content_type: 'application/json',
            success: response => {
                if (typeof response != 'object') {
                    throw new Error(response);
                }
                if (typeof response.error == 'object') {
                    throw new Error(response.error);
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
                throw new Error(request);
            },
            token: saltos.token.get(),
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
                files: [],
            };
            data.files[0] = {
                id: saltos.core.uniqid(),
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
            var row = saltos.core.html('tbody', `
                <tr id="${data.files[0].id}">
                    <td class="text-break">${data.files[0].name}</td>
                    <td class="w-25 align-middle">
                        <div class="progress" role="progressbar" aria-label="Upload percent"
                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </td>
                    <td class="p-0 align-middle" style="width: 1%"><button
                        class="btn bi-trash border-0" type="button"></button></td>
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
                    saltos.core.ajax({
                        url: 'api/?addfiles',
                        data: JSON.stringify(data),
                        method: 'post',
                        content_type: 'application/json',
                        success: response => {
                            if (typeof response != 'object') {
                                throw new Error(response);
                            }
                            if (typeof response.error == 'object') {
                                throw new Error(response.error);
                            }
                            row.saltos_data = response[0];
                            __update_data_input_file(input);
                        },
                        error: request => {
                            throw new Error(request);
                        },
                        progress: event => {
                            if (event.lengthComputable) {
                                var percent = Math.round((event.loaded / event.total) * 100);
                                row.querySelector('.progress-bar').style.width = percent + '%';
                                row.querySelector('.progress').setAttribute('aria-valuenow', percent);
                            }
                        },
                        token: saltos.token.get(),
                    });
                })(data, row);
            }
            // If there is an error
            if (reader.error) {
                data.files[0].error = reader.error.message;
                throw new Error(reader.error);
            }
        }
    });
    obj.prepend(saltos.bootstrap.__label_helper(field));
    return obj;
};

/**
 * Link constructor helper
 *
 * This function creates a field similar of text but with the appearance of a link using a button,
 * the object can receive the follow arguments:
 *
 * @label     => this parameter is used as text for the label
 * @id        => the id used by the object
 * @disabled  => this parameter raise the disabled flag
 * @value     => the value is conveted as label to be used in the button with the appearance of a link
 * @onclick   => callback function that is executed when the button is pressed
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This object is not a real link, it's a button that uses the btn-link class to get the link
 * appearance
 */
saltos.bootstrap.__field.link = field => {
    field.color = 'link';
    var obj = saltos.bootstrap.__field.button(field);
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
saltos.bootstrap.__field.label = field => {
    saltos.core.check_params(field, ['id', 'class', 'label', 'tooltip', 'value']);
    if (field.label == '') {
        field.label = field.value;
    }
    var obj = saltos.core.html(`
        <label for="${field.id}" class="form-label ${field.class}"
            data-bs-title="${field.tooltip}">${field.label}</label>
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
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
 * @width   => this parameter is used as width for the image
 * @height  => this parameter is used as height for the image
 * @color   => the color of the widget (primary, secondary, success, danger, warning, info, none)
 */
saltos.bootstrap.__field.image = field => {
    saltos.core.check_params(field, ['id', 'class', 'value', 'alt',
                                    'tooltip', 'width', 'height', 'color']);
    if (field.class == '') {
        field.class = 'img-fluid';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <img id="${field.id}" src="${field.value}" class="${border} ${field.class}" alt="${field.alt}"
            data-bs-title="${field.tooltip}" width="${field.width}" height="${field.height}" />
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @color          => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * You can get the values after to do changes by accessing to the data of the div used to create
 * the widget.
 *
 * This widget requires the handsontable library and can be loaded automatically using the require
 * feature:
 *
 * @lib/handsontable/handsontable.full.min.css
 * @lib/handsontable/handsontable.full.min.js
 */
saltos.bootstrap.__field.excel = field => {
    saltos.core.require('lib/handsontable/handsontable.full.min.css');
    saltos.core.require('lib/handsontable/handsontable.full.min.js');
    saltos.core.check_params(field, ['id', 'class', 'data', 'rowHeaders', 'colHeaders',
                                    'minSpareRows', 'contextMenu', 'rowHeaderWidth',
                                    'colWidths', 'numcols', 'numrows', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <div style="width: 100%; height: 100%; overflow: auto" class="${border}">
            <div id="${field.id}" class="${field.class}"></div>
        </div>
    `);
    field.numcols = parseInt(field.numcols);
    field.numrows = parseInt(field.numrows);
    if (!field.numcols) {
        field.numcols = 26;
    }
    if (!field.numrows) {
        field.numrows = 20;
    }
    if (field.data == '') {
        field.data = [...Array(field.numrows)].map(e => Array(field.numcols));
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
    saltos.core.when_visible(element, () => {
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
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Pdfjs constructor helper
 *
 * This function creates and returns a pdfviewer object, to do this they use the pdf.js library.
 *
 * @id     => the id used to set the reference for to the object
 * @class  => allow to set the class to the div object used to allocate the widget
 * @src    => the file that contains the pdf document
 * @srcdoc => the data that contains the pdf document
 * @label  => this parameter is used as text for the label
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This widget requires the pdfjs library and can be loaded automatically using the require
 * feature:
 *
 * @lib/pdfjs/pdf_viewer.min.css,
 * @lib/pdfjs/pdf.min.mjs
 * @lib/pdfjs/pdf_viewer.min.mjs
 * @lib/pdfjs/pdf.worker.min.mjs
 *
 * The last file (the worker) is loaded by the library and not by SaltOS, is for this reason
 * that this file not appear in the next requires
 */
saltos.bootstrap.__field.pdfjs = field => {
    saltos.core.require('lib/pdfjs/pdf_viewer.min.css');
    saltos.core.require('lib/pdfjs/pdf.min.mjs');
    saltos.core.require('lib/pdfjs/pdf_viewer.min.mjs');
    saltos.core.check_params(field, ['id', 'class', 'src', 'srcdoc', 'color']);
    if (field.srcdoc != '') {
        field.src = {data: atob(field.srcdoc)};
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div id="${field.id}" class="${field.class}">
            <div class="viewerContainer">
                <div class="pdfViewer"></div>
            </div>
        </div>
    `);
    // The follow code allow to define the needed css for with widget
    obj.append(saltos.core.html(`
        <style>
            .viewerContainer {
                position: absolute;
                width: calc(100% - 2px);
                left: -9px;
                top: -10px;
            }
            .viewerContainer *,
            .viewerContainer *::before,
            .viewerContainer *::after {
                box-sizing: content-box;
            }
        </style>
    `));
    var element = obj.querySelector('.viewerContainer');
    saltos.core.when_visible(element, () => {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'lib/pdfjs/pdf.worker.min.mjs';
        pdfjsLib.getDocument(field.src).promise.then(pdfDocument => {
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
                pdfViewer.currentScaleValue = 'auto';
            });
            eventBus.on('annotationlayerrendered', () => {
                container.querySelectorAll('a').forEach(_this => {
                    _this.setAttribute('target', '_blank');
                });
                if (field.color != 'none') {
                    container.querySelectorAll('.viewerContainer .canvasWrapper').forEach(_this => {
                        _this.classList.add('border');
                        _this.classList.add('border-' + field.color);
                    });
                }
            });
            pdfViewer.removePageBorders = true;
            pdfViewer.setDocument(pdfDocument);
            container.style.position = 'relative';
            window.addEventListener('resize', event => {
                pdfViewer.currentScaleValue = 'auto';
            });
        },
        (message, exception) => {
            throw new Error(message);
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @checkbox => add a checkbox in the first cell of each row, for mono or multi selection
 * @dropdown => a boolean value to force the usage of the dropdown feature, void for auto detection
 * @label    => this parameter is used as text for the label
 * @color    => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @actions  => each row of the data can contain an array with the actions of each row
 *
 * Each action can contain:
 *
 * @app     => the application that must be used to check the permissions
 * @action  => the accion that must to be used to check the permissions
 * @value   => the text used as label in the button of the action
 * @icon    => the icon used in the button of the action
 * @tooltip => the tooltip used in the button of the action
 * @onclick => the onclick function that receives as argument the url to access the action
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
 * The actions will be added using a dropdown menu if more than one action appear in the
 * the row data, the idea of this feature is to prevent that the icons uses lot of space
 * of the row data, and for this reason, it will define the dropdown variable that enables
 * or not the contraction feature
 *
 * The elements of the data cells can contains an object with the field specification used
 * to the saltos.bootstrap.field constructor, it is useful to convert some fields to inputs
 * instead of simple text, too is able to use the type attribute in the header specification
 * to identify if you want to use a column with some special type as for example, the icons
 */
saltos.bootstrap.__field.table = field => {
    saltos.core.check_params(field, ['class', 'id', 'checkbox', 'dropdown', 'color']);
    saltos.core.check_params(field, ['header', 'data', 'footer'], []);
    if (field.checkbox != '') {
        field.checkbox = saltos.core.eval_bool(field.checkbox);
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`
        <table class="table table-striped table-hover border-${field.color} ${field.class}"
            id="${field.id}" style="margin-bottom: 0">
        </table>
    `);
    if (Object.keys(field.header).length) {
        obj.append(saltos.core.html('table', `
            <thead>
                <tr>
                </tr>
            </thead>
        `));
        if (field.checkbox) {
            obj.querySelector('thead tr').append(saltos.core.html(
                'tr',
                `<th class="text-bg-${field.color}" style="width: 1%"><input type="checkbox" /></th>`
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
            var val = field.header[key];
            if (typeof val == 'object' && val !== null) {
                var th = saltos.core.html('tr', `<th class="text-bg-${field.color}">${val.label}</th>`);
            } else {
                var th = saltos.core.html('tr', `<th class="text-bg-${field.color}">${val}</th>`);
            }
            if (val.hasOwnProperty('align')) {
                th.classList.add('text-' + val.align);
            }
            obj.querySelector('thead tr').append(th);
        }
        if (field.data.length && field.data[0].hasOwnProperty('actions')) {
            var th = saltos.core.html('tr', `<th class="text-bg-${field.color}" style="width: 1%"></th>`);
            obj.querySelector('thead tr').append(th);
        }
    }
    if (field.data.length) {
        obj.append(saltos.core.html('table', `
            <tbody>
            </tbody>
        `));
        // This function close all dropdowns
        var dropdown_close = () => {
            obj.querySelectorAll('.show').forEach(_this => {
                _this.classList.remove('show');
            });
        };
        for (var key in field.data) {
            var val = field.data[key];
            var row = saltos.core.html('tbody', `<tr></tr>`);
            if (field.checkbox) {
                row.append(saltos.core.html('tr', `<td><input type="checkbox" value="${val.id}" /></td>`));
                row.querySelector('input[type=checkbox]').addEventListener('change', event => {
                    event.target.parentNode.parentNode.querySelectorAll('td').forEach(_this => {
                        if (event.target.checked) {
                            _this.classList.add('table-active');
                        } else {
                            _this.classList.remove('table-active');
                        }
                    });
                    dropdown_close();
                });
                row.querySelector('input[type=checkbox]').addEventListener('click', event => {
                    // Here program the multiple selection feature using the ctrlKey
                    if (!event.ctrlKey) {
                        // First state, sets the id1
                        saltos.bootstrap.__checkbox_id1 = event.target.value;
                        saltos.bootstrap.__checkbox_id2 = null;
                    } else {
                        // Second state, sets the id2
                        saltos.bootstrap.__checkbox_id2 = event.target.value;
                    }
                    if (saltos.bootstrap.__checkbox_id1 && saltos.bootstrap.__checkbox_id2) {
                        var obj = event.target.parentNode.parentNode.parentNode;
                        var nodes = obj.querySelectorAll('input[type=checkbox][value]');
                        var ids = [saltos.bootstrap.__checkbox_id1, saltos.bootstrap.__checkbox_id2];
                        // Check that the two ids are presents
                        var count = 0;
                        nodes.forEach(_this => {
                            if (ids.includes(_this.value)) {
                                count++;
                            }
                        });
                        // If the two ids are present, then apply
                        if (count == 2) {
                            var found = false;
                            nodes.forEach(_this => {
                                if (ids.includes(_this.value)) {
                                    found = !found;
                                }
                                if (found) {
                                    if (!_this.checked) {
                                        _this.click();
                                    }
                                }
                            });
                        }
                        // Reset the ids to restart the state machine
                        saltos.bootstrap.__checkbox_id1 = null;
                        saltos.bootstrap.__checkbox_id2 = null;
                    }
                    event.stopPropagation();
                });
                row.addEventListener('click', event => {
                    var obj = event.target.parentNode.querySelector('input[type=checkbox]');
                    if (obj) {
                        //~ obj.click();
                        // ctrlKey propagation is important to allow the multiple selection feature
                        obj.dispatchEvent(new MouseEvent('click', {ctrlKey: event.ctrlKey}));
                    }
                    event.stopPropagation();
                });
            }
            // This is to allow to use tables with data and without header
            var iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = val;
            }
            for (var key2 in iterator) {
                var val2 = val[key2];
                var td = saltos.core.html('tr', `<td></td>`);
                if (typeof val2 == 'object' && val2 !== null) {
                    if (val2.hasOwnProperty('type')) {
                        var temp = saltos.bootstrap.field(val2);
                        td.append(temp);
                    } else {
                        var temp = `object without type`;
                        td.append(temp);
                    }
                } else {
                    val2 = saltos.core.toString(val2);
                    var type = 'text';
                    if (iterator[key2].hasOwnProperty('type')) {
                        type = iterator[key2].type;
                    }
                    if (type == 'icon') {
                        if (val2) {
                            var temp = saltos.core.html(`<i class="bi bi-${val2}"></i>`);
                            td.append(temp);
                        }
                    } else if (type == 'html') {
                        if (val2) {
                            var temp = saltos.core.html(val2);
                            td.append(temp);
                        }
                    } else if (type == 'text') {
                        if (val2) {
                            td.append(val2);
                        }
                    } else {
                        var temp = `unknown type ${type}`;
                        td.append(temp);
                    }
                }
                if (iterator[key2].hasOwnProperty('align')) {
                    td.classList.add('text-' + iterator[key2].align);
                }
                if (iterator[key2].hasOwnProperty('class')) {
                    if (val.hasOwnProperty(iterator[key2].class)) {
                        if (val[iterator[key2].class] != '') {
                            td.classList.add('text-bg-' + val[iterator[key2].class]);
                        }
                    }
                }
                row.append(td);
            }
            if (val.hasOwnProperty('actions')) {
                var td = saltos.core.html('tr', `<td class="p-0 align-middle text-nowrap"></td>`);
                var dropdown = val.actions.length > 1;
                if (field.dropdown != '') {
                    dropdown = saltos.core.eval_bool(field.dropdown);
                }
                if (dropdown) {
                    td.append(saltos.core.html(`
                        <div>
                            <button class="btn border-0 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            </button>
                            <ul class="dropdown-menu">
                            </ul>
                        </div>
                    `));
                    // This close all dropdowns when a new dropdown appear
                    td.querySelector('ul').parentElement.addEventListener('show.bs.dropdown', dropdown_close);
                }
                var first_action = true;
                for (var key2 in val.actions) {
                    var val2 = val.actions[key2];
                    if (val2.url == '') {
                        val2.disabled = true;
                    } else {
                        if (!val2.hasOwnProperty('onclick')) {
                            throw new Error('onclick not found');
                        }
                        val2.onclick = `${val2.onclick}("${val2.url}")`;
                    }
                    if (first_action) {
                        if (val2.onclick) {
                            row.setAttribute('_onclick', val2.onclick);
                            row.addEventListener('dblclick', event => {
                                eval(event.target.parentElement.getAttribute('_onclick'));
                                if (document.selection && document.selection.empty) {
                                    window.getSelection().removeAllRanges();
                                } else if (window.getSelection) {
                                    window.getSelection().removeAllRanges();
                                }
                            });
                        }
                        first_action = false;
                    }
                    val2.color = 'none';
                    var button = saltos.bootstrap.__field.button(val2);
                    if (dropdown) {
                        button.classList.remove('btn');
                        button.classList.add('dropdown-item');
                        // This close all dropdowns when click an option inside a dropdown
                        button.addEventListener('click', dropdown_close);
                        var li = saltos.core.html(`<li></li>`);
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
        obj.append(saltos.core.html('table', `
            <tfoot>
                <tr>
                </tr>
            </tfoot>
        `));
        if (typeof field.footer == 'object') {
            if (Object.keys(field.header).length != Object.keys(field.footer).length) {
                throw new Error(`field.header.length != field.footer.length`);
            }
            if (field.checkbox) {
                obj.querySelector('tfoot tr').append(saltos.core.html('tr', `<td></td>`));
            }
            // This is to allow to use tables with footer and without header
            var iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = field.footer;
            }
            for (var key in iterator) {
                var val = field.footer[key];
                if (typeof val == 'object' && val !== null) {
                    var td = saltos.core.html('tr', `<td class="bg-${field.color}-subtle">${val.value}</td>`);
                } else {
                    var td = saltos.core.html('tr', `<td class="bg-${field.color}-subtle">${val}</td>`);
                }
                if (iterator[key].hasOwnProperty('align')) {
                    td.classList.add('text-' + iterator[key].align);
                }
                obj.querySelector('tfoot tr').append(td);
            }
            if (field.data.length && field.data[0].hasOwnProperty('actions')) {
                obj.querySelector('tfoot tr').append(saltos.core.html('tr', `<td></td>`));
            }
        }
        if (typeof field.footer == 'string') {
            obj.querySelector('tfoot tr').append(saltos.core.html(
                'tr',
                `<td colspan="100" class="text-center bg-${field.color}-subtle">${field.footer}</td>`
            ));
        }
    }
    // Convert the previous table in a responsive table
    // We are using the same div to put inside the styles instead of the table
    var obj2 = saltos.core.html(`<div class="table-responsive"></div>`);
    obj2.append(obj);
    // The follow code allow to colorize the hover and active rows of the table
    obj2.append(saltos.core.html(`
        <style>
            .table td:not([class*="text-bg-"]) {
                --bs-table-hover-bg: #fbec88;
                --bs-table-active-bg: #fbec88;
                --bs-table-hover-color: #373a3c;
                --bs-table-active-color: #373a3c;
            }
        </style>
    `));
    // Continue
    obj2 = saltos.bootstrap.__label_combine(field, obj2);
    return obj2;
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
 * @color => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Note:
 *
 * I have added the dismissible option using the close attribute, too I have added a modification
 * for the style to allow the content to use the original size of the alert, in a future, I don't
 * know if I maintain this or I remove it, but at the moment, this is added by default
 */
saltos.bootstrap.__field.alert = field => {
    saltos.core.check_params(field, ['class', 'id', 'title', 'text', 'body', 'close', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div class="alert alert-${field.color} ${field.class}" role="alert" id="${field.id}"></div>
    `);
    if (field.title != '') {
        obj.append(saltos.core.html(`<h4>${field.title}</h4>`));
    }
    if (field.text != '') {
        obj.append(saltos.core.html(`<p>${field.text}</p>`));
    }
    if (field.body != '') {
        obj.append(saltos.core.html(field.body));
    }
    if (saltos.core.eval_bool(field.close)) {
        obj.classList.add('alert-dismissible');
        obj.classList.add('fade');
        obj.classList.add('show');
        obj.append(saltos.core.html(`
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `));
        // The follow code allow to use the full width of the contents, this is a fix that solves
        // the problem caused by the close button.
        obj.append(saltos.core.html(`
            <style>
                .alert-dismissible {
                    padding-right: var(--bs-alert-padding-x);
                }
            </style>
        `));
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 */
saltos.bootstrap.__field.card = field => {
    saltos.core.check_params(field, ['id', 'image', 'alt', 'header',
                                    'footer', 'title', 'text', 'body', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`<div class="card border-${field.color}" id="${field.id}"></div>`);
    if (field.image != '') {
        obj.append(saltos.core.html(`
            <img src="${field.image}" class="card-img-top" alt="${field.alt}" />
        `));
    }
    if (field.header != '') {
        obj.append(saltos.core.html(`
            <div class="card-header border-${field.color} text-bg-${field.color}">${field.header}</div>
        `));
    }
    obj.append(saltos.core.html(`<div class="card-body"></div>`));
    if (field.title != '') {
        obj.querySelector('.card-body').append(saltos.core.html(`
            <h5 class="card-title">${field.title}</h5>
        `));
    }
    if (field.text != '') {
        obj.querySelector('.card-body').append(saltos.core.html(`
            <p class="card-text">${field.text}</p>
        `));
    }
    if (field.body != '') {
        obj.querySelector('.card-body').append(saltos.core.html(field.body));
    }
    if (field.footer != '') {
        obj.append(saltos.core.html(`
            <div class="card-footer border-${field.color} bg-${field.color}-subtle">${field.footer}</div>
        `));
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @lib/chartjs/chart.umd.min.js
 */
saltos.bootstrap.__field.chartjs = field => {
    saltos.core.require('lib/chartjs/chart.umd.min.js');
    saltos.core.check_params(field, ['id', 'mode', 'data']);
    var obj = saltos.core.html(`<canvas id="${field.id}"></canvas>`);
    for (var key in field.data.datasets) {
        field.data.datasets[key].borderWidth = 1;
    }
    var element = obj;
    saltos.core.when_visible(element, () => {
        new Chart(element, {
            type: field.mode,
            data: field.data,
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @placeholder => the text used as placeholder parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @accesskey   => the key used as accesskey parameter
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This object creates a hidden input, a text input with/without a datalist, and a badge for
 * each value, and requires the arguments of the specific widgets used in this widget
 */
saltos.bootstrap.__field.tags = field => {
    saltos.core.check_params(field, ['id', 'value', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    // This container must have the hidden input and the text input used by the
    // user to write the tags
    var obj = saltos.core.html(`<div></div>`);
    // The first field is the hidden input
    field.class = 'first';
    obj.append(saltos.bootstrap.__field.hidden(field));
    // The last field is the text input used to write the tags
    field.id_old = field.id;
    field.id_new = field.id + '_tags';
    field.id = field.id_new;
    field.value_old = field.value;
    field.value_array = field.value.split(',');
    if (field.value == '') {
        field.value_array = [];
    }
    field.value = '';
    field.class = 'last';
    obj.append(saltos.bootstrap.__field.text(field));
    field.id = field.id_old;
    field.value = field.value_old;
    // This function draws a tag and programs the delete of the same tag
    var fn = val => {
        var span = saltos.core.html(`
            <span class="badge text-bg-${field.color} mt-1 me-1 fs-6 fw-normal pe-2" saltos-data="${val}">
                ${val} <i class="bi bi-x-circle ps-1" style="cursor: pointer"></i>
            </span>
        `);
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
        if (saltos.core.get_keycode(event) != 13) {
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
        for (var key in field.value_array) {
            var val = field.value_array[key].trim();
            fn(val);
        }
    }
    // This part of the code is a trick to allow that labels previously created
    // will be linked to the input type text instead of the input type hidden,
    // remember that the hidden contains the original id and the visible textbox
    // contains the id with the _tags ending
    saltos.core.when_visible(obj, () => {
        document.querySelectorAll('label[for=' + field.id_old + ']').forEach(_this => {
            _this.setAttribute('for', field.id_new);
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
 * @images => the array with images, each image can be a string or object
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * This widget requires venobox, masonry and imagesloaded
 *
 * This widget requires the venobox, masonry and imagesloaded libraries and can be loaded
 * automatically using the require feature:
 *
 * @lib/venobox/venobox.min.css
 * @lib/venobox/venobox.min.js
 * @lib/masonry/masonry.pkgd.min.js
 * @lib/imagesloaded/imagesloaded.pkgd.min.js
 */
saltos.bootstrap.__field.gallery = field => {
    saltos.core.require('lib/venobox/venobox.min.css');
    saltos.core.require('lib/venobox/venobox.min.js');
    saltos.core.require('lib/masonry/masonry.pkgd.min.js');
    saltos.core.require('lib/imagesloaded/imagesloaded.pkgd.min.js');
    saltos.core.check_params(field, ['id', 'class', 'images', 'color']);
    if (field.class == '') {
        field.class = 'col';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
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
            saltos.core.check_params(val, ['image', 'title']);
            var img = saltos.core.html(`
                <div class="${field.class} p-1">
                    <a href="${val.image}" class="venobox" data-gall="${field.id}" title="${val.title}">
                        <img src="${val.image}" class="img-fluid img-thumbnail ${border} p-0" />
                    </a>
                </div>
            `);
            obj.querySelector('.row').append(img);
        }
    }
    var element = obj.querySelector('.row');
    saltos.core.when_visible(element, () => {
        var msnry = new Masonry(element, {
            percentPosition: true,
        });
        imagesLoaded(element).on('progress', () => {
            msnry.layout();
        });
        new VenoBox();
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Placeholder helper
 *
 * This function returns a grey area that uses all space with the placeholder glow effect
 *
 * @id => id used in the original object, it must be replaced when the data will be available
 */
saltos.bootstrap.__field.placeholder = field => {
    saltos.core.check_params(field, ['id', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div id="${field.id}" class="w-100 h-100 placeholder-glow text-${field.color}" aria-hidden="true">
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
 * @placeholder => the text used as placeholder parameter
 * @value       => the value used as value parameter
 * @disabled    => this parameter raise the disabled flag
 * @readonly    => this parameter raise the readonly flag
 * @required    => this parameter raise the required flag
 * @autofocus   => this parameter raise the autofocus flag
 * @tooltip     => this parameter raise the title flag
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter     => the function executed when enter key is pressed
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__text_helper = field => {
    saltos.core.check_params(field, ['type', 'class', 'id', 'placeholder', 'value', 'disabled', 'onenter',
                                    'readonly', 'required', 'autofocus', 'tooltip', 'accesskey', 'color']);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (saltos.core.eval_bool(field.readonly)) {
        field.readonly = 'readonly';
    }
    if (saltos.core.eval_bool(field.required)) {
        field.required = 'required';
    }
    if (saltos.core.eval_bool(field.autofocus)) {
        field.autofocus = 'autofocus';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <input type="${field.type}" class="form-control ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${field.disabled} ${field.readonly} ${field.required} ${field.autofocus}
            id="${field.id}" data-bs-title="${field.tooltip}" value="${field.value}" />
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    if (field.onenter != '') {
        saltos.bootstrap.__onenter_helper(obj, field.onenter);
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
 * @color       => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__textarea_helper = field => {
    saltos.core.check_params(field, ['class', 'id', 'placeholder', 'value', 'disabled', 'readonly',
                                    'required', 'autofocus', 'tooltip', 'accesskey', 'color']);
    if (saltos.core.eval_bool(field.disabled)) {
        field.disabled = 'disabled';
    }
    if (saltos.core.eval_bool(field.readonly)) {
        field.readonly = 'readonly';
    }
    if (saltos.core.eval_bool(field.required)) {
        field.required = 'required';
    }
    if (saltos.core.eval_bool(field.autofocus)) {
        field.autofocus = 'autofocus';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <textarea class="form-control ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${field.disabled} ${field.readonly} ${field.required} ${field.autofocus}
            id="${field.id}" data-bs-title="${field.tooltip}">${field.value}</textarea>
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
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
saltos.bootstrap.__tooltip_helper = obj => {
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
saltos.bootstrap.__label_helper = field => {
    saltos.core.check_params(field, ['label']);
    if (field.label == '') {
        return '';
    }
    var temp = saltos.core.copy_object(field);
    delete temp.class;
    return saltos.bootstrap.__field.label(temp);
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
saltos.bootstrap.__label_combine = (field, old) => {
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(old);
    obj = saltos.core.optimize(obj);
    return obj;
};

/**
 * Onclick helper
 *
 * This function is a helper function that adds the onclick event listener to the obj
 * using the correct way to do it, to do it, checks the type of fn.
 *
 * @obj   => the object where you want to add the onclick event
 * @fn    => the function that must be executed when onclick
 */
saltos.bootstrap.__onclick_helper = (obj, fn) => {
    if (typeof fn == 'string') {
        obj.addEventListener('click', new Function(fn));
        return;
    }
    if (typeof fn == 'function') {
        obj.addEventListener('click', fn);
        return;
    }
    throw new Error(`Unknown typeof ${fn}`);
};

/**
 * Onenter helper
 *
 * This function adds the event and detects the enter key in order to execute fn
 *
 * @obj => the object that you want to enable the onenter feature
 * @fn  => the function executed when the onenter is raised
 */
saltos.bootstrap.__onenter_helper = (obj, fn) => {
    obj.addEventListener('keydown', event => {
        if (saltos.core.get_keycode(event) != 13) {
            return;
        }
        if (typeof fn == 'string') {
            eval(fn);
            return;
        }
        if (typeof fn == 'function') {
            fn();
            return;
        }
        throw new Error(`Unknown typeof ${field.onenter}`);
    });
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
 * @icon              => icon of the menu
 * @disabled          => this boolean allow to disable this menu entry
 * @active            => this boolean marks the option as active
 * @onclick           => the callback used when the user select the menu
 * @dropdown_menu_end => this trick allow to open the dropdown menu from the end to start
 * @menu              => with this option, you can specify an array with the contents of the dropdown menu
 *
 * @name     => name of the menu
 * @icon     => icon of the menu
 * @disabled => this boolean allow to disable this menu entry
 * @active   => this boolean marks the option as active
 * @onclick  => the callback used when the user select the menu
 * @divider  => you can set this boolean to true to convert the element into a divider
 */
saltos.bootstrap.menu = args => {
    saltos.core.check_params(args, ['class']);
    saltos.core.check_params(args, ['menu'], []);
    var obj = saltos.core.html(`<ul class="${args.class}"></ul>`);
    for (var key in args.menu) {
        var val = args.menu[key];
        saltos.core.check_params(val, ['name', 'icon', 'disabled', 'active', 'onclick', 'dropdown_menu_end']);
        saltos.core.check_params(val, ['menu'], []);
        var disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        var active = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
        }
        if (val.menu.length) {
            var dropdown_menu_end = '';
            if (saltos.core.eval_bool(val.dropdown_menu_end)) {
                dropdown_menu_end = 'dropdown-menu-end';
            }
            var temp = saltos.core.html(`
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle ${disabled} ${active}"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ${val.name}
                    </button>
                    <ul class="dropdown-menu ${dropdown_menu_end}">
                    </ul>
                </li>
            `);
            if (val.icon) {
                temp.querySelector('button').prepend(saltos.core.html(`<i class="bi bi-${val.icon}"></i>`));
            }
            if (val.name && val.icon) {
                temp.querySelector('i').classList.add('me-1');
            }
            for (var key2 in val.menu) {
                var val2 = val.menu[key2];
                saltos.core.check_params(val2, ['name', 'icon', 'disabled', 'active', 'onclick', 'divider']);
                var disabled2 = '';
                if (saltos.core.eval_bool(val2.disabled)) {
                    disabled2 = 'disabled';
                }
                var active2 = '';
                if (saltos.core.eval_bool(val2.active)) {
                    active2 = 'active';
                }
                if (saltos.core.eval_bool(val2.divider)) {
                    var temp2 = saltos.core.html(`<li><hr class="dropdown-divider"></li>`);
                } else {
                    var temp2 = saltos.core.html(`
                        <li>
                            <button class="dropdown-item ${disabled2} ${active2}">
                                ${val2.name}
                            </button>
                        </li>`);
                    if (val2.icon) {
                        temp2.querySelector('button').prepend(
                            saltos.core.html(`<i class="bi bi-${val2.icon}"></i>`));
                    }
                    if (val2.name && val2.icon) {
                        temp2.querySelector('i').classList.add('me-1');
                    }
                    if (!saltos.core.eval_bool(val2.disabled)) {
                        saltos.bootstrap.__onclick_helper(temp2, val2.onclick);
                    }
                }
                temp.querySelector('ul').append(temp2);
            }
        } else {
            var temp = saltos.core.html(`
                <li class="nav-item">
                    <button class="nav-link ${disabled} ${active}">${val.name}</button>
                </li>
            `);
            if (val.icon) {
                temp.querySelector('button').prepend(saltos.core.html(`<i class="bi bi-${val.icon}"></i>`));
            }
            if (val.name && val.icon) {
                temp.querySelector('i').classList.add('me-1');
            }
            if (!saltos.core.eval_bool(val.disabled)) {
                saltos.bootstrap.__onclick_helper(temp, val.onclick);
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
 * @space => boolean to indicate if you want to add the space div
 * @brand => contains an object with the name, logo, width and height to be used
 * @color => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * @name   => text used in the brand
 * @logo   => filename of the brand image
 * @width  => width of the brand image
 * @height => height of the brand image
 *
 * @items => contains an array with the objects that will be added to the collapse
 */
saltos.bootstrap.navbar = args => {
    saltos.core.check_params(args, ['id', 'space', 'color']);
    saltos.core.check_params(args, ['brand'], {});
    saltos.core.check_params(args.brand, ['name', 'logo', 'width', 'height']);
    saltos.core.check_params(args, ['items'], []);
    if (!args.color) {
        args.color = 'primary';
    }
    var obj = saltos.core.html(`
        <nav class="navbar navbar-expand-md navbar-dark bg-${args.color} fixed-top">
            <div class="container-fluid">
                <div class="navbar-brand">
                    <img src="${args.brand.logo}" alt="${args.brand.name}" width="${args.brand.width}"
                        height="${args.brand.height}" class="d-inline-block align-text-top" />
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
    if (saltos.core.eval_bool(args.space)) {
        var obj2 = saltos.core.html(`<div></div>`);
        obj2.append(obj);
        obj2.append(saltos.core.html(`<div class="pt-5 pb-2"></div>`));
        return obj2;
    }
    return obj;
};

/**
 * Modal constructor helper object
 *
 * This object is used to store the element and the instance of the modal
 */
saltos.bootstrap.__modal = {};

/**
 * Modal constructor helper
 *
 * This function creates a bootstrap modal and open it, offers two ways of usage:
 *
 * 1) you can pass a string to get a quick action
 *
 * @close   => this string close the current modal
 * @isopen  => this string is used to check if some modal is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new modal
 *
 * @id      => the id used by the object
 * @class   => allow to add more classes to the default dialog
 * @title   => title used by the modal
 * @close   => text used in the close button for aria purposes
 * @body    => the content used in the modal's body
 * @footer  => the content used in the modal's footer
 * @static  => forces the modal to be static (prevent close by clicking outside the modal or
 *             by pressing the escape key)
 * @color   => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @replace => boolean that allow to replace the title, body and footer of an active modal
 *
 * Returns a boolean that indicates if the modal can be open or not
 *
 * Notes:
 *
 * This modal will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one modal is allowed at each moment.
 *
 * Body and footer allow to use a string containing a html fragment or an object, and
 * the footer too detects that the string contains something different to void, and if
 * void content is detected, then the footer is removed.
 *
 * As an extra bonus, this widget has some tricks to improve the style of the footer, as
 * you can see in the modal-footer part by removing the border and the top padding.
 */
saltos.bootstrap.modal = args => {
    // Helper actions
    if (args == 'close') {
        var bool = typeof saltos.bootstrap.__modal.instance == 'object';
        if (bool) {
            saltos.bootstrap.__modal.instance.hide();
        }
        return bool;
    }
    if (args == 'isopen') {
        return typeof saltos.bootstrap.__modal.instance == 'object';
    }
    if (args.hasOwnProperty('replace') && saltos.core.eval_bool(args.replace)) {
        var bool = typeof saltos.bootstrap.__modal.instance == 'object';
        if (bool) {
            var obj = saltos.bootstrap.__modal.obj;
            obj.querySelector('.modal-title').innerHTML = args.title;
            obj.querySelector('.modal-body').innerHTML = '';
            if (typeof args.body == 'string') {
                obj.querySelector('.modal-body').append(saltos.core.html(args.body));
            } else {
                obj.querySelector('.modal-body').append(args.body);
            }
            if (obj.querySelector('.modal-footer')) {
                if (typeof args.footer == 'string') {
                    if (args.footer != '') {
                        obj.querySelector('.modal-footer').append(saltos.core.html(args.footer));
                    } else {
                        obj.querySelector('.modal-footer').remove();
                    }
                } else {
                    obj.querySelector('.modal-footer').append(args.footer);
                }
            }
            obj.querySelectorAll('[autofocus]').forEach(_this => {
                _this.focus();
            });
        }
        return bool;
    }
    // Additional check
    if (typeof saltos.bootstrap.__modal.instance == 'object') {
        return false;
    }
    // Normal operation
    saltos.core.check_params(args, ['id', 'class', 'title', 'close', 'body', 'footer', 'static', 'color']);
    var temp = '';
    if (saltos.core.eval_bool(args.static)) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    //if (args.class == '') {
    //    args.class = 'modal-dialog-centered';
    //}
    if (!args.color) {
        args.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div class="modal fade" id="${args.id}" tabindex="-1" aria-labelledby="${args.id}_label"
            aria-hidden="true" ${temp}>
            <div class="modal-dialog ${args.class}">
                <div class="modal-content">
                    <div class="modal-header text-bg-${args.color}">
                        <h1 class="modal-title fs-5" id="${args.id}_label">${args.title}</h1>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="${args.close}"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                    </div>
                </div>
            </div>
        </div>
    `);
    document.body.append(obj);
    if (typeof args.body == 'string') {
        obj.querySelector('.modal-body').append(saltos.core.html(args.body));
    } else {
        obj.querySelector('.modal-body').append(args.body);
    }
    if (typeof args.footer == 'string') {
        if (args.footer != '') {
            obj.querySelector('.modal-footer').append(saltos.core.html(args.footer));
        } else {
            obj.querySelector('.modal-footer').remove();
        }
    } else {
        obj.querySelector('.modal-footer').append(args.footer);
    }
    var instance = new bootstrap.Modal(obj);
    saltos.bootstrap.__modal.obj = obj;
    saltos.bootstrap.__modal.instance = instance;
    obj.addEventListener('shown.bs.modal', event => {
        obj.querySelectorAll('[autofocus]').forEach(_this => {
            _this.focus();
        });
    });
    obj.addEventListener('hidden.bs.modal', event => {
        saltos.bootstrap.__modal.instance.dispose();
        saltos.bootstrap.__modal.obj.remove();
        delete saltos.bootstrap.__modal.instance;
        delete saltos.bootstrap.__modal.obj;
    });
    instance.show();
    return true;
};

/**
 * Offcanvas constructor helper object
 *
 * This object is used to store the element and the instance of the offcanvas
 */
saltos.bootstrap.__offcanvas = {};

/**
 * Offcanvas constructor helper
 *
 * This function creates a bootstrap offcanvas and open it, offers two ways of usage:
 *
 * 1) you can pass a string to get a quick action
 *
 * @close  => this string close the current offcanvas
 * @isopen => this string is used to check if some offcanvas is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new offcanvas
 *
 * @id     => the id used by the object
 * @class  => allow to add more classes to the default offcanvas
 * @title  => title used by the offcanvas
 * @close  => text used in the close button for aria purposes
 * @body   => the content used in the offcanvas's body
 * @static => forces the offcanvas to be static (prevent close by clicking outside the
 *            offcanvas or by pressing the escape key)
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Returns a boolean that indicates if the offcanvas can be open or not
 *
 * Notes:
 *
 * This offcanvas will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one offcanvas is allowed at each moment.
 *
 * Body allow to use a string containing a html fragment or an object, as the modal body.
 */
saltos.bootstrap.offcanvas = args => {
    // Helper actions
    if (args == 'close') {
        var bool = typeof saltos.bootstrap.__offcanvas.instance == 'object';
        if (bool) {
            saltos.bootstrap.__offcanvas.instance.hide();
        }
        return bool;
    }
    if (args == 'isopen') {
        return typeof saltos.bootstrap.__offcanvas.instance == 'object';
    }
    // Additional check
    if (typeof saltos.bootstrap.__offcanvas.instance == 'object') {
        return false;
    }
    // Normal operation
    saltos.core.check_params(args, ['id', 'class', 'title', 'close', 'body', 'static', 'color']);
    var temp = '';
    if (saltos.core.eval_bool(args.static)) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    if (args.class == '') {
        args.class = 'offcanvas-start';
    }
    if (!args.color) {
        args.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div class="offcanvas ${args.class}" tabindex="-1" id="${args.id}"
            aria-labelledby="${args.id}_label" ${temp}>
            <div class="offcanvas-header text-bg-${args.color}">
                <h5 class="offcanvas-title" id="${args.id}_label">${args.title}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="${args.close}"></button>
            </div>
            <div class="offcanvas-body">
            </div>
        </div>
    `);
    document.body.append(obj);
    if (typeof args.body == 'string') {
        obj.querySelector('.offcanvas-body').append(saltos.core.html(args.body));
    } else {
        obj.querySelector('.offcanvas-body').append(args.body);
    }
    var instance = new bootstrap.Offcanvas(obj);
    saltos.bootstrap.__offcanvas.obj = obj;
    saltos.bootstrap.__offcanvas.instance = instance;
    obj.addEventListener('shown.bs.offcanvas', event => {
        obj.querySelectorAll('[autofocus]').forEach(_this => {
            _this.focus();
        });
    });
    obj.addEventListener('hidden.bs.offcanvas', event => {
        saltos.bootstrap.__offcanvas.instance.dispose();
        saltos.bootstrap.__offcanvas.obj.remove();
        delete saltos.bootstrap.__offcanvas.instance;
        delete saltos.bootstrap.__offcanvas.obj;
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
 * @color    => the color of the widget (primary, secondary, success, danger, warning, info, none)
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
 * Body allow to use a string containing a html fragment or an object, as the modal body.
 */
saltos.bootstrap.toast = args => {
    saltos.core.check_params(args, ['id', 'class', 'close', 'title', 'subtitle', 'body', 'color']);
    if (document.querySelectorAll('.toast-container').length == 0) {
        document.body.append(saltos.core.html(`
            <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
        `));
    }
    // Check for repetitions
    var hash = md5(JSON.stringify(args));
    if (document.querySelector(`.toast[hash=x${hash}]`)) {
        return false;
    }
    // Continue
    if (!args.color) {
        args.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div id="${args.id}" class="toast ${args.class}" role="alert" aria-live="assertive"
            aria-atomic="true" hash="x${hash}">
            <div class="toast-header text-bg-${args.color}">
                <strong class="me-auto">${args.title}</strong>
                <small>${args.subtitle}</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                    aria-label="${args.close}"></button>
            </div>
            <div class="toast-body">
            </div>
        </div>
    `);
    document.querySelector('.toast-container').append(obj);
    if (typeof args.body == 'string') {
        obj.querySelector('.toast-body').append(saltos.core.html(args.body));
    } else {
        obj.querySelector('.toast-body').append(args.body);
    }
    var toast = new bootstrap.Toast(obj);
    obj.addEventListener('hidden.bs.toast', event => {
        toast.dispose();
        obj.remove();
    });
    toast.show();
    return true;
};

/**
 * Accesskey listener
 *
 * This function is intended to improve the default accesskey in the object by
 * adding features suck as combinations of keys like alt+f or alt+delete
 *
 * @obj => the object that you want to enable the accesskey feature
 */
saltos.bootstrap.__accesskey_listener = event => {
    var keycodes = {
        'backspace': 8,
        'tab': 9,
        'enter': 13,
        'pauseBreak': 19,
        'capsLock': 20,
        'escape': 27,
        'space': 32,
        'pageUp': 33,
        'pageDown': 34,
        'end': 35,
        'home': 36,
        'leftArrow': 37,
        'upArrow': 38,
        'rightArrow': 39,
        'downArrow': 40,
        'insert': 45,
        'delete': 46,
        '0': 48,
        '1': 49,
        '2': 50,
        '3': 51,
        '4': 52,
        '5': 53,
        '6': 54,
        '7': 55,
        '8': 56,
        '9': 57,
        'a': 65,
        'b': 66,
        'c': 67,
        'd': 68,
        'e': 69,
        'f': 70,
        'g': 71,
        'h': 72,
        'i': 73,
        'j': 74,
        'k': 75,
        'l': 76,
        'm': 77,
        'n': 78,
        'o': 79,
        'p': 80,
        'q': 81,
        'r': 82,
        's': 83,
        't': 84,
        'u': 85,
        'v': 86,
        'w': 87,
        'x': 88,
        'y': 89,
        'z': 90,
        'leftWindowKey': 91,
        'rightWindowKey': 92,
        'selectKey': 93,
        'numpad0': 96,
        'numpad1': 97,
        'numpad2': 98,
        'numpad3': 99,
        'numpad4': 100,
        'numpad5': 101,
        'numpad6': 102,
        'numpad7': 103,
        'numpad8': 104,
        'numpad9': 105,
        'multiply': 106,
        'add': 107,
        'subtract': 109,
        'decimalPoint': 110,
        'divide': 111,
        'f1': 112,
        'f2': 113,
        'f3': 114,
        'f4': 115,
        'f5': 116,
        'f6': 117,
        'f7': 118,
        'f8': 119,
        'f9': 120,
        'f10': 121,
        'f11': 122,
        'f12': 123,
        'numLock': 144,
        'scrollLock': 145,
        'semiColon': 186,
        'equalSign': 187,
        'comma': 188,
        'dash': 189,
        'period': 190,
        'forwardSlash': 191,
        'graveAccent': 192,
        'openBracket': 219,
        'backSlash': 220,
        'closeBraket': 221,
        'singleQuote': 222
    };
    document.querySelectorAll('[data-bs-accesskey]:not([data-bs-accesskey=""])').forEach(obj => {
        var temp = obj.getAttribute('data-bs-accesskey').split('+');
        var useAlt = false;
        var useCtrl = false;
        var useShift = false;
        var key = null;
        for (var i = 0,len = temp.length; i < len; i++) {
            if (temp[i] == 'alt') {
                useAlt = true;
            } else if (temp[i] == 'ctrl') {
                useCtrl = true;
            } else if (temp[i] == 'shift') {
                useShift = true;
            } else {
                key = keycodes[temp[i]];
            }
        }
        var count = 0;
        if (useAlt && event.altKey) {
            count++;
        }
        if (!useAlt && !event.altKey) {
            count++;
        }
        if (useCtrl && event.ctrlKey) {
            count++;
        }
        if (!useCtrl && !event.ctrlKey) {
            count++;
        }
        if (useShift && event.shiftKey) {
            count++;
        }
        if (!useShift && !event.shiftKey) {
            count++;
        }
        if (key == saltos.core.get_keycode(event)) {
            count++;
        }
        if (count == 4) {
            if (['button', 'a'].includes(obj.tagName.toLowerCase())) {
                obj.click();
                event.preventDefault();
            }
            if (['input', 'select', 'textarea'].includes(obj.tagName.toLowerCase())) {
                obj.focus();
                event.preventDefault();
            }
        }
    });
};

/**
 * Accesskey listener
 *
 * Attach the accesskey listener function to the keydown event of the document
 */
document.addEventListener('keydown', saltos.bootstrap.__accesskey_listener);
