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
 * ChartJS widgets for invoices
 *
 * This file contains helper functions that generate Chart.js-compatible
 * datasets for visualizing invoice-related data within SaltOS4.
 *
 * Each function returns an associative array with 'labels' and 'datasets',
 * directly usable by `<chartjs>` widgets in the XML layout system.
 *
 * The metrics provided include:
 * - Daily total invoicing
 * - Daily average invoice value
 * - Top 5 customers by total invoiced
 * - Count of paid vs unpaid invoices
 * - Average number of days to get paid
 *
 * All functions rely on data from the `app_invoices` table and assume
 * only closed invoices (`is_closed = 1`) are relevant.
 */

/**
 * compute_invoice_total_by_day
 *
 * Computes the total invoiced amount per day, for closed invoices.
 * Useful for displaying the evolution of total income over time.
 *
 * Returns a Chart.js-compatible array with labels (dates) and a single dataset (total per day).
 */
function compute_invoice_total_by_day()
{
    $sql = <<<SQL
    SELECT
        /*SQLITE strftime('%Y-%m-%d', invoice_date) AS day, */
        /*MYSQL DATE_FORMAT(invoice_date, '%Y-%m-%d') AS day, */
        SUM(total) AS total
    FROM app_invoices
    WHERE is_closed = 1
    GROUP BY day
    ORDER BY day ASC;
SQL;

    $data = execute_query_array($sql);

    $labels = [];
    $values = [];

    foreach ($data as $row) {
        $labels[] = $row['day'];
        $values[] = floatval($row['total']);
    }

    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Total invoiced per day',
            'data' => $values,
        ],],
    ];
}

/**
 * compute_invoice_avg_by_day
 *
 * Computes the average invoice amount per day, considering only closed invoices.
 * This helps analyze how invoice sizes vary over time.
 *
 * Returns a Chart.js-compatible array with labels (dates) and a single dataset (average amount).
 */
function compute_invoice_avg_by_day()
{
    $sql = <<<SQL
    SELECT
        /*SQLITE strftime('%Y-%m-%d', invoice_date) AS day, */
        /*MYSQL DATE_FORMAT(invoice_date, '%Y-%m-%d') AS day, */
        AVG(total) AS average
    FROM app_invoices
    WHERE is_closed = 1
    GROUP BY day
    ORDER BY day ASC;
SQL;

    $data = execute_query_array($sql);

    $labels = [];
    $values = [];

    foreach ($data as $row) {
        $labels[] = $row['day'];
        $values[] = floatval($row['average']);
    }

    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Average invoice per day',
            'data' => $values,
        ],],
    ];
}

/**
 * compute_top5_customers_by_total
 *
 * Retrieves the top 5 customers based on total invoiced amount for closed invoices.
 * Cuts customer names using `intelligence_cut()` for chart readability.
 *
 * Returns a Chart.js-compatible array: labels (customer names) and one dataset (total invoiced).
 */
function compute_top5_customers_by_total()
{
    $sql = <<<SQL
    SELECT customer_name, SUM(total) AS total
    FROM app_invoices
    WHERE is_closed = 1
    GROUP BY customer_name
    ORDER BY total DESC
    LIMIT 5;
SQL;

    $data = execute_query_array($sql);

    $labels = [];
    $values = [];

    foreach ($data as $row) {
        $labels[] = intelligence_cut($row['customer_name'], 20); // usa lógica interna
        $values[] = floatval($row['total']);
    }

    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Top 5 customers',
            'data' => $values,
        ],],
    ];
}

/**
 * compute_invoice_paid_vs_pending
 *
 * Counts how many closed invoices have been paid or are still unpaid.
 * Produces a pie chart data structure separating "Paid" and "Unpaid".
 *
 * Returns a Chart.js-compatible array: labels (Paid/Unpaid) and one dataset (count of invoices).
 */
function compute_invoice_paid_vs_pending()
{
    $sql = <<<SQL
    SELECT is_paid, COUNT(*) AS count
    FROM app_invoices
    WHERE is_closed = 1
    GROUP BY is_paid;
SQL;

    $data = execute_query_array($sql);

    $labels = [];
    $values = [];

    foreach ($data as $row) {
        $labels[] = $row['is_paid'] == 1 ? 'Paid' : 'Unpaid';
        $values[] = intval($row['count']);
    }

    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Invoices by payment status',
            'data' => $values,
        ],],
    ];
}

/**
 * compute_invoice_avg_days_to_pay
 *
 * Calculates the average number of days it takes to get paid, based on the difference
 * between invoice and payment dates, grouped by payment day.
 *
 * Returns a Chart.js-compatible array: labels (payment dates) and one dataset (average days to pay).
 */
function compute_invoice_avg_days_to_pay()
{
    $sql = <<<SQL
    SELECT
        /*SQLITE strftime('%Y-%m-%d', paid_date) AS day, */
        /*MYSQL DATE_FORMAT(paid_date, '%Y-%m-%d') AS day, */
        /*SQLITE AVG(julianday(paid_date) - julianday(invoice_date)) AS avg_days */
        /*MYSQL AVG(DATEDIFF(paid_date, invoice_date)) AS avg_days */
    FROM app_invoices
    WHERE is_closed = 1 AND is_paid = 1
    GROUP BY day
    ORDER BY day ASC;
SQL;

    $data = execute_query_array($sql);

    $labels = [];
    $values = [];

    foreach ($data as $row) {
        $labels[] = $row['day'];
        $values[] = round(floatval($row['avg_days']), 2);
    }

    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Average days to get paid',
            'data' => $values,
        ],],
    ];
}
