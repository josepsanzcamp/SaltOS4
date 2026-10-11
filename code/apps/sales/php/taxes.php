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
 * Taxes and withholdings helpers
 *
 * This file contains the functions shared by the documents that use taxes and
 * withholdings in their lines, like the invoices and the quotes, the taxes
 * and the withholdings are stored in the same master table (app_taxes) and
 * are differentiated by the type field (0 tax, 1 withholding), and the company
 * defines how the user operates with each one of them using the tax_mode and
 * withholding_mode fields (0 not used, 1 one by document, 2 by line).
 *
 * All functions that request a type as argument expect tax or withholding,
 * that are the names used as prefix by the fields of each type.
 */

/**
 * Get taxes modes
 *
 * This function returns an array with the mode used by each type, the keys
 * of the array are tax and withholding and the values can be as follow:
 *
 * - 0 => the type is not used, nothing is requested to the user
 * - 1 => the user selects only one value for the entire document
 * - 2 => the user selects one value for each line of the document
 */
function get_taxes_modes()
{
    $query = 'SELECT tax_mode, withholding_mode FROM app_company WHERE id = ?';
    $company = execute_query($query, [1]);
    return [
        'tax' => intval($company['tax_mode'] ?? 0),
        'withholding' => intval($company['withholding_mode'] ?? 0),
    ];
}

/**
 * Get taxes master
 *
 * This function returns the active registers of the master table, each row
 * contains the id, name, value, type and default fields.
 *
 * @type => the type that you want to retrieve, all types by default
 */
function get_taxes_master($type = '')
{
    $types = [
        'tax' => 0,
        'withholding' => 1,
    ];
    $query = 'SELECT id, name, value, type, `default` FROM app_taxes WHERE active = 1 ORDER BY id ASC';
    $rows = execute_query_array($query);
    foreach ($rows as $key => $row) {
        $row['value'] = floatval($row['value']);
        $row['type'] = intval($row['type']);
        $row['default'] = intval($row['default']);
        if ($type !== '' && $row['type'] !== $types[$type]) {
            unset($rows[$key]);
            continue;
        }
        $rows[$key] = $row;
    }
    return array_values($rows);
}

/**
 * Get taxes column
 *
 * This function returns the definition of the column used by the lines of
 * the documents, the column is a dropdown with the values of the requested
 * type when the mode is by line, otherwise the column is hidden, this allow
 * to use the same structure of data for all modes.
 *
 * @type  => the type of the column, can be tax or withholding
 * @title => the title of the column
 */
function get_taxes_column($type, $title)
{
    if (get_taxes_modes()[$type] !== 2) {
        return [
            'title' => $title,
            'type' => 'hidden',
        ];
    }
    return [
        'title' => $title,
        'width' => 100,
        'align' => 'center',
        'type' => 'dropdown',
        'source' => array_map('strval', array_column(get_taxes_master($type), 'value')),
        'autocomplete' => true,
    ];
}

/**
 * Get taxes total column
 *
 * This function returns the definition of the column used by the totals of
 * the documents, the column is hidden when the type is not used.
 *
 * @type  => the type of the column, can be tax or withholding
 * @title => the title of the column
 */
function get_taxes_total_column($type, $title)
{
    if (get_taxes_modes()[$type] === 0) {
        return [
            'title' => $title,
            'type' => 'hidden',
        ];
    }
    return [
        'title' => $title,
        'align' => 'right',
        'readOnly' => true,
    ];
}

/**
 * Get taxes subtotal column
 *
 * This function returns the definition of the subtotal column used by the
 * totals of the documents, the column is hidden when the taxes and the
 * withholdings are not used, because the subtotal is the same that the total.
 *
 * @title => the title of the column
 */
function get_taxes_subtotal_column($title)
{
    if (!array_sum(get_taxes_modes())) {
        return [
            'title' => $title,
            'type' => 'hidden',
        ];
    }
    return [
        'title' => $title,
        'align' => 'right',
        'readOnly' => true,
    ];
}

/**
 * Get taxes rows
 *
 * This function returns the rows used by the select of the one by document mode,
 * the first row is void to allow documents without tax or withholding.
 *
 * @type => the type of the rows, can be tax or withholding
 */
function get_taxes_rows($type)
{
    $rows = [
        ['label' => '', 'value' => ''],
    ];
    foreach (get_taxes_master($type) as $row) {
        $rows[] = [
            'label' => $row['name'],
            'value' => strval($row['value']),
        ];
    }
    return $rows;
}

/**
 * Get taxes default
 *
 * This function returns the value of the default register of the requested
 * type, or a void string if there is not a default register.
 *
 * @type => the type of the default, can be tax or withholding
 */
function get_taxes_default($type)
{
    foreach (get_taxes_master($type) as $row) {
        if ($row['default']) {
            return strval($row['value']);
        }
    }
    return '';
}

/**
 * Make taxes data
 *
 * This function returns the data of the lines used by the documents, the
 * argument is the list of the lines with the description, quantity, price,
 * discount, tax_id, tax_value, withholding_id, withholding_value and total
 * fields, and the returned array contains the matrix of the lines with the
 * description, quantity, price, discount, tax, withholding and total, and
 * the values used by the selects of the one by document mode, that only are set
 * when all lines of the document are using the same value.
 *
 * @lines => the list of the lines of the document
 */
function make_taxes_data($lines)
{
    $types = ['tax', 'withholding'];
    $values = array_fill_keys($types, []);
    foreach ($lines as $key => $line) {
        foreach ($types as $type) {
            $value = floatval($line["{$type}_value"]);
            // The void values are stored as zero, and must be void for the user,
            // a real zero requires an id, as the exempt operations do
            if (!$value && !$line["{$type}_id"]) {
                $value = null;
            }
            $line[$type] = $value;
            $values[$type][] = $value === null ? '' : strval($value);
        }
        $lines[$key] = [
            $line['description'],
            $line['quantity'],
            $line['price'],
            $line['discount'],
            $line['tax'],
            $line['withholding'],
            $line['total'],
        ];
    }
    $data = [];
    foreach ($types as $type) {
        $temp = array_unique($values[$type]);
        $data["{$type}_document"] = count($temp) === 1 ? reset($temp) : '';
    }
    $data['lines'] = array_values($lines);
    return $data;
}
