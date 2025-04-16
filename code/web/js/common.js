
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
    document.querySelector('.modal-body').setAttribute('id', 'four');
    const app = saltos.hash.get().split('/').at(1);
    saltos.app.send_request(`app/widgets/help/${app}`);
};

/**
 * Logout feature
 *
 * This function execute the deauthtoken action and jump to the login screen
 */
saltos.common.logout = async () => {
    await saltos.authenticate.deauthtoken();
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
        const app = saltos.hash.get().split('/').at(1);
        const id = saltos.hash.get().split('/').at(3);
        file = `app/${app}/view/download/${id}`;
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
    const row = document.getElementById('all' + file.split('/').slice(3, 6).join('/'));
    row.remove();
    const obj = document.getElementById('del' + file.split('/').at(3));
    let value = obj.value.split(',');
    value.push(file.split('/').at(-1));
    obj.value = value.filter(arg => arg != '').join(',');
};

/**
 * Version common helper
 *
 * This function open the version widget
 */
saltos.common.version = () => {
    const app = saltos.hash.get().split('/').at(1);
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/${app}/view/version/${id}`);
};

/**
 * Log common helper
 *
 * This function open the log widget
 */
saltos.common.log = () => {
    const app = saltos.hash.get().split('/').at(1);
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/${app}/view/log/${id}`);
};

/**
 * Edit common helper
 *
 * This function open the edit screen
 */
saltos.common.edit = () => {
    const app = saltos.hash.get().split('/').at(1);
    const id = saltos.hash.get().split('/').at(3);
    saltos.driver.open(`app/${app}/edit/${id}`);
};

/**
 * Create common helper
 *
 * This function open the create screen
 */
saltos.common.create = () => {
    const app = saltos.hash.get().split('/').at(1);
    saltos.driver.open(`app/${app}/create`);
};

/**
 * Viewpdf common helper
 *
 * This function open the viewpdf widget
 */
saltos.common.viewpdf = file => {
    if (file === undefined) {
        const app = saltos.hash.get().split('/').at(1);
        const id = saltos.hash.get().split('/').at(3);
        file = `app/${app}/view/viewpdf/${id}`;
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
            const row = obj.closest('.mb-3');
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
    const app = saltos.hash.get().split('/').at(1);
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
    saltos.common.viewpdf(`app/${app}/view/viewpdf/${ids}`);
};

/**
 * Download selected registers as PDF
 *
 * This method downloads the selected registers in PDF format.
 * If no registers are selected, it prompts the user to select registers first.
 */
saltos.common.download_checkbox = () => {
    const app = saltos.hash.get().split('/').at(1);
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
    saltos.common.download(`app/${app}/view/download/${ids}`);
};
