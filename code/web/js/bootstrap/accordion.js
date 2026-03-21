
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
 * @accordion   => id, flush, multiple, items, label
 */

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
    saltos.core.check_params(field, ['id', 'flush', 'multiple', 'shadow']);
    saltos.core.check_params(field, ['items'], []);
    if (saltos.core.eval_bool(field.flush)) {
        field.flush = 'accordion-flush';
    }
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let obj = saltos.core.html(`
        <div class="accordion ${shadow} ${field.flush}" id="${field.id}"></div>
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
