
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
    return localStorage.getItem('saltos.gettext.lang');
};

/**
 * Set gettext function
 *
 * This function sets the gettext stored in the localStorage
 *
 * @gettext      => the gettext that you want to store in the localStorage
 */
saltos.gettext.set = lang => {
    localStorage.setItem('saltos.gettext.lang', lang);
};

/**
 * Unset gettext and expires_at
 *
 * This function removes the gettext and expires_at in the localStorage
 */
saltos.gettext.unset = () => {
    localStorage.removeItem('saltos.gettext.lang');
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
window.T = text => {
    if (typeof text != "string") {
        return text;
    }
    var app = saltos.gettext.cache.app;
    var lang = saltos.gettext.cache.lang;
    var locale = saltos.gettext.cache.locale;
    var hash = saltos.core.encode_bad_chars(text);
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
 * TODO
 *
 * TODO
 */
 saltos.gettext.bootstrap = {};

/**
 * TODO
 *
 * TODO
 */
saltos.gettext.bootstrap.field = field => {
    // For all general bootstrap widgers
    var props = ['label', 'tooltip', 'placeholder'];
    for (var i in props) {
        if (field.hasOwnProperty(props[i])) {
            field[props[i]] = T(field[props[i]]);
        }
    }
    // Only for table widgets
    if (field.hasOwnProperty('type') && field.type == 'table') {
        if (field.hasOwnProperty('header')) {
            for (var key in field.header) {
                var val = field.header[key];
                if (typeof val == 'object' && val !== null) {
                    field.header[key].label = T(val.label);
                } else {
                    field.header[key] = T(val);
                }
            }
        }
        if (field.hasOwnProperty('data')) {
            for (var key in field.data) {
                var val = field.data[key];
                if (val.hasOwnProperty('actions')) {
                    for (var key2 in val.actions) {
                        var val2 = val.actions[key2];
                        var props = ['label', 'tooltip'];
                        for (var i in props) {
                            if (val2.hasOwnProperty(props[i])) {
                                field.data[key].actions[key2][props[i]] = T(field.data[key].actions[key2][props[i]]);
                            }
                        }
                    }
                }
            }
        }
        if (field.hasOwnProperty('footer')) {
            if (typeof field.footer == 'object') {
                for (var key in field.footer) {
                    var val = field.footer[key];
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
    return saltos.bootstrap.field(field);
}

/**
 * TODO
 *
 * TODO
 */
saltos.gettext.bootstrap.modal = args => {
    var props = ['title', 'close', 'body', 'footer'];
    for (var i in props) {
        if (args.hasOwnProperty(props[i])) {
            args[props[i]] = T(args[props[i]]);
        }
    }
    return saltos.bootstrap.modal(args);
}

/**
 * TODO
 *
 * TODO
 */
saltos.gettext.bootstrap.toast = args => {
    var props = ['title', 'subtitle', 'close', 'body'];
    for (var i in props) {
        if (args.hasOwnProperty(props[i])) {
            args[props[i]] = T(args[props[i]]);
        }
    }
    return saltos.bootstrap.toast(args);
}
