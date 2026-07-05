#!/bin/bash

#  ____        _ _    ___  ____  _  _
# / ___|  __ _| | |_ / _ \/ ___|| || |
# \___ \ / _` | | __| | | \___ \| || |_
#  ___) | (_| | | |_| |_| |___) |__   _|
# |____/ \__,_|_|\__|\___/|____/   |_|
#
# SaltOS: Framework to develop Rich Internet Applications
# Copyright (c) 2007-2026 Josep Sanz Campderrós
# SPDX-License-Identifier: MIT

set -e

# Relative path definitions based on the project structure
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
INPUT_DIR="$BASE_DIR/../atkinson/fonts"
MAKEFONT_PHP="$BASE_DIR/vendor/setasign/fpdf/makefont/makefont.php"
FONT_OUTPUT_DIR="$BASE_DIR/vendor/setasign/fpdf/font"

# Basic safety and dependency checks
if [ ! -f "$MAKEFONT_PHP" ]; then
    echo "ERROR: makefont.php script not found at $MAKEFONT_PHP" >&2
    exit 1
fi

if [ ! -d "$FONT_OUTPUT_DIR" ]; then
    echo "ERROR: Target output directory not found at $FONT_OUTPUT_DIR" >&2
    exit 1
fi

# Scan for .ttf files using nullglob to prevent errors if the directory is empty
shopt -s nullglob
files=("$INPUT_DIR"/*.ttf)
shopt -u nullglob

if [ ${#files[@]} -eq 0 ]; then
    echo "ERROR: No .ttf font files found in $INPUT_DIR" >&2
    exit 1
fi

echo ">>> Converting Atkinson fonts to FPDF format (JSON)..."

# Change directory to the font output path so generated files are created there directly
cd "$FONT_OUTPUT_DIR"

for font_path in "${files[@]}"; do
    font_name=$(basename "$font_path")

    echo -n "Processing $font_name... "

    # Invoke the native FPDF CLI utility using its absolute path
    if php "$MAKEFONT_PHP" "$font_path" cp1252 true true > /dev/null 2>&1; then
        echo "+++ OK"
    else
        echo "--- ERROR" >&2
    fi
done

echo "Process completed."
