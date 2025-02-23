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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Generic.Files.LineLength

/**
 * Test TOML
 *
 * This test performs some tests to validate the correctness
 * of the toml related functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'php/lib/toml.php';

/**
 * Main class of this unit test
 */
final class test_toml extends TestCase
{
    #[testdox('TOML functions')]
    /**
     * TOML test
     *
     * This function performs some tests to validate the correctness
     * of the toml related functions
     */
    public function test_toml(): void
    {
        $files = ['../../utest/files/example.toml'];
        require_once 'lib/toml/vendor/autoload.php';

        global $deprecated;
        $deprecated = false;
        set_error_handler(function ($type, $message, $file, $line) {
            if (words_exists('deprecated', $message)) {
                global $deprecated;
                $deprecated = true;
                error_clear_last();
                return true;
            }
            __error_handler($type, $message, $file, $line);
        });

        // @phpstan-ignore method.impossibleType
        $this->assertFalse($deprecated);

        foreach ($files as $file) {
            $array1 = toml_parse_file($file);
            $array2 = toml_decode(file_get_contents($file), true);
            $this->assertEquals($array1, $array2);

            $array1 = toml_parse(toml_emit($array1));
            $array2 = toml_decode(toml_encode($array2), true);
            $this->assertEquals($array1, $array2);
        }

        restore_error_handler();

        // @phpstan-ignore method.impossibleType
        $this->assertTrue($deprecated);
    }
}
