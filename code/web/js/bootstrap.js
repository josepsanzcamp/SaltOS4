
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * @button      => id, class, DS, AF, AK, label, onclick, tooltip, color, autoclose
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
 * @onetag      => id, class, PL, value, DS, RO, RQ, AF, AK, datalist, tooltip, label, color, OC
 * @gallery     => id, class, label, images, color
 * @placeholder => id, color, height, label
 * @list        => id, class, header, extra, data, footer, onclick, active, disabled, label
 * @tabs        => id, tabs, label, content, active, disabled, label
 * @pills       => id, tabs, label, content, active, disabled, label
 * @vpills      => id, tabs, label, content, active, disabled, label
 * @accordion   => id, flush, multiple, items, label
 * @jstree      => id, open, onclick, data
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
    // Fix when some attributes need the fix_key feature
    for (const key in field) {
        const new_key = saltos.core.fix_key(key);
        if (new_key != key && !(new_key in field)) {
            field[new_key] = field[key];
            delete field[key];
        }
    }
    // Continue
    saltos.core.check_params(field, ['id', 'type']);
    if (field.id == '') {
        field.id = saltos.core.uniqid();
    }
    if (typeof saltos.bootstrap.__field[field.type] != 'function') {
        throw new Error(`Field type ${field.type} not found`);
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
    const obj = saltos.core.html(`
        <div class="${field.class}" id="${field.id}" style="${field.style}"></div>
    `);
    for (const i in field) {
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
    const obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some container class is found
    let found = false;
    obj.classList.forEach(item => {
        if (['container', 'd-none'].includes(item)) {
            found = true;
        }
        if (item.substr(0, 10) == 'container-') {
            found = true;
        }
    });
    if (!found) {
        throw new Error('Container class not found in a container node');
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
    const obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some row class is found
    let found = false;
    obj.classList.forEach(item => {
        if (['row', 'd-none'].includes(item)) {
            found = true;
        }
        if (item.substr(0, 4) == 'row-') {
            found = true;
        }
    });
    if (!found) {
        throw new Error('Row class not found in a row node');
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
    const obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some col class is found
    let found = false;
    obj.classList.forEach(item => {
        if (['col', 'd-none'].includes(item)) {
            found = true;
        }
        if (item.substr(0, 4) == 'col-') {
            found = true;
        }
    });
    if (!found) {
        throw new Error('Col class not found in a col node');
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
    const obj = saltos.core.html(`<hr class="${field.class}" id="${field.id}" style="${field.style}"/>`);
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
 */
saltos.bootstrap.__field.text = field => {
    saltos.core.check_params(field, ['datalist'], []);
    field.type = 'text';
    let obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field)));
    if (field.datalist.length) {
        obj.querySelector('input').setAttribute('list', field.id + '_datalist');
        obj.append(saltos.core.html(`<datalist id="${field.id}_datalist"></datalist>`));
        for (const key in field.datalist) {
            const val = field.datalist[key];
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
 * @color       => the color of the widget (primary, secondary, success, danger, warning,
 *                 info, none)
 * @onenter     => the function executed when enter key is pressed
 * @onchange    => the function executed when onchange event is detected
 * @data        => this widget can store data in the data attribute, usefull to store
 *                 an array or object with data
 *
 * Notes:
 *
 * This function allow the previous parameters but for hidden inputs, only id
 * and value are usually used, in some cases can be interesting to use the
 * class to identify a group of hidden input
 */
saltos.bootstrap.__field.hidden = field => {
    field.type = 'hidden';
    const obj = saltos.bootstrap.__text_helper(field);
    if ('data' in field) {
        obj.data = field.data;
    }
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
    field.type = 'text';
    let obj = saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field));
    field.type = 'integer';
    const element = obj.querySelector('input');
    saltos.core.require([
        'lib/imaskjs/imask.min.js',
    ], () => {
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
    field.type = 'text';
    let obj = saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field));
    field.type = 'float';
    const element = obj.querySelector('input');
    saltos.core.require([
        'lib/imaskjs/imask.min.js',
    ], () => {
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
    let obj = saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field));
    obj.classList.add('d-inline-block');
    obj = saltos.bootstrap.__label_combine(field, obj);
    if (obj.children.length > 1) {
        const br = saltos.core.html('<br/>');
        obj.insertBefore(br, obj.children[1]);
    }
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
    let obj = saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field));
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
    let obj = saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field));
    obj.querySelector('input').step = 1; // This enable the seconds
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
    let obj = saltos.bootstrap.__shadow_helper(saltos.bootstrap.__text_helper(field));
    field.type = 'datetime';
    obj.querySelector('input').step = 1; // This enable the seconds
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
    saltos.core.check_params(field, ['height']);
    let obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__shadow_helper(saltos.bootstrap.__textarea_helper(field)));
    const element = obj.querySelector('textarea');
    saltos.core.require([
        'lib/autoheight/autoheight.min.js',
    ], () => {
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
    saltos.core.check_params(field, ['height', 'color', 'disabled']);
    if (!field.color) {
        field.color = 'primary';
    }
    const obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(
        saltos.bootstrap.__shadow_helper(
            saltos.bootstrap.__textarea_helper(
                saltos.core.copy_object(field)
            )
        )
    );
    const element = obj.querySelector('textarea');
    element.style.display = 'none';
    const array = [
        'lib/ckeditor/ckeditor.min.js',
    ];
    // Language prefetch
    const lang = saltos.gettext.get_short();
    if (lang != 'en') {
        array.push(`lib/ckeditor/translations/${lang}.js`);
    }
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
        height: field.height,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require(array, () => {
        placeholder.remove();
        ClassicEditor.create(element, {
            language: lang,
        }).then(editor => {
            element.ckeditor = editor;
            element.parentElement.classList.add('form-control');
            element.parentElement.classList.add('p-0');
            if (field.color != 'none') {
                element.parentElement.classList.add('border');
                element.parentElement.classList.add('border-' + field.color);
            }
            editor.model.document.on('change:data', () => {
                element.value = editor.getData();
            });
            //~ editor.keystrokes.set('Enter', 'shiftEnter');
            //~ editor.keystrokes.set('Shift+Enter', 'enter');
            // I maintain the follow commented lines as an example of usage
            /*editor.on('change:isReadOnly', (evt, propertyName, isReadOnly) => {
                const toolbar = editor.ui.view.toolbar.element;
                const editable = editor.ui.view.editable.element;
                if (isReadOnly) {
                    toolbar.classList.add('bg-body-secondary');
                    editable.classList.add('bg-body-secondary');
                } else {
                    toolbar.classList.remove('bg-body-secondary');
                    editable.classList.remove('bg-body-secondary');
                }
            });*/
        }).catch(error => {
            throw new Error(error);
        });
    });
    // Program the set feature
    element.set = value => {
        if (!('ckeditor' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('ckeditor' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        element.ckeditor.setData(value);
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('ckeditor' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        if (bool) {
            element.ckeditor.enableReadOnlyMode('editor');
        } else {
            element.ckeditor.disableReadOnlyMode('editor');
        }
    };
    if (saltos.core.eval_bool(field.disabled)) {
        element.set_disabled(true);
    }
    // Continue
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
    // Fix for dark mode
    obj.append(saltos.core.html(`
        <style>
            :root[data-bs-theme="dark"] .ck-editor__editable_inline {
                color: #000;
            }
        </style>
    `));
    // Fix for a rounded corners
    obj.append(saltos.core.html(`
        <style>
            :root {
                --ck-border-radius: var(--bs-border-radius);
            }
        </style>
    `));
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
 * @indent      => enables the indent feature, only available for xml, json, css and sql
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
    saltos.core.check_params(field, ['mode', 'height', 'color', 'disabled', 'indent']);
    if (!field.color) {
        field.color = 'primary';
    }
    let mode = field.mode;
    if (['json', 'js'].includes(mode)) {
        mode = 'javascript';
    }
    const obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(
        saltos.bootstrap.__shadow_helper(
            saltos.bootstrap.__textarea_helper(
                saltos.core.copy_object(field)
            )
        )
    );
    const element = obj.querySelector('textarea');
    element.style.display = 'none';
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
        height: field.height,
    });
    obj.append(placeholder);
    // Continue
    const array = [
        'lib/codemirror/codemirror.min.css',
        'lib/codemirror/codemirror.min.js',
    ];
    if (saltos.core.eval_bool(field.indent)) {
        array.push('lib/vkbeautify/vkbeautify.min.js');
    }
    saltos.core.require(array, () => {
        placeholder.remove();
        if (saltos.core.eval_bool(field.indent)) {
            element.value = saltos.bootstrap.__indent_helper(field.value, field.mode);
        }
        const cm = CodeMirror.fromTextArea(element, {
            mode: mode,
            styleActiveLine: true,
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 4,
        });
        element.codemirror = cm;
        element.parentElement.classList.add('form-control');
        element.parentElement.classList.add('p-0');
        if (field.color != 'none') {
            element.parentElement.classList.add('border');
            element.parentElement.classList.add('border-' + field.color);
        }
        element.nextElementSibling.style.height = 'auto';
        cm.on('change', cm.save);
        if (field.height) {
            element.nextElementSibling.querySelector('.CodeMirror-scroll').style.minHeight = field.height;
        }
        // This fix a bug because initially only paint the first 22 lines
        if (cm.lineCount() > 22) {
            cm.refresh();
        }
    });
    // Program the set feature
    element.set = value => {
        if (!('codemirror' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('codemirror' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        if (saltos.core.eval_bool(field.indent)) {
            value = saltos.bootstrap.__indent_helper(value, field.mode);
        }
        element.codemirror.setValue(value);
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('codemirror' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        if (bool) {
            element.codemirror.setOption('readOnly', true);
            element.nextElementSibling.classList.add('bg-body-secondary');
        } else {
            element.codemirror.setOption('readOnly', false);
            element.nextElementSibling.classList.remove('bg-body-secondary');
        }
    };
    if (saltos.core.eval_bool(field.disabled)) {
        element.set_disabled(true);
    }
    // Fix for a rounded corners
    obj.append(saltos.core.html(`
        <style>
            .CodeMirror {
                border-radius: var(--bs-border-radius);
            }
        </style>
    `));
    return obj;
};

/**
 * Indent helper
 *
 * This function allow to indent the string using the mode, this function is
 * intended to be used inside the codemirror widget, allowing to indent the
 * contents like xml, json, css or sql
 *
 * @str  => string that you want to indent
 * @mode => mode used to indent (xml, json, css or sql)
 */
saltos.bootstrap.__indent_helper = (str, mode) => {
    if (!str.trim().length) {
        return str;
    }
    switch (mode) {
        case 'xml':
            str = vkbeautify.xml(str);
            break;
        case 'json':
        case 'js':
        case 'javascript':
            // the follow if tries to fix some malformed json like }{, ][, }[ or ]{
            if (/}{|]\[|}\[|]\{/.test(str)) {
                // if true, fix the string adding comas and closing all into a new brackets
                str = '[' + str.replace(/}{|]\[|}\[|]\{/g, match => match[0] + ',' + match[1]) + ']';
            }
            try {
                str = vkbeautify.json(str);
            } catch (error) {
                //~ console.log(error);
            }
            break;
        case 'css':
            str = vkbeautify.css(str);
            break;
        case 'sql':
            str = vkbeautify.sql(str);
            break;
    }
    return str;
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
 *
 * Notes:
 *
 * This function allow to put contents in the srcdoc, as an extra feature, this content is
 * embedded with a doctype with html, head and body, includes the default saltos font and
 * to provide a security layer, this function creates an iframe with a sandbox and add to
 * the srcdoc a meta to configure the CSP that must apply to the contents
 *
 * To fix some issues with the iframe that adds some space between the bottom of the iframe
 * and the parent container, we must to add the d-block to convert it from inline to block
 */
saltos.bootstrap.__field.iframe = field => {
    saltos.core.check_params(field, ['src', 'srcdoc', 'id', 'class', 'height', 'color']);
    if (!field.color) {
        field.color = 'primary';
    }
    let border = `form-control p-0 shadow border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <iframe id="${field.id}" frameborder="0" class="${border} ${field.class}"></iframe>
    `);
    if (field.src) {
        obj.src = field.src;
    }
    if (field.srcdoc) {
        obj.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(field.srcdoc);
    }
    if (field.height) {
        obj.style.minHeight = field.height;
    }
    const element = obj;
    // When new load is detected
    element.addEventListener('load', event => {
        // Program the resize that computes the height
        const resizeObserver = new ResizeObserver(entries => {
            requestAnimationFrame(() => {
                const doc = element.contentWindow &&
                            element.contentWindow.document &&
                            element.contentWindow.document.documentElement;
                if (doc) {
                    element.style.height = doc.offsetHeight + 2 + 'px';
                }
            });
        });
        resizeObserver.observe(element);
        // To open the links in a new window and prevent the same origin error
        element.contentWindow.document.querySelectorAll('a, area').forEach(link => {
            link.setAttribute('target', '_blank');
        });
        // To propagate the keydown event suck as escape key
        element.contentWindow.addEventListener('keydown', event => {
            window.dispatchEvent(new KeyboardEvent('keydown', {
                altKey: event.altKey,
                ctrlKey: event.ctrlKey,
                shiftKey: event.shiftKey,
                keyCode: event.keyCode,
            }));
        });
    });
    // Program the set in the input first
    element.set = value => {
        if (typeof value == 'object') {
            if ('src' in value) {
                element.src = value.src;
            } else if ('srcdoc' in value) {
                element.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(value.srcdoc);
            }
        } else {
            if (field.src != '') {
                element.src = value;
            } else if (field.srcdoc != '') {
                element.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(value);
            }
        }
    };
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
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'required', 'onchange',
                                     'autofocus', 'multiple', 'size', 'value', 'tooltip',
                                     'accesskey', 'color', 'separator']);
    saltos.core.check_params(field, ['rows'], []);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let multiple = '';
    let width = '';
    let height = '';
    if (saltos.core.eval_bool(field.multiple)) {
        multiple = 'multiple';
        width = 'w-100';
        height = 'h-100';
    }
    let size = '';
    if (field.size != '') {
        size = `size="${field.size}"`;
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <div class="shadow ${width}">
            <select class="form-select ${border} ${height} ${field.class}" id="${field.id}"
                ${disabled} ${required} ${autofocus} ${multiple} ${size}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}"></select>
        </div>
    `);
    const select = obj.querySelector('select');
    if (field.onchange != '') {
        saltos.bootstrap.__onchange_helper(select, field.onchange);
    }
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(select);
    }
    if (!field.separator) {
        field.separator = ',';
    }
    const values = field.value.toString().split(field.separator);
    for (const key in values) {
        values[key] = values[key].trim();
    }
    for (const key in field.rows) {
        const val = saltos.core.join_attr_value(field.rows[key]);
        if (typeof val == 'object') {
            saltos.core.check_params(val, ['label', 'value']);
            let selected = '';
            if (values.includes(val.value.toString())) {
                selected = 'selected';
            }
            const option = saltos.core.html(`<option value="${val.value}" ${selected}></option>`);
            option.append(val.label);
            select.append(option);
        } else {
            let selected = '';
            if (values.includes(val.toString())) {
                selected = 'selected';
            }
            const option = saltos.core.html(`<option value="${val}" ${selected}></option>`);
            option.append(val);
            select.append(option);
        }
    }
    // Program the disabled feature
    select.set_disabled = bool => {
        if (bool) {
            select.setAttribute('disabled', '');
        } else {
            select.removeAttribute('disabled');
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
 * Warning:
 *
 * Detected a bug with this widget in chrome in mobile browsers
 */
saltos.bootstrap.__field.multiselect = field => {
    saltos.core.check_params(field, ['value', 'class', 'id', 'disabled', 'required',
                                     'size', 'tooltip', 'color', 'separator']);
    saltos.core.check_params(field, ['rows'], []);
    if (!field.separator) {
        field.separator = ',';
    }
    let obj = saltos.core.html(`
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
        class: `bi-chevron-double-right`,
        color: field.color,
        disabled: field.disabled,
        //tooltip: field.tooltip,
        onclick: () => {
            obj.querySelectorAll('#' + field.id + '_abc option').forEach(option => {
                if (option.selected) {
                    obj.querySelector('#' + field.id + '_xyz').append(option);
                }
            });
            const val = [];
            obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
                val.push(option.value);
            });
            obj.querySelector('#' + field.id).value = val.join(field.separator);
        },
    }));
    obj.querySelector('.two').append(saltos.core.html('<div class="mb-3"></div>'));
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
            const val = [];
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
        document.querySelectorAll('label[for=' + field.id + ']').forEach(item => {
            item.setAttribute('for', field.id + '_abc');
        });
    });
    // Program the set feature
    obj.querySelector('input[type=hidden]').set = value => {
        const values = value.toString().split(field.separator);
        for (const key in values) {
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
        const val = [];
        obj.querySelectorAll('#' + field.id + '_xyz option').forEach(option => {
            val.push(option.value);
        });
        obj.querySelector('input[type=hidden]').value = val.join(field.separator);
    };
    obj.querySelector('input[type=hidden]').set(field.value);
    // Program the disabled feature
    obj.querySelector('input[type=hidden]').set_disabled = bool => {
        const temp = obj.querySelector('#' + field.id).closest('.row');
        temp.querySelectorAll('select, button').forEach(item => {
            item.set_disabled(bool);
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
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let value = 0;
    if (saltos.core.eval_bool(field.value)) {
        value = 1;
    }
    let checked = '';
    if (value) {
        checked = 'checked';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    const obj = saltos.core.html(`
        <div class="form-check ${field.class}">
            <input class="form-check-input ${border} ${color}" type="checkbox" id="${field.id}"
                value="${value}" ${disabled} ${readonly} ${required} ${checked}
                data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            <label class="form-check-label" for="${field.id}"
                data-bs-title="${field.tooltip}">${field.label}</label>
        </div>
    `);
    if (field.tooltip != '') {
        obj.querySelectorAll('input, label').forEach(item => {
            saltos.bootstrap.__tooltip_helper(item);
        });
    }
    if (field.onchange != '') {
        obj.querySelectorAll('input').forEach(item => {
            saltos.bootstrap.__onchange_helper(item, field.onchange);
        });
    }
    obj.querySelector('input').addEventListener('change', event => {
        event.target.value = event.target.checked ? 1 : 0;
    });
    obj.querySelector('input').set = bool => {
        const input = obj.querySelector('input');
        if (saltos.core.eval_bool(bool)) {
            input.checked = true;
            input.value = 1;
        } else {
            input.checked = false;
            input.value = 0;
        }
    };
    // This add the colorized feature to the checkbox and switch
    obj.append(saltos.core.html(`
        <style>
            .form-check-input:checked.primary {
                background-color: var(--bs-primary);
            }
            .form-check-input:checked.secondary {
                background-color: var(--bs-secondary);
            }
            .form-check-input:checked.success {
                background-color: var(--bs-success);
            }
            .form-check-input:checked.danger {
                background-color: var(--bs-danger);
            }
            .form-check-input:checked.warning {
                background-color: var(--bs-warning);
            }
            .form-check-input:checked.info {
                background-color: var(--bs-info);
            }
            .form-check-input:disabled,
            .form-check-input[disabled],
            .form-check-input:disabled ~ .form-check-label,
            .form-check-input[disabled] ~ .form-check-label {
                opacity: 1;
            }
        </style>
    `));
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
    const obj = saltos.bootstrap.__field.checkbox(field);
    obj.classList.add('form-switch');
    obj.querySelector('input').setAttribute('role', 'switch');
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
    saltos.core.check_params(field, ['class', 'id', 'disabled', 'autofocus', 'autoclose',
                                     'onclick', 'tooltip', 'icon', 'label', 'accesskey',
                                     'color', 'collapse', 'target', 'addbr']);
    let disabled = '';
    let _class = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
        _class = 'opacity-25';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let autoclose = '';
    if (saltos.core.eval_bool(field.autoclose)) {
        autoclose = 'autoclose';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let collapse = '';
    if (saltos.core.eval_bool(field.collapse)) {
        collapse = `data-bs-toggle="collapse" data-bs-target="#${field.target}"
            aria-controls="${field.target}" aria-expanded="false"`;
    }
    let width = '';
    let height = '';
    if (field.class.includes('w-100')) {
        width = 'w-100';
    }
    if (field.class.includes('h-100')) {
        height = 'h-100';
    }
    const obj = saltos.core.html(`
        <div class="shadow d-inline-block ${width} ${height}">
            <button type="button" id="${field.id}" ${disabled} ${autofocus} ${autoclose}
                class="btn btn-${color} focus-ring focus-ring-${color} ${field.class} ${_class}"
                data-bs-accesskey="${field.accesskey}" ${collapse}
                data-bs-title="${field.tooltip}">${field.label}</button>
        </div>
    `);
    const button = obj.querySelector('button');
    if (field.icon) {
        button.prepend(saltos.core.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.label && field.icon) {
        button.querySelector('i').classList.add('me-1');
    }
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(button);
    }
    saltos.bootstrap.__onclick_helper(button, field.onclick);
    // Program the disabled feature
    button.set_disabled = bool => {
        if (bool) {
            button.setAttribute('disabled', '');
            button.classList.add('opacity-25');
        } else {
            button.removeAttribute('disabled');
            button.classList.remove('opacity-25');
        }
    };
    if (saltos.core.eval_bool(field.addbr)) {
        const temp = saltos.core.html(`<div><label class="form-label">&nbsp;</label><br/></div>`);
        temp.append(obj);
        return temp;
    }
    return obj;
};

/**
 * Password constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id           => the id used by the object
 * @class        => allow to add more classes to the default form-control
 * @placeholder  => the text used as placeholder parameter
 * @value        => the value used as value parameter
 * @disabled     => this parameter raise the disabled flag
 * @readonly     => this parameter raise the readonly flag
 * @required     => this parameter raise the required flag
 * @autofocus    => this parameter raise the autofocus flag
 * @tooltip      => this parameter raise the title flag
 * @accesskey    => the key used as accesskey parameter
 * @label        => this parameter is used as text for the label
 * @color        => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onenter      => the function executed when enter key is pressed
 * @onchange     => the function executed when onchange event is detected
 * @autocomplete => set to false to enable the hiddens passwords trick
 *
 * Notes:
 *
 * This widget add an icon to the end of the widget with an slashed eye, this allow to
 * see the entered password to verify it, in reality, this button swaps the input between
 * password and text type, allowing to do visible or not the contents of the input
 *
 * Setting the field.autocomplete=false enable the feature that tries to disable the
 * autocomplete provided by the browsers password adding the autocomplete="new-password"
 */
saltos.bootstrap.__field.password = field => {
    saltos.core.check_params(field, ['label', 'class', 'id', 'placeholder', 'value', 'disabled',
                                     'onenter', 'onchange', 'readonly', 'required',
                                     'autofocus', 'tooltip', 'accesskey', 'color']);
    saltos.core.check_params(field, ['autocomplete'], true);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    let autocomplete = '';
    if (!saltos.core.eval_bool(field.autocomplete)) {
        autocomplete = 'autocomplete="new-password"';
    }
    const obj = saltos.core.html(`
        <div>
            <div class="input-group shadow">
                <input type="password" class="form-control ${border} ${field.class}"
                    id="${field.id}" placeholder="${field.placeholder}" value="${field.value}"
                    ${disabled} ${readonly} ${required} ${autofocus} ${autocomplete}
                    aria-label="${field.placeholder}" aria-describedby="${field.id}_button"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
                <button class="btn btn-${color} bi-eye-slash" type="button"
                    id="${field.id}_button" data-bs-title="${field.tooltip}"></button>
            </div>
        </div>
    `);
    // Continue
    const input = obj.querySelector('input');
    const button = obj.querySelector('button');
    if (field.tooltip != '') {
        saltos.bootstrap.__tooltip_helper(input);
    }
    if (field.onenter != '') {
        saltos.bootstrap.__onenter_helper(input, field.onenter);
    }
    if (field.onchange != '') {
        saltos.bootstrap.__onchange_helper(input, field.onchange);
    }
    // Program the disabled feature
    input.set_disabled = bool => {
        if (bool) {
            input.setAttribute('disabled', '');
            button.setAttribute('disabled', '');
            button.classList.add('opacity-25');
        } else {
            input.removeAttribute('disabled');
            button.removeAttribute('disabled');
            button.classList.remove('opacity-25');
        }
    };
    // Program the button feature
    button.addEventListener('click', event => {
        switch (input.type) {
            case 'password':
                input.type = 'text';
                button.classList.replace('bi-eye-slash', 'bi-eye');
                break;
            case 'text':
                input.type = 'password';
                button.classList.replace('bi-eye', 'bi-eye-slash');
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
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let multiple = '';
    if (saltos.core.eval_bool(field.multiple)) {
        multiple = 'multiple';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let border1 = `border border-${color}`;
    let border2 = `border-${color}`;
    if (field.color == 'none') {
        border1 = 'border-0';
        border2 = '';
    }
    const obj = saltos.core.html(`
        <div>
            <div class="shadow">
                <input type="file" class="form-control ${border1} ${field.class}" id="${field.id}"
                    ${disabled} ${required} ${autofocus} ${multiple}
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            </div>
            <div class="form-control p-0 border-0 shadow table-responsive mt-3 d-none">
                <table class="table table-striped table-hover ${border2} mb-0">
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
        obj.querySelectorAll('input').forEach(item => {
            saltos.bootstrap.__tooltip_helper(item);
        });
    }
    // This helper programs the input file data update
    const __update_data_input_file = input => {
        const data = [];
        const tabla = input.parentElement.nextElementSibling.querySelector('table');
        tabla.querySelectorAll('tr').forEach(item => {
            data.push(item.data);
        });
        input.data = data;
    };
    __update_data_input_file(obj.querySelector('input'));
    // This helper programs the delete file button
    const __button_remove_file = event => {
        const row = event.target.parentElement.parentElement;
        const table = row.parentElement.parentElement;
        const input = table.parentElement.previousElementSibling.querySelector('input');
        saltos.core.ajax({
            url: 'api/?/upload/delfile',
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
                    table.parentElement.classList.add('d-none');
                }
                __update_data_input_file(input);
            },
            error: error => {
                throw new Error(error);
            },
            token: saltos.token.get(),
            lang: saltos.gettext.get(),
            abortable: true,
        });
    };
    // This helper paints each row of the table
    const __add_row_file = (input, table, file) => {
        // Show the table
        table.parentElement.classList.remove('d-none');
        // Add the row for the new file
        const row = saltos.core.html('tbody', `
            <tr id="${file.id}" class="align-middle">
                <td class="text-break">${file.name}</td>
                <td class="w-25">
                    <div class="progress" role="progressbar" aria-label="Upload percent"
                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                </td>
                <td class="p-0" style="width: 1%"><button
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
        const input = event.target;
        const files = event.target.files;
        const table = event.target.parentElement.nextElementSibling.querySelector('table');
        for (let i = 0; i < files.length; i++) {
            // Prepare the data to send
            const data = {
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
            const row = __add_row_file(input, table, data);
            // Get the local file using syncronous techniques
            const reader = new FileReader();
            reader.readAsDataURL(files[i]);
            while (!reader.result && !reader.error) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
            // If there is a file
            if (reader.result) {
                data.data = reader.result;
                // Check for void type
                if (!data.type) {
                    data.type = data.data.split(';')[0].split(':')[1];
                }
                // This allow multiple uploads in parallel
                ((data, row) => {
                    const ajax = new XMLHttpRequest();
                    ajax.open('POST', 'api/?/upload/addfile');
                    ajax.setRequestHeader('Content-Type', 'application/json');
                    ajax.setRequestHeader('Authorization', 'Bearer ' + saltos.token.get());
                    ajax.setRequestHeader('Accept-Language', saltos.gettext.get());
                    ajax.setRequestHeader('X-Proxy-Order', 'no');
                    ajax.onload = event => {
                        if (ajax.status < 200 || ajax.status >= 300) {
                            throw new Error(ajax);
                        }
                        let data = ajax.response;
                        if (ajax.getResponseHeader('content-type').toUpperCase().includes('JSON')) {
                            data = JSON.parse(ajax.responseText);
                        }
                        if (ajax.getResponseHeader('content-type').toUpperCase().includes('XML')) {
                            data = ajax.responseXML;
                        }
                        if (!saltos.app.check_response(data)) {
                            return;
                        }
                        row.data = data;
                        __update_data_input_file(input);
                    };
                    ajax.onerror = event => {
                        throw new Error(ajax);
                    };
                    ajax.onprogress = event => {
                        if (event.lengthComputable) {
                            const percent = Math.round((event.loaded / event.total) * 100);
                            row.querySelector('.progress-bar').style.width = percent + '%';
                            row.querySelector('.progress').setAttribute('aria-valuenow', percent);
                        }
                    };
                    ajax.upload.onprogress = ajax.onprogress;
                    ajax.send(JSON.stringify(data));
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
        const input = obj.querySelector('input');
        const tabla = input.parentElement.nextElementSibling.querySelector('table');
        tabla.querySelectorAll('tr').forEach(item => {
            item.remove();
        });
        __update_data_input_file(input);
        for (const i in data) {
            const input = obj.querySelector('input');
            const table = input.parentElement.nextElementSibling.querySelector('table');
            const row = __add_row_file(input, table, data[i]);
            const percent = 100;
            row.querySelector('.progress-bar').style.width = percent + '%';
            row.querySelector('.progress').setAttribute('aria-valuenow', percent);
        }
    };
    // Initialize the input with the previous function
    obj.querySelector('input').set(field.data);
    // Added the onchange event
    if (field.onchange != '') {
        obj.querySelectorAll('input[type=file]').forEach(item => {
            saltos.bootstrap.__onchange_helper(item, field.onchange);
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
 * This object is not a real link, it's a button that uses the btn-link class to get the link
 * appearance
 */
saltos.bootstrap.__field.link = field => {
    field.color = 'link';
    const obj = saltos.bootstrap.__field.button(field).querySelector('button');
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
    const obj = saltos.core.html(`
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
    let border = `form-control p-0 shadow border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <img id="${field.id}" src="${field.value}" alt="${field.alt}"
            class="${border} ${field.class}"
            width="${field.width}" height="${field.height}"
            data-bs-title="${field.tooltip}" />
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
 * @afterChange    => the afterChange function that receives one argument (changes), a 2D array containing
 *                    information about each of the edited cells [[row, prop, oldVal, newVal], ...],
 *                    you can do something like changes.forEach(([row, prop, oldValue, newValue])
 * @autoWrapCol    => used as autoWrapCol in the handsontable widget
 * @autoWrapRow    => used as autoWrapRow in the handsontable widget
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
    saltos.core.check_params(field, ['id', 'class', 'value', 'data', 'required', 'disabled',
                                     'rowHeaders', 'colHeaders', 'minSpareRows', 'height',
                                     'contextMenu', 'rowHeaderWidth', 'colWidths', 'color',
                                     'numcols', 'numrows', 'cell', 'cells', 'afterChange',
                                     'autoWrapCol', 'autoWrapRow']);
    if (!field.color) {
        field.color = 'primary';
    }
    let border = ['border', `border-${field.color}`];
    if (field.color == 'none') {
        border = ['border-0'];
    }
    let height = field.height;
    if (field.height == '') {
        height = '100%';
    }
    let obj = saltos.core.html(`
        <div class="form-control p-0 shadow" style="height: ${height}; overflow: auto">
            <div></div>
        </div>
    `);
    obj.prepend(saltos.bootstrap.__field.hidden(saltos.core.copy_object(field)));
    const input = obj.querySelector('input');
    field.numcols = parseInt(field.numcols, 10);
    field.numrows = parseInt(field.numrows, 10);
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
        field.rowHeaderWidth = parseInt(field.rowHeaderWidth, 10);
    }
    if (typeof field.colWidths == 'string') {
        if (field.colWidths == '') {
            field.colWidths = undefined;
        } else if (saltos.core.is_number(field.colWidths)) {
            field.colWidths = parseInt(field.colWidths, 10);
        } else if (saltos.core.is_function(field.colWidths)) {
            field.colWidths = eval(field.colWidths);
        }
    }
    if (typeof field.cells == 'string') {
        if (field.cells == '') {
            field.cells = undefined;
        } else if (saltos.core.is_function(field.cells)) {
            field.cells = eval(field.cells);
        }
    }
    if (typeof field.afterChange == 'string') {
        if (saltos.core.is_function(field.afterChange)) {
            field.afterChange = eval(field.afterChange);
        }
    }
    input.data = saltos.core.copy_object(field.data);
    const element = obj.querySelector('div');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Continue
    let options = {
        data: input.data, // This links the data
        rowHeaders: field.rowHeaders,
        colHeaders: field.colHeaders,
        minSpareRows: field.minSpareRows,
        contextMenu: field.contextMenu,
        rowHeaderWidth: field.rowHeaderWidth,
        colWidths: field.colWidths,
        autoWrapCol: field.autoWrapCol,
        autoWrapRow: field.autoWrapRow,
        cell: field.cell,
        cells: field.cells,
        afterChange: field.afterChange,
    };
    saltos.core.require([
        'lib/handsontable/handsontable.full.min.css',
        'lib/handsontable/handsontable.full.min.js',
    ], () => {
        placeholder.remove();
        const excel = new Handsontable(element, options);
        input.excel = excel;
        element.parentElement.classList.add(...border);
    });
    // Program the disabled feature
    input.set_disabled = bool => {
        if (!('excel' in input)) {
            setTimeout(() => input.set_disabled(bool), 1);
            return;
        }
        input.excel.updateSettings({
            cells: (row, col, prop) => {
                let cell = {};
                for (let key in field.cell) {
                    const val = field.cell[key];
                    if (val.row == row && val.col == col) {
                        cell = saltos.core.copy_object(val);
                    }
                }
                if ('readOnly' in cell) {
                    // Nothing to do
                } else {
                    cell.readOnly = bool;
                }
                if ('className' in cell) {
                    // Nothing to do
                } else if ('readOnlyCellClassName' in cell) {
                    // Nothing to do
                } else if (bool) {
                    cell.readOnlyCellClassName = 'bg-body-secondary';
                } else {
                    cell.readOnlyCellClassName = '';
                }
                return cell;
            },
        });
    };
    if (saltos.core.eval_bool(field.disabled)) {
        input.set_disabled(true);
    }
    // Fix for dark mode
    obj.append(saltos.core.html(`
        <style>
            :root[data-bs-theme="dark"] .handsontable td {
                color: #000;
            }
        </style>
    `));
    // Program the set in the input first
    input.set = value => {
        if (!('excel' in input)) {
            if (!('queue' in input)) {
                input.queue = [];
            }
            input.queue.push(value);
            if (!('timer' in input)) {
                input.timer = setInterval(() => {
                    if (!('excel' in input)) {
                        return;
                    }
                    clearInterval(input.timer);
                    while (input.queue.length) {
                        const item = input.queue.shift();
                        input.set(item);
                    }
                }, 1);
            }
            return;
        }
        if (Array.isArray(value)) {
            input.data = saltos.core.copy_object(value);
            options = {...options, data: input.data};
            input.excel.updateSettings(options);
        } else {
            input.data = saltos.core.copy_object(value.data);
            options = {...options, ...value, data: input.data};
            input.excel.updateSettings(options);
        }
    };
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
 * @lib/pdfjs/pdf.min.mjs
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
    saltos.core.check_params(field, ['id', 'class', 'src', 'srcdoc', 'color']);
    if (field.srcdoc != '') {
        field.src = {data: atob(field.srcdoc)};
    }
    if (!field.color) {
        field.color = 'primary';
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="${field.class}"></div>
    `);
    if (typeof field.src == 'string') {
        obj.src = new URL(field.src, window.location.href).href;
    }
    const element = obj;
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require([
        'lib/pdfjs/pdf.min.mjs',
    ], async () => {
        // To guarantee that the mjs is ready, this bug only appear in google chrome.
        while (typeof pdfjsLib != 'object') {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
        // Continue
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'lib/pdfjs/pdf.worker.min.mjs';
        pdfjsLib.getDocument(field.src).promise.then(pdf => {
            if (!pdf.numPages) {
                return;
            }
            const render = num => {
                pdf.getPage(num).then(page => {
                    const width = element.clientWidth;
                    let viewport = page.getViewport({scale: 1});
                    const scale = 2 * width / viewport.width;
                    viewport = page.getViewport({scale: scale});
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise.then(() => {
                        if (num == 1) {
                            placeholder.remove();
                        }
                        if (num < pdf.numPages) {
                            element.append(canvas);
                        } else {
                            const div = document.createElement('div');
                            div.append(canvas);
                            div.style.lineHeight = 0;
                            element.append(div);
                        }
                        canvas.style.width = '100%';
                        canvas.classList.add('form-control');
                        canvas.classList.add('p-0');
                        canvas.classList.add('shadow');
                        canvas.classList.add('border');
                        canvas.classList.add('border-' + field.color);
                        if (num < pdf.numPages) {
                            canvas.classList.add('mb-3');
                            render(num + 1);
                        }
                    });
                });
            };
            render(1);
        }, error => {
            throw new Error(error);
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
 * @onclick => the onclick function that receives as argument the arg to access the action
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
    saltos.core.check_params(field, ['header', 'data', 'footer', 'actions'], []);
    saltos.core.check_params(field, ['first_action'], true);
    if (field.checkbox != '') {
        field.checkbox = saltos.core.eval_bool(field.checkbox);
    }
    if (!field.color) {
        field.color = 'primary';
    }
    // This creates a responsive table (a table inside a div with table-responsive class)
    // We are using the same div to put inside the overlodaded styles of the table
    let obj = saltos.core.html(`
        <div id="${field.id}" class="form-control p-0 border-0 shadow table-responsive">
            <table class="table table-striped table-hover border-${field.color} ${field.class} mb-0">
            </table>
        </div>
    `);
    obj.querySelector('table').append(saltos.core.html('table', `
        <thead>
            <tr>
            </tr>
        </thead>
    `));
    if (Object.keys(field.header).length) {
        if (field.checkbox) {
            obj.querySelector('thead tr').append(saltos.core.html(
                'tr',
                `<th class="text-bg-${field.color}" style="width: 1%"><input type="checkbox" /></th>`
            ));
            obj.querySelector('thead input[type=checkbox]').addEventListener('change', event => {
                const item = event.target;
                obj.querySelectorAll('tbody input[type=checkbox]').forEach(item2 => {
                    if (item2.checked != item.checked) {
                        item2.click();
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
        for (const key in field.header) {
            field.header[key] = saltos.core.join_attr_value(field.header[key]);
            const val = field.header[key];
            let th;
            if (typeof val == 'object' && val !== null) {
                th = saltos.core.html('tr', `<th class="text-bg-${field.color}">${val.label}</th>`);
                if ('align' in val) {
                    th.classList.add('text-' + val.align);
                }
            } else {
                th = saltos.core.html('tr', `<th class="text-bg-${field.color}">${val}</th>`);
            }
            obj.querySelector('thead tr').append(th);
        }
        if (Object.keys(field.actions).length) {
            const th = saltos.core.html('tr', `<th class="text-bg-${field.color}" style="width: 1%"></th>`);
            obj.querySelector('thead tr').append(th);
        }
    } else {
        obj.querySelector('thead tr').append(saltos.core.html(
            'tr',
            `<th class="text-bg-${field.color} text-center" colspan="100">&nbsp;</th>`
        ));
    }
    obj.querySelector('table').append(saltos.core.html('table', `
        <tbody>
        </tbody>
    `));
    if (field.data.length) {
        // This function close all dropdowns
        const dropdown_close = () => {
            obj.querySelectorAll('.show').forEach(item => {
                item.classList.remove('show');
            });
        };
        for (const key in field.data) {
            const val = field.data[key];
            const row = saltos.core.html('tbody', `<tr class="align-middle"></tr>`);
            if (field.checkbox) {
                row.append(saltos.core.html('tr', `<td><input type="checkbox" value="${val.id}" /></td>`));
                row.querySelector('input[type=checkbox]').addEventListener('change', event => {
                    event.target.parentElement.parentElement.querySelectorAll('td').forEach(item => {
                        if (event.target.checked) {
                            item.classList.add('table-active');
                        } else {
                            item.classList.remove('table-active');
                        }
                    });
                    dropdown_close();
                });
                row.querySelector('input[type=checkbox]').addEventListener('click', event => {
                    // Here program the multiple selection feature using the ctrlKey
                    if (!event.altKey && !event.ctrlKey && !event.shiftKey) {
                        // First state, sets the id1
                        saltos.bootstrap.__checkbox_id1 = event.target.value;
                        saltos.bootstrap.__checkbox_id2 = null;
                    } else {
                        // Second state, sets the id2
                        saltos.bootstrap.__checkbox_id2 = event.target.value;
                    }
                    if (saltos.bootstrap.__checkbox_id1 && saltos.bootstrap.__checkbox_id2) {
                        const obj = event.target.parentElement.parentElement.parentElement;
                        const nodes = obj.querySelectorAll('input[type=checkbox][value]');
                        const ids = [saltos.bootstrap.__checkbox_id1, saltos.bootstrap.__checkbox_id2];
                        // Check that the two ids are presents
                        let count = 0;
                        nodes.forEach(item => {
                            if (ids.includes(item.value)) {
                                count++;
                            }
                        });
                        // If the two ids are present, then apply
                        if (count == 2) {
                            let found = false;
                            nodes.forEach(item => {
                                if (ids.includes(item.value)) {
                                    found = !found;
                                }
                                if (found) {
                                    if (!item.checked) {
                                        item.click();
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
                    const obj = event.target.parentElement.querySelector('input[type=checkbox]');
                    if (obj) {
                        // ctrlKey propagation is important to allow the multiple selection feature
                        obj.dispatchEvent(new MouseEvent('click', {
                            altKey: event.altKey,
                            ctrlKey: event.ctrlKey,
                            shiftKey: event.shiftKey,
                        }));
                    }
                    event.stopPropagation();
                });
            } else {
                row.setAttribute('id', `${field.id}/${val.id}`);
            }
            // This is to allow to use tables with data and without header
            let iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = val;
            }
            for (const key2 in iterator) {
                let val2 = val[key2];
                const td = saltos.core.html('tr', `<td></td>`);
                if (typeof val2 == 'object' && val2 !== null) {
                    if ('type' in val2) {
                        const temp = saltos.bootstrap.field(val2);
                        td.append(temp);
                    } else {
                        const temp = `object without type`;
                        td.append(temp);
                    }
                } else {
                    val2 = saltos.core.toString(val2);
                    let type = 'text';
                    if (typeof iterator[key2] == 'object' && 'type' in iterator[key2]) {
                        type = iterator[key2].type;
                    }
                    switch (type) {
                        case 'icon':
                            if (val2) {
                                const temp = saltos.core.html(`<i class="bi bi-${val2}"></i>`);
                                td.append(temp);
                            }
                            break;
                        case 'html':
                            if (val2) {
                                const temp = saltos.core.html(val2);
                                td.append(temp);
                            }
                            break;
                        case 'text':
                            if (val2) {
                                td.append(val2);
                            }
                            break;
                        default:
                            const temp = `unknown type ${type}`;
                            td.append(temp);
                            break;
                    }
                }
                if (typeof iterator[key2] == 'object' && 'align' in iterator[key2]) {
                    td.classList.add('text-' + iterator[key2].align);
                }
                if (typeof iterator[key2] == 'object' && 'class' in iterator[key2]) {
                    if (iterator[key2].class in val) {
                        if (val[iterator[key2].class] != '') {
                            td.classList.add('text-bg-' + val[iterator[key2].class]);
                        }
                    }
                }
                row.append(td);
            }
            if (Object.keys(field.actions).length) {
                const td = saltos.core.html('tr', `<td class="p-0 text-nowrap"></td>`);
                let dropdown = Object.keys(field.actions).length > 1;
                if (field.dropdown != '') {
                    dropdown = saltos.core.eval_bool(field.dropdown);
                }
                if (dropdown) {
                    td.append(saltos.core.html(`
                        <div>
                            <button class="btn border-0 dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            </button>
                            <ul class="dropdown-menu shadow">
                            </ul>
                        </div>
                    `));
                    // This close all dropdowns when a new dropdown appear
                    td.querySelector('ul').parentElement.addEventListener('show.bs.dropdown', dropdown_close);
                }
                let first_action = saltos.core.eval_bool(field.first_action);
                if (!('actions' in val)) {
                    val.actions = {};
                }
                for (const key2 in field.actions) {
                    const val2 = {
                        ...saltos.core.join_attr_value(field.actions[key2]),
                        ...val.actions[key2],
                    };
                    if (!('arg' in val2) || val2.arg == '') {
                        val2.disabled = true;
                    } else {
                        if (!('onclick' in val2)) {
                            throw new Error('Table onclick not found');
                        }
                        val2.onclick = `${val2.onclick}("${val2.arg}")`;
                    }
                    if (first_action) {
                        if (val2.onclick) {
                            row.setAttribute('_onclick', val2.onclick);
                            row.addEventListener('dblclick', event => {
                                (new Function(
                                    event.target.parentElement.getAttribute('_onclick')
                                )).call(event.target);
                                if (document.selection && document.selection.empty) {
                                    window.getSelection().removeAllRanges();
                                } else if (window.getSelection) {
                                    window.getSelection().removeAllRanges();
                                }
                            });
                        }
                        first_action = false;
                    }
                    if ('color' in val2) {
                        val2.class = `text-${val2.color}`;
                    }
                    val2.color = 'none';
                    const button = saltos.bootstrap.__field.button(val2).querySelector('button');
                    if (dropdown) {
                        button.classList.replace('btn', 'dropdown-item');
                        // This close all dropdowns when click an option inside a dropdown
                        button.addEventListener('click', dropdown_close);
                        const li = saltos.core.html(`<li></li>`);
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
    } else {
        if (field.nodata == '') {
            field.nodata = '&nbsp;';
        }
        obj.querySelector('tbody').append(saltos.core.html('tbody', `
            <tr class="align-middle text-center">
                <td colspan="100">${field.nodata}</td>
            </tr>
        `));
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
                throw new Error('Table field.header.length != field.footer.length');
            }
            if (field.checkbox) {
                obj.querySelector('tfoot tr').append(saltos.core.html(
                    'tr',
                    `<td class="bg-${field.color}-subtle"></td>`
                ));
            }
            // This is to allow to use tables with footer and without header
            let iterator = field.header;
            if (!Object.keys(iterator).length) {
                iterator = field.footer;
            }
            for (const key in iterator) {
                field.footer[key] = saltos.core.join_attr_value(field.footer[key]);
                const val = field.footer[key];
                let td;
                if (typeof val == 'object' && val !== null) {
                    td = saltos.core.html('tr', `<td class="bg-${field.color}-subtle">${val.value}</td>`);
                } else {
                    td = saltos.core.html('tr', `<td class="bg-${field.color}-subtle">${val}</td>`);
                }
                if (typeof iterator[key] == 'object' && 'align' in iterator[key]) {
                    td.classList.add('text-' + iterator[key].align);
                }
                obj.querySelector('tfoot tr').append(td);
            }
            if (Object.keys(field.actions).length) {
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
    // The follow code allow to fix a button size issue with small tables
    obj.append(saltos.core.html(`
        <style>
            .table-sm button {
                padding-top: 0;
                padding-bottom: 0;
            }
        </style>
    `));
    // Program the set and the add in the table
    obj.set = value => {
        const temp = saltos.bootstrap.__field.table({...field, ...value});
        obj.replaceWith(temp);
    };
    obj.add = value => {
        const suma = {...field, ...value, data: [...field.data, ...value.data]};
        const temp = saltos.bootstrap.__field.table(suma);
        obj.replaceWith(temp);
    };
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
    let obj = saltos.core.html(`
        <div class="alert alert-${field.color} shadow ${field.class} mb-0" role="alert" id="${field.id}">
        </div>
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
    let obj = saltos.core.html(`<div class="card border-${field.color} shadow" id="${field.id}"></div>`);
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
    saltos.core.check_params(field, ['id', 'mode', 'data']);
    let obj = saltos.core.html(`<div><canvas id="${field.id}" class="form-control shadow"></canvas></div>`);
    for (const key in field.data.datasets) {
        field.data.datasets[key].borderWidth = 1;
    }
    const element = obj.querySelector('canvas');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require([
        'lib/chartjs/chart.umd.min.js',
    ], () => {
        placeholder.remove();
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
 * @create      => allow to specify if the widget can create new items
 *
 * Notes:
 *
 * This widget contains a datalist with ajax autoload, this allow to send requests
 * to the desired path to retrieve the contents of the datalist for the autocomplete,
 * this request uses an historical keyword that can be retrieved in the json/term
 *
 * This widget uses the tom-select plugin, more info in their project website:
 * - https://tom-select.js.org/
 */
saltos.bootstrap.__field.tags = field => {
    saltos.core.check_params(field, ['separator'], ',');
    saltos.core.check_params(field, ['datalist', 'color']);
    saltos.core.check_params(field, ['create'], true);
    field.create = saltos.core.eval_bool(field.create);
    field.value = saltos.bootstrap.__value_helper(field.value, field.separator);
    const obj = saltos.bootstrap.__field.text(field);
    field.type = 'tags';
    const element = obj.querySelector('input');
    element.style.display = 'none';
    const fn = saltos.bootstrap.__datalist_helper(field.datalist);
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require([
        'lib/tomselect/tom-select.bootstrap5.min.css',
        'lib/tomselect/tom-select.complete.min.js',
    ], () => {
        placeholder.remove();
        const tags = new TomSelect(element, {
            delimiter: field.separator,
            preload: true,
            create: field.create,
            createOnBlur: true,
            persist: false,
            sortField: [{field: '$order'}, {field: '$score'}],
            closeAfterSelect: true,
            selectOnTab: true,
            openOnFocus: false,
            load: fn,
            plugins: [
                'remove_button',
                'clear_button',
                'caret_position',
                'input_autogrow',
            ],
        });
        element.tomselect = tags;
    });
    // Program the set in the input first
    element.set = value => {
        if (!('tomselect' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('tomselect' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        value = saltos.bootstrap.__value_helper(value, field.separator);
        element.value = value;
        element.tomselect.sync();
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('tomselect' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        if (bool) {
            element.tomselect.disable();
        } else {
            element.tomselect.enable();
        }
    };
    // Fix for dark mode
    obj.append(saltos.core.html(`
        <style>
            :root[data-bs-theme="dark"] .ts-control input,
            :root[data-bs-theme="dark"] .ts-control,
            :root[data-bs-theme="dark"] .ts-dropdown {
                color: #fff;
            }
        </style>
    `));
    return obj;
};

/**
 * One tag constructor helper
 *
 * This function creates a select that allow to be used as a text input like a select widget and allow
 * to create new items writing directly inside of the widget.
 *
 * @id          => the id used by the object
 * @value       => the value of this field
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
 * @onchange    => the function executed when onchange event is detected
 * @create      => allow to specify if the widget can create new items
 *
 * Notes:
 *
 * This widget contains a datalist with ajax autoload, this allow to send requests
 * to the desired path to retrieve the contents of the datalist for the autocomplete,
 * this request uses an historical keyword that can be retrieved in the json/term
 *
 * This widget uses the tom-select plugin, more info in their project website:
 * - https://tom-select.js.org/
 */
saltos.bootstrap.__field.onetag = field => {
    saltos.core.check_params(field, ['datalist', 'color', 'value']);
    saltos.core.check_params(field, ['create'], true);
    field.create = saltos.core.eval_bool(field.create);
    if (field.value)  {
        field.rows = [field.value];
    }
    const obj = saltos.bootstrap.__field.select(field);
    field.type = 'onetag';
    const element = obj.querySelector('select');
    element.style.display = 'none';
    const fn = saltos.bootstrap.__datalist_helper(field.datalist);
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require([
        'lib/tomselect/tom-select.bootstrap5.min.css',
        'lib/tomselect/tom-select.complete.min.js',
    ], () => {
        placeholder.remove();
        const tags = new TomSelect(element, {
            preload: true,
            create: field.create,
            createOnBlur: true,
            persist: false,
            sortField: [{field: '$order'}, {field: '$score'}],
            closeAfterSelect: true,
            selectOnTab: true,
            openOnFocus: false,
            load: fn,
            plugins: [
                'clear_button',
                'input_autogrow',
            ],
        });
        element.tomselect = tags;
    });
    // Program the set in the input first
    element.set = value => {
        if (!('tomselect' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('tomselect' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        element.tomselect.addOption({
            text: value,
            value: value,
        });
        element.tomselect.addItem(value);
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('tomselect' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        if (bool) {
            element.tomselect.disable();
        } else {
            element.tomselect.enable();
        }
    };
    // Fix for dark mode
    obj.append(saltos.core.html(`
        <style>
            :root[data-bs-theme="dark"] .ts-control input,
            :root[data-bs-theme="dark"] .ts-control,
            :root[data-bs-theme="dark"] .ts-dropdown {
                color: #fff;
            }
        </style>
    `));
    return obj;
};

/**
 * Srcdoc helper
 *
 * This function adds the needed environment to the html to improve the
 * render of the html, this function is intended to be used inside the
 * iframw widget
 *
 * @html => the code that you need to process
 */
saltos.bootstrap.__iframe_srcdoc_helper = html => {
    const font = 'lib/atkinson/atkinson.min.css';
    return `<!doctype html><html><head><meta charset="utf-8">
    <style>body { margin: 0; padding: 9px 12px; }</style>
    <link href="${font}" rel="stylesheet" integrity="">
    <style>:root { font-family: var(--bs-font-sans-serif); }</style>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self';
        style-src 'self' 'unsafe-inline' ${window.location.origin};
        font-src 'self' ${window.location.origin};
        img-src 'self' data: ${window.location.origin};">
    </head><body>${html}</body></html>`;
};

/**
 * Datalist helper
 *
 * This function is a helper function used by the tags and onetag widgets
 * and is intended to be used as load function by the tomselect library.
 *
 * @datalist => the original datalist that can be an string or an object
 */
saltos.bootstrap.__datalist_helper = datalist => {
    let fn = null;
    if (typeof datalist == 'string' && datalist != '') {
        fn = (query, callback) => {
            if (!query) {
                callback([]);
                return;
            }
            saltos.core.ajax({
                url: 'api/?/' + datalist,
                data: JSON.stringify({term: query}),
                method: 'post',
                content_type: 'application/json',
                success: response => {
                    if (!saltos.app.check_response(response)) {
                        return;
                    }
                    const array = [];
                    for (const key in response.data) {
                        const val = response.data[key];
                        if (typeof val == 'object') {
                            const temp = {};
                            if ('text' in val) {
                                temp.text = val.text;
                            } else if ('label' in val) {
                                temp.text = val.label;
                            } else if ('value' in val) {
                                temp.text = val.value;
                            }
                            if ('value' in val) {
                                temp.value = val.value;
                            } else if ('label' in val) {
                                temp.value = val.label;
                            } else if ('text' in val) {
                                temp.value = val.text;
                            }
                            array.push(temp);
                        } else {
                            array.push({
                                text: val,
                                value: val,
                            });
                        }
                    }
                    callback(array);
                },
                error: error => {
                    throw new Error(error);
                },
                token: saltos.token.get(),
                lang: saltos.gettext.get(),
                abortable: true,
            });
        };
    }
    if (typeof datalist == 'object') {
        fn = (query, callback) => {
            const array = [];
            for (const key in datalist) {
                const val = datalist[key];
                if (typeof val == 'object') {
                    const temp = {};
                    if ('text' in val) {
                        temp.text = val.text;
                    } else if ('label' in val) {
                        temp.text = val.label;
                    } else if ('value' in val) {
                        temp.text = val.value;
                    }
                    if ('value' in val) {
                        temp.value = val.value;
                    } else if ('label' in val) {
                        temp.value = val.label;
                    } else if ('text' in val) {
                        temp.value = val.text;
                    }
                    array.push(temp);
                } else {
                    array.push({
                        text: val,
                        value: val,
                    });
                }
            }
            callback(array);
        };
    }
    return fn;
};

/**
 * Value helper
 *
 * This function is a helper function used by the tags widget and is intended
 * to be used to convert a string into an array using the separator for split.
 *
 * @value     => the original value that must to be processed
 * @separator => the separator string used in the split
 */
saltos.bootstrap.__value_helper = (value, separator) => {
    value = value.toString().split(separator);
    let array = [];
    for (const key in value) {
        const val = value[key].trim();
        if (val.length) {
            array.push(val);
        }
    }
    value = array.join(separator);
    return value;
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
    saltos.core.check_params(field, ['id', 'class', 'images', 'color']);
    if (field.class == '') {
        field.class = 'col';
    }
    if (!field.color) {
        field.color = 'primary';
    }
    let border = `border border-${field.color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="container-fluid">
            <div class="row">
            </div>
        </div>
    `);
    if (typeof field.images == 'object') {
        for (const key in field.images) {
            let val = field.images[key];
            if (typeof val == 'string') {
                val = {image: val};
            }
            saltos.core.check_params(val, ['image', 'title']);
            const img = saltos.core.html(`
                <div class="${field.class} p-1">
                    <a href="${val.image}" class="venobox" data-gall="${field.id}" title="${val.title}">
                        <img src="${val.image}" class="img-fluid img-thumbnail ${border} p-0 shadow" />
                    </a>
                </div>
            `);
            obj.querySelector('.row').append(img);
        }
    }
    const element = obj.querySelector('.row');
    saltos.core.require([
        'lib/venobox/venobox.min.css',
        'lib/venobox/venobox.min.js',
        'lib/masonry/masonry.pkgd.min.js',
        'lib/imagesloaded/imagesloaded.pkgd.min.js',
    ], () => {
        const msnry = new Masonry(element, {
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
 * @id     => id used in the original object, it must be replaced when the data will be available
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @height => the height used as style.height parameter
 * @label  => this parameter is used as text for the label
 */
saltos.bootstrap.__field.placeholder = field => {
    saltos.core.check_params(field, ['id', 'color', 'height', 'label']);
    if (!field.color) {
        field.color = 'primary';
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="w-100 h-100 placeholder-glow text-${field.color}"
             aria-hidden="true" style="height:${field.height}!important">
            <span class="w-100 h-100 placeholder"></span>
        </div>
    `);
    obj = saltos.bootstrap.__label_combine(field, obj);
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
    saltos.core.check_params(field, ['data', 'actions'], []);
    // Check for data not found
    if (!field.data.length) {
        const obj = saltos.bootstrap.__field.alert({
            id: field.id,
            title: field.nodata,
            label: field.label,
        });
        obj.set = value => {
            const temp = saltos.bootstrap.__field.list({...field, ...value});
            obj.replaceWith(temp);
        };
        obj.add = value => {
            const suma = {...field, ...value, data: [...field.data, ...value.data]};
            const temp = saltos.bootstrap.__field.list(suma);
            obj.replaceWith(temp);
        };
        return obj;
    }
    // Continue
    let obj;
    if (saltos.core.eval_bool(field.onclick)) {
        obj = saltos.core.html(`<div id="${field.id}" class="list-group shadow ${field.class}"></div>`);
    } else {
        obj = saltos.core.html(`<ul id="${field.id}" class="list-group shadow ${field.class}"></ul>`);
    }
    for (const key in field.data) {
        const val = field.data[key];
        saltos.core.check_params(val, ['header', 'body', 'footer', 'class',
            'header_text', 'header_icon', 'header_color',
            'body_text', 'body_icon', 'body_color',
            'footer_text', 'footer_icon', 'footer_color',
            'onclick', 'arg', 'active', 'disabled', 'actions', 'id']);
        let item;
        if (saltos.core.eval_bool(field.onclick)) {
            item = saltos.core.html(`<button
                class="list-group-item list-group-item-action ${val.class}"></button>`);
            if (Object.keys(field.actions).length) {
                if (!('actions' in val)) {
                    val.actions = {};
                }
                const action = {
                    ...saltos.core.join_attr_value(Object.values(field.actions)[0]),
                    ...Object.values(val.actions)[0],
                };
                if ('onclick' in action && 'arg' in action) {
                    val.onclick = action.onclick;
                    val.arg = action.arg;
                }
            }
            if (val.arg != '') {
                val.onclick = `${val.onclick}("${val.arg}")`;
            }
            saltos.bootstrap.__onclick_helper(item, val.onclick);
            if (saltos.core.eval_bool(field.checkbox)) {
                if (val.id == '') {
                    val.id = saltos.core.uniqid();
                }
                item.setAttribute('id', `button_${val.id}`);
            }
        } else {
            item = saltos.core.html(`<li class="list-group-item ${val.class}"></li>`);
        }
        if (val.header != '') {
            const temp = saltos.core.html(`
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
                    <small class="text-nowrap text-${val.header_color} ms-1">
                        ${val.header_text}
                        <i class="bi bi-${val.header_icon}"></i>
                    </small>
                `));
            } else if (val.header_text != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.header_color} ms-1">
                        ${val.header_text}
                    </small>
                `));
            } else if (val.header_icon != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.header_color} ms-1">
                        <i class="bi bi-${val.header_icon}"></i>
                    </small>
                `));
            }
            item.append(temp);
        }
        if (val.body != '') {
            const temp = saltos.core.html(`
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
                    <small class="text-nowrap text-${val.body_color} ms-1">
                        ${val.body_text}
                        <i class="bi bi-${val.body_icon}"></i>
                    </small>
                `));
            } else if (val.body_text != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.body_color} ms-1">
                        ${val.body_text}
                    </small>
                `));
            } else if (val.body_icon != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.body_color} ms-1">
                        <i class="bi bi-${val.body_icon}"></i>
                    </small>
                `));
            }
            item.append(temp);
        }
        if (val.footer != '') {
            const temp = saltos.core.html(`
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
                    <small class="text-nowrap text-${val.footer_color} ms-1">
                        ${val.footer_text}
                        <i class="bi bi-${val.footer_icon}"></i>
                    </small>
                `));
            } else if (val.footer_text != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.footer_color} ms-1">
                        ${val.footer_text}
                    </small>
                `));
            } else if (val.footer_icon != '') {
                temp.append(saltos.core.html(`
                    <small class="text-nowrap text-${val.footer_color} ms-1">
                        <i class="bi bi-${val.footer_icon}"></i>
                    </small>
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
    // The --bs-body-color is used as main color here from bootstrap 5.3.5
    obj.append(saltos.core.html(`
        <style>
            .list-group {
                --bs-list-group-action-hover-bg: #fbec88;
                --bs-list-group-action-active-bg: #fbec88;
                --bs-list-group-action-hover-color: #373a3c;
                --bs-list-group-action-active-color: #373a3c;
                --bs-list-group-action-color: --var(--bs-body-color);
            }
            .list-group-item:nth-child(odd) {
                --bs-list-group-bg: rgba(var(--bs-emphasis-color-rgb), 0.05);
            }
            .list-group-item.active h5 {
                color: inherit;
            }
            .list-group-item.active [class^="text-"] {
                color: inherit!important;
            }
        </style>
    `));
    if (saltos.core.eval_bool(field.checkbox)) {
        saltos.core.when_visible(obj, () => {
            obj.classList.add('position-relative');
            for (const key in field.data) {
                const val = field.data[key];
                obj.append(saltos.core.html(`
                    <div class="position-absolute p-2">
                        <input class="form-check-input" type="checkbox"
                            value="${val.id}" id="checkbox_${val.id}">
                    </div>
                `));
                const button = obj.querySelector(`#button_${val.id}`);
                const checkbox = obj.querySelector(`#checkbox_${val.id}`);
                checkbox.parentElement.style.height = button.offsetHeight + 'px';
                checkbox.parentElement.style.top = button.offsetTop + 'px';
                const width = checkbox.parentElement.offsetWidth;
                button.style.paddingLeft = width + 'px';
                checkbox.parentElement.style.zIndex = 201;
                button.style.zIndex = 200;
                checkbox.addEventListener('change', event => {
                    const button = event.target.id.replace('checkbox', 'button');
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
                    if (!event.altKey && !event.ctrlKey && !event.shiftKey) {
                        // First state, sets the id1
                        saltos.bootstrap.__checkbox_id1 = event.target.value;
                        saltos.bootstrap.__checkbox_id2 = null;
                    } else {
                        // Second state, sets the id2
                        saltos.bootstrap.__checkbox_id2 = event.target.value;
                    }
                    if (saltos.bootstrap.__checkbox_id1 && saltos.bootstrap.__checkbox_id2) {
                        const obj = event.target.parentElement.parentElement;
                        const nodes = obj.querySelectorAll('input[type=checkbox][value]');
                        const ids = [saltos.bootstrap.__checkbox_id1, saltos.bootstrap.__checkbox_id2];
                        // Check that the two ids are presents
                        let count = 0;
                        nodes.forEach(item => {
                            if (ids.includes(item.value)) {
                                count++;
                            }
                        });
                        // If the two ids are present, then apply
                        if (count == 2) {
                            let found = false;
                            nodes.forEach(item => {
                                if (ids.includes(item.value)) {
                                    found = !found;
                                }
                                if (found) {
                                    if (!item.checked) {
                                        item.click();
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
                    const obj = event.target.querySelector('input[type=checkbox]');
                    if (obj) {
                        // ctrlKey propagation is important to allow the multiple selection feature
                        obj.dispatchEvent(new MouseEvent('click', {
                            altKey: event.altKey,
                            ctrlKey: event.ctrlKey,
                            shiftKey: event.shiftKey,
                        }));
                        // The next focus allow to continue navigating by the other checkboxes
                        obj.focus();
                    }
                    event.stopPropagation();
                });
            }
        });
    }
    // Program the set and the add in the table
    obj.set = value => {
        const temp = saltos.bootstrap.__field.list({...field, ...value});
        obj.replaceWith(temp);
    };
    obj.add = value => {
        const suma = {...field, ...value, data: [...field.data, ...value.data]};
        const temp = saltos.bootstrap.__field.list(suma);
        obj.replaceWith(temp);
    };
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
 * @label    => string with the text label to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 */
saltos.bootstrap.__field.tabs = field => {
    saltos.core.check_params(field, ['id', 'type']);
    saltos.core.check_params(field, ['items'], []);
    let obj = saltos.core.html(`
        <ul class="nav nav-${field.type} mb-3" id="${field.id}-tab" role="tablist"></ul>
        <div class="tab-content" id="${field.id}-content"></div>
    `);
    for (const key in field.items) {
        let val = field.items[key];
        val = saltos.core.join_attr_value(val);
        saltos.core.check_params(val, ['label', 'content', 'active', 'disabled']);
        let active = '';
        let selected = 'false';
        let show = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
            selected = 'true';
            show = 'show';
        }
        let disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        const id = saltos.core.uniqid();
        obj.querySelector('ul.nav').append(saltos.core.html(`
            <li class="nav-item" role="presentation">
                <button class="nav-link ${active} text-nowrap" id="${field.id}-${id}-tab"
                    data-bs-toggle="pill" data-bs-target="#${field.id}-${id}"
                    type="button" role="tab" aria-controls="${field.id}-${id}"
                    aria-selected="${selected}" ${disabled}>${val.label}</button>
            </li>
        `));
        const div = saltos.core.html(`
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
 * @label    => string with the text label to use in the tab button
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
 * @label    => string with the text name to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 * @disabled => this parameter raise the disabled flag
 */
saltos.bootstrap.__field.vpills = field => {
    saltos.core.check_params(field, ['id']);
    saltos.core.check_params(field, ['items'], []);
    let obj = saltos.core.html(`
        <div class="d-flex align-items-start">
            <div class="nav flex-column nav-pills me-3" id="${field.id}-tab"
                role="tablist" aria-orientation="vertical"></div>
            <div class="tab-content" id="${field.id}-content"></div>
        </div>
    `);
    for (let key in field.items) {
        let val = field.items[key];
        val = saltos.core.join_attr_value(val);
        saltos.core.check_params(val, ['label', 'content', 'active', 'disabled']);
        let active = '';
        let selected = 'false';
        let show = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
            selected = 'true';
            show = 'show';
        }
        let disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        const id = saltos.core.uniqid();
        obj.querySelector('div.nav').append(saltos.core.html(`
            <button class="nav-link ${active} text-nowrap" id="${field.id}-${id}-tab"
                data-bs-toggle="pill" data-bs-target="#${field.id}-${id}"
                type="button" role="tab" aria-controls="${field.id}-${id}"
                aria-selected="${selected}" ${disabled}>${val.label}</button>
        `));
        const div = saltos.core.html(`
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
 * @label    => string with the text label to use in the tab button
 * @content  => string with the content to be used in the content area
 * @active   => this parameter raise the active flag
 */
saltos.bootstrap.__field.accordion = field => {
    saltos.core.check_params(field, ['id', 'flush', 'multiple']);
    saltos.core.check_params(field, ['items'], []);
    if (saltos.core.eval_bool(field.flush)) {
        field.flush = 'accordion-flush';
    }
    let obj = saltos.core.html(`
        <div class="accordion shadow ${field.flush}" id="${field.id}"></div>
    `);
    for (const key in field.items) {
        let val = field.items[key];
        val = saltos.core.join_attr_value(val);
        saltos.core.check_params(val, ['label', 'content', 'active']);
        let collapsed = 'collapsed';
        let expanded = 'false';
        let show = '';
        if (saltos.core.eval_bool(val.active)) {
            collapsed = '';
            expanded = 'true';
            show = 'show';
        }
        let parent = `data-bs-parent="#${field.id}"`;
        if (saltos.core.eval_bool(field.multiple)) {
            parent = '';
        }
        const id = saltos.core.uniqid();
        const item = saltos.core.html(`
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
        item.querySelector('.accordion-button').append(val.label);
        item.querySelector('.accordion-body').append(val.content);
        obj.append(item);
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * JS Tree constructor helper
 *
 * This function returns a jstree object using the follow parameters:
 *
 * @id      => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default table table-striped table-hover
 * @open    => the open boolean that open all nodes
 * @onclick => the callback that receives the id as argument of the selected item
 * @nodata   => text used when no data is found
 * @label    => this parameter is used as text for the label
 * @data    => the data used to make the tree, must to be an array with nodes
 *
 * Each node must contain the follow items:
 *
 * @id       => the id of the node (used in the onclick callback)
 * @text     => the text of the node
 * @children => an array with the nodes childrens
 */
saltos.bootstrap.__field.jstree = field => {
    saltos.core.check_params(field, ['id', 'class', 'open', 'onclick', 'nodata', 'color']);
    saltos.core.check_params(field, ['data'], []);
    if (!field.color) {
        field.color = 'primary';
    }
    let obj = saltos.core.html(`<div id="${field.id}" class="${field.class}"></div>`);
    const element = obj;
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require([
        'lib/jstree/jstree.min.css',
        'lib/jstree/jstree.min.js',
    ], () => {
        placeholder.remove();
        const instance = new jsTree({}, element);
        element.instance = instance;
        instance.on('select', event => {
            let val = event.node.data.text;
            if ('id' in event.node.data) {
                val = event.node.data.id;
            }
            if (!val) {
                return;
            }
            if (typeof field.onclick == 'string') {
                (new Function(field.onclick)).call(val);
                return;
            }
            if (typeof field.onclick == 'function') {
                field.onclick(val);
                return;
            }
            throw new Error('Unknown jstree onclick typeof ' + typeof field.onclick);
        });
        /* .jstree-node-text:hover { background:var(--bs-primary-bg-subtle); } */
        element.append(saltos.core.html(`
            <style>
                .jstree-node-text { color:var(--bs-${field.color}); }
                .jstree-node-text:hover { background:#fbec88; color:#373a3c; }
                .jstree-selected,
                .jstree-selected:hover { background:var(--bs-${field.color}); color:white; }
                .jstree-node-icon:before { background:var(--bs-${field.color}); }
                .jstree-node-icon:after { background:var(--bs-${field.color}); }
                .jstree-node-text:hover .jstree-node-icon:before { background:#373a3c; }
                .jstree-node-text:hover .jstree-node-icon:after { background:#373a3c; }
                .jstree-selected:hover .jstree-node-icon:before { background:white; }
                .jstree-selected:hover .jstree-node-icon:after { background:white; }
            </style>
        `));
    });
    element.set = data => {
        if (!('instance' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(data);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('instance' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        // Check for data not found
        if (!data.length) {
            data = [{
                id: null,
                text: field.nodata,
            }];
        }
        // Continue
        element.instance.empty().create(data);
        if (saltos.core.eval_bool(field.open)) {
            element.instance.openAll();
        }
    };
    element.set(field.data);
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Dropdown constructor helper
 *
 * This function returns a dropdown object, you can pass the follow arguments:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default btn-group
 * @disabled  => this parameter raise the disabled flag
 * @label     => label to be used as text in the contents of the buttons
 * @onclick   => callback function that is executed when the button is pressed
 * @split     => to use a split button instead of single button
 * @tooltip   => this parameter raise the title flag
 * @icon      => the icon used in the main button
 * @accesskey => the key used as accesskey parameter
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @menu      => with this option, you can specify an array with the contents of the dropdown menu
 *
 * @label     => label of the menu
 * @icon      => icon of the menu
 * @disabled  => this boolean allow to disable this menu entry
 * @active    => this boolean marks the option as active
 * @onclick   => the callback used when the user select the menu
 * @divider   => you can set this boolean to true to convert the element into a divider
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 *
 * Notes:
 *
 * The tooltip can not be applied to the dropdown button because causes an internal error,
 * in this case, the tooltip only are applied in the first button of the split button and
 * all items of the menu, as brief, tootip only can be applied in all real actions buttons
 * and not in the dropdown button that opens the real dropdown menu
 */
saltos.bootstrap.__field.dropdown = field => {
    saltos.core.check_params(field, ['id', 'class', 'disabled', 'label', 'onclick',
                                     'split', 'tooltip', 'icon', 'accesskey', 'color']);
    saltos.core.check_params(field, ['menu'], []);
    // Check for main attributes
    let disabled = '';
    let _class = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
        _class = 'opacity-25';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    // Create the main object
    let obj;
    if (!saltos.core.eval_bool(field.split)) {
        obj = saltos.core.html(`
            <div class="btn-group shadow ${field.class}" id="${field.id}">
                <button type="button" ${disabled}
                    class="btn btn-${color} ${_class} dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}">
                        ${field.label}
                </button>
            </div>
        `);
    } else {
        obj = saltos.core.html(`
            <div class="btn-group shadow ${field.class}" id="${field.id}">
                <button type="button" ${disabled}
                    class="btn btn-${color} ${_class}"
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}">
                        ${field.label}
                </button>
                <button type="button" ${disabled}
                    class="btn btn-${color} ${_class} dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                </button>
            </div>
        `);
        saltos.bootstrap.__onclick_helper(obj.querySelector('button'), field.onclick);
        if (field.tooltip != '') {
            saltos.bootstrap.__tooltip_helper(obj.querySelector('button'));
        }
    }
    // Add the icon and tooltip
    if (field.icon) {
        obj.querySelector('button').prepend(saltos.core.html(`<i class="bi bi-${field.icon}"></i>`));
    }
    if (field.label && field.icon) {
        obj.querySelector('i').classList.add('me-1');
    }
    obj.append(saltos.core.html(`<ul class="dropdown-menu shadow"></ul>`));
    // Add the dropdown items
    for (const key in field.menu) {
        const val = field.menu[key];
        saltos.core.check_params(val, ['id', 'label', 'icon', 'disabled', 'active',
                                       'onclick', 'divider', 'tooltip', 'accesskey']);
        let disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        let active = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
        }
        let temp;
        if (saltos.core.eval_bool(val.divider)) {
            temp = saltos.core.html(`<li><hr class="dropdown-divider"></li>`);
        } else {
            temp = saltos.core.html(`
                <li><button id="${val.id}" class="dropdown-item ${disabled} ${active}"
                    data-bs-accesskey="${val.accesskey}" data-bs-title="${val.tooltip}">
                        ${val.label}
                </button></li>`);
            if (val.icon) {
                temp.querySelector('button').prepend(
                    saltos.core.html(`<i class="bi bi-${val.icon}"></i>`));
            }
            if (val.label && val.icon) {
                temp.querySelector('i').classList.add('me-1');
            }
            if (val.tooltip != '') {
                saltos.bootstrap.__tooltip_helper(temp.querySelector('button'));
            }
            if (!saltos.core.eval_bool(val.disabled)) {
                saltos.bootstrap.__onclick_helper(temp.querySelector('button'), val.onclick);
            }
        }
        obj.querySelector('ul').append(temp);
    }
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
 * @autosave    => allow to disable the autosave feature for this field, true by default
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__text_helper = field => {
    saltos.core.check_params(field, ['type', 'class', 'id', 'placeholder', 'value',
                                     'disabled', 'onenter', 'onchange', 'readonly', 'required',
                                     'autofocus', 'tooltip', 'accesskey', 'color']);
    saltos.core.check_params(field, ['autosave'], true);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let autosave = '';
    if (!saltos.core.eval_bool(field.autosave)) {
        autosave = 'autosave="false"';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    const obj = saltos.core.html(`
        <input type="${field.type}" class="form-control ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${disabled} ${readonly} ${required} ${autofocus} ${autosave}
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
 * @autosave    => allow to disable the autosave feature for this field, true by default
 *
 * Notes:
 *
 * This function is intended to be used by other helpers of the form_field constructor
 */
saltos.bootstrap.__textarea_helper = field => {
    saltos.core.check_params(field, ['class', 'id', 'placeholder', 'value', 'onchange',
                                     'disabled', 'readonly', 'required', 'autofocus',
                                     'tooltip', 'accesskey', 'color']);
    saltos.core.check_params(field, ['autosave'], true);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let readonly = '';
    if (saltos.core.eval_bool(field.readonly)) {
        readonly = 'readonly';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let autosave = '';
    if (!saltos.core.eval_bool(field.autosave)) {
        autosave = 'autosave="false"';
    }
    let color = field.color;
    if (!field.color) {
        color = 'primary';
    }
    let border = `border border-${color}`;
    if (field.color == 'none') {
        border = 'border-0';
    }
    const obj = saltos.core.html(`
        <textarea class="form-control ${border} ${field.class}"
            placeholder="${field.placeholder}" data-bs-accesskey="${field.accesskey}"
            ${disabled} ${readonly} ${required} ${autofocus} ${autosave}
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
 * Private shadow constructor helper
 *
 * This function creates a shadow wrapper around a given DOM element.
 *
 * It generates a <div> element with the class "shadow", appends the provided object (obj) as its child,
 * and returns the resulting DOM structure.
 *
 * @obj => The DOM element to be wrapped inside the shadow container.
 *
 * Returns the <div> element containing the original object as a child.
 */
saltos.bootstrap.__shadow_helper = obj => {
    const shadow = saltos.core.html(`<div class="shadow"></div>`);
    shadow.append(obj);
    return shadow;
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
    const instance = new bootstrap.Tooltip(obj, {
        trigger: 'hover',
        animation: false,
        delay: {
            show: 500,
            hide: 0,
        },
    });
    obj.addEventListener('focus', () => {
        instance.hide();
    });
    obj.addEventListener('click', () => {
        instance.hide();
    });
    obj.addEventListener('show.bs.tooltip', () => {
        saltos.bootstrap.__tooltip_hide();
    });
};

/**
 * Private tooltip hide helper
 *
 * This function is intended to hide all running tooltips, it's used when some widgets
 * replaces the old elements by new elements, if the tooltip is show when the transition
 * happens, it's necessary to remove it to prevent a blocking elements in the user
 * interface.
 */
saltos.bootstrap.__tooltip_hide = () => {
    document.querySelectorAll('[id^="tooltip"]').forEach(item => {
        if (!isNaN(parseFloat(item.id.slice(7)))) {
            item.remove();
        }
    });
};

/**
 * Label helper
 *
 * This function is a helper for label field, it is intended to returns the label object
 * or a void string, this is because if no label is present in the field argument, then
 * an empty string is returned, in the reception of the result, generally this is added
 * to an object and it is ignored because an empty string is not an element, this thing
 * is used by the optimizer to removes the unnecessary envelopment
 *
 * @field => the field that contains the label to be added if needed
 */
saltos.bootstrap.__label_helper = field => {
    saltos.core.check_params(field, ['label']);
    if (field.label == '') {
        return '';
    }
    const temp = saltos.core.copy_object(field);
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
    let obj = saltos.core.html(`<div></div>`);
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
    throw new Error('Unknown onclick helper fn typeof ' + typeof fn);
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
    throw new Error('Unknown onchange helper fn typeof ' + typeof fn);
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
            (new Function(fn)).call(obj);
            return;
        }
        if (typeof fn == 'function') {
            fn();
            return;
        }
        throw new Error('Unknown onenter helper fn typeof ' + typeof fn);
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
 * @label             => label of the menu
 * @id                => id used in the button element
 * @icon              => icon of the menu
 * @disabled          => this boolean allow to disable this menu entry
 * @active            => this boolean marks the option as active
 * @onclick           => the callback used when the user select the menu
 * @dropdown_menu_end => this trick allow to open the dropdown menu from the end to start
 * @menu              => with this option, you can specify an array with the contents of the dropdown menu
 *
 * @label    => label of the menu
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
    const obj = saltos.core.html(`<ul class="${args.class}"></ul>`);
    for (const key in args.menu) {
        const val = args.menu[key];
        saltos.core.check_params(val, ['label', 'icon',
            'disabled', 'active', 'onclick', 'dropdown_menu_end', 'id']);
        saltos.core.check_params(val, ['menu'], []);
        let disabled = '';
        if (saltos.core.eval_bool(val.disabled)) {
            disabled = 'disabled';
        }
        let active = '';
        if (saltos.core.eval_bool(val.active)) {
            active = 'active';
        }
        if (val.menu.length) {
            let dropdown_menu_end = '';
            if (saltos.core.eval_bool(val.dropdown_menu_end)) {
                dropdown_menu_end = 'dropdown-menu-end';
            }
            const temp = saltos.core.html(`
                <li class="nav-item dropdown">
                    <button id="${val.id}" class="nav-link dropdown-toggle ${disabled} ${active}"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ${val.label}
                    </button>
                    <ul class="dropdown-menu shadow ${dropdown_menu_end}">
                    </ul>
                </li>
            `);
            if (val.icon) {
                temp.querySelector('button').prepend(saltos.core.html(`<i class="bi bi-${val.icon}"></i>`));
            }
            if (val.label && val.icon) {
                temp.querySelector('i').classList.add('me-1');
            }
            for (const key2 in val.menu) {
                const val2 = val.menu[key2];
                saltos.core.check_params(val2, ['label', 'icon',
                    'disabled', 'active', 'onclick', 'divider', 'id']);
                let disabled2 = '';
                if (saltos.core.eval_bool(val2.disabled)) {
                    disabled2 = 'disabled';
                }
                let active2 = '';
                if (saltos.core.eval_bool(val2.active)) {
                    active2 = 'active';
                }
                if (saltos.core.eval_bool(val2.divider)) {
                    const temp2 = saltos.core.html(`<li><hr class="dropdown-divider"></li>`);
                    temp.querySelector('ul').append(temp2);
                } else {
                    const temp2 = saltos.core.html(`
                        <li><button id="${val2.id}" class="dropdown-item ${disabled2} ${active2}">
                            ${val2.label}
                        </button></li>`);
                    if (val2.icon) {
                        temp2.querySelector('button').prepend(
                            saltos.core.html(`<i class="bi bi-${val2.icon}"></i>`));
                    }
                    if (val2.label && val2.icon) {
                        temp2.querySelector('i').classList.add('me-1');
                    }
                    if (!saltos.core.eval_bool(val2.disabled)) {
                        saltos.bootstrap.__onclick_helper(temp2, val2.onclick);
                    }
                    temp.querySelector('ul').append(temp2);
                }
            }
            obj.append(temp);
        } else {
            const temp = saltos.core.html(`
                <li class="nav-item">
                    <button id="${val.id}" class="nav-link ${disabled} ${active}">${val.label}</button>
                </li>
            `);
            if (val.icon) {
                temp.querySelector('button').prepend(saltos.core.html(`<i class="bi bi-${val.icon}"></i>`));
            }
            if (val.label && val.icon) {
                temp.querySelector('i').classList.add('me-1');
            }
            if (!saltos.core.eval_bool(val.disabled)) {
                saltos.bootstrap.__onclick_helper(temp, val.onclick);
            }
            obj.append(temp);
        }
    }
    return obj;
};

/**
 * Navbar constructor helper
 *
 * This component creates a navbar intended to be used as header
 *
 * @id    => the id used by the object
 * @brand => contains an object with the label, image, width and height to be used
 * @color => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @pos   => position of the navbar, can be fixed-top, fixed-bottom, sticky-top, sticky-bottom
 * @class => class added to the navbar item
 *
 * @label  => text used in the brand
 * @image  => filename of the brand image
 * @alt    => alt text used in the brand image
 * @width  => width of the brand image
 * @height => height of the brand image
 * @class  => class added to the navbar-brand item
 *
 * @items => contains an array with the objects that will be added to the collapse
 *
 * Notes:
 *
 * If you want to use an image that uses all height of the navbar, you can set the class and
 * brand.class to py-0, the main idea is to use a combination of paddings with a brand to
 * gets a navbar of 56px of height
 */
saltos.bootstrap.navbar = args => {
    saltos.core.check_params(args, ['id', 'color', 'pos', 'class']);
    saltos.core.check_params(args, ['brand'], {});
    saltos.core.check_params(args.brand, ['label', 'image', 'alt', 'width', 'height', 'class']);
    saltos.core.check_params(args, ['items'], []);
    if (!args.color) {
        args.color = 'primary';
    }
    const obj = saltos.core.html(`
        <nav class="navbar navbar-expand-md navbar-dark shadow-lg bg-${args.color} ${args.pos} ${args.class}">
            <div class="container-fluid">
                <div class="navbar-brand ${args.brand.class}"></div>
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
    if (args.brand.image != '') {
        obj.querySelector('.navbar-brand').append(saltos.core.html(`
            <img src="${args.brand.image}" alt="${args.brand.alt}"
                width="${args.brand.width}" height="${args.brand.height}"/>
        `));
    }
    if (args.brand.label != '') {
        obj.querySelector('.navbar-brand').append(saltos.core.html(`
            ${args.brand.label}
        `));
    }
    for (const key in args.items) {
        const val = args.items[key];
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
        const bool = typeof saltos.bootstrap.__modal.instance == 'object';
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
    let temp = '';
    if (saltos.core.eval_bool(args.static)) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    if (!args.color) {
        args.color = 'primary';
    }
    // Note: removed the fade class in the first div, the old class was "modal fade"
    const obj = saltos.core.html(`
        <div class="modal" id="${args.id}" tabindex="-1"
            aria-labelledby="${args.id}_label" aria-hidden="true" ${temp}>
            <div class="modal-dialog shadow ${args.class}">
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
    const instance = new bootstrap.Modal(obj);
    saltos.bootstrap.__modal.obj = obj;
    saltos.bootstrap.__modal.instance = instance;
    obj.addEventListener('shown.bs.modal', event => {
        obj.querySelectorAll('[autofocus]').forEach(item => {
            item.focus();
        });
        const buttons = obj.querySelectorAll('button');
        if (buttons.length > 1) {
            obj.addEventListener('keydown', event => {
                let focusedIndex = Array.from(buttons).indexOf(document.activeElement);
                const key = saltos.core.get_keyname(event);
                if (event.altKey || event.ctrlKey || event.shiftKey) {
                    // Nothing to do
                } else if (key == 'rightArrow') {
                    const nextIndex = focusedIndex + 1;
                    if (nextIndex < buttons.length) {
                        event.preventDefault();
                        buttons[nextIndex].focus();
                    }
                } else if (key == 'leftArrow') {
                    if (focusedIndex < 0) {
                        focusedIndex = buttons.length;
                    }
                    const prevIndex = focusedIndex - 1;
                    if (prevIndex >= 0) {
                        event.preventDefault();
                        buttons[prevIndex].focus();
                    }
                }
            });
        }
    });
    obj.addEventListener('hide.bs.modal', event => {
        obj.querySelectorAll('[autoclose]').forEach(item => {
            item.click();
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
        const bool = typeof saltos.bootstrap.__offcanvas.instance == 'object';
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
    let temp = [];
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
    const valid_positions = ['start', 'end', 'top', 'bottom', 'left', 'right'];
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
    const obj = saltos.core.html(`
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
    const instance = new bootstrap.Offcanvas(obj);
    saltos.bootstrap.__offcanvas.obj = obj;
    saltos.bootstrap.__offcanvas.instance = instance;
    obj.addEventListener('shown.bs.offcanvas', event => {
        if (saltos.core.eval_bool(args.resize)) {
            const width = obj.offsetWidth;
            const item = document.getElementById('screen');
            item.classList.add('position-absolute');
            if (args.pos == 'start') {
                item.style.left = `${width}px`;
            }
            if (args.pos == 'end') {
                item.style.left = '0';
            }
            item.style.width = `calc(100% - ${width}px)`;
        }
        obj.querySelectorAll('[autofocus]').forEach(item => {
            item.focus();
        });
    });
    obj.addEventListener('hide.bs.offcanvas', event => {
        obj.querySelectorAll('[autoclose]').forEach(item => {
            item.click();
        });
    });
    obj.addEventListener('hidden.bs.offcanvas', event => {
        if (saltos.core.eval_bool(args.resize)) {
            const item = document.getElementById('screen');
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
    const hash = md5(JSON.stringify(args));
    if (document.querySelector(`.toast[hash=x${hash}]`)) {
        return false;
    }
    // Continue
    if (!args.color) {
        args.color = 'primary';
    }
    const obj = saltos.core.html(`
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
    const toast = new bootstrap.Toast(obj);
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
 * adding features suck as combinations of keys like ctrl+shift+f or ctrl+delete
 *
 * @obj => the object that you want to enable the accesskey feature
 */
window.addEventListener('keydown', event => {
    document.querySelectorAll('[data-bs-accesskey]:not([data-bs-accesskey=""])').forEach(obj => {
        const temp = obj.getAttribute('data-bs-accesskey').split('+');
        let useAlt = false;
        let useCtrl = false;
        let useShift = false;
        let key = null;
        for (let i = 0,len = temp.length; i < len; i++) {
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
                    key = temp[i];
                    break;
            }
        }
        let count = 0;
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
        if (key == saltos.core.get_keyname(event)) {
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
});

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
 * Check bs theme
 *
 * This function checks the bs theme
 *
 * @theme => Can be auto, light or dark
 */
saltos.bootstrap.check_bs_theme = theme => {
    const themes = ['auto', 'light', 'dark'];
    return themes.includes(theme);
};

/**
 * Set bs theme
 *
 * This function sets the bs theme
 *
 * @theme => Can be auto, light or dark
 */
saltos.bootstrap.set_bs_theme = theme => {
    if (!saltos.bootstrap.check_bs_theme(theme)) {
        throw new Error(`bs_theme ${theme} not found`);
    }
    saltos.bootstrap.window_match_media.removeEventListener(
        'change', saltos.bootstrap.set_data_bs_theme);
    switch (theme) {
        case 'auto':
            saltos.bootstrap.set_data_bs_theme(saltos.bootstrap.window_match_media);
            saltos.bootstrap.window_match_media.addEventListener(
                'change', saltos.bootstrap.set_data_bs_theme);
            break;
        case 'light':
            saltos.bootstrap.set_data_bs_theme({matches: false});
            break;
        case 'dark':
            saltos.bootstrap.set_data_bs_theme({matches: true});
            break;
    }
    saltos.storage.setItem('saltos.bootstrap.bs_theme', theme);
};

/**
 * Get bs theme
 *
 * Retrieve the bs_theme stored in the localStorage
 */
saltos.bootstrap.get_bs_theme = () => {
    return saltos.storage.getItem('saltos.bootstrap.bs_theme');
};

/**
 * Check css theme
 *
 * This function checks the css theme
 *
 * @theme => Can be default or one of the themes
 */
saltos.bootstrap.check_css_theme = theme => {
    const themes = ['default',
        'black', 'blue', 'cyan', 'gray', 'green', 'indigo',
        'orange', 'pink', 'purple', 'red', 'teal', 'yellow',
    ];
    return themes.includes(theme);
};

/**
 * Set css theme
 *
 * This function sets the css theme
 *
 * @theme => Can be default or one of the themes
 */
saltos.bootstrap.set_css_theme = theme => {
    if (!saltos.bootstrap.check_css_theme(theme)) {
        throw new Error(`css_theme ${theme} not found`);
    }
    let file;
    if (theme == 'default') {
        file = 'lib/bootstrap/bootstrap.min.css';
    } else {
        file = `lib/themes/dist/bootstrap.${theme}.min.css`;
    }
    document.querySelectorAll('link[rel=stylesheet]').forEach(item => {
        const found1 = item.href.includes('bootstrap/bootstrap.min.css');
        const found2 = item.href.includes('themes/dist/bootstrap.') && item.href.includes('.min.css');
        if (found1 || found2) {
            item.removeAttribute('integrity');
            item.href = item.href.replace(item.href, file);
        }
    });
    saltos.storage.setItem('saltos.bootstrap.css_theme', theme);
};

/**
 * Get css theme
 *
 * Retrieve the css_theme stored in the localStorage
 */
saltos.bootstrap.get_css_theme = () => {
    return saltos.storage.getItem('saltos.bootstrap.css_theme');
};
