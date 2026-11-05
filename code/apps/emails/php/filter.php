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
 * Email Query Builder
 *
 * This function builds an SQL WHERE query for filtering emails based on various criteria
 * such as account, fields, search terms, dates, and specific email states.
 */

/**
 * Generate WHERE query for emails
 *
 * This function takes a JSON input and generates a dynamic SQL WHERE clause to filter emails.
 * The query is constructed based on several optional parameters such as account ID, search fields,
 * dates, and flags indicating specific states (e.g., new emails, waiting emails).
 *
 * @json => Input parameters for generating the query.
 *
 * Returns the dynamically constructed SQL WHERE clause.
 */
function make_where_query_emails($json)
{
    // Extract input fields from the JSON object
    $account_id = $json['account_id'] ?? '';
    $folder_id = $json['folder_id'] ?? '';
    $fields = $json['fields'] ?? '';
    $search = $json['search'] ?? '';
    $date1 = $json['date1'] ?? '';
    $date2 = $json['date2'] ?? '';
    $date3 = $json['date3'] ?? '';
    $onlynew = $json['onlynew'] ?? '';
    $onlywait = $json['onlywait'] ?? '';
    $withfiles = $json['withfiles'] ?? '';
    $withoutfiles = $json['withoutfiles'] ?? '';

    // Map input fields to their corresponding database columns
    $fields_array = [
        '' => '',
        'email' => '`from`,`to`,cc,bcc',
        'subject' => 'subject',
        'body' => 'body',
    ];
    $fields = isset($fields_array[$fields]) ? $fields : '';

    // Define conditions for date ranges
    $date3_array = [
        'today' => "DATE(datetime)=DATE('" . current_date() . "')",
        'yesterday' => "DATE(datetime)=DATE('" . current_date(-86400) . "')",
        'week' => "DATE(datetime)>=DATE('" . current_date(-86400 * 7) . "')",
        'month' => "DATE(datetime)>=DATE('" . current_date(-86400 * 30) . "')",
    ];
    $date3 = isset($date3_array[$date3]) ? $date3 : '';

    // Define conditions for email states
    $only = intval($onlynew) . intval($onlywait);
    $only_array = [
        '10' => "state_new='1'",
        '01' => "state_wait='1'",
        '11' => "(state_new='1' OR state_wait='1')",
    ];
    $only = isset($only_array[$only]) ? $only : '';

    // Start building the WHERE clause
    $query = ['1=1']; // Default condition to ensure the query is valid
    if ($account_id !== '') {
        $account_id = intval($account_id);
        $query[] = "account_id='$account_id'";
    }
    $folder_id = intval($folder_id);
    if ($folder_id > 0) {
        $query[] = "folder_id='$folder_id'";
    } else {
        // Without folder, the emails of the trash and the junk are not shown, the special
        // uses are passed as parameters because MySQL removes the backslashes of the literals
        $ids = execute_query_array('SELECT id FROM app_emails_folders WHERE special_use IN (?, ?)', [
            '\Trash',
            '\Junk',
        ]);
        if (count($ids)) {
            $ids = check_ids($ids);
            $query[] = "IFNULL(folder_id, 0) NOT IN ($ids)";
        }
    }
    if ($fields !== '') {
        $query[] = make_like_query($fields_array[$fields], $search);
    }
    if ($only !== '') {
        $query[] = $only_array[$only];
    }
    if ($withfiles) {
        $query[] = 'files>0';
    }
    if ($withoutfiles) {
        $query[] = 'files=0';
    }
    if ($date1 !== '') {
        $date1 = dateval($date1);
        $query[] = "(DATE(datetime)>=DATE('$date1'))";
    }
    if ($date2 !== '') {
        $date2 = dateval($date2);
        $query[] = "(DATE('$date2')>=DATE(datetime))";
    }
    if ($date3 !== '') {
        $query[] = $date3_array[$date3];
    }

    // Combine all conditions into a single WHERE clause
    $query = implode(' AND ', $query);
    return $query;
}
