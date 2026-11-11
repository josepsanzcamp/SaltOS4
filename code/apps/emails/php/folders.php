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
 * Emails folders
 *
 * This file contains the functions used to manage the folders of the emails accounts
 */

/**
 * Check the folders of an account
 *
 * This function checks the folders of the account, creating the mandatory folders (inbox,
 * sent, trash and junk) of the POP3 accounts when they not exists; too removes the folders
 * of the deleted accounts
 *
 * @account_id => the id of the account
 *
 * Returns an array with the ids of the mandatory folders indexed by special use, or an
 * empty array if the account not exists
 */
function emails_folders_check($account_id)
{
    $account_id = intval($account_id);
    // Remove the folders of the deleted accounts
    $query = 'DELETE FROM app_emails_folders WHERE account_id NOT IN (SELECT id FROM app_emails_accounts)';
    db_query($query);
    // Check that the account exists
    $query = 'SELECT protocol FROM app_emails_accounts WHERE id = ?';
    $protocol = execute_query($query, [$account_id]);
    if (!$protocol) {
        return [];
    }
    // Create the mandatory folders that not exists, the folders of the IMAP accounts are
    // created by the synchronization with the server
    $folders = [
        '\Inbox' => 'Inbox',
        '\Sent' => 'Sent',
        '\Trash' => 'Trash',
        '\Junk' => 'Junk',
    ];
    $query = "SELECT special_use, id FROM app_emails_folders WHERE account_id = ? AND special_use != ''";
    $ids = array_column(execute_query_array($query, [$account_id]), 'id', 'special_use');
    foreach ($folders as $special_use => $label) {
        if (isset($ids[$special_use]) || $protocol === 'imap') {
            continue;
        }
        $query = prepare_insert_query('app_emails_folders', [
            'account_id' => $account_id,
            'parent_id' => 0,
            'label' => $label,
            'special_use' => $special_use,
        ]);
        db_query(...$query);
        $ids[$special_use] = db_last_insert_id();
    }
    return array_intersect_key($ids, $folders);
}

/**
 * Insert an account
 *
 * This function inserts a new emails account using the generic insert action and creates
 * the mandatory folders of the new account
 *
 * @json => the data of the new account
 *
 * Returns the result of the insert action
 */
function emails_accounts_insert($json)
{
    require_once 'php/lib/actions.php';
    $result = insert('emails_accounts', $json);
    if ($result['status'] === 'ok') {
        emails_folders_check($result['created_id']);
    }
    return $result;
}

/**
 * Folders tree
 *
 * This function returns the tree of folders of the accounts of the current user, using the
 * format of the jstree widget: one node for each account with its folders as children, the
 * id of each node contains the account and the folder separated by a slash, using the
 * folder 0 for the nodes of the accounts, the enabled accounts are shown first and opened,
 * and the disabled accounts are shown at the end and closed
 *
 * @ids => the ids of the emails used to limit the tree to its accounts (optional)
 *
 * Returns an array with the nodes of the tree
 */
function emails_folders_tree($ids = '')
{
    $query = 'SELECT id, email_from label, email_disabled FROM app_emails_accounts WHERE user_id = ?';
    if (strval($ids) !== '') {
        $ids = check_ids($ids);
        $query .= " AND id IN (SELECT account_id FROM app_emails WHERE id IN ($ids))";
    }
    $query .= ' ORDER BY email_disabled ASC, label ASC';
    $accounts = execute_query_array($query, [current_user()]);
    $tree = [];
    foreach ($accounts as $account) {
        emails_folders_check($account['id']);
        $query = 'SELECT id, parent_id, label, special_use, is_subscribed FROM app_emails_folders
            WHERE account_id = ? ORDER BY id ASC';
        $folders = execute_query_array($query, [$account['id']]);
        $tree[] = [
            'id' => $account['id'] . '/0',
            'text' => $account['label'],
            'state' => ['opened' => !$account['email_disabled']],
            'children' => __emails_folders_tree_helper($account['id'], $folders, 0),
        ];
    }
    return $tree;
}

/**
 * Folders tree helper
 *
 * This function returns the nodes of the folders that are children of the parent folder,
 * the names of the special folders are translated
 *
 * @account_id => the id of the account
 * @folders    => the array with all folders of the account
 * @parent_id  => the id of the parent folder
 *
 * Returns an array with the nodes of the children folders
 */
function __emails_folders_tree_helper($account_id, $folders, $parent_id)
{
    $texts = [
        '\Inbox' => 'Inbox',
        '\Sent' => 'Sent emails',
        '\Trash' => 'Trash',
        '\Junk' => 'Junk',
    ];
    $nodes = [];
    foreach ($folders as $folder) {
        if (intval($folder['parent_id']) !== $parent_id) {
            continue;
        }
        $text = $folder['label'];
        if (isset($texts[$folder['special_use']])) {
            $text = T($texts[$folder['special_use']]);
        }
        // The folders not subscribed (IMAP) are shown only if they have subscribed subfolders
        $children = __emails_folders_tree_helper($account_id, $folders, intval($folder['id']));
        if (!$folder['is_subscribed'] && !count($children)) {
            continue;
        }
        $nodes[] = [
            'id' => $account_id . '/' . $folder['id'],
            'text' => $text,
            'state' => ['opened' => true],
            'children' => $children,
        ];
    }
    return $nodes;
}

/**
 * Move emails to a folder
 *
 * This function moves the emails to the folder of its account identified by the special
 * use, it is an internal helper that not checks the permissions, the callers must check
 * that the emails are of the current user; the moves of the emails of the IMAP accounts
 * are enqueued, and the emails of the IMAP accounts without that folder are not moved
 *
 * @ids         => the ids of the emails separated by commas
 * @special_use => the special use of the destination folder
 */
function __emails_folders_move($ids, $special_use)
{
    $ids = check_ids($ids);
    $query = "SELECT DISTINCT account_id FROM app_emails WHERE id IN ($ids)";
    $accounts = execute_query_array($query);
    require_once 'apps/emails/php/getmail_imap.php';
    foreach ($accounts as $account_id) {
        $folder_id = emails_folders_check($account_id)[$special_use] ?? 0;
        if (!$folder_id) {
            continue;
        }
        $query = "SELECT id FROM app_emails WHERE id IN ($ids) AND account_id = ?";
        $ids2 = check_ids(execute_query_array($query, [$account_id]));
        __getmail_imap_move($ids2, $folder_id);
        $query = "UPDATE app_emails SET folder_id = ? WHERE id IN ($ids2)";
        db_query($query, [$folder_id]);
    }
}

/**
 * Move emails to a folder by id
 *
 * This function moves the emails to the folder identified by its id, the folder must be of
 * an account of the current user and only the emails of the same account are moved
 *
 * @ids       => the ids of the emails separated by commas
 * @folder_id => the id of the destination folder
 *
 * Returns a response message with the number of moved emails
 */
function emails_folders_move_to($ids, $folder_id)
{
    $ids = check_ids($ids);
    $numids = count(explode(',', $ids));
    $query = "SELECT id FROM app_emails a WHERE id IN ($ids) AND id IN (
        SELECT id FROM app_emails_control b WHERE b.id = a.id AND user_id = ?)";
    $result = execute_query_array($query, [current_user()]);
    if (count($result) !== $numids) {
        return T('Permission denied');
    }
    $query = 'SELECT account_id FROM app_emails_folders WHERE id = ? AND account_id IN (
        SELECT id FROM app_emails_accounts WHERE user_id = ?)';
    $account_id = execute_query($query, [intval($folder_id), current_user()]);
    if (!$account_id) {
        return T('Permission denied');
    }
    $query = "SELECT COUNT(*) FROM app_emails WHERE id IN ($ids) AND account_id = ?";
    $numids = execute_query($query, [$account_id]);
    require_once 'apps/emails/php/getmail_imap.php';
    $query = "SELECT id FROM app_emails WHERE id IN ($ids) AND account_id = ?";
    __getmail_imap_move(execute_query_array($query, [$account_id]), $folder_id);
    $query = "UPDATE app_emails SET folder_id = ? WHERE id IN ($ids) AND account_id = ?";
    db_query($query, [intval($folder_id), $account_id]);
    return sprintf(T('%d email(s) moved'), $numids);
}

/**
 * Folders action
 *
 * This function executes the actions of the folders management requested by the client
 *
 * @json => the data of the request: action, account_id, folder_id, parent_id, label and
 *          folder (trash or junk, used to empty these folders)
 *
 * Returns a response message
 */
function emails_folders_action($json)
{
    $action = strval($json['action'] ?? '');
    $account_id = intval($json['account_id'] ?? 0);
    $folder_id = intval($json['folder_id'] ?? 0);
    $parent_id = intval($json['parent_id'] ?? 0);
    $label = trim(strval($json['label'] ?? ''));
    if ($action === 'create') {
        return __emails_folders_create($account_id, $parent_id, $label);
    }
    if ($action === 'rename') {
        return __emails_folders_rename($folder_id, $label);
    }
    if ($action === 'move') {
        return __emails_folders_move_parent($folder_id, $parent_id);
    }
    if ($action === 'delete') {
        return __emails_folders_delete($folder_id);
    }
    if ($action === 'subscribe') {
        return __emails_folders_subscribe($account_id, strval($json['ids'] ?? ''));
    }
    $folders = [
        'trash' => '\Trash',
        'junk' => '\Junk',
    ];
    $folder = $folders[strval($json['folder'] ?? '')] ?? '';
    if ($action === 'empty' && $folder !== '') {
        return __emails_folders_empty($account_id, $folder);
    }
    return T('Unknown action');
}

/**
 * Folders get helper
 *
 * This function returns the folder if it is a folder of an account of the current user
 *
 * @folder_id => the id of the folder
 *
 * Returns an array with the fields of the folder or null if not found
 */
function __emails_folders_get($folder_id)
{
    $query = 'SELECT * FROM app_emails_folders WHERE id = ? AND account_id IN (
        SELECT id FROM app_emails_accounts WHERE user_id = ?)';
    return execute_query($query, [$folder_id, current_user()]);
}

/**
 * Folders IMAP helper
 *
 * This function checks if the account is an IMAP account, whose folders are managed in
 * the server
 *
 * @account_id => the id of the account
 *
 * Returns true if the account is an IMAP account
 */
function __emails_folders_imap($account_id)
{
    $query = 'SELECT protocol FROM app_emails_accounts WHERE id = ?';
    return execute_query($query, [$account_id]) === 'imap';
}

/**
 * Folders exists helper
 *
 * This function checks if exists a folder with the same label in the same parent
 *
 * @account_id => the id of the account
 * @parent_id  => the id of the parent folder
 * @label      => the label of the folder
 * @folder_id  => the id of the folder excluded from the check
 *
 * Returns true if exists another folder with the same label
 */
function __emails_folders_exists($account_id, $parent_id, $label, $folder_id = 0)
{
    $query = 'SELECT COUNT(*) FROM app_emails_folders
        WHERE account_id = ? AND parent_id = ? AND label = ? AND id != ?';
    return boolval(execute_query($query, [$account_id, $parent_id, $label, $folder_id]));
}

/**
 * Folders create helper
 *
 * This function creates a new folder in the account, inside the parent folder
 *
 * @account_id => the id of the account
 * @parent_id  => the id of the parent folder, 0 for the root
 * @label      => the label of the new folder
 *
 * Returns a response message
 */
function __emails_folders_create($account_id, $parent_id, $label)
{
    $query = 'SELECT COUNT(*) FROM app_emails_accounts WHERE id = ? AND user_id = ?';
    if (!execute_query($query, [$account_id, current_user()])) {
        return T('Permission denied');
    }
    if ($parent_id) {
        $parent = __emails_folders_get($parent_id);
        if (!$parent || intval($parent['account_id']) !== $account_id) {
            return T('Permission denied');
        }
    }
    if ($label === '') {
        return T('The name of the folder is required');
    }
    if (__emails_folders_exists($account_id, $parent_id, $label)) {
        return T('A folder with the same name already exists');
    }
    if (__emails_folders_imap($account_id)) {
        require_once 'apps/emails/php/getmail_imap.php';
        $error = __getmail_imap_folder_create($account_id, $parent_id, $label);
        return $error !== '' ? $error : T('Folder created');
    }
    $query = prepare_insert_query('app_emails_folders', [
        'account_id' => $account_id,
        'parent_id' => $parent_id,
        'label' => $label,
        'special_use' => '',
    ]);
    db_query(...$query);
    return T('Folder created');
}

/**
 * Folders rename helper
 *
 * This function renames a folder, the special folders can not be renamed
 *
 * @folder_id => the id of the folder
 * @label     => the new label of the folder
 *
 * Returns a response message
 */
function __emails_folders_rename($folder_id, $label)
{
    $folder = __emails_folders_get($folder_id);
    if (!$folder) {
        return T('Permission denied');
    }
    if ($folder['special_use'] !== '') {
        return T('The special folders can not be modified');
    }
    if ($label === '') {
        return T('The name of the folder is required');
    }
    if (__emails_folders_exists($folder['account_id'], $folder['parent_id'], $label, $folder_id)) {
        return T('A folder with the same name already exists');
    }
    if (__emails_folders_imap($folder['account_id'])) {
        require_once 'apps/emails/php/getmail_imap.php';
        $error = __getmail_imap_folder_rename($folder, intval($folder['parent_id']), $label);
        return $error !== '' ? $error : T('Folder renamed');
    }
    $query = 'UPDATE app_emails_folders SET label = ? WHERE id = ?';
    db_query($query, [$label, $folder_id]);
    return T('Folder renamed');
}

/**
 * Folders tree ids helper
 *
 * This function returns the id of the folder and the ids of all its subfolders
 *
 * @account_id => the id of the account
 * @folder_id  => the id of the folder
 *
 * Returns an array with the ids
 */
function __emails_folders_tree_ids($account_id, $folder_id)
{
    $query = 'SELECT id, parent_id FROM app_emails_folders WHERE account_id = ?';
    $parents = array_column(execute_query_array($query, [$account_id]), 'parent_id', 'id');
    $ids = [intval($folder_id)];
    for ($i = 0; $i < count($ids); $i++) {
        foreach ($parents as $id => $parent_id) {
            if (intval($parent_id) === $ids[$i]) {
                $ids[] = intval($id);
            }
        }
    }
    return $ids;
}

/**
 * Folders move helper
 *
 * This function moves a folder and its subfolders inside another folder of the same
 * account, the special folders can not be moved
 *
 * @folder_id => the id of the folder
 * @parent_id => the id of the new parent folder, 0 for the root
 *
 * Returns a response message
 */
function __emails_folders_move_parent($folder_id, $parent_id)
{
    $folder = __emails_folders_get($folder_id);
    if (!$folder) {
        return T('Permission denied');
    }
    if ($folder['special_use'] !== '') {
        return T('The special folders can not be modified');
    }
    if ($parent_id) {
        $parent = __emails_folders_get($parent_id);
        if (!$parent || $parent['account_id'] !== $folder['account_id']) {
            return T('Permission denied');
        }
    }
    if (in_array($parent_id, __emails_folders_tree_ids($folder['account_id'], $folder_id), true)) {
        return T('A folder can not be moved inside itself');
    }
    if (__emails_folders_exists($folder['account_id'], $parent_id, $folder['label'], $folder_id)) {
        return T('A folder with the same name already exists');
    }
    if (__emails_folders_imap($folder['account_id'])) {
        require_once 'apps/emails/php/getmail_imap.php';
        $error = __getmail_imap_folder_rename($folder, $parent_id, $folder['label']);
        return $error !== '' ? $error : T('Folder moved');
    }
    $query = 'UPDATE app_emails_folders SET parent_id = ? WHERE id = ?';
    db_query($query, [$parent_id, $folder_id]);
    return T('Folder moved');
}

/**
 * Folders summary
 *
 * This function returns the message used to confirm the delete of a folder, with the
 * number of subfolders and emails affected, or the error if the folder can not be deleted
 *
 * @json => the data of the request, with the folder_id
 *
 * Returns an array with the status (ok or ko) and the message
 */
function emails_folders_summary($json)
{
    $folder_id = intval($json['folder_id'] ?? 0);
    $folder = __emails_folders_get($folder_id);
    if (!$folder) {
        return ['status' => 'ko', 'text' => T('Permission denied')];
    }
    if ($folder['special_use'] !== '') {
        return ['status' => 'ko', 'text' => T('The special folders can not be modified')];
    }
    $ids = __emails_folders_tree_ids($folder['account_id'], $folder_id);
    $query = 'SELECT COUNT(*) FROM app_emails WHERE folder_id IN (' . implode(',', $ids) . ')';
    $emails = execute_query($query);
    return [
        'status' => 'ok',
        'text' => sprintf(
            T('The folder and its %d subfolder(s) will be deleted and its %d email(s) moved to the trash'),
            count($ids) - 1,
            $emails
        ),
    ];
}

/**
 * Folders delete helper
 *
 * This function deletes a folder and its subfolders, moving its emails to the trash of
 * the account, the special folders can not be deleted
 *
 * @folder_id => the id of the folder
 *
 * Returns a response message
 */
function __emails_folders_delete($folder_id)
{
    $folder = __emails_folders_get($folder_id);
    if (!$folder) {
        return T('Permission denied');
    }
    if ($folder['special_use'] !== '') {
        return T('The special folders can not be modified');
    }
    if (__emails_folders_imap($folder['account_id'])) {
        require_once 'apps/emails/php/getmail_imap.php';
        $error = __getmail_imap_folder_delete($folder);
        return $error !== '' ? $error : T('Folder deleted');
    }
    $ids = implode(',', __emails_folders_tree_ids($folder['account_id'], $folder_id));
    $trash = emails_folders_check($folder['account_id'])['\Trash'];
    $query = "UPDATE app_emails SET folder_id = ? WHERE folder_id IN ($ids)";
    db_query($query, [$trash]);
    $query = "DELETE FROM app_emails_folders WHERE id IN ($ids)";
    db_query($query);
    return T('Folder deleted');
}

/**
 * Folders empty ids helper
 *
 * This function returns the ids of the emails of the special folder of the account, the
 * account must be of the current user
 *
 * @account_id  => the id of the account
 * @special_use => the special use of the folder
 *
 * Returns an array with the ids of the emails, or null if the account is not of the user
 */
function __emails_folders_empty_ids($account_id, $special_use)
{
    $query = 'SELECT COUNT(*) FROM app_emails_accounts WHERE id = ? AND user_id = ?';
    if (!execute_query($query, [$account_id, current_user()])) {
        return null;
    }
    $query = 'SELECT id FROM app_emails WHERE account_id = ? AND folder_id IN (
        SELECT id FROM app_emails_folders WHERE account_id = ? AND special_use = ?)';
    return execute_query_array($query, [$account_id, $account_id, $special_use]);
}

/**
 * Folders empty summary
 *
 * This function returns the message used to confirm the empty of the trash or the junk
 * folder of the account, with the number of emails affected, or the error if the folder
 * can not be emptied (also when it is already empty)
 *
 * @json => the data of the request, with the account_id and the folder (trash or junk)
 *
 * Returns an array with the status (ok or ko) and the message
 */
function emails_folders_empty_summary($json)
{
    $folders = [
        'trash' => '\Trash',
        'junk' => '\Junk',
    ];
    $special_use = $folders[strval($json['folder'] ?? '')] ?? '';
    if ($special_use === '') {
        return ['status' => 'ko', 'text' => T('Unknown action')];
    }
    $ids = __emails_folders_empty_ids(intval($json['account_id'] ?? 0), $special_use);
    if ($ids === null) {
        return ['status' => 'ko', 'text' => T('Permission denied')];
    }
    if (!count($ids)) {
        return ['status' => 'ko', 'text' => T('The folder is empty')];
    }
    return ['status' => 'ok', 'text' => sprintf(T('%d email(s) will be deleted permanently'), count($ids))];
}

/**
 * Folders empty helper
 *
 * This function deletes permanently the emails of the trash or the junk folder of the
 * account, the emails of the junk are moved to the trash before to be deleted because
 * the delete action only removes permanently the emails that are in the trash
 *
 * @account_id  => the id of the account
 * @special_use => the special use of the folder
 *
 * Returns a response message
 */
function __emails_folders_empty($account_id, $special_use)
{
    $ids = __emails_folders_empty_ids($account_id, $special_use);
    if ($ids === null) {
        return T('Permission denied');
    }
    if (!count($ids)) {
        return T('The folder is empty');
    }
    $ids = implode(',', $ids);
    if ($special_use !== '\Trash') {
        __emails_folders_move($ids, '\Trash');
    }
    require_once 'apps/emails/php/getmail.php';
    return getmail_delete($ids);
}

/**
 * Folders of the server
 *
 * This function returns the folders of an IMAP account of the current user, refreshed from
 * the server, used by the dialog to subscribe and unsubscribe the folders: the special
 * folders and the folders that can not contain emails can not be changed
 *
 * @json => the data of the request, with the account_id
 *
 * Returns an array with the status and the folders (name, subscribed and locked), or with
 * the status and the error message
 */
function emails_folders_server($json)
{
    $account_id = intval($json['account_id'] ?? 0);
    $query = 'SELECT COUNT(*) FROM app_emails_accounts WHERE id = ? AND user_id = ?';
    if (!execute_query($query, [$account_id, current_user()])) {
        return ['status' => 'ko', 'text' => T('Permission denied')];
    }
    if (!__emails_folders_imap($account_id)) {
        return ['status' => 'ko', 'text' => T('Only the IMAP accounts have folders in the server')];
    }
    require_once 'apps/emails/php/getmail_imap.php';
    $error = __getmail_imap_folders_refresh($account_id);
    if ($error !== '') {
        return ['status' => 'ko', 'text' => $error];
    }
    $query = 'SELECT id, name, special_use, is_subscribed, is_selectable FROM app_emails_folders
        WHERE account_id = ? ORDER BY name';
    $folders = [];
    foreach (execute_query_array($query, [$account_id]) as $folder) {
        $folders[] = [
            'id' => intval($folder['id']),
            'name' => imap_class::Decode($folder['name']),
            'subscribed' => intval($folder['is_subscribed']),
            'locked' => intval($folder['special_use'] !== '' || !$folder['is_selectable']),
        ];
    }
    return ['status' => 'ok', 'folders' => $folders];
}

/**
 * Folders subscribe helper
 *
 * This function sets the folders subscribed of an IMAP account: the folders of the list
 * not subscribed are subscribed and the folders not in the list subscribed are
 * unsubscribed (deleting its emails in SaltOS), except the special folders and the folders
 * that can not contain emails
 *
 * @account_id => the id of the account
 * @ids        => the ids of the folders that must be subscribed, separated by commas
 *
 * Returns a response message
 */
function __emails_folders_subscribe($account_id, $ids)
{
    $query = 'SELECT COUNT(*) FROM app_emails_accounts WHERE id = ? AND user_id = ?';
    if (!execute_query($query, [$account_id, current_user()])) {
        return T('Permission denied');
    }
    if (!__emails_folders_imap($account_id)) {
        return T('Only the IMAP accounts have folders in the server');
    }
    $ids = check_ids_array($ids);
    $query = "SELECT id, is_subscribed FROM app_emails_folders
        WHERE account_id = ? AND special_use = '' AND is_selectable = 1";
    $subscribe = [];
    $unsubscribe = [];
    foreach (execute_query_array($query, [$account_id]) as $folder) {
        $id = intval($folder['id']);
        $wanted = in_array($id, $ids, true);
        if ($wanted && !$folder['is_subscribed']) {
            $subscribe[] = $id;
        }
        if (!$wanted && $folder['is_subscribed']) {
            $unsubscribe[] = $id;
        }
    }
    require_once 'apps/emails/php/getmail_imap.php';
    $error = __getmail_imap_subscribe($account_id, $subscribe, $unsubscribe);
    return $error !== '' ? $error : T('Folders updated');
}
