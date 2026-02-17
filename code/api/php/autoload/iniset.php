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
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
            $cur = ini_get($key);
            $diff = false;
            if (strtolower($val) === 'on' || strtolower($val) === 'off') {
                $cur = $cur ? 'On' : 'Off';
                if (strtolower($val) !== strtolower($cur)) {
                    $diff = true;
                }
            } else {
                if ($val !== $cur) {
                    $diff = true;
                }
            }
            if ($diff) {
                if (ini_set($key, $val) === false) {
                    exec_check_system();
                    show_php_error(['phperror' => "ini_set fails to set '$key' from '$cur' to '$val'"]);
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
            $cur = getenv($key);
            $diff = 0;
            if ($val !== $cur) {
                $diff = 1;
            }
            if ($diff) {
                /**
                 * According to the documentation, putenv must return false in error cases, but
                 * unfortunately php does not return false and catch a fatal error like this:
                 *
                 * putenv(): Argument #1 ($assignment) must have a valid syntax (code 0)
                 *
                 * For this reason, the show_php_error placed after the putenv that must to
                 * be executed when putenv returns false never can be executed.
                 *
                 * As trick, I have added the void key condition to force a case that executes
                 * the show_php_error
                 */
                if ($key === '' || putenv("$key=$val") === false) {
                    show_php_error(['phperror' => "putenv fails to set '$key' from '$cur' to '$val'"]);
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
 */
function eval_extras($array)
{
    if (is_array($array)) {
        foreach ($array as $key => $val) {
            $key = fix_key($key);
            if ($key === 'mb_detect_order') {
                $val = array_intersect($val, mb_list_encodings());
            }
            if (!function_exists($key) || $key($val) === false) {
                exec_check_system();
                show_php_error(['phperror' => "$key fails to set '$val'"]);
            }
        }
    }
}
