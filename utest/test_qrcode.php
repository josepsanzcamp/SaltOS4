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
 * Test qrcode
 *
 * This test performs some tests to validate the correctness
 * of the qrcode feature
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
final class test_qrcode extends TestCase
{
    #[testdox('qrcode functions')]
    /**
     * qrcode test
     *
     * This test performs some tests to validate the correctness
     * of the qrcode feature
     */
    public function test_qrcode(): void
    {
        $img = __qrcode_image("12345", 6, 10, "L");
        $this->assertStringContainsString("PNG image data", get_mime($img));
        $gd = @imagecreatefromstring($img);
        $this->assertInstanceOf(GdImage::class, $gd);
        imagedestroy($gd);

        $json = test_web_helper("qrcode", [], "");
        $this->assertArrayHasKey("error", $json);

        $json2 = test_web_helper("authtoken", [
            "user" => "admin",
            "pass" => "admin",
        ], "");
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 4);
        $this->assertArrayHasKey("token", $json2);

        $json = test_web_helper("qrcode", [], $json2["token"]);
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("qrcode", [
            "msg" => "nada",
            "format" => "nada",
        ], $json2["token"]);
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("qrcode", [
            "msg" => "nada",
            "format" => "png",
        ], $json2["token"]);
        $this->assertStringContainsString("PNG image data", get_mime($json));

        $json = test_web_helper("qrcode", [
            "msg" => "nada",
            "format" => "json",
        ], $json2["token"]);
        $this->assertIsArray($json);
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey("msg", $json);
        $this->assertArrayHasKey("image", $json);

        $json = test_web_helper("qrcode", [
            "msg" => str_repeat("nada", 1000),
            "format" => "png",
        ], $json2["token"]);
        $this->assertArrayHasKey("error", $json);
    }
}
