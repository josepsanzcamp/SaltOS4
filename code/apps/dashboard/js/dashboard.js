
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
 * Dashboard application
 *
 * This module implements the typical features associated with a dashboard,
 * allowing dynamic interaction with various elements and functionalities.
 */

/**
 * Initialization of dashboard
 *
 * This method sets up listeners, configures the catalog layout, and initializes
 * the dashboard widgets based on user-defined or default configurations.
 */
saltos.form.dashboard = data => {
    const _append = data['#attr'].append;
    const _widgets = data.value.widgets;
    const _configs = data.value.configs;

    const div = saltos.core.html(`<div class="grid-stack"></div>`);
    document.getElementById(_append).append(div);

    const grid = GridStack.init({
        column: 12,
        margin: '6 8',
        cellHeight: 50,
        columnOpts: {
            breakpoints: [
                {w: 1200, c: 8},
                {w: 768,  c: 4},
                {w: 480,  c: 1}
            ]
        },
        handle: '.widget-drag-handle',
    });
    div.grid = grid;
    grid.batchUpdate();

    const extra = {};
    const key = 'app/dashboard/widgets/default';
    if (key in _configs) {
        try {
            const layout = JSON.parse(_configs[key]);
            for (const i in layout) {
                extra[layout[i].id] = layout[i];
            }
        } catch (error) {
            // nothing to do
        }
    }

    for (const i in _widgets) {
        const id = 'widget_' + _widgets[i]['#attr'].id;
        const x = _widgets[i]['#attr'].x;
        const y = _widgets[i]['#attr'].y;
        const w = _widgets[i]['#attr'].w;
        const h = _widgets[i]['#attr'].h;

        let option = {
            id: id,
            'x': x,
            'y': y,
            'w': w,
            'h': h,
        };
        if (id in extra) {
            option = extra[id];
        }
        const widget = grid.addWidget(option);

        const content = widget.querySelector('.grid-stack-item-content');
        content.append(...saltos.form.layout({[i]: _widgets[i]}, true));
        content.classList.add('shadow-sm', 'rounded');

        // Trick to improve the visual effect caused by rounded-pill buttons
        // Also replaces rounded with rounded-pill, which is the default style used by buttons
        if (saltos.core.fix_key(i) === 'button') {
            content.style.marginLeft = '-3px';
            content.style.marginRight = '-3px';
            content.classList.replace('rounded', 'rounded-pill');
        }

        const handle = document.createElement('div');
        handle.className = 'widget-drag-handle';
        handle.innerHTML = '<i class="bi bi-arrows-move"></i>';
        widget.append(handle);
    }

    grid.batchUpdate(false);

    const save =  (event, element) => {
        element.querySelectorAll('*').forEach(item => {
            if (item.chart) {
                item.chart.resize();
            }
        });

        const layout = grid.save(false).map(i => ({
            id: i.id,
            'x': i.x,
            'y': i.y,
            'w': i.w || 1,
            'h': i.h || 1,
        }));
        saltos.app.ajax({
            url: 'app/dashboard/config',
            data: {
                'name': 'default',
                'val': JSON.stringify(layout),
            },
            success: response => {
                saltos.window.send('saltos.dashboard.update');
            },
        });
    };

    grid.on('dragstop', save);
    grid.on('resizestop', save);

};
