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
 * Setup helper module
 *
 * This file contains useful functions related to the setup process
 */

/**
 * Setup helper common
 *
 * This function loads the sample data of the common logs using the generic
 * setup helper, and creates the isolated dbstats database with sample data
 */
function __setup_helper_common()
{
    $output = __setup_helper('common');

    // Create the dbstats database with sample data
    $total = 0;
    $file = get_directory('dirs/filesdir') . (get_config('debug/slowqueryfile') ?? 'dbstats.sqlite');
    if (!file_exists($file)) {
        $obj = db_connect(['type' => 'pdo_sqlite', 'file' => $file]);
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
        $rows = [
            [1, 'user:52:current_token', 1842, 0.000041, 0.003127, 0.112385, '2026-11-07 13:10:05'],
            [2, 'perms:49:check_user', 1842, 0.000038, 0.002954, 0.098716, '2026-11-07 13:10:05'],
            [3, 'apps:40:__apps', 615, 0.000112, 0.004518, 0.104203, '2026-11-07 13:09:58'],
            [4, 'config:67:get_config', 2310, 0.000029, 0.001876, 0.087412, '2026-11-07 13:10:05'],
            [5, 'control:62:make_control', 128, 0.000087, 0.006342, 0.024519, '2026-11-07 12:55:31'],
            [6, 'indexing:62:make_index', 128, 0.000143, 0.012874, 0.051236, '2026-11-07 12:55:31'],
            [7, 'version:73:make_version', 128, 0.000096, 0.008731, 0.031847, '2026-11-07 12:55:31'],
            [8, 'log:95:make_log', 342, 0.000052, 0.003418, 0.029653, '2026-11-07 13:02:44'],
            [9, 'push:45:push_insert', 57, 0.000064, 0.002215, 0.006108, '2026-11-07 12:48:19'],
            [10, 'upload:68:add_upload_file', 23, 0.000158, 0.015327, 0.041962, '2026-11-07 11:37:02'],
        ];
        foreach ($rows as $row) {
            $query = 'INSERT INTO tbl_slowqueries (id, source, count, min, max, sum, datetime)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
            $obj->db_query($query, $row);
            $total++;
        }
    }

    $output['setup']['total']['dbstats'] = $total;
    return $output;
}
