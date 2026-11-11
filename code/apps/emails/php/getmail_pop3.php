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
 * Get email POP3 library
 *
 * This library contains the functions used to receive the emails of the POP3 accounts
 */

/**
 * Receive an account
 *
 * This function connects with the POP3 server of the account, downloads the new emails and
 * removes the expired emails from the server if the delete option of the account is enabled
 *
 * @row      => the row of the account
 * @newemail => the counter of the new emails, incremented with the downloaded emails
 *
 * Returns an empty string on success or the error message
 */
function getmail_pop3($row, &$newemail)
{
    require_once 'apps/emails/php/pop3.php';
    $id_cuenta = $row['id'];
    $prefix = get_directory('dirs/inboxdir') . $id_cuenta;
    if (!file_exists($prefix)) {
        mkdir($prefix);
        chmod_protected($prefix, 0777);
    }
    // db code
    $query = 'SELECT uidl FROM app_emails WHERE account_id = ?';
    $olduidls = execute_query_array($query, [$id_cuenta]);
    $query = 'SELECT uidl FROM app_emails_deletes WHERE account_id = ?';
    $olduidls_d = execute_query_array($query, [$id_cuenta]);
    $olduidls = array_merge($olduidls, $olduidls_d);
    // pop3 code
    $pop3 = new pop3_class();
    $pop3->hostname = $row['pop3_host'];
    if ($row['pop3_port']) {
        $pop3->port = $row['pop3_port'];
    }
    $pop3->tls = ($row['pop3_extra'] === 'tls') ? 1 : 0;
    $pop3->ssl = ($row['pop3_extra'] === 'ssl') ? 1 : 0;
    $pop3->user = $row['pop3_user'];
    $pop3->pass = $row['pop3_pass'];
    $error = $pop3->Open();
    $uidls = null;
    if ($error === '') {
        $uidls = $pop3->ListMessages();
        if (!is_array($uidls)) {
            $error = $uidls;
        }
    }
    if ($error === '') {
        // retrieve all new messages
        $retrieve = array_diff($uidls, $olduidls);
        foreach ($retrieve as $index => $uidl) {
            if (time_get_usage() > get_config('server/percentstop')) {
                break;
            }
            if ($error === '') {
                $file = $prefix . '/' . $uidls[$index] . '.eml.gz';
                if (!file_exists($file)) {
                    // retrieve the entire message
                    $message = null;
                    $error = $pop3->GetMessage($index, $message);
                    if ($error === '') {
                        // store the message into single file
                        $fp = gzopen($file, 'w');
                        gzwrite($fp, $message);
                        gzclose($fp);
                        chmod_protected($file, 0666);
                        $message = ''; // trick to release memory
                    }
                }
                if ($error === '') {
                    $messageid = $id_cuenta . '/' . $uidls[$index];
                    $last_id = __getmail_insert($file, $messageid);
                    $newemail++;
                }
            }
        }
    }
    if ($error === '' && $row['pop3_delete']) {
        // remove all expired messages (if checked the delete option)
        $delete = "'" . implode("','", $uidls) . "'";
        $query = "SELECT uidl,datetime FROM (
            SELECT uidl,datetime
            FROM app_emails
            WHERE account_id = ? AND uidl IN ($delete)
            UNION
            SELECT uidl,datetime
            FROM app_emails_deletes
            WHERE account_id = ? AND uidl IN ($delete) ) a";
        $result2 = execute_query_array($query, [$id_cuenta, $id_cuenta]);
        $time1 = strtotime(current_datetime());
        foreach ($result2 as $row2) {
            $time2 = strtotime($row2['datetime']);
            if ($time1 - $time2 >= $row['pop3_days'] * 86400) {
                // DELE only marks the message, the indexes do not change in the session
                $index2 = array_search($row2['uidl'], $uidls, true);
                $error = $pop3->DeleteMessage($index2);
                unset($uidls[$index2]);
            }
            if ($error !== '') {
                break;
            }
        }
    }
    if ($error === '') {
        $error = $pop3->Close();
    }
    if ($error === '') {
        // remove all unused uidls
        $delete = array_diff($olduidls_d, $uidls);
        $delete = "'" . implode("','", $delete) . "'";
        $query = "DELETE FROM app_emails_deletes WHERE account_id = ? AND uidl IN ($delete)";
        db_query($query, [$id_cuenta]);
    }
    return $error;
}

/**
 * Delete helper
 *
 * This function stores the uidls of the emails of the POP3 accounts that are deleted in
 * SaltOS (except the emails of the outbox) in the deletes table, to not download them
 * again while they are in the server; it must be called before the emails are deleted, it
 * is an internal helper that not checks the permissions
 *
 * @ids => the ids of the emails separated by commas
 */
function __getmail_pop3_delete($ids)
{
    $ids = check_ids($ids);
    $query = "INSERT INTO app_emails_deletes(account_id,uidl,datetime)
        SELECT account_id,uidl,datetime
        FROM app_emails WHERE id IN ($ids) AND is_outbox = 0 AND account_id IN (
            SELECT id FROM app_emails_accounts WHERE protocol = 'pop3')";
    db_query($query);
}
