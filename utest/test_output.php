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

/**
 * Test output
 *
 * This test performs some tests to validate the correctness
 * of the output functions
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
require_once 'lib/utestlib.php';

/**
 * Main class of this unit test
 */
final class test_output extends TestCase
{
    #[testdox('output functions')]
    /**
     * output test
     *
     * This test performs some tests to validate the correctness
     * of the output functions
     */
    public function test_output(): void
    {
        test_external_exec('php/output01.php', '', '');
        test_external_exec('php/output02.php', '', '');
        test_external_exec('php/output03.php', '', '');
        test_external_exec('php/output04.php', '', '');
        test_external_exec('php/output05.php', '', '');
        test_external_exec('php/output06.php', '', '');
        test_external_exec('php/output07.php', 'phperror.log', 'file nada not found');
        test_external_exec('php/output08.php', 'phperror.log', 'output_handler requires type parameter');
        test_external_exec('php/output09.php', 'phperror.log', 'output_handler requires cache parameter');
        test_external_exec('php/output10.php', '', '');
        test_external_exec('php/output11.php', '', '');
    }

    #[testdox('colorize functions')]
    /**
     * colorize test
     *
     * This test performs some tests to validate the correctness
     * of the colorize functions
     */
    public function test_colorize(): void
    {
        test_pcov_start();
        $data1 = ob_passthru('php index.php');
        test_pcov_stop(1);
        $data1 = json_decode($data1);
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $data1 = json_encode($data1, $options | JSON_PRETTY_PRINT) . "\n";
        $data1 = json_colorize($data1);

        test_pcov_start();
        $data2 = ob_passthru('unbuffer -p php index.php');
        test_pcov_stop(1);
        $data2 = str_replace("\r\n", "\n", $data2);

        $this->assertSame($data1, $data2);

        $array = [
            'text' => 'josep sanz',
            'int' => 123,
            'float' => 123.456,
            'true' => true,
            'false' => false,
            'null' => null,
            'exp' => 3e-5,
        ];
        $json = json_encode($array, JSON_PRETTY_PRINT);
        $buffer = json_colorize($json);

        extract(__COLORS_MAP__);

        $this->assertStringContainsString("{$green}\"text\"{$reset}", $buffer);
        $this->assertStringContainsString("{$blue}\"josep sanz\"{$reset}", $buffer);
        $this->assertStringContainsString("{$magenta}123{$reset}", $buffer);
        $this->assertStringContainsString("{$magenta}123.456{$reset}", $buffer);
        $this->assertStringContainsString("{$red}true{$reset}", $buffer);
        $this->assertStringContainsString("{$red}false{$reset}", $buffer);
        $this->assertStringContainsString("{$red}null{$reset}", $buffer);
        $this->assertStringContainsString("{$magenta}0.00003{$reset}", $buffer);
    }
}
