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
 * Test common
 *
 * This test performs some tests to validate the correctness
 * of the common functions
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
require_once 'php/lib/version.php';
require_once 'apps/common/php/pdf.php';
require_once 'apps/common/php/default.php';

/**
 * Main class of this unit test
 */
final class test_common extends TestCase
{
    #[testdox('common functions')]
    /**
     * common test
     *
     * This test performs some tests to validate the correctness
     * of the common functions
     */
    public function test_common(): void
    {
        $name = execute_query('SELECT name FROM app_customers WHERE id=100');

        $json = test_cli_helper('app/customers/update/100', ['name' => ''], '', '', 'admin');
        $this->assertSame($json['status'], 'ok');

        $json = test_cli_helper('app/customers/update/100', ['name' => $name], '', '', 'admin');
        $this->assertSame($json['status'], 'ok');

        $json = test_cli_helper('app/customers/view/version/100', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertStringContainsString('jspreadsheet', sprintr($json));

        $json = test_cli_helper('app/customers/view/log/100', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertStringContainsString('jspreadsheet', sprintr($json));

        $this->assertSame(del_version('customers', 100), 1);
        $this->assertSame(del_version('customers', 100), 1);

        $this->assertSame('apps/sales/xml/invoices_pdf.xml', detect_pdf_file('invoices'));
        $this->assertTrue(exists_pdf_file('invoices'));

        $this->assertSame('apps/crm/xml/customers_pdf.xml', detect_pdf_file('customers'));
        $this->assertFalse(exists_pdf_file('customers'));
    }

    #[testdox('make_app_file functions')]
    /**
     * make_app_file test
     *
     * This test performs some tests to validate the correctness of the
     * make_app_file function, used to convert a quick app yaml spec into
     * its equivalent xml file
     */
    public function test_make_app_file(): void
    {
        // meetings.yaml uses dropdown: true, to cover the explicit true branch
        $data = yaml_parse_file('apps/crm/xml/meetings.yaml');
        $this->assertSame(true, $data['dropdown']);

        // Add a multiselect field, to cover the multiselect branch
        $data['form'][] = ['extra_multi', 'multiselect', 'Extra Multi'];
        $data['select'][] = ['extra_multi', 'app_customers', 'name'];

        // Add a newline and a hrline field, to cover both branches
        $data['form'][] = ['nl1', 'newline', ''];
        $data['form'][] = ['hr1', 'hrline', ''];

        // Add an overload file, to cover the overload branch
        $data['overload'] = 'apps/common/xml/navbar.xml';

        $array = make_app_file($data);
        $this->assertIsArray($array);
        $this->assertSame('true', $array['list#1']['value']['layout']['value']['row#1']['value']['table']['#attr']['dropdown']);
        $this->assertArrayHasKey('navbar', $array);

        // Cover the auto detection of the dropdown attr
        $data2 = yaml_parse_file('apps/crm/xml/meetings.yaml');
        unset($data2['dropdown']);
        $array2 = make_app_file($data2);
        $this->assertSame('true', $array2['list#1']['value']['layout']['value']['row#1']['value']['table']['#attr']['dropdown']);
    }
}
