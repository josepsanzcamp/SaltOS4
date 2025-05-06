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
 * System helper module
 *
 * This fie contains useful functions related to system checks, allow to detect dependencies not
 * installed on the system, or misconfigurations on the SaltOS installation
 */

/**
 * Check System
 *
 * This function checks the system to detect if all knowed dependencies are found in the system, to do it,
 * defines an array with the type (class or function), the name and some extra info for the error message
 * that is triggered if the dependency is not satisfied
 */
function check_system()
{
    $result = [];
    // PACKAGE CHECKS
    $items = [
        ['extension', 'xml', 'error', 'php-xml'],
        ['extension', 'gd', 'error', 'php-gd'],
        ['extension', 'mbstring', 'error', 'php-mbstring'],
        ['extension', 'curl', 'error', 'php-curl'],
        ['extension', 'yaml', 'warning', 'php-yaml'],
        ['class', 'pdo', 'warning', 'php-pdo'],
        ['class', 'mysqli', 'warning', 'php-mysql'],
        ['class', 'sqlite3', 'warning', 'php-sqlite3'],
        ['function', 'gzencode', 'warning', 'php-zlib'],
        ['function', 'gzdeflate', 'warning', 'php-zlib'],
        ['function', 'zstd_compress', 'warning', 'php-zstd'],
        ['function', 'brotli_compress', 'warning', 'php-brotli'],
    ];
    foreach ($items as $item) {
        [$type, $name, $trigger, $package] = $item;
        $bool = false;
        switch ($type) {
            case 'extension':
                $bool = extension_loaded($name);
                break;
            case 'class':
                $bool = class_exists($name);
                break;
            case 'function':
                $bool = function_exists($name);
                break;
        }
        if (!$bool) {
            // @codeCoverageIgnoreStart
            $type = ucfirst($type);
            $name = ucfirst($name);
            $result[] = [
                $trigger => "$type $name not found",
                'details' => "Try to install $package package",
            ];
            // @codeCoverageIgnoreEnd
        }
    }
    return $result;
}

 /**
 * Check Directories
 *
 * Check all directories of the data directory to validate that the process can write inside it
 */
function check_directories()
{
    $result = [];
    // DIRECTORIES CKECKS
    $dirs = array_merge(glob('data/*'), get_config('dirs'));
    foreach ($dirs as $dir) {
        if (!file_exists($dir) || !is_dir($dir) || !is_writable($dir)) {
            $dir = str_replace(getcwd() . '/', '', $dir);
            $result[$dir] = [
                'error' => "$dir not writable",
                'details' => "Try to set permissions to do the $dir directory writable",
            ];
        }
    }
    $result = array_values($result);
    return $result;
}

 /**
 * Exec Check System
 *
 * This function executes the check system function and trigger an error if needed
 */
function exec_check_system()
{
    $output = check_system();
    foreach ($output as $key => $val) {
        // @codeCoverageIgnoreStart
        if (isset($val['error'])) {
            show_php_error([
                'phperror' => $val['error'],
                'details' => $val['details'],
            ]);
        }
        // @codeCoverageIgnoreEnd
    }
}

/**
 * Check Composer packages requirements
 *
 * Checks PHP version and PHP extension requirements defined in composer.lock files of local packages,
 * including extension version constraints when available.
 *
 * This function scans all composer.lock files under lib directory and verifies if:
 * - The current PHP version satisfies "require['php']" constraints.
 * - The required PHP extensions (marked as "require['ext-xxx']") are loaded.
 * - The extension versions (when specified and detectable) satisfy the given constraints.
 *
 * Requires the composer/semver library (loaded from lib/semver/vendor/autoload.php).
 */
function check_composer()
{
    require_once 'lib/semver/vendor/autoload.php';
    $result = [];
    $files = glob('lib/*/composer.lock');
    foreach ($files as $file) {
        $json = file_get_contents($file);
        $array = json_decode($json, true);
        if (!isset($array['packages'])) {
            continue;
        }
        foreach ($array['packages'] as $package) {
            if (!isset($package['require'])) {
                continue;
            }
            $name = $package['name'];
            foreach ($package['require'] as $key => $val) {
                if ($key == 'php') {
                    if (!Composer\Semver\Semver::satisfies(PHP_VERSION, $val)) {
                        $result[] = [
                            'error' => "$name requires $val",
                            'details' => "Try to upgrade your php or downgrade the $name package",
                        ];
                    }
                }
                if (substr($key, 0, 4) == 'ext-') {
                    $ext = substr($key, 4);
                    if (!extension_loaded($ext)) {
                        $result[] = [
                            'error' => "$name requires extension $ext",
                            'details' => "Try to install the $ext extension",
                        ];
                        continue;
                    }
                    if ($val == '*') {
                        continue;
                    }
                    $ver = phpversion($ext);
                    if ($ver !== false) {
                        if (!Composer\Semver\Semver::satisfies($ver, $val)) {
                            $result[] = [
                                'error' => "$name requires $ext $val (current: $ver)",
                                'details' => "Upgrade your $ext extension or downgrade the $name package",
                            ];
                        }
                    }
                }
            }
        }
    }
    return $result;
}
