/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Notes subtable control integration
 *
 * This file provides the frontend logic for the notes subtable control,
 * replacing the old notes system (hidden delnotes + readonly table + textarea addnotes)
 * with full CRUD via the subtable widget. Data is populated automatically by the driver
 * (which calls .set() on elements with that method), so notes_init only configures
 * the disabled state based on the current form mode.
 */

/**
 * Initialize the notes subtable control
 *
 * This method is called after saltos.driver.init() to configure the notes subtable
 * for the current form mode. In view mode, the toolbar and action buttons are disabled.
 *
 * @action => the current form mode (create, view, edit)
 */
saltos.common.notes_init = action => {
    const allnotes = document.getElementById('allnotes');
    if (!allnotes || typeof allnotes.set_disabled !== 'function') {
        return;
    }
    allnotes.set_disabled(action === 'view');
    allnotes.onsave = row => {
        return {
            ...row,
            user: row.user || '',
            datetime: row.datetime || '',
        };
    };
};

/**
 * Add a new note
 *
 * This function is called from the subtable toolbar button. It opens the modal form
 * defined in XML with empty data to create a new note.
 *
 * @id => the subtable id (default: 'allnotes')
 */
saltos.common.note_add = id => {
    const table = document.getElementById(id || 'allnotes');
    if (table && typeof table.open_modal === 'function') {
        table.open_modal({});
    }
};

/**
 * Edit an existing note
 *
 * This function is called from the subtable row edit action button. It retrieves
 * the note row data by id and opens the modal form with the current note text.
 *
 * @arg => callback argument in the format "table_id:row_id"
 */
saltos.common.note_edit = arg => {
    const parts = String(arg).split(':');
    const table_id = parts[0];
    const row_id = parts[1] || '';
    const table = document.getElementById(table_id);
    if (!table || typeof table.open_modal !== 'function') {
        return;
    }
    const row = table.get_row(row_id);
    if (row) {
        table.open_modal(row);
    }
};

/**
 * Delete a note
 *
 * This function is called from the subtable row delete action button. It shows a
 * confirmation modal dialog and deletes the note from the subtable if confirmed.
 *
 * @arg => callback argument in the format "table_id:row_id"
 */
saltos.common.note_delete = arg => {
    const parts = String(arg).split(':');
    const table_id = parts[0];
    const row_id = parts[1] || '';
    const table = document.getElementById(table_id);
    if (!table) {
        return;
    }
    saltos.app.modal(T('Delete'), T('Delete this note'), {
        color: 'warning',
        buttons: [
            {
                label: T('Delete'),
                color: 'danger',
                icon: 'trash',
                onclick: () => {
                    table.delete_row(row_id);
                },
            },
            {
                label: T('Cancel'),
                color: 'secondary',
                icon: 'x-lg',
                autofocus: true,
                onclick: () => {},
            },
        ],
    });
};
