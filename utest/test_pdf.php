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
 * Test pdf
 *
 * This test performs some tests to validate the correctness
 * of the pdf functions
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
require_once 'php/lib/pdf.php';
require_once 'lib/utestlib.php';

/**
 * Main class of this unit test
 */
final class test_pdf extends TestCase
{
    #[testdox('pdf functions')]
    /**
     * pdf test
     *
     * This test performs some tests to validate the correctness
     * of the pdf functions
     */
    public function test_pdf(): void
    {
        $invoice_id = execute_query('SELECT id FROM app_invoices ORDER BY id DESC LIMIT 1');
        $this->assertTrue(is_numeric($invoice_id));
        $this->assertTrue($invoice_id > 0);

        set_data('rest/0', 'app');
        set_data('rest/1', 'invoices');

        $pdf = pdf('apps/invoices/xml/pdf.xml', [
            'id' => $invoice_id,
        ]);
        $pdf = pdf('apps/invoices/xml/pdf.xml', [
            'id' => $invoice_id,
        ]);
        $this->assertTrue(is_array($pdf));
        $this->assertArrayHasKey('name', $pdf);
        $this->assertArrayHasKey('data', $pdf);

        $email_id = execute_query('SELECT id FROM app_emails ORDER BY id DESC LIMIT 1');
        $this->assertTrue(is_numeric($email_id));
        $this->assertTrue($email_id > 0);

        set_data('rest/0', 'app');
        set_data('rest/1', 'emails');

        $pdf = pdf('apps/emails/xml/pdf.xml', [
            'id' => $email_id,
        ]);
        $pdf = pdf('apps/emails/xml/pdf.xml', [
            'id' => $email_id,
        ]);
        $this->assertTrue(is_array($pdf));
        $this->assertArrayHasKey('name', $pdf);
        $this->assertArrayHasKey('data', $pdf);

        set_data('rest/0', 'app');
        set_data('rest/1', 'dashboard');

        $pdf = pdf('../../utest/files/pdf.xml', [
            'dir' => 'ltr',
        ]);
        $this->assertTrue(is_array($pdf));
        $this->assertArrayHasKey('name', $pdf);
        $this->assertArrayHasKey('data', $pdf);

        $separator = ',';
        $str = '"x","y","z"';
        $limit = 2;
        $array = __pdf_eval_explode($separator, $str, $limit);
        $this->assertSame($array, [
            '"x"',
            '"y","z"',
        ]);

        $this->assertTrue(
            defined('K_TCPDF_THROW_EXCEPTION_ERROR'),
            'K_TCPDF_THROW_EXCEPTION_ERROR not found'
        );
        $this->assertTrue(
            K_TCPDF_THROW_EXCEPTION_ERROR,
            'tcpdf_config.php contains K_TCPDF_THROW_EXCEPTION_ERROR as false instead of true'
        );

        test_external_exec('php/pdf1.php', 'phperror.log', 'array not found');
        test_external_exec('php/pdf2.php', 'phperror.log', 'foreach without query');
        test_external_exec('php/pdf3.php', 'phperror.log', 'file nada not found');
        test_external_exec('php/pdf4.php', 'phperror.log', 'eval pdf tag error nada');
        test_external_exec('php/pdf5.php', 'phperror.log', 'output node not found in template');
    }
}
