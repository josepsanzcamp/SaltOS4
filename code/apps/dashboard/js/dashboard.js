
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Dashboard application
 *
 * This module implements the typical features associated with a dashboard,
 * allowing dynamic interaction with various elements and functionalities.
 */

/**
 * Main object
 *
 * Contains all the logic and code for the SaltOS framework related to the dashboard.
 */
saltos.dashboard = {};

/**
 * Initialization of dashboard
 *
 * This method sets up listeners, configures the catalog layout, and initializes
 * the dashboard widgets based on user-defined or default configurations.
 */
saltos.dashboard.init = arg => {
    // Remove the pb-3 of the screen
    document.getElementById('one').classList.remove('pb-3');
    // Sets a listener to update dashboard-related elements on event triggers
    saltos.window.set_listener('saltos.dashboard.update', event => {
        saltos.hash.trigger();
    });
};
