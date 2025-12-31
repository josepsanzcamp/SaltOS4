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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Deprecated helper module
 *
 * This file add some functions used by SaltOS that are deprecated in new versions of PHP
 */

// Function xml_parser_free() is deprecated since 8.5, as it has no effect since PHP 8.0
function xml_parser_free_deprecated($parser)
{
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        xml_parser_free($parser);
    }
}

// Function curl_close() is deprecated since 8.5, as it has no effect since PHP 8.0
function curl_close_deprecated($handle)
{
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        curl_close($handle);
    }
}

// Function imagedestroy() is deprecated since 8.5, as it has no effect since PHP 8.0
function imagedestroy_deprecated($image)
{
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        imagedestroy($image);
    }
}

// Function finfo_close() is deprecated since 8.5, as finfo objects are freed automatically
function finfo_close_deprecated($finfo)
{
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        finfo_close($finfo);
    }
}
