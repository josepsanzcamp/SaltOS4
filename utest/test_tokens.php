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
 * Test tokens
 *
 * This test performs some tests to validate the correctness
 * of the token generator and the token format checker
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
final class test_tokens extends TestCase
{
    #[testdox('tokens functions')]
    /**
     * Tokens test
     *
     * This function performs some tests to validate the correctness
     * of the token generator and the token format checker
     */
    public function test_tokens(): void
    {
        // Check generator
        $token = get_unique_token();
        $this->assertSame(strlen($token), 36);

        // Check valid format
        $token2 = check_token_format($token);
        $this->assertSame($token2, $token);

        // Check when parts is bad
        $token2 = check_token_format(substr($token, 0, 10));
        $this->assertSame($token2, null);

        // Check when length is bad
        $token2 = check_token_format(substr($token, 0, -1));
        $this->assertSame($token2, null);

        // Check when not hex is found
        $token2 = check_token_format(substr($token, 0, -1) . 'x');
        $this->assertSame($token2, null);

        // Check lowercase patterns tokens
        $token = 'gggggggg-gggg-gggg-gggg-gggggggggggg';
        $token2 = check_token_format($token);
        $this->assertSame($token2, null);

        // Check generic patterns tokens
        $token = '00000000-0000-0000-0000-000000000000';
        $token2 = check_token_format($token);
        $this->assertSame($token2, $token);

        // Check generic patterns tokens
        $token = '99999999-9999-9999-9999-999999999999';
        $token2 = check_token_format($token);
        $this->assertSame($token2, $token);

        // Check lowercase patterns tokens
        $token = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $token2 = check_token_format($token);
        $this->assertSame($token2, $token);

        // Check uppercase patterns tokens
        $token = 'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF';
        $token2 = check_token_format($token);
        $this->assertSame($token2, $token);

        // Check when token is not an string
        $token2 = check_token_format(null);
        $this->assertSame($token2, null);

        // Check when token is not an string
        $token2 = check_token_format(false);
        $this->assertSame($token2, null);

        // Check when token is not an string
        $token2 = check_token_format([]);
        $this->assertSame($token2, null);

        // Check for token collision
        $tokens1 = [];
        for ($i = 0; $i < 1000000; $i++) {
            $tokens1[] = get_unique_token();
        }
        $tokens2 = array_flip($tokens1);
        $this->assertSame(count($tokens1), count($tokens2));
    }
}
