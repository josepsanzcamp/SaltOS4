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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Generic.Files.LineLength

/**
 * Test YAML
 *
 * This test performs some tests to validate the correctness
 * of the yaml related functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Main class of this unit test
 */
final class test_yaml extends TestCase
{
    #[testdox('YAML functions')]
    /**
     * YAML test
     *
     * This function performs some tests to validate the correctness
     * of the yaml related functions
     */
    public function test_yaml(): void
    {
        $files = glob('apps/*/xml/*.yaml');
        require_once 'lib/yaml/vendor/autoload.php';

        //~ $this->assertEquals(yaml_parse('nada: nada: nada'), null);
        //~ $this->assertEquals(yaml_parse_file('xml/config.php'), null);

        foreach ($files as $file) {
            $array1 = yaml_parse_file($file);
            $array2 = Symfony\Component\Yaml\Yaml::parseFile($file);
            $this->assertSame($array1, $array2);

            $array1 = yaml_parse(yaml_emit($array1));
            $array2 = Symfony\Component\Yaml\Yaml::parse(Symfony\Component\Yaml\Yaml::dump($array2));
            $this->assertSame($array1, $array2);

            $cache1 = get_cache_file(['array1' => $file]);
            yaml_emit_file($cache1, $array1);

            $cache2 = get_cache_file(['array2' => $file]);
            file_put_contents($cache2, yaml_emit($array1));
            chmod_protected($cache2, 0666);

            $this->assertFileEquals($cache1, $cache2);
        }
    }
}
