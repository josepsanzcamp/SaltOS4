<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * Setup helper module
 *
 * This file contains useful functions related to the setup process
 */

if (get_data('server/request_method') != 'CLI') {
    show_php_error(['phperror' => 'Permission denied']);
}

require_once 'php/lib/control.php';
require_once 'php/lib/indexing.php';

require_once 'apps/emails/php/getmail.php';
$time1 = microtime(true);
$output = [
    'account' => 0,
    'files' => 0,
    'emails' => 0,
];

$remote_addr = get_data('server/remote_addr');
$user_agent = get_data('server/user_agent');
$query = 'SELECT token FROM tbl_users_tokens
    WHERE user_id = 1 AND active = 1 AND remote_addr = ? AND user_agent = ?';
$token = execute_query($query, [$remote_addr, $user_agent]);
set_data('server/token', $token);

// Add a new email account
$exists = execute_query('SELECT COUNT(*) FROM app_emails_accounts');
if (!$exists) {
    $query = prepare_insert_query('app_emails_accounts', [
        'id' => 1,
        'user_id' => 1,
        'email_name' => 'Admin user',
        'email_from' => 'admin@example.com',
        'email_signature' => 'Email sent from my <a href="https://www.saltos.org">SaltOS</a>',
        'pop3_host' => 'localhost',
        'pop3_port' => '995',
        'pop3_extra' => 'TLS',
        'pop3_user' => 'admin',
        'pop3_pass' => 'admin',
        'pop3_delete' => 1,
        'pop3_days' => 90,
        'smtp_host' => 'localhost',
        'smtp_port' => '465',
        'smtp_extra' => 'TLS',
        'smtp_user' => 'admin',
        'smtp_pass' => 'admin',
        'email_default' => 1,
    ]);
    db_query(...$query);
    $output['account']++;
    make_control('emails_accounts', 1);
    make_version('emails_accounts', 1);
    make_index('emails_accounts', 1);
}

// Create the account directory and copy all initial RFC822 files
if (!file_exists('data/inbox/1')) {
    mkdir('data/inbox/1');
    chmod_protected('data/inbox/1', 0777);
    $files = glob('apps/emails/sample/inbox/1/*.eml.gz');
    foreach ($files as $file) {
        copy($file, 'data/inbox/1/' . basename($file));
        $output['files']++;
    }
}

// Import emails
$exists = execute_query('SELECT COUNT(*) FROM app_emails');
if (!$exists) {
    $files = glob('data/inbox/1/*.eml.gz');
    foreach ($files as $file) {
        $msgid = str_replace(['data/inbox/', '.eml.gz'], '', $file);
        __getmail_insert($file, $msgid, 1, 0, 0, 0, 0, 0, 0, '');
        $output['emails']++;
    }
}

$time2 = microtime(true);
output_handler_json([
    'setup' => array_merge([
        'time' => sprintf('%f', $time2 - $time1),
    ], $output),
]);
