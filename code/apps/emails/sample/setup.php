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
 * TODO
 *
 * TODO
 */
function __setup_helper_emails()
{
    require_once 'php/lib/control.php';
    require_once 'php/lib/log.php';
    require_once 'php/lib/version.php';
    require_once 'php/lib/indexing.php';
    require_once 'apps/emails/php/getmail.php';
    $time1 = microtime(true);

    // Add a new email account
    $numaccounts = 0;
    $exists = execute_query('SELECT COUNT(*) FROM app_emails_accounts');
    if (!$exists) {
        $query = prepare_insert_query('app_emails_accounts', [
            'id' => 1,
            'user_id' => 1,
            'email_name' => 'Admin user',
            'email_from' => 'admin@example.com',
            'email_signature' => '<p>Email sent from my <a href="https://www.saltos.org">SaltOS</a></p>',
            'pop3_host' => 'example.com',
            'pop3_port' => '995',
            'pop3_extra' => 'ssl',
            'pop3_user' => 'admin',
            'pop3_pass' => 'admin',
            'pop3_delete' => 1,
            'pop3_days' => 90,
            'smtp_host' => 'example.com',
            'smtp_port' => '587',
            'smtp_extra' => 'ssl',
            'smtp_user' => 'admin',
            'smtp_pass' => 'admin',
            'email_default' => 1,
        ]);
        db_query(...$query);
        $numaccounts++;
        make_control('emails_accounts', 1);
        make_version('emails_accounts', 1);
        make_index('emails_accounts', 1);
        make_log('emails_accounts', 'setup', 1);
    }

    // Create the account directory and copy all initial RFC822 files
    $numfiles = 0;
    if (!file_exists('data/inbox/1')) {
        mkdir('data/inbox/1');
        chmod_protected('data/inbox/1', 0777);
        $files = glob('apps/emails/sample/eml/*.eml.gz');
        foreach ($files as $file) {
            copy($file, 'data/inbox/1/' . basename($file));
            $numfiles++;
        }
    }

    // Import emails
    $numemails = 0;
    $exists = execute_query('SELECT COUNT(*) FROM app_emails');
    if (!$exists) {
        $files = glob('data/inbox/1/*.eml.gz');
        foreach ($files as $file) {
            $msgid = str_replace(['data/inbox/', '.eml.gz'], '', $file);
            __getmail_insert($file, $msgid, 1, 0, 0, 0, 0, 0, 0, '');
            $numemails++;
        }
    }

    $time2 = microtime(true);
    return [
        'setup' => [
            'time' => round($time2 - $time1, 6),
            'accounts' => $numaccounts,
            'files' => $numfiles,
            'emails' => $numemails,
        ],
    ];
}
