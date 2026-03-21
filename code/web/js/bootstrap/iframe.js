
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
 * @iframe      => id, class, src, srcdoc, height, label, color
 */

/**
 * Iframe constructor helper
 *
 * This function returns an iframe object, you can pass the follow arguments:
 *
 * @id     => the id used by the object
 * @src    => the value used as src parameter
 * @srcdoc => the value used as srcdoc parameter
 * @class  => allow to add more classes to the default form-control
 * @height => the height used as style.minHeight parameter
 * @label  => this parameter is used as text for the label
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @invert => enable the invert feature in the widget contents
 *
 * Notes:
 *
 * This function allow to put contents in the srcdoc, as an extra feature, this content is
 * embedded with a doctype with html, head and body, includes the default saltos font and
 * to provide a security layer, this function creates an iframe with a sandbox and add to
 * the srcdoc a meta to configure the CSP that must apply to the contents
 *
 * To fix some issues with the iframe that adds some space between the bottom of the iframe
 * and the parent container, we must to add the d-block to convert it from inline to block
 */
saltos.bootstrap.__field.iframe = field => {
    saltos.core.check_params(field, ['src', 'srcdoc', 'id', 'class', 'invert',
                                     'height', 'color', 'shadow', 'rounded']);
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
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <div class="form-control p-0 ${shadow} ${rounded} ${border}" style="line-height: 0">
            <iframe id="${field.id}" frameborder="0" class="${rounded} ${field.class} w-100"></iframe>
        </div>
    `);
    const element = obj.querySelector('iframe');
    if (field.src !== '') {
        element.src = field.src;
    }
    if (field.srcdoc !== '') {
        element.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(field.srcdoc);
    }
    if (field.height !== '') {
        element.style.minHeight = field.height;
    }
    // When new load is detected
    element.addEventListener('load', event => {
        // Program the resize that computes the height
        const resizeObserver = new ResizeObserver(entries => {
            requestAnimationFrame(() => {
                const doc = element.contentWindow &&
                            element.contentWindow.document &&
                            element.contentWindow.document.documentElement;
                if (doc) {
                    element.style.height = doc.offsetHeight + 2 + 'px';
                }
            });
        });
        resizeObserver.observe(element);
        // To open the links in a new window and prevent the same origin error
        element.contentWindow.document.querySelectorAll('a, area').forEach(link => {
            link.setAttribute('target', '_blank');
        });
        // To propagate the keydown event suck as escape key
        element.contentWindow.addEventListener('keydown', event => {
            window.dispatchEvent(new KeyboardEvent('keydown', {
                altKey: event.altKey,
                ctrlKey: event.ctrlKey,
                shiftKey: event.shiftKey,
                keyCode: event.keyCode,
            }));
        });
    });
    // Program the set in the input first
    element.set = value => {
        if (typeof value === 'object') {
            if ('src' in value) {
                element.src = value.src;
            } else if ('srcdoc' in value) {
                element.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(value.srcdoc);
            }
        } else {
            if (field.src !== '') {
                element.src = value;
            } else if (field.srcdoc !== '') {
                element.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(value);
            }
        }
    };
    obj = saltos.bootstrap.__label_combine(field, obj);
    // Fix for dark mode
    if (saltos.core.eval_bool(field.invert)) {
        const button_id = field.id + '_dark';
        const button_value = saltos.bootstrap.__button_value_helper(field.id);
        if (button_value) {
            element.style.background = '#fff';
            element.style.filter = 'invert(.9)';
        }
        const button = saltos.bootstrap.field({
            id: button_id,
            type: 'switch',
            class: 'float-end',
            color: color,
            value: button_value,
            onchange: event => {
                const bool = button.querySelector('input').checked;
                if (bool) {
                    element.style.background = '#fff';
                    element.style.filter = 'invert(.9)';
                } else {
                    element.style.background = '';
                    element.style.filter = '';
                }
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

/**
 * Srcdoc helper
 *
 * This function adds the needed environment to the html to improve the
 * render of the html, this function is intended to be used inside the
 * iframw widget
 *
 * @html => the code that you need to process
 */
saltos.bootstrap.__iframe_srcdoc_helper = html => {
    const font = 'lib/atkinson/atkinson.min.css';
    return `<!doctype html><html><head><meta charset="utf-8">
    <style>body { margin: 0; padding: 9px 12px; }</style>
    <link href="${font}" rel="stylesheet" integrity="">
    <style>:root { font-family: var(--bs-font-sans-serif); }</style>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self';
        style-src 'self' 'unsafe-inline' ${window.location.origin};
        font-src 'self' ${window.location.origin};
        img-src 'self' data: ${window.location.origin};">
    </head><body>${html}</body></html>`;
};
