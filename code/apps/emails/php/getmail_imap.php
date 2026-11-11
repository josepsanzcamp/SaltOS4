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
 * Get email IMAP library
 *
 * This library contains the functions used to synchronize the IMAP accounts with the
 * server: the folders, the emails and its flags
 */

/**
 * Synchronize an account
 *
 * This function connects with the IMAP server of the account, applies the pending
 * operations of the queue and synchronizes the folders and the emails of the subscribed
 * folders
 *
 * @account  => the row of the account
 * @newemail => the counter of the new emails, incremented with the downloaded emails
 *
 * Returns an empty string on success or the error message
 */
function getmail_imap($account, &$newemail)
{
    $imap = __getmail_imap_client($account);
    $error = $imap->Open();
    if ($error === '') {
        $error = __getmail_imap_queue($imap, intval($account['id']));
    }
    if ($error === '') {
        $error = __getmail_imap_folders($imap, intval($account['id']));
    }
    if ($error === '') {
        $error = __getmail_imap_emails($imap, intval($account['id']), $newemail);
    }
    if ($error === '') {
        $error = $imap->Close();
    }
    return $error;
}

/**
 * IMAP client
 *
 * This function returns the IMAP client configured with the incoming server of the
 * account, not connected
 *
 * @account => the row of the account
 *
 * Returns the IMAP client
 */
function __getmail_imap_client($account): imap_class
{
    require_once 'apps/emails/php/imap.php';
    $imap = new imap_class();
    $imap->hostname = $account['pop3_host'];
    $imap->port = intval($account['pop3_port']);
    $imap->tls = $account['pop3_extra'] === 'tls' ? 1 : 0;
    $imap->ssl = $account['pop3_extra'] === 'ssl' ? 1 : 0;
    $imap->user = $account['pop3_user'];
    $imap->pass = $account['pop3_pass'];
    return $imap;
}

/**
 * Synchronize the folders
 *
 * This function updates the folders of the account with the folders of the server: adds the
 * new folders, updates the existing ones and removes the folders that not exists in the
 * server, the folders are identified by its full name; the sent, the trash and the junk
 * are created in the server if they not exist; the inbox, the sent, the trash, the junk
 * and the archive are always subscribed; in the first synchronization of the account,
 * the folders subscribed in the server are subscribed too, the rest of the folders are
 * subscribed by the user
 *
 * @imap       => the IMAP client connected with the server
 * @account_id => the id of the account
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_folders($imap, $account_id)
{
    $folders = $imap->ListFolders();
    if (!is_array($folders)) {
        return $folders;
    }
    $folders = __getmail_imap_specials($imap, $folders);
    if (!is_array($folders)) {
        return $folders;
    }
    $query = 'SELECT name, id FROM app_emails_folders WHERE account_id = ?';
    $ids = array_column(execute_query_array($query, [$account_id]), 'id', 'name');
    $first = !count($ids);
    // The parents are processed before its children to know the parent_id
    usort($folders, fn ($a, $b) => __getmail_imap_depth($a) <=> __getmail_imap_depth($b));
    $names = [];
    foreach ($folders as $folder) {
        $names[] = $folder['name'];
        $fields = [
            'parent_id' => $ids[$folder['parent']] ?? 0,
            'label' => $folder['label'],
            'delimiter' => $folder['delimiter'],
            'special_use' => $folder['special_use'],
            'is_selectable' => $folder['selectable'] ? 1 : 0,
        ];
        // The generic special folders are always subscribed, also when they appear after
        // the first synchronization, because they are locked in the dialog of the folders
        $generic = ['\Inbox', '\Sent', '\Trash', '\Junk', '\Archive'];
        $generic = $folder['selectable'] && in_array($folder['special_use'], $generic, true);
        if ($generic) {
            $fields['is_subscribed'] = 1;
        }
        if (isset($ids[$folder['name']])) {
            $query = prepare_update_query('app_emails_folders', $fields, [
                'id' => $ids[$folder['name']],
            ]);
            db_query(...$query);
            continue;
        }
        $subscribed = $generic ? 1 : 0;
        if ($first && $folder['selectable']) {
            $excluded = ['\Drafts', '\All', '\Flagged', '\Important'];
            $excluded = in_array($folder['special_use'], $excluded, true);
            $subscribed = ($generic || ($folder['subscribed'] && !$excluded)) ? 1 : 0;
        }
        $query = prepare_insert_query('app_emails_folders', array_merge($fields, [
            'account_id' => $account_id,
            'name' => $folder['name'],
            'is_subscribed' => $subscribed,
        ]));
        db_query(...$query);
        $ids[$folder['name']] = db_last_insert_id();
    }
    // The emails of the removed folders are processed by the synchronization of the emails
    $removed = array_diff_key($ids, array_flip($names));
    if (count($removed)) {
        $query = 'DELETE FROM app_emails_folders WHERE id IN (' . check_ids($removed) . ')';
        db_query($query);
    }
    return '';
}

/**
 * Special folders
 *
 * This function completes the folders of the server with the sent, the trash and the junk
 * folders used by SaltOS: if there is no folder with the special use, a root folder with
 * the standard name (Sent, Trash or Junk) is used as the special folder, otherwise it is
 * created and subscribed in the server (with its special use if the server supports
 * CREATE-SPECIAL-USE); the folders that the server does not create are retried in the next
 * synchronization
 *
 * @imap    => the IMAP client connected with the server
 * @folders => the folders returned by the ListFolders of the IMAP client
 *
 * Returns the folders with the special folders, or the error message if the connection fails
 */
function __getmail_imap_specials($imap, $folders)
{
    $names = ['\Sent' => 'Sent', '\Trash' => 'Trash', '\Junk' => 'Junk'];
    $delimiter = '';
    foreach ($folders as $folder) {
        if ($delimiter === '' || $folder['name'] === 'INBOX') {
            $delimiter = $folder['delimiter'];
        }
    }
    foreach ($names as $special_use => $name) {
        if (in_array($special_use, array_column($folders, 'special_use'), true)) {
            continue;
        }
        foreach ($folders as $index => $folder) {
            if ($folder['parent'] === '' && $folder['name'] === $name && $folder['special_use'] === '') {
                $folders[$index]['special_use'] = $special_use;
                continue 2;
            }
        }
        $error = $imap->CreateFolder($name, $special_use);
        if ($error === '') {
            $error = $imap->SubscribeFolder($name);
        }
        if ($error !== '' && !$imap->Connected()) {
            return $error;
        }
        if ($error !== '') {
            continue;
        }
        $folders[] = [
            'name' => $name,
            'label' => $name,
            'parent' => '',
            'delimiter' => $delimiter,
            'special_use' => $special_use,
            'subscribed' => 1,
            'selectable' => 1,
        ];
    }
    return $folders;
}

/**
 * Depth of a folder
 *
 * This function returns the number of levels of the full name of the folder, used to sort
 * the folders with the parents before its children
 *
 * @folder => the folder returned by the ListFolders of the IMAP client
 *
 * Returns the number of delimiters found in the full name
 */
function __getmail_imap_depth($folder)
{
    if ($folder['delimiter'] === '') {
        return 0;
    }
    return substr_count($folder['name'], $folder['delimiter']);
}

/**
 * Synchronize the emails
 *
 * This function synchronizes the emails of the subscribed folders in three steps: first
 * finds the vanished emails (in SaltOS but not in the server) and the appeared emails (in
 * the server but not in SaltOS) of all the folders, then matches them in all the account to
 * detect the emails moved by other clients, and the emails without UID with the appeared
 * emails of its folder, and finally deletes the vanished emails not matched and downloads
 * the appeared emails not matched; the emails with pending operations in the queue are
 * frozen: they are never vanished and its flags are not changed
 *
 * @imap       => the IMAP client connected with the server
 * @account_id => the id of the account
 * @newemail   => the counter of the new emails, incremented with the downloaded emails
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_emails($imap, $account_id, &$newemail)
{
    $query = 'SELECT DISTINCT email_id FROM app_emails_queue WHERE account_id = ?';
    $frozen = array_flip(array_map('intval', execute_query_array($query, [$account_id])));
    $vanished = [];
    $appeared = [];
    $error = __getmail_imap_scan($imap, $account_id, $frozen, $vanished, $appeared);
    // The emails without UID in a folder of the account and without pending moves
    $query = "SELECT id, folder_id, imap_messageid, imap_emailid, size, is_outbox FROM app_emails
        WHERE account_id = ? AND IFNULL(imap_uid, 0) = 0
        AND folder_id IN (SELECT id FROM app_emails_folders WHERE account_id = ?)
        AND id NOT IN (SELECT email_id FROM app_emails_queue WHERE action = 'move')";
    $orphans = execute_query_array($query, [$account_id, $account_id]);
    if ($error === '' && count($appeared) && (count($vanished) || count($orphans))) {
        $error = __getmail_imap_match($imap, $frozen, $vanished, $orphans, $appeared);
    }
    if ($error === '' && count($vanished)) {
        __getmail_delete(implode(',', $vanished));
    }
    if ($error === '') {
        $error = __getmail_imap_download($imap, $account_id, $appeared, $newemail);
    }
    return $error;
}

/**
 * Find the vanished and the appeared emails
 *
 * This function compares the UIDs of the subscribed folders in the server with the UIDs of
 * the emails in SaltOS: the emails of a folder with a new UIDVALIDITY and the emails of
 * the folders removed in the server are vanished too, except the frozen emails; the UIDs of
 * the pending operations are known, so the emails moved in SaltOS and not yet in the server
 * do not appear in its old folder; the folders without changes since the last
 * synchronization (same UIDVALIDITY, same UIDNEXT and the same number of emails) are not
 * searched; too synchronizes the flags of the emails of each folder, except the frozen
 * emails, and the state of each folder is saved for the next synchronization
 *
 * @imap       => the IMAP client connected with the server
 * @account_id => the id of the account
 * @frozen     => the ids of the emails with pending operations as keys
 * @vanished   => filled with the ids of the vanished emails
 * @appeared   => filled with the UIDs of the appeared emails by folder id
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_scan($imap, $account_id, $frozen, &$vanished, &$appeared)
{
    // The emails of the folders removed in the server, with or without UID, except the
    // emails without folder (sent before the first synchronization of the account)
    $query = 'SELECT id FROM app_emails WHERE account_id = ? AND folder_id > 0
        AND folder_id NOT IN (SELECT id FROM app_emails_folders WHERE account_id = ?)';
    $ids = array_map('intval', execute_query_array($query, [$account_id, $account_id]));
    $vanished = array_values(array_diff($ids, array_keys($frozen)));
    $query = 'SELECT id, name, uidvalidity, uidnext, highestmodseq FROM app_emails_folders
        WHERE account_id = ? AND is_subscribed = 1 AND is_selectable = 1 ORDER BY id';
    $folders = execute_query_array($query, [$account_id]);
    foreach ($folders as $folder) {
        if (time_get_usage() > get_config('server/percentstop')) {
            break;
        }
        $state = $imap->SelectFolder(imap_class::Decode($folder['name']));
        if (!is_array($state)) {
            return $state;
        }
        $query = 'SELECT imap_uid, id FROM app_emails WHERE folder_id = ? AND imap_uid > 0';
        $local = array_column(execute_query_array($query, [$folder['id']]), 'id', 'imap_uid');
        $query = 'SELECT imap_uid FROM app_emails_queue WHERE folder_id = ? AND imap_uid > 0';
        $pending = array_map('intval', execute_query_array($query, [$folder['id']]));
        // With a new UIDVALIDITY, the UIDs of the emails in SaltOS are not valid
        $changed = $folder['uidvalidity'] !== null;
        $changed = $changed && intval($folder['uidvalidity']) !== $state['uidvalidity'];
        if ($changed) {
            $vanished = array_merge($vanished, array_values(array_diff(
                array_map('intval', array_values($local)),
                array_keys($frozen)
            )));
            $local = [];
        }
        $known = array_unique(array_merge(array_keys($local), $pending));
        $skip = intval($folder['uidnext']) === $state['uidnext'] && count($known) === $state['exists'];
        $skip = $skip && !$changed;
        if (!$skip) {
            $server = $imap->Search('ALL');
            if (!is_array($server)) {
                return $server;
            }
            $vanished = array_merge($vanished, array_values(array_diff(
                array_map('intval', array_values(array_diff_key($local, array_flip($server)))),
                array_keys($frozen)
            )));
            $new = array_values(array_diff($server, $known));
            if (count($new)) {
                $appeared[intval($folder['id'])] = $new;
            }
        }
        $local = array_filter($local, fn ($id) => !isset($frozen[intval($id)]));
        $error = __getmail_imap_flags($imap, $folder, $state, $local);
        if ($error !== '') {
            return $error;
        }
        $query = prepare_update_query('app_emails_folders', [
            'uidvalidity' => $state['uidvalidity'],
            'uidnext' => $state['uidnext'],
            'highestmodseq' => $state['highestmodseq'],
        ], [
            'id' => $folder['id'],
        ]);
        db_query(...$query);
    }
    return '';
}

/**
 * Match the vanished and the appeared emails
 *
 * This function matches one to one the appeared emails with the vanished emails of the
 * account, to detect the emails moved by other clients (or the emails of a folder with a
 * new UIDVALIDITY), and with the emails without UID of the same folder (moved in SaltOS
 * without learning its new UID): first by the Email-ID (OBJECTID or Gmail) and then by the
 * Message-ID and the size; the ambiguous cases (more than one candidate) are not matched,
 * so the vanished emails are deleted and the appeared emails are downloaded; the matched
 * emails get its folder, its UID and the states of its flags (except the frozen emails),
 * the UIDs learned are written in its pending operations, and the matched emails are
 * removed from the vanished and the appeared emails
 *
 * @imap     => the IMAP client connected with the server
 * @frozen   => the ids of the emails with pending operations as keys
 * @vanished => the ids of the vanished emails
 * @orphans  => the emails without UID in a folder of the account
 * @appeared => the UIDs of the appeared emails by folder id
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_match($imap, $frozen, &$vanished, $orphans, &$appeared)
{
    $locals = [];
    if (count($vanished)) {
        $query = 'SELECT id, folder_id, imap_messageid, imap_emailid, size FROM app_emails
            WHERE id IN (' . implode(',', $vanished) . ')';
        $locals = execute_query_array($query);
    }
    // The Message-ID, the Email-ID and the size of the appeared emails
    $remotes = [];
    $folders = execute_query_array('SELECT id, name FROM app_emails_folders WHERE id IN (' .
        implode(',', array_keys($appeared)) . ')');
    $items = 'FLAGS RFC822.SIZE BODY.PEEK[HEADER.FIELDS (MESSAGE-ID)]' . __getmail_imap_emailid_item($imap);
    foreach ($folders as $folder) {
        $state = $imap->SelectFolder(imap_class::Decode($folder['name']));
        if (!is_array($state)) {
            return $state;
        }
        foreach (array_chunk($appeared[$folder['id']], 500) as $uids) {
            $fetch = $imap->Fetch(implode(',', $uids), $items);
            if (!is_array($fetch)) {
                return $fetch;
            }
            foreach ($fetch as $uid => $data) {
                [$messageid, $emailid] = __getmail_imap_ids($data);
                $remotes[] = [
                    'folder_id' => intval($folder['id']),
                    'uid' => $uid,
                    'imap_messageid' => $messageid,
                    'imap_emailid' => $emailid,
                    'size' => intval($data['RFC822.SIZE'] ?? 0),
                    'states' => __getmail_imap_states($data),
                ];
            }
        }
    }
    // The matching by Email-ID and then by Message-ID and size, only one to one, the
    // emails without Email-ID or without Message-ID use an empty key, that never matches;
    // the emails without UID are matched only with the emails of its folder
    $keys = [
        fn ($item) => strval($item['imap_emailid']),
        fn ($item) => strval($item['imap_messageid']) === '' ? '' : implode('/', [
            $item['imap_messageid'],
            $item['size'],
        ]),
    ];
    // The sent emails are matched by the Message-ID only, because the copy saved by the
    // server (like Gmail) can have more headers (the remote emails are not outbox)
    $outbox = fn ($item) => ($item['is_outbox'] ?? 1) ? strval($item['imap_messageid']) : '';
    $sets = ['vanished' => $locals, 'orphans' => $orphans];
    foreach (array_keys($sets) as $set) {
        foreach ($set === 'orphans' ? [...$keys, $outbox] : $keys as $key) {
            $scoped = fn ($item) => $key($item) === '' ? '' : (
                $set === 'orphans' ? $item['folder_id'] . '/' . $key($item) : $key($item)
            );
            $groups1 = [];
            foreach ($sets[$set] as $index => $local) {
                $groups1[$scoped($local)][] = $index;
            }
            $groups2 = [];
            foreach ($remotes as $index => $remote) {
                $groups2[$scoped($remote)][] = $index;
            }
            foreach ($groups1 as $value => $indexes1) {
                $indexes2 = $groups2[$value] ?? [];
                if ($value === '' || count($indexes1) !== 1 || count($indexes2) !== 1) {
                    continue;
                }
                $local = $sets[$set][$indexes1[0]];
                $remote = $remotes[$indexes2[0]];
                $email_id = intval($local['id']);
                $fields = [
                    'folder_id' => $remote['folder_id'],
                    'imap_uid' => $remote['uid'],
                ];
                if (!isset($frozen[$email_id])) {
                    $fields = array_merge($fields, $remote['states']);
                }
                $query = prepare_update_query('app_emails', $fields, [
                    'id' => $email_id,
                ]);
                db_query(...$query);
                __getmail_imap_learn($email_id, 0, $remote['folder_id'], $remote['uid']);
                unset($sets[$set][$indexes1[0]]);
                unset($remotes[$indexes2[0]]);
                $vanished = array_values(array_diff($vanished, [$email_id]));
                $folder_id = $remote['folder_id'];
                $appeared[$folder_id] = array_values(array_diff($appeared[$folder_id], [$remote['uid']]));
            }
        }
    }
    $appeared = array_filter($appeared, fn ($uids) => count($uids) > 0);
    return '';
}

/**
 * Download the appeared emails
 *
 * This function downloads the appeared emails, the newest first of each folder and up to
 * the batch size of the config, the rest are downloaded in the next synchronizations
 *
 * @imap       => the IMAP client connected with the server
 * @account_id => the id of the account
 * @appeared   => the UIDs of the appeared emails by folder id
 * @newemail   => the counter of the new emails, incremented with the downloaded emails
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_download($imap, $account_id, $appeared, &$newemail)
{
    $batch = intval(get_config('emails/imapbatch'));
    foreach ($appeared as $folder_id => $uids) {
        if ($batch <= 0 || time_get_usage() > get_config('server/percentstop')) {
            break;
        }
        $name = execute_query('SELECT name FROM app_emails_folders WHERE id = ?', [$folder_id]);
        $state = $imap->SelectFolder(imap_class::Decode($name));
        if (!is_array($state)) {
            return $state;
        }
        rsort($uids);
        foreach (array_slice($uids, 0, $batch) as $uid) {
            if (time_get_usage() > get_config('server/percentstop')) {
                break;
            }
            $error = __getmail_imap_message($imap, $account_id, $folder_id, $uid);
            if ($error !== '') {
                return $error;
            }
            $newemail++;
            $batch--;
        }
    }
    return '';
}

/**
 * Download an email
 *
 * This function downloads an email of the selected folder, stores it in the inbox directory
 * of the account using a new uidl generated by SaltOS, that never changes, and inserts it
 * with its folder, its UID, its Message-ID, its Email-ID (OBJECTID or Gmail) and the states
 * of its flags
 *
 * @imap       => the IMAP client connected with the server
 * @account_id => the id of the account
 * @folder_id  => the id of the selected folder
 * @uid        => the UID of the email in the selected folder
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_message($imap, $account_id, $folder_id, $uid)
{
    $items = 'FLAGS BODY.PEEK[HEADER.FIELDS (MESSAGE-ID)] BODY.PEEK[]' . __getmail_imap_emailid_item($imap);
    $fetch = $imap->Fetch(strval($uid), $items);
    if (!is_array($fetch)) {
        return $fetch;
    }
    // The email can be removed by another client after the search
    if (!isset($fetch[$uid]['BODY[]'])) {
        return '';
    }
    $data = $fetch[$uid];
    // The file of the email, with a new uidl that not exists in the account
    $prefix = get_directory('dirs/inboxdir') . $account_id;
    if (!file_exists($prefix)) {
        mkdir($prefix);
        chmod_protected($prefix, 0777);
    }
    do {
        $uidl = strtoupper(bin2hex(random_bytes(8)));
        $file = $prefix . '/' . $uidl . '.eml.gz';
    } while (file_exists($file));
    $fp = gzopen($file, 'w');
    gzwrite($fp, strval($data['BODY[]']));
    gzclose($fp);
    chmod_protected($file, 0666);
    unset($data['BODY[]']); // Trick to release memory
    [$messageid, $emailid] = __getmail_imap_ids($data);
    __getmail_insert($file, "$account_id/$uidl", array_merge(__getmail_imap_states($data), [
        'folder_id' => $folder_id,
        'imap_uid' => $uid,
        'imap_messageid' => $messageid,
        'imap_emailid' => $emailid,
    ]));
    return '';
}

/**
 * Email-ID item
 *
 * This function returns the item of the FETCH command used to get the Email-ID of the
 * emails, a stable id that not changes when the email is moved: EMAILID (OBJECTID) or
 * X-GM-MSGID (Gmail), or an empty string if the server not supports any of them
 *
 * @imap => the IMAP client connected with the server
 *
 * Returns the item with a leading space or an empty string
 */
function __getmail_imap_emailid_item($imap)
{
    if ($imap->HasCapability('OBJECTID')) {
        return ' EMAILID';
    }
    if ($imap->HasCapability('X-GM-EXT-1')) {
        return ' X-GM-MSGID';
    }
    return '';
}

/**
 * Message-ID and Email-ID
 *
 * This function returns the Message-ID (from the header returned by the FETCH command,
 * unfolding the header lines) and the Email-ID (EMAILID or X-GM-MSGID) of an email
 *
 * @data => the items of the email returned by the FETCH command
 *
 * Returns an array with the Message-ID and the Email-ID, empty strings if not found
 */
function __getmail_imap_ids($data)
{
    $messageid = '';
    foreach ($data as $key => $value) {
        if (strncmp($key, 'BODY[HEADER', 11) === 0) {
            $header = preg_replace("~\r?\n[ \t]+~", ' ', strval($value));
            if (preg_match('~^Message-ID:\s*(\S+)~im', $header, $matches)) {
                $messageid = $matches[1];
            }
        }
    }
    $emailid = $data['EMAILID'] ?? $data['X-GM-MSGID'] ?? '';
    if (is_array($emailid)) {
        $emailid = $emailid[0] ?? '';
    }
    return [$messageid, strval($emailid)];
}

/**
 * Synchronize the flags
 *
 * This function updates the states of the emails of the selected folder with its flags in
 * the server, that wins: with CONDSTORE only the emails changed since the HIGHESTMODSEQ of
 * the last synchronization (nothing if it is the same), otherwise all the emails
 *
 * @imap   => the IMAP client connected with the server
 * @folder => the row of the selected folder, with the HIGHESTMODSEQ of the last
 *            synchronization
 * @state  => the state of the selected folder returned by the SELECT command
 * @local  => the ids of the emails of the folder in SaltOS by UID
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_flags($imap, $folder, $state, $local)
{
    if (!count($local) || !$state['exists']) {
        return '';
    }
    $since = '';
    if ($state['highestmodseq'] !== '' && strval($folder['highestmodseq']) !== '') {
        if ($state['highestmodseq'] === $folder['highestmodseq']) {
            return '';
        }
        $since = $folder['highestmodseq'];
    }
    $fetch = $imap->Fetch('1:*', 'FLAGS', $since);
    if (!is_array($fetch)) {
        return $fetch;
    }
    $fetch = array_intersect_key($fetch, $local);
    if (!count($fetch)) {
        return '';
    }
    $query = 'SELECT imap_uid, state_new, state_reply, state_forward, state_wait FROM app_emails
        WHERE id IN (' . implode(',', array_intersect_key($local, $fetch)) . ')';
    $rows = array_column(execute_query_array($query), null, 'imap_uid');
    foreach ($fetch as $uid => $data) {
        $states = __getmail_imap_states($data);
        $current = array_map('intval', array_intersect_key($rows[$uid], $states));
        if ($current === $states) {
            continue;
        }
        $query = prepare_update_query('app_emails', $states, [
            'id' => $local[$uid],
        ]);
        db_query(...$query);
    }
    return '';
}

/**
 * States of the flags
 *
 * This function returns the states of an email from its flags: state_new is not \Seen,
 * state_reply is \Answered, state_forward is $Forwarded and state_wait is \Flagged
 *
 * @data => the items of the email returned by the FETCH command, with the FLAGS item
 *
 * Returns an array with the states
 */
function __getmail_imap_states($data)
{
    $flags = array_map('strtolower', is_array($data['FLAGS'] ?? null) ? $data['FLAGS'] : []);
    return [
        'state_new' => in_array('\\seen', $flags, true) ? 0 : 1,
        'state_reply' => in_array('\\answered', $flags, true) ? 1 : 0,
        'state_forward' => in_array('$forwarded', $flags, true) ? 1 : 0,
        'state_wait' => in_array('\\flagged', $flags, true) ? 1 : 0,
    ];
}

/**
 * Enqueue an operation
 *
 * This function adds an operation done in SaltOS to the queue of the account of the email,
 * to be applied in the server by the next synchronization: the operation acts on the email
 * in the folder where it is now in SaltOS, with its UID if known, or without UID (0) if it
 * depends on a pending move of the same email; the operations on emails that never were in
 * the server (without UID and without pending operations) are not enqueued, except the
 * append, that puts them in the server; the UIDs 0 and
 * NULL mean unknown UID, because the IMAP UIDs start at 1 and the insert and update helpers
 * store the NULL values of the integer fields as 0
 *
 * @email_id => the id of the email
 * @action   => the operation: flag_add, flag_remove, move, expunge or append
 * @fields   => the other fields of the operation: flag (flag_add and flag_remove) or
 *              to_folder_id (move and append)
 *
 * Returns true if the operation is enqueued, false otherwise
 */
function __getmail_imap_enqueue($email_id, $action, $fields = [])
{
    $query = 'SELECT account_id, folder_id, imap_uid FROM app_emails WHERE id = ?';
    $email = execute_query($query, [$email_id]);
    if (!is_array($email)) {
        return false;
    }
    $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE email_id = ?';
    if ($action !== 'append' && !intval($email['imap_uid']) && !execute_query($query, [$email_id])) {
        return false;
    }
    $query = prepare_insert_query('app_emails_queue', array_merge([
        'account_id' => $email['account_id'],
        'email_id' => $email_id,
        'action' => $action,
        'folder_id' => $email['folder_id'],
        'imap_uid' => intval($email['imap_uid']),
        'datetime' => current_datetime(),
        'retries' => 0,
        'error' => '',
    ], $fields));
    db_query(...$query);
    return true;
}

/**
 * Enqueue a change of state
 *
 * This function enqueues the flag of a change of state of the emails of the IMAP accounts
 * that change: state_new is \Seen (inverted), state_wait is \Flagged, state_reply is
 * \Answered and state_forward is $Forwarded; it must be called before the change is done
 * in SaltOS, it is an internal helper that not checks the permissions
 *
 * @ids   => the ids of the emails, separated by commas or as array
 * @field => the state that changes
 * @value => the new value of the state, 0 or 1
 */
function __getmail_imap_state($ids, $field, $value)
{
    $flags = [
        'state_new' => '\Seen',
        'state_wait' => '\Flagged',
        'state_reply' => '\Answered',
        'state_forward' => '$Forwarded',
    ];
    if (!isset($flags[$field])) {
        show_php_error(['phperror' => "Unknown state $field"]);
    }
    $ids = check_ids($ids);
    $add = $field === 'state_new' ? !$value : (bool)$value;
    $query = "SELECT id FROM app_emails WHERE id IN ($ids) AND $field != ? AND account_id IN (
        SELECT id FROM app_emails_accounts WHERE protocol = 'imap')";
    foreach (execute_query_array($query, [intval($value)]) as $id) {
        __getmail_imap_enqueue(intval($id), $add ? 'flag_add' : 'flag_remove', [
            'flag' => $flags[$field],
        ]);
    }
}

/**
 * Enqueue a move
 *
 * This function enqueues the move of the emails of the IMAP accounts to another folder of
 * its account, and sets its UID as unknown (the UID in the destination folder is learned
 * when the move is applied); it must be called before the folder of the emails is changed
 * in SaltOS, it is an internal helper that not checks the permissions
 *
 * @ids       => the ids of the emails, separated by commas or as array
 * @folder_id => the id of the destination folder
 */
function __getmail_imap_move($ids, $folder_id)
{
    $ids = check_ids($ids);
    $query = "SELECT id FROM app_emails WHERE id IN ($ids) AND folder_id != ? AND account_id IN (
        SELECT id FROM app_emails_accounts WHERE protocol = 'imap')";
    $moved = execute_query_array($query, [intval($folder_id)]);
    foreach ($moved as $id) {
        __getmail_imap_enqueue(intval($id), 'move', ['to_folder_id' => intval($folder_id)]);
    }
    if (count($moved)) {
        db_query('UPDATE app_emails SET imap_uid = 0 WHERE id IN (' . check_ids($moved) . ')');
    }
}

/**
 * Enqueue an append
 *
 * This function enqueues the append of a sent email of an IMAP account in the sent folder
 * of the server (the folder of the email in SaltOS), if the copy in the sent folder is
 * enabled in the account (disabled for the servers that save the copy itself, like Gmail,
 * whose copy is adopted by the synchronization); it is an internal helper that not checks
 * the permissions
 *
 * @email_id => the id of the email
 */
function __getmail_imap_append($email_id)
{
    $query = "SELECT folder_id FROM app_emails WHERE id = ? AND folder_id > 0 AND account_id IN (
        SELECT id FROM app_emails_accounts WHERE protocol = 'imap' AND imap_append_sent = 1)";
    $folder_id = intval(execute_query($query, [$email_id]));
    if ($folder_id) {
        __getmail_imap_enqueue(intval($email_id), 'append', ['to_folder_id' => $folder_id]);
    }
}

/**
 * Enqueue an expunge
 *
 * This function enqueues the permanent delete of the emails of the IMAP accounts in the
 * server; it must be called before the emails are deleted in SaltOS, it is an internal
 * helper that not checks the permissions
 *
 * @ids => the ids of the emails separated by commas
 */
function __getmail_imap_expunge($ids)
{
    $ids = check_ids($ids);
    $query = "SELECT id FROM app_emails WHERE id IN ($ids) AND account_id IN (
        SELECT id FROM app_emails_accounts WHERE protocol = 'imap')";
    foreach (execute_query_array($query) as $id) {
        __getmail_imap_enqueue(intval($id), 'expunge');
    }
}

/**
 * Apply the queue
 *
 * This function applies the pending operations of the account in order: each operation is
 * removed when done or when the email not exists in the server; the operations without UID
 * wait until a previous move learns it (or until the synchronization finds it), and after
 * an error returned by the server the operation is retried in the next synchronization up
 * to the maximum retries of the config (then it is removed); in both cases the next
 * operations of the same email wait to keep the order; an error of the connection stops
 * the synchronization
 *
 * @imap       => the IMAP client connected with the server
 * @account_id => the id of the account
 *
 * Returns an empty string on success or the error message of the connection
 */
function __getmail_imap_queue($imap, $account_id)
{
    $query = 'SELECT * FROM app_emails_queue WHERE account_id = ? ORDER BY id';
    $rows = execute_query_array($query, [$account_id]);
    $query = 'SELECT id, name FROM app_emails_folders WHERE account_id = ?';
    $names = array_column(execute_query_array($query, [$account_id]), 'name', 'id');
    $waiting = [];
    $selected = '';
    foreach ($rows as $row) {
        if (time_get_usage() > get_config('server/percentstop')) {
            break;
        }
        $email_id = intval($row['email_id']);
        if (isset($waiting[$email_id])) {
            continue;
        }
        if ($row['action'] === 'append') {
            $error = __getmail_imap_queue_append($imap, $row, $names, $selected);
            $error = __getmail_imap_queue_result($imap, $row, $error, $waiting);
            if ($error !== '') {
                return $error;
            }
            continue;
        }
        // The UID learned by a previous move of this synchronization, or the UID of the
        // email if it is in the same folder
        $uid = intval(execute_query('SELECT imap_uid FROM app_emails_queue WHERE id = ?', [$row['id']]));
        if (!$uid) {
            $query = 'SELECT imap_uid FROM app_emails WHERE id = ? AND folder_id = ?';
            $uid = intval(execute_query($query, [$email_id, $row['folder_id']]));
        }
        if (!$uid) {
            $waiting[$email_id] = 1;
            continue;
        }
        // The email not exists in the server, also if its folder was removed (the
        // to_folder_id is NULL in the operations that are not moves)
        $folder = $names[intval($row['folder_id'])] ?? '';
        $to_folder = $names[intval($row['to_folder_id'])] ?? '';
        $error = '';
        $found = [];
        if ($folder !== '' && ($row['action'] !== 'move' || $to_folder !== '')) {
            if ($selected !== $folder) {
                $state = $imap->SelectFolder(imap_class::Decode($folder));
                $error = is_array($state) ? '' : $state;
                $selected = $error === '' ? $folder : '';
            }
            if ($error === '') {
                $found = $imap->Search("UID $uid");
                $error = is_array($found) ? '' : $found;
            }
        }
        if ($error === '' && !count($found)) {
            db_query('DELETE FROM app_emails_queue WHERE id = ?', [$row['id']]);
            continue;
        }
        if ($error === '') {
            $error = match ($row['action']) {
                'flag_add' => $imap->Store(strval($uid), '+', [$row['flag']]),
                'flag_remove' => $imap->Store(strval($uid), '-', [$row['flag']]),
                'move' => __getmail_imap_queue_move($imap, $row, $uid, $to_folder, $selected),
                'expunge' => $imap->Expunge(strval($uid)),
                default => 'Unknown action ' . $row['action'],
            };
        }
        $error = __getmail_imap_queue_result($imap, $row, $error, $waiting);
        if ($error !== '') {
            return $error;
        }
    }
    return '';
}

/**
 * Result of an operation of the queue
 *
 * This function removes the operation when it is done; after an error returned by the
 * server, the operation is retried in the next synchronization up to the maximum retries
 * of the config (then it is removed and logged) and the next operations of the email wait
 *
 * @imap    => the IMAP client connected with the server
 * @row     => the operation of the queue
 * @error   => the result of the operation, empty string on success
 * @waiting => the ids of the emails whose operations wait as keys
 *
 * Returns the error message if it is an error of the connection, otherwise an empty string
 */
function __getmail_imap_queue_result($imap, $row, $error, &$waiting)
{
    $email_id = intval($row['email_id']);
    if ($error === '') {
        db_query('DELETE FROM app_emails_queue WHERE id = ?', [$row['id']]);
        return '';
    }
    if (!$imap->Connected()) {
        return $error;
    }
    // The errors returned by the server, after the maximum retries the operation is
    // removed and the next operations of the email can be applied
    $retries = intval($row['retries']) + 1;
    if ($retries >= intval(get_config('emails/imapretries'))) {
        db_query('DELETE FROM app_emails_queue WHERE id = ?', [$row['id']]);
        addlog("IMAP operation {$row['action']} of the email $email_id removed: $error");
        return '';
    }
    $waiting[$email_id] = 1;
    $query = prepare_update_query('app_emails_queue', [
        'retries' => $retries,
        'error' => $error,
    ], [
        'id' => $row['id'],
    ]);
    db_query(...$query);
    return '';
}

/**
 * Append an email of the queue
 *
 * This function puts a sent email in the sent folder of the server: if the email has an
 * UID it is already in the server (adopted by the synchronization); otherwise the
 * Message-ID is searched in the folder and, if it is not found, the email is appended with
 * the \Seen flag; the UID found or returned by the server (UIDPLUS) is learned, otherwise the
 * next synchronization adopts the email
 *
 * @imap     => the IMAP client connected with the server
 * @row      => the operation of the queue
 * @names    => the full names of the folders of the account by id
 * @selected => the full name of the selected folder, updated if the folder is selected
 *
 * Returns an empty string when done or the error message
 */
function __getmail_imap_queue_append($imap, $row, $names, &$selected)
{
    $query = 'SELECT account_id, uidl, imap_uid, imap_messageid FROM app_emails WHERE id = ?';
    $email = execute_query($query, [$row['email_id']]);
    $folder = $names[intval($row['to_folder_id'])] ?? '';
    if (!is_array($email) || intval($email['imap_uid']) || $folder === '') {
        return '';
    }
    $state = $imap->SelectFolder(imap_class::Decode($folder));
    if (!is_array($state)) {
        return $state;
    }
    $selected = $folder;
    $uid = 0;
    $messageid = strval($email['imap_messageid']);
    if ($messageid !== '') {
        $found = $imap->Search('HEADER Message-ID ' . imap_class::Quote($messageid));
        if (!is_array($found)) {
            return $found;
        }
        if (count($found) === 1) {
            $uid = $found[0];
        }
    }
    if (!$uid) {
        $file = get_directory('dirs/outboxdir') . $email['account_id'] . '/' . $email['uidl'] . '.eml.gz';
        if (!file_exists($file)) {
            return '';
        }
        $message = file_get_contents('compress.zlib://' . $file);
        $error = $imap->Append(imap_class::Decode($folder), $message, ['\Seen'], $uid);
        if ($error !== '') {
            return $error;
        }
    }
    if ($uid) {
        $email_id = intval($row['email_id']);
        __getmail_imap_learn($email_id, intval($row['id']), intval($row['to_folder_id']), $uid);
    }
    return '';
}


/**
 * Move an email of the queue
 *
 * This function moves the email to the destination folder and learns its new UID from
 * the server (UIDPLUS) or searching its Message-ID in the destination folder when it is
 * unique; the new UID is written in the next operations of the email up to its next move
 * (they act on the destination folder), and in the email if there is no next move; if the
 * new UID is not learned, the next operations wait until the synchronization finds it
 * matching the email without UID with the appeared emails of the destination folder
 *
 * @imap      => the IMAP client connected with the server
 * @row       => the operation of the queue
 * @uid       => the UID of the email in the selected folder
 * @to_folder => the full name of the destination folder
 * @selected  => the full name of the selected folder, updated if the destination folder
 *               is selected to search the Message-ID
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_queue_move(imap_class $imap, $row, $uid, $to_folder, &$selected)
{
    $error = $imap->Move(strval($uid), imap_class::Decode($to_folder), $map);
    if ($error !== '') {
        return $error;
    }
    $newuid = $map[$uid] ?? null;
    $query = 'SELECT imap_messageid FROM app_emails WHERE id = ?';
    $messageid = strval(execute_query($query, [$row['email_id']]));
    if ($newuid === null && $messageid !== '') {
        $state = $imap->SelectFolder(imap_class::Decode($to_folder));
        if (!is_array($state)) {
            return $state;
        }
        $selected = $to_folder;
        $found = $imap->Search('HEADER Message-ID ' . imap_class::Quote($messageid));
        if (!is_array($found)) {
            return $found;
        }
        if (count($found) === 1) {
            $newuid = $found[0];
        }
    }
    if ($newuid !== null) {
        $email_id = intval($row['email_id']);
        __getmail_imap_learn($email_id, intval($row['id']), intval($row['to_folder_id']), $newuid);
    }
    return '';
}

/**
 * Learn an UID
 *
 * This function writes the new UID of an email, in a folder, in the pending operations of
 * the email after an operation (they act on that folder) up to the next move of the email,
 * and in the email if there is no next move and it is in that folder
 *
 * @email_id  => the id of the email
 * @after     => the id of the operation that moved the email, 0 to start from the first
 * @folder_id => the id of the folder of the new UID
 * @uid       => the new UID
 */
function __getmail_imap_learn($email_id, $after, $folder_id, $uid)
{
    $query = 'SELECT MIN(id) FROM app_emails_queue WHERE email_id = ? AND id > ? AND action = ?';
    $next = execute_query($query, [$email_id, $after, 'move']);
    $query = 'UPDATE app_emails_queue SET imap_uid = ?
        WHERE email_id = ? AND id > ? AND id <= ? AND IFNULL(imap_uid, 0) = 0';
    db_query($query, [$uid, $email_id, $after, $next ?? PHP_INT_MAX]);
    if ($next === null) {
        $query = 'UPDATE app_emails SET imap_uid = ? WHERE id = ? AND folder_id = ?';
        db_query($query, [$uid, $email_id, $folder_id]);
    }
}

/**
 * Full name of a folder
 *
 * This function returns the full name in the server (UTF-8) of a folder with the label
 * inside the parent folder, using the delimiter of the parent folder or, for the root
 * folders, the delimiter of the INBOX (or of any folder of the account)
 *
 * @account_id => the id of the account
 * @parent_id  => the id of the parent folder, 0 for the root
 * @label      => the label of the folder
 *
 * Returns an array with the full name and the delimiter
 */
function __getmail_imap_folder_name($account_id, $parent_id, $label)
{
    $query = 'SELECT name, delimiter FROM app_emails_folders WHERE id = ? AND account_id = ?';
    $parent = execute_query($query, [$parent_id, $account_id]);
    if (is_array($parent)) {
        $delimiter = strval($parent['delimiter']);
        return [imap_class::Decode($parent['name']) . $delimiter . $label, $delimiter];
    }
    $query = "SELECT delimiter FROM app_emails_folders WHERE account_id = ? AND delimiter != ''
        ORDER BY CASE WHEN name = 'INBOX' THEN 0 ELSE 1 END, id";
    $delimiter = execute_query_array($query, [$account_id])[0] ?? '';
    return [$label, strval($delimiter)];
}

/**
 * Create a folder in the server
 *
 * This function creates and subscribes the folder in the server and, if it is done, adds
 * the folder in SaltOS; it is an internal helper that not checks the permissions
 *
 * @account_id => the id of the account
 * @parent_id  => the id of the parent folder, 0 for the root
 * @label      => the label of the new folder
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_folder_create($account_id, $parent_id, $label)
{
    require_once 'apps/emails/php/imap.php';
    [$name, $delimiter] = __getmail_imap_folder_name($account_id, $parent_id, $label);
    if ($delimiter !== '' && strpos($label, $delimiter) !== false) {
        return sprintf(T('The name of the folder can not contain the character %s'), $delimiter);
    }
    $account = execute_query('SELECT * FROM app_emails_accounts WHERE id = ?', [$account_id]);
    $imap = __getmail_imap_client($account);
    $error = $imap->Open();
    if ($error === '') {
        $error = $imap->CreateFolder($name);
    }
    if ($error === '') {
        $error = $imap->SubscribeFolder($name);
    }
    $imap->Close();
    if ($error !== '') {
        return sprintf(T('The server returned an error: %s'), $error);
    }
    $query = prepare_insert_query('app_emails_folders', [
        'account_id' => $account_id,
        'parent_id' => $parent_id,
        'name' => imap_class::Encode($name),
        'label' => $label,
        'delimiter' => $delimiter,
        'special_use' => '',
    ]);
    db_query(...$query);
    return '';
}

/**
 * Rename or move a folder in the server
 *
 * This function renames the folder in the server, with the new label or inside the new
 * parent folder, and, if it is done, updates the folder and the full names of its
 * subfolders in SaltOS; it is an internal helper that not checks the permissions
 *
 * @folder    => the row of the folder
 * @parent_id => the id of the new parent folder, 0 for the root
 * @label     => the new label of the folder
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_folder_rename($folder, $parent_id, $label)
{
    require_once 'apps/emails/php/imap.php';
    $account_id = intval($folder['account_id']);
    [$name, $delimiter] = __getmail_imap_folder_name($account_id, $parent_id, $label);
    if ($delimiter !== '' && strpos($label, $delimiter) !== false) {
        return sprintf(T('The name of the folder can not contain the character %s'), $delimiter);
    }
    $account = execute_query('SELECT * FROM app_emails_accounts WHERE id = ?', [$account_id]);
    $imap = __getmail_imap_client($account);
    $error = $imap->Open();
    if ($error === '') {
        $error = $imap->RenameFolder(imap_class::Decode($folder['name']), $name);
    }
    $imap->Close();
    if ($error !== '') {
        return sprintf(T('The server returned an error: %s'), $error);
    }
    // The subfolders keep its parents but its full names change
    $old = $folder['name'];
    $new = imap_class::Encode($name);
    require_once 'apps/emails/php/folders.php';
    $ids = __emails_folders_tree_ids($account_id, intval($folder['id']));
    $query = 'SELECT id, name FROM app_emails_folders WHERE id IN (' . implode(',', $ids) . ')';
    foreach (execute_query_array($query) as $row) {
        $query = prepare_update_query('app_emails_folders', [
            'name' => $new . substr($row['name'], strlen($old)),
        ], [
            'id' => $row['id'],
        ]);
        db_query(...$query);
    }
    $query = prepare_update_query('app_emails_folders', [
        'parent_id' => $parent_id,
        'label' => $label,
    ], [
        'id' => $folder['id'],
    ]);
    db_query(...$query);
    return '';
}

/**
 * Delete a folder in the server
 *
 * This function deletes the folder and its subfolders in the server: first applies the
 * queue of the account, then moves the emails of each folder to the trash (the emails in
 * SaltOS get the trash and its new UIDs, or an unknown UID that the next synchronization
 * learns), and then deletes the folders, the subfolders first; each folder deleted in the
 * server is deleted in SaltOS; it is an internal helper that not checks the permissions
 *
 * @folder => the row of the folder
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_folder_delete($folder)
{
    require_once 'apps/emails/php/imap.php';
    require_once 'apps/emails/php/folders.php';
    $account_id = intval($folder['account_id']);
    $query = 'SELECT id, name FROM app_emails_folders WHERE account_id = ? AND special_use = ?';
    $trash = execute_query($query, [$account_id, '\Trash']);
    if (!is_array($trash)) {
        return T('The account has no trash folder');
    }
    $ids = __emails_folders_tree_ids($account_id, intval($folder['id']));
    $query = 'SELECT id, name, is_selectable FROM app_emails_folders
        WHERE id IN (' . implode(',', $ids) . ')';
    $rows = array_column(execute_query_array($query), null, 'id');
    $account = execute_query('SELECT * FROM app_emails_accounts WHERE id = ?', [$account_id]);
    $imap = __getmail_imap_client($account);
    $error = $imap->Open();
    if ($error === '') {
        $error = __getmail_imap_queue($imap, $account_id);
    }
    // The emails of each folder to the trash
    foreach ($ids as $id) {
        if ($error !== '' || !$rows[$id]['is_selectable']) {
            continue;
        }
        $state = $imap->SelectFolder(imap_class::Decode($rows[$id]['name']));
        $error = is_array($state) ? '' : $state;
        if ($error !== '' || !$state['exists']) {
            continue;
        }
        $error = $imap->Move('1:*', imap_class::Decode($trash['name']), $map);
        if ($error !== '') {
            continue;
        }
        $query = 'SELECT id, imap_uid FROM app_emails WHERE folder_id = ?';
        foreach (execute_query_array($query, [$id]) as $email) {
            $query = prepare_update_query('app_emails', [
                'folder_id' => $trash['id'],
                'imap_uid' => $map[intval($email['imap_uid'])] ?? 0,
            ], [
                'id' => $email['id'],
            ]);
            db_query(...$query);
        }
    }
    // The folders, the subfolders first, with the trash selected because some servers (like
    // Dovecot) close the connection when the selected folder is deleted
    if ($error === '') {
        $state = $imap->SelectFolder(imap_class::Decode($trash['name']));
        $error = is_array($state) ? '' : $state;
    }
    foreach (array_reverse($ids) as $id) {
        if ($error !== '') {
            break;
        }
        $error = $imap->DeleteFolder(imap_class::Decode($rows[$id]['name']));
        if ($error === '') {
            $query = 'UPDATE app_emails SET folder_id = ?, imap_uid = 0 WHERE folder_id = ?';
            db_query($query, [$trash['id'], $id]);
            db_query('DELETE FROM app_emails_folders WHERE id = ?', [$id]);
        }
    }
    $imap->Close();
    if ($error !== '') {
        return sprintf(T('The server returned an error: %s'), $error);
    }
    return '';
}

/**
 * Refresh the folders
 *
 * This function connects with the server and synchronizes the folders of the account,
 * used by the dialog of the folders of the server; it is an internal helper that not
 * checks the permissions
 *
 * @account_id => the id of the account
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_folders_refresh($account_id)
{
    $account = execute_query('SELECT * FROM app_emails_accounts WHERE id = ?', [$account_id]);
    $imap = __getmail_imap_client($account);
    $error = $imap->Open();
    if ($error === '') {
        $error = __getmail_imap_folders($imap, intval($account_id));
    }
    $imap->Close();
    if ($error !== '') {
        return sprintf(T('The server returned an error: %s'), $error);
    }
    return '';
}

/**
 * Subscribe and unsubscribe folders
 *
 * This function subscribes the folders in the server and in SaltOS (its emails are
 * downloaded by the next synchronizations) and unsubscribes the folders in SaltOS only,
 * deleting its emails and its pending operations in SaltOS (the emails stay in the server);
 * it is an internal helper that not checks the permissions
 *
 * @account_id  => the id of the account
 * @subscribe   => the ids of the folders to subscribe
 * @unsubscribe => the ids of the folders to unsubscribe
 *
 * Returns an empty string on success or the error message
 */
function __getmail_imap_subscribe($account_id, $subscribe, $unsubscribe)
{
    if (count($subscribe)) {
        $query = 'SELECT id, name FROM app_emails_folders WHERE id IN (' . check_ids($subscribe) . ')';
        $folders = execute_query_array($query);
        $account = execute_query('SELECT * FROM app_emails_accounts WHERE id = ?', [$account_id]);
        $imap = __getmail_imap_client($account);
        $error = $imap->Open();
        foreach ($folders as $folder) {
            if ($error === '') {
                $error = $imap->SubscribeFolder(imap_class::Decode($folder['name']));
            }
            if ($error === '') {
                db_query('UPDATE app_emails_folders SET is_subscribed = 1 WHERE id = ?', [$folder['id']]);
            }
        }
        $imap->Close();
        if ($error !== '') {
            return sprintf(T('The server returned an error: %s'), $error);
        }
    }
    foreach ($unsubscribe as $folder_id) {
        $ids = execute_query_array('SELECT id FROM app_emails WHERE folder_id = ?', [$folder_id]);
        if (count($ids)) {
            db_query('DELETE FROM app_emails_queue WHERE email_id IN (' . check_ids($ids) . ')');
            __getmail_delete(check_ids($ids));
        }
        db_query('UPDATE app_emails_folders SET is_subscribed = 0 WHERE id = ?', [$folder_id]);
    }
    return '';
}
