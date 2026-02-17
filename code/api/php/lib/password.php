<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
    require_once 'lib/zxcvbn/vendor/autoload.php';
    static $zxcvbn = null;
    if ($zxcvbn === null) {
        $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
    }
    $result = $zxcvbn->passwordStrength($pass);
    return $result['score'] * 25;
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
    require_once 'lib/phpass/src/PasswordHash.php';
    static $t_hasher = null;
    if ($t_hasher === null) {
        $t_hasher = new PasswordHash(8, true);
    }
    $result = $t_hasher->CheckPassword($pass, $hash);
    return $result;
}
