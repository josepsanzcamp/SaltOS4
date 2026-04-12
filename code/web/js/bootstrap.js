
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
 * @hr          => id, class, style
 * @label       => id, class, label, tooltip
 * @placeholder => id, color, height, label
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
    // Remove the disable background color
    if (!saltos.bootstrap.__cssinit) {
        document.body.append(saltos.core.html(`
            <style>
                .form-control:disabled,
                .form-select:disabled,
                .disabled .ts-control {
                    background-color: inherit!important;
                    color: var(--bs-secondary-color);
                }
            </style>
        `));
        saltos.bootstrap.__cssinit = true;
    }
    // Fix when some attributes need the fix_key feature
    for (const key in field) {
        const new_key = saltos.core.fix_key(key);
        if (new_key !== key && !(new_key in field)) {
            field[new_key] = field[key];
            delete field[key];
        }
    }
    // Continue
    saltos.core.check_params(field, ['id', 'type']);
    if (field.id === '') {
        field.id = saltos.core.uniqid();
    }
    if (typeof saltos.bootstrap.__field[field.type] !== 'function') {
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
    if (field.class === '') {
        field.class = 'container-fluid';
    }
    const obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some container class is found
    let found = false;
    obj.classList.forEach(item => {
        if (['container', 'd-none'].includes(item)) {
            found = true;
        }
        if (item.substr(0, 10) === 'container-') {
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
    if (field.class === '') {
        field.class = 'row';
    }
    const obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some row class is found
    let found = false;
    obj.classList.forEach(item => {
        if (['row', 'd-none'].includes(item)) {
            found = true;
        }
        if (item.substr(0, 4) === 'row-') {
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
    if (field.class === '') {
        field.class = 'col';
    }
    const obj = saltos.bootstrap.__field.div(field);
    // Checks to guarantee that some col class is found
    let found = false;
    obj.classList.forEach(item => {
        if (['col', 'd-none'].includes(item)) {
            found = true;
        }
        if (item.substr(0, 4) === 'col-') {
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
 * Label constructor helper
 *
 * This function returns a label object, you can pass some arguments as:
 *
 * @id       => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default form-label
 * @label    => this parameter is used as text for the label
 * @tooltip  => this parameter raise the title flag
 * @required => this parameter add a red bold asterisk to the end of the label
 */
saltos.bootstrap.__field.label = field => {
    saltos.core.check_params(field, ['id', 'class', 'label', 'tooltip', 'required']);
    const obj = saltos.core.html(`
        <label for="${field.id}" class="form-label ${field.class}"
            data-bs-title="${field.tooltip}">${field.label}</label>
    `);
    if (saltos.core.eval_bool(field.required)) {
        obj.append(saltos.core.html('<span class="fw-bold text-danger ms-1">*</span>'));
    }
    if (field.tooltip !== '') {
        saltos.bootstrap.__tooltip_helper(obj);
    }
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
    saltos.core.check_params(field, ['id', 'color', 'height', 'label', 'rounded']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="w-100 h-100 placeholder-glow text-${color}"
             aria-hidden="true" style="height:${field.height}!important">
            <span class="w-100 h-100 placeholder ${rounded}"></span>
        </div>
    `);
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};

/**
 * Determines whether the current Bootstrap theme is set to dark mode.
 *
 * @returns {boolean} True if the active theme is "dark", otherwise false.
 */
saltos.bootstrap.__is_dark_helper = () => {
    return document.documentElement.dataset.bsTheme === 'dark';
};

/**
 * Returns the current UI mode based on the Bootstrap theme.
 *
 * @returns {string} "dark" if dark mode is active, otherwise "light".
 */
saltos.bootstrap.__get_mode_helper = () => {
    return saltos.bootstrap.__is_dark_helper() ? 'dark' : 'light';
};

/**
 * Generates a storage key for a button based on its field ID and the current mode.
 *
 * @param {string} field_id - The unique identifier of the field.
 * @returns {string} A composed key in the format "field_id/mode".
 */
saltos.bootstrap.__button_key_helper = field_id => {
    const mode_id = saltos.bootstrap.__get_mode_helper();
    return `${field_id}/${mode_id}`;
};

/**
 * Retrieves the stored button value for the given field and current mode.
 * Falls back to the current theme mode if no stored value exists.
 *
 * @param {string} field_id - The unique identifier of the field.
 * @returns {boolean} The stored boolean value, or the current theme mode as default.
 */
saltos.bootstrap.__button_value_helper = field_id => {
    const key = saltos.bootstrap.__button_key_helper(field_id);
    let value = saltos.storage.getItem(key);
    if (value !== null) {
        value = saltos.core.eval_bool(value);
    } else {
        value = saltos.bootstrap.__is_dark_helper();
    }
    return value;
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
    if (field.label === '') {
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
    if (typeof fn === 'string') {
        obj.addEventListener('click', new Function(fn));
        return;
    }
    if (typeof fn === 'function') {
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
    if (typeof fn === 'string') {
        obj.addEventListener('change', new Function(fn));
        return;
    }
    if (typeof fn === 'function') {
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
        if (saltos.core.get_keycode(event) !== 13) {
            return;
        }
        if (typeof fn === 'string') {
            (new Function(fn)).call(obj);
            return;
        }
        if (typeof fn === 'function') {
            fn();
            return;
        }
        throw new Error('Unknown onenter helper fn typeof ' + typeof fn);
    });
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
        const temp = obj.dataset.bsAccesskey.split('+');
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
        if (key === saltos.core.get_keyname(event)) {
            count++;
        }
        if (count === 4) {
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
 * Field Types
 *
 * This list defines the widgets that are treated as data input fields.
 *
 * These field types are used by functions such as:
 * - saltos.driver.reset
 * - saltos.app.get_data
 * - saltos.app.check_required
 * - saltos.app.form_disabled
 */
saltos.bootstrap.__field_types = [
    'text', 'hidden', 'integer', 'float', 'color', 'date', 'time', 'datetime',
    'select', 'multiselect', 'checkbox', 'switch', 'password', 'file',
    'textarea', 'joditeditor', 'codemirror', 'handsontable', 'tags', 'onetag',
];
