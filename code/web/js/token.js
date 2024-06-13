
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
 * Token helper module
 *
 * This module provides the needed tools to manage the tokens
 */

/**
 * Token helper object
 *
 * This object stores all token functions to get and set data using the localStorage
 */
saltos.token = {};

/**
 * Get token function
 *
 * This function returns the token stored in the localStorage
 */
saltos.token.get = () => {
    return localStorage.getItem('saltos.token.token');
};

/**
 * Get expires_at function
 *
 * This function returns the expires_at stored in the localStorage
 */
saltos.token.get_expires_at = () => {
    return localStorage.getItem('saltos.token.expires_at');
};

/**
 * Set token and expires_at
 *
 * This function store the token and expires_at in the localStorage
 *
 * @response   => the object that contains the follow parameters:
 * @token      => the token that you want to store in the localStorage
 * @expires_at => the expires_at of the token that you want to store in the localStorage
 */
saltos.token.set = response => {
    localStorage.setItem('saltos.token.token', response.token);
    localStorage.setItem('saltos.token.expires_at', response.expires_at);
};

/**
 * Unset token and expires_at
 *
 * This function removes the token and expires_at in the localStorage
 */
saltos.token.unset = () => {
    localStorage.removeItem('saltos.token.token');
    localStorage.removeItem('saltos.token.expires_at');
};
