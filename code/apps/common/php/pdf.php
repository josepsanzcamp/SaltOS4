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
 * PDF helper file
 */

/**
 * Build the expected PDF template file path for the given app.
 *
 * Given an application ID, this function resolves the folder name
 * using `detect_app_folder($app)` and returns the full relative path
 * to its associated PDF XML definition file, expected at:
 *
 *     apps/<folder>/xml/<app>_pdf.xml
 *
 * This path is used to define the PDF layout when exporting records
 * from the app.
 *
 * @app => The application code (e.g., 'invoices', 'quotes')
 *
 * Return the relative path to the PDF XML file
 */
function detect_pdf_file($app)
{
    $dir = detect_app_folder($app);
    $pdf = "apps/$dir/xml/{$app}_pdf.xml";
    return $pdf;
}

/**
 * Check if the PDF layout file exists for the given app.
 *
 * This function uses `detect_pdf_file()` to resolve the expected
 * path of the XML file used to generate PDF output for a given app,
 * and returns whether the file actually exists.
 *
 * @app => The application code (e.g., 'invoices', 'quotes')
 *
 * Return true if the PDF file exists, false otherwise
 */
function exists_pdf_file($app)
{
    return file_exists(detect_pdf_file($app));
}
