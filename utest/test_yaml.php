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

        foreach ($files as $file) {
            $array1 = yaml_parse_file($file);
            $array2 = Symfony\Component\Yaml\Yaml::parseFile($file);
            $this->assertSame($array1, $array2);

            $array1 = yaml_parse(yaml_emit($array1));
            $array2 = Symfony\Component\Yaml\Yaml::parse(Symfony\Component\Yaml\Yaml::dump($array2));
            $this->assertSame($array1, $array2);
        }

        $time0 = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            foreach ($files as $file) {
                $array1 = yaml_parse_file($file);
            }
        }

        $time1 = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            foreach ($files as $file) {
                $array2 = Symfony\Component\Yaml\Yaml::parseFile($file);
            }
        }

        $time2 = microtime(true);

        $time2 = $time2 - $time1;
        $time1 = $time1 - $time0;

        //~ print_r([
            //~ 'time1' => sprintf('%f', $time1),
            //~ 'time2' => sprintf('%f', $time2),
        //~ ]);

        $this->assertTrue($time1 < $time2);
    }
}
