
/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Profile application
 *
 * This application implements the typical features associated with user profiles,
 * such as managing themes, language settings, and authentication updates.
 */

/**
 * Main object
 *
 * Contains all the logic and code for the SaltOS framework related to the profile application.
 */
saltos.profile = {};

/**
 * Initialization of profile settings
 *
 * This method initializes the profile settings by setting the current Bootstrap theme,
 * custom CSS theme, and language preferences in the respective input fields.
 */
saltos.profile.init = arg => {
    document.getElementById('bs_theme').value = saltos.bootstrap.get_bs_theme();
    document.getElementById('css_theme').value = saltos.bootstrap.get_css_theme();
    document.getElementById('lang').value = saltos.gettext.get();
};

/**
 * Update authentication settings
 *
 * This method restores the previous state of the application if necessary,
 * validates required fields, and then updates the authentication credentials
 * using the provided old password, new password, and its confirmation.
 */
saltos.profile.authupdate = () => {
    saltos.backup.restore('right');
    if (!saltos.app.check_required()) {
        return;
    }
    const data = saltos.app.get_data(true);
    saltos.authenticate.authupdate(data.oldpass, data.newpass, data.renewpass);
};
