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
 */
function sendmail($account_id, $to, $subject, $body, $files = "")
{
    require_once "lib/phpmailer/vendor/autoload.php";
    require_once "php/getmail.php";
    // Check for special account_id case
    if (is_array($account_id)) {
        if (count($account_id) != 2) {
            return "account_id error1";
        }
        list($account_id0, $account_id1) = array_values($account_id);
        if (is_numeric($account_id1) && is_string($account_id0)) {
            list($account_id0, $account_id1) = [$account_id1, $account_id0];
        }
        if (!is_numeric($account_id0) || !is_string($account_id1)) {
            return "account_id error2";
        }
    }
    // FIND ACCOUNT DATA
    if (isset($account_id0)) {
        $account_id = $account_id0;
    }
    $query = "SELECT * FROM app_emails_accounts WHERE id='$account_id'";
    $result = execute_query($query);
    if (!isset($result["id"])) {
        return "id not found";
    }
    if ($result["email_disabled"]) {
        return "email disabled";
    }
    $host = $result["smtp_host"];
    $port = $result["smtp_port"];
    $extra = $result["smtp_extra"];
    $user = $result["smtp_user"];
    $pass = $result["smtp_pass"];
    $from = $result["email_from"];
    $fromname = $result["email_name"];
    // CONTINUE
    $mail = new PHPMailer();
    if (!$mail->set("XMailer", get_name_version_revision())) {
        return $mail->ErrorInfo;
    }
    if (!$mail->AddCustomHeader("X-Originating-IP", getServer("REMOTE_ADDR"))) {
        if ($mail->ErrorInfo) {
            return $mail->ErrorInfo;
        }
    }
    if (!$mail->SetLanguage("es")) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("CharSet", "UTF-8")) {
        return $mail->ErrorInfo;
    }
    if (isset($account_id1)) {
        $fromname = $account_id1;
    }
    if (!$mail->SetFrom($from, $fromname)) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("WordWrap", 50)) {
        return $mail->ErrorInfo;
    }
    $options = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true,
        ],
    ];
    if (!$mail->set("SMTPOptions", $options)) {
        return $mail->ErrorInfo;
    }
    $mail->IsHTML();
    if (!in_array($host, ["mail", "sendmail", "qmail", ""])) {
        $mail->IsSMTP();
        if (!$mail->set("Host", $host)) {
            return $mail->ErrorInfo;
        }
        if ($port != "") {
            if (!$mail->set("Port", $port)) {
                return $mail->ErrorInfo;
            }
        }
        if ($extra != "") {
            if (!$mail->set("SMTPSecure", $extra)) {
                return $mail->ErrorInfo;
            }
        }
        if (!$mail->set("Username", $user)) {
            return $mail->ErrorInfo;
        }
        if (!$mail->set("Password", $pass)) {
            return $mail->ErrorInfo;
        }
        if (!$mail->set("SMTPAuth", ($user != "" || $pass != ""))) {
            return $mail->ErrorInfo;
        }
        if (!$mail->set("Hostname", $host)) {
            return $mail->ErrorInfo;
        }
    } else {
        if ($host == "mail") {
            $mail->IsMail();
        } elseif ($host == "sendmail") {
            $mail->IsSendmail();
        } elseif ($host == "qmail") {
            $mail->IsQmail();
        }
    }
    if (!$mail->set("Subject", $subject)) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("Body", $body)) {
        return $mail->ErrorInfo;
    }
    if (!$mail->set("AltBody", html2text($body))) {
        return $mail->ErrorInfo;
    }
    if (is_array($files)) {
        foreach ($files as $file) {
            if (isset($file["data"]) && !isset($file["cid"])) {
                $mail->AddStringAttachment($file["data"], $file["name"], "base64", $file["mime"]);
            }
            if (isset($file["file"]) && !isset($file["cid"])) {
                if (!$mail->AddAttachment($file["file"], $file["name"], "base64", $file["mime"])) {
                    return $mail->ErrorInfo;
                }
            }
            if (isset($file["data"]) && isset($file["cid"])) {
                $mail->AddStringEmbeddedImage(
                    $file["data"],
                    $file["cid"],
                    $file["name"],
                    "base64",
                    $file["mime"]
                );
            }
            if (isset($file["file"]) && isset($file["cid"])) {
                if (
                    !$mail->AddEmbeddedImage(
                        $file["file"],
                        $file["cid"],
                        $file["name"],
                        "base64",
                        $file["mime"]
                    )
                ) {
                    return $mail->ErrorInfo;
                }
            }
        }
    }
    $bcc = [];
    if (is_array($to)) {
        $valids = ["to:", "cc:", "bcc:", "crt:", "priority:", "sensitivity:", "replyto:"];
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
                if (!$mail->set("ConfirmReadingTo", $addr)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[4]) {
                if (!$mail->set("Priority", $addr)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
            }
            if ($type == $valids[5]) {
                if (!$mail->AddCustomHeader("Sensitivity", $addr)) {
                    if ($mail->ErrorInfo) {
                        return $mail->ErrorInfo;
                    }
                }
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
    capture_next_error();
    $current = $mail->PreSend();
    get_clear_error();
    if (!$current) {
        return $mail->ErrorInfo;
    }
    if (!semaphore_acquire(__FUNCTION__)) {
        show_php_error(["phperror" => "Could not acquire the semaphore"]);
    }
    $messageid = __sendmail_messageid($account_id, $mail->From);
    $file1 = __sendmail_emlsaver($mail->GetSentMIMEMessage(), $messageid);
    $file2 = __sendmail_objsaver($mail, $messageid);
    $last_id = __getmail_insert($file1, $messageid, 0, 0, 0, 0, 0, 1, 1, "");
    semaphore_release(__FUNCTION__);
    if (count($bcc)) {
        __getmail_add_bcc($last_id, $bcc); // BCC DOESN'T APPEAR IN THE RFC822 SOMETIMES
    }
    if (CONFIG("email_async")) {
        __getmail_update("state_sent", 0, $last_id);
        __getmail_update("state_error", "", $last_id);
        return "";
    }
    capture_next_error();
    $current = $mail->PostSend();
    $error = get_clear_error();
    if (words_exists("PostSend non-object", $error)) {
        __getmail_update("state_sent", 1, $last_id);
        __getmail_update("state_error", T("This email was not sent by an internal error"), $last_id);
        unlink($file2);
        return T("This email was not sent by an internal error");
    }
    if (!$current) {
        if (words_exists("connection refused", $error)) {
            $error = T("Connection refused by server");
        } elseif (words_exists("unable to connect", $error)) {
            $error = T("Can not connect to server");
        } else {
            $orig = ["\n", "\r", "'", "\""];
            $dest = [" ", "", "", ""];
            $error = str_replace($orig, $dest, $mail->ErrorInfo);
        }
        __getmail_update("state_sent", 0, $last_id);
        __getmail_update("state_error", $error, $last_id);
        return $error;
    }
    __getmail_update("state_sent", 1, $last_id);
    __getmail_update("state_error", "", $last_id);
    unlink($file2);
    return "";
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
    $pos1 = strpos($oldaddr, "<");
    $pos2 = strpos($oldaddr, ">");
    if ($pos1 !== false && $pos2 !== false) {
        $name = trim(substr($oldaddr, 0, $pos1));
        $addr = trim(substr($oldaddr, $pos1 + 1, $pos2 - $pos1 - 1));
    } else {
        $name = "";
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
    require_once "php/getmail.php";
    $prefix = get_directory("dirs/outboxdir") . $account_id;
    if (!file_exists($prefix)) {
        mkdir($prefix);
        chmod($prefix, 0777);
    }
    $query = "SELECT MAX(id) FROM app_emails";
    $count = execute_query($query);
    if (!$count) {
        $count = 1;
    }
    $uidl2 = sprintf("%08X", crc32($from));
    for (;;) {
        $uidl1 = sprintf("%08X", $count);
        $file = $prefix . "/" . $uidl1 . $uidl2 . ".eml.gz";
        if (!file_exists($file)) {
            break;
        }
        $count++;
    }
    return $account_id . "/" . $uidl1 . $uidl2;
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
    require_once "php/getmail.php";
    $prefix = get_directory("dirs/outboxdir") . $messageid;
    $file = $prefix . ".eml.gz";
    $fp = gzopen($file, "w");
    gzwrite($fp, $message);
    gzclose($fp);
    chmod($file, 0666);
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
    $prefix = get_directory("dirs/outboxdir") . $messageid;
    $file = $prefix . ".obj";
    file_put_contents($file, serialize($mail));
    chmod($file, 0666);
    return $file;
}

/**
 * TODO
 *
 * TODO
 */
function __signature_getfile($id)
{
    if (!$id) {
        return null;
    }
    $query = "SELECT * FROM app_emails_accounts WHERE id='$id'";
    $row = execute_query($query);
    if (!$row) {
        return null;
    }
    if (!$row["email_signature_file"]) {
        return null;
    }
    $id = $row["id"];
    $name = $row["email_signature"];
    $file = $row["email_signature_file"];
    $type = $row["email_signature_type"];
    $size = $row["email_signature_size"];
    $data = file_get_contents(get_directory("dirs/filesdir") . $file);
    $alt = $row["email_name"] . " (" . $row["email_from"] . ")";
    return [
        "id" => $id,
        "name" => $name,
        "file" => $file,
        "type" => $type,
        "size" => $size,
        "data" => $data,
        "alt" => $alt,
    ];
}

/**
 * TODO
 *
 * TODO
 */
function __signature_getauto($file)
{
    if (!$file) {
        return null;
    }
    if (!$file["file"]) {
        return null;
    }
    if ($file["type"] == "text/plain") {
        $file["auto"] = trim($file["data"]);
        $file["auto"] = htmlentities($file["auto"], ENT_COMPAT, "UTF-8");
        $file["auto"] = str_replace(
            [" ", "\t", "\n"],
            ["&nbsp;", str_repeat("&nbsp;", 8), "<br/>"],
            $file["auto"]
        );
    } elseif ($file["type"] == "text/html") {
        $file["auto"] = trim($file["data"]);
    } elseif (substr($file["type"], 0, 6) == "image/") {
        $file["src"] = mime_inline($file["type"], $file["data"]);
        $file["auto"] = "<img alt=\"{$file["alt"]}\" border=\"0\" src=\"{$file["src"]}\" />";
    } else {
        $file["auto"] = "Name: {$file["name"]}<br/>Type: {$file["type"]}<br/>Size: {$file["size"]}";
    }
    require_once "apps/emails/php/getmail.php";
    $file["auto"] = __SIGNATURE_OPEN__ . "<p>--</p>" . $file["auto"] . __SIGNATURE_CLOSE__;
    return $file;
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_prepare($action, $email_id)
{
    $query = "SELECT id
        FROM app_emails_accounts
        WHERE user_id='" . current_user() . "'
            AND email_disabled='0'
            AND smtp_host!=''
            AND email_default='1'
        LIMIT 1";
    $account_id = execute_query($query);
    if (!$account_id) {
        $query = "SELECT id FROM (
            SELECT id,(
                SELECT COUNT(*)
                FROM app_emails_address
                WHERE email_id IN (
                    SELECT id
                    FROM app_emails
                    WHERE account_id=a.id
                        AND is_outbox=1
                )
                AND type_id IN (1,2,3,4)
            ) counter,
            email_default
            FROM app_emails_accounts a
            WHERE user_id='" . current_user() . "'
                AND email_disabled='0'
                AND smtp_host!=''
            ORDER BY email_default DESC,counter DESC
            LIMIT 1) z";
        $account_id = execute_query($query);
    }
    $to_extra = [];
    $cc_extra = [];
    $bcc_extra = [];
    $state_crt = "";
    $subject_extra = "";
    $body_extra = "";
    if (in_array($action, ["reply", "replyall", "forward"])) {
        $query = "SELECT account_id FROM app_emails WHERE id='{$email_id}'";
        $result2 = execute_query($query);
        if ($result2 && $account_id != $result2) {
            $account_id = $result2;
        }
    }
    if (1) { // GET THE DEFAULT ADDMETOCC
        $query = "SELECT * FROM app_emails_accounts
            WHERE user_id='" . current_user() . "' AND id='$account_id'";
        $result2 = execute_query($query);
        if ($result2 && $result2["email_addmetocc"]) {
            $cc_extra[] = $result2["email_name"] . " <" . $result2["email_from"] . ">";
        }
    }
    if (1) { // GET THE DEFAULT CRT
        $query = "SELECT * FROM app_emails_accounts
            WHERE user_id='" . current_user() . "' AND id='$account_id'";
        $result2 = execute_query($query);
        if ($result2) {
            $state_crt = $result2["email_crt"];
        }
    }
    if (1) { // GET THE DEFAULT SIGNATURE
        $file = __signature_getauto(__signature_getfile($account_id));
        $body_extra = __HTML_NEWLINE__ . "<signature>" . ($file ? $file["auto"] : "") . "</signature>";
    }
    if (in_array($action, ["reply", "replyall"])) {
        $query = "SELECT * FROM app_emails_address WHERE email_id='{$email_id}'";
        $result2 = execute_query_array($query);
        foreach ($result2 as $addr) {
            if ($addr["type_id"] == 6) {
                $finded_replyto = $addr;
            }
            if ($addr["type_id"] == 1) {
                $finded_from = $addr;
            }
        }
        if (isset($finded_replyto) || isset($finded_from)) {
            if (isset($finded_replyto)) {
                $finded = $finded_replyto;
            } elseif (isset($finded_from)) {
                $finded = $finded_from;
            }
            if ($finded["name"] != "") {
                $to_extra[] = $finded["name"] . " <" . $finded["value"] . ">";
            } else {
                $to_extra[] = $finded["value"];
            }
        }
    }
    if ($action == "replyall") {
        if (isset($finded_replyto) && isset($finded_from)) {
            $finded_tocc = [];
            $finded_tocc[] = $finded_from;
        }
        foreach ($result2 as $addr) {
            if ($addr["type_id"] == 2 || $addr["type_id"] == 3) {
                if (!isset($finded_tocc)) {
                    $finded_tocc = [];
                }
                $finded_tocc[] = $addr;
            }
        }
        if (isset($finded_tocc)) {
            if (isset($finded)) {
                foreach ($finded_tocc as $key2 => $addr) {
                    if ($addr["value"] == $finded["value"]) {
                        unset($finded_tocc[$key2]);
                    }
                }
            }
            $query = "SELECT * FROM app_emails_accounts
                WHERE user_id='" . current_user() . "' AND id='$account_id'";
            $result2 = execute_query_array($query);
            foreach ($result2 as $addr) {
                foreach ($finded_tocc as $key2 => $addr2) {
                    if ($addr2["value"] == $addr["email_from"]) {
                        unset($finded_tocc[$key2]);
                    }
                }
            }
            foreach ($finded_tocc as $addr) {
                if ($addr["name"] != "") {
                    $cc_extra[] = $addr["name"] . " <" . $addr["value"] . ">";
                } else {
                    $cc_extra[] = $addr["value"];
                }
            }
        }
    }
    if ($action == "forward") {
        $query = "SELECT * FROM app_emails_address WHERE email_id='{$email_id}'";
        $result2 = execute_query_array($query);
        foreach ($result2 as $addr) {
            if ($addr["type_id"] == 1) {
                $finded_from = $addr;
            }
        }
    }
    if (in_array($action, ["reply", "replyall", "forward"])) {
        require_once "apps/emails/php/getmail.php";
        $query = "SELECT * FROM app_emails WHERE id='{$email_id}'";
        $row2 = execute_query($query);
        if ($row2 && isset($row2["subject"])) {
            $subject_extra = $row2["subject"];
            $prefixes = ["reply" => "Re: ", "replyall" => "Re: ", "forward" => "Fwd: "];
            $prefix = $prefixes[$action];
            if (strncasecmp($subject_extra, $prefix, strlen($prefix)) != 0) {
                $subject_extra = $prefix . $subject_extra;
            }
        }
        if (isset($row2["datetime"]) && isset($finded_from)) {
            $oldhead = "";
            $oldhead .= __HTML_TEXT_OPEN__;
            $oldhead .= T("The #datetime#, #fromname# wrote:");
            $oldhead .= __HTML_TEXT_CLOSE__;
            $oldhead = str_replace("#datetime#", $row2["datetime"], $oldhead);
            $oldhead = str_replace(
                "#fromname#",
                $finded_from["name"] ? $finded_from["name"] : $finded_from["value"],
                $oldhead
            );
            $oldbody = "";
            $decoded = __getmail_getmime($email_id);
            if ($action == "forward") {
                $result2 = __getmail_getinfo(__getmail_getnode("0", $decoded));
                $lista = ["from", "to", "cc", "bcc"];
                foreach ($lista as $temp) {
                    unset($result2[$temp]);
                }
                foreach ($result2["emails"] as $email) {
                    if ($email["name"] != "") {
                        $email["value"] = "{$email["name"]} <{$email["value"]}>";
                    }
                    if (!isset($result2[$email["type"]])) {
                        $result2[$email["type"]] = [];
                    }
                    $result2[$email["type"]][] = $email["value"];
                }
                if (isset($result2["from"])) {
                    $result2["from"] = implode("; ", $result2["from"]);
                }
                if (isset($result2["to"])) {
                    $result2["to"] = implode("; ", $result2["to"]);
                    $query = "SELECT email_from
                        FROM app_emails_accounts
                        WHERE id=(
                            SELECT account_id
                            FROM app_emails
                            WHERE id='{$email_id}')";
                    $result2["to"] = str_replace("<>", "<" . execute_query($query) . ">", $result2["to"]);
                }
                if (!isset($result2["to"])) {
                    $query = "SELECT CASE
                        WHEN (
                            SELECT email_name
                            FROM app_emails_accounts
                            WHERE id=(
                                SELECT account_id
                                FROM app_emails
                                WHERE id='{$email_id}'
                            )
                        )=''
                        THEN (
                            SELECT email_from
                            FROM app_emails_accounts
                            WHERE id=(
                                SELECT account_id
                                FROM app_emails
                                WHERE id='{$email_id}'
                            )
                        )
                        ELSE (
                            SELECT CONCAT(email_name,' <',email_from,'>')
                            FROM app_emails_accounts
                            WHERE id=(
                                SELECT account_id
                                FROM app_emails
                                WHERE id='{$email_id}'
                            )
                        ) END";
                    $result2["to"] = execute_query($query);
                }
                if (isset($result2["cc"])) {
                    $result2["cc"] = implode("; ", $result2["cc"]);
                }
                if (isset($result2["bcc"])) {
                    $result2["bcc"] = implode("; ", $result2["bcc"]);
                }
                $lista = [
                    "from" => T("from"),
                    "to" => T("to"),
                    "cc" => T("cc"),
                    "bcc" => T("bcc"),
                    "datetime" => T("datetime"),
                    "subject" => T("subject"),
                ];
                if (!isset($result2["from"])) {
                    unset($lista["from"]);
                }
                if (!isset($result2["to"])) {
                    unset($lista["to"]);
                }
                if (!isset($result2["cc"])) {
                    unset($lista["cc"]);
                }
                if (!isset($result2["bcc"])) {
                    unset($lista["bcc"]);
                }
                if (!$result2["subject"]) {
                    $result2["subject"] = T("(no subject)");
                }
                $oldbody .= __HTML_BOX_OPEN__;
                foreach ($lista as $key2 => $val2) {
                    $result2[$key2] = str_replace(["<", ">"], ["&lt;", "&gt;"], $result2[$key2]);
                    $oldbody .= __HTML_TEXT_OPEN__;
                    $oldbody .= $lista[$key2] . ": ";
                    $oldbody .= "<b>" . $result2[$key2] . "</b>";
                    $oldbody .= __HTML_TEXT_CLOSE__;
                }
                $first = 1;
                foreach ($result2["files"] as $file) {
                    $cname = $file["cname"];
                    $hsize = $file["hsize"];
                    if ($first) {
                        $oldbody .= __HTML_TEXT_OPEN__;
                        $oldbody .= T("Attachments") . ": ";
                    } else {
                        $oldbody .= " | ";
                    }
                    $oldbody .= "<b>{$cname}</b> ({$hsize})";
                    $first = 0;
                }
                if (!$first) {
                    $oldbody .= __HTML_TEXT_CLOSE__;
                }
                $oldbody .= __HTML_BOX_CLOSE__;
                $oldbody .= __HTML_SEPARATOR__;
            }
            $result2 = __getmail_getfullbody(__getmail_getnode("0", $decoded));
            $first = 1;
            foreach ($result2 as $index => $node) {
                $disp = $node["disp"];
                $type = $node["type"];
                if (__getmail_processplainhtml($disp, $type)) {
                    $temp = $node["body"];
                    if ($type == "plain") {
                        $temp = wordwrap($temp, 120);
                        $temp = htmlentities($temp, ENT_COMPAT, "UTF-8");
                        $temp = str_replace(
                            [" ", "\t", "\n"],
                            ["&nbsp;", str_repeat("&nbsp;", 8), "<br/>"],
                            $temp
                        );
                    }
                    if ($type == "html") {
                        require_once "php/lib/html.php";
                        $temp = remove_script_tag($temp);
                        $temp = remove_style_tag($temp);
                    }
                    foreach ($result2 as $index2 => $node2) {
                        $disp2 = $node2["disp"];
                        $type2 = $node2["type"];
                        if (
                            !__getmail_processplainhtml($disp2, $type2) &&
                            !__getmail_processmessage($disp2, $type2)
                        ) {
                            $cid2 = $node2["cid"];
                            if ($cid2 != "") {
                                $chash2 = $node2["chash"];
                                $ctype2 = $node2["ctype"];
                                $data = mime_inline($ctype2, $node2["body"]);
                                $temp = str_replace("cid:{$cid2}", $data, $temp);
                            }
                        }
                    }
                    if (!$first) {
                        $oldbody .= __HTML_SEPARATOR__;
                    }
                    if ($type == "plain") {
                        $oldbody .= __PLAIN_TEXT_OPEN__ . $temp . __PLAIN_TEXT_CLOSE__;
                    }
                    if ($type == "html") {
                        $oldbody .= __HTML_TEXT_OPEN__ . $temp . __HTML_TEXT_CLOSE__;
                    }
                    $first = 0;
                }
            }
            $body_extra .= __HTML_NEWLINE__ .
                $oldhead . __BLOCKQUOTE_OPEN__ . $oldbody . __BLOCKQUOTE_CLOSE__;
            unset($oldhead); // TRICK TO RELEASE MEMORY
            unset($oldbody); // TRICK TO RELEASE MEMORY
            unset($decoded); // TRICK TO RELEASE MEMORY
        }
    }
    // CONTINUE
    $to_extra = implode("; ", $to_extra);
    $cc_extra = implode("; ", $cc_extra);
    $bcc_extra = implode("; ", $bcc_extra);
    return ["account_id" => $account_id,
        "to" => $to_extra, "cc" => $cc_extra, "bcc" => $bcc_extra,
        "subject" => $subject_extra, "body" => $body_extra,
        "state_crt" => $state_crt, "priority" => 0, "sensitivity" => 0,
    ];
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_action()
{
    require_once "php/getmail.php";
    require_once "php/sendmail.php";
    // GET ALL DATA
    $prefix = "default_0_";
    $id_extra = explode("_", getParam($prefix . "id_extra"), 3);
    $account_id = intval(getParam($prefix . "account_id"));
    $para = getParam($prefix . "para");
    $cc = getParam($prefix . "cc");
    $bcc = getParam($prefix . "bcc");
    $subject = getParam($prefix . "subject");
    $body = getParam($prefix . "body");
    $state_crt = intval(getParam($prefix . "state_crt"));
    $priority = intval(getParam($prefix . "priority"));
    $sensitivity = intval(getParam($prefix . "sensitivity"));
    // SEARCH FROM
    $query = "SELECT CONCAT(email_name,' <',email_from,'>') email
        FROM app_emails_accounts
        WHERE user_id='" . current_user() . "'
            AND id='{$account_id}'";
    $de = execute_query($query);
    if (!$de) {
        javascript_error(LANG("msgfromkosendmail", "correo"));
        javascript_unloading();
        die();
    }
    // REMOVE THE SIGNATURE TAG IF EXISTS
    $body = str_replace(["<signature>", "</signature>"], "", $body);
    // CHECK FOR MOBILE DEVICES
    if (ismobile()) {
        $source = $body;
        $source = htmlentities($source, ENT_COMPAT, "UTF-8");
        $source = str_replace([" ", "\t", "\n"], ["&nbsp;", str_repeat("&nbsp;", 8), "<br/>"], $source);
        $body = __HTML_PAGE_OPEN__;
        $body .= __PLAIN_TEXT_OPEN__;
        $body .= $source;
        $body .= __PLAIN_TEXT_CLOSE__;
        $body .= __HTML_PAGE_CLOSE__;
    }
    // REPLACE SIGNATURE IF NEEDED AND ADD THE INLINE IMAGE
    $inlines = [];
    require_once "php/libaction.php";
    $file = __signature_getauto(__signature_getfile($account_id));
    if ($file && isset($file["src"])) {
        $cid = md5($file["data"]);
        $prehash = md5($body);
        $file["src"] = str_replace("&", "&amp;", $file["src"]); // CKEDITOR CORRECTION
        $body = str_replace($file["src"], "cid:{$cid}", $body);
        $posthash = md5($body);
        if ($prehash != $posthash) {
            $inlines[] = [
                "body" => $file["data"],
                "cid" => $cid,
                "cname" => $file["name"],
                "ctype" => $file["type"],
            ];
        }
    }
    // PREPARE THE INLINES IMAGES AND EMBEDDED ATTACHMENTS
    $attachs = [];
    if (in_array($action, ["reply", "replyall", "forward"])) {
        $decoded = __getmail_getmime($email_id);
        $result2 = __getmail_getfullbody(__getmail_getnode("0", $decoded));
        $useimginline = eval_bool(getDefault("cache/useimginline"));
        foreach ($result2 as $index2 => $node2) {
            $disp2 = $node2["disp"];
            $type2 = $node2["type"];
            if (!__getmail_processplainhtml($disp2, $type2) && !__getmail_processmessage($disp2, $type2)) {
                $cid2 = $node2["cid"];
                if ($cid2 != "") {
                    $chash2 = $node2["chash"];
                    $prehash = md5($body);
                    if ($useimginline) {
                        $data = base64_encode($node2["body"]);
                        $data = "data:image/png;base64,{$data}";
                        $body = str_replace($data, "cid:{$cid2}", $body);
                    } else {
                        $url = "?action=getmail&id={$email_id}&cid={$chash2}";
                        $url = str_replace("&", "&amp;", $url); // CKEDITOR CORRECTION
                        $body = str_replace($url, "cid:{$cid2}", $body);
                    }
                    $posthash = md5($body);
                    if ($prehash != $posthash) {
                        $inlines[] = __getmail_getcid(__getmail_getnode("0", $decoded), $chash2);
                    }
                }
            }
            if ($action == "forward" && __getmail_processfile($disp2, $type2)) {
                $cname2 = $node2["cname"];
                if ($cname2 != "") {
                    $chash2 = $node2["chash"];
                    $delete = "files_old_{$chash2}_fichero_del";
                    if (!getParam($delete)) {
                        $attachs[] = __getmail_getcid(__getmail_getnode("0", $decoded), $chash2);
                    }
                }
            }
        }
    }
    // PREPARE THE RECIPIENTS
    $recipients = [];
    $para = explode(";", $para);
    foreach ($para as $addr) {
        $addr = trim($addr);
        if ($addr != "") {
            $recipients[] = "to:" . $addr;
        }
    }
    $cc = explode(";", $cc);
    foreach ($cc as $addr) {
        $addr = trim($addr);
        if ($addr != "") {
            $recipients[] = "cc:" . $addr;
        }
    }
    $bcc = explode(";", $bcc);
    foreach ($bcc as $addr) {
        $addr = trim($addr);
        if ($addr != "") {
            $recipients[] = "bcc:" . $addr;
        }
    }
    // ADD EXTRAS IN THE RECIPIENTS
    if ($state_crt) {
        $recipients[] = "crt:" . $de;
    }
    $priorities = [-1 => "5 (Low)", 1 => "1 (High)"];
    if (isset($priorities[$priority])) {
        $recipients[] = "priority:" . $priorities[$priority];
    }
    $sensitivities = [1 => "Personal", 2 => "Private", 3 => "Company-Confidential"];
    if (isset($sensitivities[$sensitivity])) {
        $recipients[] = "sensitivity:" . $sensitivities[$sensitivity];
    }
    // ADD UPLOADED ATTACHMENTS
    $files = [];
    foreach ($_FILES as $file) {
        if (isset($file["tmp_name"]) && $file["tmp_name"] != "" && file_exists($file["tmp_name"])) {
            if (!isset($file["name"])) {
                $file["name"] = basename($file["tmp_name"]);
            }
            if (!isset($file["type"])) {
                $file["type"] = saltos_content_type($file["tmp_name"]);
            }
            $files[] = ["file" => $file["tmp_name"], "name" => $file["name"], "mime" => $file["type"]];
        } else {
            if (isset($file["name"]) && $file["name"] != "") {
                javascript_error(LANG("fileuploaderror") . $file["name"]);
            }
            if (isset($file["error"]) && $file["error"] != "") {
                javascript_error(
                    LANG("fileuploaderror") .
                        upload_error2string($file["error"]) .
                            " (code " . $file["error"] . ")"
                );
            }
            javascript_unloading();
            die();
        }
    }
    // ADD INLINES IMAGES
    foreach ($inlines as $inline) {
        $files[] = [
            "data" => $inline["body"],
            "cid" => $inline["cid"],
            "name" => $inline["cname"],
            "mime" => $inline["ctype"],
        ];
    }
    // ADD EMBEDDED ATTACHMENTS
    foreach ($attachs as $attach) {
        $files[] = [
            "data" => $attach["body"],
            "name" => $attach["cname"],
            "mime" => $attach["ctype"],
        ];
    }
    // DO THE SEND ACTION
    $send = sendmail($account_id, $recipients, $subject, $body, $files);
    if ($send == "") {
        $query = "SELECT MAX(id) FROM app_emails WHERE account_id='{$account_id}' AND is_outbox='1'";
        $last_id = execute_query($query);
        // SOME UPDATES
        if (in_array($action, ["reply", "replyall", "forward"])) {
            __getmail_update("email_id", $email_id, $last_id);
            if ($action == "reply") {
                $campo = "state_reply";
            }
            if ($action == "replyall") {
                $campo = "state_reply";
            }
            if ($action == "forward") {
                $campo = "state_forward";
            }
            __getmail_update($campo, 1, $email_id);
        }
        // FINISH THE ACTION
        session_alert(LANG("msgsendoksendmail", "correo"));
        $go = eval_bool(intval(getParam("returnhere")) ? "true" : "false") ? 0 : -1;
        javascript_history($go);
    } else {
        // CANCEL THE ACTION
        javascript_error($send);
        javascript_unloading();
    }
    die();
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_server()
{
    // CHECK THE SEMAPHORE
    $semaphore = [getParam("action"), current_user()];
    if (!semaphore_acquire($semaphore, getDefault("semaphoretimeout", 100000))) {
        if (!getParam("ajax")) {
            session_error(LANG("msgerrorsemaphore") . getParam("action"));
            javascript_history(-1);
        } else {
            javascript_error(LANG("msgerrorsemaphore") . getParam("action"));
        }
        die();
    }
    // BEGIN THE SPOOL OPERATION
    $query = "SELECT a.id,a.account_id,a.uidl
        FROM app_emails a
        LEFT JOIN app_emails_accounts c ON c.id=a.account_id
        WHERE c.user_id='" . current_user() . "'
            AND a.is_outbox='1'
            AND a.state_sent='0'";
    $result = execute_query_array($query);
    if (!count($result)) {
        if (!getParam("ajax")) {
            session_alert(LANG("msgnotsendfound", "correo"));
            javascript_history(-1);
        }
        semaphore_release($semaphore);
        javascript_headers();
        die();
    }
    require_once "lib/phpmailer/vendor/autoload.php";
    require_once "php/getmail.php";
    $sended = 0;
    $haserror = 0;
    foreach ($result as $row) {
        if (time_get_usage() > getDefault("server/percentstop")) {
            break;
        }
        $last_id = $row["id"];
        $messageid = $row["account_id"] . "/" . $row["uidl"];
        $file = get_directory("dirs/outboxdir") . $messageid . ".obj";
        if (file_exists($file)) {
            $mail = unserialize(file_get_contents($file));
            capture_next_error();
            $current = $mail->PostSend();
            $error = get_clear_error();
            if (words_exists("PostSend non-object", $error)) {
                __getmail_update("state_sent", 1, $last_id);
                __getmail_update("state_error", LANG("interrorsendmail", "correo"), $last_id);
                unlink($file);
                $haserror = 1;
            } else {
                if ($current !== true) {
                    $host = $mail->Host;
                    $port = $mail->Port;
                    $extra = $mail->SMTPSecure;
                    $user = $mail->Username;
                    $pass = $mail->Password;
                    // FIND ACCOUNT DATA
                    $query = "SELECT * FROM app_emails_accounts WHERE id='" . $row["account_id"] . "'";
                    $result2 = execute_query($query);
                    $current_host = $result2["smtp_host"];
                    $current_port = $result2["smtp_port"] ? $result2["smtp_port"] : 25;
                    $current_extra = $result2["smtp_extra"];
                    $current_user = $result2["smtp_user"];
                    $current_pass = $result2["smtp_pass"];
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
                        if (!in_array($current_host, ["mail", "sendmail", "qmail", ""])) {
                            $mail->IsSMTP();
                            $mail->set("Host", $current_host);
                            $mail->set("Port", $current_port);
                            $mail->set("SMTPSecure", $current_extra);
                            $mail->set("Username", $current_user);
                            $mail->set("Password", $current_pass);
                            $mail->set("SMTPAuth", ($current_user != "" || $current_pass != ""));
                            $mail->set("Hostname", $current_host);
                        } else {
                            if ($current_host == "mail") {
                                $mail->IsMail();
                            } elseif ($current_host == "sendmail") {
                                $mail->IsSendmail();
                            } elseif ($current_host == "qmail") {
                                $mail->IsQmail();
                            }
                        }
                        capture_next_error();
                        $current = $mail->PostSend();
                        $error = get_clear_error();
                    }
                }
                if ($current !== true) {
                    if (words_exists("connection refused", $error)) {
                        $error = LANG("msgconnrefusedpop3email", "correo");
                    } elseif (words_exists("unable to connect", $error)) {
                        $error = LANG("msgconnerrorpop3email", "correo");
                    } else {
                        $orig = ["\n", "\r", "'", "\""];
                        $dest = [" ", "", "", ""];
                        $error = str_replace($orig, $dest, $mail->ErrorInfo);
                    }
                    __getmail_update("state_sent", 0, $last_id);
                    __getmail_update("state_error", $error, $last_id);
                    if (!getParam("ajax")) {
                        session_error(LANG("msgerrorsendmail", "correo") . $error);
                    } else {
                        javascript_error(LANG("msgerrorsendmail", "correo") . $error);
                    }
                    $haserror = 1;
                } else {
                    __getmail_update("state_sent", 1, $last_id);
                    __getmail_update("state_error", "", $last_id);
                    unlink($file);
                    $sended++;
                }
            }
        } else {
            __getmail_update("state_sent", 1, $last_id);
            __getmail_update("state_error", LANG("interrorsendmail", "correo"), $last_id);
            $haserror = 1;
        }
    }
    if (!getParam("ajax")) {
        if ($sended > 0) {
            session_alert($sended . LANG("msgtotalsendmail" . min($sended, 2), "correo"));
        }
        javascript_history(-1);
    } else {
        if ($sended > 0) {
            javascript_alert($sended . LANG("msgtotalsendmail" . min($sended, 2), "correo"));
            if (!$haserror) {
                javascript_settimeout(
                    "$('#enviar').addClass('ui-state-disabled');", 1000, "is_correo_list()"
                );
            }
        }
        if ($sended > 0 || $haserror) {
            $condition = "update_correo_list()";
            javascript_history(0, $condition);
        }
    }
    // RELEASE THE SEMAPHORE
    semaphore_release($semaphore);
    javascript_headers();
    die();
}

/**
 * TODO
 *
 * TODO
 */
function sendmail_files($action, $email_id)
{
    if ($action == "forward" && $email_id) {
        require_once "apps/emails/php/getmail.php";
        return getmail_files($email_id);
    }
    return [];
}
