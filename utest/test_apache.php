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
 * Test apache
 *
 * This test performs some tests to validate the correctness
 * of the apache configuration
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
final class test_apache extends TestCase
{
    #[testdox('apache configuration')]
    /**
     * addlog test
     *
     * This test performs some tests to validate the correctness of the apache configuration,
     * the main idea is that the path saltos/code4 of the web server must point to the web
     * directory.
     */
    public function test_apache(): void
    {
        $urls = [
            'https://127.0.0.1/saltos/code4/api/apps/',
            'https://127.0.0.1/saltos/code4/api/data/',
            'https://127.0.0.1/saltos/code4/api/data/files',
            'https://127.0.0.1/saltos/code4/api/data/files/config.xml',
            'https://127.0.0.1/saltos/code4/api/data/files/saltos.sqlite',
            'https://127.0.0.1/saltos/code4/api/lib/',
            'https://127.0.0.1/saltos/code4/api/lib/tcpdf/vendor/tecnickcom/tcpdf/examples/',
            'https://127.0.0.1/saltos/code4/api/lib/tcpdf/vendor/tecnickcom/tcpdf/examples/index.php',
            'https://127.0.0.1/saltos/code4/api/lib/browscap/update.php',
            'https://127.0.0.1/saltos/code4/api/xml/',
            'https://127.0.0.1/saltos/code4/api/xml/config.xml',
            'https://127.0.0.1/saltos/code4/apps/',
            'https://127.0.0.1/saltos/code4/lib/',
        ];
        foreach ($urls as $url) {
            $response = __url_get_contents($url);
            $this->assertStringContainsString('403 Forbidden', $response['body']);
            $this->assertStringContainsString('403 Forbidden', array_keys($response['headers'])[0]);
        }

        $url = 'https://127.0.0.1/saltos/code4/api/';
        $response = __url_get_contents($url);

        // expose_php = Off
        $this->assertArrayNotHasKey('x-powered-by', $response['headers']);

        // ServerSignature Off
        // ServerTokens Prod
        $this->assertSame($response['headers']['Server'], 'Apache');
    }
}
