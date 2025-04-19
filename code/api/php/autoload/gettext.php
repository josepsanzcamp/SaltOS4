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
 * Gettext helper module
 *
 * This fie contains useful functions related to gettext funcionality, allow to manage the
 * SaltOS translations using a merged system of the unix locales and the old SaltOS translations
 * system.
 */

/**
 * Get Text function
 *
 * This function replaces the gettext abreviation _() using the SaltOS gettext
 * feature, is based in the original system of the old SaltOS with improvements
 * to do more open as the GNU gettext
 *
 * @text => The text that you want to translate
 * @app  => Optional, to force gettext to use an specific app instead of current_app
 *
 * Notes:
 *
 * This function uses multiples locales at same time, SaltOS provides a basic set of
 * usefull strings and each application can add and overwrite more strings, this is
 * the same feature that old SaltOS provides
 *
 * If you call the function without argument, the function returns the gettext
 * dictionary intended to populate the clients gettext module and contains the
 * app, the lang and the locales for the app and lang.
 */
function T($text = '', $app = '')
{
    static $cache = [];
    $lang = current_lang();
    if ($lang && !isset($cache[$lang])) {
        $file = "locale/$lang/messages.yaml";
        if (file_exists($file)) {
            $temp = yaml_parse_file($file);
            if (is_array($temp)) {
                $cache[$lang] = $temp;
            }
        }
    }
    if (!$app) {
        $app = current_app();
    }
    if ($app && !isset($cache[$app][$lang])) {
        $dir = detect_app_folder($app);
        $file = "apps/$dir/locale/$lang/messages.yaml";
        if (file_exists($file)) {
            $temp = yaml_parse_file($file);
            if (is_array($temp)) {
                $cache[$app][$lang] = $temp;
            }
        }
    }
    if (!func_num_args()) {
        return [
            'app' => $app,
            'lang' => $lang,
            'locale' => $cache,
        ];
    }
    if (!is_string($text)) {
        show_php_error(['phperror' => 'text is not string']);
    }
    $hash = encode_bad_chars($text);
    if (isset($cache[$app][$lang][$hash])) {
        return $cache[$app][$lang][$hash];
    }
    if (isset($cache[$lang][$hash])) {
        return $cache[$lang][$hash];
    }
    return $text;
}

/**
 * Check lang format
 *
 * This function checks the correctness of the lang and returns a valid
 * string that can be used safely as lang in other sites
 *
 * @lang => the lang that you want to process
 */
function check_lang_format($lang)
{
    // First check
    if (!is_string($lang)) {
        return null;
    }
    // Check the number of parts and the length of each parts
    $temp = explode(' ', str_replace(['-', '_', '.'], ' ', $lang));
    if (count($temp) < 2) {
        return null;
    }
    if (strlen($temp[0]) != 2) {
        return null;
    }
    if (strlen($temp[1]) != 2) {
        return null;
    }
    // Build the output
    $temp[0] = strtolower($temp[0]);
    $temp[1] = strtoupper($temp[1]);
    return "{$temp[0]}_{$temp[1]}";
}

/**
 * Current lang
 *
 * This function returns the current lang
 */
function current_lang()
{
    return get_data('server/lang');
}
