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

// phpcs:disable PSR1.Classes.ClassDeclaration
// phpcs:disable Squiz.Classes.ValidClassName
// phpcs:disable PSR1.Methods.CamelCapsMethodName

/**
 * pop3_class: cURL/POP3 compatible replacement.
 * Requirements: ext-curl enabled.
 *
 * Notes:
 * - This implementation authenticates per command (no persistent session),
 *   leveraging cURL’s POP3 support to keep transport logic simple and robust.
 * - STARTTLS (STLS) can be requested via $tls=1 (when not using POP3S).
 */
class pop3_class
{
    public $hostname = '';
    public $port = 110;
    public $tls = 0; // 1 = STARTTLS (STLS)
    public $ssl = 0; // 1 = POP3S (implicit TLS)

    private $user = '';
    private $pass = '';
    private $baseUrl = '';
    private $msgBuffer = null;
    private $msgPos = 0;

    /**
     * Initialize connection parameters and compute base URL.
     * Returns '' on success or an error string on failure.
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
        return '';
    }

    /**
     * Store credentials and validate them with a lightweight NOOP.
     * Returns '' on success or an error string on failure.
     */
    public function Login($user, $pass)
    {
        $this->user = (string)$user;
        $this->pass = (string)$pass;

        // Verify credentials using a one-line command (NOOP)
        $err = $this->exec('NOOP', true, $out, false);
        return $err; // '' if OK
    }

    /**
     * List messages.
     * $uidls=0 → LIST (sizes) | $uidls=1 → UIDL (unique IDs)
     * @return array|string Array on success; error string on failure.
     */
    public function ListMessages($folder = '', $uidls = 0)
    {
        $cmd = ((int)$uidls === 1) ? 'UIDL' : 'LIST';
        $err = $this->exec($cmd, true, $out, true);
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
                $result[$n] = ((int)$uidls === 1) ? (string)$v : (int)$v;
            }
        }
        ksort($result, SORT_NUMERIC);
        return $result;
    }

    /**
     * Open a message for streamed reads (via GetMessage()).
     * $lines = -1 → full message (RETR); otherwise use TOP with $lines.
     * Returns '' on success or an error string on failure.
     */
    public function OpenMessage($index, $lines = -1)
    {
        $index = (int)$index;
        if ($index < 1) {
            return 'OpenMessage failed: bad index';
        }
        $custom = true;
        $cmd = ($lines === -1)
            ? "RETR {$index}"
            : "TOP {$index} " . max(0, (int)$lines);

        $err = $this->exec($cmd, $custom, $out, true);
        if ($err !== '') {
            return $err;
        }

        $this->msgBuffer = (string)$out;
        $this->msgPos = 0;
        return '';
    }

    /**
     * Read a chunk from the currently opened message.
     * @param  int    $length Bytes to read
     * @param  string &$out   Output chunk
     * @param  int    &$eof   1 when end-of-file is reached; 0 otherwise
     * @return string Empty string on success; error text on failure
     */
    public function GetMessage($length, &$out, &$eof)
    {
        $out = '';
        $eof = 0;
        if ($this->msgBuffer === null) {
            return 'No message opened';
        }

        $len = strlen($this->msgBuffer);
        if ($this->msgPos >= $len) {
            $eof = 1;
            return '';
        }

        $chunk = substr($this->msgBuffer, $this->msgPos, (int)$length);
        $this->msgPos += strlen($chunk);
        $out = $chunk;
        if ($this->msgPos >= $len) {
            $eof = 1;
        }
        return '';
    }

    /**
     * Mark a message for deletion.
     * Returns '' on success or an error string on failure.
     */
    public function DeleteMessage($index)
    {
        $index = (int)$index;
        if ($index < 1) {
            return 'DeleteMessage failed: bad index';
        }
        return $this->exec("DELE {$index}", true, $out, false);
    }

    /**
     * Cleanup local state. Each cURL call is standalone, so no persistent QUIT is needed.
     */
    public function Close()
    {
        $this->msgBuffer = null;
        $this->msgPos = 0;
        return '';
    }

    /**
     * Execute a POP3 command (UIDL, LIST, RETR N, TOP N L, DELE N, NOOP, CAPA, etc.).
     *
     * If $custom=true we use CURLOPT_CUSTOMREQUEST with the verb (e.g., "UIDL", "LIST", "RETR 1").
     * If $custom=false we switch to a path-style request (/N) — kept for compatibility if needed.
     *
     * $withBody controls whether we expect a multi-line response:
     *  - true  → multi-line commands (UIDL/LIST/RETR/TOP) return the body
     *  - false → one-line commands (NOOP/DELE/CAPA) avoid waiting for a body to prevent hangs
     *
     * @param  string  $cmdOrPath  POP3 command or path
     * @param  bool    $custom     Use CUSTOMREQUEST when true
     * @param  ?string &$out       Filled with response body when $withBody = true
     * @param  bool    $withBody   Whether to read a response body
     * @return string '' on success; error text on failure
     */
    // @phpstan-ignore parameterByRef.unusedType
    private function exec(string $cmdOrPath, bool $custom, ?string &$out, bool $withBody = true)
    {
        $out = '';
        $ch = curl_init();

        $opts = [
            CURLOPT_USERNAME         => $this->user,
            CURLOPT_PASSWORD         => $this->pass,
            CURLOPT_RETURNTRANSFER   => true,
            CURLOPT_URL              => $this->baseUrl,

            // NOTE: Verification is disabled for compatibility.
            CURLOPT_SSL_VERIFYPEER   => false,
            CURLOPT_SSL_VERIFYHOST   => 0,
            CURLOPT_SSL_ENABLE_ALPN  => false,

            //~ CURLOPT_VERBOSE      => true,
        ];

        // STARTTLS via STLS when requested (and not using implicit TLS)
        if ($this->ssl != 1 && $this->tls == 1) {
            $opts[CURLOPT_USE_SSL] = CURLUSESSL_ALL;
        }

        if ($custom) {
            // e.g., "UIDL", "LIST", "RETR 1", "TOP 3 20", "DELE 2"
            $opts[CURLOPT_CUSTOMREQUEST] = $cmdOrPath;
        } else {
            // Path-based fallback (rarely needed with POP3)
            $opts[CURLOPT_URL] = rtrim($this->baseUrl, '/') . '/' . ltrim($cmdOrPath, '/');
        }

        if ($withBody) {
            // Multi-line commands: we want the body (UIDL/LIST/RETR/TOP)
            $opts[CURLOPT_NOBODY] = false;
        } else {
            // One-line commands: do not wait for a body to avoid hangs (Debian 12 workaround)
            $opts[CURLOPT_NOBODY]        = true;
            $opts[CURLOPT_FRESH_CONNECT] = true;  // force a new connection
            $opts[CURLOPT_FORBID_REUSE]  = true;  // and do not reuse it
        }

        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = 'cURL: ' . curl_error($ch);
            curl_close($ch);
            return $err;
        }

        // POP3 is text-based; CURLINFO_RESPONSE_CODE may be unused/zero here.
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $out = $withBody ? (string)$resp : '';

        // Basic POP3 error detection from response payload (multi-line cases)
        if ($withBody && preg_match('~^-ERR~mi', $out)) {
            return 'POP3 error: ' . trim($out);
        }
        return '';
    }
}
