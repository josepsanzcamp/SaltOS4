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

/**
 * SaltOS POP3 Client Component
 *
 * PURPOSE:
 * Replaces legacy phpclasses.org POP3 implementations with a modern, cURL-based
 * architecture to eliminate dependency on obsolete external code.
 *
 * ARCHITECTURE & DESIGN:
 * - Stateless per-command operation: Executes verbs via fresh cURL transfers
 * instead of maintaining persistent app-level TCP sessions.
 * - Transport: Supports implicit TLS (POP3S) or opportunistic STARTTLS.
 * - Centralized execution: `exec()` handles protocol multi-line/single-line
 * responses and error normalization.
 *
 * SECURITY & CONFIGURATION:
 * - Warning: Certificate verification is currently disabled for compatibility.
 * Production environments should enable peer/host verification.
 * - Requirement: PHP cURL extension (ext-curl).
 */
final class pop3_class
{
    /** @var string POP3 server hostname or IP (required). */
    public $hostname = '';

    /** @var int POP3 TCP port (110 for POP3, 995 for POP3S if not set explicitly). */
    public $port = 110;

    /** @var int 1 to request STARTTLS (STLS) when using plain POP3; 0 otherwise. */
    public $tls = 0;

    /** @var int 1 to use POP3S (implicit TLS); 0 for plain POP3. */
    public $ssl = 0;

    /** @var string Username for authentication. */
    public $user = '';

    /** @var string Password for authentication. */
    public $pass = '';

    /** @var string Computed base URL (pop3[s]://host:port/). */
    private $baseUrl = '';

    /** @var CurlHandle|null cURL handle reused across invocations. */
    private $ch = null;

    /** @var array|null Baseline cURL options applied on each exec(). */
    private $opts = null;

    /**
     * Prepare the cURL handle and compute the base URL based on configuration.
     *
     * This does not establish a persistent POP3 session; it only sets up
     * the common cURL options used by exec().
     *
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function Open()
    {
        if (!$this->hostname) {
            return 'Open failed: empty host';
        }

        // Choose URL scheme and default port
        if ((int)$this->ssl === 1) {
            $scheme = 'pop3s'; // POP3 over implicit TLS
            if (!$this->port) {
                $this->port = 995;
            }
        } else {
            $scheme = 'pop3';
            if (!$this->port) {
                $this->port = 110;
            }
        }
        $this->baseUrl = sprintf('%s://%s:%d/', $scheme, $this->hostname, (int)$this->port);

        $this->ch = curl_init();

        $this->opts = [
            CURLOPT_USERNAME         => $this->user,
            CURLOPT_PASSWORD         => $this->pass,
            CURLOPT_RETURNTRANSFER   => true,
            CURLOPT_URL              => $this->baseUrl,

            // NOTE: Disabled for compatibility; enable in production deployments.
            CURLOPT_SSL_VERIFYPEER   => false,
            CURLOPT_SSL_VERIFYHOST   => 0,
            CURLOPT_SSL_ENABLE_ALPN  => false,

            //~ CURLOPT_VERBOSE      => true,
        ];

        // STARTTLS via STLS when requested (and not using implicit TLS)
        if ($this->ssl !== 1 && $this->tls === 1) {
            $this->opts[CURLOPT_USE_SSL] = CURLUSESSL_ALL;
        }

        return '';
    }

    /**
     * Retrieve the server's UIDL listing and return a map of index => uidl.
     *
     * Behavior:
     * - Issues "UIDL" and parses the multi-line response.
     * - Skips status lines (+OK / -ERR) and returns only numbered entries.
     *
     * Caveat:
     * - Indices are assigned by the server and can change between calls if
     *   messages are deleted. Consumers should prefer using UIDLs as stable ids.
     *
     * @return array<int,string>|string Array of [msgIndex => uidl] on success; error string on failure.
     */
    public function ListMessages()
    {
        $err = $this->exec('UIDL', $out, true);
        if ($err !== '') {
            return $err;
        }

        $lines = preg_split('~\r?\n~', trim($out));
        $result = [];
        foreach ($lines as $line) {
            // Skip status lines such as +OK or -ERR
            if ($line === '' || $line[0] === '+' || stripos($line, '-ERR') === 0) {
                continue;
            }
            if (preg_match('~^(\d+)\s+(\S+)~', trim($line), $m)) {
                $n = (int)$m[1];
                $v = $m[2];
                $result[$n] = (string)$v;
            }
        }
        ksort($result, SORT_NUMERIC);
        return $result;
    }

    /**
     * Download a full message by server index using "RETR <index>".
     *
     * @param  int         $index  1-based server message index.
     * @param  string|null $out    Filled with the raw RFC 5322 message on success.
     * @return string              Empty string on success; non-empty error message on failure.
     */
    public function GetMessage($index, &$out)
    {
        $index = (int)$index;
        if ($index < 1) {
            return 'OpenMessage failed: bad index';
        }

        $err = $this->exec("RETR {$index}", $out, true);
        if ($err !== '') {
            return $err;
        }
        return '';
    }

    /**
     * Issue "DELE <index>" to mark a message for deletion.
     *
     * Notes:
     * - Since this client operates per-command with independent connections,
     *   deletion is immediately applied server-side for that command.
     * - Be careful when chaining deletions by index; if indices shift between
     *   commands, you may delete unintended messages. Prefer resolving indices
     *   from UIDLs with ListMessages() right before deletion.
     *
     * @param  int    $index 1-based server message index.
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function DeleteMessage($index)
    {
        $index = (int)$index;
        if ($index < 1) {
            return 'DeleteMessage failed: bad index';
        }
        return $this->exec("DELE {$index}", $out, false);
    }

    /**
     * Release the cURL handle. No explicit POP3 QUIT is required because each
     * command is executed as its own transfer.
     *
     * @return string Always returns empty string.
     */
    public function Close()
    {
        curl_close_deprecated($this->ch);
        return '';
    }

    /**
     * Execute a POP3 command using cURL.
     *
     * Behavior:
     * - Sets CURLOPT_CUSTOMREQUEST to the given verb (e.g., "UIDL", "RETR 1", "DELE 2").
     * - When $withBody is true (multi-line commands like UIDL/LIST/RETR/TOP),
     *   fetches and returns the response body in $out.
     * - When $withBody is false (single-line commands like NOOP/DELE/CAPA),
     *   uses CURLOPT_NOBODY and forces a fresh connection to avoid hangs
     *   observed on some distributions (e.g., Debian 12).
     *
     * Error handling:
     * - On transport failure, returns a "cURL: ..." message.
     * - On multi-line responses, scans for "-ERR" and returns a "POP3 error: ..."
     *   message when found.
     *
     * @param  string       $cmd       POP3 command (verbatim), e.g., "UIDL", "RETR 1", "DELE 3".
     * @param  string|null  $out       Filled with response body when $withBody is true; otherwise ''.
     * @param  bool         $withBody  True for multi-line commands; false for single-line commands.
     * @return string                  Empty string on success; non-empty error text on failure.
     */
    // @phpstan-ignore parameterByRef.unusedType
    private function exec(string $cmd, ?string &$out, bool $withBody = true)
    {
        $out = '';

        $opts = $this->opts;
        $opts[CURLOPT_CUSTOMREQUEST] = $cmd;

        if ($withBody) {
            // Multi-line commands: we want the body (UIDL/LIST/RETR/TOP)
            $opts[CURLOPT_NOBODY] = false;
        } else {
            // One-line commands: do not wait for a body to avoid hangs (Debian 12 workaround)
            $opts[CURLOPT_NOBODY]        = true;
            $opts[CURLOPT_FRESH_CONNECT] = true;  // force a new connection
            $opts[CURLOPT_FORBID_REUSE]  = true;  // and do not reuse it
        }

        curl_reset($this->ch);
        curl_setopt_array($this->ch, $opts);
        $resp = curl_exec($this->ch);
        if ($resp === false) {
            $err = 'cURL: ' . curl_error($this->ch);
            curl_close_deprecated($this->ch);
            return $err;
        }

        $out = $withBody ? (string)$resp : '';

        // Basic POP3 error detection from response payload (multi-line cases)
        if ($withBody && preg_match('~^-ERR~mi', $out)) {
            return 'POP3 error: ' . trim($out);
        }
        return '';
    }
}
