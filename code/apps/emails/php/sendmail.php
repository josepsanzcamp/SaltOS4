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
 * Send email library
 *
 * This library provides the necesary functions to send emails.
 */

/**
 * Used libraries
 *
 * This use loads the external libraries needed to run this library.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Sendmail
 *
 * This function send an email in synchronous and/or asynchronous mode
 *
 * $account_id => the account id used to detect the source of the email
 * $to         => can be an string with the destination email or an array with
 *                the follow prefixes => to:, cc:, bcc:, crt:, priority:,
 *                sensitivity:, replyto
 * $subject    => the subject string
 * $body       => the body string
 * $files      => an array with files
 *
 * Notes:
 *
 * This function must return the last_id if success, and a string if error
 */
function sendmail($account_id, $to, $subject, $body, $files = '', $async = true)
{
    require_once 'apps/emails/lib/phpmailer/vendor/autoload.php';
    require_once 'apps/emails/php/getmail.php';
    // FIND ACCOUNT DATA
    $query = 'SELECT * FROM app_emails_accounts WHERE id = ? AND user_id = ?';
    $result = execute_query($query, [$account_id, current_user()]);
    if (!isset($result['id'])) {
        return T('Id not found');
    }
    if ($result['email_disabled']) {
        return T('Email disabled');
    }
    $host = $result['smtp_host'];
    $port = $result['smtp_port'];
    $extra = $result['smtp_extra'];
    $user = $result['smtp_user'];
    $pass = $result['smtp_pass'];
    $from = $result['email_from'];
    $fromname = $result['email_name'];
    // CONTINUE
    $mail = new PHPMailer();
    $mail->set('XMailer', get_name_version_revision());
    $mail->AddCustomHeader('X-Originating-IP', get_server('REMOTE_ADDR'));
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = '__sendmail_debugoutput_helper';
    if (!$mail->SetLanguage(current_lang() ?? '')) {
        return sprintf(T('Lang %s not found'), current_lang());
    }
    $mail->set('CharSet', 'UTF-8');
    if (!$mail->SetFrom($from, $fromname)) {
        return $mail->ErrorInfo;
    }
    $mail->set('WordWrap', 50);
    $options = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];
    $mail->set('SMTPOptions', $options);
    $mail->IsHTML();
    if (!in_array($host, ['mail', 'sendmail', 'qmail', ''])) {
        $mail->IsSMTP();
        $mail->set('Host', $host);
        if ($port != '') {
            $mail->set('Port', $port);
        }
        if ($extra != '') {
            $mail->set('SMTPSecure', $extra);
        }
        $mail->set('Username', $user);
        $mail->set('Password', $pass);
        $mail->set('SMTPAuth', ($user != '' || $pass != ''));
        $mail->set('Hostname', $host);
    } else {
        if ($host == 'mail') {
            $mail->IsMail();
        } elseif ($host == 'sendmail') {
            $mail->IsSendmail();
        } elseif ($host == 'qmail') {
            $mail->IsQmail();
        }
    }
    $mail->set('Subject', $subject);
    $mail->set('Body', $body);
    $mail->set('AltBody', html2text($body));
    if (is_array($files)) {
        foreach ($files as $file) {
            if (isset($file['data']) && !isset($file['cid'])) {
                $mail->AddStringAttachment(
                    $file['data'], $file['name'], 'base64', $file['mime']
                );
            }
            if (isset($file['file']) && !isset($file['cid'])) {
                $mail->AddAttachment(
                    $file['file'], $file['name'], 'base64', $file['mime']
                );
            }
            if (isset($file['data']) && isset($file['cid'])) {
                $mail->AddStringEmbeddedImage(
                    $file['data'], $file['cid'], $file['name'], 'base64', $file['mime']
                );
            }
            if (isset($file['file']) && isset($file['cid'])) {
                $mail->AddEmbeddedImage(
                    $file['file'], $file['cid'], $file['name'], 'base64', $file['mime']
                );
            }
        }
    }
    $bcc = [];
    if (is_array($to)) {
        $valids = ['to:', 'cc:', 'bcc:', 'crt:', 'priority:', 'sensitivity:', 'replyto:'];
        foreach ($to as $addr) {
            $type = $valids[0];
            foreach ($valids as $valid) {
                if (strncasecmp($addr, $valid, strlen($valid)) == 0) {
                    $type = $valid;
                    $addr = substr($addr, strlen($type));
                    break;
                }
            }
            // EXTRA FOR POPULATE $bcc
            if ($type == $valids[2]) {
                $bcc[] = $addr;
            }
            // CONTINUE
            list($addr, $addrname) = __sendmail_parser($addr);
            if ($type == $valids[0]) {
                if (!$mail->AddAddress($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[1]) {
                if (!$mail->AddCC($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[2]) {
                if (!$mail->AddBCC($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[3]) {
                $mail->set('ConfirmReadingTo', $addr);
            }
            if ($type == $valids[4]) {
                $mail->set('Priority', $addr);
            }
            if ($type == $valids[5]) {
                $mail->AddCustomHeader('Sensitivity', $addr);
            }
            if ($type == $valids[6]) {
                if (!$mail->AddReplyTo($addr, $addrname)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
            }
        }
    } else {
        list($to, $toname) = __sendmail_parser($to);
        if (!$mail->AddAddress($to, $toname)) {
            return $mail->ErrorInfo;
        }
    }
    $current = $mail->PreSend();
    if (!$current) {
        return $mail->ErrorInfo;
    }
    if (!semaphore_acquire(__FUNCTION__)) {
        show_php_error(['phperror' => 'Could not acquire the semaphore']);
    }
    $messageid = __sendmail_messageid($account_id, $mail->From);
    $file1 = __sendmail_emlsaver($mail->GetSentMIMEMessage(), $messageid);
    $file2 = __sendmail_objsaver($mail, $messageid);
    $last_id = __getmail_insert($file1, $messageid, 0, 0, 0, 0, 0, 1, 1, '');
    semaphore_release(__FUNCTION__);
    if (count($bcc)) {
        __getmail_add_bcc($last_id, $bcc); // BCC DOESN'T APPEAR IN THE RFC822 SOMETIMES
    }
    if ($async) {
        __getmail_update('state_sent', 0, $last_id);
        __getmail_update('state_error', '', $last_id);
        return $last_id;
    }
    ob_start();
    $current = $mail->PostSend();
    $error = ob_get_clean();
    if (!$current) {
        if (!$error) {
            $error = $mail->ErrorInfo;
        }
        __getmail_update('state_sent', 0, $last_id);
        __getmail_update('state_error', $error, $last_id);
        return $error;
    }
    __getmail_update('state_sent', 1, $last_id);
    __getmail_update('state_error', '', $last_id);
    unlink($file2);
    return $last_id;
}

/**
 * Debugoutput helper
 *
 * This function tries to echoed all debug information with the SMTP Error: prefix,
 * with this feature we can capture smtp errors with more details like the error
 * stored in $mail->ErrorInfo that always contains SMTP connect() failed, the signature
 * of this function will accomplish the neested callback arguments
 *
 * @str   => the debug trace string that can contain the errors
 * @level => unused in this function
 */
function __sendmail_debugoutput_helper($str, $level)
{
    $fix = 'smtp error:';
    if (strncasecmp($str, $fix, strlen($fix)) == 0) {
        echo $str;
    }
}

/**
 * Parser
 *
 * This function gets an address and tries to detect the name part and the addr
 * part of the argument. It's returns an array with two elements, the first is
 * for the addr and the second is for the name.
 *
 * @oldaddr => the string that must to be processed
 */
function __sendmail_parser($oldaddr)
{
    $pos1 = strpos($oldaddr, '<');
    $pos2 = strpos($oldaddr, '>');
    if ($pos1 !== false && $pos2 !== false) {
        $name = trim(substr($oldaddr, 0, $pos1));
        $addr = trim(substr($oldaddr, $pos1 + 1, $pos2 - $pos1 - 1));
    } else {
        $name = '';
        $addr = trim($oldaddr);
    }
    return [$addr, $name];
}

/**
 * Message Id
 *
 * This function returns the message id for a new email, to do it, tries
 * to detect the outbox directory, compute an aproximation to the newest
 * value and checks that is unique in the system to prevent concurrence.
 *
 * @account_id => the account id used to send the new email
 * @from       => the from used to compute the crc32
 */
function __sendmail_messageid($account_id, $from)
{
    $prefix = get_directory('dirs/outboxdir') . $account_id;
    if (!file_exists($prefix)) {
        mkdir($prefix);
        chmod_protected($prefix, 0777);
    }
    $query = 'SELECT MAX(id) FROM app_emails';
    $count = execute_query($query);
    if (!$count) {
        $count = 1;
    }
    $uidl2 = sprintf('%08X', crc32($from));
    for (;;) {
        $uidl1 = sprintf('%08X', $count);
        $file = $prefix . '/' . $uidl1 . $uidl2 . '.eml.gz';
        if (!file_exists($file)) {
            break;
        }
        $count++;
    }
    return $account_id . '/' . $uidl1 . $uidl2;
}

/**
 * Eml saver
 *
 * This function is intended to save the RFC822 message into the eml gzfile
 *
 * @message   => the contents in RFC822 format of the message
 * @messageid => the message id computed previously
 */
function __sendmail_emlsaver($message, $messageid)
{
    $prefix = get_directory('dirs/outboxdir') . $messageid;
    $file = $prefix . '.eml.gz';
    $fp = gzopen($file, 'w');
    gzwrite($fp, $message);
    gzclose($fp);
    chmod_protected($file, 0666);
    return $file;
}

/**
 * Obj saver
 *
 * This function is intended to save the PHPMailer object into the obj file
 *
 * @mail      => the PHPMailer object of the asynchronous transaction
 * @messageid => the message id computed previously
 */
function __sendmail_objsaver($mail, $messageid)
{
    $prefix = get_directory('dirs/outboxdir') . $messageid;
    $file = $prefix . '.obj';
    file_put_contents($file, serialize($mail));
    chmod_protected($file, 0666);
    return $file;
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_prepare($action, $email_id)
{
    require_once 'apps/emails/php/getmail.php';
    $query = "SELECT id
        FROM app_emails_accounts
        WHERE user_id = ?
            AND email_disabled = 0
            AND smtp_host != ''
            AND email_default = 1
        LIMIT 1";
    $account_id = execute_query($query, [current_user()]);
    if (!$account_id) {
        $query = "SELECT id FROM (
            SELECT id,(
                SELECT COUNT(*) FROM app_emails_address
                WHERE email_id IN (
                    SELECT id FROM app_emails
                    WHERE account_id = a.id AND is_outbox = 1
                ) AND type_id IN (1,2,3,4)
            ) counter, email_default
            FROM app_emails_accounts a
            WHERE user_id = ? AND email_disabled = 0 AND smtp_host != ''
            ORDER BY email_default DESC, counter DESC
            LIMIT 1) z";
        $account_id = execute_query($query, [current_user()]);
    }
    $to_extra = [];
    $cc_extra = [];
    $bcc_extra = [];
    $state_crt = '';
    $subject_extra = '';
    $body_extra = '';
    if (in_array($action, ['reply', 'replyall', 'forward'])) {
        $query = 'SELECT account_id FROM app_emails WHERE id = ?';
        $result2 = execute_query($query, [$email_id]);
        if ($result2 && $account_id != $result2) {
            $account_id = $result2;
        }
    }
    // GET THE DEFAULT ADDMETOCC
    $query = 'SELECT * FROM app_emails_accounts WHERE user_id = ? AND id = ?';
    $result2 = execute_query($query, [current_user(), $account_id]);
    if ($result2 && $result2['email_addmetocc']) {
        $cc_extra[] = $result2['email_name'] . ' <' . $result2['email_from'] . '>';
    }
    // GET THE DEFAULT CRT
    if ($result2) {
        $state_crt = $result2['email_crt'];
    }
    // GET THE DEFAULT SIGNATURE
    if ($result2) {
        $body_extra = __HTML_NEWLINE__ . __SECTION_OPEN__ . __SIGNATURE_OPEN__ .
            $result2['email_signature'] . __SIGNATURE_CLOSE__ . __SECTION_CLOSE__;
    } else {
        $body_extra = __HTML_NEWLINE__ . __SECTION_OPEN__ . __SECTION_CLOSE__;
    }
    if (in_array($action, ['reply', 'replyall'])) {
        $query = 'SELECT * FROM app_emails_address WHERE email_id = ?';
        $result2 = execute_query_array($query, [$email_id]);
        foreach ($result2 as $addr) {
            if ($addr['type_id'] == 6) {
                $finded_replyto = $addr;
            }
            if ($addr['type_id'] == 1) {
                $finded_from = $addr;
            }
        }
        if (isset($finded_replyto) || isset($finded_from)) {
            $finded = null;
            if (isset($finded_replyto)) {
                $finded = $finded_replyto;
            } elseif (isset($finded_from)) {
                $finded = $finded_from;
            }
            if ($finded['name'] != '') {
                $to_extra[] = $finded['name'] . ' <' . $finded['value'] . '>';
            } else {
                $to_extra[] = $finded['value'];
            }
        }
    }
    if ($action == 'replyall') {
        if (isset($finded_replyto) && isset($finded_from)) {
            $finded_tocc = [];
            $finded_tocc[] = $finded_from;
        }
        foreach ($result2 as $addr) {
            if ($addr['type_id'] == 2 || $addr['type_id'] == 3) {
                if (!isset($finded_tocc)) {
                    $finded_tocc = [];
                }
                $finded_tocc[] = $addr;
            }
        }
        if (isset($finded_tocc)) {
            if (isset($finded)) {
                foreach ($finded_tocc as $key2 => $addr) {
                    if ($addr['value'] == $finded['value']) {
                        unset($finded_tocc[$key2]);
                    }
                }
            }
            $query = 'SELECT * FROM app_emails_accounts WHERE user_id = ? AND id = ?';
            $result2 = execute_query_array($query, [current_user(), $account_id]);
            foreach ($result2 as $addr) {
                foreach ($finded_tocc as $key2 => $addr2) {
                    if ($addr2['value'] == $addr['email_from']) {
                        unset($finded_tocc[$key2]);
                    }
                }
            }
            foreach ($finded_tocc as $addr) {
                if ($addr['name'] != '') {
                    $cc_extra[] = $addr['name'] . ' <' . $addr['value'] . '>';
                } else {
                    $cc_extra[] = $addr['value'];
                }
            }
        }
    }
    if ($action == 'forward') {
        $query = 'SELECT * FROM app_emails_address WHERE email_id = ?';
        $result2 = execute_query_array($query, [$email_id]);
        foreach ($result2 as $addr) {
            if ($addr['type_id'] == 1) {
                $finded_from = $addr;
            }
        }
    }
    if (in_array($action, ['reply', 'replyall', 'forward'])) {
        $query = 'SELECT * FROM app_emails WHERE id = ?';
        $row2 = execute_query($query, [$email_id]);
        if ($row2 && isset($row2['subject'])) {
            $subject_extra = $row2['subject'];
            $prefixes = ['reply' => 'Re: ', 'replyall' => 'Re: ', 'forward' => 'Fwd: '];
            $prefix = $prefixes[$action];
            if (strncasecmp($subject_extra, $prefix, strlen($prefix)) != 0) {
                $subject_extra = $prefix . $subject_extra;
            }
        }
        if (isset($row2['datetime']) && isset($finded_from)) {
            $oldhead = '';
            $oldhead .= __HTML_TEXT_OPEN__;
            $oldhead .= sprintf(
                T('The %s, %s wrote:'), $row2['datetime'],
                $finded_from['name'] ? $finded_from['name'] : $finded_from['value']
            );
            $oldhead .= __HTML_TEXT_CLOSE__;
            $oldbody = '';
            $decoded = __getmail_getmime($email_id);
            if ($action == 'forward') {
                $oldbody .= __getmail_head_helper($decoded, $email_id);
            }
            $oldbody .= __getmail_body_helper($decoded, true);
            $body_extra .= __HTML_NEWLINE__ . $oldhead . __HTML_NEWLINE__ .
                __BLOCKQUOTE_OPEN__ . $oldbody . __BLOCKQUOTE_CLOSE__ . __HTML_NEWLINE__;
            unset($oldhead); // TRICK TO RELEASE MEMORY
            unset($oldbody); // TRICK TO RELEASE MEMORY
            unset($decoded); // TRICK TO RELEASE MEMORY
        }
    }
    // CONTINUE
    $to_extra = implode('; ', $to_extra);
    $cc_extra = implode('; ', $cc_extra);
    $bcc_extra = implode('; ', $bcc_extra);
    return ['from' => $account_id,
        'to' => $to_extra, 'cc' => $cc_extra, 'bcc' => $bcc_extra,
        'subject' => $subject_extra, 'body' => $body_extra,
        'state_crt' => $state_crt, 'priority' => 0, 'sensitivity' => 0,
        'files' => sendmail_files($action, $email_id),
    ];
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_action($json, $action, $email_id)
{
    require_once 'apps/emails/php/getmail.php';
    require_once 'php/lib/upload.php';
    // GET ALL DATA
    $account_id = intval($json['from'] ?? 0);
    $to = strval($json['to'] ?? '');
    $cc = strval($json['cc'] ?? '');
    $bcc = strval($json['bcc'] ?? '');
    $subject = strval($json['subject'] ?? '');
    $body = strval($json['body'] ?? '');
    $state_crt = intval($json['state_crt'] ?? 0);
    $priority = intval($json['priority'] ?? 0);
    $sensitivity = intval($json['sensitivity'] ?? 0);
    $uploads = array_protected($json['files'] ?? []);
    // SEARCH FROM
    $query = "SELECT CONCAT(email_name,' <',email_from,'>') email
        FROM app_emails_accounts WHERE user_id = ? AND id = ?";
    $from = execute_query($query, [current_user(), $account_id]);
    if (!$from) {
        show_php_error(['phperror' => 'From not found']);
    }
    // REMOVE THE SIGNATURE TAG IF EXISTS
    $body = str_replace([__SECTION_OPEN__, __SECTION_CLOSE__], '', $body);
    // PREPARE THE RECIPIENTS
    $recipients = [];
    $to = explode(';', $to);
    foreach ($to as $addr) {
        $addr = trim($addr);
        if ($addr != '') {
            $recipients[] = 'to:' . $addr;
        }
    }
    $cc = explode(';', $cc);
    foreach ($cc as $addr) {
        $addr = trim($addr);
        if ($addr != '') {
            $recipients[] = 'cc:' . $addr;
        }
    }
    $bcc = explode(';', $bcc);
    foreach ($bcc as $addr) {
        $addr = trim($addr);
        if ($addr != '') {
            $recipients[] = 'bcc:' . $addr;
        }
    }
    // ADD EXTRAS IN THE RECIPIENTS
    if ($state_crt) {
        $recipients[] = 'crt:' . $from;
    }
    $priorities = [-1 => '5 (Low)', 1 => '1 (High)'];
    if (isset($priorities[$priority])) {
        $recipients[] = 'priority:' . $priorities[$priority];
    }
    $sensitivities = [1 => 'Personal', 2 => 'Private', 3 => 'Company-Confidential'];
    if (isset($sensitivities[$sensitivity])) {
        $recipients[] = 'sensitivity:' . $sensitivities[$sensitivity];
    }
    // ADD UPLOADED ATTACHMENTS
    $files = [];
    $dir = get_directory('dirs/uploaddir') ?? getcwd_protected() . '/data/upload/';
    foreach ($uploads as $file) {
        if (
            check_upload_file([
                'user_id' => current_user(),
                'uniqid' => $file['id'],
                'app' => $file['app'],
                'name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'file' => $file['file'],
                'hash' => $file['hash'],
            ])
        ) {
            $files[] = [
                'file' => $dir . $file['file'],
                'name' => $file['name'],
                'mime' => $file['type'],
            ];
        }
    }
    // TRY TO CONVERT THE INLINE IMAGES INTO ATTACHMENTS WITH CID
    require_once 'apps/emails/php/html.php';
    [$body, $files1] = extract_img_tag($body);
    [$body, $files2] = extract_img_style($body);
    [$body, $files3] = extract_img_background($body);
    foreach (array_merge($files1, $files2, $files3) as $hash => $file) {
        $files[] = [
            'data' => $file['data'],
            'name' => mime2name($file['type']),
            'mime' => $file['type'],
            'cid' => $hash,
        ];
    }
    // DO THE SEND ACTION
    $last_id = sendmail($account_id, $recipients, $subject, $body, $files);
    if (is_int($last_id)) {
        // NOTHING TO DO
    } elseif (is_string($last_id)) {
        // CANCEL THE ACTION
        return [
            'status' => 'ko',
            'text' => $last_id,
        ];
    } else {
        show_php_error(['phperror' => 'Internal error']);
    }
    // SOME UPDATES
    if (in_array($action, ['reply', 'replyall', 'forward'])) {
        __getmail_update('email_id', $email_id, $last_id);
        $campo = null;
        if ($action == 'reply') {
            $campo = 'state_reply';
        }
        if ($action == 'replyall') {
            $campo = 'state_reply';
        }
        if ($action == 'forward') {
            $campo = 'state_forward';
        }
        __getmail_update($campo, 1, $email_id);
    }
    // REMOVE THE UPLOADED FILES
    foreach ($uploads as $file) {
        del_upload_file($file);
    }
    // FINISH THE ACTION
    return [
        'status' => 'ok',
        'text' => T('Email sent successfully'),
    ];
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_server()
{
    // check the semaphore
    $semaphore = [__FUNCTION__, current_user()];
    if (!semaphore_acquire($semaphore, 100000)) {
        return [T('Could not acquire the semaphore')];
    }
    // begin the spool operation
    $query = 'SELECT a.id, a.account_id, a.uidl
        FROM app_emails a
        LEFT JOIN app_emails_accounts c ON c.id = a.account_id
        WHERE c.user_id = ? AND a.is_outbox = 1 AND a.state_sent = 0';
    $result = execute_query_array($query, [current_user()]);
    require_once 'apps/emails/lib/phpmailer/vendor/autoload.php';
    require_once 'apps/emails/php/getmail.php';
    $sended = 0;
    $haserror = [];
    foreach ($result as $row) {
        if (time_get_usage() > get_config('server/percentstop')) {
            break;
        }
        $last_id = $row['id'];
        $messageid = $row['account_id'] . '/' . $row['uidl'];
        $file = get_directory('dirs/outboxdir') . $messageid . '.obj';
        if (!file_exists($file)) {
            $error = T('This email was not sent by an internal error');
            __getmail_update('state_sent', 1, $last_id);
            __getmail_update('state_error', $error, $last_id);
            $haserror[] = $error;
            continue;
        }
        $mail = unserialize(file_get_contents($file));
        if (!$mail || !method_exists($mail, 'PostSend')) {
            $error = T('This email was not sent by an internal error');
            __getmail_update('state_sent', 1, $last_id);
            __getmail_update('state_error', $error, $last_id);
            unlink($file);
            $haserror[] = $error;
            continue;
        }
        /** @var PHPMailer $mail */
        ob_start();
        $current = $mail->PostSend();
        $error = ob_get_clean();
        if ($current !== true) {
            $host = $mail->Host;
            $port = $mail->Port;
            $extra = $mail->SMTPSecure;
            $user = $mail->Username;
            $pass = $mail->Password;
            // FIND ACCOUNT DATA
            $query = 'SELECT * FROM app_emails_accounts WHERE id = ?';
            $result2 = execute_query($query, [$row['account_id']]);
            $current_host = $result2['smtp_host'];
            $current_port = $result2['smtp_port'] ? $result2['smtp_port'] : 25;
            $current_extra = $result2['smtp_extra'];
            $current_user = $result2['smtp_user'];
            $current_pass = $result2['smtp_pass'];
            // CONTINUE
            $idem = 1;
            if ($current_host != $host) {
                $idem = 0;
            }
            if ($current_port != $port) {
                $idem = 0;
            }
            if ($current_extra != $extra) {
                $idem = 0;
            }
            if ($current_user != $user) {
                $idem = 0;
            }
            if ($current_pass != $pass) {
                $idem = 0;
            }
            if (!$idem) {
                if (!in_array($current_host, ['mail', 'sendmail', 'qmail', ''])) {
                    $mail->IsSMTP();
                    $mail->set('Host', $current_host);
                    $mail->set('Port', $current_port);
                    $mail->set('SMTPSecure', $current_extra);
                    $mail->set('Username', $current_user);
                    $mail->set('Password', $current_pass);
                    $mail->set('SMTPAuth', ($current_user != '' || $current_pass != ''));
                    $mail->set('Hostname', $current_host);
                } else {
                    if ($current_host == 'mail') {
                        $mail->IsMail();
                    } elseif ($current_host == 'sendmail') {
                        $mail->IsSendmail();
                    } elseif ($current_host == 'qmail') {
                        $mail->IsQmail();
                    }
                }
                ob_start();
                $current = $mail->PostSend();
                $error = ob_get_clean();
            }
        }
        if ($current !== true) {
            if (!$error) {
                $error = $mail->ErrorInfo;
            }
            __getmail_update('state_sent', 0, $last_id);
            __getmail_update('state_error', $error, $last_id);
            $haserror[] = $error;
        } else {
            __getmail_update('state_sent', 1, $last_id);
            __getmail_update('state_error', '', $last_id);
            unlink($file);
            $sended++;
        }
    }
    $haserror[] = sprintf(T('%d email(s) sended'), $sended);
    // intended to be used by cron feature
    if (get_data('server/xuid') && $sended) {
        require_once 'php/lib/push.php';
        push_insert('event', 'saltos.emails.update');
    }
    // release the semaphore
    semaphore_release($semaphore);
    return $haserror;
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_files($action, $email_id)
{
    if ($action == 'forward' && $email_id) {
        require_once 'apps/emails/php/getmail.php';
        require_once 'php/lib/upload.php';
        if (!__getmail_checkperm($email_id)) {
            show_php_error(['phperror' => 'Permission denied']);
        }
        $decoded = __getmail_getmime($email_id);
        if (!$decoded) {
            show_php_error(['phperror' => 'Could not decode de message']);
        }
        // CONTINUE
        $result = __getmail_getfiles(__getmail_getnode('0', $decoded));
        foreach ($result as $val) {
            // Check that attachment is not found in the upload table
            $id = check_upload_file([
                'user_id' => current_user(),
                'uniqid' => $val['chash'],
                'app' => current_hash(),
                'name' => $val['cname'],
                'size' => $val['csize'],
                'type' => $val['ctype'],
                'hash' => md5($val['body']),
            ]);
            if ($id) {
                continue;
            }
            // Store it in a local file and do the insert
            add_upload_file([
                'id' => $val['chash'],
                'app' => current_hash(),
                'name' => $val['cname'],
                'size' => $val['csize'],
                'type' => $val['ctype'],
                'data' => mime_inline($val['ctype'], $val['body']),
            ]);
        }
    }
    $query = "SELECT uniqid id,app,name,size,type,'' data,'' error,file,hash
        FROM tbl_uploads WHERE user_id = ? AND app = ? ORDER BY id DESC";
    $files = execute_query_array($query, [current_user(), current_hash()]);
    return $files;
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_signature($json)
{
    require_once 'apps/emails/php/getmail.php';
    $old = intval($json['old'] ?? 0);
    $new = intval($json['new'] ?? 0);
    $body = strval($json['body'] ?? '');
    $cc = strval($json['cc'] ?? '');
    $state_crt = intval($json['state_crt'] ?? 0);
    // FIND THE OLD AND NEW CC'S AND STATE_CRT'S
    $query = 'SELECT * FROM app_emails_accounts WHERE user_id = ? AND id = ?';
    $result_old = execute_query($query, [current_user(), $old]);
    $result_new = execute_query($query, [current_user(), $new]);
    // REPLACE THE SIGNATURE BODY
    if ($result_new) {
        $auto = __SIGNATURE_OPEN__ . $result_new['email_signature'] . __SIGNATURE_CLOSE__;
    } else {
        $auto = '';
    }
    $pos1 = strpos($body, __SECTION_OPEN__);
    if ($pos1 !== false) {
        $pos1 += strlen(__SECTION_OPEN__);
    }
    $pos2 = strpos($body, __SECTION_CLOSE__);
    if ($pos1 !== false && $pos2 !== false) {
        $body = substr_replace($body, $auto, $pos1, $pos2 - $pos1);
    }
    // REPLACE THE CC
    if ($result_old && $result_new) {
        $cc = explode(';', $cc);
        foreach ($cc as $key => $val) {
            $val = trim($val);
            if ($val) {
                $cc[$key] = $val;
            } else {
                unset($cc[$key]);
            }
        }
        if ($result_old['email_addmetocc']) {
            foreach ($cc as $key => $val) {
                list($email_from, $email_name) = __sendmail_parser($val);
                if ($result_old['email_from'] == $email_from && $result_old['email_name'] == $email_name) {
                    unset($cc[$key]);
                }
            }
        }
        if ($result_new['email_addmetocc']) {
            foreach ($cc as $key => $val) {
                list($email_from, $email_name) = __sendmail_parser($val);
                if ($result_new['email_from'] == $email_from && $result_new['email_name'] == $email_name) {
                    unset($cc[$key]);
                }
            }
            array_unshift($cc, $result_new['email_name'] . ' <' . $result_new['email_from'] . '>');
        }
        $cc = implode('; ', $cc);
    }
    // REPLACE THE STATE_CRT
    if ($result_old && $result_new) {
        if ($result_old['email_crt'] == $state_crt) {
            $state_crt = $result_new['email_crt'];
        }
    }
    // PREPARE THE OUTPUT
    return ['body' => $body, 'cc' => $cc, 'state_crt' => $state_crt];
}
