
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
