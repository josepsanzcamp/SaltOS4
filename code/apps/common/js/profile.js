
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
 *
 * A successful update replaces the current token with a fresh one, exactly as a new login
 * would, so besides the success message this also triggers the same saltos.app.login event
 * used by the login and renew screens, to refresh the current view with the new session.
 *
 * Note: the outcome must to be checked using the response status instead of the token
 * presence, because unlike login/renew, here a valid token already exists before calling
 * this function, so it would stay valid (and truthy) even when the update itself fails
 *
 * Note: the success message uses saltos.app.toast instead of saltos.app.modal, following
 * the same convention used elsewhere in the app (see driver.js) for successful actions;
 * every hashchange event unconditionally closes any open modal (see hash.js), and the
 * saltos.app.login event triggered right below fires exactly that, so a modal here would
 * close itself before ever being seen
 */
saltos.profile.authupdate = async () => {
    saltos.backup.restore('right');
    if (!saltos.app.check_required()) {
        return;
    }
    const data = saltos.app.get_data(true);
    const response = await saltos.authenticate.authupdate(data.oldpass, data.newpass, data.renewpass);
    if (!response || response.status !== 'ok') {
        return;
    }
    saltos.app.toast('Response', 'Password updated successfully');
    if (['', 'app/login'].includes(saltos.hash.get())) {
        saltos.hash.set('app/dashboard');
    }
    saltos.window.send('saltos.app.login');
};
