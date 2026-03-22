<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
        return null;
    }
    // Check the number of parts and the length of each parts
    $parts = explode('-', $token);
    $lengths = array_map(function ($val) {
        return strlen($val);
    }, $parts);
    if (implode('-', $lengths) !== '8-4-4-4-12') {
        return null;
    }
    // Check the type of each part
    if (!ctype_xdigit(implode('', $parts))) {
        return null;
    }
    return $token;
}
