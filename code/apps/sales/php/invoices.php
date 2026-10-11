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
    require_once 'apps/sales/php/taxes.php';

    // The selects of the document mode are only used by the client
    unset($json['tax_document']);
    unset($json['withholding_document']);

    if (!count($json)) {
        return $json;
    }

    $checks = ['lines', 'totals'];
    foreach ($checks as $check) {
        if (!isset($json[$check]) || !check_real_matrix($json[$check])) {
            return $json;
        }
    }

    // The taxes box is not sent when the taxes and the withholdings are not used,
    // in this case the taxes of the document are not modified
    $has_taxes = isset($json['taxes']);
    if ($has_taxes && !check_real_matrix($json['taxes'])) {
        return $json;
    }

    $lines = $json['lines'] ?? [];
    $taxes = $json['taxes'] ?? [];
    $totals = $json['totals'] ?? [];
    unset($json['lines']);
    unset($json['taxes']);
    unset($json['totals']);

    // Lines array
    $query = 'SELECT * FROM app_invoices_lines WHERE invoice_id = ? ORDER BY id ASC';
    $lines_array = execute_query_array($query, [$invoice_id]);

    // Taxes master, that contains the taxes (0) and the withholdings (1)
    $taxes_master_by_name = [[], []];
    $taxes_master_by_value = [[], []];
    foreach (get_taxes_master() as $row) {
        $taxes_master_by_name[$row['type']][$row['name']] = $row;
        $taxes_master_by_value[$row['type']][strval($row['value'])] = $row;
    }

    // Taxes array
    $query = 'SELECT * FROM app_invoices_taxes WHERE invoice_id = ? ORDER BY id ASC';
    $taxes_array = execute_query_array($query, [$invoice_id]);

    // Totals array
    $query = 'SELECT subtotal, tax, withholding, total FROM app_invoices WHERE id = ?';
    $totals_array = execute_query_array($query, [$invoice_id]);

    // Columns of each matrix, in the same order used by the client
    $lines_cols = ['description', 'quantity', 'price', 'discount', 'tax', 'withholding', 'total'];
    $taxes_cols = ['type', 'name', 'base', 'tax'];
    $totals_cols = ['subtotal', 'tax', 'withholding', 'total'];

    // Concepts box
    $count = count($lines);
    foreach ($lines as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $val[$key2] = $val2 ?? '';
        }
        $val = array_combine($lines_cols, $val);

        $concept_id = 0;
        if (isset($lines_array[$key])) {
            $concept_id = $lines_array[$key]['id'];
        }
        $tax_id = 0;
        $temp = is_numeric($val['tax']) ? strval(floatval($val['tax'])) : '';
        if (isset($taxes_master_by_value[0][$temp])) {
            $tax_id = $taxes_master_by_value[0][$temp]['id'];
        }
        $withholding_id = 0;
        $temp = is_numeric($val['withholding']) ? strval(floatval($val['withholding'])) : '';
        if (isset($taxes_master_by_value[1][$temp])) {
            $withholding_id = $taxes_master_by_value[1][$temp]['id'];
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
            'description' => $val['description'],
            'quantity' => $val['quantity'],
            'price' => $val['price'],
            'discount' => $val['discount'],
            'tax_id' => $tax_id,
            'tax_value' => $val['tax'],
            'withholding_id' => $withholding_id,
            'withholding_value' => $val['withholding'],
            'total' => $val['total'],
        ];
        if (!$concept_id) {
            unset($row['id']);
            // @phpstan-ignore booleanNot.alwaysTrue
            if (!$product_id) {
                unset($row['product_id']);
            }
            if ($val['description'] === '') {
                unset($row['description']);
            }
            if ($val['quantity'] === '') {
                unset($row['quantity']);
            }
            if ($val['price'] === '') {
                unset($row['price']);
            }
            if ($val['discount'] === '') {
                unset($row['discount']);
            }
            if (!$tax_id) {
                unset($row['tax_id']);
            }
            if ($val['tax'] === '') {
                unset($row['tax_value']);
            }
            if (!$withholding_id) {
                unset($row['withholding_id']);
            }
            if ($val['withholding'] === '') {
                unset($row['withholding_value']);
            }
            if ($val['total'] === '') {
                unset($row['total']);
            }
        } else {
            if ($lines_array[$key]['product_id'] === $product_id) {
                unset($row['product_id']);
            }
            if ($lines_array[$key]['description'] === $val['description']) {
                unset($row['description']);
            }
            if ($lines_array[$key]['quantity'] === $val['quantity']) {
                unset($row['quantity']);
            }
            if ($lines_array[$key]['price'] === $val['price']) {
                unset($row['price']);
            }
            if ($lines_array[$key]['discount'] === $val['discount']) {
                unset($row['discount']);
            }
            if ($lines_array[$key]['tax_id'] === $tax_id) {
                unset($row['tax_id']);
            }
            if ($lines_array[$key]['tax_value'] === $val['tax']) {
                unset($row['tax_value']);
            }
            if ($lines_array[$key]['withholding_id'] === $withholding_id) {
                unset($row['withholding_id']);
            }
            if (floatval($lines_array[$key]['withholding_value']) === floatval($val['withholding'])) {
                unset($row['withholding_value']);
            }
            if ($lines_array[$key]['total'] === $val['total']) {
                unset($row['total']);
            }
            if (count($row) === 1) {
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
        foreach ($val as $key2 => $val2) {
            $val[$key2] = $val2 ?? '';
        }
        $val = array_combine($taxes_cols, $val);

        $reg_id = 0;
        if (isset($taxes_array[$key])) {
            $reg_id = $taxes_array[$key]['id'];
        }
        // The type is received as the label used by the client
        $type = 0;
        if (in_array($val['type'], ['withholding', T('Withholding')], true)) {
            $type = 1;
        }
        $tax_id = 0;
        $tax_value = 0;
        if (isset($taxes_master_by_name[$type][$val['name']])) {
            $tax_id = $taxes_master_by_name[$type][$val['name']]['id'];
            $tax_value = $taxes_master_by_name[$type][$val['name']]['value'];
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
            'type' => $type,
            'tax_id' => $tax_id,
            'tax_name' => $val['name'],
            'tax_value' => $tax_value,
            'base' => $val['base'],
            'tax' => $val['tax'],
        ];
        if (!$reg_id) {
            unset($row['id']);
            if (!$tax_id) {
                unset($row['tax_id']);
            }
            if ($val['name'] === '') {
                unset($row['tax_name']);
            }
            if (!$tax_value) {
                unset($row['tax_value']);
            }
            if ($val['base'] === '') {
                unset($row['base']);
            }
            if ($val['tax'] === '') {
                unset($row['tax']);
            }
        } else {
            if (intval($taxes_array[$key]['type']) === $type) {
                unset($row['type']);
            }
            if ($taxes_array[$key]['tax_id'] === $tax_id) {
                unset($row['tax_id']);
            }
            if ($taxes_array[$key]['tax_name'] === $val['name']) {
                unset($row['tax_name']);
            }
            if ($taxes_array[$key]['tax_value'] === $tax_value) {
                unset($row['tax_value']);
            }
            if ($taxes_array[$key]['base'] === $val['base']) {
                unset($row['base']);
            }
            if ($taxes_array[$key]['tax'] === $val['tax']) {
                unset($row['tax']);
            }
            if (count($row) === 1) {
                unset($row['id']);
            }
        }
        if (count($row)) {
            $taxes[$key] = $row;
        } else {
            unset($taxes[$key]);
        }
    }
    $taxes_array = array_slice($taxes_array, $has_taxes ? $count : count($taxes_array));
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
        $totals = array_combine($totals_cols, $totals[0]);
        $json['subtotal'] = $totals['subtotal'];
        $json['tax'] = $totals['tax'];
        $json['withholding'] = $totals['withholding'];
        $json['total'] = $totals['total'];
        if (isset($totals_array[0])) {
            if ($totals_array[0]['subtotal'] === $totals['subtotal']) {
                unset($json['subtotal']);
            }
            if ($totals_array[0]['tax'] === $totals['tax']) {
                unset($json['tax']);
            }
            if ($totals_array[0]['withholding'] === $totals['withholding']) {
                unset($json['withholding']);
            }
            if ($totals_array[0]['total'] === $totals['total']) {
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
    if (!count($json)) {
        return $json;
    }

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
        if (!isset($json['paid']) || !$json['paid']) {
            if (isset($json['total']) && $json['total']) {
                $json['paid'] = $json['total'];
            } else {
                $query = 'SELECT total FROM app_invoices WHERE id = ?';
                $total = execute_query($query, [$invoice_id]);
                $json['paid'] = $total;
            }
        }
    }

    // Set the due_date if needed
    if (isset($json['is_closed']) && $json['is_closed']) {
        if (!isset($json['due_date']) || !$json['due_date']) {
            $json['due_date'] = current_date();
        }
    }

    // Set the company
    $query = 'SELECT * FROM app_invoices WHERE id = ?';
    $invoice = execute_query($query, [$invoice_id]);
    $query = 'SELECT * FROM app_company WHERE id = ?';
    $company = execute_query($query, [1]);
    $fields = ['id', 'name', 'code', 'address', 'city', 'province', 'zip', 'country'];
    foreach ($fields as $field) {
        $real = 'company_' . $field;
        if (($invoice[$real] ?? '') !== ($company[$field] ?? '')) {
            $json[$real] = $company[$field];
        }
    }

    return $json;
}
