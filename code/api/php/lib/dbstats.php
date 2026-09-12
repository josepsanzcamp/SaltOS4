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
 * DB Stats module
 *
 * This file contains the function used to record per-query usage statistics (call count,
 * min/max/total execution time) when the debug/slowquerystats config flag is enabled,
 * useful to spot usage patterns like N+1 queries while debugging
 *
 * This file is intentionally kept out of the autoload set and is only require_once'd by
 * db_query() when the flag is active, since it is a debugging feature that does not need
 * to be loaded in memory on every request
 */

/**
 * DB Query Stats
 *
 * This private function is intended to record usage statistics for every executed query,
 * used for reporting/debugging purposes (not to filter or flag queries as slow)
 *
 * @curtime => the elapsed time, in seconds, that the query took to execute
 *
 * Notes:
 *
 * This function uses its own isolated SQLite connection (a plain db_connect() call that
 * is never stored in the global config), so it never shares the main app connection and
 * never affects db_last_insert_id() of the real database. The stats table is created by
 * hand the first time (file_exists() detects a first run, since the SQLite driver itself
 * creates an empty file when one does not exist yet), without any dependency on
 * dbschema.xml/dbschema.php, to avoid any reentrant call back into db_query()
 *
 * Each query is identified by its call-site (file:line:function), obtained by walking the
 * backtrace until a frame outside this file is found
 */
function __db_query_stats($curtime)
{
    static $obj = null;
    if ($obj === null) {
        $file = get_directory('dirs/filesdir') . (get_config('debug/slowqueryfile') ?? 'dbstats.sqlite');
        $new = !file_exists($file);
        $obj = db_connect(['type' => 'pdo_sqlite', 'file' => $file]);
        if ($new) {
            $obj->db_query('CREATE TABLE tbl_slowqueries (
                id INTEGER PRIMARY KEY,
                source TEXT,
                count INTEGER,
                min REAL,
                max REAL,
                sum REAL,
                datetime TEXT
            )');
            $obj->db_query('CREATE INDEX idx_slowqueries_source ON tbl_slowqueries (source)');
        }
    }

    static $cache = [];

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $skip = [__FUNCTION__, 'db_query', 'execute_query', 'execute_query_array'];
    $source = null;
    foreach ($backtrace as $frame) {
        if (!in_array($frame['function'], $skip, true)) {
            $source = ($file_line ?? 'unknown:unknown') . ':' . $frame['function'];
            break;
        }
        $file_line = __get_code_from_file_and_line($frame['file'] ?? 'unknown', $frame['line'] ?? 'unknown');
    }

    $datetime = current_datetime();

    if (!isset($cache[$source])) {
        $query = 'SELECT id FROM tbl_slowqueries WHERE source = ?';
        $cache[$source] = $obj->db_query($query, [$source])['rows'][0] ?? null;
    }

    if ($cache[$source] === null) {
        $query = 'INSERT INTO tbl_slowqueries (source, count, min, max, sum, datetime)
            VALUES (?, 1, ?, ?, ?, ?)';
        $obj->db_query($query, [$source, $curtime, $curtime, $curtime, $datetime]);

        $cache[$source] = [
            'id' => $obj->db_last_insert_id(),
        ];
    } else {
        $id = $cache[$source]['id'];

        $query = 'UPDATE tbl_slowqueries SET
            count = count + 1,
            min = MIN(min, CAST(? AS REAL)),
            max = MAX(max, CAST(? AS REAL)),
            sum = sum + ?,
            datetime = ?
            WHERE id = ?';
        $obj->db_query($query, [$curtime, $curtime, $curtime, $datetime, $id]);
    }
}
