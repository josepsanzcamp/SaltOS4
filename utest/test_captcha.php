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
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\DependsExternal;

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
        $this->assertSame(__captcha_isprime(2), true);
        $this->assertSame(__captcha_isprime(3), true);
        $this->assertSame(__captcha_isprime(4), false);
        $this->assertSame(__captcha_isprime(5), true);
        $this->assertSame(__captcha_isprime(6), false);
        $this->assertSame(__captcha_isprime(7), true);
        $this->assertSame(__captcha_isprime(8), false);
        $this->assertSame(__captcha_isprime(9), false);
        $this->assertSame(__captcha_isprime(10), false);
        $this->assertSame(__captcha_isprime(11), true);
        $this->assertSame(__captcha_isprime(12), false);
        $this->assertSame(__captcha_isprime(13), true);

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
    }
}
