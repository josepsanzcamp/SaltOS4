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
        ['extension', 'xml', 'error', 'php-xml'], // core module
        ['extension', 'gd', 'error', 'php-gd'], // core module
        ['extension', 'mbstring', 'error', 'php-mbstring'], // core module
        ['extension', 'bcmath', 'error', 'php-bcmath'], // tcpdf library
        ['extension', 'yaml', 'warning', 'php-yaml'], // core module
        ['program', 'timeout', 'warning', 'coreutils'], // core module
        ['program', 'git', 'warning', 'git'], // core module
        ['class', 'pdo', 'warning', 'php-pdo'], // database driver
        ['class', 'mysqli', 'warning', 'php-mysql'], // database driver
        ['class', 'sqlite3', 'warning', 'php-sqlite3'], // database driver
        ['function', 'gzencode', 'warning', 'php-zlib'], // output_handler function
        ['function', 'gzdeflate', 'warning', 'php-zlib'], // output_handler function
        ['function', 'zstd_compress', 'warning', 'php-zstd'], // output_handler function
        ['function', 'brotli_compress', 'warning', 'php-brotli'], // output_handler function
        ['extension', 'curl', 'warning', 'php-curl'], // url_get_contents function
        ['program', 'xlsxio_xlsx2csv', 'warning', 'libxlsxio'], // import module
        ['program', 'soffice', 'warning', 'libreoffice'], // unoconv module
        ['program', 'pdftotext', 'warning', 'poppler-utils'], // unoconv module
        ['program', 'convert', 'warning', 'imagemagick'], // unoconv module
        ['program', 'tesseract', 'warning', 'tesseract-ocr'], // unoconv module
        ['program', 'pdftoppm', 'warning', 'poppler-utils'], // unoconv module
        ['extension', 'mailparse', 'warning', 'php-mailparse'], // emails module
        ['extension', 'imap', 'warning', 'php-imap'], // emails module
        ['program', 'chromium', 'warning', 'chromium'], // emails module
        ['program', 'pdfunite', 'warning', 'poppler-utils'], // emails module
        ['program', 'certutil', 'warning', 'libnss3-tools'], // nssdb module
        ['program', 'openssl', 'warning', 'openssl'], // nssdb module
        ['program', 'pk12util', 'warning', 'libnss3-tools'], // nssdb module
        ['program', 'pdfsig', 'warning', 'poppler-utils'], // nssdb module
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
            case 'program':
                $bool = check_commands($name, 0);
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
        $dir = dirname($file);
        if (substr($dir, -4, 4) === '.old') {
            continue;
        }
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
                if ($key === 'php') {
                    if (!Composer\Semver\Semver::satisfies(PHP_VERSION, $val)) {
                        $result[] = [
                            'error' => "$name requires $val",
                            'details' => "Try to upgrade your php or downgrade the $name package",
                        ];
                    }
                }
                if (substr($key, 0, 4) === 'ext-') {
                    $ext = substr($key, 4);
                    if (!extension_loaded($ext)) {
                        $result[] = [
                            'error' => "$name requires extension $ext",
                            'details' => "Try to install the $ext extension",
                        ];
                        continue;
                    }
                    if ($val === '*') {
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
