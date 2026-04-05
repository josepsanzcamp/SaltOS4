
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
 * Modal constructor helper object
 *
 * This object is used to store the element and the instance of the modal
 */
saltos.bootstrap.__modal = {};

/**
 * Modal constructor helper
 *
 * This function creates a bootstrap modal and open it, offers two ways of usage:
 *
 * 1) you can pass a string to get a quick action
 *
 * @close   => this string close the current modal
 * @isopen  => this string is used to check if some modal is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new modal
 *
 * @id      => the id used by the object
 * @class   => allow to add more classes to the default dialog
 * @title   => title used by the modal
 * @close   => text used in the close button for aria purposes
 * @body    => the content used in the modal's body
 * @footer  => the content used in the modal's footer
 * @static  => forces the modal to be static (prevent close by clicking outside the modal or
 *             by pressing the escape key)
 * @color   => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Returns a boolean that indicates if the modal can be open or not
 *
 * Notes:
 *
 * This modal will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one modal is allowed at each moment.
 *
 * Body and footer allow to use a string containing a html fragment or an object, and
 * the footer too detects that the string contains something different to void, and if
 * void content is detected, then the footer is removed.
 *
 * As an extra bonus, this widget has some tricks to improve the style of the footer, as
 * you can see in the modal-footer part by removing the border and the top padding.
 */
saltos.bootstrap.modal = args => {
    // Helper actions
    if (args === 'close') {
        const bool = typeof saltos.bootstrap.__modal.instance === 'object';
        if (bool) {
            saltos.bootstrap.__modal.instance.hide();
        }
        return bool;
    }
    if (args === 'isopen') {
        return typeof saltos.bootstrap.__modal.instance === 'object';
    }
    // Additional check
    if (typeof saltos.bootstrap.__modal.instance === 'object') {
        return false;
    }
    // Normal operation
    saltos.core.check_params(args, ['id', 'class', 'title', 'close', 'body',
                                    'footer', 'static', 'color', 'shadow', 'rounded']);
    let temp = '';
    if (saltos.core.eval_bool(args.static)) {
        temp = `data-bs-backdrop="static" data-bs-keyboard="false"`;
    }
    if (!args.color) {
        args.color = 'primary';
    }
    let shadow = 'shadow-sm';
    if (args.shadow) {
        shadow = args.shadow;
    }
    let rounded = 'rounded';
    let rounded_top = 'rounded-top';
    if (args.rounded) {
        rounded = args.rounded;
        rounded_top = args.rounded.replace('rounded', 'rounded-top');
    }
    // Note: removed the fade class in the first div, the old class was "modal fade"
    const obj = saltos.core.html(`
        <div class="modal" id="${args.id}" tabindex="-1"
            aria-labelledby="${args.id}_label" aria-hidden="true" ${temp}>
            <div class="modal-dialog ${shadow} ${rounded} ${args.class}">
                <div class="modal-content ${rounded}">
                    <div class="modal-header bg-${args.color}-subtle ${rounded_top}">
                        <h1 class="modal-title fs-5" id="${args.id}_label">${args.title}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="${args.close}"></button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                    </div>
                </div>
            </div>
        </div>
    `);
    document.body.append(obj);
    if (typeof args.body === 'string') {
        if (args.body !== '') {
            obj.querySelector('.modal-body').append(saltos.core.html(args.body));
        }
    } else if (saltos.core.is_iterable(args.body)) {
        obj.querySelector('.modal-body').append(...args.body);
    } else {
        obj.querySelector('.modal-body').append(args.body);
    }
    if (typeof args.footer === 'string') {
        if (args.footer !== '') {
            obj.querySelector('.modal-footer').append(saltos.core.html(args.footer));
        } else {
            obj.querySelector('.modal-footer').remove();
        }
    } else {
        obj.querySelector('.modal-footer').append(args.footer);
    }
    const instance = new bootstrap.Modal(obj);
    saltos.bootstrap.__modal.obj = obj;
    saltos.bootstrap.__modal.instance = instance;
    obj.addEventListener('shown.bs.modal', event => {
        obj.querySelectorAll('[autofocus]').forEach(item => {
            item.focus();
        });
        const buttons = obj.querySelectorAll('button');
        if (buttons.length > 1) {
            obj.addEventListener('keydown', event => {
                let focusedIndex = Array.from(buttons).indexOf(document.activeElement);
                const key = saltos.core.get_keyname(event);
                if (event.altKey || event.ctrlKey || event.shiftKey) {
                    // Nothing to do
                } else if (key === 'rightArrow') {
                    const nextIndex = focusedIndex + 1;
                    if (nextIndex < buttons.length) {
                        event.preventDefault();
                        buttons[nextIndex].focus();
                    }
                } else if (key === 'leftArrow') {
                    if (focusedIndex < 0) {
                        focusedIndex = buttons.length;
                    }
                    const prevIndex = focusedIndex - 1;
                    if (prevIndex >= 0) {
                        event.preventDefault();
                        buttons[prevIndex].focus();
                    }
                }
            });
        }
    });
    obj.addEventListener('hide.bs.modal', event => {
        obj.querySelectorAll('[autoclose]').forEach(item => {
            item.click();
        });
    });
    obj.addEventListener('hidden.bs.modal', event => {
        saltos.bootstrap.__modal.instance.dispose();
        saltos.bootstrap.__modal.obj.remove();
        delete saltos.bootstrap.__modal.instance;
        delete saltos.bootstrap.__modal.obj;
    });
    instance.show();
    return true;
};
