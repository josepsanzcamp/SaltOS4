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

// phpcs:disable PSR1.Files.SideEffects

/**
 * Get email library
 *
 * This library provides the necesary functions to download, parse and manage emails.
 */

/**
 * Requires section
 *
 * This requires loads the external libraries needed to run this library.
 */
require_once 'apps/emails/lib/mimeparser/mime_parser.php';
require_once 'apps/emails/lib/mimeparser/rfc822_addresses.php';
require_once 'apps/emails/lib/pop3class/pop3.php';

/**
 * Defines section
 *
 * This defines allow to define some useful standards to do html pages and more.
 */
// phpcs:disable Generic.Files.LineLength
define('__HTML_BOX_OPEN__', '<div style="background:#ffffff;">');
define('__HTML_BOX_CLOSE__', '</div>');
define('__HTML_TEXT_OPEN__', '<div style="color:#333;font-size:0.9rem;line-height:1rem;">');
define('__HTML_TEXT_CLOSE__', '</div>');
define('__PLAIN_TEXT_OPEN__', '<div style="color:#333;font-family:monospace;font-size:0.9rem;line-height:1rem;">');
define('__PLAIN_TEXT_CLOSE__', '</div>');
define('__HTML_SEPARATOR__', '<hr style="background:#ccc;border:0;height:1px;"/>');
define('__HTML_NEWLINE__', '<p>&nbsp;</p>');
define('__BLOCKQUOTE_OPEN__', '<blockquote style="border-left:#ccc 1px solid;margin:0 0 0 0.8ex;padding-left:1ex;">');
define('__BLOCKQUOTE_CLOSE__', '</blockquote>');
define('__SIGNATURE_OPEN__', '<div style="color:#ccc;font-size:0.8rem;line-height:1rem;"><p>--</p>');
define('__SIGNATURE_CLOSE__', '</div>');
define('__SECTION_OPEN__', '<section>');
define('__SECTION_CLOSE__', '</section>');
// phpcs:enable Generic.Files.LineLength

/**
 * Remove all body
 *
 * This function removes the body entry in the array, it is only for debug purposes
 *
 * @aarray => The array that you want to process
 */
function __getmail_removebody($array)
{
    if (isset($array['Body'])) {
        $array['Body'] = '##### BODY REMOVED FOR DEBUG PURPOSES #####';
    }
    $parts = __getmail_getnode('Parts', $array);
    if ($parts) {
        foreach ($parts as $index => $node) {
            $array['Parts'][$index] = __getmail_removebody($node);
        }
    }
    return $array;
}

/**
 * Process message
 *
 * This function returns a boolean that identify if the disposition and the type
 * allow the node to be processed.
 *
 * @disp => disposition, can be inline or attachment
 * @type => type, can be message, html or plain
 */
function __getmail_processmessage($disp, $type)
{
    return ($type == 'message' && $disp == 'inline');
}

/**
 * Process plain html
 *
 * This function returns a boolean that identify if the disposition and the type
 * allow the node to be processed.
 *
 * @disp => disposition, can be inline or attachment
 * @type => type, can be message, html or plain
 */
function __getmail_processplainhtml($disp, $type)
{
    return (in_array($type, ['plain', 'html']) && $disp == 'inline');
}

/**
 * Process file
 *
 * This function returns a boolean that identify if the disposition and the type
 * allow the node to be processed.
 *
 * @disp => disposition, can be inline or attachment
 * @type => type, can be message, html or plain
 */
function __getmail_processfile($disp, $type)
{
    return (
        $disp == 'attachment' ||
        ($disp == 'inline' && !in_array($type, ['plain', 'html', 'message', 'alternative', 'multipart']))
    );
}

/**
 * Check permissions
 *
 * This function allow to check if the current user has permissions to view the
 * message identified by the id argument
 *
 * @id => id of the email
 */
function __getmail_checkperm($id)
{
    $query = 'SELECT a.id
        FROM (
            SELECT a2.*, uc.email_privated email_privated
            FROM app_emails a2
            LEFT JOIN app_emails_accounts uc ON a2.account_id = uc.id
        ) a
        LEFT JOIN app_emails_control e ON e.id = a.id
        LEFT JOIN tbl_users d ON e.user_id = d.id
        WHERE a.id = ?
            AND (
                IFNULL(email_privated, 0) = 0 OR
                (IFNULL(email_privated, 0) = 1 AND e.user_id = ?)
            )
            AND ' . check_sql('emails', 'view');
    return execute_query($query, [abs(intval($id)), current_user()]);
}

/**
 * Get GZ File
 *
 * This function returns the file that contains the email
 *
 * @id => id of the email
 */
function __getmail_gzfile($id)
{
    $query = 'SELECT account_id, uidl, is_outbox FROM app_emails WHERE id = ?';
    $row = execute_query($query, [$id]);
    if (!$row) {
        return '';
    }
    $email = $row['account_id'] . '/' . $row['uidl'];
    $inout = $row['is_outbox'] ? 'out' : 'in';
    $file = get_directory("dirs/{$inout}boxdir") . $email . '.eml.gz';
    if (!file_exists($file)) {
        return '';
    }
    return $file;
}

/**
 * Get source
 *
 * This function returns the original RFC822 message as string
 *
 * @id => id of the email
 *
 * Notes:
 *
 * This function returns the email source without the base64 attachments
 */
function __getmail_getsource($id)
{
    $file = __getmail_gzfile($id);
    if ($file == '') {
        return '';
    }
    $message = file_get_contents('compress.zlib://' . $file);
    $pos = stripos($message, 'content-transfer-encoding: base64');
    while ($pos !== false) {
        $break = "\r\n";
        $pos2 = strpos($message, $break . $break, $pos);
        if ($pos2 === false) {
            $break = "\n";
            $pos2 = strpos($message, $break . $break, $pos);
        }
        if ($pos2 === false) {
            break;
        }
        $pos3 = strpos($message, $break . '--', $pos2);
        if ($pos3 === false) {
            break;
        }
        $pos2 += strlen($break . $break);
        $replacement = substr($message, $pos2, 1024);
        $replacement = explode($break, $replacement);
        $replacement = array_slice($replacement, 0, 3);
        $replacement = implode($break, $replacement);
        $replacement .= $break . '...' . $break . '...' . $break . '...';
        $message = substr_replace($message, $replacement, $pos2, $pos3 - $pos2);
        $pos = stripos($message, 'content-transfer-encoding: base64', $pos + 1);
    }
    return $message;
}

/**
 * Mime decode protected
 *
 * This function decodes the input string that contains the RFC822 message
 * using the mime_parser_class to do it, and returns the decoded array.
 *
 * @input => the RFC822 string that contains the message
 */
function __getmail_mime_decode_protected($input)
{
    $mime = new mime_parser_class();
    $decoded = [];
    $mime->Decode($input, $decoded);
    if (!count($decoded)) {
        $mime->decode_bodies = 0;
        $mime->Decode($input, $decoded);
    }
    return $decoded;
}

/**
 * Get mime
 *
 * This function returns the decoded array of the email identified by the id
 * argument, to do this more optimal, this function uses an internal cache
 * file to improve the performance for repeated executions.
 *
 * @id => id of the email
 */
function __getmail_getmime($id)
{
    $file = __getmail_gzfile($id);
    if ($file == '') {
        return '';
    }
    $query = 'SELECT account_id,uidl,is_outbox,datetime,size FROM app_emails WHERE id = ?';
    $row = execute_query($query, [$id]);
    $cache = get_cache_file($row, '.eml');
    if (!file_exists($cache)) {
        $decoded = __getmail_mime_decode_protected(['File' => 'compress.zlib://' . $file]);
        file_put_contents($cache, serialize($decoded));
        chmod_protected($cache, 0666);
    } else {
        $decoded = unserialize(file_get_contents($cache));
    }
    return $decoded;
}

/**
 * Get Node
 *
 * This function returns the node using a xpath notation
 *
 * @path  => xpath that identify the desired path that must to be returned
 * @array => the decoded message in an array format
 */
function __getmail_getnode($path, $array)
{
    if (!is_array($path)) {
        $path = explode('/', $path);
    }
    $elem = array_shift($path);
    if (!is_array($array) || !isset($array[$elem])) {
        return null;
    }
    if (count($path) == 0) {
        return $array[$elem];
    }
    return __getmail_getnode($path, $array[$elem]);
}

/**
 * Get type
 *
 * This function tries to unify the differents content-type to standarize it into
 * the follow formats: html, plain, messsage, alternative, multipart or other.
 *
 * @array => the decoded message in an array format
 */
function __getmail_gettype($array)
{
    $ctype = strtoupper(__getmail_fixstring(__getmail_getnode('Headers/content-type:', $array)));
    if (!$ctype) {
        $ctype = 'TEXT/PLAIN';
    }
    if (strpos($ctype, 'TEXT/HTML') !== false) {
        $type = 'html';
    } elseif (strpos($ctype, 'TEXT/PLAIN') !== false) {
        $type = 'plain';
    } elseif (strpos($ctype, 'MESSAGE/RFC822') !== false) {
        $type = 'message';
    } elseif (strpos($ctype, 'MULTIPART/ALTERNATIVE') !== false) {
        $type = 'alternative';
    } elseif (strpos($ctype, 'MULTIPART/') !== false) {
        $type = 'multipart';
    } else {
        $type = 'other';
    }
    return $type;
}

/**
 * Get disposition
 *
 * This function tries to unify the differents content-dispoaition to standarize
 * it into the follow formats: attachment, inline or other.
 *
 * @array => the decoded message in an array format
 */
function __getmail_getdisposition($array)
{
    $cdisp = strtoupper(__getmail_fixstring(__getmail_getnode('Headers/content-disposition:', $array)));
    if (!$cdisp) {
        $cdisp = 'INLINE';
    }
    if (strpos($cdisp, 'ATTACHMENT') !== false) {
        $disp = 'attachment';
    } elseif (strpos($cdisp, 'INLINE') !== false) {
        $disp = 'inline';
    } else {
        $disp = 'other';
    }
    return $disp;
}

/**
 * Get files
 *
 * This function returns an array with the attachment files of the message
 *
 * @array => the decoded message in an array format
 * @level => this parameter is internally used to detect recursion
 */
function __getmail_getfiles($array, $level = 0)
{
    $result = [];
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processfile($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $cid = __getmail_fixstring(__getmail_getnode('Headers/content-id:', $array));
            if (substr($cid, 0, 1) == '<') {
                $cid = substr($cid, 1);
            }
            if (substr($cid, -1, 1) == '>') {
                $cid = substr($cid, 0, -1);
            }
            if ($cid != '') {
                $cid = "cid:$cid";
            }
            $cname = getutf8(__getmail_fixstring(__getmail_getnode('FileName', $array)));
            $location = __getmail_fixstring(__getmail_getnode('Headers/content-location:', $array));
            if ($cid == '' && $location != '') {
                $cid = $location;
            }
            $ctype = __getmail_fixstring(__getmail_getnode('Headers/content-type:', $array));
            if (strpos($ctype, ';') !== false) {
                $ctype = strtok($ctype, ';');
            }
            // @phpstan-ignore booleanAnd.rightAlwaysTrue
            if ($cid == '' && $cname == '' && __getmail_processfile($disp, $type)) {
                $cname = encode_bad_chars($ctype) . '.eml';
            }
            if ($cname != '') {
                $csize = __getmail_fixstring(__getmail_getnode('BodyLength', $array));
                $hsize = __getmail_gethumansize($csize);
                                     // md5 inside as memory trick
                $chash = md5(serialize([md5($temp), $cid, $cname, $ctype, $csize]));
                $result[] = [
                    'disp' => $disp,
                    'type' => $type,
                    'ctype' => $ctype,
                    'cid' => $cid,
                    'cname' => $cname,
                    'csize' => $csize,
                    'hsize' => $hsize,
                    'chash' => $chash,
                    'body' => $temp,
                ];
            }
        }
    } elseif (__getmail_processplainhtml($disp, $type)) {
        // This data is used by the next trick
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $temp = getutf8($temp);
            $result[] = ['disp' => $disp, 'type' => $type, 'body' => $temp];
        }
    } elseif (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(['Data' => $temp]);
            $result = array_merge($result, __getmail_getfiles(__getmail_getnode('0', $decoded), $level + 1));
        }
    }
    $parts = __getmail_getnode('Parts', $array);
    if ($parts) {
        foreach ($parts as $index => $node) {
            $result = array_merge($result, __getmail_getfiles($node, $level + 1));
        }
    }
    if ($level == 0) {
        // Trick to remove the files that contain name and cid
        foreach ($result as $index => $node) {
            $disp = $node['disp'];
            $type = $node['type'];
            if (__getmail_processplainhtml($disp, $type)) {
                $temp = $node['body'];
                foreach ($result as $index2 => $node2) {
                    $disp2 = $node2['disp'];
                    $type2 = $node2['type'];
                    if (__getmail_processfile($disp2, $type2)) {
                        $cid2 = $node2['cid'];
                        if ($cid2 != '') {
                            if (strpos($temp, $cid2) !== false) {
                                unset($result[$index2]);
                            }
                        }
                    }
                }
                unset($result[$index]);
            }
        }
    }
    return $result;
}

/**
 * Get human size
 *
 * This function returns an string containing the size in human format
 *
 * @size => the number of bytes to convert to human format
 */
function __getmail_gethumansize($size)
{
    if ($size >= 1073741824) {
        $size = round($size / 1073741824, 2) . ' Gbytes';
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576, 2) . ' Mbytes';
    } elseif ($size >= 1024) {
        $size = round($size / 1024, 2) . ' Kbytes';
    } else {
        $size = $size . ' bytes';
    }
    return $size;
}

/**
 * Get info
 *
 * Returns all information of the decoded message in a structured format
 *
 * @array => the decoded message in an array format
 */
function __getmail_getinfo($array)
{
    //~ echo "<pre>" . sprintr(__getmail_removebody($array)) . "</pre>";
    $result = [
        'emails' => [],
        'datetime' => '',
        'subject' => '',
        'spam' => '',
        'files' => [],
        'crt' => 0,
        'priority' => 0,
        'sensitivity' => 0,
        'from' => '',
        'to' => '',
        'cc' => '',
        'bcc' => '',
    ];
    // Create the from, to, cc and bcc string
    $lista = [
        1 => 'from',
        2 => 'to',
        3 => 'cc',
        4 => 'bcc',
        5 => 'return-path',
        6 => 'reply-to',
        7 => 'disposition-notification-to',
    ];
    foreach ($lista as $key => $val) {
        $addresses = __getmail_getnode("ExtractedAddresses/$val:", $array);
        if ($addresses) {
            $temp = [];
            foreach ($addresses as $a) {
                $name = getutf8(__getmail_fixstring(__getmail_getnode('name', $a)));
                $addr = getutf8(__getmail_fixstring(__getmail_getnode('address', $a)));
                $result['emails'][] = ['type_id' => $key, 'type' => $val, 'name' => $name, 'value' => $addr];
                $temp[] = ($name != '') ? $name . ' <' . $addr . '>' : $addr;
            }
            $temp = implode('; ', $temp);
            if (array_key_exists($val, $result)) {
                $result[$val] = $temp;
            }
        }
    }
    // Create the datetime string
    $datetime = __getmail_fixstring(__getmail_getnode('Headers/date:', $array));
    if (!$datetime) {
        $datetime = __getmail_fixstring(__getmail_getnode('Headers/delivery-date:', $array));
    }
    if ($datetime && strpos($datetime, '(') !== false) {
        $datetime = strtok($datetime, '(');
    }
    if ($datetime) {
        $result['datetime'] = date('Y-m-d H:i:s', strtotime($datetime));
    }
    if (!$datetime) {
        $result['datetime'] = current_datetime();
    }
    // Create the subject string
    $subject = __getmail_fixstring(__getmail_getnode('DecodedHeaders/subject:/0/0/Value', $array));
    if (!$subject) {
        $subject = __getmail_fixstring(__getmail_getnode('Headers/subject:', $array));
    }
    $result['subject'] = trim_words(str_replace("\t", ' ', getutf8($subject)));
    // Check x-spam-status header
    $spam = strtoupper(trim(__getmail_fixstring(__getmail_getnode('Headers/x-spam-status:', $array))));
    $result['spam'] = (substr($spam, 0, 3) == 'YES' || substr($spam, -3, 3) == 'YES') ? '1' : '0';
    // Get the number of attachments
    $result['files'] = __getmail_getfiles($array);
    // Get the crt if exists
    foreach ($result['emails'] as $email) {
        if ($email['type_id'] == 7) {
            $result['crt'] = 1;
        }
    }
    // Get the priority if exists
    $priority = strtolower(__getmail_fixstring(__getmail_getnode('Headers/x-priority:', $array)));
    $priorities = ['low' => 5, 'high' => 1];
    if (isset($priorities[$priority])) {
        $priority = $priorities[$priority];
    }
    $priority = intval($priority);
    $priorities = [5 => -1, 4 => -1, 3 => 0, 2 => 1, 1 => 1];
    if (isset($priorities[$priority])) {
        $result['priority'] = $priorities[$priority];
    }
    // Get the sensitivity if exists
    $sensitivity = strtolower(__getmail_fixstring(__getmail_getnode('Headers/sensitivity:', $array)));
    $sensitivities = [
        'personal' => 1,
        'private' => 2,
        'company-confidential' => 3,
        // the next line is not an error, this not contains the minus simbol between the words
        'company confidential' => 3,
    ];
    if (isset($sensitivities[$sensitivity])) {
        $result['sensitivity'] = $sensitivities[$sensitivity];
    }
    // Return the result
    //~ $result["body"] = __getmail_gettextbody($array);
    //~ echo "<pre>" . sprintr($result) . "</pre>";
    //~ die();
    return $result;
}

/**
 * Fix string
 *
 * This function is a helper used by all functions that pcoesses the headers
 * of the decoded message.
 *
 * @arg => the string that must to be checked and fixed if needed
 */
function __getmail_fixstring($arg)
{
    while (is_array($arg)) {
        $arg = array_shift($arg);
    }
    return $arg ?? '';
}

/**
 * Get text body
 *
 * This function returns all text body concatenated as an unique string
 *
 * @array => the decoded message in an array format
 * @level => this parameter is internally used to detect recursion
 */
function __getmail_gettextbody($array, $level = 0)
{
    $result = [];
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processplainhtml($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $temp = getutf8($temp);
            if ($type == 'html') {
                $temp = html2text($temp);
            }
            $result[] = ['type' => $type, 'body' => $temp];
        }
    } elseif (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(['Data' => $temp]);
            $result[] = ['type' => $type, 'body' => __getmail_gettextbody(__getmail_getnode('0', $decoded))];
        }
    }
    $parts = __getmail_getnode('Parts', $array);
    if ($parts) {
        $recursive = [];
        foreach ($parts as $index => $node) {
            $recursive = array_merge($recursive, __getmail_gettextbody($node, $level + 1));
        }
        if ($type == 'alternative') {
            // Remove repetitions detected in ajuntament.respon@bcn.cat
            $hashes = [];
            foreach ($recursive as $index => $node) {
                $hash = md5(serialize($node));
                if (isset($hashes[$hash])) {
                    unset($recursive[$index]);
                } else {
                    $hashes[$hash] = $index;
                }
            }
            // Priorize the html content in front of plain content
            $count_plain = 0;
            $count_html = 0;
            foreach ($recursive as $index => $node) {
                if ($node['type'] == 'plain') {
                    $count_plain++;
                } elseif ($node['type'] == 'html') {
                    $count_html++;
                }
            }
            if ($count_plain == 1 && $count_html == 1) {
                foreach ($recursive as $index => $node) {
                    if ($node['type'] == 'plain') {
                        unset($recursive[$index]);
                        break;
                    }
                }
            }
        }
        $result = array_merge($result, $recursive);
    }
    if ($level == 0) {
        foreach ($result as $index => $node) {
            $result[$index] = $node['body'];
        }
        $result = implode("\n", $result);
    }
    return $result;
}

/**
 * Get full body
 *
 * This function returns all body and attachments information as an array
 *
 * @array => the decoded message in an array format
 */
function __getmail_getfullbody($array)
{
    $result = [];
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processplainhtml($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $temp = getutf8($temp);
            $result[] = ['disp' => $disp, 'type' => $type, 'body' => $temp];
        }
    } elseif (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(['Data' => $temp]);
            $result = array_merge($result, __getmail_getfullbody(__getmail_getnode('0', $decoded)));
        }
    } else {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $cid = __getmail_fixstring(__getmail_getnode('Headers/content-id:', $array));
            if (substr($cid, 0, 1) == '<') {
                $cid = substr($cid, 1);
            }
            if (substr($cid, -1, 1) == '>') {
                $cid = substr($cid, 0, -1);
            }
            if ($cid != '') {
                $cid = "cid:$cid";
            }
            $cname = getutf8(__getmail_fixstring(__getmail_getnode('FileName', $array)));
            $location = __getmail_fixstring(__getmail_getnode('Headers/content-location:', $array));
            if ($cid == '' && $location != '') {
                $cid = $location;
            }
            $ctype = __getmail_fixstring(__getmail_getnode('Headers/content-type:', $array));
            if (strpos($ctype, ';') !== false) {
                $ctype = strtok($ctype, ';');
            }
            if ($cid == '' && $cname == '' && __getmail_processfile($disp, $type)) {
                $cname = encode_bad_chars($ctype) . '.eml';
            }
            if ($cid != '' || $cname != '') {
                $csize = __getmail_fixstring(__getmail_getnode('BodyLength', $array));
                $hsize = __getmail_gethumansize($csize);
                                     // md5 inside as memory trick
                $chash = md5(serialize([md5($temp), $cid, $cname, $ctype, $csize]));
                $result[] = [
                    'disp' => $disp,
                    'type' => $type,
                    'ctype' => $ctype,
                    'cid' => $cid,
                    'cname' => $cname,
                    'csize' => $csize,
                    'hsize' => $hsize,
                    'chash' => $chash,
                    'body' => $temp,
                ];
            }
        }
    }
    $parts = __getmail_getnode('Parts', $array);
    if ($parts) {
        $recursive = [];
        foreach ($parts as $index => $node) {
            $recursive = array_merge($recursive, __getmail_getfullbody($node));
        }
        if ($type == 'alternative') {
            // Remove repetitions detected in ajuntament.respon@bcn.cat
            $hashes = [];
            foreach ($recursive as $index => $node) {
                $hash = md5(serialize($node));
                if (isset($hashes[$hash])) {
                    unset($recursive[$index]);
                } else {
                    $hashes[$hash] = $index;
                }
            }
            // Priorize the html content in front of plain content
            $count_plain = 0;
            $count_html = 0;
            foreach ($recursive as $index => $node) {
                if ($node['type'] == 'plain') {
                    $count_plain++;
                } elseif ($node['type'] == 'html') {
                    $count_html++;
                }
            }
            if ($count_plain == 1 && $count_html == 1) {
                foreach ($recursive as $index => $node) {
                    if ($node['type'] == 'plain') {
                        unset($recursive[$index]);
                        break;
                    }
                }
            }
        }
        $result = array_merge($result, $recursive);
    }
    return $result;
}

/**
 * Get cid
 *
 * This function returns the requested attachment indentified by the hash argument
 *
 * @array => the decoded message in an array format
 * @hash  => the hash of the content requested
 */
function __getmail_getcid($array, $hash)
{
    $disp = __getmail_getdisposition($array);
    $type = __getmail_gettype($array);
    if (__getmail_processmessage($disp, $type)) {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $decoded = __getmail_mime_decode_protected(['Data' => $temp]);
            $result = __getmail_getcid(__getmail_getnode('0', $decoded), $hash);
            if ($result) {
                return $result;
            }
        }
    } else {
        $temp = __getmail_getnode('Body', $array);
        if ($temp) {
            $cid = __getmail_fixstring(__getmail_getnode('Headers/content-id:', $array));
            if (substr($cid, 0, 1) == '<') {
                $cid = substr($cid, 1);
            }
            if (substr($cid, -1, 1) == '>') {
                $cid = substr($cid, 0, -1);
            }
            if ($cid != '') {
                $cid = "cid:$cid";
            }
            $cname = getutf8(__getmail_fixstring(__getmail_getnode('FileName', $array)));
            $location = __getmail_fixstring(__getmail_getnode('Headers/content-location:', $array));
            if ($cid == '' && $location != '') {
                $cid = $location;
            }
            $ctype = __getmail_fixstring(__getmail_getnode('Headers/content-type:', $array));
            if (strpos($ctype, ';') !== false) {
                $ctype = strtok($ctype, ';');
            }
            if ($cid == '' && $cname == '' && __getmail_processfile($disp, $type)) {
                $cname = encode_bad_chars($ctype) . '.eml';
            }
            $csize = __getmail_fixstring(__getmail_getnode('BodyLength', $array));
            $chash = md5(serialize([md5($temp), $cid, $cname, $ctype, $csize])); // md5 inside as memory trick
            if ($chash == $hash) {
                $hsize = __getmail_gethumansize($csize);
                return [
                    'disp' => $disp,
                    'type' => $type,
                    'ctype' => $ctype,
                    'cid' => $cid,
                    'cname' => $cname,
                    'csize' => $csize,
                    'hsize' => $hsize,
                    'chash' => $chash,
                    'body' => $temp,
                ];
            }
            // For compatibility with old saltos versions
            if (
                in_array($hash, [
                    md5(md5($temp) . md5($cid) . md5($cname) . md5($ctype) . md5(strval($csize))),
                    md5(serialize([$temp, $cid, $cname, $ctype, $csize])),
                    md5(serialize([md5($temp), $cid, $cname, $ctype, $csize])),
                    md5(serialize([md5($temp), null, $cname, $ctype, $csize])),
                    md5(json_encode([md5($temp), $cid, $cname, $ctype, $csize])),
                ])
            ) {
                $hsize = __getmail_gethumansize($csize);
                return [
                    'disp' => $disp,
                    'type' => $type,
                    'ctype' => $ctype,
                    'cid' => $cid,
                    'cname' => $cname,
                    'csize' => $csize,
                    'hsize' => $hsize,
                    'chash' => $chash,
                    'body' => $temp,
                ];
            }
            // End of compatibility code
        }
    }
    $parts = __getmail_getnode('Parts', $array);
    if ($parts) {
        foreach ($parts as $index => $node) {
            $result = __getmail_getcid($node, $hash);
            if ($result) {
                return $result;
            }
        }
    }
    return null;
}

/**
 * Insert
 *
 * This function do the insert in the app_emails table, and
 *
 * @file          => the gzfile that contains the message in RFC822 format
 * @messageid     => the id of the message (account_id/uidl)
 * @state_new     => the 0/1 that sets the state new flag
 * @state_reply   => the 0/1 that sets the state reply flag
 * @state_forward => the 0/1 that sets the state forward flag
 * @state_wait    => the 0/1 that sets the state wait flag
 * @id_correo     => the id of the related email (used to create relations between emails)
 * @is_outbox     => the 0/1 that sets the is outbox flag
 * @state_sent    => the 0/1 that sets the state sent flag
 * @state_error   => the string that contains the error (if exists an error)
 */
function __getmail_insert(
    $file,
    $messageid,
    $state_new,
    $state_reply,
    $state_forward,
    $state_wait,
    $email_id,
    $is_outbox,
    $state_sent,
    $state_error
) {
    list($account_id, $uidl) = explode('/', $messageid);
    $size = gzfilesize($file);
    $id_usuario = current_user();
    $datetime = current_datetime();
    // Decode the message
    $decoded = __getmail_mime_decode_protected(['File' => 'compress.zlib://' . $file]);
    $info = __getmail_getinfo(__getmail_getnode('0', $decoded));
    $body = __getmail_gettextbody(__getmail_getnode('0', $decoded));
    unset($decoded); // Trick to release memory
    // Insert the new email
    if (!semaphore_acquire(__FUNCTION__)) {
        show_php_error(['phperror' => 'Could not acquire the semaphore']);
    }
    $query = prepare_insert_query('app_emails', [
        'account_id' => $account_id,
        'uidl' => $uidl,
        'size' => $size,
        'datetime' => $info['datetime'],
        'subject' => $info['subject'],
        'body' => $body,
        'state_new' => $state_new,
        'state_reply' => $state_reply,
        'state_forward' => $state_forward,
        'state_wait' => $state_wait,
        'state_spam' => $info['spam'],
        'email_id' => $email_id,
        'is_outbox' => $is_outbox,
        'state_sent' => $state_sent,
        'state_error' => $state_error,
        'state_crt' => $info['crt'],
        'priority' => $info['priority'],
        'sensitivity' => $info['sensitivity'],
        'from' => $info['from'],
        'to' => $info['to'],
        'cc' => $info['cc'],
        'bcc' => $info['bcc'],
        'files' => count($info['files']),
    ]);
    unset($body); // Trick to release memory
    db_query(...$query);
    // Get last_id
    $query = 'SELECT MAX(id) FROM app_emails WHERE account_id = ? AND is_outbox = ?';
    $last_id = execute_query($query, [$account_id, $is_outbox]);
    semaphore_release(__FUNCTION__);
    // Insert all address
    foreach ($info['emails'] as $email) {
        $query = prepare_insert_query('app_emails_address', [
            'email_id' => $last_id,
            'type_id' => $email['type_id'],
            'name' => $email['name'],
            'value' => $email['value'],
        ]);
        db_query(...$query);
    }
    // Insert all attachments
    foreach ($info['files'] as $file) {
        $query = prepare_insert_query('app_emails_files', [
            'reg_id' => $last_id,
            'user_id' => $id_usuario,
            'datetime' => $datetime,
            'name' => $file['cname'],
            'size' => $file['csize'],
            'type' => $file['ctype'],
            'hash' => $file['chash'],
        ]);
        db_query(...$query);
    }
    // Insert the control register
    require_once 'php/lib/control.php';
    require_once 'php/lib/indexing.php';
    make_control('emails', $last_id);
    make_index('emails', $last_id);
    return $last_id;
}

/**
 * Update
 *
 * This function updates the field with the value of the app_emails for the
 * register identified by the id argument.
 *
 * @field => field that you want to update
 * @value => value that you want to set
 * @id    => id of the register to do the update
 */
function __getmail_update($field, $value, $id)
{
    $query = prepare_update_query('app_emails', [
        $field => $value,
    ], [
        'id' => $id,
    ]);
    db_query(...$query);
}

/**
 * Add bcc
 *
 * This function adds the bbc to the database, this is because the messages
 * does not contains the bcc field (is hidden in theory), and only is available
 * if the current execution is the sender of the message.
 *
 * @id  => id of the email
 * @bcc => an array with the addresses of the emails
 */
function __getmail_add_bcc($id, $bcc)
{
    foreach ($bcc as $addr) {
        list($value, $name) = __sendmail_parser($addr);
        $query = prepare_insert_query('app_emails_address', [
            'email_id' => $id,
            'type_id' => 4, // defined in __getmail_getinfo function
            'name' => $name,
            'value' => $value,
        ]);
        db_query(...$query);
    }
    $bcc = implode('; ', $bcc);
    $query = prepare_update_query('app_emails', [
        'bcc' => $bcc,
    ], [
        'id' => $id,
    ]);
    db_query(...$query);
}

/**
 * Gzfile size
 *
 * This function is copied from http://php.net/manual/es/function.gzread.php#110078
 * and allow to know the file size of the file after a gzip descompression.
 *
 * @filename => the gzip filename that you want to know the size
 */
function gzfilesize($filename)
{
    $gzfs = false;
    if (($zp = fopen($filename, 'r')) !== false) {
        if (@fread($zp, 2) == "\x1F\x8B") { // this is a gzip'd file
            fseek($zp, -4, SEEK_END);
            if (strlen($datum = @fread($zp, 4)) == 4) {
                extract(unpack('Vgzfs', $datum));
            }
        } else { // not a gzip'd file, revert to regular filesize function
            $gzfs = filesize($filename);
        }
        fclose($zp);
    }
    return $gzfs;
}

/**
 * Get email body
 *
 * This function returns the string that contains the body of the email
 * intended to be rendered in an iframe, for example
 *
 * @id => id of the email
 */
function getmail_body($id, $images = false)
{
    if (!__getmail_checkperm($id)) {
        show_php_error(['phperror' => 'Permission denied']);
    }
    $decoded = __getmail_getmime($id);
    if (!$decoded) {
        show_php_error(['phperror' => 'Could not decode de message']);
    }
    // MARCAR CORREO COMO LEIDO SI ES EL PROPIETARIO
    $query = 'SELECT id FROM app_emails_control WHERE id = ? AND user_id = ?';
    $id2 = execute_query($query, [$id, current_user()]);
    if ($id == $id2) {
        $query = prepare_update_query('app_emails', [
            'state_new' => 0,
        ], [
            'id' => $id,
            'state_new' => 1,
        ]);
        db_query(...$query);
    }
    // CONTINUE
    $buffer = __getmail_body_helper($decoded, $images);
    return $buffer;
}

/**
 * TODO
 *
 * TODO
 */
function __getmail_head_helper($decoded, $email_id)
{
    $result = __getmail_getinfo(__getmail_getnode('0', $decoded));
    $lista = ['from', 'to', 'cc', 'bcc'];
    foreach ($lista as $temp) {
        unset($result[$temp]);
    }
    foreach ($result['emails'] as $email) {
        if ($email['name'] != '') {
            $email['value'] = "{$email["name"]} <{$email["value"]}>";
        }
        if (!isset($result[$email['type']])) {
            $result[$email['type']] = [];
        }
        $result[$email['type']][] = $email['value'];
    }
    if (isset($result['from'])) {
        $result['from'] = implode('; ', $result['from']);
    }
    if (isset($result['to'])) {
        $result['to'] = implode('; ', $result['to']);
        $query = 'SELECT email_from FROM app_emails_accounts WHERE id=(
            SELECT account_id FROM app_emails WHERE id = ? )';
        $result['to'] = str_replace('<>', '<' .
            execute_query($query, [$email_id]) . '>', $result['to']);
    }
    if (!isset($result['to'])) {
        $query = "SELECT CASE
            WHEN (
                SELECT email_name FROM app_emails_accounts WHERE id=(
                    SELECT account_id FROM app_emails WHERE id = ?
                )
            )=''
            THEN (
                SELECT email_from FROM app_emails_accounts WHERE id=(
                    SELECT account_id FROM app_emails WHERE id = ?
                )
            )
            ELSE (
                SELECT CONCAT(email_name,' <',email_from,'>')
                FROM app_emails_accounts WHERE id=(
                    SELECT account_id FROM app_emails WHERE id = ?
                )
            ) END";
        $result['to'] = execute_query($query, [$email_id, $email_id, $email_id]);
    }
    if (isset($result['cc'])) {
        $result['cc'] = implode('; ', $result['cc']);
    }
    if (isset($result['bcc'])) {
        $result['bcc'] = implode('; ', $result['bcc']);
    }
    $lista = [
        'from' => T('From'),
        'to' => T('To'),
        'cc' => T('CC'),
        'bcc' => T('BCC'),
        'datetime' => T('Datetime'),
        'subject' => T('Subject'),
    ];
    if (!isset($result['from'])) {
        unset($lista['from']);
    }
    if (!isset($result['to'])) {
        unset($lista['to']);
    }
    if (!isset($result['cc'])) {
        unset($lista['cc']);
    }
    if (!isset($result['bcc'])) {
        unset($lista['bcc']);
    }
    if (!$result['subject']) {
        $result['subject'] = T('(no subject)');
    }
    $buffer = __HTML_BOX_OPEN__;
    foreach ($lista as $key2 => $val2) {
        $result[$key2] = str_replace(['<', '>'], ['&lt;', '&gt;'], $result[$key2]);
        $buffer .= __HTML_TEXT_OPEN__;
        $buffer .= $lista[$key2] . ': ';
        $buffer .= '<b>' . $result[$key2] . '</b>';
        $buffer .= __HTML_TEXT_CLOSE__;
    }
    $first = true;
    foreach ($result['files'] as $file) {
        $cname = $file['cname'];
        $hsize = $file['hsize'];
        if ($first) {
            $buffer .= __HTML_TEXT_OPEN__;
            $buffer .= T('Attachments') . ': ';
        } else {
            $buffer .= ' | ';
        }
        $buffer .= "<b>$cname</b> ($hsize)";
        $first = false;
    }
    if (!$first) {
        $buffer .= __HTML_TEXT_CLOSE__;
    }
    $buffer .= __HTML_SEPARATOR__;
    $buffer .= __HTML_BOX_CLOSE__;
    return $buffer;
}

/**
 * TODO
 *
 * TODO
 */
function __getmail_body_helper($decoded, $images = false)
{
    $buffer = '';
    $result = __getmail_getfullbody(__getmail_getnode('0', $decoded));
    $first = true;
    foreach ($result as $index => $node) {
        $disp = $node['disp'];
        $type = $node['type'];
        if (__getmail_processplainhtml($disp, $type)) {
            $temp = $node['body'];
            if ($type == 'plain') {
                $temp = wordwrap($temp, 120, "\n", true);
                $temp = htmlentities($temp, ENT_COMPAT, 'UTF-8');
                $temp = str_replace([' ', "\t", "\n"], ['&nbsp;', str_repeat('&nbsp;', 4), '<br>'], $temp);
            }
            if ($type == 'html') {
                require_once 'php/lib/html.php';
                $temp = remove_script_tag($temp);
                $temp = remove_style_tag($temp);
                $temp = remove_comment_tag($temp);
                $temp = remove_meta_tag($temp);
                $temp = remove_link_tag($temp);
                if ($images) {
                    $temp = inline_img_tag($temp);
                    $temp = inline_img_style($temp);
                    $temp = inline_img_background($temp);
                }
            }
            foreach ($result as $index2 => $node2) {
                $disp2 = $node2['disp'];
                $type2 = $node2['type'];
                if (
                    !__getmail_processplainhtml($disp2, $type2) &&
                    !__getmail_processmessage($disp2, $type2)
                ) {
                    $cid2 = $node2['cid'];
                    if ($cid2 != '') {
                        $chash2 = $node2['chash'];
                        $ctype2 = $node2['ctype'];
                        $data = mime_inline($ctype2, $node2['body']);
                        $temp = str_replace($cid2, $data, $temp);
                    }
                }
            }
            if ($type == 'html') {
                $temp = fix_img_tag($temp);
                $temp = fix_img_style($temp);
                $temp = fix_img_background($temp);
                $temp = fix_file_b64($temp);
            }
            if (!$first) {
                $buffer .= __HTML_SEPARATOR__;
            }
            if ($type == 'plain') {
                $buffer .= __PLAIN_TEXT_OPEN__ . $temp . __PLAIN_TEXT_CLOSE__;
            }
            if ($type == 'html') {
                $buffer .= __HTML_TEXT_OPEN__ . $temp . __HTML_TEXT_CLOSE__;
            }
            $first = false;
        }
    }
    return $buffer;
}

/**
 * Get email source
 *
 * This function returns the string that contains the source of the email
 * intended to be rendered in an iframe, for example
 *
 * @id => id of the email
 */
function getmail_source($id)
{
    if (!__getmail_checkperm($id)) {
        show_php_error(['phperror' => 'Permission denied']);
    }
    $source = __getmail_getsource($id);
    $source = getutf8($source);
    $source = wordwrap($source, 120, "\n", true);
    $source = htmlentities($source, ENT_COMPAT, 'UTF-8');
    $source = str_replace([' ', "\t", "\n"], ['&nbsp;', str_repeat('&nbsp;', 4), '<br>'], $source);
    $buffer = '';
    $buffer .= __PLAIN_TEXT_OPEN__;
    $buffer .= $source;
    $buffer .= __PLAIN_TEXT_CLOSE__;
    return $buffer;
}

/**
 * Get email files
 *
 * This function returns an arryy that contains the files of the email
 * intended to be rendered in an table, for example
 *
 * @id => id of the email
 */
function getmail_files($id)
{
    if (!__getmail_checkperm($id)) {
        show_php_error(['phperror' => 'Permission denied']);
    }
    $decoded = __getmail_getmime($id);
    if (!$decoded) {
        show_php_error(['phperror' => 'Could not decode de message']);
    }
    // CONTINUE
    $result = __getmail_getfiles(__getmail_getnode('0', $decoded));
    $array = [];
    foreach ($result as $file) {
        $array[] = [
            'id' => $id . '/' . $file['chash'],
            'name' => $file['cname'],
            'size' => $file['hsize'],
        ];
    }
    return $array;
}

/**
 * Get cid
 *
 * This function returns the requested attachment indentified by the cid argument
 *
 * @id  => id of the email
 * @cid => the cid of the content requested
 */
function getmail_cid($id, $cid)
{
    if (!__getmail_checkperm($id)) {
        show_php_error(['phperror' => 'Permission denied']);
    }
    $decoded = __getmail_getmime($id);
    if (!$decoded) {
        show_php_error(['phperror' => 'Could not decode de message']);
    }
    // continue
    $result = __getmail_getcid(__getmail_getnode('0', $decoded), $cid);
    if (!$result) {
        show_php_error(['phperror' => 'cid not found in message']);
    }
    $name = $result['cname'] ? $result['cname'] : $result['cid'];
    return [
        'data' => $result['body'],
        'type' => $result['ctype'],
        'size' => $result['csize'],
        'name' => $name,
    ];
}

/**
 * Is outbox
 *
 * Returns the field of the email identified by the id argument
 *
 * @field => field requested
 * @id    => id of the email
 */
function getmail_field($field, $id)
{
    if (!__getmail_checkperm($id)) {
        show_php_error(['phperror' => 'Permission denied']);
    }
    $query = "SELECT $field FROM app_emails WHERE id = ?";
    return execute_query($query, [$id]);
}

/**
 * Server
 *
 * This function implements the old getmail action of the old saltos.
 */
function getmail_server()
{
    // check the semaphore
    $semaphore = [__FUNCTION__, current_user()];
    if (!semaphore_acquire($semaphore, 100000)) {
        return [T('Could not acquire the semaphore')];
    }
    // for debug purposes
    if (get_data('server/xuid') && get_data('json/getmailmsgid')) {
        $file = get_directory('dirs/inboxdir') . get_data('json/getmailmsgid') . '.eml.gz';
        if (!file_exists($file)) {
            $file = get_directory('dirs/outboxdir') . get_data('json/getmailmsgid') . '.eml.gz';
        }
        $last_id = __getmail_insert($file, get_data('json/getmailmsgid'), 1, 0, 0, 0, 0, 0, 0, '');
        semaphore_release($semaphore);
        output_handler_json([
            'emails' => [
                'getmailmsgid' => get_data('json/getmailmsgid'),
                'file' => $file,
                'last_id' => $last_id,
            ],
        ]);
    }
    // datos pop3
    $query = 'SELECT * FROM app_emails_accounts WHERE user_id = ? AND email_disabled = 0';
    $result = execute_query_array($query, [current_user()]);
    if (!count($result)) {
        semaphore_release($semaphore);
        return [T('Could not found configuration')];
    }
    // begin the loop
    $newemail = 0;
    $haserror = [];
    foreach ($result as $row) {
        if (time_get_usage() > get_config('server/percentstop')) {
            break;
        }
        $error = '';
        if ($row['pop3_host'] == '') {
            $temp = $row['email_from'];
            if (!$temp) {
                $temp = $row['email_name'];
            }
            if ($temp) {
                $temp = " ($temp)";
            }
            $error = sprintf(T('POP3 server %s not configured'), $temp);
        }
        $pop3 = null;
        $olduidls = null;
        $olduidls_d = null;
        $prefix = null;
        $id_cuenta = null;
        if ($error == '') {
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
            $pop3->tls = ($row['pop3_extra'] == 'tls') ? 1 : 0;
            // I have detected that stream_socket_client generates uncontrolable
            // errors, for this reason, I have overloaded the error handler to
            // manage this kind of errors
            overload_error_handler('stream_socket_client');
            $error = $pop3->Open();
            restore_error_handler();
            // End of the overloaded error zone
        }
        if ($error == '') {
            $error = $pop3->Login($row['pop3_user'], $row['pop3_pass']);
        }
        $sizes = null;
        if ($error == '') {
            $sizes = $pop3->ListMessages('', 0);
            if (!is_array($sizes)) {
                $error = $sizes;
            }
        }
        $uidls = null;
        if ($error == '') {
            $uidls = $pop3->ListMessages('', 1);
            if (!is_array($uidls)) {
                $error = $uidls;
            }
        }
        if ($error == '') {
            // retrieve all new messages
            $retrieve = array_diff($uidls, $olduidls);
            foreach ($retrieve as $index => $uidl) {
                if (time_get_usage() > get_config('server/percentstop')) {
                    break;
                }
                if ($error == '') {
                    $file = $prefix . '/' . $uidls[$index] . '.eml.gz';
                    if (!file_exists($file)) {
                        // retrieve the entire message
                        $error = $pop3->OpenMessage($index, -1);
                        $message = null;
                        if ($error == '') {
                            $message = '';
                            $eof = 0;
                            while (!$eof && $error == '') {
                                $temp = '';
                                $error = $pop3->GetMessage($sizes[$index] + 1, $temp, $eof);
                                $message .= $temp;
                            }
                        }
                        if ($error == '') {
                            // store the message into single file
                            $fp = gzopen($file, 'w');
                            gzwrite($fp, $message);
                            gzclose($fp);
                            chmod_protected($file, 0666);
                            $message = ''; // trick to release memory
                        }
                    }
                    if ($error == '') {
                        $messageid = $id_cuenta . '/' . $uidls[$index];
                        $last_id = __getmail_insert($file, $messageid, 1, 0, 0, 0, 0, 0, 0, '');
                        $newemail++;
                    }
                }
            }
        }
        if ($error == '' && $row['pop3_delete']) {
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
                    $index2 = array_search($row2['uidl'], $uidls);
                    $error = $pop3->DeleteMessage($index2);
                    unset($uidls[$index2]);
                }
                if ($error != '') {
                    break;
                }
            }
        }
        if ($error == '') {
            $error = $pop3->Close();
        }
        if ($error == '') {
            // remove all unused uidls
            $delete = array_diff($olduidls_d, $uidls);
            $delete = "'" . implode("','", $delete) . "'";
            $query = "DELETE FROM app_emails_deletes WHERE account_id = ? AND uidl IN ($delete)";
            db_query($query, [$id_cuenta]);
        }
        if ($error != '') {
            $haserror[] = sprintf(
                T('There has been the following error: %s (%s)'), $error, $row['pop3_host']
            );
        }
    }
    $haserror[] = sprintf(T('%d email(s) received'), $newemail);
    // intended to be used by cron feature
    if (get_data('server/xuid') && $newemail) {
        require_once 'php/lib/push.php';
        push_insert('event', 'saltos.emails.update');
    }
    // release the semaphore
    semaphore_release($semaphore);
    return $haserror;
}

/**
 * Delete
 *
 * This function implements the old delete action of the old saltos.
 *
 * @ids => array with the emails id
 */
function getmail_delete($ids)
{
    $ids = check_ids($ids);
    $numids = count(explode(',', $ids));
    $query = "SELECT id FROM app_emails a WHERE id IN ($ids) AND id IN (
        SELECT id FROM app_emails_control b WHERE b.id = a.id AND user_id = ? )";
    $result = execute_query_array($query, [current_user()]);
    $numresult = count($result);
    if ($numresult != $numids) {
        return T('Permission denied');
    }
    // CREAR DATOS EN TABLA DE CORREOS BORRADOS (SOLO LOS DEL INBOX)
    $query = "INSERT INTO app_emails_deletes(account_id,uidl,datetime)
        SELECT account_id,uidl,datetime
        FROM app_emails WHERE id IN ($ids) AND is_outbox = 0";
    db_query($query);
    // BORRAR FICHEROS .EML.GZ DEL INBOX
    $query = "SELECT
        CONCAT('" . get_directory('dirs/inboxdir') . "',account_id,'/',uidl,'.eml.gz') action_delete
        FROM app_emails WHERE id IN ($ids) AND is_outbox = 0";
    $result = execute_query_array($query);
    foreach ($result as $delete) {
        if (file_exists($delete) && is_file($delete)) {
            unlink($delete);
        }
    }
    // BORRAR FICHEROS .EML.GZ DEL OUTBOX
    $query = "SELECT
        CONCAT('" . get_directory('dirs/outboxdir') . "',account_id,'/',uidl,'.eml.gz') action_delete
        FROM app_emails WHERE id IN ($ids) AND is_outbox = 1";
    $result = execute_query_array($query);
    foreach ($result as $delete) {
        if (file_exists($delete) && is_file($delete)) {
            unlink($delete);
        }
    }
    // BORRAR FICHEROS .OBJ DEL OUTBOX
    $query = "SELECT
        CONCAT('" . get_directory('dirs/outboxdir') . "',account_id,'/',uidl,'.obj') action_delete
        FROM app_emails WHERE id IN ($ids) AND is_outbox = 1";
    $result = execute_query_array($query);
    foreach ($result as $delete) {
        if (file_exists($delete) && is_file($delete)) {
            unlink($delete);
        }
    }
    // BORRAR CORREOS
    $query = "DELETE FROM app_emails WHERE id IN ($ids)";
    db_query($query);
    // BORRAR DIRECCIONES DE LOS CORREOS
    $query = "DELETE FROM app_emails_address WHERE email_id IN ($ids)";
    db_query($query);
    // BORRAR FICHEROS ADJUNTOS DE LOS CORREOS
    $query = "DELETE FROM app_emails_files WHERE reg_id IN ($ids)";
    db_query($query);
    // BORRAR REGISTRO DE LOS CORREOS
    $ids = explode(',', $ids);
    foreach ($ids as $id) {
        require_once 'php/lib/control.php';
        require_once 'php/lib/indexing.php';
        make_control('emails', $id);
        make_index('emails', $id);
    }
    // MOSTRAR RESULTADO
    return sprintf(T('%d email(s) deleted'), $numids);
}

/**
 * Get viewpdf
 *
 * This function returns the requested attachment indentified by the cid argument
 * in a pdf format for the viewpdf widget
 *
 * @id  => id of the email
 * @cid => the cid of the content requested
 */
function getmail_viewpdf($id, $cid)
{
    $file = getmail_cid($id, $cid);
    $ext = strtolower(extension($file['name']));
    if (!$ext) {
        $ext = strtolower(saltos_content_type1($file['type']));
    }
    $cache1 = get_cache_file([$id, $cid], $ext);
    file_put_contents($cache1, $file['data']);
    chmod_protected($cache1, 0666);
    // CREAR THUMBS SI ES NECESARIO
    $cache2 = get_cache_file([$id, $cid], 'pdf');
    if (!file_exists($cache2)) {
        require_once 'php/lib/unoconv.php';
        file_put_contents($cache2, unoconv2pdf($cache1));
        if (!filesize($cache2)) {
            require_once 'php/lib/pdf.php';
            file_put_contents($cache2, __pdf_all2pdf($cache1));
        }
        chmod_protected($cache2, 0666);
    }
    return base64_encode(file_get_contents($cache2));
}

/**
 * Get download
 *
 * This function returns the requested attachment indentified by the cid argument
 * in an array format for the download feature
 *
 * @id  => id of the email
 * @cid => the cid of the content requested
 */
function getmail_download($id, $cid)
{
    $file = getmail_cid($id, $cid);
    $file['data'] = base64_encode($file['data']);
    return $file;
}

/**
 * TODO
 *
 * TODO
 */
function getmail_setter($ids, $what)
{
    $ids = check_ids($ids);
    $numids = count(explode(',', $ids));
    $query = "SELECT id FROM app_emails a WHERE id IN ($ids) AND id IN (
        SELECT id FROM app_emails_control b WHERE b.id=a.id AND user_id = ?)";
    $result = execute_query_array($query, [current_user()]);
    $numresult = count($result);
    if ($numresult != $numids) {
        return T('Permission denied');
    }
    // process the real action
    $what = explode('=', $what);
    $what[1] = intval($what[1]);
    if ($what[0] == 'new') {
        // BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
        $query = "SELECT COUNT(*) FROM app_emails
            WHERE id IN ($ids) AND state_new != ? AND is_outbox = 0";
        $numids = execute_query($query, [$what[1]]);
        // PONER STATE_NEW = 0 EN LOS CORREOS SELECCIONADOS
        $query = "UPDATE app_emails SET state_new = ?
            WHERE id IN ($ids) AND state_new != ? AND is_outbox = 0";
        db_query($query, [$what[1], $what[1]]);
    } elseif ($what[0] == 'wait') {
        // BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
        $query = "SELECT COUNT(*) FROM app_emails
            WHERE id IN ($ids) AND state_wait != ?";
        $numids = execute_query($query, [$what[1]]);
        // PONER STATE_WAIT = 1 EN LOS CORREOS SELECCIONADOS
        $query = "UPDATE app_emails SET state_new = 0, state_wait = ?
            WHERE id IN ($ids) AND state_wait != ?";
        db_query($query, [$what[1], $what[1]]);
    } elseif ($what[0] == 'spam') {
        // BUSCAR CUANTOS REGISTROS SE VAN A MODIFICAR
        $query = "SELECT COUNT(*) FROM app_emails
            WHERE id IN ($ids) AND state_spam != ? AND is_outbox = 0";
        $numids = execute_query($query, [$what[1]]);
        // PONER STATE_SPAM = 1 EN LOS CORREOS SELECCIONADOS
        $query = "UPDATE app_emails SET state_new = 0, state_spam = ?
            WHERE id IN ($ids) AND state_spam != ? AND is_outbox = 0";
        db_query($query, [$what[1], $what[1]]);
    }
    // return the response
    return sprintf(T('%d email(s) modified successfully'), $numids);
}

/**
 * TODO
 *
 * TODO
 */
function getmail_pdf($ids)
{
    if (!check_commands('wkhtmltopdf')) {
        require_once 'php/lib/pdf.php';
        return pdf('apps/emails/xml/pdf.xml', ['id' => check_ids($ids)]);
    }
    static $cache = [];
    $hash = md5(serialize($ids));
    if (isset($cache[$hash])) {
        return $cache[$hash];
    }
    $ids = check_ids_array($ids);
    $pdfs = [];
    foreach ($ids as $id) {
        if (!__getmail_checkperm($id)) {
            show_php_error(['phperror' => 'Permission denied']);
        }
        $input = get_cache_file([__FUNCTION__, $id], '.html');
        if (!file_exists($input)) {
            $decoded = __getmail_getmime($id);
            if (!$decoded) {
                show_php_error(['phperror' => 'Could not decode de message']);
            }
            $buffer = '';
            $buffer .= __getmail_head_helper($decoded, $id);
            $buffer .= __getmail_body_helper($decoded, true);
            $buffer = __iframe_srcdoc_helper($buffer);
            file_put_contents($input, $buffer);
            chmod_protected($input, 0666);
        }
        $output = get_cache_file([__FUNCTION__, $id], '.pdf');
        if (!file_exists($output)) {
            $options = '--enable-local-file-access';
            ob_passthru("wkhtmltopdf $options $input $output 2>&1");
            chmod_protected($output, 0666);
        }
        $pdfs[] = $output;
    }
    if (count($pdfs) > 1) {
        $input = implode(' ', $pdfs);
        $output = get_cache_file([__FUNCTION__, $ids], '.pdf');
        ob_passthru("pdfunite $input $output 2>&1");
        chmod_protected($output, 0666);
    } else {
        $output = $pdfs[0];
    }
    if (count($pdfs) > 1) {
        $name = encode_bad_chars(T('Emails')) . '.pdf';
    } else {
        $name = encode_bad_chars(execute_query("SELECT CONCAT(
            '" . T('Email') . "',' ',id,' ',
            CASE WHEN subject='' THEN '" . T('sinsubject') . "' ELSE subject END
        ) subject
        FROM app_emails
        WHERE id IN ({$ids[0]})")) . '.pdf';
    }
    $cache[$hash] = [
        'name' => $name,
        'type' => 'application/pdf',
        'data' => base64_encode(file_get_contents($output)),
    ];
    return $cache[$hash];
}

/**
 * TODO
 *
 * TODO
 */
function __iframe_srcdoc_helper($html)
{
    $font = realpath('../web/lib/atkinson-hyperlegible/atkinson-hyperlegible.min.css');
    $html = '<!doctype html><html><head><meta charset="utf-8">
    <style>body { margin: 0; padding: 0; }</style>
    <link href="' . $font . '" rel="stylesheet" integrity="">
    <style>:root { font-family: var(--bs-font-sans-serif); }</style>
    <meta http-equiv="Content-Security-Policy" content="default-src \'self\';
        style-src \'self\' \'unsafe-inline\' ${window.location.origin};
        font-src \'self\' ${window.location.origin};
        img-src \'self\' data: ${window.location.origin};">
    </head><body>' . $html . '</body></html>';
    return $html;
}
