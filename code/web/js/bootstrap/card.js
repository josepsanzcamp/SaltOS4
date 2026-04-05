
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
 * @card        => id, image, alt, header, footer, title, text, body, value, label, color
 */

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
    saltos.core.check_params(field, ['id', 'image', 'alt', 'header', 'footer',
                                     'title', 'text', 'body', 'color', 'shadow']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let obj = saltos.core.html(`<div class="card border-${color} ${shadow}" id="${field.id}"></div>`);
    if (field.image !== '') {
        obj.append(saltos.core.html(`
            <img src="${field.image}" class="card-img-top" alt="${field.alt}" />
        `));
    }
    if (field.header !== '') {
        obj.append(saltos.core.html(`
            <div class="card-header border-${color} text-bg-${color}">${field.header}</div>
        `));
    }
    obj.append(saltos.core.html(`<div class="card-body"></div>`));
    if (field.title !== '') {
        obj.querySelector('.card-body').append(saltos.core.html(`
            <h5 class="card-title">${field.title}</h5>
        `));
    }
    if (field.text !== '') {
        obj.querySelector('.card-body').append(saltos.core.html(`
            <p class="card-text">${field.text}</p>
        `));
    }
    if (field.body !== '') {
        obj.querySelector('.card-body').append(saltos.core.html(field.body));
    }
    if (field.footer !== '') {
        obj.append(saltos.core.html(`
            <div class="card-footer border-${color} bg-${color}-subtle">${field.footer}</div>
        `));
    }
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
