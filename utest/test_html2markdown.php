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
 * Test html2markdown library
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
final class test_html2markdown extends TestCase
{
    #[testdox('html2text function')]
    /**
     * html2text
     *
     * This function checks the correctness of the html2text method provided by the
     * html2markdown library.
     */
    public function test_html2text(): void
    {
        $html = 'The SaltOS project<br/><a href="https://www.saltos.org">www.saltos.org</a>';
        $text = "The SaltOS project\n[www.saltos.org](https://www.saltos.org)";
        $this->assertSame(html2text($html), $text);

        $this->assertSame(html2text(' '), '');
        $this->assertSame(html2text(true), '1');
        $this->assertSame(html2text(''), '');
        $this->assertSame(html2text(false), '');
        $this->assertSame(html2text(null), '');

        // Case 2: Malformed entity
        $html = 'Tom & Jerry<br/>Cartoon';
        $text = "Tom & Jerry\nCartoon";
        $this->assertSame(html2text($html), $text);

        // Case 3: Unclosed HTML tags
        $html = '<div><p><b>Hello SaltOS</div>';
        $text = '**Hello SaltOS**';
        $this->assertSame(html2text($html), $text);

        // Case 4: Incomplete HTML (unclosed link)
        $html = '<a href="https://saltos.org">SaltOS';
        $text = '[SaltOS](https://saltos.org)';
        $this->assertSame(html2text($html), $text);

        // Case 5: HTML list
        $html = '<ul><li>One</li><li>Two</li></ul>';
        $text = "- One\n- Two";
        $this->assertSame(html2text($html), $text);

        // Case 6: Invalid or unusual UTF-8 character (may trigger warnings without libxml handling)
        $html = "Hola\x01 Mundo<br/>SaltOS";
        $text = "Hola Mundo\nSaltOS";
        $this->assertSame(html2text($html), $text);
    }
}
