
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
 * Driver type1 module
 *
 * This fie contains the needed implementation for all driver features
 */

/**
 * Driver type1 object
 *
 * This object stores the functions used by the type1 driver
 */
saltos.app.__driver.type1 = {};

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type1.template = `
    <div id="top"></div>
    <div class="container-fluid">
        <div class="row">
            <div id="left" class="col-auto p-0 overflow-auto-xl d-flex"></div>
            <div id="one" class="col-xl py-3 overflow-auto-xl"></div>
            <div id="right" class="col-auto p-0 overflow-auto-xl d-flex"></div>
        </div>
    </div>
    <div id="bottom"></div>
`;

/**
 * TODO
 *
 * TODO
 */
saltos.app.__driver.type1.init = saltos.app.__driver.type0.init;
saltos.app.__driver.type1.open = saltos.app.__driver.type0.open;
saltos.app.__driver.type1.close = saltos.app.__driver.type0.close;
