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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Yaml helper module
 *
 * This file provide the functions provided by the php-yaml package, intended
 * to be used by setups that can not install this package.
 *
 * Layout: the actual polyfill logic lives in the __yaml_*_helper()
 * functions below (never guarded: they can't collide with anything the
 * extension provides). Every yaml_* function is just a thin delegate to
 * its same-named helper, grouped at the end of this file and guarded
 * with function_exists() so it's only defined when the real extension
 * is missing. That split exists so utest/test_yaml.php can call the
 * helper directly and exercise this polyfill's actual code even on
 * machines that do have the native extension installed, where the
 * plain yaml_* names are already taken by the extension and can't be
 * used to reach this file's code.
 */

/**
 * Yaml Parse Helper
 *
 * Parse a YAML string into a PHP array.
 *
 * @yaml => The YAML string.
 */
function __yaml_parse_helper(string $yaml)
{
    require_once 'lib/yaml/vendor/autoload.php';
    return Symfony\Component\Yaml\Yaml::parse($yaml);
}

/**
 * Yaml Parse File Helper
 *
 * Parse a YAML file into a PHP array
 *
 * @filename => The path to the YAML file
 */
function __yaml_parse_file_helper(string $filename)
{
    require_once 'lib/yaml/vendor/autoload.php';
    return Symfony\Component\Yaml\Yaml::parseFile($filename);
}

/**
 * Yaml Emit Helper
 *
 * Emit an array as a YAML string.
 *
 * @data   => The data to convert to YAML.
 * @inline => The level at which to start inlining YAML (default: 2).
 * @indent => The number of spaces to use for indentation (default: 4).
 */
function __yaml_emit_helper(array $data, int $inline = 2, int $indent = 4)
{
    require_once 'lib/yaml/vendor/autoload.php';
    return Symfony\Component\Yaml\Yaml::dump($data, $inline, $indent);
}

/**
 * Yaml Emit File Helper
 *
 * Emit an array as a YAML file
 *
 * @filename => The path to save the YAML file
 * @data     => The data to convert to YAML
 * @inline   => The level at which to start inlining YAML (default: 2)
 * @indent   => The number of spaces to use for indentation (default: 4)
 */
function __yaml_emit_file_helper(string $filename, array $data, int $inline = 2, int $indent = 4)
{
    require_once 'lib/yaml/vendor/autoload.php';
    file_put_contents($filename, Symfony\Component\Yaml\Yaml::dump($data, $inline, $indent));
    chmod_protected($filename, 0666);
}

/**
 * Thin yaml_* delegates: only defined when the real extension is
 * missing, in which case each one just forwards to its same-named
 * __yaml_*_helper() above.
 */

if (!function_exists('yaml_parse')) {
    /**
     * Yaml Parse
     */
    function yaml_parse(string $yaml)
    {
        return __yaml_parse_helper($yaml);
    }
}

if (!function_exists('yaml_parse_file')) {
    /**
     * Yaml Parse File
     */
    function yaml_parse_file(string $filename)
    {
        return __yaml_parse_file_helper($filename);
    }
}

if (!function_exists('yaml_emit')) {
    /**
     * Yaml Emit
     */
    function yaml_emit(array $data, int $inline = 2, int $indent = 4)
    {
        return __yaml_emit_helper($data, $inline, $indent);
    }
}

if (!function_exists('yaml_emit_file')) {
    /**
     * Yaml Emit File
     */
    function yaml_emit_file(string $filename, array $data, int $inline = 2, int $indent = 4)
    {
        return __yaml_emit_file_helper($filename, $data, $inline, $indent);
    }
}
