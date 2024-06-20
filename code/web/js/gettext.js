
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
 * This module provides the needed tools to manage the gettexts
 */

/**
 * Gettext helper object
 *
 * This object stores all gettext functions to get and set data using the localStorage
 */
saltos.gettext = {};

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
