
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
 *
 * @jstree      => id, open, onclick, data
 */

/**
 * JS Tree constructor helper
 *
 * This function returns a jstree object using the follow parameters:
 *
 * @id      => the id used to set the reference for to the object
 * @class    => allow to add more classes to the default table table-striped table-hover
 * @open    => the open boolean that open all nodes
 * @onclick => the callback that receives the id as argument of the selected item
 * @nodata   => text used when no data is found
 * @label    => this parameter is used as text for the label
 * @data    => the data used to make the tree, must to be an array with nodes
 *
 * Each node must contain the follow items:
 *
 * @id       => the id of the node (used in the onclick callback)
 * @text     => the text of the node
 * @children => an array with the nodes childrens
 */
saltos.bootstrap.__field.jstree = field => {
    saltos.core.check_params(field, ['id', 'class', 'open', 'onclick', 'nodata', 'color']);
    saltos.core.check_params(field, ['data'], []);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let obj = saltos.core.html(`<div id="${field.id}" class="${field.class}"></div>`);
    const element = obj;
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: color,
    });
    obj.append(placeholder);
    // Continue
    saltos.core.require([
        'lib/jstree/jstree.min.css',
        'lib/jstree/jstree.min.js',
        'lib/jstree/jstree.dark.min.css',
    ], () => {
        placeholder.remove();
        const instance = new jsTree({}, element);
        element.instance = instance;
        instance.on('select', event => {
            let val = event.node.data.text;
            if ('id' in event.node.data) {
                val = event.node.data.id;
            }
            if (!val) {
                return;
            }
            if (typeof field.onclick === 'string') {
                (new Function(field.onclick)).call(val);
                return;
            }
            if (typeof field.onclick === 'function') {
                field.onclick(val);
                return;
            }
            throw new Error('Unknown jstree onclick typeof ' + typeof field.onclick);
        });
        /* .jstree-node-text:hover { background:var(--bs-primary-bg-subtle); } */
        element.append(saltos.core.html(`
            <style>
                .jstree-node-text { color:var(--bs-${color}); }
                .jstree-node-text:hover { background:#fbec88; color:#373a3c; }
                .jstree-selected,
                .jstree-selected:hover { background:var(--bs-${color}); color:white; }
                .jstree-node-icon:before { background:var(--bs-${color}); }
                .jstree-node-icon:after { background:var(--bs-${color}); }
                .jstree-node-text:hover .jstree-node-icon:before { background:#373a3c; }
                .jstree-node-text:hover .jstree-node-icon:after { background:#373a3c; }
                .jstree-selected:hover .jstree-node-icon:before { background:white; }
                .jstree-selected:hover .jstree-node-icon:after { background:white; }
            </style>
        `));
    });
    element.set = data => {
        if (!('instance' in element)) {
            if (!('queue' in element)) {
                element.queue = [];
            }
            element.queue.push(data);
            if (!('timer' in element)) {
                element.timer = setInterval(() => {
                    if (!('instance' in element)) {
                        return;
                    }
                    clearInterval(element.timer);
                    while (element.queue.length) {
                        const item = element.queue.shift();
                        element.set(item);
                    }
                }, 1);
            }
            return;
        }
        // Check for data not found
        if (!data.length) {
            data = [{
                id: null,
                text: field.nodata,
            }];
        }
        // Continue
        element.instance.empty().create(data);
        if (saltos.core.eval_bool(field.open)) {
            element.instance.openAll();
        }
    };
    element.set(field.data);
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
