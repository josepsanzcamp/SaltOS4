
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
 * Email application
 *
 * This application implements typical features associated with email functionality,
 * such as initialization, server actions, email deletion, sending, setting states,
 * handling signatures, generating and downloading PDFs, viewing email sources, replying
 * and forwarding.
 */

/**
 * Main object
 *
 * This object contains all SaltOS code related to email operations.
 */
saltos.emails = {};

/**
 * Initialize the email application
 *
 * This method handles initialization tasks for different views (`create`, `view`, `close`, `list`)
 * in the email application. It manages UI updates, visibility behaviors, and email state changes.
 *
 * @param {string} arg Specifies the type of view or action to initialize.
 */
saltos.emails.init = arg => {
    // Handle list visibility and reset button states
    if (['create', 'view', 'close'].includes(arg)) {
        saltos.core.when_visible('list', () => {
            const obj = document.getElementById('list');
            obj.querySelectorAll('button').forEach(item => {
                item.classList.remove('active');
                item.removeAttribute('aria-current');
            });
        });
    }

    // Handle state changes for list and view
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

    // Handle initialization for creating emails
    if (['create'].includes(arg)) {
        saltos.core.when_visible('from', () => {
            saltos.emails.old_account = document.getElementById('from').value;
        });
        const type = document.getElementById('screen').getAttribute('type');
        if (type == 'type1') {
            document.getElementById('only').parentElement.parentElement.remove();
        }
    }

    // Handle initialization for viewing emails
    if (['view'].includes(arg)) {
        // Remove unnecessary fields
        if (document.getElementById('cc').value == '') {
            document.getElementById('cc').closest('.mb-3').remove();
        }
        if (document.getElementById('bcc').value == '') {
            document.getElementById('bcc').closest('.mb-3').remove();
        }
        if (document.getElementById('state_error').value == '') {
            document.getElementById('state_error').closest('.mb-3').remove();
        }

        // Remove unnecessary switches and buttons
        if (document.getElementById('is_outbox').value == '1') {
            document.getElementById('state_new').closest('.mb-3').remove();
            document.getElementById('state_reply').closest('.mb-3').remove();
            document.getElementById('state_spam').closest('.mb-3').remove();
            document.getElementById('reply').closest('.col-auto').remove();
            document.getElementById('new0').closest('li').remove();
            document.getElementById('new1').closest('li').remove();
            document.getElementById('spam0').closest('li').remove();
            document.getElementById('spam1').closest('li').remove();
        } else {
            document.getElementById('state_sent').closest('.mb-3').remove();
            document.getElementById('forward').closest('.col-auto').remove();
            if (document.getElementById('state_new').value == '1') {
                document.getElementById('new1').closest('li').remove();
            } else {
                document.getElementById('new0').closest('li').remove();
            }
            if (document.getElementById('state_spam').value == '1') {
                document.getElementById('spam1').closest('li').remove();
            } else {
                document.getElementById('spam0').closest('li').remove();
            }
        }
        if (document.getElementById('state_wait').value == '1') {
            document.getElementById('wait1').closest('li').remove();
        } else {
            document.getElementById('wait0').closest('li').remove();
        }

        // Remove unnecessary table if no files exist
        if (document.getElementById('num_files').value == '0') {
            document.getElementById('files').closest('.mb-3').remove();
        }

        // Load the email body with images
        saltos.core.when_visible('body', () => {
            const iframe = document.getElementById('body');
            if (!iframe) {
                return;
            }
            const hasimg = srcdoc => {
                return /data:image\/gif;base64,/.test(srcdoc);
            };
            if (!hasimg(iframe.srcdoc)) {
                return;
            }

            let id1 = saltos.hash.get().split('/').at(3);
            if (!saltos.core.is_number(id1)) {
                id1 = saltos.hash.get().split('/').at(4);
            }
            saltos.app.ajax({
                url: `app/emails/view/body/${id1}`,
                success: response => {
                    let id2 = saltos.hash.get().split('/').at(3);
                    if (!saltos.core.is_number(id2)) {
                        id2 = saltos.hash.get().split('/').at(4);
                    }
                    if (id1 != id2) {
                        return;
                    }
                    const iframe = document.getElementById('body');
                    if (!iframe) {
                        return;
                    }
                    iframe.srcdoc = saltos.bootstrap.__iframe_srcdoc_helper(response.srcdoc);
                },
                loading: false,
            });
        });

        // Remove specific button for certain screen types
        const type = document.getElementById('screen').getAttribute('type');
        if (type == 'type1') {
            document.getElementById('only').parentElement.parentElement.remove();
        }
    }
};

/**
 * Perform server-related actions for emails
 *
 * This method triggers server-side actions for emails and displays the responses in toasts.
 */
saltos.emails.server = () => {
    saltos.app.ajax({
        url: 'app/emails/action/server',
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
 * Delete selected emails
 *
 * This method deletes multiple emails selected by the user, after showing a confirmation modal.
 */
saltos.emails.delete1 = () => {
    let ids = saltos.app.checkbox_ids(document.getElementById('list'));
    if (!ids.length) {
        saltos.app.modal(
            'Select emails',
            'You must select the desired emails to be deleted',
            {
                color: 'danger',
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
 * Delete a single email
 *
 * This method deletes the currently viewed email after showing a confirmation modal.
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
 * Send an email
 *
 * This method validates the required fields and sends the email data to the server.
 * It handles responses and clears unsaved data on success.
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
 * Set email state
 *
 * This function updates the state of a specific email identified by its ID. The updated state is sent
 * to the server, and the UI is refreshed afterward.
 *
 * @param {string} what Specifies the state to set for the email.
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
 * Add signature to email
 *
 * This function applies the email signature based on the selected account and its related data.
 * The signature is retrieved from the server and updated in the email form.
 */
saltos.emails.signature = () => {
    const old_account = saltos.emails.old_account;
    const new_account = document.getElementById('from').value;
    const body = document.getElementById('body').value;
    const cc = document.getElementById('cc').value;
    const state_crt = document.getElementById('state_crt').value;
    saltos.emails.old_account = new_account;
    saltos.app.ajax({
        url: 'app/emails/create/signature',
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
 * View email source
 *
 * This function opens the raw source of the selected email for viewing in the driver.
 */
saltos.emails.source = () => {
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/emails/view/source/${id}`);
};

/**
 * Reply to an email
 *
 * This function opens the reply form for the currently selected email.
 */
saltos.emails.reply = () => {
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/emails/create/reply/${id}`);
};

/**
 * Reply to all recipients of an email
 *
 * This function opens the reply-all form for the currently selected email, including all recipients.
 */
saltos.emails.replyall = () => {
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/emails/create/replyall/${id}`);
};

/**
 * Forward an email
 *
 * This function opens the forward form for the currently selected email.
 */
saltos.emails.forward = () => {
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/emails/create/forward/${id}`);
};
