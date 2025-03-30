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
        $files = glob("locale/$lang/notfound.pdf");
        if (isset($files[0])) {
            $files[0] = 'api/' . $files[0];
        }
    }
    if (!count($files)) {
        $files = glob('locale/*/notfound.pdf');
        if (isset($files[0])) {
            $files[0] = 'api/' . $files[0];
        }
    }
    if (!count($files)) {
        show_php_error(['phperror' => 'Help not found']);
    }
    return $files[0];
}
