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
 * Test sales
 *
 * This test performs some tests to validate the correctness of the sales
 * functions. The invoices app functions (unmake_matrix_data and
 * set_proforma_invoice) are exercised through the web interface, since these
 * functions are loaded on demand by the XML router and can't be required
 * directly (their name clashes with other apps that define a function with
 * the same name, such as apps/crm/php/quotes.php or apps/users/php/matrix.php)
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
require_once 'apps/sales/php/echarts.php';

/**
 * Main class of this unit test
 */
final class test_sales extends TestCase
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
    #[testdox('invoices functions')]
    /**
     * invoices test
     *
     * This test performs some tests to validate the correctness of the
     * unmake_matrix_data and set_proforma_invoice functions, exercised
     * through the insert and update rest actions of the invoices app
     */
    public function test_invoices(array $json): void
    {
        $token = $json['token'];

        // Setup the master tax used by lines and taxes
        $query = make_insert_query('app_taxes', [
            'name' => 'IVA test sales',
            'value' => 21,
            'active' => 1,
        ]);
        db_query($query);
        $tax_id = execute_query('SELECT MAX(id) FROM app_taxes');

        // Insert a new invoice submitting a real matrix (lines, taxes, totals),
        // this is the only way to make check_real_matrix succeed and reach the
        // real diff logic of unmake_matrix_data, instead of the early return
        // used by simpler tests that submit associative rows
        $json2 = test_web_helper('app/invoices/insert', [
            'customer_name' => 'Test sales customer',
            'lines' => [
                ['Item A', '1', '100', '0', '21', '100'],
                ['Item B', '1', '50', '0', '21', '50'],
                ['Item to be dropped', '1', '30', '0', '21', '30'],
            ],
            'taxes' => [
                ['IVA test sales', '150', '31.5'],
            ],
            'totals' => [
                [180, 37.8, 217.8],
            ],
        ], $token, '');
        $this->assertSame('ok', $json2['status']);
        $invoice_id = $json2['created_id'];

        // set_proforma_invoice must have generated a proforma_code and date,
        // and must have filled in the company_* fields, but must not have
        // touched anything related to closing/paying the invoice
        $invoice = execute_query('SELECT * FROM app_invoices WHERE id = ?', [$invoice_id]);
        $this->assertMatchesRegularExpression('/^PF\d{4}-\d+$/', $invoice['proforma_code']);
        $this->assertSame(current_date(), $invoice['proforma_date']);
        $this->assertNull($invoice['invoice_code']);
        $company = execute_query('SELECT * FROM app_company WHERE id = ?', [1]);
        $this->assertSame($company['name'], $invoice['company_name']);
        $this->assertSame($company['code'], $invoice['company_code']);

        // unmake_matrix_data must have inserted the three submitted lines and
        // the one submitted tax, since the invoice didn't have any of them yet
        $lines_array = execute_query_array(
            'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(3, $lines_array);
        $this->assertSame('Item A', $lines_array[0]['description']);
        $this->assertSame('Item B', $lines_array[1]['description']);

        $taxes_array = execute_query_array(
            'SELECT * FROM app_invoices_taxes WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(1, $taxes_array);

        // Update the invoice: line 0 unchanged, line 1 changed, the 3rd line is
        // dropped since it's not part of the submitted matrix, and the tax is
        // submitted unchanged too
        $json3 = test_web_helper("app/invoices/update/$invoice_id", [
            'proforma_code' => $invoice['proforma_code'],
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
            'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(2, $lines_array2);
        $ids2 = array_column($lines_array2, 'id');
        $this->assertContains($lines_array[0]['id'], $ids2);
        $this->assertContains($lines_array[1]['id'], $ids2);
        $this->assertNotContains($lines_array[2]['id'], $ids2);
        $changed_line =
            current(array_filter($lines_array2, fn($row) => $row['id'] === $lines_array[1]['id']));
        $this->assertSame('Item C', $changed_line['description']);

        // Since the invoice already had a proforma_code, it must not be regenerated
        $invoice2 = execute_query('SELECT * FROM app_invoices WHERE id = ?', [$invoice_id]);
        $this->assertSame($invoice['proforma_code'], $invoice2['proforma_code']);
        $this->assertSame($invoice['proforma_date'], $invoice2['proforma_date']);

        // Closing and marking the invoice as paid, without touching lines/taxes,
        // must generate the invoice_code, invoice_date, due_date and paid_date,
        // and must fall back to the stored total since no total is submitted
        $json4 = test_web_helper("app/invoices/update/$invoice_id", [
            'proforma_code' => $invoice['proforma_code'],
            'is_closed' => true,
            'is_paid' => true,
        ], $token, '');
        $this->assertSame('ok', $json4['status']);

        $invoice3 = execute_query('SELECT * FROM app_invoices WHERE id = ?', [$invoice_id]);
        $this->assertMatchesRegularExpression('/^F\d{4}-\d+$/', $invoice3['invoice_code']);
        $this->assertSame(current_date(), $invoice3['invoice_date']);
        $this->assertSame(current_date(), $invoice3['due_date']);
        $this->assertSame(current_date(), $invoice3['paid_date']);
        $this->assertEquals($invoice3['total'], $invoice3['paid']);

        // The two lines from the previous update must still be there untouched
        $lines_array3 = execute_query_array(
            'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(2, $lines_array3);

        $query = "DELETE FROM app_invoices WHERE id = $invoice_id";
        db_query($query);
        $query = "DELETE FROM app_invoices_lines WHERE invoice_id = $invoice_id";
        db_query($query);
        $query = "DELETE FROM app_invoices_taxes WHERE invoice_id = $invoice_id";
        db_query($query);
        $query = "DELETE FROM app_taxes WHERE id = $tax_id";
        db_query($query);
    }

    #[testdox('echarts functions')]
    /**
     * echarts test
     *
     * This test performs some tests to validate the correctness
     * of the echarts functions
     */
    public function test_echarts(): void
    {
        // Use a date far away from any real demo data, to avoid mixing sums
        $day = '2099-09-09';

        $query = make_insert_query('app_invoices', [
            'customer_name' => 'echarts cust',
            'invoice_date' => $day,
            'paid_date' => '2099-09-19',
            'total' => 100,
            'is_closed' => 1,
            'is_paid' => 1,
        ]);
        db_query($query);
        $id1 = execute_query('SELECT MAX(id) FROM app_invoices');

        $query = make_insert_query('app_invoices', [
            'customer_name' => 'echarts cust',
            'invoice_date' => $day,
            'total' => 300,
            'is_closed' => 1,
            'is_paid' => 0,
        ]);
        db_query($query);
        $id2 = execute_query('SELECT MAX(id) FROM app_invoices');

        $query = make_insert_query('app_invoices', [
            'customer_name' => 'echarts cust open',
            'total' => 0,
            'is_closed' => 0,
            'is_paid' => 0,
        ]);
        db_query($query);
        $id3 = execute_query('SELECT MAX(id) FROM app_invoices');

        // The expected values are recomputed live, so the assertions stay
        // correct regardless of whatever demo data already exists
        $expected_total = execute_query(
            'SELECT SUM(total) FROM app_invoices WHERE is_closed = 1 AND invoice_date = ?',
            [$day]
        );
        $expected_avg = execute_query(
            'SELECT AVG(total) FROM app_invoices WHERE is_closed = 1 AND invoice_date = ?',
            [$day]
        );

        $result = compute_invoice_total_by_day();
        $this->assertArrayHasKey('xAxis', $result);
        $idx = array_search($day, $result['xAxis']['data'], true);
        $this->assertNotFalse($idx);
        $this->assertEquals((float) $expected_total, $result['series'][0]['data'][$idx]);

        $result = compute_invoice_avg_by_day();
        $idx = array_search($day, $result['xAxis']['data'], true);
        $this->assertNotFalse($idx);
        $this->assertEquals((float) $expected_avg, $result['series'][0]['data'][$idx]);

        $result = compute_invoice_avg_days_to_pay();
        $this->assertArrayHasKey('series', $result);

        // A very large total guarantees the customer shows up in the top 5
        $query = make_insert_query('app_invoices', [
            'customer_name' => 'echarts top cust',
            'total' => 9000000,
            'is_closed' => 1,
        ]);
        db_query($query);
        $id4 = execute_query('SELECT MAX(id) FROM app_invoices');

        $result = compute_top5_customers_by_total();
        $this->assertContains('echarts top cust', $result['xAxis']['data']);
        $this->assertContains(9000000.0, $result['series'][0]['data']);

        $result = compute_invoice_paid_vs_pending();
        $names = array_column($result['series'][0]['data'], 'name');
        $this->assertContains('Paid', $names);
        $this->assertContains('Unpaid', $names);

        $result = compute_invoice_open_vs_closed();
        $names = array_column($result['series'][0]['data'], 'name');
        $this->assertContains('Open', $names);
        $this->assertContains('Closed', $names);

        $query = "DELETE FROM app_invoices WHERE id IN ($id1, $id2, $id3, $id4)";
        db_query($query);
    }
}
