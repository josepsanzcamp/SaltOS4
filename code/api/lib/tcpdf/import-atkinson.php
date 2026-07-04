<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Atkinson Font Importer Utility
 *
 * This script automates the conversion and registration of Atkinson Hyperlegible
 * TrueType fonts (.ttf) into the tc-lib-pdf native format for use within SaltOS.
 */

// Ensure this points to the correct autoload file where tc-lib-pdf is installed
require_once __DIR__ . '/vendor/autoload.php';

// Path to the directory containing your raw Atkinson .ttf files
$input_dir = __DIR__ . '/../atkinson/fonts/';

// Target output directory where tc-lib-pdf looks for available fonts
$output_dir = __DIR__ . '/vendor/tecnickcom/tc-lib-pdf-font/target/fonts/atkinson/';

if (!is_dir($output_dir)) {
    mkdir($output_dir, 0755, true);
}

// Scan the directory for TrueType Font files
$files = glob($input_dir . '*.ttf');

if (empty($files)) {
    fwrite(STDERR, "ERROR: No .ttf files found in $input_dir\n");
    exit(1);
}

echo ">>> Converting Atkinson fonts to tc-lib-pdf format...\n";

foreach ($files as $font_path) {
    try {
        // Direct invocation of the native library font importer object
        $import = new \Com\Tecnick\Pdf\Font\Import(
            realpath($font_path),
            $output_dir,
            '', // Font type (empty string triggers auto-detection)
            ''  // Encoding (empty string defaults to native TrueType / Unicode)
        );
        echo "+++ OK: " . basename($font_path) . " successfully added as -> " . $import->getFontName() . "\n";
    } catch (\Exception $e) {
        fwrite(STDERR, "--- ERROR processing " . basename($font_path) . ": " . $e->getMessage() . "\n");
    }
}

echo "Process completed.\n";
