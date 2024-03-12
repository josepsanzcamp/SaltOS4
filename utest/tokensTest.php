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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class tokensTest extends TestCase
{
    public function test_authtoken(): array
    {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?authtoken", [
            "body" => json_encode([
                "user" => "admin",
                "pass" => "admin",
            ]),
            "method" => "post",
            "headers" => [
                "Content-Type" => "application/json",
            ],
        ]);
        $json = json_decode($response["body"], true);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 4);
        $this->assertArrayHasKey("token", $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    public function test_checktoken(array $json): array
    {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?checktoken", [
            "headers" => [
                "token" => $json["token"],
            ],
        ]);
        $json = json_decode($response["body"], true);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 5);
        $this->assertArrayHasKey("token", $json);
        return $json;
    }

    #[Depends('test_checktoken')]
    public function test_deauthtoken(array $json): void
    {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?deauthtoken", [
            "headers" => [
                "token" => $json["token"],
            ],
        ]);
        $json = json_decode($response["body"], true);
        $this->assertSame($json["status"], "ok");
        $this->assertSame(count($json), 1);
    }
}
