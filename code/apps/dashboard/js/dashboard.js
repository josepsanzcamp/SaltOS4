
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
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
