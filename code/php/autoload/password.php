<?php

/*
 ____        _ _    ___  ____    _  _    ___
/ ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
\___ \ / _` | | __| | | \___ \  | || |_| | | |
 ___) | (_| | | |_| |_| |___) | |__   _| |_| |
|____/ \__,_|_|\__|\___/|____/     |_|(_)___/

SaltOS: Framework to develop Rich Internet Applications
Copyright (C) 2007-2023 by Josep Sanz Campderrós
More information in https://www.saltos.org or info@saltos.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);

/**
 * Password Strength
 *
 * This fucntion returns a number between 0 and 100 that try to categorize
 * the quality of the pass checked, this is usefull to known if the new
 * password is a good option or maybe is needed to request another new
 * password
 *
 * @pass => password that do you want to check
 */
function password_strength($pass)
{
    require_once "lib/wolfsoftware/password_strength.class.php";
    $ps = new Password_Strength();
    $ps->set_password($pass);
    $ps->calculate();
    $score = round($ps->get_score(), 0);
    unset($ps);
    return $score;
}

/**
 * Password Remake
 *
 * This fucntion returns the pass hash stored to be compared with the hashes
 * of the database, too allow to detect old passwords as md5 or sha1 hashes
 * and convert it to the new system using the crypto functions
 *
 * @user => username in plain text
 * @pass => password in plain text
 */
function password_remake($user, $pass)
{
    $query = "SELECT * FROM tbl_users WHERE " . make_where_query(array(
        "active" => 1,
        "login" => $user,
    ));
    $result = db_query($query);
    if (db_num_rows($result) == 1) {
        $row = db_fetch_row($result);
        if ($user == $row["login"]) {
            if (password_verify($pass, $row["password"])) {
                $pass = $row["password"];
            } elseif (in_array($row["password"], array(md5($pass),sha1($pass)))) {
                // CONVERT FROM MD5/SHA1 TO PASSWORD_HASH FORMAT
                $pass = password_hash($pass, PASSWORD_DEFAULT);
                $query = make_update_query("tbl_users", array(
                    "password" => $pass
                ), "active='1' AND login='{$user}'");
                db_query($query);
            }
        }
    }
    db_free($result);
    return $pass;
}
