
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
saltos.emails.getmail = () => {
    saltos.app.form.screen('loading');
    var app = saltos.hash.get().split('/').at(1);
    saltos.core.ajax({
        url: `api/?app/${app}/action/getmail`,
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            for (var key in response.array) {
                saltos.app.toast('Response', response.array[key]);
            }
            saltos.window.send('saltos.emails.update');
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
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
    saltos.app.modal('Delete emails???', 'Do you want to delete the selected emails???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                saltos.app.form.screen('loading');
                var app = saltos.hash.get().split('/').at(1);
                ids = ids.join(',');
                saltos.core.ajax({
                    url: `api/?app/${app}/action/delete/${ids}`,
                    success: response => {
                        saltos.app.form.screen('unloading');
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        saltos.app.toast('Response', response.text);
                        saltos.window.send('saltos.emails.update');
                    },
                    error: request => {
                        saltos.app.form.screen('unloading');
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    token: saltos.token.get(),
                    lang: saltos.gettext.get(),
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
                saltos.app.form.screen('loading');
                var app = saltos.hash.get().split('/').at(1);
                var id = saltos.hash.get().split('/').at(3);
                saltos.core.ajax({
                    url: `api/?app/${app}/action/delete/${id}`,
                    success: response => {
                        saltos.app.form.screen('unloading');
                        if (!saltos.app.check_response(response)) {
                            return;
                        }
                        saltos.window.send('saltos.emails.update');
                        saltos.driver.close();
                    },
                    error: request => {
                        saltos.app.form.screen('unloading');
                        saltos.app.show_error({
                            text: request.statusText,
                            code: request.status,
                        });
                    },
                    token: saltos.token.get(),
                    lang: saltos.gettext.get(),
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
    // TODO
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.download = (file) => {
    saltos.core.ajax({
        url: 'api/?' + file.substr(1),
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            var a = document.createElement('a');
            a.download = response.file.name;
            response.file.type = 'application/force-download'; // to force download dialog
            a.href = `data:${response.file.type};base64,${response.file.data}`;
            a.click();
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.setter = what => {
    var app = saltos.hash.get().split('/').at(1);
    var id = saltos.hash.get().split('/').at(3);
    saltos.core.ajax({
        url: `api/?app/${app}/action/setter/${id}`,
        data: JSON.stringify({
            'what': what,
        }),
        method: 'post',
        content_type: 'application/json',
        success: response => {
            saltos.app.form.screen('unloading');
            if (!saltos.app.check_response(response)) {
                return;
            }
            saltos.window.send('saltos.emails.update');
            saltos.hash.trigger();
        },
        error: request => {
            saltos.app.form.screen('unloading');
            saltos.app.show_error({
                text: request.statusText,
                code: request.status,
            });
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
    });
};
