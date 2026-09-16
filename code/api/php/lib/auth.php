<?php

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
    // Zero check
    foreach (['user', 'pass'] as $key) {
        if ($$key === null) {
            return [
                'status' => 'ko',
                'text' => "$key not found",
                'code' => __get_code_from_trace(),
            ];
        }
    }

    $user_id = user_check($user);
    if (!$user_id) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }

    if (password_check($user_id, $pass)) {
        return token_issue($user_id);
    }

    if (password_expired_check($user_id, $pass)) {
        return [
            'status' => 'expired',
            'text' => 'Password expired',
            'code' => __get_code_from_trace(),
        ];
    }

    return [
        'status' => 'ko',
        'text' => 'Permission denied',
        'code' => __get_code_from_trace(),
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
 *
 * Notes:
 *
 * On success, this function returns the same response shape as a successful authtoken call
 * (via token_issue), exactly as the authrenew function does, so that changing the password
 * always ends in the same atomic, self-explanatory outcome: a fresh session issued for the
 * new password, replacing the token used to authorize the update
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

    // Zero check
    foreach (['oldpass', 'newpass', 'renewpass'] as $key) {
        if ($$key === null) {
            return [
                'status' => 'ko',
                'text' => "$key not found",
                'code' => __get_code_from_trace(),
            ];
        }
    }

    // Password checks
    if ($newpass !== $renewpass) {
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
    newpass_insert($user_id, $newpass);

    // The new password is already known to be correct, no need to go through authtoken again
    return token_issue($user_id);
}

/**
 * Authentication renew action
 *
 * This file implements the renew password action, intended to be used only when a login
 * attempt reports that the password is correct but expired (see the 'expired' status
 * returned by the authtoken function). It allows setting a new password without an active
 * session, using the expired password itself as the proof of identity.
 *
 * @user      => username used in the authentication process
 * @oldpass   => the expired password, must validate against the last password entry of the user
 * @newpass   => New password, must to be new, must to pass the score check and
 *               never must to be used in the system for the user
 * @renewpass => The repeated new password, to prevent writing errors
 *
 * This action not requires a valid token, as it's intended to be used exactly when the
 * standard login has failed because the password has expired
 *
 * Notes:
 *
 * On success, this function returns the same response shape as a successful authtoken call
 * (via token_issue), to allow a seamless login using the new password
 */
function authrenew($user, $oldpass, $newpass, $renewpass)
{
    // Zero check
    foreach (['user', 'oldpass', 'newpass', 'renewpass'] as $key) {
        if ($$key === null) {
            return [
                'status' => 'ko',
                'text' => "$key not found",
                'code' => __get_code_from_trace(),
            ];
        }
    }

    $user_id = user_check($user);
    if (!$user_id) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }

    // Expired password check
    if (!password_expired_check($user_id, $oldpass)) {
        return [
            'status' => 'ko',
            'text' => 'Permission denied',
            'code' => __get_code_from_trace(),
        ];
    }

    // Password checks
    if ($newpass !== $renewpass) {
        return [
            'status' => 'ko',
            'text' => 'New password differs',
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
    newpass_insert($user_id, $newpass);

    // The new password is already known to be correct, no need to go through authtoken again
    return token_issue($user_id);
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
    require_once 'php/lib/password.php';
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
    if (!is_array($row) || !isset($row['password'])) {
        return false;
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

/**
 * Password expired check
 *
 * This function checks that the provided password matches the last password entry of the
 * user_id, and that this last entry is not active (expired), this is used by the login
 * workflow to distinguish a wrong password from an expired one
 *
 * Notes:
 *
 * This uses password_row_check instead of a plain password_verify, so a user whose last
 * password was never migrated from the legacy MD5/SHA1/PHPASS formats (because it expired
 * before they ever logged in again) can still be recognized as expired instead of just ko
 *
 * @user_id => the user_id to use in the check task
 * @pass    => the password to use in the check task
 */
function password_expired_check($user_id, $pass)
{
    $query = 'SELECT * FROM tbl_users_passwords WHERE user_id = ? ORDER BY id DESC LIMIT 1';
    $row = execute_query($query, [$user_id]);
    if (!is_array($row) || !isset($row['password']) || $row['active']) {
        return false;
    }
    return password_row_check($row, $pass);
}

/**
 * User check
 *
 * This function checks that the given login belongs to an active user
 *
 * @user => the login to use in the check task
 *
 * Notes:
 *
 * This function returns the user_id on success or false otherwise
 */
function user_check($user)
{
    $query = 'SELECT * FROM tbl_users WHERE active = 1 AND login = ?';
    $row = execute_query($query, [$user]);
    if (!is_array($row) || !isset($row['login']) || $user !== $row['login']) {
        return false;
    }
    return $row['id'];
}

/**
 * Password row check
 *
 * This function checks if the given password matches the hash stored in the given
 * tbl_users_passwords row, supporting the current password_hash format and, for backward
 * compatibility, the legacy MD5/SHA1/PHPASS formats used by old SaltOS3 installations; when
 * a legacy format matches, the row is migrated in place to the current password_hash format
 *
 * @row  => the tbl_users_passwords row to check, requires at least the id and password keys
 * @pass => the password to use in the check task
 */
function password_row_check($row, $pass)
{
    if (password_verify($pass, $row['password'])) {
        return true;
    }

    require_once 'php/lib/password.php';
    if (
        in_array($row['password'], [md5($pass), sha1($pass)], true) ||
        password_verify_phpass($pass, $row['password'])
    ) {
        // Convert from MD5/SHA1/PHPASS to password_hash format
        $query = prepare_update_query('tbl_users_passwords', [
            'password' => password_hash($pass, PASSWORD_DEFAULT),
        ], [
            'id' => $row['id'],
        ]);
        db_query(...$query);
        return true;
    }

    return false;
}

/**
 * Password check
 *
 * This function checks that the given password matches the active password of the user_id
 *
 * @user_id => the user_id to use in the check task
 * @pass    => the password to use in the check task
 */
function password_check($user_id, $pass)
{
    $query = 'SELECT * FROM tbl_users_passwords WHERE user_id = ? AND active = 1';
    $row = execute_query($query, [$user_id]);
    if (!is_array($row) || !isset($row['password'])) {
        return false;
    }
    return password_row_check($row, $pass);
}

/**
 * Token issue
 *
 * This function revokes any previous active tokens of the user_id and issues a new one,
 * this is the common final step shared by the login, update and renew workflows
 *
 * @user_id => the user_id to use in the issue task
 */
function token_issue($user_id)
{
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
