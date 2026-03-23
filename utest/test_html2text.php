<?php

/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test html2text library
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
final class test_html2text extends TestCase
{
    #[testdox('html2text function')]
    /**
     * html2text
     *
     * This function checks the correctness of the html2text method provided by the
     * soundasleep/html2text library.
     */
    public function test_html2text(): void
    {
        $html = 'The SaltOS project<br/><a href="https://www.saltos.org">www.saltos.org</a>';
        $text = "The SaltOS project\nwww.saltos.org";
        $this->assertSame(html2text($html), $text);

        $html = 'The SaltOS project<br/><a href="https://www.saltos.org">saltos.org</a>';
        $text = "The SaltOS project\n[saltos.org](https://www.saltos.org)";
        $this->assertSame(html2text($html), $text);

        $html = 'The SaltOS project<br/><a href="https://www.saltos.org">The SaltOS project</a>';
        $text = "The SaltOS project\n[The SaltOS project](https://www.saltos.org)";
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
        $text = 'Hello SaltOS';
        $this->assertSame(html2text($html), $text);

        // Case 4: Incomplete HTML (unclosed link)
        $html = '<a href="https://saltos.org">SaltOS';
        $text = '[SaltOS](https://saltos.org)';
        $this->assertSame(html2text($html), $text);

        // Case 5: HTML list
        $html = '<ul><li>One</li><li>Two</li></ul>';
        $text = "- One\n- Two";
        $this->assertSame(html2text($html), $text);

        $html = '<ol><li>One</li><li>Two</li></ol>';
        $text = "- One\n- Two";
        $this->assertSame(html2text($html), $text);

        // Case 6: Invalid or unusual UTF-8 character (may trigger warnings without libxml handling)
        $html = "Hola\x01 Mundo<br/>SaltOS";
        $text = "Hola Mundo\nSaltOS";
        $this->assertSame(html2text($html), $text);
    }
}
