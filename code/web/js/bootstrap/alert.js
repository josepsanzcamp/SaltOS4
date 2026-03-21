
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
 * @alert       => id, class, title, text, body, value, label, color
 */

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
    saltos.core.check_params(field, ['class', 'id', 'title', 'text', 'body',
                                     'close', 'color', 'shadow', 'rounded']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let obj = saltos.core.html(`
        <div class="alert alert-${color} ${rounded} ${shadow} ${field.class} mb-0 border-0"
            role="alert" id="${field.id}"></div>
    `);
    if (field.title !== '') {
        obj.append(saltos.core.html(`<h4>${field.title}</h4>`));
        if (field.text + field.body === '') {
            obj.querySelector('h4').classList.add('mb-0');
        }
    }
    if (field.text !== '') {
        obj.append(saltos.core.html(`<p>${field.text}</p>`));
        if (field.body === '') {
            obj.querySelector('p').classList.add('mb-0');
        }
    }
    if (field.body !== '') {
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
