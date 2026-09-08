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

echo ">>> Fixing tcpdf_config.php...\n";

$file = 'vendor/tecnickcom/tcpdf/config/tcpdf_config.php';

$buffer = file_get_contents($file);

$buffer = str_replace('helvetica', 'atkinsonhyperlegiblenext', $buffer);
$buffer = str_replace('courier', 'atkinsonhyperlegiblemono', $buffer);

file_put_contents($file, $buffer);

echo "Process completed.\n";
