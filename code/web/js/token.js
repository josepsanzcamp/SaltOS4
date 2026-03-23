
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
    return saltos.storage.getItem('saltos.token.token');
};

/**
 * Get expires_at function
 *
 * This function returns the expires_at stored in the localStorage
 */
saltos.token.get_expires_at = () => {
    return saltos.storage.getItem('saltos.token.expires_at');
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
    saltos.storage.setItem('saltos.token.token', response.token);
    saltos.storage.setItem('saltos.token.expires_at', response.expires_at);
};

/**
 * Unset token and expires_at
 *
 * This function removes the token and expires_at in the localStorage
 */
saltos.token.unset = () => {
    saltos.storage.removeItem('saltos.token.token');
    saltos.storage.removeItem('saltos.token.expires_at');
};
