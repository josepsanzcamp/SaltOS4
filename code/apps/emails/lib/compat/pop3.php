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
 * pop3_class (replacement using PHP imap extension in POP3 mode)
 * Drop-in for old PHPClasses pop3_class.
 */
class pop3_class
{
    // --- Public API-compatible properties ---
    public $hostname = '';
    public $port = 110;      // default POP3
    public $tls = 0;         // 1 => /tls
    public $ssl = 0;         // optional: 1 => /ssl if you want to use it instead of tls

    // --- Internal state ---
    private $mailboxString = '';
    private $stream = null;
    private $user = '';
    private $pass = '';
    private $opened = false;

    // message buffering to emulate OpenMessage/GetMessage
    private $currentMsgIndex = null;
    private $currentMsgData = '';
    private $currentMsgPtr = 0;

    // tracks if any delete was requested (to expunge on close)
    private $hasDeletes = false;

    // --- Helpers ---
    private function lastError(): string
    {
        $e = imap_last_error();
        imap_errors(); // clear error stack
        return $e ? (string)$e : 'Unknown IMAP/POP3 error';
    }

    private function buildMailboxString(): string
    {
        $host = $this->hostname ?: 'localhost';
        $port = (int)$this->port ?: 110;

        $flags = ['/pop3'];
        if ($this->tls) {
            $flags[] = '/tls';
            $flags[] = '/novalidate-cert';
        }
        if ($this->ssl) {
            $flags[] = '/ssl';
            $flags[] = '/novalidate-cert';
        }

        // POP3 has no folders; the name after } is irrelevant, we use INBOX for compatibility
        return sprintf('{%s:%d%s}INBOX', $host, $port, implode('', $flags));
    }

    // --- Public API ---

    /**
     * Emulates the original Open(): here it only prepares the mailbox string.
     * The real connection is done in Login(), just like in the old class.
     * @return string '' if ok, or error message
     */
    public function Open(): string
    {
        if (!$this->hostname) {
            return 'POP3 host not set';
        }
        $this->mailboxString = $this->buildMailboxString();
        return '';
    }

    /**
     * Opens the authenticated connection (imap_open in POP3 mode).
     * @return string '' if ok, or error message
     */
    public function Login(string $user, string $pass): string
    {
        $this->user = $user;
        $this->pass = $pass;

        // Reasonable timeouts
        imap_timeout(IMAP_OPENTIMEOUT, 20);
        imap_timeout(IMAP_READTIMEOUT, 60);
        imap_timeout(IMAP_WRITETIMEOUT, 60);

        $params = 0; // options
        $n_retries = 1;

        overload_error_handler('imap_open');
        $stream = imap_open($this->mailboxString, $this->user, $this->pass, $params, $n_retries);
        restore_error_handler();
        if ($stream === false) {
            return $this->lastError();
        }
        $this->stream = $stream;
        $this->opened = true;

        // This line prevents the following phperror.log notice:
        // PHP Request Shutdown: SECURITY PROBLEM: insecure server advertised AUTH=PLAIN
        imap_errors();

        // Continue
        return '';
    }

    /**
     * Message list: sizes (uidls=0) or UIDL (uidls=1).
     * Returns array indexed 1..N or error string.
     */
    public function ListMessages(string $mailbox = '', int $uidls = 0)
    {
        if (!$this->opened || !$this->stream) {
            return 'Not connected';
        }
        overload_error_handler('imap_num_msg');
        $num = imap_num_msg($this->stream);
        restore_error_handler();
        if ($num === false) {
            return $this->lastError();
        }
        if ($num < 1) {
            return []; // no messages
        }

        if ($uidls === 0) {
            // Sizes via overview
            overload_error_handler('imap_fetch_overview');
            $ov = imap_fetch_overview($this->stream, "1:$num", 0);
            restore_error_handler();
            if ($ov === false) {
                return $this->lastError();
            }
            $sizes = [];
            foreach ($ov as $o) {
                $sizes[$o->msgno] = isset($o->size) ? (int)$o->size : 0;
            }
            return $sizes;
        } else {
            // Try real POP3 UIDL via overview->uid; fallback to sequential msgno
            overload_error_handler('imap_fetch_overview');
            $ov = imap_fetch_overview($this->stream, "1:$num", 0);
            restore_error_handler();
            if ($ov === false) {
                return $this->lastError();
            }

            $uidlsArr = [];
            foreach ($ov as $o) {
                if (isset($o->uid) && $o->uid !== '' && $o->uid !== 0) {
                    // Real POP3 UIDL
                    $uidlsArr[$o->msgno] = (string)$o->uid;
                } else {
                    // Fallback: sequential id
                    $uidlsArr[$o->msgno] = (string)$o->msgno;
                }
            }
            return $uidlsArr;
        }
    }

    /**
     * Prepares a buffer with the full message.
     * $length is ignored (kept for signature compatibility).
     * @return string '' if ok, or error message
     */
    public function OpenMessage(int $index, int $length = -1): string
    {
        if (!$this->opened || !$this->stream) {
            return 'Not connected';
        }

        // Full headers (no flags)
        overload_error_handler('imap_fetchheader');
        $hdr = imap_fetchheader($this->stream, $index, 0);
        restore_error_handler();
        if ($hdr === false) {
            return $this->lastError();
        }

        // Full body (no flags)
        overload_error_handler('imap_body');
        $body = imap_body($this->stream, $index, 0);
        restore_error_handler();
        if ($body === false) {
            return $this->lastError();
        }

        // --- Normalize header/body seam without cutting content ---
        // Count CRLF at the end of the header (0,1,2)
        $t = 0;
        $end4 = substr($hdr, -4);
        if ($end4 === "\r\n\r\n") {
            $t = 2;
        } elseif (substr($hdr, -2) === "\r\n") {
            $t = 1;
        }

        // Count CRLF at the beginning of the body (0,1,2)
        $b = 0;
        $start4 = substr($body, 0, 4);
        if ($start4 === "\r\n\r\n") {
            $b = 2;
        } elseif (substr($body, 0, 2) === "\r\n") {
            $b = 1;
        }

        // Add only the CRLF needed to reach 2 in total
        $need = 2 - ($t + $b);
        if ($need > 0) {
            $hdr .= str_repeat("\r\n", $need);
        }

        $this->currentMsgIndex = $index;
        $this->currentMsgData  = $hdr . $body;
        $this->currentMsgPtr   = 0;
        return '';
    }

    /**
     * Emulates chunked reading from the buffer loaded by OpenMessage().
     * @param  int    $max  Maximum bytes to read in this call
     * @param  string &$out Block read
     * @param  int    &$eof 1 when there is nothing left to read, 0 otherwise
     * @return string '' if ok, or error message
     */
    public function GetMessage(int $max, string &$out, int &$eof): string
    {
        if ($this->currentMsgIndex === null) {
            $out = '';
            $eof = 1;
            return 'No message opened';
        }
        $remaining = strlen($this->currentMsgData) - $this->currentMsgPtr;
        if ($remaining <= 0) {
            $out = '';
            $eof = 1;
            return '';
        }
        $readLen = ($max > 0) ? min($max, $remaining) : $remaining;
        $out = substr($this->currentMsgData, $this->currentMsgPtr, $readLen);
        $this->currentMsgPtr += $readLen;
        $eof = ($this->currentMsgPtr >= strlen($this->currentMsgData)) ? 1 : 0;
        return '';
    }

    /**
     * Marks a message for deletion (expunged on Close()).
     * @return string '' if ok, or error message
     */
    public function DeleteMessage(int $index): string
    {
        if (!$this->opened || !$this->stream) {
            return 'Not connected';
        }
        overload_error_handler('imap_delete');
        $ok = imap_delete($this->stream, (string)$index, 0); // flag \Deleted
        restore_error_handler();
        // @phpstan-ignore identical.alwaysFalse
        if ($ok === false) {
            return $this->lastError();
        }
        $this->hasDeletes = true;
        return '';
    }

    /**
     * Closes the session; if there were deletes, does EXPUNGE.
     * @return string '' if ok, or error message
     */
    public function Close(): string
    {
        if ($this->stream) {
            $flags = $this->hasDeletes ? CL_EXPUNGE : 0;
            overload_error_handler('imap_close');
            $ok = imap_close($this->stream, $flags);
            restore_error_handler();
            // @phpstan-ignore identical.alwaysFalse
            if ($ok === false) {
                // Even if it fails, try to release resources
                $err = $this->lastError();
                $this->stream = null;
                $this->opened = false;
                return $err;
            }
        }
        $this->stream = null;
        $this->opened = false;
        $this->currentMsgIndex = null;
        $this->currentMsgData  = '';
        $this->currentMsgPtr   = 0;
        $this->hasDeletes = false;
        return '';
    }
}
