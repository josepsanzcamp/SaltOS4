
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
 * @chartjs     => id, mode, data, value, label, color
 */

/**
 * Chart.js constructor helper
 *
 * This function creates a chart using the chart.js library, to do this requires de follow arguments:
 *
 * @id    => the id used by the object
 * @mode  => to specify what kind of plot do you want to do: can be bar, line, doughnut, pie
 * @data  => the data used to plot the graph, see the data argument used by the graph.js library
 * @label => this parameter is used as text for the label
 *
 * Notes:
 *
 * To be more practice and for stetic reasons, I'm adding to all datasets the borderWidth = 1
 *
 * This widget requires the chartjs library and can be loaded automatically using the require
 * feature:
 *
 * @lib/chartjs/chart.umd.min.js
 */
saltos.bootstrap.__field.chartjs = field => {
    saltos.core.check_params(field, ['id', 'mode', 'data', 'shadow', 'rounded']);
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    let obj = saltos.core.html(`
        <div><canvas id="${field.id}"></canvas></div>
    `);
    for (const key in field.data.datasets) {
        field.data.datasets[key].borderWidth = 1;
    }
    const element = obj.querySelector('canvas');
    // Add the placeholder
    const placeholder = saltos.bootstrap.__field.placeholder({
        color: field.color,
    });
    obj.prepend(placeholder);
    // Continue
    saltos.core.require([
        'lib/chartjs/chart.umd.min.js',
    ], () => {
        placeholder.remove();
        element.setAttribute('class', `form-control ${rounded} ${shadow}`);
        new Chart(element, {
            type: field.mode,
            data: field.data,
        });
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
