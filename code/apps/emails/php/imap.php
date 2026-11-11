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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName
// phpcs:disable PSR1.Files.SideEffects

/**
 * SaltOS IMAP Client Component
 *
 * PURPOSE:
 * IMAP client used by the emails app to synchronize the folders and the emails of the
 * IMAP accounts.
 *
 * ARCHITECTURE & DESIGN:
 * - Persistent session: one connection and one login for all the commands, using the
 * transport of socket.php (PHP streams).
 * - Transport: Supports implicit TLS (IMAPS) or STARTTLS.
 * - Capabilities: read on each connection, the optional extensions are used only when the
 * server announces them, and the ignore property allows to hide some of them to test the
 * fallbacks.
 * - Responses: the untagged responses are returned as lists of tokens, decoding the
 * quoted strings, the literals, the NIL and the parenthesized lists.
 *
 * SECURITY & CONFIGURATION:
 * - Warning: Certificate verification is currently disabled for compatibility.
 */

require_once 'apps/emails/php/socket.php';

final class imap_class
{
    /** @var string IMAP server hostname or IP (required). */
    public $hostname = '';

    /** @var int IMAP TCP port (143 for IMAP, 993 for IMAPS if not set explicitly). */
    public $port = 0;

    /** @var int 1 to request STARTTLS when using plain IMAP; 0 otherwise. */
    public $tls = 0;

    /** @var int 1 to use IMAPS (implicit TLS); 0 for plain IMAP. */
    public $ssl = 0;

    /** @var string Username for authentication. */
    public $user = '';

    /** @var string Password for authentication. */
    public $pass = '';

    /** @var int Timeout in seconds used when connecting and when reading. */
    public $timeout = 30;

    /** @var array<int,string> Capabilities ignored even if the server announces them. */
    public $ignore = [];

    /** @var socket_class|null The connection with the server. */
    private $socket = null;

    /** @var int The counter used to generate the tags of the commands. */
    private $tag = 0;

    /** @var array<int,string> The capabilities announced by the server, in uppercase. */
    private $capabilities = [];

    /** @var string The text of the last tagged response, used to read its response code. */
    private $tagged = '';

    /**
     * Open the session: connect, read the greeting, start TLS if requested and login.
     *
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function Open()
    {
        if (!$this->hostname) {
            return 'Open failed: empty host';
        }
        if (!$this->port) {
            $this->port = (int)$this->ssl === 1 ? 993 : 143;
        }

        $this->socket = new socket_class();
        $this->socket->hostname = $this->hostname;
        $this->socket->port = (int)$this->port;
        $this->socket->ssl = (int)$this->ssl;
        $this->socket->timeout = (int)$this->timeout;
        $error = $this->socket->Connect();
        if ($error !== '') {
            $this->socket = null;
            return $error;
        }

        // Greeting of the server, the PREAUTH greeting is not supported
        $this->tag = 0;
        $this->capabilities = [];
        $error = $this->read($raw);
        if ($error === '' && !preg_match('~^\* OK\b~i', $raw)) {
            $error = "Greeting failed: $raw";
        }
        if ($error !== '') {
            return $this->abort($error);
        }
        $this->code_capability(substr($raw, 2));

        // STARTTLS when requested (and not using implicit TLS), the capabilities announced
        // before the TLS negotiation must be discarded
        if ((int)$this->ssl !== 1 && (int)$this->tls === 1) {
            $error = $this->command('STARTTLS', $untagged);
            if ($error === '') {
                $error = $this->socket->StartTls();
            }
            if ($error !== '') {
                return $this->abort($error);
            }
            $this->capabilities = [];
        }
        if (!count($this->capabilities)) {
            $error = $this->command('CAPABILITY', $untagged);
            if ($error !== '') {
                return $this->abort($error);
            }
        }

        // Login using AUTHENTICATE PLAIN if available (with the initial response if the
        // server supports it) or LOGIN, the capabilities change after the login
        if ($this->HasCapability('AUTH=PLAIN')) {
            $plain = base64_encode("\0" . $this->user . "\0" . $this->pass);
            if ($this->HasCapability('SASL-IR')) {
                $this->capabilities = [];
                $error = $this->command("AUTHENTICATE PLAIN $plain", $untagged, 'AUTHENTICATE');
            } else {
                $this->capabilities = [];
                $error = $this->command('AUTHENTICATE PLAIN', $untagged, 'AUTHENTICATE', $plain);
            }
        } elseif ($this->HasCapability('LOGINDISABLED')) {
            $error = 'Login failed: the server requires TLS';
        } else {
            $this->capabilities = [];
            $error = $this->command(
                'LOGIN ' . self::Quote($this->user) . ' ' . self::Quote($this->pass),
                $untagged,
                'LOGIN'
            );
        }
        if ($error === '' && !count($this->capabilities)) {
            $error = $this->command('CAPABILITY', $untagged);
        }
        if ($error !== '') {
            return $this->abort($error);
        }
        return '';
    }

    /**
     * Check if the server announces a capability, ignoring the capabilities of the ignore
     * property.
     *
     * @param  string $name Name of the capability, e.g. "UIDPLUS", "AUTH=PLAIN".
     * @return bool         True if the capability is available.
     */
    public function HasCapability(string $name)
    {
        return in_array(strtoupper($name), $this->capabilities, true);
    }

    /**
     * Close the session with LOGOUT and close the connection.
     *
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function Close()
    {
        if ($this->socket === null) {
            return '';
        }
        $error = $this->command('LOGOUT', $untagged);
        if ($this->socket !== null) {
            $this->socket->Close();
            $this->socket = null;
        }
        return $error;
    }

    /**
     * Check if the connection is open: it is closed after an error of the connection (not
     * after the errors returned by the server), so the callers can distinguish both cases.
     *
     * @return bool True if the connection is open.
     */
    public function Connected()
    {
        return $this->socket !== null;
    }

    /**
     * List the folders of the server using LIST, asking the special uses and the
     * subscriptions with the LIST-EXTENDED options when the server supports them, or using
     * LSUB and the known names of the special folders otherwise.
     *
     * Each folder contains:
     * - name: the full name in the server (modified UTF-7), used in the other commands.
     * - label: the last part of the name, decoded to UTF-8.
     * - parent: the full name of the parent folder, empty for the root folders.
     * - delimiter: the hierarchy delimiter, empty if the server has no hierarchy.
     * - special_use: \Inbox, \Sent, \Trash, \Junk, \Drafts, \Archive, \All, \Flagged,
     *   \Important or empty.
     * - subscribed: 1 if the folder is subscribed, 0 otherwise.
     * - selectable: 0 for the folders that can not contain emails (\Noselect), 1 otherwise.
     *
     * @return array<int,array<string,mixed>>|string List of folders on success; error
     *                                               string on failure.
     */
    public function ListFolders()
    {
        $special = $this->HasCapability('SPECIAL-USE');
        $extended = $this->HasCapability('LIST-EXTENDED');
        $options = array_filter([$special ? 'SPECIAL-USE' : '', $extended ? 'SUBSCRIBED' : '']);
        $cmd = 'LIST "" "*"';
        if ($extended && count($options)) {
            $cmd .= ' RETURN (' . implode(' ', $options) . ')';
        }
        $error = $this->command($cmd, $untagged);
        if ($error !== '') {
            return $error;
        }
        $subscribed = [];
        if (!$extended) {
            $error = $this->command('LSUB "" "*"', $lsub);
            if ($error !== '') {
                return $error;
            }
            foreach ($lsub as $tokens) {
                if (self::word($tokens, 0) === 'LSUB' && isset($tokens[3])) {
                    $subscribed[] = strval($tokens[3]);
                }
            }
        }

        $uses = ['\All', '\Archive', '\Drafts', '\Flagged', '\Important', '\Junk', '\Sent', '\Trash'];
        $folders = [];
        foreach ($untagged as $tokens) {
            if (self::word($tokens, 0) !== 'LIST' || !isset($tokens[3])) {
                continue;
            }
            $attributes = array_map('strtolower', array_map('strval', (array)$tokens[1]));
            $delimiter = strval($tokens[2]);
            $name = strval($tokens[3]);
            $parts = $delimiter !== '' ? explode($delimiter, $name) : [$name];
            $label = array_pop($parts);
            $parent = implode($delimiter, $parts);
            $special_use = '';
            foreach ($uses as $use) {
                if (in_array(strtolower($use), $attributes, true)) {
                    $special_use = $use;
                    break;
                }
            }
            if (strtoupper($name) === 'INBOX') {
                $special_use = '\Inbox';
            } elseif (!$special && $special_use === '') {
                // Without SPECIAL-USE, only the root folders and the children of INBOX
                if (in_array(strtoupper($parent), ['', 'INBOX'], true)) {
                    $special_use = self::special_use_by_name(self::Decode($label));
                }
            }
            $is_subscribed = in_array('\subscribed', $attributes, true);
            $is_subscribed = $is_subscribed || in_array($name, $subscribed, true);
            $folders[] = [
                'name' => $name,
                'label' => self::Decode($label),
                'parent' => $parent,
                'delimiter' => $delimiter,
                'special_use' => $special_use,
                'subscribed' => (int)$is_subscribed,
                'selectable' => (int)!array_intersect(['\noselect', '\nonexistent'], $attributes),
            ];
        }
        return $folders;
    }

    /**
     * Create a folder, with its special use if the server supports CREATE-SPECIAL-USE.
     *
     * @param  string $name        Full name of the folder in UTF-8, using the delimiter of
     *                             the server.
     * @param  string $special_use Special use of the folder, e.g. "\Trash" (optional).
     * @return string              Empty string on success; non-empty error message on failure.
     */
    public function CreateFolder(string $name, string $special_use = '')
    {
        $cmd = 'CREATE ' . self::Quote(self::Encode($name));
        if ($special_use !== '' && $this->HasCapability('CREATE-SPECIAL-USE')) {
            $cmd .= " (USE ($special_use))";
        }
        return $this->command($cmd, $untagged);
    }

    /**
     * Rename or move a folder, the server renames its subfolders too.
     *
     * @param  string $name    Full name of the folder in UTF-8.
     * @param  string $newname New full name of the folder in UTF-8.
     * @return string          Empty string on success; non-empty error message on failure.
     */
    public function RenameFolder(string $name, string $newname)
    {
        $cmd = 'RENAME ' . self::Quote(self::Encode($name)) . ' ' . self::Quote(self::Encode($newname));
        return $this->command($cmd, $untagged);
    }

    /**
     * Delete a folder, the subfolders must be deleted before.
     *
     * @param  string $name Full name of the folder in UTF-8.
     * @return string       Empty string on success; non-empty error message on failure.
     */
    public function DeleteFolder(string $name)
    {
        return $this->command('DELETE ' . self::Quote(self::Encode($name)), $untagged);
    }

    /**
     * Subscribe a folder in the server.
     *
     * @param  string $name Full name of the folder in UTF-8.
     * @return string       Empty string on success; non-empty error message on failure.
     */
    public function SubscribeFolder(string $name)
    {
        return $this->command('SUBSCRIBE ' . self::Quote(self::Encode($name)), $untagged);
    }

    /**
     * Select a folder to work with its emails, enabling CONDSTORE when the server supports
     * it to get the HIGHESTMODSEQ of the folder.
     *
     * Returns an array with:
     * - exists: the number of emails of the folder.
     * - uidvalidity: the UIDVALIDITY of the folder, if it changes the UIDs are not valid.
     * - uidnext: the UID that the server will assign to the next email.
     * - highestmodseq: the HIGHESTMODSEQ of the folder (as string), empty without CONDSTORE.
     *
     * @param  string                    $name Full name of the folder in UTF-8.
     * @return array<string,mixed>|string      The state of the folder on success; error
     *                                         string on failure.
     */
    public function SelectFolder(string $name)
    {
        $cmd = 'SELECT ' . self::Quote(self::Encode($name));
        if ($this->HasCapability('CONDSTORE')) {
            $cmd .= ' (CONDSTORE)';
        }
        $error = $this->command($cmd, $untagged);
        if ($error !== '') {
            return $error;
        }
        $result = ['exists' => 0, 'uidvalidity' => 0, 'uidnext' => 0, 'highestmodseq' => ''];
        foreach ($untagged as $tokens) {
            if (self::word($tokens, 1) === 'EXISTS') {
                $result['exists'] = (int)$tokens[0];
            }
            $pattern = '~^\[(UIDVALIDITY|UIDNEXT|HIGHESTMODSEQ) (\d+)\]~i';
            if (self::word($tokens, 0) === 'OK' && preg_match($pattern, $tokens[1], $matches)) {
                $key = strtolower($matches[1]);
                $result[$key] = $key === 'highestmodseq' ? $matches[2] : (int)$matches[2];
            }
        }
        return $result;
    }

    /**
     * Search the emails of the selected folder using UID SEARCH.
     *
     * @param  string                $criteria The search criteria, e.g. "ALL", "SINCE 1-Jan-2026",
     *                                         "HEADER Message-ID <id@host>" (quoted).
     * @return array<int,int>|string           List of UIDs on success; error string on failure.
     */
    public function Search(string $criteria)
    {
        $error = $this->command("UID SEARCH $criteria", $untagged);
        if ($error !== '') {
            return $error;
        }
        $uids = [];
        foreach ($untagged as $tokens) {
            if (self::word($tokens, 0) === 'SEARCH') {
                $uids = array_merge($uids, array_map('intval', array_slice($tokens, 1)));
            }
        }
        sort($uids);
        return $uids;
    }

    /**
     * Fetch data of the emails of the selected folder using UID FETCH, returning only the
     * emails changed after a modseq when the changedsince argument is used (CONDSTORE).
     *
     * The items are returned using the names sent by the server in uppercase, e.g. FLAGS
     * (list of flags), RFC822.SIZE, INTERNALDATE, BODY[] (the full email) or
     * BODY[HEADER.FIELDS (MESSAGE-ID)], note that the BODY.PEEK items are returned as BODY.
     *
     * @param  string                          $uids         The UIDs, e.g. "1:*", "4,7,9".
     * @param  string                          $items        The items, e.g. "FLAGS RFC822.SIZE".
     * @param  string                          $changedsince The modseq (optional).
     * @return array<int,array<string,mixed>>|string         The items by UID on success;
     *                                                       error string on failure.
     */
    public function Fetch(string $uids, string $items, string $changedsince = '')
    {
        $cmd = "UID FETCH $uids ($items)";
        if ($changedsince !== '') {
            $cmd .= " (CHANGEDSINCE $changedsince)";
        }
        $error = $this->command($cmd, $untagged);
        if ($error !== '') {
            return $error;
        }
        $result = [];
        foreach ($untagged as $tokens) {
            if (self::word($tokens, 1) !== 'FETCH' || !is_array($tokens[2] ?? null)) {
                continue;
            }
            $data = [];
            for ($i = 0; $i + 1 < count($tokens[2]); $i += 2) {
                $data[self::word($tokens[2], $i)] = $tokens[2][$i + 1];
            }
            // The unsolicited FETCH responses (changes done by other clients) can omit the UID
            if (isset($data['UID'])) {
                $result[(int)$data['UID']] = $data;
            }
        }
        ksort($result);
        return $result;
    }

    /**
     * Add or remove flags to the emails of the selected folder.
     *
     * @param  string            $uids  The UIDs, e.g. "4,7,9".
     * @param  string            $op    "+" to add the flags, "-" to remove them.
     * @param  array<int,string> $flags The flags, e.g. ["\Seen", "\Flagged"].
     * @return string                   Empty string on success; non-empty error message on failure.
     */
    public function Store(string $uids, string $op, array $flags)
    {
        $op = $op === '-' ? '-' : '+';
        return $this->command("UID STORE $uids {$op}FLAGS.SILENT (" . implode(' ', $flags) . ')', $untagged);
    }

    /**
     * Move emails of the selected folder to another folder, using MOVE or, if the server
     * not supports it, COPY plus Expunge; the new UIDs are returned when the server
     * supports UIDPLUS (COPYUID response code).
     *
     * @param     string             $uids   The UIDs, e.g. "4,7,9".
     * @param     string             $folder Full name of the destination folder in UTF-8.
     * @param     array|null         $map    Filled with the new UIDs by old UID, empty if
     *                                       the server not returns them.
     * @param-out array<int,int>     $map
     * @return    string                     Empty string on success; non-empty error message
     *                                       on failure.
     */
    public function Move(string $uids, string $folder, ?array &$map)
    {
        $map = [];
        $verb = $this->HasCapability('MOVE') ? 'MOVE' : 'COPY';
        $error = $this->command("UID $verb $uids " . self::Quote(self::Encode($folder)), $untagged);
        if ($error !== '') {
            return $error;
        }
        // The COPYUID comes in an untagged OK with MOVE and in the tagged OK with COPY
        $texts = [$this->tagged];
        foreach ($untagged as $tokens) {
            if (self::word($tokens, 0) === 'OK') {
                $texts[] = 'OK ' . $tokens[1];
            }
        }
        foreach ($texts as $text) {
            if (preg_match('~^OK \[COPYUID \d+ ([\d:,]+) ([\d:,]+)\]~i', $text, $matches)) {
                $old = self::uid_set($matches[1]);
                $new = self::uid_set($matches[2]);
                if (count($old) === count($new)) {
                    $map = array_combine($old, $new);
                }
            }
        }
        if ($verb === 'COPY') {
            return $this->Expunge($uids);
        }
        return '';
    }

    /**
     * Delete permanently emails of the selected folder: marks them as deleted and expunges
     * them, using UID EXPUNGE when the server supports UIDPLUS, otherwise EXPUNGE, that also
     * removes the other emails of the folder marked as deleted by other clients.
     *
     * @param  string $uids The UIDs, e.g. "4,7,9".
     * @return string       Empty string on success; non-empty error message on failure.
     */
    public function Expunge(string $uids)
    {
        $error = $this->Store($uids, '+', ['\Deleted']);
        if ($error !== '') {
            return $error;
        }
        $cmd = $this->HasCapability('UIDPLUS') ? "UID EXPUNGE $uids" : 'EXPUNGE';
        return $this->command($cmd, $untagged);
    }

    /**
     * Append an email to a folder, the new UID is returned when the server supports UIDPLUS
     * (APPENDUID response code).
     *
     * @param     string            $folder  Full name of the folder in UTF-8.
     * @param     string            $message The full email (RFC 5322), the line endings are
     *                                       converted to CRLF.
     * @param     array<int,string> $flags   The flags of the email, e.g. ["\Seen"].
     * @param     int|null          $uid     Filled with the new UID, 0 if the server not
     *                                       returns it.
     * @param-out int               $uid
     * @return    string                     Empty string on success; non-empty error message
     *                                       on failure.
     */
    public function Append(string $folder, string $message, array $flags, ?int &$uid)
    {
        $uid = 0;
        $message = preg_replace("~\r?\n~", "\r\n", $message);
        $length = strlen($message);
        $cmd = 'APPEND ' . self::Quote(self::Encode($folder)) . ' (' . implode(' ', $flags) . ')';
        // With LITERAL+ the email is sent with the command, otherwise after the continuation
        if ($this->HasCapability('LITERAL+')) {
            $error = $this->command("$cmd {{$length}+}\r\n$message", $untagged, 'APPEND');
        } else {
            $error = $this->command("$cmd {{$length}}", $untagged, 'APPEND', $message);
        }
        if ($error !== '') {
            return $error;
        }
        if (preg_match('~^OK \[APPENDUID \d+ (\d+)\]~i', $this->tagged, $matches)) {
            $uid = (int)$matches[1];
        }
        return '';
    }

    /**
     * Word helper, returns a token of a response in uppercase to compare it with the
     * keywords, or an empty string if the token is missing or is not a string (a list or
     * NIL), to process safely any response of the server.
     *
     * @param  array  $tokens The tokens of the response.
     * @param  int    $index  The position of the token.
     * @return string         The token in uppercase or empty.
     */
    private static function word(array $tokens, int $index)
    {
        return is_string($tokens[$index] ?? null) ? strtoupper($tokens[$index]) : '';
    }

    /**
     * UID set helper, expands a set of UIDs like "4:6,9" to the list [4, 5, 6, 9].
     *
     * @param  string         $set The set of UIDs.
     * @return array<int,int>      The list of UIDs.
     */
    private static function uid_set(string $set)
    {
        $uids = [];
        foreach (explode(',', $set) as $range) {
            $limits = array_map('intval', explode(':', $range));
            $uids = array_merge($uids, range(min($limits), max($limits)));
        }
        return $uids;
    }

    /**
     * Encode a folder name from UTF-8 to the modified UTF-7 used by IMAP.
     *
     * @param  string $name The name in UTF-8.
     * @return string       The name in modified UTF-7.
     */
    public static function Encode(string $name)
    {
        return mb_convert_encoding($name, 'UTF7-IMAP', 'UTF-8');
    }

    /**
     * Decode a folder name from the modified UTF-7 used by IMAP to UTF-8.
     *
     * @param  string $name The name in modified UTF-7.
     * @return string       The name in UTF-8.
     */
    public static function Decode(string $name)
    {
        return mb_convert_encoding($name, 'UTF-8', 'UTF7-IMAP');
    }

    /**
     * Special use by name helper, used with the servers without SPECIAL-USE to detect the
     * special folders by their usual names.
     *
     * @param  string $label The name of the folder in UTF-8, without the parents.
     * @return string        The special use or empty.
     */
    private static function special_use_by_name(string $label)
    {
        $names = [
            '\Sent' => [
                'sent', 'sent items', 'sent messages', 'sent mail',
                'enviados', 'elementos enviados', 'enviats',
            ],
            '\Trash' => ['trash', 'deleted items', 'deleted messages', 'bin', 'papelera', 'paperera'],
            '\Junk' => [
                'junk', 'spam', 'junk e-mail', 'junk email', 'bulk mail',
                'correo no deseado', 'correu brossa',
            ],
            '\Drafts' => ['drafts', 'borradores', 'esborranys'],
            '\Archive' => ['archive', 'archivo', 'arxiu'],
        ];
        $label = mb_strtolower($label);
        foreach ($names as $use => $list) {
            if (in_array($label, $list, true)) {
                return $use;
            }
        }
        return '';
    }

    /**
     * Quote a string to be used as argument of a command, escaping the backslashes and
     * the double quotes.
     *
     * @param  string $value The string to quote.
     * @return string        The quoted string.
     */
    public static function Quote(string $value)
    {
        return '"' . addcslashes($value, '"\\') . '"';
    }

    /**
     * Parse the data of an untagged response into a list of tokens: the atoms and the
     * numbers as strings, the quoted strings and the literals decoded, NIL as null and the
     * parenthesized lists as arrays. The brackets of the atoms like BODY[HEADER] are kept
     * in the atom, including its spaces and parentheses.
     *
     * @param  string $data The response without the "* " prefix, including the literals.
     * @return array        The list of tokens.
     */
    public static function Parse(string $data)
    {
        $pos = 0;
        return self::parse_list($data, $pos, '');
    }

    /**
     * Parse list helper, used by Parse to parse the tokens until the end of the data or
     * until the end of the current parenthesized list.
     *
     * @param  string $data The data to parse.
     * @param  int    $pos  The current position, updated while parsing.
     * @param  string $end  The character that ends the list, or empty for the top level.
     * @return array        The list of tokens.
     */
    private static function parse_list(string $data, int &$pos, string $end)
    {
        $tokens = [];
        $length = strlen($data);
        while ($pos < $length) {
            $char = $data[$pos];
            if ($char === ' ') {
                $pos++;
                continue;
            }
            if ($char === ')') {
                $pos++;
                if ($end === ')') {
                    return $tokens;
                }
                continue;
            }
            if ($char === '(') {
                $pos++;
                $tokens[] = self::parse_list($data, $pos, ')');
                continue;
            }
            if ($char === '"') {
                $pos++;
                $value = '';
                while ($pos < $length && $data[$pos] !== '"') {
                    if ($data[$pos] === '\\') {
                        $pos++;
                    }
                    $value .= $data[$pos] ?? '';
                    $pos++;
                }
                $pos++;
                $tokens[] = $value;
                continue;
            }
            if ($char === '{' && preg_match('~\G\{(\d+)\+?\}\r\n~', $data, $matches, 0, $pos)) {
                $pos += strlen($matches[0]);
                $tokens[] = substr($data, $pos, (int)$matches[1]);
                $pos += (int)$matches[1];
                continue;
            }
            $value = '';
            $depth = 0;
            while ($pos < $length) {
                $char = $data[$pos];
                if ($depth === 0 && in_array($char, [' ', '(', ')'], true)) {
                    break;
                }
                if ($char === '[') {
                    $depth++;
                }
                if ($char === ']') {
                    $depth--;
                }
                $value .= $char;
                $pos++;
            }
            $tokens[] = strtoupper($value) === 'NIL' ? null : $value;
        }
        return $tokens;
    }

    /**
     * Send a command and read its responses until the tagged response.
     *
     * @param     string     $cmd          IMAP command without the tag, e.g. "CAPABILITY".
     * @param     array|null $untagged     Filled with the untagged responses, each one is
     *                                     the list of tokens returned by Parse, except the
     *                                     status responses (OK, NO, BAD, BYE) that are the
     *                                     status and the text.
     * @param     string     $name         Name of the command used in the error messages, to
     *                                     hide the arguments of the commands with passwords.
     * @param     string     $continuation Line sent when the server requests a continuation.
     * @param-out array      $untagged
     * @return    string                   Empty string on success; non-empty error message
     *                                     on failure.
     */
    private function command(string $cmd, ?array &$untagged, string $name = '', string $continuation = '')
    {
        $untagged = [];
        if ($this->socket === null) {
            return 'Command failed: not connected';
        }
        if ($name === '') {
            $name = $cmd;
        }
        $tag = sprintf('A%04d', ++$this->tag);
        $error = $this->socket->WriteLine("$tag $cmd");
        if ($error !== '') {
            // @codeCoverageIgnoreStart
            // Not testable, the writes only fail when the connection is broken
            return $this->abort($error);
            // @codeCoverageIgnoreEnd
        }
        while (true) {
            $error = $this->read($raw);
            if ($error !== '') {
                return $this->abort($error);
            }
            if (substr($raw, 0, 2) === '* ') {
                $untagged[] = $this->untagged(substr($raw, 2));
                continue;
            }
            if (substr($raw, 0, 1) === '+') {
                if ($continuation === '') {
                    return "$name failed: unexpected continuation";
                }
                $error = $this->socket->WriteLine($continuation);
                if ($error !== '') {
                    // @codeCoverageIgnoreStart
                    // Not testable, the writes only fail when the connection is broken
                    return $this->abort($error);
                    // @codeCoverageIgnoreEnd
                }
                $continuation = '';
                continue;
            }
            if (substr($raw, 0, strlen($tag) + 1) === "$tag ") {
                $text = substr($raw, strlen($tag) + 1);
                $this->tagged = $text;
                $this->code_capability($text);
                if (preg_match('~^OK\b~i', $text)) {
                    return '';
                }
                return "$name failed: $text";
            }
        }
    }

    /**
     * Read a full response line, including the literals: when a line ends with a literal
     * {n}, the n bytes and the rest of the line are read and added to the response.
     *
     * @param     string|null $raw Filled with the response, the literals keep the {n} and
     *                             the CRLF before the bytes, as sent by the server.
     * @param-out string   $raw
     * @return    string           Empty string on success; non-empty error message on failure.
     */
    private function read(?string &$raw)
    {
        $raw = '';
        while (true) {
            $error = $this->socket->ReadLine($line);
            if ($error !== '') {
                return $error;
            }
            $raw .= $line;
            if (!preg_match('~\{(\d+)\+?\}$~', $line, $matches)) {
                return '';
            }
            $error = $this->socket->ReadBytes((int)$matches[1], $bytes);
            if ($error !== '') {
                return $error;
            }
            $raw .= "\r\n" . $bytes;
        }
    }

    /**
     * Process an untagged response: the status responses are returned as the status and
     * the text (checking the CAPABILITY response code), the other responses are parsed and
     * the CAPABILITY response updates the capabilities.
     *
     * @param  string $data The response without the "* " prefix.
     * @return array        The status and the text, or the list of tokens.
     */
    private function untagged(string $data)
    {
        if (preg_match('~^(OK|NO|BAD|BYE|PREAUTH)\b ?(.*)$~is', $data, $matches)) {
            $this->code_capability($data);
            return [strtoupper($matches[1]), $matches[2]];
        }
        $tokens = self::Parse($data);
        if (self::word($tokens, 0) === 'CAPABILITY') {
            $this->set_capabilities(array_slice($tokens, 1));
        }
        return $tokens;
    }

    /**
     * Check the CAPABILITY response code of a status response, like the greeting or the
     * tagged response of the login, and updates the capabilities.
     *
     * @param string $text The status response, e.g. "OK [CAPABILITY IMAP4rev1 ...] Ready".
     */
    private function code_capability(string $text): void
    {
        if (preg_match('~^\w+ \[CAPABILITY ([^\]]*)\]~i', $text, $matches)) {
            $this->set_capabilities(explode(' ', trim($matches[1])));
        }
    }

    /**
     * Set the capabilities, in uppercase and without the ignored ones.
     *
     * @param array $list The capabilities announced by the server.
     */
    private function set_capabilities(array $list): void
    {
        $list = array_map('strtoupper', array_map('strval', $list));
        $ignore = array_map('strtoupper', $this->ignore);
        $this->capabilities = array_values(array_diff($list, $ignore, ['']));
    }

    /**
     * Abort helper, closes the connection after an error in the opening of the session or
     * an error of the connection, because the responses of the session are lost.
     *
     * @param  string $error The error message.
     * @return string        The same error message.
     */
    private function abort(string $error)
    {
        if ($this->socket !== null) {
            $this->socket->Close();
            $this->socket = null;
        }
        return $error;
    }
}
