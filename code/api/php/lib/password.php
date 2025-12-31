<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2026 by Josep Sanz Campderrós
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
 * Password helper module
 *
 * This fie contains useful functions related to password, currently only publish one feature to check
 * the password strength, but is open to be used to add more password features if it is needed
 */

/**
 * Password Strength
 *
 * This fucntion returns a number between 0 and 100 that try to categorize
 * the quality of the pass checked, this is useful to known if the new
 * password is a good option or maybe is needed to request another new
 * password
 *
 * @pass => password that do you want to check
 */
function password_strength($pass)
{
    require_once 'lib/wolfsoftware/password_strength.class.php';
    $ps = new Password_Strength();
    $ps->set_password($pass);
    $ps->calculate();
    $score = max(min(round($ps->get_score(), 0), 100), 0);
    unset($ps);
    return $score;
}

/**
 * Verifies a password hash using the legacy PHPass framework.
 *
 * This function is designed for backward compatibility with older SaltOS3 versions
 * which used the external PHPass library for password hashing. Newer versions of SaltOS
 * rely on PHP's native password_hash() and password_verify() functions, but this function
 * is kept to ensure validation of legacy password hashes.
 *
 * @pass => The plain text password provided by the user.
 * @hash => The hashed password stored in the database (created with PHPass).
 *
 * Returns true if the given password matches the stored hash, false otherwise.
 *
 * Notes:
 *
 * - see https://www.openwall.com/phpass/ (Original PHPass project)
 */
function password_verify_phpass($pass, $hash)
{
    require_once 'lib/phpass/PasswordHash.php';
    $t_hasher = new PasswordHash(8, true);
    $result = $t_hasher->CheckPassword($pass, $hash);
    unset($t_hasher);
    return $result;
}
