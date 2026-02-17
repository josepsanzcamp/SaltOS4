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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Yaml helper module
 *
 * This file provide the functions provided by the php-yaml package, intended
 * to be used by setups that can not install this package.
 */

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
        require_once 'lib/yaml/vendor/autoload.php';
        return Symfony\Component\Yaml\Yaml::parse($yaml);
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
        require_once 'lib/yaml/vendor/autoload.php';
        return Symfony\Component\Yaml\Yaml::parseFile($filename);
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
        require_once 'lib/yaml/vendor/autoload.php';
        return Symfony\Component\Yaml\Yaml::dump($data, $inline, $indent);
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
        require_once 'lib/yaml/vendor/autoload.php';
        file_put_contents($filename, Symfony\Component\Yaml\Yaml::dump($data, $inline, $indent));
        chmod_protected($filename, 0666);
    }
}
