
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
 * @echarts => id, mode, data, value, label, color
 */

/**
 * ECharts constructor helper
 *
 * This function creates a chart using the chart.js library, to do this requires de follow arguments:
 *
 * @id    => element id
 * @data  => the data used to plot the graph, see the chart.setOption of the echarts library
 * @ratio => aspect ratio used in the plot
 * @label => this parameter is used as text for the label
 *
 * Notes:
 *
 * This widget requires the echarts library and can be loaded automatically using the require
 * feature:
 *
 * @lib/echarts/echarts.min.js
 */
saltos.bootstrap.__field.echarts = field => {
    saltos.core.check_params(field, ['id', 'data', 'shadow', 'rounded', 'ratio']);
    let shadow = 'shadow-sm';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let ratio = '16/9';
    if (field.ratio !== '') {
        ratio = field.ratio;
    }
    // Tricks for line and bar plots
    const type = field.data.series[0].type;
    if (['line', 'bar'].includes(type)) {
        field.data.tooltip = {
            trigger: 'axis',
        };
        if (!('legend' in field.data)) {
            field.data.legend = {
                top: 10,
            };
        }
        if (!('grid' in field.data)) {
            field.data.grid = {
                'left': 20,
                'right': 20,
                'top': 40,
                'bottom': 20,
                'containLabel': true,
            };
        }
        if (type === 'bar') {
            field.data.xAxis.axisLabel = {
                interval: 0,
                rotate: 30,
            };
            field.data.grid.bottom = 0;
        }
    }
    // Tricks for pie and doughnut
    if (['pie', 'doughnut'].includes(type)) {
        field.data.tooltip = {
            trigger: 'item',
        };
        if (!('title' in field.data)) {
            field.data.title = {
                top: 0,
                subtext: field.data.series[0].name,
            };
            field.data.series[0].top = 20;
        }
        if (type === 'pie') {
            field.data.series[0].radius = '70%';
        }
        if (type === 'doughnut') {
            field.data.series[0].type = 'pie';
            field.data.series[0].radius = ['40%', '70%'];
        }
    }
    // Continue
    if (!('darkMode' in field.data)) {
        field.data.darkMode = saltos.bootstrap.__is_dark_helper();
    }
    let obj = saltos.core.html(`
        <div style="height:100%">
            <div id="${field.id}" style="height:100%;aspect-ratio:${ratio}"></div>
        </div>
    `);
    const element = obj.querySelector('div');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.prepend(placeholder);
    // Continue
    saltos.core.require([
        'lib/echarts/echarts.min.js',
    ], () => {
        placeholder.remove();
        element.setAttribute('class', `form-control ${rounded} ${shadow} p-0`);
        const chart = echarts.init(element);
        chart.setOption(field.data);
        window.addEventListener('resize', () => chart.resize());
        element.chart = chart;
    });
    // Fix for a rounded corners
    let _rounded = 'var(--bs-border-radius)';
    if (saltos.core.is_number(field.rounded.replace('rounded-', ''))) {
        const index = parseInt(field.rounded.replace('rounded-', ''), 10);
        switch (index) {
            case 0:
                _rounded = '0';
                break;
            case 1:
                _rounded = 'var(--bs-border-radius-sm)';
                break;
            case 2:
                _rounded = 'var(--bs-border-radius)';
                break;
            case 3:
                _rounded = 'var(--bs-border-radius-lg)';
                break;
            case 4:
                _rounded = 'var(--bs-border-radius-xl)';
                break;
            case 5:
                _rounded = 'var(--bs-border-radius-xxl)';
                break;
        }
    }
    obj.append(saltos.core.html(`
        <style>
            canvas {
                border-radius: ${_rounded};
            }
        </style>
    `));
    // Fix for dark mode
    new MutationObserver(() => {
        element.chart.setOption({
            darkMode: saltos.bootstrap.__is_dark_helper()
        });
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-bs-theme'],
    });
    // Continue
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
