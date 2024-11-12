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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test json
 *
 * This test performs some tests to validate the correctness
 * of the json feature
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
final class test_json extends TestCase
{
    #[testdox('json functions')]
    /**
     * json test
     *
     * This test performs some tests to validate the correctness
     * of the json feature
     */
    public function test_json(): void
    {
        $array = [
            'text' => 'josep sanz',
            'int' => 123,
            'float' => 123.456,
            'true' => true,
            'false' => false,
            'null' => null,
        ];
        $json = json_encode($array, JSON_PRETTY_PRINT);
        $buffer = json_colorize($json);

        $this->assertStringContainsString("\e[32m\"text\"\e[0m", $buffer);
        $this->assertStringContainsString("\e[34m\"josep sanz\"\e[0m", $buffer);
        $this->assertStringContainsString("\e[35m123\e[0m", $buffer);
        $this->assertStringContainsString("\e[35m123.456\e[0m", $buffer);
        $this->assertStringContainsString("\e[31mtrue\e[0m", $buffer);
        $this->assertStringContainsString("\e[31mfalse\e[0m", $buffer);
        $this->assertStringContainsString("\e[31mnull\e[0m", $buffer);
    }
}
