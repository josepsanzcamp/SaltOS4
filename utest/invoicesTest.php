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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class invoicesTest extends TestCase
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
    public function test_create(array $json): array
    {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?app/invoices/create", [
            "headers" => [
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertArrayHasKey("layout", $json2);
        $this->assertArrayNotHasKey("data", $json2);
        return $json;
    }

    #[Depends('test_create')]
    public function test_insert(array $json): array
    {
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?insert/invoices", [
            "body" => json_encode([
                "data" => [
                    "nombre" => "The SaltOS project",
                    "direccion" => "X",
                    "nombre_pais" => "Y",
                    "nombre_provincia" => "Z",
                    "nombre_poblacion" => "Barcelona",
                    "nombre_codpostal" => "08001",
                    "cif" => "12345678X",
                    "iva" => "21",
                    "irpf" => "15",
                    "detail" => [
                        [
                            "concepto" => "ABC",
                            "unidades" => "1",
                            "precio" => "2",
                            "descuento" => "3",
                        ], [
                            "concepto" => "DEF",
                            "unidades" => "4",
                            "precio" => "5",
                            "descuento" => "6",
                        ],
                    ],
                ],
            ]),
            "method" => "post",
            "headers" => [
                "Content-Type" => "application/json",
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey("created_id", $json2);
        return [
            "token" => $json["token"],
            "created_id" => $json2["created_id"],
        ];
    }

    #[Depends('test_insert')]
    public function test_list(array $json): array
    {
        $search = "The SaltOS project 12345678X";
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?list/invoices/table", [
            "body" => json_encode([
                "search" => $search,
            ]),
            "method" => "post",
            "headers" => [
                "Content-Type" => "application/json",
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertTrue(count($json2["data"]) >= 1);
        $this->assertSame($json2["search"], $search);
        return [
            "token" => $json["token"],
            "created_id" => $json["created_id"],
        ];
    }

    #[Depends('test_list')]
    public function test_view(array $json): array
    {
        $id = $json["created_id"];
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?app/invoices/view/$id", [
            "headers" => [
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertArrayHasKey("layout", $json2);
        $this->assertArrayHasKey("data", $json2);
        $this->assertArrayHasKey("data#1", $json2);
        $this->assertArrayHasKey("data#2", $json2);
        return [
            "token" => $json["token"],
            "created_id" => $json["created_id"],
        ];
    }

    #[Depends('test_view')]
    public function test_edit(array $json): array
    {
        $id = $json["created_id"];
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?app/invoices/edit/$id", [
            "headers" => [
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertArrayHasKey("layout", $json2);
        $this->assertArrayHasKey("data", $json2);
        $this->assertArrayHasKey("data#1", $json2);
        $this->assertArrayHasKey("data#2", $json2);
        return [
            "token" => $json["token"],
            "created_id" => $json["created_id"],
        ];
    }

    #[Depends('test_edit')]
    public function test_update(array $json): array
    {
        $id = $json["created_id"];
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?update/invoices/$id", [
            "body" => json_encode([
                "data" => [
                    "nombre" => "The SaltOS project v2",
                    "cif" => "12345678Z",
                    "detail" => [
                        [
                            "concepto" => "GHI",
                            "unidades" => "7",
                            "precio" => "8",
                            "descuento" => "9",
                        ],
                    ],
                ],
            ]),
            "method" => "post",
            "headers" => [
                "Content-Type" => "application/json",
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey("updated_id", $json2);
        return [
            "token" => $json["token"],
            "updated_id" => $json2["updated_id"],
        ];
    }

    #[Depends('test_update')]
    public function test_delete(array $json): void
    {
        $id = $json["updated_id"];
        $response = __url_get_contents("https://127.0.0.1/saltos/code4/api/index.php?delete/invoices/$id", [
            "headers" => [
                "token" => $json["token"],
            ],
        ]);
        $json2 = json_decode($response["body"], true);
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 2);
        $this->assertArrayHasKey("deleted_id", $json2);
    }
}
