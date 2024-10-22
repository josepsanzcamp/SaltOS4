
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
 * Email application
 *
 * This application implements the tipical features associated to emails
 */

/**
 * Driver emails object
 *
 * This object stores the functions used by the emails driver
 */
saltos.driver.__types.emails = {};

/**
 * TODO
 *
 * TODO
 */
saltos.driver.__types.emails.template = arg => {
    const obj = saltos.driver.__types.type5.template();
    obj.setAttribute('type', 'emails');
    obj.querySelector('#one').classList.replace('col-xl', 'col-xl-4');
    return obj;
};

/**
 * TODO
 *
 * TODO
 */
saltos.driver.__types.emails.init = saltos.driver.__types.type5.init;
saltos.driver.__types.emails.open = saltos.driver.__types.type5.open;
saltos.driver.__types.emails.close = saltos.driver.__types.type5.close;
