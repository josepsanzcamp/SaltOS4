
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
 */

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
    saltos.core.check_params(args, ['id', 'class', 'close', 'title', 'subtitle', 'body', 'color', 'timeout']);
    if (document.querySelectorAll('.toast-container').length === 0) {
        // Remove border only for light mode
        document.body.append(saltos.core.html(`
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
            </div>
            <style>
                :root:not([data-bs-theme="dark"]) .toast,
                :root:not([data-bs-theme="dark"]) .toast-header {
                    border: 0;
                }
            </style>
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
    if (!args.timeout) {
        args.timeout = 5000;
    }
    const obj = saltos.core.html(`
        <div id="${args.id}" class="toast ${args.class}" role="alert" aria-live="assertive"
            aria-atomic="true" hash="x${hash}">
            <div class="toast-header bg-${args.color}-subtle">
                <strong class="me-auto">${args.title}</strong>
                <small>${args.subtitle}</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast"
                    aria-label="${args.close}"></button>
            </div>
            <div class="toast-body">
            </div>
        </div>
    `);
    document.querySelector('.toast-container').append(obj);
    if (typeof args.body === 'string') {
        if (args.body !== '') {
            obj.querySelector('.toast-body').append(saltos.core.html(args.body));
        }
    } else {
        obj.querySelector('.toast-body').append(args.body);
    }
    // Disabled the native autohide
    const toast = new bootstrap.Toast(obj, {
        animation: false,
        autohide: false,
    });
    // The next code implements the autohide with visibility check
    let timer = null;
    const handleVisibility = () => {
        clearTimeout(timer);
        document.removeEventListener('visibilitychange', handleVisibility);
        if (!document.hidden) {
            timer = setTimeout(() => toast.hide(), args.timeout);
        } else {
            document.addEventListener('visibilitychange', handleVisibility);
        }
    };
    obj.addEventListener('hidden.bs.toast', event => {
        clearTimeout(timer);
        document.removeEventListener('visibilitychange', handleVisibility);
        toast.dispose();
        obj.remove();
    });
    toast.show();
    handleVisibility();
    return true;
};
