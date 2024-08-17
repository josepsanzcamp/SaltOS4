
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
 * Main object
 *
 * This object contains all SaltOS code
 */
saltos.emails = {};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.server = () => {
    saltos.app.ajax({
        url: `app/emails/action/server`,
        success: response => {
            for (var key in response) {
                saltos.app.toast('Response', response[key]);
            }
            saltos.window.send('saltos.emails.update');
        },
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.delete1 = () => {
    var ids = saltos.app.checkbox_ids(document.getElementById('list').parentElement);
    if (!ids.length) {
        saltos.app.modal(
            'Select emails',
            'You must select the desired emails to be deleted',
            {
                color: 'warning',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.app.modal('Delete emails???', 'Do you want to delete the selected emails???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                saltos.app.ajax({
                    url: `app/emails/delete/${ids}`,
                    success: response => {
                        saltos.app.toast('Response', response.text);
                        saltos.window.send('saltos.emails.update');
                    },
                });
            },
        },{
            label: 'No',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
        color: 'danger',
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.delete2 = () => {
    saltos.app.modal('Delete this email???', 'Do you want to delete this email???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                var id = saltos.hash.get().split('/').at(3);
                saltos.app.ajax({
                    url: `app/emails/delete/${id}`,
                    success: response => {
                        saltos.app.toast('Response', response.text);
                        saltos.window.send('saltos.emails.update');
                        saltos.driver.close();
                    },
                });
            },
        },{
            label: 'No',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
        color: 'danger',
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.send = () => {
    saltos.app.form.__backup.restore('two,one');
    if (!saltos.app.check_required()) {
        saltos.app.toast('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    var data = saltos.app.get_data(true);
    var action = saltos.hash.get().split('/').at(3);
    if (typeof action == 'undefined') {
        action = '';
    } else {
        action = '/' + action;
    }
    var email_id = saltos.hash.get().split('/').at(4);
    if (typeof email_id == 'undefined') {
        email_id = '';
    } else {
        email_id = '/' + email_id;
    }
    saltos.app.ajax({
        url: `app/emails/create/sendmail${action}${email_id}`,
        data: data,
        success: response => {
            if (response.status == 'ok') {
                saltos.app.toast('Response', response.text);
                saltos.window.send('saltos.emails.update');
                saltos.driver.close();
                return;
            }
            if (response.status == 'ko') {
                saltos.app.toast('Response', response.text, {color: 'danger'});
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.setter = what => {
    var id = saltos.hash.get().split('/').at(3);
    saltos.app.ajax({
        url: `app/emails/view/setter/${id}`,
        data: {
            'what': what,
        },
        success: response => {
            saltos.app.toast('Response', response.text);
            saltos.window.send('saltos.emails.update');
            saltos.hash.trigger();
        },
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.signature = () => {
    var old_account = saltos.emails.old_account;
    var new_account = document.getElementById('from').value;
    var body = document.getElementById('body').value;
    var cc = document.getElementById('cc').value;
    var state_crt = document.getElementById('state_crt').value;
    saltos.emails.old_account = new_account;
    saltos.app.ajax({
        url: `app/emails/create/signature`,
        data: {
            'old': old_account,
            'new': new_account,
            'body': body,
            'cc': cc,
            'state_crt': state_crt,
        },
        success: response => {
            saltos.app.form.data(response.data);
        },
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.core.when_visible('from', () => {
    saltos.emails.old_account = document.getElementById('from').value;
});
