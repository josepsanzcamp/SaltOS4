<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Math utils helper module
 *
 * This fie contains useful functions related to math operations
 */

/**
 * Sign function
 *
 * This function returns 1 for positive, -1 for negative and 0 for zero.
 *
 * @n => the number that you want to be processed
 */
function sign($n)
{
    if ($n > 0) {
        return 1;
    }
    if ($n < 0) {
        return -1;
    }
    return 0;
}

/**
 * Is Prime Number
 *
 * This function is a detector of prime numbers, uses some optimizations and
 * ideas from www.polprimos.com, please, see the previous web to understand
 * the speedup of this function in the prime numbers validation
 *
 * @num => the number that you want to check if it is a primer numner
 *
 * Notes:
 *
 * See www.polprimos.com for understand this algorithm
 */
function is_prime($num)
{
    if ($num < 2) {
        return false;
    }
    if ($num % 2 == 0 && $num != 2) {
        return false;
    }
    if ($num % 3 == 0 && $num != 3) {
        return false;
    }
    if ($num % 5 == 0 && $num != 5) {
        return false;
    }
    // Primer numbers are distributed in 8 columns
    $div = 7;
    $max = intval(sqrt(floatval($num)));
    for (;;) {
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div == 0 && $num != $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
    }
    return true;
}
