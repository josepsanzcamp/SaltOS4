
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
 * Common helper module
 *
 * This fie contains useful functions related to the common applications
 */

/**
 * Common helper object
 *
 * This object stores all application functions and data
 */
saltos.common = {};

/**
 * Panels object
 *
 * This object stores the functions used to open the panels that can be restored using the
 * panel param of the hash, the apps can add their own panels
 */
saltos.common.__panels = {
    about: () => saltos.common.about(),
    help: () => saltos.common.help(),
    profile: () => saltos.common.profile(),
    filter: () => saltos.common.filter(),
};

/**
 * Panel open helper
 *
 * This function stores the name of the open panel in the panel param of the hash and
 * removes it when the panel is closed, it must be called after opening the panel
 *
 * @name => the name of the panel
 * @type => the type of the panel (modal or offcanvas)
 */
saltos.common.__panel_open = (name, type) => {
    saltos.hash.add(null, {
        ...saltos.hash.get(true).params,
        panel: name,
    });
    saltos.common.__panel_status = {name, type};
    // Cleanup when the panel is closed: removes the status and the panel param of the hash
    saltos.bootstrap[`__${type}`].obj.addEventListener(`hidden.bs.${type}`, event => {
        delete saltos.common.__panel_status;
        const params = saltos.hash.get(true).params;
        if (params.panel === name) {
            delete params.panel;
            saltos.hash.add(null, params);
        }
    });
};

/**
 * Panel restore helper
 *
 * This function opens the panel of the panel param of the hash, closing the current panel
 * if it is different, it is used when the screen is loaded and when the history changes
 * the params but not the hash
 *
 * @name => the name of the panel that must be open
 */
saltos.common.__panel_restore = name => {
    const status = saltos.common.__panel_status;
    if (status && status.name === name) {
        return;
    }
    if (status) {
        // Chain when the panel is closed: opens the new panel after the cleanup of the old one
        saltos.bootstrap[`__${status.type}`].obj.addEventListener(`hidden.bs.${status.type}`, event => {
            saltos.common.__panel_restore(name);
        }, {once: true});
        saltos.bootstrap[status.type]('close');
        return;
    }
    if (!(name in saltos.common.__panels)) {
        return;
    }
    if (saltos.bootstrap.modal('isopen') || saltos.bootstrap.offcanvas('isopen')) {
        return;
    }
    saltos.common.__panels[name]();
};

/**
 * Profile screen
 *
 * This function allow to open the profile screen in a offcanvas widget
 */
saltos.common.profile = () => {
    if (saltos.bootstrap.offcanvas('isopen')) {
        saltos.bootstrap.offcanvas('close');
        return;
    }
    saltos.gettext.bootstrap.offcanvas({
        pos: 'right',
        close: 'Close',
    });
    saltos.common.__panel_open('profile', 'offcanvas');
    document.querySelector('.offcanvas-body').setAttribute('id', 'right');
    saltos.app.send_request('app/widgets/profile');
};

/**
 * Help screen
 *
 * This function allow to open the help screen in a modal widget
 */
saltos.common.help = () => {
    if (saltos.bootstrap.modal('isopen')) {
        saltos.bootstrap.modal('close');
        return;
    }
    saltos.gettext.bootstrap.modal({
        close: 'Close',
        class: 'modal-xl',
    });
    saltos.common.__panel_open('help', 'modal');
    document.querySelector('.modal-body').setAttribute('id', 'four');
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    saltos.app.send_request(saltos.hash.encode.private({
        app: 'widgets',
        action: 'help',
        id: app,
    }));
};

/**
 * About screen
 *
 * This function allow to open the about screen in a modal widget
 */
saltos.common.about = () => {
    if (saltos.bootstrap.modal('isopen')) {
        saltos.bootstrap.modal('close');
        return;
    }
    saltos.gettext.bootstrap.modal({
        close: 'Close',
        class: 'modal-xl',
    });
    saltos.common.__panel_open('about', 'modal');
    saltos.core.ajax({
        url: 'api/?/about',
        success: response => {
            if (!saltos.bootstrap.modal('isopen')) {
                return;
            }
            const title = document.querySelector('.modal-title');
            title.innerHTML = [
                T('About'),
                response.about,
            ].join(' - ');
            const body = document.querySelector('.modal-body');
            body.append(saltos.gettext.bootstrap.field({
                id: 'header',
                type: 'codemirror',
                value: response.header.join('\n'),
            }));
            body.append(saltos.gettext.bootstrap.field({
                type: 'div',
                class: 'mb-3',
            }));
            const header = {
                name: {
                    label: 'Name',
                    width: '25%',
                },
                version: {
                    label: 'Version',
                    width: '15%',
                },
                license_id: {
                    label: 'License',
                    width: '15%',
                },
                homepage: {
                    label: 'Homepage',
                    type: 'link',
                    width: '45%',
                },
            };
            body.append(saltos.gettext.bootstrap.field({
                id: 'web-libs',
                type: 'table',
                label: 'WEB libraries',
                header: header,
                data: Object.values(response.libraries.web),
            }));
            body.append(saltos.gettext.bootstrap.field({
                type: 'div',
                class: 'mb-3',
            }));
            body.append(saltos.gettext.bootstrap.field({
                id: 'api-libs',
                type: 'table',
                label: 'API libraries',
                header: header,
                data: Object.values(response.libraries.api),
            }));
            body.append(saltos.gettext.bootstrap.field({
                type: 'div',
                class: 'mb-3',
            }));
            body.append(saltos.gettext.bootstrap.field({
                id: 'apps-libs',
                type: 'table',
                label: 'APPS libraries',
                header: header,
                data: Object.values(response.libraries.apps),
            }));
            body.append(saltos.gettext.bootstrap.field({
                type: 'div',
                class: 'mb-3',
            }));
            body.append(saltos.gettext.bootstrap.field({
                id: 'legal',
                type: 'codemirror',
                label: 'Legal',
                value: response.legal.join('\n'),
            }));
        },
    });
};

/**
 * Logout feature
 *
 * This function execute the deauthtoken action and jump to the login screen
 *
 * Notes:
 *
 * Before the logout, the proxy tries to sync the queued requests, if some of
 * them remains in the queue (for example, because there is no connection), the
 * user is warned that they will be lost and the logout can be cancelled, after
 * the logout, the queued requests of the user are removed (the cache is kept)
 */
saltos.common.logout = async () => {
    const user_id = saltos.token.get_user_id();
    await saltos.core.sync();
    const pending = await saltos.core.proxy({action: 'pending', user_id: user_id});
    if (pending) {
        const accept = await new Promise(resolve => {
            const modal = saltos.app.modal(
                'Logout',
                'There are pending changes that will be lost, do you want to continue?',
                {
                    buttons: [{
                        label: 'Yes',
                        color: 'success',
                        icon: 'check-lg',
                        autofocus: true,
                        onclick: () => resolve(true),
                    },{
                        label: 'No',
                        color: 'danger',
                        icon: 'x-lg',
                        onclick: () => resolve(false),
                    }],
                    color: 'danger',
                }
            );
            if (!modal) {
                resolve(false);
            }
        });
        if (!accept) {
            return;
        }
    }
    await saltos.authenticate.deauthtoken();
    await saltos.core.proxy({action: 'purge', user_id: user_id});
    saltos.window.send('saltos.app.logout');
};

/**
 * Filter screen
 *
 * This function allow to open the filter screen in a offcanvas widget
 */
saltos.common.filter = () => {
    if (saltos.bootstrap.offcanvas('isopen')) {
        saltos.bootstrap.offcanvas('close');
        return;
    }
    saltos.gettext.bootstrap.offcanvas({
        pos: 'left',
        close: 'Close',
    });
    saltos.common.__panel_open('filter', 'offcanvas');
    // The search_extra field is ignored by get_data, so it must be synced with the search field
    const search = document.getElementById('search');
    const search_extra = document.getElementById('search_extra');
    if (search && search_extra) {
        search_extra.value = search.value;
    }
    const filter = document.getElementById('filter');
    if ('data-bs-title' in filter) {
        document.querySelector('.offcanvas-title').innerHTML = T(filter['data-bs-title']);
    }
    const items = Array.prototype.slice.call(filter.childNodes);
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
 * Download helper
 *
 * This function allow to download files, to do it, make the ajax request and
 * using the base64 data response, sets the href of an anchor created dinamically
 * to emulate the download action
 *
 * @file => the file data used to identify the desired file in the backend part
 */
saltos.common.download = file => {
    if (file === undefined) {
        const app = saltos.hash.decode.public(saltos.hash.get()).app;
        const id = saltos.hash.decode.public(saltos.hash.get()).id;
        file = saltos.hash.encode.private({
            app,
            action: 'view',
            subaction: 'download',
            id,
        });
    }
    saltos.app.ajax({
        url: file,
        success: response => {
            const a = document.createElement('a');
            a.download = response.name;
            response.type = 'application/force-download'; // to force download dialog
            a.href = `data:${response.type};base64,${response.data}`;
            a.click();
        },
    });
};

/**
 * Delete helper
 *
 * This function allow to remove the files and notes in the files and notes widgets
 *
 * @file => the file or note string path
 */
saltos.common.delete = file => {
    const route = saltos.hash.decode.private(file);
    const all_id = `all${route.subaction}_${route.id}_${route.sub_id}`;
    const row = document.getElementById(all_id);
    row.remove();
    const del_id = `del${route.subaction}`;
    const obj = document.getElementById(del_id);
    let value = obj.value.split(',');
    value.push(route.sub_id);
    obj.value = value.filter(arg => arg !== '').join(',');
};

/**
 * Version common helper
 *
 * This function open the version widget
 */
saltos.common.version = () => {
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    const id = saltos.hash.decode.public(saltos.hash.get()).id;
    saltos.driver.open(saltos.hash.encode.public({
        app,
        action: 'view',
        subaction: 'version',
        id,
    }));
};

/**
 * Log common helper
 *
 * This function open the log widget
 */
saltos.common.log = () => {
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    const id = saltos.hash.decode.public(saltos.hash.get()).id;
    saltos.driver.open(saltos.hash.encode.public({
        app,
        action: 'view',
        subaction: 'log',
        id,
    }));
};

/**
 * Edit common helper
 *
 * This function open the edit screen
 */
saltos.common.edit = () => {
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    const id = saltos.hash.decode.public(saltos.hash.get()).id;
    saltos.driver.open(saltos.hash.encode.public({
        app,
        action: 'edit',
        id,
    }));
};

/**
 * Create common helper
 *
 * This function open the create screen
 */
saltos.common.create = () => {
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    saltos.driver.open(saltos.hash.encode.public({app, action: 'create'}));
};

/**
 * Viewpdf common helper
 *
 * This function open the viewpdf widget
 */
saltos.common.viewpdf = file => {
    if (file === undefined) {
        const app = saltos.hash.decode.public(saltos.hash.get()).app;
        const id = saltos.hash.decode.public(saltos.hash.get()).id;
        file = saltos.hash.encode.public({
            app,
            action: 'view',
            subaction: 'viewpdf',
            id,
        });
    }
    saltos.driver.open(file);
};

/**
 * Remove common helper
 *
 * This function works as helper for the allfiles and allnotes functions and
 * tries to detect if the table identified by the argument contains something
 *
 * @id => the id of the table
 */
saltos.common.__remove_helper = id => {
    const obj = document.getElementById(id);
    if (obj) {
        const len = obj.querySelector('tbody').innerText.trim().length;
        if (!len) {
            const row = obj.closest('.col-12');
            if (row) {
                row.remove();
            }
        }
    }
};

/**
 * Allfiles common helper
 *
 * This function remove the allfiles table if it is void
 */
saltos.common.allfiles = () => {
    saltos.common.__remove_helper('allfiles');
};

/**
 * Allnotes common helper
 *
 * This function remove the allnotes table if it is void
 */
saltos.common.allnotes = () => {
    saltos.common.__remove_helper('allnotes');
};

/**
 * View selected registers in PDF format
 *
 * This method opens a PDF viewer to display the selected registers.
 * If no registers are selected, it prompts the user to select registers first.
 */
saltos.common.viewpdf_checkbox = () => {
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    let ids = saltos.app.checkbox_ids(document.getElementById('list'));
    if (!ids.length) {
        saltos.app.modal(
            'Select registers',
            'You must select the desired registers that you want see in the PDF file',
            {
                color: 'danger',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.common.viewpdf(saltos.hash.encode.public({
        app,
        action: 'view',
        subaction: 'viewpdf',
        id: ids,
    }));
};

/**
 * Download selected registers as PDF
 *
 * This method downloads the selected registers in PDF format.
 * If no registers are selected, it prompts the user to select registers first.
 */
saltos.common.download_checkbox = () => {
    const app = saltos.hash.decode.public(saltos.hash.get()).app;
    let ids = saltos.app.checkbox_ids(document.getElementById('list'));
    if (!ids.length) {
        saltos.app.modal(
            'Select registers',
            'You must select the desired registers that you want download in the PDF file',
            {
                color: 'danger',
            },
        );
        return;
    }
    ids = ids.join(',');
    saltos.common.download(saltos.hash.encode.private({
        app,
        action: 'view',
        subaction: 'download',
        id: ids,
    }));
};
