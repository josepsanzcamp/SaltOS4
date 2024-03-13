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

use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class test_cli_tokens1 extends TestCase
{
    #[DependsOnClass('test_web_tokens2')]
    #[testdox('authtoken action')]
    public function test_authtoken(): array
    {
        file_put_contents("/tmp/input", json_encode([
            "user" => "admin",
            "pass" => "admin",
        ]));
        $response = ob_passthru("cat /tmp/input | php index.php authtoken");
        $json = json_decode($response, true);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey("token", $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('checktoken action')]
    public function test_checktoken(array $json): array
    {
        $token = $json["token"];
        $response = ob_passthru("TOKEN=$token php index.php checktoken");
        $json = json_decode($response, true);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 5);
        $this->assertArrayHasKey("token", $json);
        return $json;
    }
}
