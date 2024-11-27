<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * Compatibility helper module
 *
 * This file add some functions used by SaltOS that can not be found in all allowed versions of PHP
 */

if (!function_exists('array_key_last')) {
    /**
     * Array Key Last
     *
     * This function appear in PHP 7.3, and for previous version SaltOS
     * uses this code
     *
     * @array => the array where you want to obtain the last key
     *
     * Notes:
     *
     * Code copied from the follow web:
     * https://www.php.net/manual/es/function.array-key-last.php#124007
     */
    function array_key_last(array $array)
    {
        if (!empty($array)) {
            return key(array_slice($array, -1, 1, true));
        }
    }
}

if (!function_exists('array_key_first')) {
    /**
     * Array Key First
     *
     * This function appear in PHP 7.3, and for previous version SaltOS
     * uses this code
     *
     * @array => the array where you want to obtain the first key
     *
     * Notes:
     *
     * Code copied from the follow web:
     * https://www.php.net/manual/es/function.array-key-last.php#124007
     */
    function array_key_first(array $arr)
    {
        foreach ($arr as $key => $val) {
            return $key;
        }
    }
}

if (!function_exists('yaml_parse')) {
    /**
     * Yaml Parse
     *
     * Parse a YAML string into a PHP array.
     *
     * @yaml => The YAML string.
     */
    function yaml_parse(string $yaml)
    {
        try {
            require_once 'lib/yaml/vendor/autoload.php';
            return Symfony\Component\Yaml\Yaml::parse($yaml);
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('yaml_parse_file')) {
    /**
     * Yaml Parse File
     *
     * Parse a YAML file into a PHP array
     *
     * @filename => The path to the YAML file
     */
    function yaml_parse_file(string $filename)
    {
        if (!file_exists($filename)) {
            return null;
        }
        return yaml_parse(file_get_contents($filename));
    }
}

if (!function_exists('yaml_emit')) {
    /**
     * Yaml Emit
     *
     * Emit an array as a YAML string.
     *
     * @data   => The data to convert to YAML.
     * @inline => The level at which to start inlining YAML (default: 2).
     * @indent => The number of spaces to use for indentation (default: 4).
     */
    function yaml_emit(array $data, int $inline = 2, int $indent = 4)
    {
        try {
            require_once 'lib/yaml/vendor/autoload.php';
            return Symfony\Component\Yaml\Yaml::dump($data, $inline, $indent, Yaml::DUMP_OBJECT_AS_MAP);
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('yaml_emit_file')) {
    /**
     * Yaml Emit File
     *
     * Emit an array as a YAML file
     *
     * @filename => The path to save the YAML file
     * @data     => The data to convert to YAML
     * @inline   => The level at which to start inlining YAML (default: 2)
     * @indent   => The number of spaces to use for indentation (default: 4)
     */
    function yaml_emit_file(string $filename, array $data, int $inline = 2, int $indent = 4)
    {
        $yaml = yaml_emit($data, $inline, $indent);
        if ($yaml === null) {
            return false;
        }
        file_put_contents($filename, $yaml);
        chmod_protected($filename, 0666);
        return true;
    }
}
