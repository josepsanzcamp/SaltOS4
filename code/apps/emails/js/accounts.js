
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
 * Emails accounts application
 *
 * This application contains the features of the emails accounts app, like the test of the
 * connection with the servers of the account.
 */

/**
 * Main object
 *
 * This object contains all SaltOS code related to the emails accounts.
 */
saltos.emails_accounts = {};

/**
 * Test connection
 *
 * This method sends the data of the account form to the server, that tests the connection
 * with the incoming or the outgoing server without saving the account, and shows the
 * result in a modal.
 *
 * @server => the server to test, incoming or outgoing
 */
saltos.emails_accounts.test = server => {
    const app = saltos.hash.get().split('/').at(1);
    const data = saltos.app.get_data(true);
    data.server = server;
    saltos.app.ajax({
        url: `app/${app}/action/test`,
        proxy: 'network',
        data: data,
        success: response => {
            // textContent because the message comes from the server
            const body = saltos.core.html('<div></div>');
            body.textContent = response.text;
            saltos.app.modal('Test connection', body, {
                color: response.status === 'ok' ? 'success' : 'danger',
            });
        },
    });
};
