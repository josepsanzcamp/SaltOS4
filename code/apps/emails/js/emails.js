
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
        const obj = document.getElementById('list');
        if (obj) {
            obj.querySelectorAll('button').forEach(item => {
                item.classList.remove('active');
                item.removeAttribute('aria-current');
            });
        }
    }

    // Load the folders tree each time that a filter is loaded, including the last
    // filter restored when the list is opened
    if (['list'].includes(arg)) {
        saltos.window.set_listener('saltos.filter.load', event => {
            if (document.getElementById('folders')) {
                saltos.emails.folders();
            }
        });
        // And each time that the synchronization of the IMAP accounts changes the folders
        saltos.window.set_listener('saltos.emails.folders', event => {
            if (document.getElementById('folders')) {
                saltos.emails.folders();
            }
        });
    }

    // Handle state changes for list and view
    if (['list', 'view'].includes(arg)) {
        const id = saltos.hash.get().split('/').at(-1);
        const button = document.getElementById(`button_${id}`);
        if (button) {
            button.classList.add('active');
            button.setAttribute('aria-current', 'true');
            button.classList.remove('fw-bold');
            button.querySelector('h5').classList.remove('fw-bold');
            const is_new = button.querySelector('span.text-bg-success');
            if (is_new && ['New', T('New')].includes(is_new.innerHTML.trim())) {
                is_new.classList.remove('text-bg-success');
                is_new.classList.add('bg-secondary-subtle', 'text-secondary-emphasis', 'fw-normal');
                is_new.innerHTML = T('Read');
            }
            button.blur();
        }
    }

    // Handle initialization for creating emails
    if (['create'].includes(arg)) {
        saltos.core.when_visible('from', () => {
            saltos.emails.old_account = document.getElementById('from').value;
        });
    }

    // Handle initialization for viewing emails
    if (['view'].includes(arg)) {
        // Remove unnecessary fields
        if (document.getElementById('cc').value === '') {
            document.getElementById('cc').closest('.col-12').remove();
        }
        if (document.getElementById('bcc').value === '') {
            document.getElementById('bcc').closest('.col-12').remove();
        }
        if (document.getElementById('state_error').value === '') {
            document.getElementById('state_error').closest('.col-12').remove();
        }

        // Remove unnecessary switches and buttons
        if (document.getElementById('is_outbox').value === '1') {
            document.getElementById('state_new').closest('.col-md-2').remove();
            document.getElementById('state_reply').closest('.col-md-2').remove();
            document.getElementById('reply').closest('.col-auto').remove();
            document.getElementById('new0').closest('li').remove();
            document.getElementById('new1').closest('li').remove();
        } else {
            document.getElementById('state_sent').closest('.col-md-2').remove();
            document.getElementById('forward').closest('.col-auto').remove();
            if (document.getElementById('state_new').value === '1') {
                document.getElementById('new1').closest('li').remove();
            } else {
                document.getElementById('new0').closest('li').remove();
            }
        }
        if (document.getElementById('state_wait').value === '1') {
            document.getElementById('wait1').closest('li').remove();
        } else {
            document.getElementById('wait0').closest('li').remove();
        }

        // Remove unnecessary table if no files exist
        if (document.getElementById('num_files').value === '0') {
            document.getElementById('files').closest('.col-12').remove();
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
                    if (id1 !== id2) {
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
    }
};

/**
 * Load folders
 *
 * This method loads the tree of folders of the accounts of the user, marking as selected
 * the node of the account and the folder of the search form
 */
saltos.emails.folders = () => {
    saltos.app.ajax({
        url: 'app/emails/list/folders',
        success: response => {
            const account_id = document.getElementById('account_id').value;
            const folder_id = document.getElementById('folder_id').value;
            saltos.emails.__folders_select(response.data, `${account_id}/${folder_id}`);
            saltos.emails.__folders_data = response.data;
            document.getElementById('folders').saltos.set_value(response.data);
        },
    });
};

/**
 * Folders select helper
 *
 * This method marks as selected the node of the tree that matches the id, and opens it and
 * its parents to show it and its children when it is inside a closed node or when it is a
 * closed node, like the disabled accounts
 *
 * @nodes => the nodes of the tree
 * @id    => the id of the node to select
 *
 * Returns true if the node is found
 */
saltos.emails.__folders_select = (nodes, id) => {
    let found = false;
    for (const node of nodes) {
        if (node.id === id) {
            node.state.selected = true;
            node.state.opened = true;
            found = true;
        }
        if (saltos.emails.__folders_select(node.children, id)) {
            node.state.opened = true;
            found = true;
        }
    }
    return found;
};

/**
 * Select folder
 *
 * This method is executed when a node of the tree is selected, the value contains the
 * account and the folder separated by a slash (folder 0 for all folders of the account),
 * sets both in the search form and searchs the emails
 */
saltos.emails.folder = value => {
    const [account_id, folder_id] = String(value).split('/');
    document.getElementById('account_id').saltos.set_value(account_id);
    document.getElementById('folder_id').value = folder_id;
    saltos.driver.search();
};

/**
 * All emails
 *
 * This method shows the emails of all the accounts together in the same list (except the
 * trash and the junk), keeping the other filters of the search form, and reloads the
 * folders tree to remove the selection of the previous folder
 */
saltos.emails.folder_all = () => {
    document.getElementById('account_id').saltos.set_value('');
    document.getElementById('folder_id').value = '0';
    saltos.driver.search();
    saltos.emails.folders();
};

/**
 * Reset filter
 *
 * This method resets the search filter and reloads the folders tree to remove the
 * selection of the previous folder
 */
saltos.emails.reset = () => {
    saltos.driver.reset();
    saltos.emails.folders();
};

/**
 * Folders data
 *
 * This property stores the last data loaded in the folders tree
 */
saltos.emails.__folders_data = [];

/**
 * Selected folder helper
 *
 * This method returns the account and the folder selected in the tree, and the nodes of
 * both, or shows a modal and returns null if no account is selected
 */
saltos.emails.__folder_selected = () => {
    const account_id = document.getElementById('account_id').value;
    const folder_id = document.getElementById('folder_id').value;
    const account = saltos.emails.__folders_data.find(node => node.id === `${account_id}/0`);
    if (!account) {
        saltos.app.modal(
            'Select folder',
            'You must select a folder or an account in the tree',
            {
                color: 'danger',
            },
        );
        return null;
    }
    const find = nodes => {
        for (const node of nodes) {
            if (node.id === `${account_id}/${folder_id}`) {
                return node;
            }
            const temp = find(node.children);
            if (temp) {
                return temp;
            }
        }
        return null;
    };
    return {
        account_id: account_id,
        folder_id: folder_id,
        account: account,
        folder: find(account.children),
    };
};

/**
 * Folder action helper
 *
 * This method sends an action of the folders management to the server, shows the response,
 * reloads the folders tree and updates the list of emails
 *
 * @data => the data of the action
 */
saltos.emails.__folder_action = data => {
    saltos.app.ajax({
        url: 'app/emails/action/folder',
        proxy: 'network',
        data: data,
        success: response => {
            saltos.app.toast('Response', response.text);
            saltos.emails.folders();
            saltos.window.send('saltos.emails.update');
        },
    });
};

/**
 * Folders panel
 *
 * This method shows and hides the folders panel, an offcanvas with the folders tree and its
 * options, using the same mechanism of the search form (saltos.common.filter): the
 * contents of the folders_panel div are moved to the offcanvas while it is open
 */
saltos.emails.folders_panel = () => {
    if (saltos.bootstrap.offcanvas('isopen')) {
        saltos.bootstrap.offcanvas('close');
        return;
    }
    saltos.gettext.bootstrap.offcanvas({
        pos: 'left',
        close: 'Close',
    });
    const panel = document.getElementById('folders_panel');
    if ('data-bs-title' in panel) {
        document.querySelector('.offcanvas-title').innerHTML = T(panel['data-bs-title']);
    }
    const items = Array.prototype.slice.call(panel.childNodes);
    const parents = [];
    for (const i in items) {
        parents[i] = items[i].parentElement;
        document.querySelector('.offcanvas-body').append(items[i]);
    }
    const obj = saltos.bootstrap.__offcanvas.obj;
    obj.addEventListener('hide.bs.offcanvas', event => {
        for (const i in items) {
            parents[i].append(items[i]);
        }
    });
};

/**
 * Folder modal helper
 *
 * This method programs the close of the modal of the folders management when the search
 * form, that contains the folders tree, is closed
 */
saltos.emails.__folder_modal = () => {
    if (!saltos.bootstrap.offcanvas('isopen')) {
        return;
    }
    saltos.bootstrap.__offcanvas.obj.addEventListener('hide.bs.offcanvas', event => {
        if (saltos.bootstrap.modal('isopen')) {
            saltos.bootstrap.modal('close');
        }
    }, {once: true});
};

/**
 * Create folder
 *
 * This method shows a modal to create a new folder in the account selected in the tree,
 * using the selected folder as default parent
 */
saltos.emails.folder_create = () => {
    const selected = saltos.emails.__folder_selected();
    if (!selected) {
        return;
    }
    const rows = [{
        label: selected.account.text,
        value: '0',
    }];
    saltos.emails.__move_rows(selected.account.children, rows, '');
    const body = saltos.core.html('<div></div>');
    const parent = saltos.gettext.bootstrap.field({
        type: 'select',
        id: 'folder_parent_id',
        label: 'Parent folder',
        rows: rows,
        value: selected.folder_id,
    });
    const label = saltos.gettext.bootstrap.field({
        type: 'text',
        id: 'folder_label',
        label: 'Name',
    });
    label.classList.add('mt-3');
    body.append(parent, label);
    saltos.app.modal('New folder', body, {
        buttons: [{
            label: 'Create',
            color: 'success',
            icon: 'check-lg',
            onclick: () => {
                saltos.emails.__folder_action({
                    action: 'create',
                    account_id: selected.account_id,
                    parent_id: parent.querySelector('select').value,
                    label: label.querySelector('input').value,
                });
            },
        },{
            label: 'Cancel',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
    });
    saltos.emails.__folder_modal();
};

/**
 * Server folders
 *
 * This method shows a modal with the folders of the server of the IMAP account selected in
 * the tree, to subscribe and unsubscribe them: the emails of the subscribed folders are
 * synchronized, and the emails of the unsubscribed folders are deleted in SaltOS (they stay
 * in the server); the special folders can not be changed
 */
saltos.emails.folder_server = () => {
    const selected = saltos.emails.__folder_selected();
    if (!selected) {
        return;
    }
    saltos.app.ajax({
        url: 'app/emails/action/server_folders',
        proxy: 'network',
        data: {
            account_id: selected.account_id,
        },
        success: response => {
            if (response.status !== 'ok') {
                saltos.app.modal('Server folders', response.text, {color: 'danger'});
                return;
            }
            const body = saltos.core.html('<div></div>');
            const help = saltos.core.html('<p></p>');
            help.textContent = T('The unsubscribed folders are removed from SaltOS with its ' +
                'emails, they stay in the server');
            body.append(help);
            const fields = [];
            for (const folder of response.folders) {
                const field = saltos.gettext.bootstrap.field({
                    type: 'switch',
                    id: `server_folder_${folder.id}`,
                    label: folder.name,
                    value: folder.subscribed,
                    disabled: folder.locked,
                });
                fields.push([folder.id, field]);
                body.append(field);
            }
            saltos.app.modal('Server folders', body, {
                buttons: [{
                    label: 'Save',
                    color: 'success',
                    icon: 'check-lg',
                    onclick: () => {
                        const ids = fields.filter(([id, field]) => {
                            return field.querySelector('input').checked;
                        }).map(([id, field]) => id);
                        saltos.emails.__folder_action({
                            action: 'subscribe',
                            account_id: selected.account_id,
                            ids: ids.join(','),
                        });
                    },
                },{
                    label: 'Cancel',
                    color: 'danger',
                    icon: 'x-lg',
                    onclick: () => {},
                }],
            });
            saltos.emails.__folder_modal();
        },
    });
};

/**
 * Rename folder
 *
 * This method shows a modal to rename the folder selected in the tree
 */
saltos.emails.folder_rename = () => {
    const selected = saltos.emails.__folder_selected();
    if (!selected) {
        return;
    }
    if (!selected.folder) {
        saltos.app.modal(
            'Select folder',
            'You must select a folder in the tree',
            {
                color: 'danger',
            },
        );
        return;
    }
    const label = saltos.gettext.bootstrap.field({
        type: 'text',
        id: 'folder_label',
        label: 'Name',
        value: selected.folder.text,
    });
    saltos.app.modal('Rename folder', label, {
        buttons: [{
            label: 'Rename',
            color: 'success',
            icon: 'check-lg',
            onclick: () => {
                saltos.emails.__folder_action({
                    action: 'rename',
                    folder_id: selected.folder_id,
                    label: label.querySelector('input').value,
                });
            },
        },{
            label: 'Cancel',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
    });
    saltos.emails.__folder_modal();
};

/**
 * Move folder
 *
 * This method shows a modal to move the folder selected in the tree inside another folder
 * of the same account
 */
saltos.emails.folder_move = () => {
    const selected = saltos.emails.__folder_selected();
    if (!selected) {
        return;
    }
    if (!selected.folder) {
        saltos.app.modal(
            'Select folder',
            'You must select a folder in the tree',
            {
                color: 'danger',
            },
        );
        return;
    }
    const rows = [{
        label: selected.account.text,
        value: '0',
    }];
    saltos.emails.__move_rows(selected.account.children, rows, '', selected.folder.id);
    const parent = saltos.gettext.bootstrap.field({
        type: 'select',
        id: 'folder_parent_id',
        label: 'Parent folder',
        rows: rows,
    });
    saltos.app.modal('Move folder', parent, {
        buttons: [{
            label: 'Move',
            color: 'success',
            icon: 'check-lg',
            onclick: () => {
                saltos.emails.__folder_action({
                    action: 'move',
                    folder_id: selected.folder_id,
                    parent_id: parent.querySelector('select').value,
                });
            },
        },{
            label: 'Cancel',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
    });
    saltos.emails.__folder_modal();
};

/**
 * Delete folder
 *
 * This method shows a modal with the summary of the folders and emails affected and
 * deletes the folder selected in the tree with its subfolders, the emails are moved
 * to the trash, and the emails of the account are shown after the delete
 */
saltos.emails.folder_delete = () => {
    const selected = saltos.emails.__folder_selected();
    if (!selected) {
        return;
    }
    if (!selected.folder) {
        saltos.app.modal(
            'Select folder',
            'You must select a folder in the tree',
            {
                color: 'danger',
            },
        );
        return;
    }
    saltos.app.ajax({
        url: 'app/emails/action/folder_summary',
        data: {
            folder_id: selected.folder_id,
        },
        success: response => {
            if (response.status !== 'ok') {
                saltos.app.modal('Delete folder', response.text, {color: 'danger'});
                return;
            }
            saltos.app.modal('Delete folder???', response.text, {
                buttons: [{
                    label: 'Yes',
                    color: 'success',
                    icon: 'check-lg',
                    onclick: () => {
                        document.getElementById('folder_id').value = '0';
                        saltos.emails.__folder_action({
                            action: 'delete',
                            folder_id: selected.folder_id,
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
            saltos.emails.__folder_modal();
        },
    });
};

/**
 * Empty folder
 *
 * This method shows a modal with the number of emails affected and deletes permanently
 * the emails of the trash or the junk folder of the account selected in the tree
 *
 * @folder => the folder to empty, trash or junk
 */
saltos.emails.folder_empty = folder => {
    const selected = saltos.emails.__folder_selected();
    if (!selected) {
        return;
    }
    saltos.app.ajax({
        url: 'app/emails/action/folder_empty_summary',
        data: {
            account_id: selected.account_id,
            folder: folder,
        },
        success: response => {
            if (response.status !== 'ok') {
                const title = folder === 'trash' ? 'Empty trash' : 'Empty junk';
                saltos.app.modal(title, response.text, {color: 'danger'});
                return;
            }
            const title = folder === 'trash' ? 'Empty trash???' : 'Empty junk???';
            saltos.app.modal(title, response.text, {
                buttons: [{
                    label: 'Yes',
                    color: 'success',
                    icon: 'check-lg',
                    onclick: () => {
                        saltos.emails.__folder_action({
                            action: 'empty',
                            account_id: selected.account_id,
                            folder: folder,
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
            saltos.emails.__folder_modal();
        },
    });
};

/**
 * Move selected emails
 *
 * This method moves the emails selected in the list to another folder
 */
saltos.emails.move1 = () => {
    const ids = saltos.app.checkbox_ids(document.getElementById('list'));
    if (!ids.length) {
        saltos.app.modal(
            'Select emails',
            'You must select the desired emails to be moved',
            {
                color: 'danger',
            },
        );
        return;
    }
    saltos.emails.__move(ids.join(','));
};

/**
 * Move this email
 *
 * This method moves the email opened in the view to another folder
 */
saltos.emails.move2 = () => {
    const id = saltos.hash.get().split('/').at(3);
    saltos.emails.__move(id);
};

/**
 * Move helper
 *
 * This method shows a modal with the folders of the account of the emails, and moves the
 * emails to the selected folder, all emails must be of the same account
 *
 * @ids => the ids of the emails separated by commas
 */
saltos.emails.__move = ids => {
    saltos.app.ajax({
        url: 'app/emails/list/folders',
        data: {
            ids: ids,
        },
        success: response => {
            if (response.data.length !== 1) {
                saltos.app.modal(
                    'Select emails',
                    'You must select emails of only one account to be moved',
                    {
                        color: 'danger',
                    },
                );
                return;
            }
            const rows = [];
            saltos.emails.__move_rows(response.data[0].children, rows, '');
            const field = saltos.gettext.bootstrap.field({
                type: 'select',
                id: 'move_folder_id',
                label: 'Folder',
                rows: rows,
            });
            saltos.app.modal('Move emails', field, {
                buttons: [{
                    label: 'Move',
                    color: 'success',
                    icon: 'check-lg',
                    autofocus: true,
                    onclick: () => {
                        saltos.app.ajax({
                            url: 'app/emails/action/move',
                            proxy: 'network',
                            data: {
                                ids: ids,
                                folder_id: field.querySelector('select').value,
                            },
                            success: response => {
                                saltos.app.toast('Response', response.text);
                                saltos.window.send('saltos.emails.update');
                            },
                        });
                    },
                },{
                    label: 'Cancel',
                    color: 'danger',
                    icon: 'x-lg',
                    onclick: () => {},
                }],
            });
        },
    });
};

/**
 * Move rows helper
 *
 * This method converts the nodes of the folders of an account in the rows of a select,
 * using the path of each folder as label
 *
 * @nodes  => the nodes of the tree
 * @rows   => the array where the rows are added
 * @prefix => the path of the parent node
 * @skip   => the id of the node excluded with its children (optional)
 */
saltos.emails.__move_rows = (nodes, rows, prefix, skip = '') => {
    for (const node of nodes) {
        if (node.id === skip) {
            continue;
        }
        const label = prefix + node.text;
        rows.push({
            label: label,
            value: node.id.split('/').at(1),
        });
        saltos.emails.__move_rows(node.children, rows, label + ' / ', skip);
    }
};

/**
 * Perform server-related actions for emails
 *
 * This method triggers server-side actions for emails and displays the responses in toasts,
 * then updates the list and the folders tree (the synchronization can change the folders).
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
            saltos.window.send('saltos.emails.folders');
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
            if (response.status === 'ok') {
                saltos.app.toast('Response', response.text);
                saltos.window.send('saltos.emails.update');
                saltos.autosave.clear('two,one');
                saltos.driver.close();
                return;
            }
            if (response.status === 'ko') {
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

/**
 * Driver search
 *
 * This function implement the search feature associated to tables and lists
 * using the filters fields
 *
 * Additionally, translates the `body_text` field of each result
 * before rendering the list.
 *
 * @arg => unused at this scope
 */
saltos.driver.search = arg => {
    document.getElementById('page').value = '0';
    saltos.backup.restore('top+one');
    const data = saltos.app.get_data(true);
    saltos.filter.update('last', data);
    const app = saltos.hash.get().split('/').at(1);
    // Restore the more button
    const obj = document.getElementById('more');
    if (obj && 'saltos' in obj && typeof obj.saltos.set_disabled === 'function') {
        obj.saltos.set_disabled(false);
    }
    // Continue
    saltos.app.ajax({
        url: `app/${app}/list/data`,
        data: data,
        success: response => {
            for (const i in response.data) {
                if (response.data[i].body_text !== '') {
                    response.data[i].body_text = T(response.data[i].body_text);
                }
            }
            document.getElementById('list').saltos.set_value(response);
            document.getElementById('one').scrollTop = 0;
        },
    });
};
