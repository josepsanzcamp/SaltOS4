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
 * Test addlog
 *
 * This test performs some tests to validate the correctness
 * of the addlog functions
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
require_once "lib/utestlib.php";

/**
 * Main class of this unit test
 */
final class test_addlog extends TestCase
{
    #[testdox('addlog functions')]
    /**
     * addlog test
     *
     * This test performs some tests to validate the correctness
     * of the addlog functions
     */
    public function test_addlog(): void
    {
        $file = "data/logs/saltos.log";
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper("addlog", [], "");
        $this->assertArrayHasKey("error", $json);
        $this->assertFileDoesNotExist($file);

        $json = test_web_helper("addlog", [
            "msg" => "hola mundo",
        ], "");
        $this->assertArrayHasKey("status", $json);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 1);
        $this->assertFileExists($file);
        $this->assertTrue(words_exists("hola mundo", file_get_contents($file)));
        unlink($file);
    }
}
