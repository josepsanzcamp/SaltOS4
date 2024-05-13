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
 * Test captcha
 *
 * This test performs some tests to validate the correctness
 * of the captcha feature
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
require_once "php/lib/captcha.php";

/**
 * Main class of this unit test
 */
final class test_captcha extends TestCase
{
    #[testdox('captcha functions')]
    /**
     * captcha test
     *
     * This test performs some tests to validate the correctness
     * of the captcha feature
     */
    public function test_captcha(): void
    {
        $this->assertSame(__captcha_isprime(1), false);
        $this->assertSame(__captcha_isprime(4), false);
        $this->assertSame(__captcha_isprime(9), false);
        $this->assertSame(__captcha_isprime(25), false);
        $this->assertSame(__captcha_isprime(49), false);
        $this->assertSame(__captcha_isprime(2), true);
        $this->assertSame(__captcha_isprime(121), false);
        $this->assertSame(__captcha_isprime(67), true);
        $this->assertSame(__captcha_isprime(169), false);
        $this->assertSame(__captcha_isprime(149), true);
        $this->assertSame(__captcha_isprime(289), false);
        $this->assertSame(__captcha_isprime(197), true);
        $this->assertSame(__captcha_isprime(361), false);
        $this->assertSame(__captcha_isprime(331), true);
        $this->assertSame(__captcha_isprime(529), false);
        $this->assertSame(__captcha_isprime(401), true);
        $this->assertSame(__captcha_isprime(841), false);
        $this->assertSame(__captcha_isprime(577), true);
        $this->assertSame(__captcha_isprime(961), false);
        $this->assertSame(__captcha_isprime(907), true);

        $img = __captcha_image("12345");
        $this->assertStringContainsString("PNG image data", get_mime($img));
        $gd = @imagecreatefromstring($img);
        $this->assertInstanceOf(GdImage::class, $gd);
        imagedestroy($gd);

        $text = __captcha_make_number(5);
        $this->assertSame(is_numeric($text), true);
        $this->assertSame(strlen($text), 5);

        $text = __captcha_make_math(5);
        $this->assertSame(is_numeric($text), false);
        $this->assertSame(strlen($text), 5);

        $json = test_web_helper("captcha", [], "");
        $this->assertArrayHasKey("error", $json);

        $json2 = test_web_helper("authtoken", [
            "user" => "admin",
            "pass" => "admin",
        ], "");
        $this->assertSame($json2["status"], "ok");
        $this->assertSame(count($json2), 4);
        $this->assertArrayHasKey("token", $json2);

        $json = test_web_helper("captcha", [], $json2["token"]);
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("captcha", [
            "type" => "nada",
            "format" => "nada",
        ], $json2["token"]);
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("captcha", [
            "type" => "number",
            "format" => "nada",
        ], $json2["token"]);
        $this->assertArrayHasKey("error", $json);

        $json = test_web_helper("captcha", [
            "type" => "number",
            "format" => "png",
        ], $json2["token"]);
        $this->assertStringContainsString("PNG image data", get_mime($json));

        $json = test_web_helper("captcha", [
            "type" => "math",
            "format" => "png",
        ], $json2["token"]);
        $this->assertStringContainsString("PNG image data", get_mime($json));

        $json = test_web_helper("captcha", [
            "type" => "number",
            "format" => "json",
        ], $json2["token"]);
        $this->assertIsArray($json);
        $this->assertSame(count($json), 2);
        $this->assertArrayHasKey("code", $json);
        $this->assertArrayHasKey("image", $json);
    }
}
