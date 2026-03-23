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
    if ($num % 2 === 0 && $num !== 2) {
        return false;
    }
    if ($num % 3 === 0 && $num !== 3) {
        return false;
    }
    if ($num % 5 === 0 && $num !== 5) {
        return false;
    }
    // Primer numbers are distributed in 8 columns
    $div = 7;
    $max = intval(sqrt(floatval($num)));
    for (;;) {
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 4;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 2;
        if ($num % $div === 0 && $num !== $div) {
            return false;
        }
        if ($div >= $max) {
            break;
        }
        $div += 6;
    }
    return true;
}
