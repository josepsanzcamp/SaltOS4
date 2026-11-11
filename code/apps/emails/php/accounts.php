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
 * Emails accounts library
 *
 * This library contains the functions used by the emails accounts app
 */

/**
 * Used libraries
 *
 * This use loads the external libraries needed to run this library.
 */
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Test the connection of an account
 *
 * This function tests the incoming server (POP3 or IMAP) or the outgoing server (SMTP),
 * according to the server field, using the data of the account form, without saving it
 *
 * @json => the data of the account form, with the server to test, incoming or outgoing
 *
 * Returns an array with the status, ok or error, and the message
 */
function emails_accounts_test($json)
{
    $value = fn ($key) => strval($json[$key] ?? '');
    if ($value('server') === 'incoming') {
        return emails_accounts_test_incoming($value);
    }
    if ($value('server') === 'outgoing') {
        return emails_accounts_test_outgoing($value);
    }
    show_php_error(['phperror' => 'Unknown server ' . $value('server')]);
}

/**
 * Test the incoming server
 *
 * This function connects and logins with the incoming server and reports the number of
 * emails (POP3) or the number of folders (IMAP)
 *
 * @value => the function used to get the fields of the account form
 *
 * Returns an array with the status, ok or error, and the message
 */
function emails_accounts_test_incoming($value)
{
    require_once 'apps/emails/php/pop3.php';
    require_once 'apps/emails/php/imap.php';
    $client = $value('protocol') === 'imap' ? new imap_class() : new pop3_class();
    $client->hostname = $value('pop3_host');
    $client->port = intval($value('pop3_port'));
    $client->tls = $value('pop3_extra') === 'tls' ? 1 : 0;
    $client->ssl = $value('pop3_extra') === 'ssl' ? 1 : 0;
    $client->user = $value('pop3_user');
    $client->pass = $value('pop3_pass');
    $client->timeout = 10;
    $result = ['status' => 'error', 'text' => $client->Open()];
    if ($result['text'] === '') {
        if ($client instanceof imap_class) {
            $items = $client->ListFolders();
            $text = T('Connection successful, %d folders in the server');
        } else {
            $items = $client->ListMessages();
            $text = T('Connection successful, %d emails in the server');
        }
        $result = ['status' => 'error', 'text' => $items];
        if (is_array($items)) {
            $result = ['status' => 'ok', 'text' => sprintf($text, count($items))];
        }
    }
    $client->Close();
    return $result;
}

/**
 * Test the outgoing server
 *
 * This function connects and authenticates with the SMTP server using PHPMailer, the
 * local servers (mail, sendmail and qmail) are not tested
 *
 * @value => the function used to get the fields of the account form
 *
 * Returns an array with the status, ok or error, and the message
 */
function emails_accounts_test_outgoing($value)
{
    require_once 'apps/emails/lib/phpmailer/vendor/autoload.php';
    require_once 'apps/emails/php/sendmail.php';
    $host = $value('smtp_host');
    if (in_array($host, ['mail', 'sendmail', 'qmail', ''], true)) {
        return ['status' => 'ok', 'text' => T('Not tested, the emails are sent using the local server')];
    }
    // The errors are captured from the debug output like in the sendmail function, because
    // smtpConnect does not set the ErrorInfo and the debug output contains the server response
    $mail = new PHPMailer();
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = '__sendmail_debugoutput_helper';
    $mail->SetLanguage(current_lang() ?? '');
    $mail->IsSMTP();
    $mail->set('Host', $host);
    if ($value('smtp_port') !== '') {
        $mail->set('Port', $value('smtp_port'));
    }
    if ($value('smtp_extra') !== '') {
        $mail->set('SMTPSecure', $value('smtp_extra'));
    }
    $mail->set('Username', $value('smtp_user'));
    $mail->set('Password', $value('smtp_pass'));
    $mail->set('SMTPAuth', ($value('smtp_user') !== '' || $value('smtp_pass') !== ''));
    $mail->set('Timeout', 10);
    $mail->set('SMTPOptions', [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ]);
    ob_start();
    $current = $mail->smtpConnect();
    $mail->smtpClose();
    $error = ob_get_clean();
    $result = ['status' => 'ok', 'text' => T('Connection successful')];
    if (!$current) {
        $result = ['status' => 'error', 'text' => $error ? $error : $mail->ErrorInfo];
    }
    return $result;
}
