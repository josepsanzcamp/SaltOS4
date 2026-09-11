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

    #[Depends('test_authtoken')]
    #[testdox('invoices matrix edge cases')]
    /**
     * invoices matrix edge cases test
     *
     * This test performs some tests to validate the correctness of the
     * unmake_matrix_data function when it receives edge case inputs: an
     * empty payload, a payload missing the taxes/totals keys, a fully
     * blank new row (must be dropped silently), a blank existing row
     * (must become a deletion marker), a leftover row beyond the
     * submitted matrix (must also become a deletion marker) and a new
     * row that is only partially blank (per-field unset branches)
     */
    public function test_invoices_matrix_edge_cases(array $json): void
    {
        $token = $json['token'];

        // Submitting a completely empty payload must hit the early
        // return of unmake_matrix_data
        $json0 = test_web_helper('app/invoices/insert', [], $token, '');
        $this->assertSame('ko', $json0['status']);

        // Submitting a payload with a "lines" matrix but no "taxes" or
        // "totals" keys must hit the check_real_matrix guard early
        // return; since the matrix doesn't get emptied by the guard,
        // set_proforma_invoice still fills in a proforma_code and the
        // main row ends up inserted before the (still raw) lines
        // matrix fails its own field validation, so the stray row
        // must be cleaned up too
        $before_id = execute_query('SELECT MAX(id) FROM app_invoices');
        $json0b = test_web_helper('app/invoices/insert', [
            'lines' => [['Item', '1', '1', '0', '21', '1']],
        ], $token, '');
        $this->assertSame('ko', $json0b['status']);
        $after_id = execute_query('SELECT MAX(id) FROM app_invoices');
        if ($after_id > $before_id) {
            db_query("DELETE FROM app_invoices WHERE id = $after_id");
        }

        // Setup a second master tax used by the extra taxes row below
        $query = make_insert_query('app_taxes', [
            'name' => 'IVA test sales 2',
            'value' => 10,
            'active' => 1,
        ]);
        db_query($query);
        $tax_id2 = execute_query('SELECT MAX(id) FROM app_taxes');

        $query = make_insert_query('app_taxes', [
            'name' => 'IVA test sales 3',
            'value' => 21,
            'active' => 1,
        ]);
        db_query($query);
        $tax_id3 = execute_query('SELECT MAX(id) FROM app_taxes');

        // Insert an invoice with one line and two taxes
        $json1 = test_web_helper('app/invoices/insert', [
            'customer_name' => 'Test sales edge customer',
            'lines' => [
                ['Keep item', '1', '100', '0', '21', '100'],
            ],
            'taxes' => [
                ['IVA test sales 3', '100', '21'],
                ['IVA test sales 2', '50', '5'],
            ],
            'totals' => [
                [150, 26, 176],
            ],
        ], $token, '');
        $this->assertSame('ok', $json1['status']);
        $invoice_id = $json1['created_id'];

        $lines_array = execute_query_array(
            'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $taxes_array = execute_query_array(
            'SELECT * FROM app_invoices_taxes WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(1, $lines_array);
        $this->assertCount(2, $taxes_array);

        $invoice = execute_query('SELECT * FROM app_invoices WHERE id = ?', [$invoice_id]);

        // Update: blank out the existing line (deletion marker), add a
        // fully blank new line (dropped silently), drop the second tax
        // by not resubmitting it (leftover deletion marker), and
        // resubmit the exact same totals as currently stored, to hit
        // the "no change" branches of the totals box
        $json2 = test_web_helper("app/invoices/update/$invoice_id", [
            'proforma_code' => $invoice['proforma_code'],
            'lines' => [
                ['', '', '', '', '', ''],
                ['', '', '', '', '', ''],
            ],
            'taxes' => [
                [
                    $taxes_array[0]['tax_name'],
                    $taxes_array[0]['base'],
                    $taxes_array[0]['tax'],
                ],
            ],
            'totals' => [
                [$invoice['subtotal'], $invoice['tax'], $invoice['total']],
            ],
        ], $token, '');
        $this->assertSame('ok', $json2['status']);

        $lines_array2 = execute_query_array(
            'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(0, $lines_array2);

        $taxes_array2 = execute_query_array(
            'SELECT * FROM app_invoices_taxes WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(1, $taxes_array2);
        $this->assertSame($taxes_array[0]['id'], $taxes_array2[0]['id']);

        // Update again: submit two brand new lines that are only
        // partially blank (one field left non-blank to avoid the
        // fully-blank early drop), to hit the remaining per-field unset
        // branches of the new-line case; blank out the remaining
        // existing tax (deletion marker), add two brand new partially
        // blank taxes (per-field unset branches) and a brand new fully
        // blank tax (dropped silently)
        $json3 = test_web_helper("app/invoices/update/$invoice_id", [
            'proforma_code' => $invoice['proforma_code'],
            'lines' => [
                ['', '', '', '', '', '30'],
                ['New item', '', '', '', '', ''],
            ],
            'taxes' => [
                ['', '', ''],
                ['', '50', '10'],
                ['Unknown tax name', '', ''],
                ['', '', ''],
            ],
            'totals' => [
                [180, 30, 210],
            ],
        ], $token, '');
        $this->assertSame('ok', $json3['status']);

        $lines_array3 = execute_query_array(
            'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(2, $lines_array3);

        $taxes_array3 = execute_query_array(
            'SELECT * FROM app_invoices_taxes WHERE invoice_id = ? ORDER BY id ASC',
            [$invoice_id]
        );
        $this->assertCount(2, $taxes_array3);

        // Close the invoice explicitly once
        $json4 = test_web_helper("app/invoices/update/$invoice_id", [
            'proforma_code' => $invoice['proforma_code'],
            'is_closed' => true,
        ], $token, '');
        $this->assertSame('ok', $json4['status']);

        // Update again without specifying is_closed: since the invoice
        // is already closed in the DB, this must hit the "set
        // is_closed=1 from the DB" fallback branch of
        // set_proforma_invoice; submitting is_paid together with a
        // total and no explicit paid amount must hit the "paid =
        // total" fallback branch too
        $json5 = test_web_helper("app/invoices/update/$invoice_id", [
            'proforma_code' => $invoice['proforma_code'],
            'is_paid' => true,
            'total' => 999,
        ], $token, '');
        $this->assertSame('ok', $json5['status']);

        $invoice2 = execute_query('SELECT * FROM app_invoices WHERE id = ?', [$invoice_id]);
        $this->assertEquals(1, $invoice2['is_closed']);
        $this->assertEquals(999, $invoice2['paid']);

        $query = "DELETE FROM app_invoices WHERE id = $invoice_id";
        db_query($query);
        $query = "DELETE FROM app_invoices_lines WHERE invoice_id = $invoice_id";
        db_query($query);
        $query = "DELETE FROM app_invoices_taxes WHERE invoice_id = $invoice_id";
        db_query($query);
        $query = "DELETE FROM app_taxes WHERE id IN ($tax_id2, $tax_id3)";
        db_query($query);
    }
}
