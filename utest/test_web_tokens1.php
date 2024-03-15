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
 * Test web tokens (first part)
 *
 * This test performs some part of the actions related with the tokens suck
 * as authtoken and checktoken, using the apache sapi interface
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once "lib/weblib.php";

/**
 * Main class of this unit test
 */
final class test_web_tokens1 extends TestCase
{
    #[testdox('authtoken action')]
    /**
     * Authtoken
     *
     * This function execute the authtoken rest request, and must to get the
     * json with the valid token to continue in the nexts unit tests
     */
    public function test_authtoken(): array
    {
        $json = test_web_helper("authtoken", [
            "user" => "admin",
            "pass" => "admin",
        ], "");
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey("token", $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('checktoken action')]
    /**
     * Checktoken
     *
     * This function execute the checktoken rest request, and must to get the
     * json with the ok about the valid token that you are trying to check
     */
    public function test_checktoken(array $json): array
    {
        $json = test_web_helper("checktoken", "", $json["token"]);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 5);
        $this->assertArrayHasKey("token", $json);
        return $json;
    }
}
