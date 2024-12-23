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
