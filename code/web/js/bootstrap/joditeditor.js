
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
 * @joditeditor => id, class, PL, value, DS, RO, RQ, AF, AK, rows, label, color, height, OC
 */

/**
 * Joditeditor constructor helper
 *
 * This function returns a textarea object with the joditeditor plugin enabled
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
 * This widget requires the joditeditor library and can be loaded automatically using the require
 * feature:
 *
 * @lib/joditeditor/jodit.fat.min.css
 * @lib/joditeditor/jodit.fat.min.js
 *
 * The returned object contains a textarea with two new properties like joditeditor and set,
 * the first contains the joditeditor object and the second is a function used to update the
 * value of the joditeditor, intended to load new data.
 */
saltos.bootstrap.__field.joditeditor = field => {
    saltos.core.check_params(field, ['height', 'color', 'disabled', 'rounded']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
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
    saltos.core.require([
        'lib/joditeditor/jodit.fat.min.css',
        'lib/joditeditor/jodit.fat.min.js',
    ], () => {
        placeholder.remove();
        const buttons = [
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'ul', 'ol', '|',
            'outdent', 'indent', '|',
            'link', '|',
            'brush', '|',
            'undo', 'redo', '|',
            'image', 'paragraph', 'table', 'hr', '|',
            'source',
        ];
        const editor = new Jodit(element, {
            tabIndex: 0,
            toolbarAdaptive: true,
            toolbarSticky: true,
            uploader: {
                insertImageAsBase64URI: true
            },
            statusbar: false,
            buttons: buttons,
            buttonsMD: buttons,
            buttonsSM: buttons,
            buttonsXS: buttons,
            language: saltos.gettext.get_short(),
            minHeight: field.height,
            disabled: false,
            addNewLine: false,
        });
        element.joditeditor = editor;
    });
    // Program the set feature
    element.set = value => {
        if (!('joditeditor' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(value);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('joditeditor' in element)) {
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
        element.joditeditor.value = value;
    };
    // Program the disabled feature
    element.set_disabled = bool => {
        if (!('joditeditor' in element)) {
            setTimeout(() => element.set_disabled(bool), 1);
            return;
        }
        element.joditeditor.setReadOnly(bool);
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
            .jodit-container {
                --jd-border-radius-default: ${rounded};
            }
            .jodit-workplace {
                border-radius: 0 0 ${rounded} ${rounded};
            }
        </style>
    `));
    // Fix for border color
    if (color !== 'none') {
        obj.append(saltos.core.html(`
            <style>
                :root {
                    --jd-color-border: var(--bs-${color}-border-subtle);
                }
            </style>
        `));
    }
    // Fix for dark mode
    obj.append(saltos.core.html(`
        <style>
            :root[data-bs-theme="dark"] {
                --jd-color-background-light-gray: #1e1e1e;
                --jd-color-background-default: #1e1e1e;
                --jd-color-panel: #2a2a2a;
                --jd-color-icon: #b5b5b5;
            }
        </style>
    `));
    return obj;
};
