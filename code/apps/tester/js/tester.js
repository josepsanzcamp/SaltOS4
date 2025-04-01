
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
 * Tester application
 *
 * This application implements the typical features associated with a tester,
 * showcasing functionalities such as modals, toasts, and offcanvas elements.
 */

/**
 * Tester object
 *
 * This object stores all the functions used by the Tester application,
 * providing the necessary methods for interaction and testing features.
 */
saltos.tester = {};

/**
 * Campo 8: Alert functionality
 *
 * This function triggers a simple alert box when called, showcasing a button-click behavior.
 */
saltos.tester.campo8 = () => {
    alert('button onclick');
};

/**
 * Campo 9: Modal functionality
 *
 * This function displays a Bootstrap modal with a title, body, and footer. The modal includes:
 * - Text placeholders for content.
 * - A dropdown menu for additional options.
 * - Footer buttons to accept or cancel actions, with corresponding console logs.
 */
saltos.tester.campo9 = () => {
    saltos.bootstrap.modal({
        static: false,
        title: 'Title',
        close: 'Close',
        body: `
            <div>
                Some text as placeholder. In real life you can have the elements you have
                chosen. Like, text, images, lists, etc.
            </div>
            <div class='dropdown mt-3'>
                <button class='btn btn-secondary dropdown-toggle' type='button'
                data-bs-toggle='dropdown'>
                    Dropdown button
                </button>
                <ul class='dropdown-menu'>
                    <li><a class='dropdown-item' href='#'>Action</a></li>
                    <li><a class='dropdown-item' href='#'>Another action</a></li>
                    <li><a class='dropdown-item' href='#'>Something else here</a></li>
                </ul>
            </div>
        `,
        footer: (() => {
            const obj = saltos.core.html('<div></div>');
            obj.append(saltos.bootstrap.field({
                type: 'button',
                label: 'Accept',
                onclick: () => {
                    console.log('OK');
                    saltos.bootstrap.modal('close');
                }
            }));
            obj.append(saltos.bootstrap.field({
                type: 'button',
                label: 'Cancel',
                class: 'ms-1',
                onclick: () => {
                    console.log('KO');
                    saltos.bootstrap.modal('close');
                },
            }));
            return obj;
        })()
    });
};

/**
 * Campo 10: Offcanvas functionality
 *
 * This function displays a Bootstrap offcanvas element with a title and body content.
 * The body includes:
 * - Text placeholders for content.
 * - A dropdown menu for additional options.
 */
saltos.tester.campo10 = () => {
    saltos.bootstrap.offcanvas({
        static: false,
        //class: 'offcanvas-start',
        title: 'Title',
        close: 'Close',
        body: `
            <div>
                Some text as placeholder. In real life you can have the elements you have
                chosen. Like, text, images, lists, etc.
            </div>
            <div class='dropdown mt-3'>
                <button class='btn btn-secondary dropdown-toggle' type='button'
                data-bs-toggle='dropdown'>
                    Dropdown button
                </button>
                <ul class='dropdown-menu'>
                    <li><a class='dropdown-item' href='#'>Action</a></li>
                    <li><a class='dropdown-item' href='#'>Another action</a></li>
                    <li><a class='dropdown-item' href='#'>Something else here</a></li>
                </ul>
            </div>
        `,
    });
};

/**
 * Campo 11: Toast functionality
 *
 * This function displays a Bootstrap toast notification with customizable attributes, including:
 * - A title and subtitle for context.
 * - Body content with dynamic timestamp information.
 */
saltos.tester.campo11 = () => {
    saltos.bootstrap.toast({
        //class: 'text-bg-primary',
        close: 'Close',
        title: 'Hello World',
        subtitle: 'Well then',
        body: 'Well then, hello world ' + new Date(),
    });
};

/**
 * Campo 22: Open statistics page
 *
 * This function opens the SaltOS statistics page in a new browser tab or window.
 */
saltos.tester.campo23 = () => {
    window.open('https://www.saltos.org/portal/es/estadisticas');
};
