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
// phpcs:disable Generic.Files.LineLength

use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class test_cli_customers extends TestCase
{
    #[DependsExternal('test_cli_tokens1', 'test_authtoken')]
    #[testdox('create action')]
    public function test_create(array $json): array
    {
        $json2 = test_cli_helper("app/customers/create", "", $json["token"]);
        $this->assertArrayHasKey("layout", $json2);
        $this->assertArrayNotHasKey("data", $json2);
        return $json;
    }

    #[Depends('test_create')]
    #[testdox('insert action')]
    public function test_insert(array $json): array
    {
        $json2 = test_cli_helper("insert/customers", [
            "data" => [
                "nombre" => "The SaltOS project",
                "cif" => "12345678X",
                "nombre_poblacion" => "Barcelona",
                "nombre_codpostal" => "08001",
            ],
        ], $json["token"]);
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey("created_id", $json2);
        return [
            "token" => $json["token"],
            "created_id" => $json2["created_id"],
        ];
    }

    #[Depends('test_insert')]
    #[testdox('list action')]
    public function test_list(array $json): array
    {
        $search = "The SaltOS project 12345678X";
        $json2 = test_cli_helper("list/customers/table", [
            "search" => $search,
        ], $json["token"]);
        $this->assertTrue(count($json2["data"]) >= 1);
        $this->assertSame($json2["search"], $search);
        return [
            "token" => $json["token"],
            "created_id" => $json["created_id"],
        ];
    }

    #[Depends('test_list')]
    #[testdox('view action')]
    public function test_view(array $json): array
    {
        $id = $json["created_id"];
        $json2 = test_cli_helper("app/customers/view/$id", "", $json["token"]);
        $this->assertArrayHasKey("layout", $json2);
        $this->assertArrayHasKey("data", $json2);
        return [
            "token" => $json["token"],
            "created_id" => $json["created_id"],
        ];
    }

    #[Depends('test_view')]
    #[testdox('edit action')]
    public function test_edit(array $json): array
    {
        $id = $json["created_id"];
        $json2 = test_cli_helper("app/customers/edit/$id", "", $json["token"]);
        $this->assertArrayHasKey("layout", $json2);
        $this->assertArrayHasKey("data", $json2);
        return [
            "token" => $json["token"],
            "created_id" => $json["created_id"],
        ];
    }

    #[Depends('test_edit')]
    #[testdox('upgrade action')]
    public function test_update(array $json): array
    {
        $id = $json["created_id"];
        $json2 = test_cli_helper("update/customers/$id", [
            "data" => [
                "nombre" => "The SaltOS project v2",
                "cif" => "12345678Z",
            ],
        ], $json["token"]);
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey("updated_id", $json2);
        return [
            "token" => $json["token"],
            "updated_id" => $json2["updated_id"],
        ];
    }

    #[Depends('test_update')]
    #[testdox('delete action')]
    public function test_delete(array $json): void
    {
        $id = $json["updated_id"];
        $json2 = test_cli_helper("delete/customers/$id", "", $json["token"]);
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey("deleted_id", $json2);
    }
}
