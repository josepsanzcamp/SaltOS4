<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
