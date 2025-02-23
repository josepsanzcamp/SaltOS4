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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Toml helper module
 *
 * This file provide the functions provided by the php-toml package, intended
 * to be used by setups that can not install this package.
 */

/**
 * Toml Parse
 *
 * Parse a TOML string into a PHP array.
 *
 * @toml => The TOML string.
 */
function toml_parse(string $toml, bool $asArray = true, bool $asFloat = false)
{
    try {
        require_once 'lib/toml/vendor/autoload.php';
        return toml_decode($toml, $asArray, $asFloat);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Toml Parse File
 *
 * Parse a TOML file into a PHP array
 *
 * @filename => The path to the TOML file
 */
function toml_parse_file(string $filename, bool $asArray = true, bool $asFloat = false)
{
    try {
        require_once 'lib/toml/vendor/autoload.php';
        return toml_decode(file_get_contents($filename), $asArray, $asFloat);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Toml Emit
 *
 * Emit an array as a TOML string.
 *
 * @data => The data to convert to TOML.
 */
function toml_emit(array $data)
{
    try {
        require_once 'lib/toml/vendor/autoload.php';
        return toml_encode($data);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Toml Emit File
 *
 * Emit an array as a TOML file
 *
 * @filename => The path to save the TOML file
 * @data     => The data to convert to TOML
 */
function toml_emit_file(string $filename, array $data)
{
    try {
        require_once 'lib/toml/vendor/autoload.php';
        file_put_contents($filename, toml_encode($data));
        chmod_protected($filename, 0666);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
