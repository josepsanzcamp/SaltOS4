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
 * Iniset helper module
 *
 * This fie contains useful functions related to the evaluation of the iniset, puntenv and extra
 * directives configures in the config.xml file
 */

/**
 * Eval Iniset
 *
 * This function evaluates the iniset section of the config file, is intended
 * to execute all ini_set commands detecting the current values and determining
 * if is needed to change or not the current setting, is able to understand
 * boolean values as On/Off, and too is able to set keys as mbstring.internal_encoding
 * or mbstring.detect_order that must to be set by using another mb_* functions
 *
 * @array => the array with the pairs of keys vals
 */
function eval_iniset($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $key = fix_key($key);
            $current = ini_get($key);
            $diff = 0;
            if (strtolower($val) == "on" || strtolower($val) == "off") {
                $current = $current ? "On" : "Off";
                if (strtolower($val) != strtolower($current)) {
                    $diff = 1;
                }
            } else {
                if ($val != $current) {
                    $diff = 1;
                }
            }
            if ($diff) {
                if (ini_set($key, $val) === false) {
                    // @codeCoverageIgnoreStart
                    show_php_error(["phperror" => "ini_set fails to set '$key' from '$current' to '$val'"]);
                    // @codeCoverageIgnoreEnd
                }
            }
        }
    }
}

/**
 * Eval Putenv
 *
 * This function evaluates the putenv section of the config file, is intended
 * to execute all putenv commands detecting the current values and determining
 * if is needed to change or not the current setting
 *
 * @array => the array with the pairs of keys vals
 */
function eval_putenv($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $key = fix_key($key);
            $current = getenv($key);
            $diff = 0;
            if ($val != $current) {
                $diff = 1;
            }
            if ($diff) {
                if (putenv($key . "=" . $val) === false) {
                    // @codeCoverageIgnoreStart
                    show_php_error(["phperror" => "putenv fails to set '$key' from '$current' to '$val'"]);
                    // @codeCoverageIgnoreEnd
                }
            }
        }
    }
}

/**
 * Eval Extras
 *
 * This function evaluates the extra init requirements, intended for the multibyte
 * functions and for the gettext initialization process
 *
 * @array => the array with the pairs of keys vals
 *
 * Notes:
 *
 * If the val of the pair key val is an array, then each value of the array is
 * used as an argument for the key function, only is intended at the moment to
 * accept functions with one and two arguments, otherwise an error is triggered
 */
function eval_extras($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $key = fix_key($key);
            if (is_array($val)) {
                if (count($val) == 2) {
                    if ($key($val[0], $val[1]) === false) {
                        // @codeCoverageIgnoreStart
                        $val = implode(",", $val);
                        show_php_error(["phperror" => "$key fails to set '$val'"]);
                        // @codeCoverageIgnoreEnd
                    }
                    continue;
                }
                // @codeCoverageIgnoreStart
                $val = implode(",", $val);
                show_php_error(["phperror" => "$key fails to set '$val'"]);
                // @codeCoverageIgnoreEnd
            }
            // Special case only for the mb_detect_order that only accepts encodings
            // that appear in the mb_list_encondings, otherwise an error is launched
            if ($key == "mb_detect_order") {
                $val = implode(",", array_intersect(explode(",", $val), mb_list_encodings()));
            }
            if ($key($val) === false) {
                // @codeCoverageIgnoreStart
                show_php_error(["phperror" => "$key fails to set '$val'"]);
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
