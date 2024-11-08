
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
 * Gettext helper module
 *
 * This fie contains useful functions related to gettext funcionality, allow to manage the
 * SaltOS translations using a merged system of the unix locales and the old SaltOS translations
 * system.
 */

/**
 * Gettext helper object
 *
 * This object stores all gettext functions to get and set data using the localStorage
 */
saltos.gettext = {
    cache: {
        app: '',
        lang: '',
        locale: {},
    },
};

/**
 * Get gettext function
 *
 * This function returns the gettext stored in the localStorage
 */
saltos.gettext.get = () => {
    return saltos.storage.getItem('saltos.gettext.lang');
};

/**
 * Get Short gettext function
 *
 * This function returns the short version of gettext stored in the localStorage
 */
saltos.gettext.get_short = () => {
    return saltos.storage.getItem('saltos.gettext.short');
};

/**
 * Set gettext function
 *
 * This function sets the gettext stored in the localStorage
 *
 * @gettext      => the gettext that you want to store in the localStorage
 */
saltos.gettext.set = lang => {
    lang = lang.replace('-', '_');
    const short = lang.split('_').at(0);
    document.documentElement.setAttribute('lang', short);
    saltos.storage.setItem('saltos.gettext.lang', lang);
    saltos.storage.setItem('saltos.gettext.short', short);
};

/**
 * Unset gettext and expires_at
 *
 * This function removes the gettext and expires_at in the localStorage
 */
saltos.gettext.unset = () => {
    saltos.storage.removeItem('saltos.gettext.lang');
    saltos.storage.removeItem('saltos.gettext.short');
};

/**
 * Get Text function
 *
 * This function replaces the gettext abreviation _() using the SaltOS gettext
 * feature, is based in the original system of the old SaltOS with improvements
 * to do more open as the GNU gettext
 *
 * @text => The text that you want to translate
 *
 * Notes:
 *
 * This function uses multiples locales at same time, SaltOS provides a basic set of
 * usefull strings and each application can add and overwrite more strings, this is
 * the same feature that old SaltOS provides
 */
saltos.gettext.T = text => {
    if (typeof text != 'string') {
        throw new Error('Unknown gettext typeof ' + typeof text);
    }
    const app = saltos.gettext.cache.app;
    const lang = saltos.gettext.cache.lang;
    const locale = saltos.gettext.cache.locale;
    const hash = saltos.core.encode_bad_chars(text);
    if (locale.hasOwnProperty(app)) {
        if (locale[app].hasOwnProperty(lang)) {
            if (locale[app][lang].hasOwnProperty(hash)) {
                return locale[app][lang][hash];
            }
        }
    }
    if (locale.hasOwnProperty(lang)) {
        if (locale[lang].hasOwnProperty(hash)) {
            return locale[lang][hash];
        }
    }
    return text;
};

/**
 * T function
 *
 * This line allow to publish the saltos.gettext.T function in the global scope
 */
window.T = saltos.gettext.T;

/**
 * Bootstrap gettext object
 *
 * This object stores some bootstrap overloads that allow to other modules to
 * access to the bootstrap modules using the gettext feature and translating
 * texts.
 */
saltos.gettext.bootstrap = {};

/**
 * Bootstrap field overload
 *
 * This function overload the saltos.bootstrap.field funtion to add gettext
 * features to the arguments depending the type of field.
 *
 * @field => the argument passed to the original bootstrap function
 */
saltos.gettext.bootstrap.field = field => {
    // For all general bootstrap widgers
    const props = ['label', 'tooltip', 'placeholder'];
    for (const i in props) {
        if (field.hasOwnProperty(props[i])) {
            field[props[i]] = T(field[props[i]]);
        }
    }
    // Only for table widgets
    if (field.hasOwnProperty('type') && field.type == 'table') {
        if (field.hasOwnProperty('header')) {
            for (const key in field.header) {
                field.header[key] = saltos.core.join_attr_value(field.header[key]);
                const val = field.header[key];
                if (typeof val == 'object' && val !== null) {
                    field.header[key].label = T(val.label);
                } else {
                    field.header[key] = T(val);
                }
            }
        }
        if (field.hasOwnProperty('data')) {
            for (const key in field.data) {
                const val = field.data[key];
                if (val.hasOwnProperty('actions')) {
                    for (const key2 in val.actions) {
                        const val2 = val.actions[key2];
                        const props = ['label', 'tooltip'];
                        for (const i in props) {
                            if (val2.hasOwnProperty(props[i])) {
                                field.data[key].actions[key2][props[i]] =
                                    T(field.data[key].actions[key2][props[i]]);
                            }
                        }
                    }
                }
            }
        }
        if (field.hasOwnProperty('footer')) {
            if (typeof field.footer == 'object') {
                for (const key in field.footer) {
                    const val = field.footer[key];
                    if (typeof val == 'object' && val !== null) {
                        field.footer[key].value = T(val.value);
                    } else {
                        field.footer[key] = T(val);
                    }
                }
            }
            if (typeof field.footer == 'string') {
                field.footer = T(field.footer);
            }
        }
    }
    // Only for table and list widgets
    if (field.hasOwnProperty('type') && ['table', 'list', 'jstree'].includes(field.type)) {
        const props = ['nodata'];
        for (const i in props) {
            if (field.hasOwnProperty(props[i])) {
                field[props[i]] = T(field[props[i]]);
            }
        }
    }
    // Only for select and multiselect widgets
    if (field.hasOwnProperty('type') && ['select', 'multiselect'].includes(field.type)) {
        if (field.hasOwnProperty('rows')) {
            for (const key in field.rows) {
                field.rows[key] = saltos.core.join_attr_value(field.rows[key]);
                const val = field.rows[key];
                if (val.hasOwnProperty('label')) {
                    field.rows[key].label = T(val.label);
                }
            }
        }
    }
    // Only for alert widgets
    if (field.hasOwnProperty('type') && field.type == 'alert') {
        const props = ['title', 'text', 'body'];
        for (const i in props) {
            if (field.hasOwnProperty(props[i])) {
                field[props[i]] = T(field[props[i]]);
            }
        }
    }
    // Only for dropdown widgets
    if (field.hasOwnProperty('type') && field.type == 'dropdown') {
        if (field.hasOwnProperty('menu')) {
            for (const key in field.menu) {
                field.menu[key] = saltos.core.join_attr_value(field.menu[key]);
                const val = field.menu[key];
                const props = ['label', 'tooltip'];
                for (const i in props) {
                    if (val.hasOwnProperty(props[i])) {
                        field.menu[key][props[i]] = T(field.menu[key][props[i]]);
                    }
                }
            }
        }
    }
    return saltos.bootstrap.field(field);
};

/**
 * Bootstrap modal overload
 *
 * This function overload the saltos.bootstrap.modal funtion to add gettext
 * features to the arguments
 *
 * @arg => the argument passed to the original bootstrap function
 */
saltos.gettext.bootstrap.modal = args => {
    const props = ['title', 'close', 'body', 'footer'];
    for (const i in props) {
        if (args.hasOwnProperty(props[i])) {
            if (typeof args[props[i]] == 'string') {
                args[props[i]] = T(args[props[i]]);
            }
        }
    }
    return saltos.bootstrap.modal(args);
};

/**
 * Bootstrap toast overload
 *
 * This function overload the saltos.bootstrap.toast funtion to add gettext
 * features to the arguments
 *
 * @arg => the argument passed to the original bootstrap function
 */
saltos.gettext.bootstrap.toast = args => {
    const props = ['title', 'subtitle', 'close', 'body'];
    for (const i in props) {
        if (args.hasOwnProperty(props[i])) {
            if (typeof args[props[i]] == 'string') {
                args[props[i]] = T(args[props[i]]);
            }
        }
    }
    return saltos.bootstrap.toast(args);
};

/**
 * Bootstrap menu overload
 *
 * This function overload the saltos.bootstrap.menu funtion to add gettext
 * features to the arguments
 *
 * @arg => the argument passed to the original bootstrap function
 */
saltos.gettext.bootstrap.menu = args => {
    if (args.hasOwnProperty('menu')) {
        for (const key in args.menu) {
            const val = args.menu[key];
            if (val.hasOwnProperty('label')) {
                args.menu[key].label = T(val.label);
            }
            if (val.hasOwnProperty('menu')) {
                for (const key2 in val.menu) {
                    const val2 = val.menu[key2];
                    if (val2.hasOwnProperty('label')) {
                        args.menu[key].menu[key2].label = T(val2.label);
                    }
                }
            }
        }
    }
    return saltos.bootstrap.menu(args);
};

/**
 * Bootstrap offcanvas overload
 *
 * This function overload the saltos.bootstrap.offcanvas funtion to add gettext
 * features to the arguments
 *
 * @arg => the argument passed to the original bootstrap function
 */
saltos.gettext.bootstrap.offcanvas = args => {
    const props = ['title', 'close', 'body'];
    for (const i in props) {
        if (args.hasOwnProperty(props[i])) {
            if (typeof args[props[i]] == 'string') {
                args[props[i]] = T(args[props[i]]);
            }
        }
    }
    return saltos.bootstrap.offcanvas(args);
};
