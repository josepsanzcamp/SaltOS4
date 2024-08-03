
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
 * @text        => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, OE, OC
 * @hidden      => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, color, OE, OC
 * @integer     => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @float       => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @color       => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @date        => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @time        => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @datetime    => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @textarea    => id, class, PL, value, DS, RO, RQ, AF, AK, rows, tooltip, label, color, height, OC
 * @ckeditor    => id, class, PL, value, DS, RO, RQ, AF, AK, rows, label, color, height, OC
 * @codemirror  => id, class, PL, value, DS, RO, RQ, AF, AK, rows, mode, label, color, height, OC
 * @iframe      => id, class, src, srcdoc, height, label, color
 * @select      => id, class, DS, RQ, AF, AK, rows, multiple, size, value, tooltip, label, color, OC
 * @multiselect => id, class, DS, RQ, AF, AK, rows, multiple, size, value, multiple, tooltip, label, color
 * @checkbox    => id, class, DS, RO, AK, label, value, tooltip, color, OC
 * @switch      => id, class, DS, RO, AK, label, value, tooltip, color, OC
 * @button      => id, class, DS, AK, label, onclick, tooltip, color
 * @password    => id, class, PL, value, DS, RO, RQ, AF, AK, tooltip, label, color, OE, OC
 * @file        => id, class, DS, RQ, AF, AK, multiple, tooltip, label, color, OC
 * @link        => id, DS, AK, value, onclick, tooltip, label, color
 * @label       => id, class, label, tooltip
 * @image       => id, class, value, alt, tooltip, width, height, label, color
 * @excel       => id, class, data, rowHeaders, colHeaders, minSpareRows, contextMenu, rowHeaderWidth,
 *                 colWidths, label, color
 * @pdfjs       => id, class, value, label, color
 * @table       => id, class, header, data, footer, value, label, color
 * @alert       => id, class, title, text, body, value, label, color
 * @card        => id, image, alt, header, footer, title, text, body, value, label, color
 * @chartjs     => id, mode, data, value, label, color
 * @tags        => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, OC
 * @gallery     => id, class, label, images, color
 * @placeholder => id, color
 * @list        => id, class, header, extra, data, footer, onclick, active, disabled, label
 * @tabs        => id, tabs, name, content, active, disabled, label
 * @pills       => id, tabs, name, content, active, disabled, label
 * @v-pills     => id, tabs, name, content, active, disabled, label
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
 * @OE => onenter
 * @OC => onchange
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
 *
 * Notes:
 *
 * As special feature for div containes suck as cols, rows and containers, the unused arguments of fields
 * are set as data-bs-{subfield} in the object to be accesed from the obj directly, this allow to set for
 * example the data-bs-title or other parameter in a div container to be used futher
 */
saltos.bootstrap.__field.div = field => {
    saltos.core.check_params(field, ['class', 'id', 'style']);
    var obj = saltos.core.html(`<div class="${field.class}" id="${field.id}" style="${field.style}"></div>`);
    for (var i in field) {
        if (obj.hasAttribute(i)) {
            continue;
        }
        if (['type', 'col_class'].includes(i)) {
            continue;
        }
        obj[`data-bs-${i}`] = field[i];
    }
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
 * HR constructor helper
 *
 * This function returns an object of the type class by default, you can pass the class
 * argument in the field object to specify what kind of class do you want to use.
 *
 * @id    => the id used by the object
 * @class => the class used in the div object
 * @style => the style used in the div object
 */
saltos.bootstrap.__field.hr = field => {
    saltos.core.check_params(field, ['class', 'id', 'style']);
    var obj = saltos.core.html(`<hr class="${field.class}" id="${field.id}" style="${field.style}"/>`);
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
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget contains a datalist with ajax autoload, this allow to send requests
 * to the desired path to retrieve the contents of the datalist for the autocomplete,
 * this request uses an historical keyword that can be retrieved in the json/term
 */
saltos.bootstrap.__field.text = field => {
    saltos.core.check_params(field, ['datalist'], []);
    field.type = 'text';
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__text_helper(field));
    if (field.datalist.length) {
        if (typeof field.datalist == 'string') {
            field.datalist_old = field.datalist;
            field.datalist = [];
            obj.querySelector('input').addEventListener('keypress', saltos.core.delay(event => {
                var value = event.target.value;
                var old_value = event.target.getAttribute('old_value');
                if (value == old_value || value == '') {
                    return;
                }
                event.target.setAttribute('old_value', value);
                obj.querySelectorAll('datalist option').forEach(option => {
                    option.remove();
                });
                saltos.core.ajax({
                    url: 'api/?' + field.datalist_old,
                    data: JSON.stringify({term: value}),
                    method: 'post',
                    content_type: 'application/json',
                    success: response => {
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        for (var key in response.data) {
                            var val = response.data[key];
                            if (typeof val == 'object') {
                                obj.querySelector('datalist')
                                    .append(saltos.core.html(`<option value="${val.label}" />`));
                            } else {
                                obj.querySelector('datalist')
                                    .append(saltos.core.html(`<option value="${val}" />`));
                            }
                        }
                    },
                    error: request => {
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    abort: request => {
                        saltos.app.form.screen('unloading');
                    },
                    token: saltos.token.get(),
                    lang: saltos.gettext.get(),
                });
            }, 500));
        }
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
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
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget requires the ckeditor library and can be loaded automatically using the require
 * feature:
 *
 * @lib/ckeditor/ckeditor.min.js
 *
 * The returned object contains a textarea with two new properties like ckeditor and set,
 * the first contains the ckeditor object and the second is a function used to update the
 * value of the ckeditor, intended to load new data.
 */
saltos.bootstrap.__field.ckeditor = field => {
    saltos.core.require('lib/ckeditor/ckeditor.min.js');
    // Language prefetch
    var lang = saltos.gettext.get_short();
    if (lang != 'en') {
        saltos.core.require(`lib/ckeditor/translations/${lang}.js`);
    }
    // Continue
    saltos.core.check_params(field, ['height', 'color', 'disabled']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(saltos.core.copy_object(field)));
    var element = obj.querySelector('textarea');
    saltos.core.when_visible(element, () => {
        ClassicEditor.create(element, {
            language: lang,
        }).then(editor => {
            element.ckeditor = editor;
            // Program the set feature
            element.set = value => {
                editor.setData(value);
            };
            // Program the disabled feature
            element.set_disabled = bool => {
                if (bool) {
                    editor.enableReadOnlyMode('editor');
                } else {
                    editor.disableReadOnlyMode('editor');
                }
            };
            if (field.color != 'none') {
                element.nextElementSibling.classList.add('border');
                element.nextElementSibling.classList.add('border-' + field.color);
            }
            editor.model.document.on('change:data', () => {
                element.value = editor.getData();
            });
            // I maintain the follow commented lines as an example of usage
            /*editor.on('change:isReadOnly', (evt, propertyName, isReadOnly) => {
                var toolbar = editor.ui.view.toolbar.element;
                var editable = editor.ui.view.editable.element;
                if (isReadOnly) {
                    toolbar.classList.add('bg-body-secondary');
                    editable.classList.add('bg-body-secondary');
                } else {
                    toolbar.classList.remove('bg-body-secondary');
                    editable.classList.remove('bg-body-secondary');
                }
            });*/
            if (saltos.core.eval_bool(field.disabled)) {
                element.set_disabled(true);
            }
        }).catch(error => {
            throw new Error(error);
        });
    });
    obj.append(saltos.core.html(`
        <style>
            .ck-read-only {
                background-color: var(--bs-secondary-bg)!important;
            }
            .ck-toolbar:has(.ck-disabled:not(.ck-off)) {
                background-color: var(--bs-secondary-bg)!important;
            }
        </style>
    `));
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
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget requires the codemirror library and can be loaded automatically using the require
 * feature:
 *
 * @lib/codemirror/codemirror.min.css
 * @lib/codemirror/codemirror.min.js
 *
 * The returned object contains a textarea with two new properties like codemirror and set,
 * the first contains the codemirror object and the second is a function used to update the
 * value of the codemirror, intended to load new data.
 */
saltos.bootstrap.__field.codemirror = field => {
    saltos.core.require('lib/codemirror/codemirror.min.css');
    saltos.core.require('lib/codemirror/codemirror.min.js');
    saltos.core.check_params(field, ['mode', 'height', 'color', 'disabled']);
    if (!field.color) {
        field.color = 'primary';
    }
    var obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(saltos.core.copy_object(field)));
    var element = obj.querySelector('textarea');
    saltos.core.when_visible(element, () => {
        var cm = CodeMirror.fromTextArea(element, {
            mode: field.mode,
            styleActiveLine: true,
            lineNumbers: true,
            lineWrapping: true,
        });
        element.codemirror = cm;
        // Program the set feature
        element.set = value => {
            cm.setValue(value);
        };
        // Program the disabled feature
        element.set_disabled = bool => {
            if (bool) {
                cm.setOption('readOnly', 'nocursor');
                element.nextElementSibling.classList.add('bg-body-secondary');
            } else {
                cm.setOption('readOnly', '');
                element.nextElementSibling.classList.remove('bg-body-secondary');
            }
        };
        if (saltos.core.eval_bool(field.disabled)) {
            element.set_disabled(true);
        }
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
        saltos.core.when_visible(obj, () => {
            window.dispatchEvent(new Event('resize'));
        });
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
 * @onchange  => the function executed when onchange event is detected
 */
saltos.bootstrap.__field.select = field => {
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'required', 'onchange', 'autofocus',
                                     'multiple', 'size', 'value', 'tooltip', 'accesskey', 'color']);
    saltos.core.check_params(field, ['rows'], []);
    var disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    var required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    var autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    var multiple = '';
    if (saltos.core.eval_bool(field.multiple)) {
        multiple = 'multiple';
    }
    var size = '';
    if (field.size != '') {
        size = `size="${field.size}"`;
    }
    var color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    var border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <select class="form-select ${border} ${field.class}" id="${field.id}"
            ${disabled} ${required} ${autofocus} ${multiple} ${size}
            data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}"></select>
    `);
    if (field.onchange != '') {
        saltos.bootstrap.__onchange_helper(obj, field.onchange);
    }
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    for (var key in field.rows) {
        var val = saltos.core.join_attr_value(field.rows[key]);
        saltos.core.check_params(val, ['label', 'value']);
        var selected = '';
        if (field.value.toString() == val.value.toString()) {
            selected = 'selected';
        }
        var option = saltos.core.html(`<option value="${val.value}" ${selected}></option>`);
        option.append(val.label);
        obj.append(option);
    }
    // Program the disabled feature
    obj.set_disabled = bool => {
        if (bool) {
            obj.setAttribute('disabled', '');
        } else {
            obj.removeAttribute('disabled');
        }
    };
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
 * @required  => this parameter raise the required flag
 * @size      => this parameter allow to see the options list opened with n (size) entries
 * @value     => the value used as src parameter
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @rows      => this parameter contains the list of options, each option must be an object
 *               with label and value entries
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @separator => the separator string used to split and join the values
 * @onchange  => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget is created joinin 2 selects and 2 buttons, the user must get the value
 * using the hidden input that is builded using the original id passed by argument.
 *
 * TODO: detected a bug with this widget in chrome in mobile browsers
 */
saltos.bootstrap.__field.multiselect = field => {
    saltos.core.check_params(field, ['value', 'class', 'id', 'disabled', 'required',
                                     'size', 'tooltip', 'color', 'separator']);
    saltos.core.check_params(field, ['rows'], []);
    if (!field.separator) {
        field.separator = ',';
    }
    var obj = saltos.core.html(`
        <div class="container-fluid">
            <div class="row">
                <div class="col px-0 one d-flex">
                </div>
                <div class="col col-auto my-auto two">
                </div>
                <div class="col px-0 three d-flex">
                </div>
            </div>
        </div>
    `);
    obj.querySelector('.one').append(saltos.bootstrap.__field.hidden(saltos.core.copy_object(field)));
    obj.querySelector('.one').append(saltos.bootstrap.__field.select({
        color: field.color,
        id: field.id + '_abc',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
        rows: field.rows,
    }));
    obj.querySelector('.two').append(saltos.bootstrap.__field.button({
        class: `bi-chevron-double-right mb-3`,
        color: field.color,
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            obj.querySelectorAll('#' + field.id + '_abc option').forEach(option => {
                if (option.selected) {
                    obj.querySelector('#' + field.id + '_xyz').append(option);
                }
            });
            var val = [];
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            obj.querySelector('#' + field.id).value = val.join(field.separator);
        },
    }));
    obj.querySelector('.two').append(saltos.core.html('<br />'));
    obj.querySelector('.two').append(saltos.bootstrap.__field.button({
        class: `bi-chevron-double-left`,
        color: field.color,
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                if (option.selected) {
                    obj.querySelector('#' + field.id + '_abc').append(option);
                }
            });
            var val = [];
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            obj.querySelector('#' + field.id).value = val.join(field.separator);
        },
    }));
    obj.querySelector('.three').append(saltos.bootstrap.__field.select({
        color: field.color,
        id: field.id + '_xyz',
        disabled: field.disabled,
        tooltip: field.tooltip,
        multiple: true,
        size: field.size,
    }));
    saltos.core.when_visible(obj, () => {
        document.querySelectorAll('label[for=' + field.id + ']').forEach(_this => {
            _this.setAttribute('for', field.id + '_abc');
        });
    });
    // Program the set feature
    obj.querySelector('input[type=hidden]').set = value => {
        var values = value.split(field.separator);
        for (var key in values) {
            values[key] = values[key].trim();
        }
        obj.querySelectorAll('#' + field.id + '_abc option').forEach(option => {
            if (values.includes(option.value)) {
                obj.querySelector('#' + field.id + '_xyz').append(option);
            }
        });
        obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
            if (!values.includes(option.value)) {
                obj.querySelector('#' + field.id + '_abc').append(option);
            }
        });
        var val = [];
        obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
            val.push(option.value);
        });
        obj.querySelector('input[type=hidden]').value = val.join(field.separator);
    };
    obj.querySelector('input[type=hidden]').set(field.value);
    // Program the disabled feature
    obj.querySelector('input[type=hidden]').set_disabled = bool => {
        var temp = obj.querySelector('#' + field.id).parentElement.parentElement;
        temp.querySelectorAll('select,button').forEach(_this => {
            _this.set_disabled(bool);
        });
    };
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
 * @required  => this parameter raise the required flag
 * @label     => this parameter is used as label for the checkbox
 * @value     => this parameter is used to check or unckeck the checkbox, the value
 *               must contain a number that raise as true or false in the if condition
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onchange  => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget returns their value by setting a zero or one (0/1) value on the value of the input.
 */
saltos.bootstrap.__field.checkbox = field => {
    saltos.core.check_params(field, ['value', 'id', 'disabled', 'readonly', 'required', 'onchange',
                                     'label', 'tooltip', 'class', 'accesskey', 'color']);
    var disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    var readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    var required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    var value = 0;
    if (saltos.core.eval_bool(field.value)) {
        value = 1;
    }
    var checked = '';
    if (value) {
        checked = 'checked';
    }
    var color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    var border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <div class="form-check ${field.class}">
            <input class="form-check-input ${border}" type="checkbox" id="${field.id}"
                value="${value}" ${disabled} ${readonly} ${required} ${checked}
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
    if (field.onchange != '') {
        obj.querySelectorAll('input').forEach(_this => {
            saltos.bootstrap.__onchange_helper(_this, field.onchange);
        });
    }
    obj.querySelector('input').addEventListener('change', event => {
        event.target.value = event.target.checked ? 1 : 0;
    });
    obj.querySelector('input').set = bool => {
        var input = obj.querySelector('input');
        if (saltos.core.eval_bool(bool)) {
            input.checked = true;
            input.value = 1;
        } else {
            input.checked = false;
            input.value = 0;
        }
    };
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
 * @onchange  => the function executed when onchange event is detected
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
 * @label     => label to be used as text in the contents of the buttons
 * @onclick   => callback function that is executed when the button is pressed
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @collapse  => a boolean to enable or disable the collapse feature in the button
 * @target    => the id of the element controlled by the collapse feature
 * @addbr     => this special feature adds a void label with a new line tag to align the button with
 *               the other elements that are label+widget
 *
 * Notes:
 *
 * The buttons adds the focus-ring classes to use this new feature that solves issues suck as
 * hidden focus when you try to focus a button inside a modal, for example.
 */
saltos.bootstrap.__field.button = field => {
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'autofocus', 'onclick', 'tooltip',
                                     'icon', 'label', 'accesskey', 'color', 'collapse', 'target', 'addbr']);
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
    var collapse = '';
    if (saltos.core.eval_bool(field.collapse)) {
        collapse = `data-bs-toggle="collapse" data-bs-target="#${field.target}"
            aria-controls="${field.target}" aria-expanded="false"`;
    }
    var obj = saltos.core.html(`
        <button type="button" id="${field.id}" ${field.disabled} ${field.autofocus}
            class="btn btn-${field.color} focus-ring focus-ring-${field.color} ${field.class}"
            data-bs-accesskey="${field.accesskey}" ${collapse}
            data-bs-title="${field.tooltip}">${field.label}</button>
    `);
    if (field.icon) {
        obj.prepend(saltos.core.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.label && field.icon) {
        obj.querySelector('i').classList.add('me-1');
    }
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    saltos.bootstrap.__onclick_helper(obj, field.onclick);
    // Program the disabled feature
    obj.set_disabled = bool => {
        if (bool) {
            obj.setAttribute('disabled', '');
            obj.classList.add('opacity-25');
        } else {
            obj.removeAttribute('disabled');
            obj.classList.remove('opacity-25');
        }
    };
    if (saltos.core.eval_bool(field.addbr)) {
        var obj2 = saltos.core.html(`<div><label class="form-label">&nbsp;</label><br/></div>`);
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
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This widget add an icon to the end of the widget with an slashed eye, this allow to
 * see the entered password to verify it, in reality, this button swaps the input between
 * password and text type, allowing to do visible or not the contents of the input
 */
saltos.bootstrap.__field.password = field => {
    saltos.core.check_params(field, ['label', 'class', 'id', 'placeholder', 'value', 'disabled',
                                     'onenter', 'onchange', 'readonly', 'required',
                                     'autofocus', 'tooltip', 'accesskey', 'color']);
    var disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    var readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    var required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    var autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    var color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    var border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <div>
            <div class="input-group">
                <input type="password" class="form-control ${border} ${field.class}"
                    id="${field.id}" placeholder="${field.placeholder}" value="${field.value}"
                    ${disabled} ${readonly} ${required} ${autofocus}
                    aria-label="${field.placeholder}" aria-describedby="${field.id}_button"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
                <button class="btn btn-${color} bi-eye-slash" type="button"
                    id="${field.id}_button" data-bs-title="${field.tooltip}"></button>
            </div>
        </div>
    `);
    // Trick to prevent the browser password manager
    for (var i = 0; i < 10; i++) {
        var name = saltos.core.uniqid();
        var value = saltos.core.uniqid();
        obj.append(saltos.core.html(`
            <input type="password" name="${name}" value="${value}" class="d-none"/>
        `));
    }
    // Continue
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
    if (field.onchange != '') {
        obj.querySelectorAll('input[type=password]').forEach(_this => {
            saltos.bootstrap.__onchange_helper(_this, field.onchange);
        });
    }
    obj.querySelector('button').addEventListener('click', event => {
        var input = event.target.parentElement.querySelector('input[type=password], input[type=text]');
        switch (input.type) {
            case 'password':
                input.type = 'text';
                event.target.classList.replace('bi-eye-slash', 'bi-eye');
                break;
            case 'text':
                input.type = 'password';
                event.target.classList.replace('bi-eye', 'bi-eye-slash');
                break;
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
 * @onchange  => the function executed when onchange event is detected
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
    saltos.core.check_params(field, ['class', 'id', 'value', 'data', 'disabled', 'required', 'onchange',
                                     'autofocus', 'multiple', 'tooltip', 'accesskey', 'color']);
    var disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    var required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    var autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    var multiple = '';
    if (saltos.core.eval_bool(field.multiple)) {
        multiple = 'multiple';
    }
    var color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    var border1 = `border border-${color}`;
    var border2 = `border-${color}`;
    if (field.color == 'none') {
        border1 = 'border-0';
        border2 = '';
    }
    var obj = saltos.core.html(`
        <div>
            <input type="file" class="form-control ${border1} ${field.class}" id="${field.id}"
                ${disabled} ${required} ${autofocus} ${multiple}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            <div class="table-responsive">
                <table class="table table-striped table-hover ${border2} d-none mb-0">
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
            data.push(_this.data);
        });
        input.data = data;
    };
    __update_data_input_file(obj.querySelector('input'));
    // This helper programs the delete file button
    var __button_remove_file = event => {
        var row = event.target.parentElement.parentElement;
        var table = row.parentElement.parentElement;
        var input = table.parentElement.previousElementSibling;
        saltos.core.ajax({
            url: 'api/?upload/delfile',
            data: JSON.stringify(row.data),
            method: 'post',
            content_type: 'application/json',
            success: response => {
                if (!saltos.app.check_response(response)) {
                    return;
                }
                row.data = response;
                // If server removes the file, i remove the row
                if (response.file == '') {
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
            abort: request => {
                saltos.app.form.screen('unloading');
            },
            token: saltos.token.get(),
            lang: saltos.gettext.get(),
        });
    };
    // This helper paints each row of the table
    var __add_row_file = (input, table, file) => {
        // Show the table
        table.classList.remove('d-none');
        // Add the row for the new file
        var row = saltos.core.html('tbody', `
            <tr id="${file.id}">
                <td class="text-break">${file.name}</td>
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
        row.data = file;
        // Program de remove button
        row.querySelector('button').addEventListener('click', __button_remove_file);
        // Add the row
        table.querySelector('tbody').append(row);
        __update_data_input_file(input);
        return row;
    };
    // Program the automatic upload
    obj.querySelector('input').addEventListener('change', async event => {
        var input = event.target;
        var files = event.target.files;
        var table = event.target.nextElementSibling.querySelector('table');
        for (var i = 0; i < files.length; i++) {
            // Prepare the data to send
            var data = {
                id: saltos.core.uniqid(),
                app: saltos.hash.get(),
                name: files[i].name,
                size: files[i].size,
                type: files[i].type,
                data: '',
                error: '',
                file: '',
                hash: '',
            };
            // Add the row to the table
            var row = __add_row_file(input, table, data);
            // Get the local file using syncronous techniques
            var reader = new FileReader();
            reader.readAsDataURL(files[i]);
            while (!reader.result && !reader.error) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
            // If there is a file
            if (reader.result) {
                data.data = reader.result;
                // This allow multiple uploads in parallel
                ((data, row) => {
                    saltos.core.ajax({
                        url: 'api/?upload/addfile',
                        data: JSON.stringify(data),
                        method: 'post',
                        content_type: 'application/json',
                        success: response => {
                            if (!saltos.app.check_response(response)) {
                                return;
                            }
                            row.data = response;
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
                        abort: request => {
                            saltos.app.form.screen('unloading');
                        },
                        token: saltos.token.get(),
                        lang: saltos.gettext.get(),
                    });
                })(data, row);
            }
            // If there is an error
            if (reader.error) {
                data.error = reader.error.message;
                throw new Error(reader.error);
            }
        }
        input.value = '';
    });
    // Program the set function
    obj.querySelector('input').set = data => {
        for (var i in data) {
            var input = obj.querySelector('input');
            var table = input.nextElementSibling.querySelector('table');
            var row = __add_row_file(input, table, data[i]);
            var percent = 100;
            row.querySelector('.progress-bar').style.width = percent + '%';
            row.querySelector('.progress').setAttribute('aria-valuenow', percent);
        }
    };
    // Initialize the input with the previous function
    obj.querySelector('input').set(field.data);
    // Added the onchange event
    if (field.onchange != '') {
        obj.querySelectorAll('input[type=file]').forEach(_this => {
            saltos.bootstrap.__onchange_helper(_this, field.onchange);
        });
    }
    // Continue
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
 */
saltos.bootstrap.__field.label = field => {
    saltos.core.check_params(field, ['id', 'class', 'label', 'tooltip']);
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
    saltos.core.check_params(field, ['id', 'class', 'value', 'data', 'required',
                                     'rowHeaders', 'colHeaders', 'minSpareRows',
                                     'contextMenu', 'rowHeaderWidth', 'colWidths',
                                     'numcols', 'numrows', 'color', 'cell', 'cells']);
    if (!field.color) {
        field.color = 'primary';
    }
    var border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <div style="width: 100%; height: 100%; overflow: auto" class="${border}">
            <div></div>
        </div>
    `);
    var input = saltos.bootstrap.__field.hidden(saltos.core.copy_object(field));
    obj.prepend(input);
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
        field.contextMenu = false;
    }
    if (field.rowHeaderWidth == '') {
        field.rowHeaderWidth = undefined;
    } else {
        field.rowHeaderWidth = parseInt(field.rowHeaderWidth);
    }
    if (field.colWidths == '') {
        field.colWidths = undefined;
    } else {
        field.colWidths = parseInt(field.colWidths);
    }
    input.data = saltos.core.copy_object(field.data);
    var element = obj.querySelector('div');
    saltos.core.when_visible(element, () => {
        var excel = new Handsontable(element, {
            data: input.data, // This links the data
            rowHeaders: field.rowHeaders,
            colHeaders: field.colHeaders,
            minSpareRows: field.minSpareRows,
            contextMenu: field.contextMenu,
            rowHeaderWidth: field.rowHeaderWidth,
            colWidths: field.colWidths,
            autoWrapCol: false,
            autoWrapRow: false,
            cell: field.cell,
            cells: field.cells,
            // I maintain the follow commented lines as an example of usage
            /*enterMoves: {row: 0, col: 1},*/
            /*cell: [{
                col: 1,
                row: 0,
                type: 'dropdown',
                source: ['Allow', 'Deny'],
                readOnly: true,
            }],*/
            /*cells: (row, col, prop) => {
                if (row === 0 && col === 0) {
                    console.log([row,col,prop]);
                    return {
                        readOnly: true,
                        editor: 'select',
                        selectOptions: ['Allow', 'Deny'],
                        type: 'dropdown',
                        source: ['Allow', 'Deny'],
                    };
                }
            },*/
        });
        input.excel = excel;
        // Program the disabled feature
        input.set_disabled = bool => {
            if (bool) {
                input.excel.updateSettings({
                    cells: (row, col, prop) => {
                        return {
                            readOnly: true,
                            readOnlyCellClassName: 'bg-body-secondary',
                        };
                    },
                });
            } else {
                input.excel.updateSettings({
                    cells: (row, col, prop) => {
                        return {
                            readOnly: false,
                            readOnlyCellClassName: '',
                        };
                    },
                });
            }
        };
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
 *
 * Change scale causes issues in scrollTop when pdfjs is used inside a modal, to prevent this,
 * the two updates to the pdfViewer.currentScaleValue = 'update' will add a control to fix
 * that modal scrollTop is the same.
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
    if (typeof field.src == 'string') {
        obj.src = new URL(field.src, document.location.href).href;
    }
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
                var modal = document.querySelector('.modal');
                if (modal) {
                    var scrollTop = modal.scrollTop;
                }
                pdfViewer.currentScaleValue = 'auto';
                if (modal) {
                    modal.scrollTop = scrollTop;
                }
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
                var modal = document.querySelector('.modal');
                if (modal) {
                    var scrollTop = modal.scrollTop;
                }
                pdfViewer.currentScaleValue = 'auto';
                if (modal) {
                    modal.scrollTop = scrollTop;
                }
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
 * @nodata   => text used when no data is found
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
    saltos.core.check_params(field, ['class', 'id', 'checkbox', 'dropdown', 'color', 'nodata']);
    saltos.core.check_params(field, ['header', 'data', 'footer'], []);
    // Check for data not found
    if (!field.data.length) {
        return saltos.bootstrap.__field.alert({
            id: field.id,
            color: field.color,
            title: field.nodata,
        });
    }
    // Continue
    if (field.checkbox != '') {
        field.checkbox = saltos.core.eval_bool(field.checkbox);
    }
    if (!field.color) {
        field.color = 'primary';
    }
    // This creates a responsive table (a table inside a div with table-responsive class)
    // We are using the same div to put inside the overlodaded styles of the table
    var obj = saltos.core.html(`
        <div id="${field.id}" class="table-responsive">
            <table class="table table-striped table-hover border-${field.color} ${field.class} mb-0">
            </table>
        </div>
    `);
    if (Object.keys(field.header).length) {
        obj.querySelector('table').append(saltos.core.html('table', `
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
            obj.querySelector('thead input[type=checkbox]').parentElement.addEventListener('click', event => {
                event.target.querySelector('input[type=checkbox]').click();
                event.stopPropagation();
            });
        }
        for (var key in field.header) {
            field.header[key] = saltos.core.join_attr_value(field.header[key]);
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
        obj.querySelector('table').append(saltos.core.html('table', `
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
                    event.target.parentElement.parentElement.querySelectorAll('td').forEach(_this => {
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
                        var obj = event.target.parentElement.parentElement.parentElement;
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
                    var obj = event.target.parentElement.querySelector('input[type=checkbox]');
                    if (obj) {
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
                    switch (type) {
                        case 'icon':
                            if (val2) {
                                var temp = saltos.core.html(`<i class="bi bi-${val2}"></i>`);
                                td.append(temp);
                            }
                            break;
                        case 'html':
                            if (val2) {
                                var temp = saltos.core.html(val2);
                                td.append(temp);
                            }
                            break;
                        case 'text':
                            if (val2) {
                                td.append(val2);
                            }
                            break;
                        default:
                            var temp = `unknown type ${type}`;
                            td.append(temp);
                            break;
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
                                //~ eval(event.target.parentElement.getAttribute('_onclick'));
                                (new Function(event.target.parentElement.getAttribute('_onclick'))).call(event.target);
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
                        button.classList.replace('btn', 'dropdown-item');
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
        obj.querySelector('table').append(saltos.core.html('table', `
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
                obj.querySelector('tfoot tr').append(saltos.core.html(
                    'tr',
                    `<td class="bg-${field.color}-subtle"></td>`
                ));
            }
            // This is to allow to use tables with footer and without header
            var iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = field.footer;
            }
            for (var key in iterator) {
                field.footer[key] = saltos.core.join_attr_value(field.footer[key]);
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
                obj.querySelector('tfoot tr').append(saltos.core.html(
                    'tr',
                    `<td class="bg-${field.color}-subtle"></td>`
                ));
            }
        }
        if (typeof field.footer == 'string') {
            obj.querySelector('tfoot tr').append(saltos.core.html(
                'tr',
                `<td colspan="100" class="text-center bg-${field.color}-subtle">${field.footer}</td>`
            ));
        }
    }
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
    // Continue
    obj = saltos.bootstrap.__label_combine(field, obj);
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
        <div class="alert alert-${field.color} ${field.class} mb-0" role="alert" id="${field.id}"></div>
    `);
    if (field.title != '') {
        obj.append(saltos.core.html(`<h4>${field.title}</h4>`));
        if (field.text + field.body == '') {
            obj.querySelector('h4').classList.add('mb-0');
        }
    }
    if (field.text != '') {
        obj.append(saltos.core.html(`<p>${field.text}</p>`));
        if (field.body == '') {
            obj.querySelector('p').classList.add('mb-0');
        }
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
 * @separator   => the separator string used to split and join the tags
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This object creates a hidden input, a text input with/without a datalist, and a badge for
 * each value, and requires the arguments of the specific widgets used in this widget
 *
 * The returned object contains a hiden input with one new properties like set, this is a
 * function used to update the value of the tags widget, intended to load new data.
 */
saltos.bootstrap.__field.tags = field => {
    saltos.core.check_params(field, ['id', 'value', 'color', 'separator']);
    if (!field.color) {
        field.color = 'primary';
    }
    if (!field.separator) {
        field.separator = ',';
    }
    // This container must have the hidden input and the text input used by the
    // user to write the tags
    var obj = saltos.core.html(`<div></div>`);
    // The first field is the hidden input
    var field_first = saltos.core.copy_object(field);
    field_first.class = 'first';
    field_first.required = false;
    obj.append(saltos.bootstrap.__field.hidden(field_first));
    // The last field is the text input used to write the tags
    var field_last = saltos.core.copy_object(field);
    field_last.id = field.id + '_tags';
    field_last.value = '';
    field_last.class = 'last';
    obj.append(saltos.bootstrap.__field.text(field_last));
    // This function draws a tag and programs the delete of the same tag
    var fn = val => {
        var span = saltos.core.html(`
            <span class="badge text-bg-${field.color} mt-1 me-1 fs-6 fw-normal pe-2" data="${val}">
                <i class="bi bi-x-circle ps-1" style="cursor: pointer"></i>
            </span>
        `);
        span.prepend(val);
        obj.append(span);
        span.querySelector('i').addEventListener('click', event => {
            var tag = event.target.parentElement;
            var val = tag.getAttribute('data').trim();
            var input = obj.querySelector('input.first');
            var val_old = input.value.split(field.separator);
            var val_new = [];
            for (var key in val_old) {
                val_old[key] = val_old[key].trim();
                if (![val, ''].includes(val_old[key])) {
                    val_new.push(val_old[key]);
                }
            }
            input.value = val_new.join(field.separator);
            tag.remove();
        });
    };
    // This function program the enter event that adds tags to the hidden and
    // draw the new tag using the previous function
    obj.querySelector('input.last').addEventListener('keydown', event => {
        if (![13, 9].includes(saltos.core.get_keycode(event))) {
            return;
        }
        var input_new = obj.querySelector('input.last');
        var val = input_new.value.trim();
        if (val == '') {
            return;
        }
        var input = obj.querySelector('input.first');
        var val_old = input.value.split(field.separator);
        var val_new = [];
        for (var key in val_old) {
            val_old[key] = val_old[key].trim();
            if (![val, ''].includes(val_old[key])) {
                val_new.push(val_old[key]);
            }
            if (val_old[key] == val) {
                return;
            }
        }
        fn(val);
        val_new.push(val);
        input.value = val_new.join(field.separator);
        input_new.value = '';
    });
    // Program the set in the input first
    obj.querySelector('input.first').set = value => {
        obj.querySelector('input.first').value = value;
        obj.querySelectorAll('span.badge').forEach(_this => {
            _this.remove();
        });
        var value_array = value.split(field.separator);
        if (value == '') {
            value_array = [];
        }
        for (var key in value_array) {
            var val = value_array[key].trim();
            fn(val);
        }
    };
    // This part of the code adds the initials tags using the fn function
    obj.querySelector('input.first').set(field.value);
    // Program the disabled feature
    obj.querySelector('input.first').set_disabled = bool => {
        if (bool) {
            obj.querySelector('input.last').setAttribute('disabled', '');
        } else {
            obj.querySelector('input.last').removeAttribute('disabled');
        }
    };
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
 * List widget constructor helper
 *
 * Returns a list widget using the follow params:
 *
 * @id       => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default list-group
 * @onclick  => this parameter allow to enable or disable the buttons in the list
 * @data     => 2D array with the data used to mount the list
 * @truncate => this parameter add the text-truncate to all texts of the items
 * @checkbox => add a checkbox in the first cell of each row, for mono or multi selection
 * @nodata   => text used when no data is found
 * @label    => this parameter is used as text for the label
 *
 * Each item in the data can contain:
 *
 * @header   => string with the header to use
 * @body     => string with the data to use
 * @footer   => string with the footer to use
 * @onclick  => the onclick function that receives as argument the url to access the action
 * @url      => this parameter is used as argument for the onclick function
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 * @actions  => this parameter allow to recicle the actions feature of the list action
 * @id       => the id used to set the reference for each checkbox
 *
 * As an extra fields, the widget allow to provide multiple texts and icons
 *
 * @header_text  => an small text added at the end of the same line of the header
 * @header_icon  => an small icon added at the end of the same line of the header
 * @header_color => the color used in the previous small text and icon
 * @body_text    => an small text added at the end of the same line of the body
 * @body_icon    => an small icon added at the end of the same line of the body
 * @body_color   => the color used in the previous small text and icon
 * @footer_text  => an small text added at the end of the same line of the footer
 * @footer_icon  => an small icon added at the end of the same line of the footer
 * @footer_color => the color used in the previous small text and icon
 *
 * Notes:
 *
 * The first onclick parameter is used to raise the construction of the widget and items,
 * depending of this parameter, the function uses a dir or an ul element to do the list
 */
saltos.bootstrap.__field.list = field => {
    saltos.core.check_params(field, ['class', 'id', 'onclick', 'truncate', 'checkbox', 'nodata']);
    saltos.core.check_params(field, ['data'], []);
    // Check for data not found
    if (!field.data.length) {
        return saltos.bootstrap.__field.alert({
            id: field.id,
            title: field.nodata,
        });
    }
    // Continue
    if (saltos.core.eval_bool(field.onclick)) {
        var obj = saltos.core.html(`<div id="${field.id}" class="list-group ${field.class}"></div>`);
    } else {
        var obj = saltos.core.html(`<ul id="${field.id}" class="list-group ${field.class}"></ul>`);
    }
    for (var key in field.data) {
        var val = field.data[key];
        saltos.core.check_params(val, ['header', 'body', 'footer', 'class',
            'header_text', 'header_icon', 'header_color',
            'body_text', 'body_icon', 'body_color',
            'footer_text', 'footer_icon', 'footer_color',
            'onclick', 'url', 'active', 'disabled', 'actions', 'id']);
        if (saltos.core.eval_bool(field.onclick)) {
            var item = saltos.core.html(`<button
                class="list-group-item list-group-item-action ${val.class}"></button>`);
            if (val.hasOwnProperty('actions') && val.actions.hasOwnProperty('0') &&
                val.actions[0].hasOwnProperty('onclick') && val.actions[0].hasOwnProperty('url')) {
                val.onclick = val.actions[0].onclick;
                val.url = val.actions[0].url;
            }
            if (val.url != '') {
                val.onclick = `${val.onclick}("${val.url}")`;
            }
            saltos.bootstrap.__onclick_helper(item, val.onclick);
            // To prevent that the button remain focused
            saltos.bootstrap.__onclick_helper(item, function() {
                this.parentElement.parentElement.querySelectorAll('button').forEach(_this => {
                    _this.classList.remove('active');
                    _this.removeAttribute('aria-current');
                });
                this.classList.add('active');
                this.setAttribute('aria-current', 'true');
            });
            if (saltos.core.eval_bool(field.checkbox)) {
                if (val.id == '') {
                    val.id = saltos.core.uniqid();
                }
                item.setAttribute('id', `button_${val.id}`);
            }
        } else {
            var item = saltos.core.html(`<li class="list-group-item ${val.class}"></li>`);
        }
        if (val.header != '') {
            var temp = saltos.core.html(`
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1 ${val.class}"></h5>
                </div>
            `);
            temp.querySelector('h5').append(val.header);
            if (saltos.core.eval_bool(field.truncate)) {
                temp.querySelector('h5').classList.add('text-truncate');
            }
            if (val.header_text != '' && val.header_icon != '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap">
                        <small class="text-${val.header_color}">${val.header_text}</small>
                        <i class="bi bi-${val.header_icon} text-${val.header_color}"></i>
                    </div>
                `));
            } else if (val.header_text != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.header_color}">${val.header_text}</small>
                `));
            } else if (val.header_icon != '') {
                temp.append(saltos.core.html(`
                    <i class="bi bi-${val.header_icon} text-${val.header_color}"></i>
                `));
            }
            item.append(temp);
        }
        if (val.body != '') {
            var temp = saltos.core.html(`
                <div class="d-flex w-100 justify-content-between">
                    <p class="mb-1"></p>
                </div>
            `);
            temp.querySelector('p').append(val.body);
            if (saltos.core.eval_bool(field.truncate)) {
                temp.querySelector('p').classList.add('text-truncate');
            }
            if (val.body_text != '' && val.body_icon != '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap">
                        <small class="text-${val.body_color}">${val.body_text}</small>
                        <i class="bi bi-${val.body_icon} text-${val.body_color}"></i>
                    </div>
                `));
            } else if (val.body_text != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.body_color}">${val.body_text}</small>
                `));
            } else if (val.body_icon != '') {
                temp.append(saltos.core.html(`
                    <i class="bi bi-${val.body_icon} text-${val.body_color}"></i>
                `));
            }
            item.append(temp);
        }
        if (val.footer != '') {
            var temp = saltos.core.html(`
                <div class="d-flex w-100 justify-content-between">
                    <small></small>
                </div>
            `);
            temp.querySelector('small').append(val.footer);
            if (saltos.core.eval_bool(field.truncate)) {
                temp.querySelector('small').classList.add('text-truncate');
            }
            if (val.footer_text != '' && val.footer_icon != '') {
                temp.append(saltos.core.html(`
                    <div class="text-nowrap">
                        <small class="text-${val.footer_color}">${val.footer_text}</small>
                        <i class="bi bi-${val.footer_icon} text-${val.footer_color}"></i>
                    </div>
                `));
            } else if (val.footer_text != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.footer_color}">${val.footer_text}</small>
                `));
            } else if (val.footer_icon != '') {
                temp.append(saltos.core.html(`
                    <i class="bi bi-${val.footer_icon} text-${val.footer_color}"></i>
                `));
            }
            item.append(temp);
        }
        if (saltos.core.eval_bool(val.active)) {
            item.classList.add('active');
            item.setAttribute('aria-current', 'true');
        }
        if (saltos.core.eval_bool(val.disabled)) {
            item.classList.add('disabled');
            item.setAttribute('aria-disabled', 'true');
        }
        obj.append(item);
    }
    // The follow code allow to colorize the hover and active rows of the list
    obj.append(saltos.core.html(`
        <style>
            .list-group {
                --bs-list-group-action-hover-bg: #fbec88;
                --bs-list-group-action-active-bg: #fbec88;
                --bs-list-group-action-hover-color: #373a3c;
                --bs-list-group-action-active-color: #373a3c;
            }
            .list-group-item:nth-child(odd) {
                --bs-list-group-bg: rgba(var(--bs-emphasis-color-rgb), 0.05);
            }
            .list-group-item.active h5 {
                color: inherit;
            }
        </style>
    `));
    if (saltos.core.eval_bool(field.checkbox)) {
        obj.classList.add('rounded-0');
        saltos.core.when_visible(obj, () => {
            obj.classList.add('position-relative');
            for (var key in field.data) {
                var val = field.data[key];
                obj.append(saltos.core.html(`
                    <div class="position-absolute p-2">
                        <input class="form-check-input" type="checkbox"
                            value="${val.id}" id="checkbox_${val.id}">
                    </div>
                `));
                var button = obj.querySelector(`#button_${val.id}`);
                var checkbox = obj.querySelector(`#checkbox_${val.id}`);
                checkbox.parentElement.style.height = button.offsetHeight + 'px';
                checkbox.parentElement.style.top = button.offsetTop + 'px';
                var width = checkbox.parentElement.offsetWidth;
                button.style.paddingLeft = width + 'px';
                checkbox.parentElement.style.zIndex = 201;
                button.style.zIndex = 200;
                checkbox.addEventListener('change', event => {
                    var button = event.target.id.replace('checkbox', 'button');
                    if (event.target.checked) {
                        document.getElementById(button).style.background =
                            'var(--bs-list-group-action-active-bg)';
                        document.getElementById(button).style.color =
                            'var(--bs-list-group-action-active-color)';
                    } else {
                        document.getElementById(button).style.background = '';
                        document.getElementById(button).style.color = '';
                    }
                });
                checkbox.addEventListener('click', event => {
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
                        var obj = event.target.parentElement.parentElement.parentElement;
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
                checkbox.parentElement.addEventListener('click', event => {
                    var obj = event.target.querySelector('input[type=checkbox]');
                    if (obj) {
                        // ctrlKey propagation is important to allow the multiple selection feature
                        obj.dispatchEvent(new MouseEvent('click', {ctrlKey: event.ctrlKey}));
                        // The next focus allow to continue navigating by the other checkboxes
                        obj.focus();
                    }
                    event.stopPropagation();
                });
            }
        });
    }
    // Continue
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Tabs widget constructor helper
 *
 * Returns a tabs widget using the follow params:
 *
 * @id    => the id used to set the reference for to the object
 * @items => 2D array with the data used to mount the tab and content
 * @label    => this parameter is used as text for the label
 *
 * Each item in the tabs can contain:
 *
 * @name     => string with the text name to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 */
saltos.bootstrap.__field.tabs = field => {
    saltos.core.check_params(field, ['id', 'type']);
    saltos.core.check_params(field, ['items'], []);
    var obj = saltos.core.html(`
        <ul class="nav nav-${field.type} mb-3" id="${field.id}-tab" role="tablist"></ul>
        <div class="tab-content" id="${field.id}-content"></div>
    `);
    for (var key in field.items) {
        var val = field.items[key];
        val = saltos.core.join_attr_value(val);
        saltos.core.check_params(val, ['name', 'content', 'active', 'disabled']);
        var active = '';
        var selected = 'false';
        var show = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
            selected = 'true';
            show = 'show';
        }
        var disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        var id = saltos.core.uniqid();
        obj.querySelector('ul.nav').append(saltos.core.html(`
            <li class="nav-item" role="presentation">
                <button class="nav-link ${active} text-nowrap" id="${field.id}-${id}-tab"
                    data-bs-toggle="pill" data-bs-target="#${field.id}-${id}"
                    type="button" role="tab" aria-controls="${field.id}-${id}"
                    aria-selected="${selected}" ${disabled}>${val.name}</button>
            </li>
        `));
        var div = saltos.core.html(`
            <div class="tab-pane fade ${show} ${active}" id="${field.id}-${id}"
                role="tabpanel" aria-labelledby="${field.id}-${id}-tab" tabindex="0">
            </div>
        `);
        div.append(val.content);
        obj.querySelector('div.tab-content').append(div);
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Pills widget constructor helper
 *
 * Returns a tabs widget using the follow params:
 *
 * @id    => the id used to set the reference for to the object
 * @items => 2D array with the data used to mount the tab and content
 * @label    => this parameter is used as text for the label
 *
 * Each item in the tabs can contain:
 *
 * @name     => string with the text name to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 */
saltos.bootstrap.__field.pills = field => {
    return saltos.bootstrap.__field.tabs(field);
};

/**
 * V-Pills widget constructor helper
 *
 * Returns a tabs widget using the follow params:
 *
 * @id    => the id used to set the reference for to the object
 * @items => 2D array with the data used to mount the tab and content
 * @label    => this parameter is used as text for the label
 *
 * Each item in the tabs can contain:
 *
 * @name     => string with the text name to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 */
saltos.bootstrap.__field['v-pills'] = field => {
    saltos.core.check_params(field, ['id']);
    saltos.core.check_params(field, ['items'], []);
    var obj = saltos.core.html(`
        <div class="d-flex align-items-start">
            <div class="nav flex-column nav-pills me-3" id="${field.id}-tab"
                role="tablist" aria-orientation="vertical"></div>
            <div class="tab-content" id="${field.id}-content"></div>
        </div>
    `);
    for (var key in field.items) {
        var val = field.items[key];
        val = saltos.core.join_attr_value(val);
        saltos.core.check_params(val, ['name', 'content', 'active', 'disabled']);
        var active = '';
        var selected = 'false';
        var show = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
            selected = 'true';
            show = 'show';
        }
        var disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        var id = saltos.core.uniqid();
        obj.querySelector('div.nav').append(saltos.core.html(`
            <button class="nav-link ${active} text-nowrap" id="${field.id}-${id}-tab"
                data-bs-toggle="pill" data-bs-target="#${field.id}-${id}"
                type="button" role="tab" aria-controls="${field.id}-${id}"
                aria-selected="${selected}" ${disabled}>${val.name}</button>
        `));
        var div = saltos.core.html(`
            <div class="tab-pane fade ${show} ${active}" id="${field.id}-${id}"
                role="tabpanel" aria-labelledby="${field.id}-${id}-tab" tabindex="0">
            </div>
        `);
        div.append(val.content);
        obj.querySelector('div.tab-content').append(div);
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Accordion widget constructor helper
 *
 * Returns an accordion widget using the follow params:
 *
 * @id       => the id used to set the reference for to the object
 * @flush    => if true, add the accordion-flush to the widget
 * @multiple => if true, allow to have open multiple items at same time
 * @items    => 2D array with the data used to mount the accordion and content
 * @label    => this parameter is used as text for the label
 *
 * Each item in the tabs can contain:
 *
 * @name     => string with the text name to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 */
saltos.bootstrap.__field.accordion = field => {
    saltos.core.check_params(field, ['id', 'flush', 'multiple']);
    saltos.core.check_params(field, ['items'], []);
    if (saltos.core.eval_bool(field.flush)) {
        field.flush = 'accordion-flush';
    }
    var obj = saltos.core.html(`
        <div class="accordion ${field.flush}" id="${field.id}"></div>
    `);
    for (var key in field.items) {
        var val = field.items[key];
        val = saltos.core.join_attr_value(val);
        saltos.core.check_params(val, ['name', 'content', 'active']);
        var collapsed = 'collapsed';
        var expanded = 'false';
        var show = '';
        if (saltos.core.eval_bool(val.active)) {
            collapsed = '';
            expanded = 'true';
            show = 'show';
        }
        var parent = `data-bs-parent="#${field.id}"`;
        if (saltos.core.eval_bool(field.multiple)) {
            parent = '';
        }
        var id = saltos.core.uniqid();
        var item = saltos.core.html(`
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${collapsed}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#${field.id}-${id}"
                        aria-expanded="${expanded}" aria-controls="${field.id}-${id}">
                    </button>
                </h2>
                <div id="${field.id}-${id}" class="accordion-collapse collapse ${show}" ${parent}>
                    <div class="accordion-body">
                    </div>
                </div>
            </div>
        `);
        item.querySelector('.accordion-button').append(val.name);
        item.querySelector('.accordion-body').append(val.content);
        obj.append(item);
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
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
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__text_helper = field => {
    saltos.core.check_params(field, ['type', 'class', 'id', 'placeholder', 'value',
                                     'disabled', 'onenter', 'onchange', 'readonly', 'required',
                                     'autofocus', 'tooltip', 'accesskey', 'color']);
    var disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    var readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    var required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    var autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    var color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    var border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <input type="${field.type}" class="form-control ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${disabled} ${readonly} ${required} ${autofocus}
            id="${field.id}" data-bs-title="${field.tooltip}" value="${field.value}" />
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    if (field.onenter != '') {
        saltos.bootstrap.__onenter_helper(obj, field.onenter);
    }
    if (field.onchange != '') {
        saltos.bootstrap.__onchange_helper(obj, field.onchange);
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
 * @onchange    => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__textarea_helper = field => {
    saltos.core.check_params(field, ['class', 'id', 'placeholder', 'value', 'onchange',
                                     'disabled', 'readonly', 'required', 'autofocus',
                                     'tooltip', 'accesskey', 'color']);
    var disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    var readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    var required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    var autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    var color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    var border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    var obj = saltos.core.html(`
        <textarea class="form-control ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${disabled} ${readonly} ${required} ${autofocus}
            id="${field.id}" data-bs-title="${field.tooltip}">${field.value}</textarea>
    `);
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
    if (field.onchange != '') {
        saltos.bootstrap.__onchange_helper(obj, field.onchange);
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
 * Onchange helper
 *
 * This function is a helper function that adds the onchange event listener to the obj
 * using the correct way to do it, to do it, checks the type of fn.
 *
 * @obj   => the object where you want to add the onchange event
 * @fn    => the function that must be executed when onchange
 */
saltos.bootstrap.__onchange_helper = (obj, fn) => {
    if (typeof fn == 'string') {
        obj.addEventListener('change', new Function(fn));
        return;
    }
    if (typeof fn == 'function') {
        obj.addEventListener('change', fn);
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
            //~ eval(fn);
            (new Function(fn)).call(obj);
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
 * @id                => id used in the button element
 * @icon              => icon of the menu
 * @disabled          => this boolean allow to disable this menu entry
 * @active            => this boolean marks the option as active
 * @onclick           => the callback used when the user select the menu
 * @dropdown_menu_end => this trick allow to open the dropdown menu from the end to start
 * @menu              => with this option, you can specify an array with the contents of the dropdown menu
 *
 * @name     => name of the menu
 * @id       => id used in the button element
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
        saltos.core.check_params(val, ['name', 'icon',
            'disabled', 'active', 'onclick', 'dropdown_menu_end', 'id']);
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
                    <button id="${val.id}" class="nav-link dropdown-toggle ${disabled} ${active}"
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
                saltos.core.check_params(val2, ['name', 'icon',
                    'disabled', 'active', 'onclick', 'divider', 'id']);
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
                            <button id="${val2.id}" class="dropdown-item ${disabled2} ${active2}">
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
                    <button id="${val.id}" class="nav-link ${disabled} ${active}">${val.name}</button>
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
 * @brand => contains an object with the name, logo, width and height to be used
 * @color => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @pos   => position of the navbar, can be fixed-top, fixed-bottom, sticky-top, sticky-bottom
 * @class => class added to the navbar item
 *
 * @name   => text used in the brand
 * @logo   => filename of the brand image
 * @alt    => alt text used in the brand image
 * @width  => width of the brand image
 * @height => height of the brand image
 * @class  => class added to the navbar-brand item
 *
 * @items => contains an array with the objects that will be added to the collapse
 *
 * Notes:
 *
 * If you want to use a logo that uses all height of the navbar, you can set the class and
 * brand.class to py-0, the main idea is to use a combination of paddings with a brand to
 * gets a navbar of 56px of height
 */
saltos.bootstrap.navbar = args => {
    saltos.core.check_params(args, ['id', 'color', 'pos', 'class']);
    saltos.core.check_params(args, ['brand'], {});
    saltos.core.check_params(args.brand, ['name', 'logo', 'alt', 'width', 'height', 'class']);
    saltos.core.check_params(args, ['items'], []);
    if (!args.color) {
        args.color = 'primary';
    }
    var obj = saltos.core.html(`
        <nav class="navbar navbar-expand-md navbar-dark bg-${args.color} ${args.pos} ${args.class}">
            <div class="container-fluid">
                <div class="navbar-brand ${args.brand.class}">
                    <img src="${args.brand.logo}" alt="${args.brand.alt}"
                        width="${args.brand.width}" height="${args.brand.height}" />
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
    // Note: removed the fade class in the first div, the old class was "modal fade"
    var obj = saltos.core.html(`
        <div class="modal" id="${args.id}" tabindex="-1" aria-labelledby="${args.id}_label"
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
        if (args.body != '') {
            obj.querySelector('.modal-body').append(saltos.core.html(args.body));
        }
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
 * @id       => the id used by the object
 * @pos      => allow to specify the position of the offcanvac (start, end, top or bottom)
 * @title    => title used by the offcanvas
 * @close    => text used in the close button for aria purposes
 * @body     => the content used in the offcanvas's body
 * @static   => forces the offcanvas to be static (prevent close by clicking outside the
 *              offcanvas or by pressing the escape key)
 * @backdrop => to configure the backdrop feature (true or false)
 * @keyboard => to configure the keyboard feature (true or false)
 * @color    => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @resize   => the resize allow to the offcanvas to resize the contents of the screen to prevent
 *              offcanvas from hiding things
 *
 * Returns a boolean that indicates if the offcanvas can be open or not
 *
 * Notes:
 *
 * This offcanvas will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one offcanvas is allowed at each moment.
 *
 * Body allow to use a string containing a html fragment or an object, as the modal body.
 *
 * The resize option only works with start and end positions, too you can use left or right
 * as replacements for start and end positions, the resize will be disabled in top or bottom
 * positions.
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
    saltos.core.check_params(args, ['id', 'pos', 'title', 'close', 'body', 'color',
                                    'resize', 'static', 'backdrop', 'keyboard']);
    var temp = [];
    if (saltos.core.eval_bool(args.static)) {
        temp.push(`data-bs-backdrop="static"`);
        temp.push(`data-bs-keyboard="false"`);
    }
    if (saltos.core.eval_bool(args.backdrop)) {
        temp.push(`data-bs-backdrop="false"`);
        temp.push(`data-bs-keyboard="false"`);
    }
    if (saltos.core.eval_bool(args.keyboard)) {
        temp.push(`data-bs-keyboard="false"`);
    }
    temp = temp.join(' ');
    var valid_positions = ['start', 'end', 'top', 'bottom', 'left', 'right'];
    if (!valid_positions.includes(args.pos)) {
        args.pos = valid_positions[0];
    }
    if (args.pos == 'left') {
        args.pos = 'start';
    }
    if (args.pos == 'right') {
        args.pos = 'end';
    }
    if (saltos.core.eval_bool(args.resize) && !['start', 'end'].includes(args.pos)) {
        args.resize = false;
    }
    if (!args.color) {
        args.color = 'primary';
    }
    var obj = saltos.core.html(`
        <div class="offcanvas offcanvas-${args.pos}" tabindex="-1" id="${args.id}"
            aria-labelledby="${args.id}_label" ${temp}>
            <div class="offcanvas-header text-bg-${args.color}">
                <h5 class="offcanvas-title" id="${args.id}_label">${args.title}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="${args.close}"></button>
            </div>
            <div class="offcanvas-body"></div>
        </div>
    `);
    document.body.append(obj);
    if (typeof args.body == 'string') {
        if (args.body != '') {
            obj.querySelector('.offcanvas-body').append(saltos.core.html(args.body));
        }
    } else {
        obj.querySelector('.offcanvas-body').append(args.body);
    }
    var instance = new bootstrap.Offcanvas(obj);
    saltos.bootstrap.__offcanvas.obj = obj;
    saltos.bootstrap.__offcanvas.instance = instance;
    obj.addEventListener('shown.bs.offcanvas', event => {
        if (saltos.core.eval_bool(args.resize)) {
            var width = obj.offsetWidth;
            var item = document.body.firstChild;
            item.classList.add('position-absolute');
            if (args.pos == 'start') {
                item.style.left = `${width}px`;
            }
            if (args.pos == 'end') {
                item.style.left = '0px';
            }
            item.style.width = `calc(100% - ${width}px)`;
        }
        obj.querySelectorAll('[autofocus]').forEach(_this => {
            _this.focus();
        });
    });
    obj.addEventListener('hidden.bs.offcanvas', event => {
        if (saltos.core.eval_bool(args.resize)) {
            var item = document.body.firstChild;
            item.classList.remove('position-absolute');
            item.style.left = '';
            item.style.width = '';
        }
        saltos.bootstrap.__offcanvas.instance.dispose();
        saltos.bootstrap.__offcanvas.obj.remove();
        delete saltos.bootstrap.__offcanvas.instance;
        delete saltos.bootstrap.__offcanvas.obj;
    });
    obj.append(saltos.core.html(`
        <style>
            .offcanvas,
            .offcanvas-backdrop.fade {
                transition: none;
            }
        </style>
    `));
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
        if (args.body != '') {
            obj.querySelector('.toast-body').append(saltos.core.html(args.body));
        }
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
        'backspace': 8, 'tab': 9, 'enter': 13, 'pauseBreak': 19, 'capsLock': 20, 'escape': 27,
        'space': 32, 'pageUp': 33, 'pageDown': 34, 'end': 35, 'home': 36, 'leftArrow': 37,
        'upArrow': 38, 'rightArrow': 39, 'downArrow': 40, 'insert': 45, 'delete': 46,
        '0': 48, '1': 49, '2': 50, '3': 51, '4': 52, '5': 53, '6': 54, '7': 55, '8': 56,
        '9': 57, 'a': 65, 'b': 66, 'c': 67, 'd': 68, 'e': 69, 'f': 70, 'g': 71, 'h': 72,
        'i': 73, 'j': 74, 'k': 75, 'l': 76, 'm': 77, 'n': 78, 'o': 79, 'p': 80, 'q': 81,
        'r': 82, 's': 83, 't': 84, 'u': 85, 'v': 86, 'w': 87, 'x': 88, 'y': 89, 'z': 90,
        'leftWindowKey': 91, 'rightWindowKey': 92, 'selectKey': 93,
        'numpad0': 96, 'numpad1': 97, 'numpad2': 98, 'numpad3': 99, 'numpad4': 100,
        'numpad5': 101, 'numpad6': 102, 'numpad7': 103, 'numpad8': 104, 'numpad9': 105,
        'multiply': 106, 'add': 107, 'subtract': 109, 'decimalPoint': 110, 'divide': 111,
        'f1': 112, 'f2': 113, 'f3': 114, 'f4': 115, 'f5': 116, 'f6': 117,
        'f7': 118, 'f8': 119, 'f9': 120, 'f10': 121, 'f11': 122, 'f12': 123,
        'numLock': 144, 'scrollLock': 145, 'semiColon': 186, 'equalSign': 187, 'comma': 188,
        'dash': 189, 'period': 190, 'forwardSlash': 191, 'graveAccent': 192, 'openBracket': 219,
        'backSlash': 220, 'closeBraket': 221, 'singleQuote': 222
    };
    document.querySelectorAll('[data-bs-accesskey]:not([data-bs-accesskey=""])').forEach(obj => {
        var temp = obj.getAttribute('data-bs-accesskey').split('+');
        var useAlt = false;
        var useCtrl = false;
        var useShift = false;
        var key = null;
        for (var i = 0,len = temp.length; i < len; i++) {
            switch (temp[i]) {
                case 'alt':
                    useAlt = true;
                    break;
                case 'ctrl':
                    useCtrl = true;
                    break;
                case 'shift':
                    useShift = true;
                    break;
                default:
                    key = keycodes[temp[i]];
                    break;
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

/**
 * Window match media
 *
 * This function returns an object intended to monitorize the bs_theme
 */
saltos.bootstrap.window_match_media = window.matchMedia('(prefers-color-scheme: dark)');

/**
 * Set data_bs_theme
 *
 * This function sets the data_bs_theme attribute to enable or disable the dark bs theme
 */
saltos.bootstrap.set_data_bs_theme = e => {
    document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : '');
};

/**
 * Set bs theme
 *
 * This function sets the bs theme
 *
 * @theme => Can be auto, light or dark
 */
saltos.bootstrap.set_bs_theme = theme => {
    switch (theme) {
        case 'auto':
            saltos.bootstrap.set_data_bs_theme(saltos.bootstrap.window_match_media);
            saltos.bootstrap.window_match_media.addEventListener(
                'change', saltos.bootstrap.set_data_bs_theme);
            break;
        case 'light':
            saltos.bootstrap.set_data_bs_theme({matches: false});
            saltos.bootstrap.window_match_media.removeEventListener(
                'change', saltos.bootstrap.set_data_bs_theme);
            break;
        case 'dark':
            saltos.bootstrap.set_data_bs_theme({matches: true});
            saltos.bootstrap.window_match_media.removeEventListener(
                'change', saltos.bootstrap.set_data_bs_theme);
            break;
    }
    localStorage.setItem('saltos.bootstrap.bs_theme', theme);
};

/**
 * Set css theme
 *
 * This function sets the css theme
 *
 * @theme => Can be default or one of the bootswatch themes
 */
saltos.bootstrap.set_css_theme = theme => {
    if (theme == 'default') {
        var file = 'lib/bootstrap/bootstrap.min.css';
    } else {
        var file = `lib/bootswatch/${theme}.min.css`;
    }
    document.querySelectorAll('link[rel=stylesheet]').forEach(_this => {
        var found1 = _this.href.includes('bootstrap/bootstrap.min.css');
        var found2 = _this.href.includes('bootswatch/') && _this.href.includes('.min.css');
        if (found1 || found2) {
            _this.removeAttribute('integrity');
            _this.href = _this.href.replace(_this.href, file);
        }
    });
    localStorage.setItem('saltos.bootstrap.css_theme', theme);
};

/**
 * Get bs theme
 *
 * Retrieve the bs_theme stored in the localStorage
 */
saltos.bootstrap.get_bs_theme = () => {
    return localStorage.getItem('saltos.bootstrap.bs_theme');
};

/**
 * Get css theme
 *
 * Retrieve the css_theme stored in the localStorage
 */
saltos.bootstrap.get_css_theme = () => {
    return localStorage.getItem('saltos.bootstrap.css_theme');
};
