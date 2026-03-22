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

/**
 * About endpoint.
 *
 * Exposes framework metadata, license information,
 * third-party libraries and legal text as JSON.
 */

require_once 'php/lib/license.php';
$about = [
    'about' => get_name_version_revision(),
    'copyright' => get_copyright(),
    ...get_license(),
    'header' => get_header(),
    ...get_libraries(),
    'legal' => get_legal(),
];

$path = array_slice(get_data('rest'), 1);
if (count($path)) {
    $about = __array_getnode($path, $about);
}

output_handler_json($about);
