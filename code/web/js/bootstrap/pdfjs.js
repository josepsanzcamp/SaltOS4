
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
 * @pdfjs       => id, class, value, label, color
 */

/**
 * Pdfjs constructor helper
 *
 * This function creates and returns a pdfviewer object, to do this they use the pdf.js library.
 *
 * @id     => the id used to set the reference for to the object
 * @class  => allow to set the class to the div object used to allocate the widget
 * @src    => the file that contains the pdf document
 * @srcdoc => the data that contains the pdf document
 * @label  => this parameter is used as text for the label
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @invert => enable the invert feature in the widget contents
 *
 * Notes:
 *
 * This widget requires the pdfjs library and can be loaded automatically using the require
 * feature:
 *
 * @lib/pdfjs/pdf.min.mjs
 * @lib/pdfjs/pdf.worker.min.mjs
 *
 * The last file (the worker) is loaded by the library and not by SaltOS, is for this reason
 * that this file not appear in the next requires
 *
 * Change scale causes issues in scrollTop when pdfjs is used inside a modal, to prevent this,
 * the two updates to the pdfViewer.currentScaleValue = 'update' will add a control to fix
 * that modal scrollTop is the same.
 */
saltos.bootstrap.__field.pdfjs = field => {
    saltos.core.check_params(field, ['id', 'class', 'src', 'srcdoc',
                                     'invert', 'color', 'shadow', 'rounded']);
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let border = ['border', `border-${color}-subtle`];
    if (field.color === 'none') {
        border = ['border-0'];
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="${field.class}"></div>
    `);
    let src = field.src;
    if (field.srcdoc !== '') {
        src = {data: atob(field.srcdoc)};
    } else {
        src = new URL(src, window.location.href).href;
    }
    obj.src = src;
    const element = obj;
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: color,
    });
    obj.append(placeholder);
    saltos.core.require([
        'lib/pdfjs/pdf.min.mjs',
    ], async () => {
        // To guarantee that the mjs is ready, this bug only appear in google chrome.
        while (typeof pdfjsLib !== 'object') {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
        // Continue
        const url = new URL('lib/pdfjs/pdf.worker.min.mjs', window.location.href).href;
        pdfjsLib.GlobalWorkerOptions.workerSrc = url;
        pdfjsLib.getDocument(src).promise.then(pdf => {
            if (!pdf.numPages) {
                return;
            }
            const render = num => {
                pdf.getPage(num).then(page => {
                    const width = element.clientWidth;
                    let viewport = page.getViewport({scale: 1});
                    const scale = 2 * width / viewport.width;
                    viewport = page.getViewport({scale: scale});
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise.then(() => {
                        if (num === 1) {
                            placeholder.remove();
                        }
                        const div = document.createElement('div');
                        div.style.lineHeight = 0;
                        div.append(canvas);
                        element.append(div);
                        canvas.style.width = '100%';
                        div.classList.add('form-control');
                        div.classList.add(rounded);
                        canvas.classList.add(rounded);
                        div.classList.add('p-0');
                        div.classList.add(shadow);
                        div.classList.add(...border);
                        if (saltos.core.eval_bool(field.invert)) {
                            const button_value = saltos.bootstrap.__button_value_helper(field.id);
                            if (button_value) {
                                canvas.style.filter = 'invert(.9)';
                            }
                        }
                        if (num < pdf.numPages) {
                            render(num + 1);
                        }
                    });
                });
            };
            render(1);
        }, error => {
            throw error;
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    // Fix for dark mode
    if (saltos.core.eval_bool(field.invert)) {
        const button_id = field.id + '_dark';
        const button_value = saltos.bootstrap.__button_value_helper(field.id);
        const button = saltos.bootstrap.field({
            id: button_id,
            type: 'switch',
            class: 'float-end',
            color: color,
            value: button_value,
            onchange: event => {
                const bool = button.querySelector('input').checked;
                element.querySelectorAll('canvas').forEach(item => {
                    if (bool) {
                        item.style.filter = 'invert(.9)';
                    } else {
                        item.style.filter = '';
                    }
                });
                if (event.isTrusted) {
                    const button_key = saltos.bootstrap.__button_key_helper(field.id);
                    saltos.storage.setItem(button_key, bool);
                }
            },
        });
        button.querySelector('input').style.marginLeft = '0px';
        obj.prepend(button);
        new MutationObserver(() => {
            const button_value = saltos.bootstrap.__button_value_helper(field.id);
            button.querySelector('input').checked = button_value;
            button.querySelector('input').dispatchEvent(new Event('change'));
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });
    }
    return obj;
};
