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
 * Test gdlib
 *
 * This test performs some tests to validate the correctness
 * of the gdlib feature
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
require_once 'php/lib/gdlib.php';

/**
 * Main class of this unit test
 */
final class test_gdlib extends TestCase
{
    #[testdox('gdlib functions')]
    /**
     * gdlib test
     *
     * This test performs some tests to validate the correctness
     * of the gdlib feature
     */
    public function test_gdlib(): void
    {
        $width = compute_width('hello world', 12);
        $this->assertIsInt($width);
        $this->assertGreaterThan(70, $width);
        $this->assertLessThan(80, $width);

        // Check for error images
        $buffer = image_resize('nada', 1000);
        $this->assertSame($buffer, 'nada');

        // Check for images of size = 200 * 200
        $im = imagecreatetruecolor(200, 200);
        ob_start();
        imagejpeg($im);
        $data = ob_get_clean();
        imagedestroy($im);

        $buffer = image_resize($data, 1000);
        $this->assertSame($buffer, $data);

        // Check for images of size = 2000 * 2000
        $im = imagecreatetruecolor(2000, 2000);
        ob_start();
        imagejpeg($im);
        $data = ob_get_clean();
        imagedestroy($im);

        $buffer = image_resize($data, 1000);
        $this->assertNotSame($buffer, $data);

        $im = @imagecreatefromstring($buffer);
        $width = imagesx($im);
        $height = imagesy($im);
        imagedestroy($im);

        $this->assertSame($width, 1000);
        $this->assertSame($height, 1000);

        // Check for images of size = 200 * 2000
        $im = imagecreatetruecolor(200, 2000);
        ob_start();
        imagejpeg($im);
        $data = ob_get_clean();
        imagedestroy($im);

        $buffer = image_resize($data, 1000);
        $this->assertNotSame($buffer, $data);

        $im = @imagecreatefromstring($buffer);
        $width = imagesx($im);
        $height = imagesy($im);
        imagedestroy($im);

        $this->assertSame($width, 100);
        $this->assertSame($height, 1000);

        // Check for images of size = 2000 * 200
        $im = imagecreatetruecolor(2000, 200);
        ob_start();
        imagejpeg($im);
        $data = ob_get_clean();
        imagedestroy($im);

        $buffer = image_resize($data, 1000);
        $this->assertNotSame($buffer, $data);

        $im = @imagecreatefromstring($buffer);
        $width = imagesx($im);
        $height = imagesy($im);
        imagedestroy($im);

        $this->assertSame($width, 1000);
        $this->assertSame($height, 100);
    }
}
