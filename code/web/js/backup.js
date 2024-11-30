
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
 * Notes:
 *
 * This object is intended to store more forms that one, usefull when driver uses the same
 * screen to allocate lists and forms that contains fields for the search engine or fields
 * for the create or edit features.
 */
saltos.backup = {};

/**
 * Backup object
 *
 * This object stores the backup data
 */
saltos.backup.__forms = {};

/**
 * Save feature
 *
 * This action performs a backup using the specified key
 *
 * @key => the key selector that you want to use.
 */
saltos.backup.save = key => {
    saltos.backup.__forms[key] = {};
    saltos.backup.__forms[key].fields = saltos.form.__form.fields;
    saltos.backup.__forms[key].templates = saltos.form.__form.templates;
};

/**
 * Restore feature
 *
 * This action performs the restoration action using the specified key, if the
 * key is not found, then empty fields and templates are used for the restoration
 *
 * @key => the key selector that you want to use.
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
 * Selector helper
 *
 * This function allow to search the forms contexts stored in the backup, allow to
 * do queries like two,one or top+one, in each case, the result will be an array
 * with the ids of the found items uwing the selector trick.
 *
 * All actions that uses this selector, are able to understand some expressions like comma
 * and plus, the follow examples shown how runs the selector in the restore feature:
 *
 * @two,one => this example is intended to restore the two context if it is found, otherwise
 * tries to restore the one context, otherwise a void context is set.
 *
 * @top+one => this example is intended to restore two contexts in one context, intender to
 * load the context of the search list that can be contained in the top and one containers.
 *
 * @key => the key selector that you want to use.
 */
saltos.backup.__selector_helper = key => {
    if (key.includes('+')) {
        key = key.split('+');
        let result = [];
        for (const i in key) {
            if (key[i] in saltos.backup.__forms) {
                result.push(key[i]);
            }
        }
        return result;
    }
    key = key.split(',');
    for (const i in key) {
        if (key[i] in saltos.backup.__forms) {
            return [key[i]];
        }
    }
    return [];
};

/**
 * Autosave feature object
 *
 * This object stores all functions used by the autosave feature
 *
 * Notes:
 *
 * The autosave feature uses the init, save, restore, clear and purge functions, and
 * workflow of the operation will be start using the init to initialize the key, then
 * the save to store the data of the key context, the restore will be used when a new
 * screen is loaded to load the stored data, the clear will remove the data and the
 * purge will remove the old entries, act as a garbage collector
 *
 * As an additional feature, this module uses the storage with the timestamp, this
 * usage allow to detect old entries and purge it.
 */
saltos.autosave = {};

/**
 * Init
 *
 * This function checks that the key not exists and creates a void entry, used by
 * save to allow the storage of data.
 *
 * @key  => the key selector that you want to use.
 * @hash => optional hash used in the current action.
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
 * Save
 *
 * This function save the key context data in the storage, only apply if previously
 * an init is executed.
 *
 * @key  => the key selector that you want to use.
 * @hash => optional hash used in the current action.
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
 * Restore
 *
 * This function restore the key context data of the storage, only apply if data is found
 * and if data contains real data, not a void object
 *
 * @key  => the key selector that you want to use.
 * @hash => optional hash used in the current action.
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
 * Clear
 *
 * This function remove the key context data in the storage, apply in all cases
 *
 * @key  => the key selector that you want to use.
 * @hash => optional hash used in the current action.
 */
saltos.autosave.clear = (key, hash = saltos.hash.get()) => {
    key = saltos.backup.__selector_helper(key);
    for (const i in key) {
        saltos.storage.removeItem(`saltos.autosave/${hash}/${key[i]}`);
    }
    return key.length > 0;
};

/**
 * Purge
 *
 * This function purge the void and old data, to do it, checks if data exists
 * and if it is void, in this case the element will be removed.
 *
 * As an additional feature, uses the saltos.storage.purgeWithTimestamp to maintain
 * clean the localStorage by purging old entries.
 *
 * @key  => the key selector that you want to use.
 * @hash => optional hash used in the current action.
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
