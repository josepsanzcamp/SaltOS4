
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * TODO
 *
 * TODO
 */

/**
 * Driver object
 *
 * This object stores the functions used by the layouts widgets and must work with all screens
 */
saltos.app.driver = {};

/**
 * Driver code
 *
 * This is the code that defines all methods of the driver object, the main idea of this object
 * is that only contains a forwarder functions to the driver used at each moment, to do it, it
 * is using the screen attribute placed in the document.body element.
 */
(() => {
    var fns = ['init', 'open', 'close', 'search', 'reset', 'more', 'insert', 'update', 'delete'];
    for (var key in fns) {
        let val = fns[key];
        saltos.app.driver[val] = arg => {
            var screen = document.body.getAttribute('screen');
            if (!screen) {
                throw new Error(`screen not found`);
            }
            if (!saltos.app.__driver.hasOwnProperty(screen)) {
                throw new Error(`driver ${screen} not found`);
            }
            if (!saltos.app.__driver[screen].hasOwnProperty(val)) {
                throw new Error(`function ${val} for driver ${screen} not found`);
            }
            saltos.app.__driver[screen][val](arg);
        };
    }
})();

/**
 * Driver internal object
 *
 * This object stores the functions used by the main driver
 */
saltos.app.__driver = {};
