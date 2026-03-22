
/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
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
    saltos.core.check_params(args, ['id', 'color', 'pos', 'class', 'shadow']);
    saltos.core.check_params(args, ['brand'], {});
    saltos.core.check_params(args.brand, ['label', 'image', 'alt', 'width', 'height', 'class']);
    saltos.core.check_params(args, ['items'], []);
    if (!args.color) {
        args.color = 'primary';
    }
    let shadow = 'shadow';
    if (args.shadow) {
        shadow = args.shadow;
    }
    const obj = saltos.core.html(`
        <nav class="navbar navbar-expand-md ${shadow} py-0
            bg-${args.color}-subtle ${args.pos} ${args.class}">
            <div class="container-fluid ps-0">
                <div class="navbar-brand py-0 me-1 ${args.brand.class}"></div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#${args.id}" aria-controls="${args.id}"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse ps-2 ps-md-0" id="${args.id}">
                </div>
            </div>
        </nav>
    `);
    if (args.brand.image !== '') {
        obj.querySelector('.navbar-brand').append(saltos.core.html(`
            <img src="${args.brand.image}" alt="${args.brand.alt}"
                width="${args.brand.width}" height="${args.brand.height}"
                class="bg-${args.color}"/>
        `));
    }
    if (args.brand.label !== '') {
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
    saltos.core.check_params(args, ['class', 'shadow', 'rounded']);
    saltos.core.check_params(args, ['menu'], []);
    const obj = saltos.core.html(`<ul class="${args.class}"></ul>`);
    obj.append(saltos.core.html(`
        <style>
            .dropdown-menu-scroll {
                max-height: calc(100vh - 100px);
                overflow-y: auto;
            }
        </style>
    `));
    let shadow = 'shadow';
    if (args.shadow) {
        shadow = args.shadow;
    }
    let rounded = 'rounded';
    if (args.rounded) {
        rounded = args.rounded;
    }
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
                    <ul class="dropdown-menu ${rounded} ${shadow} ${dropdown_menu_end} dropdown-menu-scroll">
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
