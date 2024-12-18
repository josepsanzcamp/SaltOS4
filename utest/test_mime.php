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
 * Test mime
 *
 * This test performs some tests to validate the correctness
 * of the mime functions
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
require_once __ROOT__ . 'php/lib/html.php';

/**
 * Main class of this unit test
 */
final class test_mime extends TestCase
{
    #[testdox('mime functions')]
    /**
     * mime test
     *
     * This test performs some tests to validate the correctness
     * of the mime functions
     */
    public function test_mime(): void
    {
        $this->assertSame(saltos_content_type('pepe.png'), 'image/png');

        $files = glob('xml/config.xml');
        $this->assertSame(saltos_content_type($files[0]), 'text/xml');

        $this->assertSame(saltos_content_type0('image/png'), 'image');
        $this->assertSame(saltos_content_type1('image/png'), 'png');

        $this->assertSame(saltos_content_type0('image/png/nada'), '');
        $this->assertSame(saltos_content_type1('image/png/nada'), '');

        $files = glob('xml/config.xml');
        $buffer = file_get_contents($files[0]);
        $this->assertSame(saltos_content_type_from_string($buffer), 'text/xml');

        $array = mime_extract(__GIF_IMAGE__);
        $this->assertCount(2, $array);
        $this->assertSame($array['type'], 'image/gif');
        $this->assertSame($array['data'], base64_decode('R0lGODdhAQABAIABAOns7wAAACwAAAAAAQABAAACAkQBADs='));

        $array = mime_extract('nada');
        $this->assertCount(2, $array);
        $this->assertSame(implode('', $array), '');

        $name = mime2name('image/gif');
        $this->assertSame($name, 'image.gif');

        $name = mime2name('application/octet-stream');
        $this->assertSame($name, 'application.bin');

        $name = mime2name('image/svg+xml');
        $this->assertSame($name, 'image.svg');
    }
}
