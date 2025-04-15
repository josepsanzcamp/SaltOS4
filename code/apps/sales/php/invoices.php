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

/**
 * Reverses the process of `make_matrix_data()` for the `invoices` app.
 *
 * This function takes a previously generated matrix-style array of invoice data
 * (grouped as lines, taxes, and totals), compares it with the current values
 * in the database, and builds a minimal diff-like array that includes only
 * the fields that have changed, been removed, or added.
 */

/**
 * unmake_matrix_data
 *
 * Reverse the matrix-encoded invoice data to a diff-style data array.
 *
 * @json       => The matrix-encoded invoice data (from make_matrix_data)
 * @invoice_id => The invoice ID used to retrieve the original data
 *
 * Return a diff array representing only the differences between the provided
 * data and the current state of the database.
 */
function unmake_matrix_data($json, $invoice_id)
{
    $lines = $json['lines'] ?? [];
    $taxes = $json['taxes'] ?? [];
    $totals = $json['totals'] ?? [];
    unset($json['lines']);
    unset($json['taxes']);
    unset($json['totals']);

    // Lines array
    $query = 'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC';
    $lines_array = execute_query_array($query, [$invoice_id]);

    // Taxes master
    $query = 'SELECT id, name, value FROM app_taxes WHERE active=1';
    $taxes_master = execute_query_array($query);
    $taxes_master_by_name = array_column($taxes_master, null, 'name');
    $taxes_master_by_value = array_column($taxes_master, null, 'value');

    // Taxes array
    $query = 'SELECT * FROM app_invoices_taxes WHERE invoice_id = ? ORDER BY id ASC';
    $taxes_array = execute_query_array($query, [$invoice_id]);

    // Totals array
    $query = 'SELECT subtotal, tax, total FROM app_invoices WHERE id = ?';
    $totals_array = execute_query_array($query, [$invoice_id]);

    // Concepts box
    $count = count($lines);
    foreach ($lines as $key => $val) {
        $concept_id = 0;
        if (isset($lines_array[$key])) {
            $concept_id = $lines_array[$key]['id'];
        }
        $tax_id = 0;
        if (isset($taxes_master_by_value[$val[4]])) {
            $tax_id = $taxes_master_by_value[$val[4]]['id'];
        }
        $product_id = 0;
        if (!strlen(implode('', $val))) {
            if (!$concept_id) {
                unset($lines[$key]);
                continue;
            }
            $concept_id *= -1;
        }
        $row = [
            'id' => $concept_id,
            'product_id' => $product_id,
            'description' => $val[0],
            'quantity' => $val[1],
            'price' => $val[2],
            'discount' => $val[3],
            'tax_id' => $tax_id,
            'tax_value' => $val[4],
            'total' => $val[5],
        ];
        if (!$concept_id) {
            unset($row['id']);
            // @phpstan-ignore booleanNot.alwaysTrue
            if (!$product_id) {
                unset($row['product_id']);
            }
            if ($val[0] == '') {
                unset($row['description']);
            }
            if ($val[1] == '') {
                unset($row['quantity']);
            }
            if ($val[2] == '') {
                unset($row['price']);
            }
            if ($val[3] == '') {
                unset($row['discount']);
            }
            if (!$tax_id) {
                unset($row['tax_id']);
            }
            if ($val[4] == '') {
                unset($row['tax_value']);
            }
            if ($val[5] == '') {
                unset($row['total']);
            }
        } else {
            if ($lines_array[$key]['product_id'] == $product_id) {
                unset($row['product_id']);
            }
            if ($lines_array[$key]['description'] == $val[0]) {
                unset($row['description']);
            }
            if ($lines_array[$key]['quantity'] == $val[1]) {
                unset($row['quantity']);
            }
            if ($lines_array[$key]['price'] == $val[2]) {
                unset($row['price']);
            }
            if ($lines_array[$key]['discount'] == $val[3]) {
                unset($row['discount']);
            }
            if ($lines_array[$key]['tax_id'] == $tax_id) {
                unset($row['tax_id']);
            }
            if ($lines_array[$key]['tax_value'] == $val[4]) {
                unset($row['tax_value']);
            }
            if ($lines_array[$key]['total'] == $val[5]) {
                unset($row['total']);
            }
            if (count($row) == 1) {
                unset($row['id']);
            }
        }
        if (count($row)) {
            $lines[$key] = $row;
        } else {
            unset($lines[$key]);
        }
    }
    $lines_array = array_slice($lines_array, $count);
    foreach ($lines_array as $key => $val) {
        $lines[] = [
            'id' => $val['id'] * -1,
        ];
    }
    if (count($lines)) {
        $json['lines'] = $lines;
    }

    // Taxes box
    $count = count($taxes);
    foreach ($taxes as $key => $val) {
        $reg_id = 0;
        if (isset($taxes_array[$key])) {
            $reg_id = $taxes_array[$key]['id'];
        }
        $tax_id = 0;
        $tax_value = 0;
        if (isset($taxes_master_by_name[$val[0]])) {
            $tax_id = $taxes_master_by_name[$val[0]]['id'];
            $tax_value = $taxes_master_by_name[$val[0]]['value'];
        }
        if (!strlen(implode('', $val))) {
            if (!$reg_id) {
                unset($taxes[$key]);
                continue;
            }
            $reg_id *= -1;
        }
        $row = [
            'id' => $reg_id,
            'tax_id' => $tax_id,
            'tax_name' => $val[0],
            'tax_value' => $tax_value,
            'base' => $val[1],
            'tax' => $val[2],
        ];
        if (!$reg_id) {
            unset($row['id']);
            if (!$tax_id) {
                unset($row['tax_id']);
            }
            if ($val[0] == '') {
                unset($row['tax_name']);
            }
            if (!$tax_value) {
                unset($row['tax_value']);
            }
            if ($val[1] == '') {
                unset($row['base']);
            }
            if ($val[2] == '') {
                unset($row['tax']);
            }
        } else {
            if ($taxes_array[$key]['tax_id'] == $tax_id) {
                unset($row['tax_id']);
            }
            if ($taxes_array[$key]['tax_name'] == $val[0]) {
                unset($row['tax_name']);
            }
            if ($taxes_array[$key]['tax_value'] == $tax_value) {
                unset($row['tax_value']);
            }
            if ($taxes_array[$key]['base'] == $val[1]) {
                unset($row['base']);
            }
            if ($taxes_array[$key]['tax'] == $val[2]) {
                unset($row['tax']);
            }
            if (count($row) == 1) {
                unset($row['id']);
            }
        }
        if (count($row)) {
            $taxes[$key] = $row;
        } else {
            unset($taxes[$key]);
        }
    }
    $taxes_array = array_slice($taxes_array, $count);
    foreach ($taxes_array as $key => $val) {
        $taxes[] = [
            'id' => $val['id'] * -1,
        ];
    }
    if (count($taxes)) {
        $json['taxes'] = $taxes;
    }

    // Totals box
    if (count($totals)) {
        $json['subtotal'] = $totals[0][0];
        $json['tax'] = $totals[0][1];
        $json['total'] = $totals[0][2];
        if (isset($totals_array[0])) {
            if ($totals_array[0]['subtotal'] == $totals[0][0]) {
                unset($json['subtotal']);
            }
            if ($totals_array[0]['tax'] == $totals[0][1]) {
                unset($json['tax']);
            }
            if ($totals_array[0]['total'] == $totals[0][2]) {
                unset($json['total']);
            }
        }
    }

    return $json;
}

/**
 * Generates or completes proforma and final invoice metadata.
 *
 * This function ensures that the given invoice JSON structure has appropriate values for:
 * - `proforma_code` and `proforma_date` (if not provided or if it's a new invoice)
 * - `invoice_code` and `invoice_date` (if the invoice is being closed)
 * - `is_closed` (based on database if not explicitly set)
 * - `paid_date` (if the invoice is marked as paid)
 * - `due_date` (if the invoice is marked as closed)
 *
 * Rules:
 * - If no `invoice_id` is given or `proforma_code` is not defined, a new proforma number is generated.
 * - If the invoice is being closed (`is_closed` is true), a new invoice number is generated.
 * - If `is_closed` is not defined in the JSON, it's looked up in the database for the given invoice ID.
 * - If `is_paid` is true and no `paid_date` is set, the current date is assigned.
 * - If the invoice is closed and no `due_date` is set, the current date is assigned.
 *
 * @json       => Invoice data to process and complete.
 * @invoice_id => ID of the invoice, used to retrieve current status from DB.
 *
 * Return the updated JSON with appropriate codes and dates filled in.
 */
function set_proforma_invoice($json, $invoice_id)
{
    // Set proforma_code and proforma_date
    if (!$invoice_id || !isset($json['proforma_code']) || !$json['proforma_code']) {
        $query = 'SELECT MAX(proforma_code) FROM app_invoices';
        $last = execute_query($query);
        if ($last) {
            $last = explode('-', $last);
            $last = array_reverse($last);
            $last = intval($last[0]);
        } else {
            $last = 0;
        }
        $next = $last + 1;
        $year = date('Y');
        $json['proforma_code'] = "PF$year-$next";
        $json['proforma_date'] = current_date();
    }

    // Set invoice_code and invoice_date
    if (isset($json['is_closed']) && $json['is_closed']) {
        $query = 'SELECT MAX(invoice_code) FROM app_invoices';
        $last = execute_query($query);
        if ($last) {
            $last = explode('-', $last);
            $last = array_reverse($last);
            $last = intval($last[0]);
        } else {
            $last = 0;
        }
        $next = $last + 1;
        $year = date('Y');
        $json['invoice_code'] = "F$year-$next";
        $json['invoice_date'] = current_date();
    }

    // Set is_closed to true if already closed in DB
    if (!isset($json['is_closed']) || !$json['is_closed']) {
        $query = 'SELECT is_closed FROM app_invoices WHERE id = ?';
        $is_closed = execute_query($query, [$invoice_id]);
        if ($is_closed) {
            $json['is_closed'] = 1;
        }
    }

    // Set the paid_date if needed
    if (isset($json['is_paid']) && $json['is_paid']) {
        if (!isset($json['paid_date']) || !$json['paid_date']) {
            $json['paid_date'] = current_date();
        }
    }

    // Set the due_date if needed
    if (isset($json['is_closed']) && $json['is_closed']) {
        if (!isset($json['due_date']) || !$json['due_date']) {
            $json['due_date'] = current_date();
        }
    }

    return $json;
}
