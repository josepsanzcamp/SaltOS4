
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
 * Backup & Autosave helper module
 *
 * This module provides the needed tools to implement the backup & autosave feature
 */

/**
 * Form backup helper
 *
 * This object stores the needed structure to allocate the forms backups with all their
 * data and the functions needed to do and restore the backups to the main __form object.
 *
 * @do      => this action performs a backup using the specified key
 * @restore => this action performs the restoration action using the specified key, if the
 *             key is not found, then empty fields and templates are used for the restoration.
 *
 * Notes:
 *
 * This object is intended to store more forms that one, usefull when driver uses the same
 * screen to allocate lists and forms that contains fields for the search engine or fields
 * for the create or edit features.
 *
 * The restore action is able to understand some expressions like comma and plus:
 * @ two,one => this example is intended to restore the two context if it is found, otherwise
 *   tries to restore the one context, otherwise a void context is set.
 * @ top+one => this example is intended to restore two contexts in one context, intender to
 *   load the context of the search list that can be contained in the top and one containers.
 */
saltos.backup = {};

/**
 * TODO
 *
 * TODO
 */
saltos.backup.__forms = {};

/**
 * TODO
 *
 * TODO
 */
saltos.backup.save = key => {
    saltos.backup.__forms[key] = {};
    saltos.backup.__forms[key].fields = saltos.form.__form.fields;
    saltos.backup.__forms[key].templates = saltos.form.__form.templates;
};

/**
 * TODO
 *
 * TODO
 */
saltos.backup.restore = key => {
    saltos.form.__form.fields = [];
    saltos.form.__form.templates = {};
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        saltos.form.__form.fields = [
            ...saltos.form.__form.fields,
            ...saltos.backup.__forms[key[i]].fields
        ];
        saltos.form.__form.templates = {
            ...saltos.form.__form.templates,
            ...saltos.backup.__forms[key[i]].templates
        };
    }
    return key.length > 0;
};

/**
 * TODO
 *
 * TODO
 */
saltos.backup.__selector_helper = key => {
    if (key.includes('+')) {
        key = key.split('+');
        let result = [];
        for (const i in key) {
            if (saltos.backup.__forms.hasOwnProperty(key[i])) {
                result.push(key[i]);
            }
        }
        return result;
    }
    key = key.split(',');
    for (const i in key) {
        if (saltos.backup.__forms.hasOwnProperty(key[i])) {
            return [key[i]];
        }
    }
    return [];
};

/**
 * TODO
 *
 * TODO
 */
saltos.autosave = {};

/**
 * TODO
 *
 * TODO
 */
saltos.autosave.init = (key, hash = saltos.hash.get()) => {
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        if (saltos.storage.getItem(`saltos.autosave/${hash}/${key[i]}`)) {
            continue;
        }
        saltos.storage.setItemWithTimestamp(`saltos.autosave/${hash}/${key[i]}`, {});
    }
    return key.length > 0;
};

/**
 * TODO
 *
 * TODO
 */
saltos.autosave.save = (key, hash = saltos.hash.get()) => {
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        if (!saltos.storage.getItem(`saltos.autosave/${hash}/${key[i]}`)) {
            continue;
        }
        saltos.backup.restore(key[i]);
        const data = saltos.app.get_data();
        if (!Object.keys(data).length) {
            continue;
        }
        saltos.storage.setItemWithTimestamp(`saltos.autosave/${hash}/${key[i]}`, data);
    }
    return key.length > 0;
};

/**
 * TODO
 *
 * TODO
 */
saltos.autosave.restore = (key, hash = saltos.hash.get()) => {
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        if (!saltos.storage.getItem(`saltos.autosave/${hash}/${key[i]}`)) {
            continue;
        }
        const data = saltos.storage.getItemWithTimestamp(`saltos.autosave/${hash}/${key[i]}`);
        if (!Object.keys(data).length) {
            continue;
        }
        saltos.backup.restore(key[i]);
        saltos.form.data(data, false);
    }
    return key.length > 0;
};

/**
 * TODO
 *
 * TODO
 */
saltos.autosave.clear = (key, hash = saltos.hash.get()) => {
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        saltos.storage.removeItem(`saltos.autosave/${hash}/${key[i]}`);
    }
    return key.length > 0;
};

/**
 * TODO
 *
 * TODO
 */
saltos.autosave.purge = (key, hash = saltos.hash.get()) => {
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        if (!saltos.storage.getItem(`saltos.autosave/${hash}/${key[i]}`)) {
            continue;
        }
        const data = saltos.storage.getItemWithTimestamp(`saltos.autosave/${hash}/${key[i]}`);
        if (Object.keys(data).length) {
            continue;
        }
        saltos.storage.removeItem(`saltos.autosave/${hash}/${key[i]}`);
    }
    saltos.storage.purgeWithTimestamp('saltos.autosave', -86400);
    return key.length > 0;
};
