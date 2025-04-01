
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
