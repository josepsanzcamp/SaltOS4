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
 * Array helper module
 *
 * This file contain useful array functions
 */

/**
 * Null to array converter
 *
 * This function convert all nulls into an array, is intended to be
 * used as helper for example in the glob output, to force to have
 * an array in all cases
 *
 * @arr => the input, generally must to be an array, if a null is passed,
 *         then a void array will be returned
 */
function array_protected($x)
{
    if ($x === null) {
        return [];
    }
    if (is_string($x)) {
        if ($x != "") {
            return [$x];
        }
        return [];
    }
    if (!is_array($x)) {
        return [$x];
    }
    return $x;
}

/**
 * Join for array
 *
 * This function allow to join the #attr and value to get only an associative
 * array, it is intended to be used to simplify the specification of some elements
 * and to simplify the usage of this elements in the client side
 *
 * @array => the input that can contains an array with #attr and value
 */
function join4array($array)
{
    if (is_array($array) && isset($array["value"]) && isset($array["#attr"])) {
        if (is_string($array["value"])) {
            if (trim($array["value"]) == "") {
                $array["value"] = [];
            } else {
                $array["value"] = ["value" => $array["value"]];
            }
        }
        $array = array_merge($array["value"], $array["#attr"]);
    }
    return $array;
}
