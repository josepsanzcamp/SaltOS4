<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test help
 *
 * This test performs some tests to validate the correctness
 * of the help functions
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
require_once 'php/lib/help.php';

/**
 * Main class of this unit test
 */
final class test_help extends TestCase
{
    #[testdox('help functions')]
    /**
     * help test
     *
     * This test performs some tests to validate the correctness
     * of the help functions
     */
    public function test_help(): void
    {
        $file = detect_help_file('nada', 'nada');
        $this->assertStringContainsString('notfound.pdf', $file);

        $file = detect_help_file('nada', 'en_US');
        $this->assertStringContainsString('api/locale/en_US/notfound.pdf', $file);

        $file = detect_help_file('nada', 'es_ES');
        $this->assertStringContainsString('api/locale/es_ES/notfound.pdf', $file);

        $file = detect_help_file('nada', 'ca_ES');
        $this->assertStringContainsString('api/locale/ca_ES/notfound.pdf', $file);

        $file = detect_help_file('emails', 'nada');
        $this->assertStringContainsString('emails.pdf', $file);

        $file = detect_help_file('emails', 'en_US');
        $this->assertStringContainsString('apps/emails/locale/en_US/emails.pdf', $file);

        $file = detect_help_file('emails', 'es_ES');
        $this->assertStringContainsString('apps/emails/locale/es_ES/emails.pdf', $file);

        $file = detect_help_file('emails', 'ca_ES');
        $this->assertStringContainsString('apps/emails/locale/ca_ES/emails.pdf', $file);

        $file = detect_help_file('emails_accounts', 'nada');
        $this->assertStringContainsString('emails_accounts.pdf', $file);

        $file = detect_help_file('emails_accounts', 'en_US');
        $this->assertStringContainsString('apps/emails/locale/en_US/emails_accounts.pdf', $file);

        $file = detect_help_file('emails_accounts', 'es_ES');
        $this->assertStringContainsString('apps/emails/locale/es_ES/emails_accounts.pdf', $file);

        $file = detect_help_file('emails_accounts', 'ca_ES');
        $this->assertStringContainsString('apps/emails/locale/ca_ES/emails_accounts.pdf', $file);
    }
}
