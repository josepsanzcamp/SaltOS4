
/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Bootstrap helper module
 *
 * This fie contains useful functions related to the bootstrap widgets, allow to create widgets and
 * other plugins suck as plots or rich editors
 *
 * @codemirror  => id, class, PL, value, DS, RO, RQ, AF, AK, rows, mode, label, color, height, OC
 */

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
    saltos.core.check_params(field, ['mode', 'height', 'color', 'disabled', 'indent', 'rounded']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = ['border', `border-${color}-subtle`];
    if (field.color === 'none') {
        border = ['border-0'];
    }
    let mode = field.mode;
    if (['json', 'js'].includes(mode)) {
        mode = 'javascript';
    }
    const obj = saltos.core.html(`<div></div>`);
    obj.append(saltos.bootstrap.__label_helper(field));
    obj.append(saltos.bootstrap.__textarea_helper(saltos.core.copy_object(field)));
    const element = obj.querySelector('textarea');
    element.style.display = 'none';
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: color,
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
    const detect_theme = () => {
        return saltos.bootstrap.__is_dark_helper() ? 'dracula' : 'default';
    };
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
            theme: detect_theme(),
        });
        element.codemirror = cm;
        element.parentElement.classList.add('form-control', 'p-0', ...border);
        element.nextElementSibling.style.height = 'auto';
        cm.on('change', cm.save);
        if (field.height !== '') {
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
            //~ element.nextElementSibling.classList.add('bg-body-secondary');
        } else {
            element.codemirror.setOption('readOnly', false);
            //~ element.nextElementSibling.classList.remove('bg-body-secondary');
        }
    };
    if (saltos.core.eval_bool(field.disabled)) {
        element.set_disabled(true);
    }
    // Fix for a rounded corners
    let rounded = 'var(--bs-border-radius)';
    if (saltos.core.is_number(field.rounded.replace('rounded-', ''))) {
        const index = parseInt(field.rounded.replace('rounded-', ''), 10);
        switch (index) {
            case 0:
                rounded = '0';
                break;
            case 1:
                rounded = 'var(--bs-border-radius-sm)';
                break;
            case 2:
                rounded = 'var(--bs-border-radius)';
                break;
            case 3:
                rounded = 'var(--bs-border-radius-lg)';
                break;
            case 4:
                rounded = 'var(--bs-border-radius-xl)';
                break;
            case 5:
                rounded = 'var(--bs-border-radius-xxl)';
                break;
        }
    }
    obj.append(saltos.core.html(`
        <style>
            .CodeMirror {
                border-radius: ${rounded};
            }
        </style>
    `));
    // Fix for dark mode
    new MutationObserver(() => {
        element.codemirror.setOption('theme', detect_theme());
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-bs-theme'],
    });
    // Fix for font-size
    obj.append(saltos.core.html(`
        <style>
            .CodeMirror {
                font-size: 0.9rem;
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
