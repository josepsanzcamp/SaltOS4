<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2023 by Josep Sanz Campderrós
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
 * Get Text function
 *
 * This function replaces the gettext abreviation _() using the SaltOS gettext
 * feature, is based in the original system of the SaltOS 3 with improvements
 * to do more open as the GNU gettext
 */
function T($text)
{
    static $cache = [];
    $lang = getenv("LANG");
    $temp = explode(".", $lang);
    $lang = $temp[0];
    if (!isset($cache[$lang])) {
        if (file_exists("locale/$lang/messages.xml")) {
            $cache[$lang] = xmlfile2array("locale/$lang/messages.xml");
        }
    }
    $hash = encode_bad_chars($text);
    if (isset($cache[$lang][$hash])) {
        return $cache[$lang][$hash];
    }
    return $text;
}
