<?php

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

declare(strict_types=1);

/**
 * Tokens helper module
 *
 * This fie contains the functions related to the tokens usage and manipulations
 */

/**
 * Get Unique Token
 *
 * This function returns a string with a new and unique token
 */
function get_unique_token()
{
    $x = str_split(bin2hex(random_bytes(16)), 4);
    return $x[0] . $x[1] . '-' . $x[2] . '-' . $x[3] . '-' . $x[4] . '-' . $x[5] . $x[6] . $x[7];
}

/**
 * Check token format
 *
 * This function checks the correctness of the token and returns a valid
 * string that can be used safely as token in sql queries
 *
 * @token => the token that you want to process
 */
function check_token_format($token)
{
    // First check
    if (!is_string($token)) {
        return '';
    }
    // Check the number of parts and the length of each parts
    $parts = explode('-', $token);
    $lengths = array_map(function ($val) {
        return strlen($val);
    }, $parts);
    if (implode('-', $lengths) != '8-4-4-4-12') {
        return '';
    }
    // Check the type of each part
    if (!ctype_xdigit(implode('', $parts))) {
        return '';
    }
    return $token;
}
