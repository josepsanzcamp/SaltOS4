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
 * Reverses the process of `make_matrix_data()` for the `quotes` app.
 *
 * This function takes a previously generated matrix-style array of quote data
 * (grouped as lines, taxes, and totals), compares it with the current values
 * in the database, and builds a minimal diff-like array that includes only
 * the fields that have changed, been removed, or added.
 */

/**
 * unmake_matrix_data
 *
 * Reverse the matrix-encoded quote data to a diff-style data array.
 *
 * @json     => The matrix-encoded quote data (from make_matrix_data)
 * @quote_id => The quote ID used to retrieve the original data
 *
 * Return a diff array representing only the differences between the provided
 * data and the current state of the database.
 */
function unmake_matrix_data($json, $quote_id)
{
    require_once 'apps/sales/php/taxes.php';

    // The selects of the document mode are only used by the client
    unset($json['tax_document']);
    unset($json['withholding_document']);

    if (!count($json)) {
        return $json;
    }

    // The client only sends the matrices that contains changes, and the taxes box is not
    // sent when the taxes and the withholdings are not used, in these cases only the sent
    // matrices are processed and the data of the other ones are not modified
    $checks = ['lines', 'taxes', 'totals'];
    $found = false;
    foreach ($checks as $check) {
        if (isset($json[$check])) {
            if (!check_real_matrix($json[$check])) {
                return $json;
            }
            $found = true;
        }
    }
    if (!$found) {
        return $json;
    }
    $has_lines = isset($json['lines']);
    $has_taxes = isset($json['taxes']);

    $lines = $json['lines'] ?? [];
    $taxes = $json['taxes'] ?? [];
    $totals = $json['totals'] ?? [];
    unset($json['lines']);
    unset($json['taxes']);
    unset($json['totals']);

    // Lines array
    $query = 'SELECT * FROM app_quotes_lines WHERE quote_id = ? ORDER BY id ASC';
    $lines_array = execute_query_array($query, [$quote_id]);

    // Taxes master, that contains the taxes (0) and the withholdings (1)
    $taxes_master_by_name = [[], []];
    $taxes_master_by_value = [[], []];
    foreach (get_taxes_master() as $row) {
        $taxes_master_by_name[$row['type']][$row['name']] = $row;
        $taxes_master_by_value[$row['type']][strval($row['value'])] = $row;
    }

    // Taxes array
    $query = 'SELECT * FROM app_quotes_taxes WHERE quote_id = ? ORDER BY id ASC';
    $taxes_array = execute_query_array($query, [$quote_id]);

    // Totals array
    $query = 'SELECT subtotal, tax, withholding, total FROM app_quotes WHERE id = ?';
    $totals_array = execute_query_array($query, [$quote_id]);

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
            if (intval($lines_array[$key]['product_id']) === $product_id) {
                unset($row['product_id']);
            }
            if (strval($lines_array[$key]['description']) === strval($val['description'])) {
                unset($row['description']);
            }
            if (floatval($lines_array[$key]['quantity']) === floatval($val['quantity'])) {
                unset($row['quantity']);
            }
            if (floatval($lines_array[$key]['price']) === floatval($val['price'])) {
                unset($row['price']);
            }
            if (floatval($lines_array[$key]['discount']) === floatval($val['discount'])) {
                unset($row['discount']);
            }
            if (intval($lines_array[$key]['tax_id']) === $tax_id) {
                unset($row['tax_id']);
            }
            if (floatval($lines_array[$key]['tax_value']) === floatval($val['tax'])) {
                unset($row['tax_value']);
            }
            if (intval($lines_array[$key]['withholding_id']) === $withholding_id) {
                unset($row['withholding_id']);
            }
            if (floatval($lines_array[$key]['withholding_value']) === floatval($val['withholding'])) {
                unset($row['withholding_value']);
            }
            if (floatval($lines_array[$key]['total']) === floatval($val['total'])) {
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
    $lines_array = array_slice($lines_array, $has_lines ? $count : count($lines_array));
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
            if (intval($taxes_array[$key]['tax_id']) === $tax_id) {
                unset($row['tax_id']);
            }
            if (strval($taxes_array[$key]['tax_name']) === strval($val['name'])) {
                unset($row['tax_name']);
            }
            if (floatval($taxes_array[$key]['tax_value']) === floatval($tax_value)) {
                unset($row['tax_value']);
            }
            if (floatval($taxes_array[$key]['base']) === floatval($val['base'])) {
                unset($row['base']);
            }
            if (floatval($taxes_array[$key]['tax']) === floatval($val['tax'])) {
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
            if (floatval($totals_array[0]['subtotal']) === floatval($totals['subtotal'])) {
                unset($json['subtotal']);
            }
            if (floatval($totals_array[0]['tax']) === floatval($totals['tax'])) {
                unset($json['tax']);
            }
            if (floatval($totals_array[0]['withholding']) === floatval($totals['withholding'])) {
                unset($json['withholding']);
            }
            if (floatval($totals_array[0]['total']) === floatval($totals['total'])) {
                unset($json['total']);
            }
        }
    }

    return $json;
}

/**
 * Get next code
 *
 * This function returns the next code of a serie, the codes are composed by
 * a prefix that contains the year and a correlative number, the number is
 * computed using the numeric value of the codes that begin by the prefix,
 * by this reason the serie is restarted when the year changes.
 *
 * @table  => the table that contains the codes
 * @field  => the field that contains the codes
 * @prefix => the prefix of the serie, for example PF2026-
 */
function get_next_code($table, $field, $prefix)
{
    $query = "SELECT $field FROM $table WHERE $field LIKE ?";
    $codes = execute_query_array($query, ["$prefix%"]);
    $last = 0;
    foreach ($codes as $code) {
        $last = max($last, intval(substr($code, strlen($prefix))));
    }
    return $prefix . ($last + 1);
}

/**
 * Generates or completes quote and final quote metadata.
 *
 * This function ensures that the given quote JSON structure has appropriate values for:
 * - `code`, `date` and `valid_until` (if not provided or if it's a new quote)
 *
 * Rules:
 * - If no `quote_id` is given or `code` is not defined, a new code number is generated.
 *
 * @json     => Quote data to process and complete.
 * @quote_id => ID of the quote, used to retrieve current status from DB.
 *
 * Return the updated JSON with appropriate codes and dates filled in.
 */
function set_quote($json, $quote_id)
{
    if (!count($json)) {
        return $json;
    }

    // The form only sends the modified fields, by this reason the stored
    // quote is used to know the value of the fields that not are received
    $query = 'SELECT * FROM app_quotes WHERE id = ?';
    $quote = execute_query($query, [$quote_id]);

    // The code and their date are set here and never by the client
    unset($json['code']);
    unset($json['date']);

    // Set code and date
    if (!$quote_id || !($quote['code'] ?? '')) {
        $json['code'] = get_next_code('app_quotes', 'code', 'Q' . date('Y') . '-');
        $json['date'] = current_date();
    }

    // Set the valid_until if needed
    $valid_until = $quote['valid_until'] ?? '';
    if (array_key_exists('valid_until', $json)) {
        $valid_until = $json['valid_until'];
    }
    if (in_array(strval($valid_until), ['', '0000-00-00'], true)) {
        $json['valid_until'] = current_date();
    }

    // Set the company
    $query = 'SELECT * FROM app_company WHERE id = ?';
    $company = execute_query($query, [1]);
    $fields = [
        'company_id', 'company_name', 'company_code', 'company_address',
        'company_city', 'company_province', 'company_zip', 'company_country',
    ];
    foreach ($fields as $field) {
        $real = str_replace('company_', '', $field);
        if (($quote[$field] ?? '') !== $company[$real]) {
            $json[$field] = $company[$real];
        }
    }

    return $json;
}
