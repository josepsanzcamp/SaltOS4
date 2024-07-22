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
 * Users functions
 *
 * This file contain all functions needed by the users app
 */

/**
 * Insert user action
 *
 * This action allow to insert registers in the database associated to
 * the users app and only requires the data.
 *
 * TODO
 */
function insert_user($data)
{
    if (!is_array($data) || !count($data)) {
        return [
            "status" => "ko",
            "text" => "Data not found",
            "code" => __get_code_from_trace(),
        ];
    }

    // Check parameters
    foreach (["newpass", "renewpass"] as $key) {
        if (!isset($data[$key]) || $data[$key] == "") {
            return [
                "status" => "ko",
                "text" => "$key not found or void",
                "code" => __get_code_from_trace(),
            ];
        }
    }
    $newpass = $data["newpass"];
    $renewpass = $data["renewpass"];

    // Password checks
    if ($newpass != $renewpass) {
        return [
            "status" => "ko",
            "text" => "New password differs",
            "code" => __get_code_from_trace(),
        ];
    }

    // Score check
    $minscore = intval(get_config("auth/passwordminscore"));
    require_once "php/lib/password.php";
    if (password_strength($newpass) < $minscore) {
        return [
            "status" => "ko",
            "text" => "New password strength error",
            "code" => __get_code_from_trace(),
        ];
    }

    // Fix for days
    $data["days"] = days2bin($data["days"] ?? "");

    // Real insert using general insert action
    unset($data["newpass"]);
    unset($data["renewpass"]);
    require_once "php/lib/actions.php";
    $array = insert("users", $data);
    if ($array["status"] == "ko") {
        return $array;
    }

    // Continue creating the password entry
    $user_id = $array["created_id"];
    $newpass = password_hash($newpass, PASSWORD_DEFAULT);
    $created_at = current_datetime();
    $expires_at = current_datetime(get_config("auth/passwordexpires"));

    $query = make_insert_query("tbl_users_passwords", [
        "active" => 1,
        "user_id" => $user_id,
        "created_at" => $created_at,
        "remote_addr" => get_data("server/remote_addr"),
        "user_agent" => get_data("server/user_agent"),
        "password" => $newpass,
        "expires_at" => $expires_at,
    ]);
    db_query($query);

    return [
        "status" => "ok",
        "created_id" => $user_id,
    ];
}

/**
 * Update user action
 *
 * This action allow to update registers in the database associated to
 * the users app and requires the user_id and data.
 *
 * TODO
 */
function update_user($user_id, $data)
{
    if (!is_array($data) || !count($data)) {
        return [
            "status" => "ko",
            "text" => "Data not found",
            "code" => __get_code_from_trace(),
        ];
    }

    $newpass = $data["newpass"] ?? "";
    $renewpass = $data["renewpass"] ?? "";

    if ($newpass || $renewpass) {
        // Password checks
        if ($newpass != $renewpass) {
            return [
                "status" => "ko",
                "text" => "New password differs",
                "code" => __get_code_from_trace(),
            ];
        }

        // Score check
        $minscore = intval(get_config("auth/passwordminscore"));
        require_once "php/lib/password.php";
        if (password_strength($newpass) < $minscore) {
            return [
                "status" => "ko",
                "text" => "New password strength error",
                "code" => __get_code_from_trace(),
            ];
        }

        // Old passwords check
        $query = "SELECT password FROM tbl_users_passwords WHERE " . make_where_query([
            "user_id" => $user_id,
        ]);
        $oldspass = execute_query_array($query);
        foreach ($oldspass as $oldpass) {
            if (password_verify($newpass, $oldpass)) {
                return [
                    "status" => "ko",
                    "text" => "New password used previously",
                    "code" => __get_code_from_trace(),
                ];
            }
        }
    }

    if (isset($data["days"])) {
        // Fix for days
        $data["days"] = days2bin($data["days"]);
    }

    // Real update using general update action
    unset($data["newpass"]);
    unset($data["renewpass"]);
    if (count($data)) {
        require_once "php/lib/actions.php";
        $array = update("users", $user_id, $data);
        if ($array["status"] == "ko") {
            return $array;
        }
    }

    if ($newpass || $renewpass) {
        // Continue
        $query = make_update_query("tbl_users_passwords", [
            "active" => 0,
        ], make_where_query([
            "user_id" => $user_id,
        ]));
        db_query($query);

        // Continue creating the password entry
        $newpass = password_hash($newpass, PASSWORD_DEFAULT);
        $created_at = current_datetime();
        $expires_at = current_datetime(get_config("auth/passwordexpires"));

        $query = make_insert_query("tbl_users_passwords", [
            "active" => 1,
            "user_id" => $user_id,
            "created_at" => $created_at,
            "remote_addr" => get_data("server/remote_addr"),
            "user_agent" => get_data("server/user_agent"),
            "password" => $newpass,
            "expires_at" => $expires_at,
        ]);
        db_query($query);
    }

    return [
        "status" => "ok",
        "updated_id" => $user_id,
    ];
}

/**
 * Delete user action
 *
 * This action allow to delete registers in the database associated to
 * the users app and only requires the user_id.
 *
 * TODO
 */
function delete_user($user_id)
{
    // Real delete using general delete action
    require_once "php/lib/actions.php";
    $array = delete("users", $user_id);
    if ($array["status"] == "ko") {
        return $array;
    }

    // Continue removing the password entry
    $query = "DELETE FROM tbl_users_passwords WHERE user_id = $user_id";
    db_query($query);

    return [
        "status" => "ok",
        "deleted_id" => $user_id,
    ];
}

/**
 * Days to bin
 *
 * This function tries to convert the days format used by the multiselect
 * to the string expected by the database formed by ones and zeroes to
 * represent if a day is operative for the user or not, for example, the
 * selection 64,32,16,8,4 is returned like from monday to friday (1111100)
 *
 * @days => the string containing the days in power of two separated by comma
 */
function days2bin($days)
{
    $days = array_diff(explode(",", $days), [""]);
    $days = decbin(array_sum($days));
    $days = str_pad($days, 7, "0", STR_PAD_LEFT);
    return $days;
}

/**
 * Bin to days
 *
 * This function tries to do the reverse action that the previous function,
 * is able to get an string like 1111100 and returns the list of all bits in
 * decimal like 64,32,16,8,4.
 *
 * @days => the string containing the days in binary format
 */
function bin2days($days)
{
    $days = str_split($days);
    $days = array_reverse($days);
    foreach ($days as $key => $val) {
        $days[$key] = 2 ** $key * $val;
    }
    $days = array_diff($days, [0]);
    $days = implode(",", $days);
    return $days;
}

/**
 * Fix for days
 *
 * This function is intended to be used as wrapper in the result of the query
 * that contains an element called days, in the database the days is stored
 * using the binary notation like 1111100, and for the user interface, is needed
 * to translate this string into a decimal string like 64,32,16,8,4.
 *
 * @data => the data obtained from an execute_query, for example, they must contain
 *          an entry called days.
 */
function fix4days($data)
{
    $data["days"] = bin2days($data["days"]);
    return $data;
}
