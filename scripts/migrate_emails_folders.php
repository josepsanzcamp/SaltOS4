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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Migrate emails folders
 *
 * This script migrates the emails to the folders in two steps, it must be executed inside
 * the code directory or inside an instance:
 *
 * 1) Before the setup: php ../scripts/migrate_emails_folders.php export <file>
 *    Saves in the file the ids of the spam emails, using the state_spam field that is
 *    removed by the setup
 *
 * 2) After the setup: php ../scripts/migrate_emails_folders.php import <file>
 *    Creates the mandatory folders of the existing emails accounts and assigns a folder
 *    to the emails without folder: the outbox emails go to sent, the spam emails of the
 *    file go to junk and the others go to inbox, it can be executed more than once
 */

foreach (['api', 'apps', 'data'] as $dir) {
    if (!is_dir($dir)) {
        die("Error: $dir not found, this script must be executed inside the code or an instance\n");
    }
}

$mode = $argv[1] ?? '';
$path = $argv[2] ?? '';
if (!in_array($mode, ['export', 'import'], true) || $path === '') {
    die("Usage: php ../scripts/migrate_emails_folders.php export|import <file>\n");
}
$path = realpath(dirname($path)) . '/' . basename($path);

chdir('api');
foreach (glob('php/autoload/*.php') as $file) {
    if (basename($file) === 'zindex.php') {
        continue;
    }
    require $file;
}

init_timer();

global $_CONFIG;
$_CONFIG = eval_attr(prepare_config_files(xmlfiles2array(detect_config_files('xml/config.xml'))));
ini_set('date.timezone', $_CONFIG['iniset']['date.timezone']);
db_connect();

if ($mode === 'export') {
    $query = 'SELECT id FROM app_emails WHERE state_spam = 1 AND is_outbox = 0 ORDER BY id ASC';
    $spam = execute_query_array($query);
    file_put_contents($path, json_encode($spam));
    echo 'Spam emails exported: ' . count($spam) . " to $path\n";
    die();
}

if (!file_exists($path)) {
    die("Error: $path not found, execute the export before the setup\n");
}
$spam = json_decode(file_get_contents($path), true);
$spam = check_ids(is_array($spam) ? $spam : []);

require_once 'apps/emails/php/folders.php';

$query = 'SELECT id FROM app_emails_accounts ORDER BY id ASC';
$accounts = execute_query_array($query);
foreach ($accounts as $account_id) {
    $ids = emails_folders_check($account_id);
    $updates = [
        '\Sent' => 'is_outbox = 1',
        '\Junk' => "is_outbox = 0 AND id IN ($spam)",
        '\Inbox' => 'is_outbox = 0',
    ];
    foreach ($updates as $special_use => $where) {
        $query = "UPDATE app_emails SET folder_id = ?
            WHERE account_id = ? AND IFNULL(folder_id, 0) = 0 AND $where";
        db_query($query, [$ids[$special_use], $account_id]);
    }
    echo "Account $account_id migrated\n";
}

$query = 'SELECT COUNT(*) FROM app_emails WHERE IFNULL(folder_id, 0) = 0';
$count = execute_query($query);
echo "Emails without folder: $count\n";
