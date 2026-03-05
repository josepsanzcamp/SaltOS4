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
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Help feature
 *
 * This file contains the help functions used by SaltOS
 */

/**
 * Detect Help File
 *
 * This function is intended to return the name of the pdf file used as help
 * for the app and lang, they use 4 checks to search the correct file that must
 * to returns, the first in to search in the app folder for the specified lang,
 * otherwise search for some othe lang, and if no app file is found, then the
 * same process is used for the notfound.pdf file.
 *
 * @app  => the application to search
 * @lang => the prefered lang to search
 */
function detect_help_file($app, $lang)
{
    $dir = detect_app_folder($app);
    $files = glob("apps/$dir/locale/$lang/$app.pdf");
    if (!count($files)) {
        $files = glob("apps/$dir/locale/*/$app.pdf");
    }
    if (!count($files)) {
        $dir = 'common';
        $app = 'notfound';
        $files = glob("apps/$dir/locale/$lang/$app.pdf");
    }
    if (!count($files)) {
        $files = glob("apps/$dir/locale/*/$app.pdf");
    }
    if (!count($files)) {
        show_php_error(['phperror' => 'Help not found']);
    }
    return $files[0];
}
