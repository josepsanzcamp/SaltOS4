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
 * DB stats management functions
 *
 * These functions expose the tbl_slowqueries table collected by __db_query_stats()
 * (see php/lib/dbstats.php) as a read-only list/view application. The table lives in
 * its own isolated SQLite file, not in the main application database, so the queries
 * here connect to it directly instead of going through db_query()/get_config('db/obj')
 */

/**
 * Get the DB stats connection
 *
 * This function returns the isolated connection to the stats database, or null when
 * the file does not exist yet (debug/slowquerystats never enabled, or nothing recorded)
 *
 * Returns the database connector object, or null if the stats file does not exist
 */
function __dbstats_connect()
{
    $file = get_directory('dirs/filesdir') . (get_config('debug/slowqueryfile') ?? 'dbstats.sqlite');
    if (!file_exists($file)) {
        return null;
    }
    return db_connect(['type' => 'pdo_sqlite', 'file' => $file]);
}

/**
 * List DB stats
 *
 * This function retrieves a list of rows from the tbl_slowqueries table, applies search
 * filters over the source column, and paginates the results based on offset and limit
 *
 * @search => Search term or query to filter rows by source.
 * @order  => Column and direction to order by, already validated by check_order().
 * @offset => Offset for pagination.
 * @limit  => Maximum number of rows to retrieve.
 *
 * Return the list of rows with their id, source and aggregated timing stats.
 */
function __dbstats_list($search, $order, $offset, $limit)
{
    $obj = __dbstats_connect();
    if ($obj === null) {
        return [];
    }

    $where = '1=1';
    $params = [];
    $search = explode_with_quotes(' ', $search);
    foreach ($search as $val) {
        $val = get_string_from_quotes($val);
        $type = '+';
        while (isset($val[0]) && in_array($val[0], ['+', '-'], true)) {
            $type = $val[0];
            $val = substr($val, 1);
        }
        $val = get_string_from_quotes($val);
        if (!strlen($val)) {
            continue;
        }
        $where .= $type === '-' ? ' AND source NOT LIKE ?' : ' AND source LIKE ?';
        $params[] = '%' . $val . '%';
    }

    $order = $order !== '' ? $order : 'sum DESC';
    $query = "SELECT *, sum * 1.0 / count AS avg FROM tbl_slowqueries WHERE $where ORDER BY $order";
    if ($limit !== INF) {
        $query .= ' LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
    }
    $list = $obj->db_query($query, $params)['rows'];

    foreach ($list as $key => $row) {
        $list[$key] = __dbstats_format($row);
    }

    return $list;
}

/**
 * Format a DB stats row
 *
 * This function is a helper used by __dbstats_list and __dbstats_view to format a raw
 * tbl_slowqueries row into the fields shown by the application, adding the average time
 *
 * @row => the raw row, as returned by the database driver.
 *
 * Return the formatted row.
 */
function __dbstats_format($row)
{
    return [
        'id' => $row['id'],
        'source' => $row['source'],
        'count' => $row['count'],
        'min' => round($row['min'], 6),
        'max' => round($row['max'], 6),
        'avg' => round($row['sum'] / $row['count'], 6),
        'sum' => round($row['sum'], 6),
        'datetime' => $row['datetime'],
    ];
}

/**
 * Check DB stats row existence
 *
 * This function checks if a row with the given id exists in the tbl_slowqueries table.
 *
 * @id => Id of the row to check.
 *
 * Return true if the row exists, false otherwise.
 */
function __dbstats_check($id)
{
    $obj = __dbstats_connect();
    if ($obj === null) {
        return false;
    }
    $row = $obj->db_query('SELECT id FROM tbl_slowqueries WHERE id = ?', [intval($id)])['rows'][0] ?? null;
    return $row !== null;
}

/**
 * View DB stats row
 *
 * This function retrieves the detailed information of a single tbl_slowqueries row.
 *
 * @id => Id of the row to view.
 *
 * Return the formatted row, or a JSON error if the row is not found.
 */
function __dbstats_view($id)
{
    $obj = __dbstats_connect();
    $row = null;
    if ($obj !== null) {
        $row = $obj->db_query('SELECT * FROM tbl_slowqueries WHERE id = ?', [intval($id)])['rows'][0] ?? null;
    }
    if ($row === null) {
        show_json_error('Id not found');
    }
    return __dbstats_format($row);
}
