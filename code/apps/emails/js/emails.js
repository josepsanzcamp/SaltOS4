
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
saltos.emails.init = arg => {
    if (['create', 'view', 'close'].includes(arg)) {
        saltos.core.when_visible('list', () => {
            const obj = document.getElementById('list').parentElement;
            obj.querySelectorAll('button').forEach(_this => {
                _this.classList.remove('active');
                _this.removeAttribute('aria-current');
            });
        });
    }

    if (['list', 'view'].includes(arg)) {
        const id = saltos.hash.get().split('/').at(-1);
        if (!isNaN(parseInt(id))) {
            saltos.core.when_visible(`button_${id}`, () => {
                const button = document.getElementById(`button_${id}`);
                button.classList.add('active');
                button.setAttribute('aria-current', 'true');
                button.classList.remove('fw-bold');
                button.querySelector('h5').classList.remove('fw-bold');
                const is_new = button.querySelector('small.text-success');
                if (is_new && ['New', T('New')].includes(is_new.innerHTML)) {
                    is_new.classList.remove('text-success');
                    is_new.innerHTML = T('Read');
                }
            });
        }
    }
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.server = () => {
    saltos.app.ajax({
        url: `app/emails/action/server`,
        proxy: 'network',
        success: response => {
            for (const key in response) {
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
    let ids = saltos.app.checkbox_ids(document.getElementById('list').parentElement);
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
                    proxy: 'network',
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
                const id = saltos.hash.get().split('/').at(3);
                saltos.app.ajax({
                    url: `app/emails/delete/${id}`,
                    proxy: 'network',
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
    saltos.backup.restore('two,one');
    if (!saltos.app.check_required()) {
        saltos.app.toast('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    const data = saltos.app.get_data(true);
    let action = saltos.hash.get().split('/').at(3);
    if (action === undefined) {
        action = '';
    } else {
        action = '/' + action;
    }
    let email_id = saltos.hash.get().split('/').at(4);
    if (email_id === undefined) {
        email_id = '';
    } else {
        email_id = '/' + email_id;
    }
    saltos.app.ajax({
        url: `app/emails/create/sendmail${action}${email_id}`,
        data: data,
        proxy: 'network,queue',
        success: response => {
            if (response.status == 'ok') {
                saltos.app.toast('Response', response.text);
                saltos.window.send('saltos.emails.update');
                saltos.autosave.clear('two,one');
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
    const id = saltos.hash.get().split('/').at(3);
    saltos.app.ajax({
        url: `app/emails/view/setter/${id}`,
        proxy: 'network',
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
    const old_account = saltos.emails.old_account;
    const new_account = document.getElementById('from').value;
    const body = document.getElementById('body').value;
    const cc = document.getElementById('cc').value;
    const state_crt = document.getElementById('state_crt').value;
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
            saltos.form.data(response.data);
        },
    });
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.viewpdf = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('list').parentElement);
    if (!ids.length) {
        saltos.app.modal(
            'Select emails',
            'You must select the desired emails that you want see in the PDF file',
            {
                color: 'warning',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.driver.open('app/emails/view/viewpdf/' + ids);
};

/**
 * TODO
 *
 * TODO
 */
saltos.emails.download = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('list').parentElement);
    if (!ids.length) {
        saltos.app.modal(
            'Select emails',
            'You must select the desired emails that you want download in the PDF file',
            {
                color: 'warning',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.app.download('app/emails/view/download/' + ids);
};

/**
 * TODO
 *
 * TODO
 */
saltos.core.when_visible('from', () => {
    saltos.emails.old_account = document.getElementById('from').value;
});
