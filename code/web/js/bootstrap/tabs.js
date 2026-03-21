
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
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
 * @tabs        => id, tabs, label, content, active, disabled, label
 * @pills       => id, tabs, label, content, active, disabled, label
 * @vpills      => id, tabs, label, content, active, disabled, label
 */

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
