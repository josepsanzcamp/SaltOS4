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
 * Test crm
 *
 * This test performs some tests to validate the correctness of the quotes
 * app functions (unmake_matrix_data and set_quote), using the web interface
 * since these functions are loaded on demand by the XML router and can't be
 * required directly (their name clashes with other apps that define a
 * function with the same name, such as apps/users/php/matrix.php)
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

/**
 * Main class of this unit test
 */
final class test_crm extends TestCase
{
    #[testdox('authtoken action')]
    /**
     * Authtoken
     *
     * This function execute the authtoken rest request, and must to get the
     * json with the valid token to continue in the nexts unit tests
     */
    public function test_authtoken(): array
    {
        $json = test_web_helper('auth/login', [
            'user' => 'admin',
            'pass' => 'admin',
        ], '', '');
        $this->assertSame($json['status'], 'ok');
        $this->assertArrayHasKey('token', $json);
        return $json;
    }

    #[Depends('test_authtoken')]
    #[testdox('quotes functions')]
    /**
     * quotes test
     *
     * This test performs some tests to validate the correctness of the
     * unmake_matrix_data and set_quote functions, exercised through the
     * insert and update rest actions of the quotes app
     */
    public function test_quotes(array $json): void
    {
        $token = $json['token'];

        // Setup the master tax used by lines and taxes
        $query = make_insert_query('app_taxes', [
            'name' => 'IVA test crm',
            'value' => 21,
            'active' => 1,
        ]);
        db_query($query);
        $tax_id = execute_query('SELECT MAX(id) FROM app_taxes');

        // Insert a new quote submitting a real matrix (lines, taxes, totals),
        // this is the only way to make check_real_matrix succeed and reach
        // the real diff logic of unmake_matrix_data, instead of the early
        // return used by simpler tests that submit associative rows
        $json2 = test_web_helper('app/quotes/insert', [
            'customer_name' => 'Test crm customer',
            'lines' => [
                ['Item A', '1', '100', '0', '21', '100'],
                ['Item B', '1', '50', '0', '21', '50'],
                ['Item to be dropped', '1', '30', '0', '21', '30'],
            ],
            'taxes' => [
                ['IVA test crm', '150', '31.5'],
            ],
            'totals' => [
                [180, 37.8, 217.8],
            ],
        ], $token, '');
        $this->assertSame('ok', $json2['status']);
        $quote_id = $json2['created_id'];

        // set_quote must have generated a code, a date and a valid_until,
        // and must have filled in the company_* fields
        $quote = execute_query('SELECT * FROM app_quotes WHERE id = ?', [$quote_id]);
        $this->assertMatchesRegularExpression('/^Q\d{4}-\d+$/', $quote['code']);
        $this->assertSame(current_date(), $quote['date']);
        $this->assertSame(current_date(), $quote['valid_until']);
        $company = execute_query('SELECT * FROM app_company WHERE id = ?', [1]);
        $this->assertSame($company['name'], $quote['company_name']);
        $this->assertSame($company['code'], $quote['company_code']);

        // unmake_matrix_data must have inserted the three submitted lines and
        // the one submitted tax, since the quote didn't have any of them yet
        $lines_array = execute_query_array(
            'SELECT * FROM app_quotes_lines WHERE quote_id = ? ORDER BY id ASC',
            [$quote_id]
        );
        $this->assertCount(3, $lines_array);
        $this->assertSame('Item A', $lines_array[0]['description']);
        $this->assertSame('Item B', $lines_array[1]['description']);

        $taxes_array = execute_query_array(
            'SELECT * FROM app_quotes_taxes WHERE quote_id = ? ORDER BY id ASC',
            [$quote_id]
        );
        $this->assertCount(1, $taxes_array);

        // Update the quote: line 0 unchanged, line 1 changed, the 3rd line is
        // dropped since it's not part of the submitted matrix, and the tax is
        // submitted unchanged too
        $json3 = test_web_helper("app/quotes/update/$quote_id", [
            'code' => $quote['code'],
            'lines' => [
                [
                    $lines_array[0]['description'],
                    $lines_array[0]['quantity'],
                    $lines_array[0]['price'],
                    $lines_array[0]['discount'],
                    $lines_array[0]['tax_value'],
                    $lines_array[0]['total'],
                ],
                ['Item C', '3', '10', '0', '21', '30'],
            ],
            'taxes' => [
                [
                    $taxes_array[0]['tax_name'],
                    $taxes_array[0]['base'],
                    $taxes_array[0]['tax'],
                ],
            ],
            'totals' => [
                [180, 37.8, 217.8],
            ],
        ], $token, '');
        $this->assertSame('ok', $json3['status']);

        $lines_array2 = execute_query_array(
            'SELECT * FROM app_quotes_lines WHERE quote_id = ? ORDER BY id ASC',
            [$quote_id]
        );
        $this->assertCount(2, $lines_array2);
        $ids2 = array_column($lines_array2, 'id');
        $this->assertContains($lines_array[0]['id'], $ids2);
        $this->assertContains($lines_array[1]['id'], $ids2);
        $this->assertNotContains($lines_array[2]['id'], $ids2);
        $changed_line =
            current(array_filter($lines_array2, fn($row) => $row['id'] === $lines_array[1]['id']));
        $this->assertSame('Item C', $changed_line['description']);

        // Since the quote already had a code, it must not be regenerated
        $quote2 = execute_query('SELECT * FROM app_quotes WHERE id = ?', [$quote_id]);
        $this->assertSame($quote['code'], $quote2['code']);
        $this->assertSame($quote['date'], $quote2['date']);

        $query = "DELETE FROM app_quotes WHERE id = $quote_id";
        db_query($query);
        $query = "DELETE FROM app_quotes_lines WHERE quote_id = $quote_id";
        db_query($query);
        $query = "DELETE FROM app_quotes_taxes WHERE quote_id = $quote_id";
        db_query($query);
        $query = "DELETE FROM app_taxes WHERE id = $tax_id";
        db_query($query);
    }
}
