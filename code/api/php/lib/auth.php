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
 * Login functions
 *
 * This file contain all functions needed by the logins app
 */

/**
 * Authentication token action
 *
 * This file implements the login action, allowing to authenticate users using the pair
 * of login/password to validate the credentials and get a valid token to operate in SaltOS
 *
 * @user => username used in the authentication process
 * @pass => password used in the authentication process
 *
 * This action not requires a valid token, all valid tokens associated
 * to the user will be revoked when a new token is assigned, as the result of this action
 * is a flag that indicates the validity of the token, this action returns a json with the
 * status of te token instead of returns a json with an error in case of non validity
 */
function authtoken($user, $pass)
{
    // First check
    $query = 'SELECT * FROM tbl_users WHERE active = 1 AND login = ?';
    $row = execute_query($query, [$user]);
    if (!is_array($row) || !isset($row['login']) || $user != $row['login']) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }
    $user_id = $row['id'];

    // Second check
    $query = 'SELECT * FROM tbl_users_passwords WHERE user_id = ? AND active = 1';
    $row2 = execute_query($query, [$user_id]);
    if (!is_array($row2) || !isset($row2['password'])) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    } elseif (password_verify($pass, $row2['password'])) {
        // Nothing to do, password is correct!!!
    } elseif (in_array($row2['password'], [md5($pass), sha1($pass)])) {
        // Convert from MD5/SHA1 to password_hash format
        $row2['password'] = password_hash($pass, PASSWORD_DEFAULT);
        $query = prepare_update_query('tbl_users_passwords', [
            'password' => $row2['password'],
        ], [
            'id' => $row2['id'],
        ]);
        db_query(...$query);
    } else {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }

    // Continue
    $query = prepare_update_query('tbl_users_tokens', [
        'active' => 0,
    ], [
        'user_id' => $user_id,
        'active' => 1,
    ]);
    db_query(...$query);

    $created_at = current_datetime();
    $token = get_unique_token();
    $short_expires = current_datetime(get_config('auth/tokenshortexpires'));
    $long_expires = current_datetime(get_config('auth/tokenlongexpires'));

    $query = prepare_insert_query('tbl_users_tokens', [
        'user_id' => $user_id,
        'active' => 1,
        'created_at' => $created_at,
        'remote_addr' => get_data('server/remote_addr'),
        'user_agent' => get_data('server/user_agent'),
        'token' => $token,
        'expires_at' => min($short_expires, $long_expires),
    ]);
    db_query(...$query);

    return [
        'status' => 'ok',
        'token' => $token,
        'created_at' => $created_at,
        'expires_at' => min($short_expires, $long_expires),
    ];
}

/**
 * Deauthentication token action
 *
 * This file implements the logout action, allowing to deauthenticate users
 * using a valid token, for security reasons, the deauth action only can
 * be performed by the same actor that execute the login action
 *
 * The unique requirement to execute this action is to have a valid token
 */
function deauthtoken()
{
    $token_id = current_token();
    if (!$token_id) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }

    $query = prepare_update_query('tbl_users_tokens', [
        'active' => 0,
    ], [
        'id' => $token_id,
    ]);
    db_query(...$query);

    return [
        'status' => 'ok',
    ];
}

/**
 * Check token action
 *
 * This file implements the check action, allowing to check token's validity, the check
 * action only can be performed by the same actor that execute the login action
 *
 * The unique requirement to execute this action is to have a token to be checked, as the
 * result of this action is a flag that indicates the validity of the token, this action
 * returns a json with the status of te token instead of returns a json with an error in
 * case of non validity
 */
function checktoken()
{
    $token_id = current_token();
    if (!$token_id) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }

    $query = 'SELECT * FROM tbl_users_tokens WHERE id = ?';
    $row = execute_query($query, [$token_id]);

    $updated_at = current_datetime();
    $short_expires = current_datetime(get_config('auth/tokenshortexpires'));
    $long_expires = date('Y-m-d H:i:s', strtotime($row['created_at']) + get_config('auth/tokenlongexpires'));

    $query = prepare_update_query('tbl_users_tokens', [
        'updated_at' => $updated_at,
        'expires_at' => min($short_expires, $long_expires),
    ], [
        'id' => $token_id,
    ]);
    db_query(...$query);

    return [
        'status' => 'ok',
        'token' => $row['token'],
        'created_at' => $row['created_at'],
        'updated_at' => $updated_at,
        'expires_at' => min($short_expires, $long_expires),
    ];
}

/**
 * Authentication update action
 *
 * This file implements the update password action, allowing to authenticated
 * users by a token, and providing the old password to update a new password
 *
 * @oldpass   => Old password, must to validate the active password of the user
 *               associated to the token used in the action
 * @newpass   => New password, must to be new, must to pass the score check and
 *               never must to be used in the system for the user
 * @renewpass => The repeated new password, to prevent writing errors
 *
 * This action requires a valid token associated to the user that wants to do
 * the password update
 */
function authupdate($oldpass, $newpass, $renewpass)
{
    $user_id = current_user();
    if (!$user_id) {
        return [
            'status' => 'ko',
            'text' => 'Authentication update error',
            'code' => __get_code_from_trace(),
        ];
    }

    // Password checks
    if ($newpass != $renewpass) {
        return [
            'status' => 'ko',
            'text' => 'New password differs',
            'code' => __get_code_from_trace(),
        ];
    }

    if (!oldpass_check($user_id, $oldpass)) {
        return [
            'status' => 'ko',
            'text' => 'Old password authentication error',
            'code' => __get_code_from_trace(),
        ];
    }

    // Score check
    if (!score_check($newpass)) {
        return [
            'status' => 'ko',
            'text' => 'New password strength error',
            'code' => __get_code_from_trace(),
        ];
    }

    // Old passwords check
    if (!newpass_check($user_id, $newpass)) {
        return [
            'status' => 'ko',
            'text' => 'New password used previously',
            'code' => __get_code_from_trace(),
        ];
    }

    // Continue
    oldpass_disable($user_id);
    $array = newpass_insert($user_id, $newpass);

    return [
        'status' => 'ok',
        'updated_at' => $array['created_at'],
        'expires_at' => $array['expires_at'],
    ];
}

/**
 * Score check
 *
 * This function checks the score quality of the provided password
 *
 * @newpass => the password thay you want to check
 */
function score_check($newpass)
{
    $minscore = intval(get_config('auth/passwordminscore'));
    require_once __ROOT__ . 'php/lib/password.php';
    return password_strength($newpass) >= $minscore;
}

/**
 * Old password check
 *
 * This function checks that the provided password is valid for the user_id
 *
 * @user_id => the user_id to use in the check task
 * @oldpass => the password to use in the check task
 */
function oldpass_check($user_id, $oldpass)
{
    $query = 'SELECT * FROM tbl_users_passwords WHERE user_id = ? AND active = 1';
    $row = execute_query($query, [$user_id]);
    if (!is_array($row)) {
        return false;
    }
    if (!isset($row['password'])) {
        show_php_error(['phperror' => 'Internal error']);
    }
    return password_verify($oldpass, $row['password']);
}

/**
 * New password check
 *
 * This function checks that the provided password has never been used by the user_id
 *
 * @user_id => the user_id to use in the check task
 * @newpass => the password to use in the check task
 */
function newpass_check($user_id, $newpass)
{
    $query = 'SELECT password FROM tbl_users_passwords WHERE user_id = ?';
    $oldspass = execute_query_array($query, [$user_id]);
    foreach ($oldspass as $oldpass) {
        if (password_verify($newpass, $oldpass)) {
            return false;
        }
    }
    return true;
}

/**
* Old password disable
*
* This function disable all passwords associated to the user_id
*
 * @user_id => the user_id to use in the check task
*/
function oldpass_disable($user_id)
{
    $query = prepare_update_query('tbl_users_passwords', [
        'active' => 0,
    ], [
        'user_id' => $user_id,
        'active' => 1,
    ]);
    db_query(...$query);
}

/**
* New password insert
*
* This function inserts a new password record to the database
*
 * @user_id => the user_id to use in the insert task
 * @newpass => the password to use in the insert task
 *
 * Notes:
 *
 * This function returns the created and expires timestamps
*/
function newpass_insert($user_id, $newpass)
{
    $newpass = password_hash($newpass, PASSWORD_DEFAULT);
    $created_at = current_datetime();
    $expires_at = current_datetime(get_config('auth/passwordexpires'));

    $query = prepare_insert_query('tbl_users_passwords', [
        'active' => 1,
        'user_id' => $user_id,
        'created_at' => $created_at,
        'remote_addr' => get_data('server/remote_addr'),
        'user_agent' => get_data('server/user_agent'),
        'password' => $newpass,
        'expires_at' => $expires_at,
    ]);
    db_query(...$query);

    return [
        'created_at' => $created_at,
        'expires_at' => $expires_at,
    ];
}
