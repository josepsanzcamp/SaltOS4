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
 * Push utils helper module
 *
 * This fie contains useful functions related to push feature
 */

/**
 * Push insert
 *
 * This function adds an entry to the push system using the type and message
 *
 * @type    => the type (one of this: success, danger or event)
 * @message => the desired message that you want to put in the queue
 */
function push_insert($type, $message)
{
    if (!in_array($type, ['success', 'danger', 'event'])) {
        show_php_error(['phperror' => "Unknown type $type"]);
    }
    $query = prepare_insert_query('tbl_push', [
        'user_id' => current_user(),
        'datetime' => current_datetime(),
        'type' => $type,
        'message' => $message,
        'timestamp' => microtime(true),
    ]);
    db_query(...$query);
}

/**
 * Push select
 *
 * This function returns the push data found after the timestamp used
 *
 * @timestamp => the timestamp used to begin the search
 *
 * Notes:
 *
 * - This function returns the entries found without repetitions, to be
 *   usefull, only uses the last entries removing the repeated entries and
 *   using only the type and message to detect repetitions
 */
function push_select($timestamp)
{
    $query = 'SELECT type, message, timestamp
        FROM tbl_push
        WHERE user_id = ? AND timestamp > ?
        ORDER BY id DESC';
    $rows = execute_query_array($query, [current_user(), $timestamp]);
    // remove repetitions
    $used = [];
    foreach ($rows as $key => $val) {
        $hash = md5(serialize([
            $val['type'],
            $val['message'],
        ]));
        if (isset($used[$hash])) {
            unset($rows[$key]);
        }
        $used[$hash] = $hash;
    }
    // order by id asc
    $rows = array_reverse($rows);
    return $rows;
}
