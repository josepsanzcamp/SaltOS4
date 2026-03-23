
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
 * Offcanvas constructor helper object
 *
 * This object is used to store the element and the instance of the offcanvas
 */
saltos.bootstrap.__offcanvas = {};

/**
 * Offcanvas constructor helper
 *
 * This function creates a bootstrap offcanvas and open it, offers two ways of usage:
 *
 * 1) you can pass a string to get a quick action
 *
 * @close  => this string close the current offcanvas
 * @isopen => this string is used to check if some offcanvas is open at the moment
 *
 * 2) you can pass an object with the follow items, intended to open a new offcanvas
 *
 * @id       => the id used by the object
 * @pos      => allow to specify the position of the offcanvac (start, end, top or bottom)
 * @title    => title used by the offcanvas
 * @close    => text used in the close button for aria purposes
 * @body     => the content used in the offcanvas's body
 * @static   => forces the offcanvas to be static (prevent close by clicking outside the
 *              offcanvas or by pressing the escape key)
 * @backdrop => to configure the backdrop feature (true or false)
 * @keyboard => to configure the keyboard feature (true or false)
 * @color    => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * Returns a boolean that indicates if the offcanvas can be open or not
 *
 * Notes:
 *
 * This offcanvas will be destroyed (instance and element) when it closes, too is important
 * to undestand that only one offcanvas is allowed at each moment.
 *
 * Body allow to use a string containing a html fragment or an object, as the modal body.
 */
saltos.bootstrap.offcanvas = args => {
    // Helper actions
    if (args === 'close') {
        const bool = typeof saltos.bootstrap.__offcanvas.instance === 'object';
        if (bool) {
            saltos.bootstrap.__offcanvas.instance.hide();
        }
        return bool;
    }
    if (args === 'isopen') {
        return typeof saltos.bootstrap.__offcanvas.instance === 'object';
    }
    // Additional check
    if (typeof saltos.bootstrap.__offcanvas.instance === 'object') {
        return false;
    }
    // Normal operation
    saltos.core.check_params(args, ['id', 'pos', 'title', 'close', 'body',
                                    'color', 'static', 'backdrop', 'keyboard']);
    let temp = [];
    if (saltos.core.eval_bool(args.static)) {
        temp.push(`data-bs-backdrop="static"`);
        temp.push(`data-bs-keyboard="false"`);
    }
    if (saltos.core.eval_bool(args.backdrop)) {
        temp.push(`data-bs-backdrop="false"`);
        temp.push(`data-bs-keyboard="false"`);
    }
    if (saltos.core.eval_bool(args.keyboard)) {
        temp.push(`data-bs-keyboard="false"`);
    }
    temp = temp.join(' ');
    const valid_positions = ['start', 'end', 'top', 'bottom', 'left', 'right'];
    if (!valid_positions.includes(args.pos)) {
        args.pos = valid_positions[0];
    }
    if (args.pos === 'left') {
        args.pos = 'start';
    }
    if (args.pos === 'right') {
        args.pos = 'end';
    }
    if (!args.color) {
        args.color = 'primary';
    }
    const obj = saltos.core.html(`
        <div class="offcanvas offcanvas-${args.pos}" tabindex="-1" id="${args.id}"
            aria-labelledby="${args.id}_label" ${temp}>
            <div class="offcanvas-header bg-${args.color}-subtle">
                <h5 class="offcanvas-title" id="${args.id}_label">${args.title}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                    aria-label="${args.close}"></button>
            </div>
            <div class="offcanvas-body"></div>
        </div>
    `);
    document.body.append(obj);
    if (typeof args.body === 'string') {
        if (args.body !== '') {
            obj.querySelector('.offcanvas-body').append(saltos.core.html(args.body));
        }
    } else {
        obj.querySelector('.offcanvas-body').append(args.body);
    }
    const instance = new bootstrap.Offcanvas(obj);
    saltos.bootstrap.__offcanvas.obj = obj;
    saltos.bootstrap.__offcanvas.instance = instance;
    obj.addEventListener('shown.bs.offcanvas', event => {
        obj.querySelectorAll('[autofocus]').forEach(item => {
            item.focus();
        });
    });
    obj.addEventListener('hide.bs.offcanvas', event => {
        obj.querySelectorAll('[autoclose]').forEach(item => {
            item.click();
        });
    });
    obj.addEventListener('hidden.bs.offcanvas', event => {
        saltos.bootstrap.__offcanvas.instance.dispose();
        saltos.bootstrap.__offcanvas.obj.remove();
        delete saltos.bootstrap.__offcanvas.instance;
        delete saltos.bootstrap.__offcanvas.obj;
    });
    instance.show();
    return true;
};
