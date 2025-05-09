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
 * Test roundcube library
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Main class of this unit test
 */
final class test_roundcube extends TestCase
{
    #[testdox('html2text function')]
    /**
     * html2text
     *
     * This function checks the correctness of the html2text method provided by the
     * roundcube library.
     */
    public function test_html2text(): void
    {
        $html = 'The SaltOS project<br/><a href="https://www.saltos.org">www.saltos.org</a>';
        $text = "The SaltOS project\nwww.saltos.org [1]\n\nLinks:\n------\n[1] https://www.saltos.org\n";
        $this->assertSame(html2text($html), $text);

        $this->assertSame(html2text(' '), ' ');
        $this->assertSame(html2text(true), '1');
        $this->assertSame(html2text(''), '');
        $this->assertSame(html2text(false), '');
        $this->assertSame(html2text(null), '');
    }
}
