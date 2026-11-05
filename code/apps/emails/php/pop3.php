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
 * SaltOS POP3 Client Component
 *
 * PURPOSE:
 * POP3 client used by the emails app to download the emails of the accounts.
 *
 * ARCHITECTURE & DESIGN:
 * - Persistent session: one connection and one login for all the commands, using the
 * transport of socket.php (PHP streams).
 * - Transport: Supports implicit TLS (POP3S) or STARTTLS (STLS).
 * - The DELE command only marks the messages, the server removes them when the session
 * is closed with QUIT, so the message numbers do not change during the session.
 *
 * SECURITY & CONFIGURATION:
 * - Warning: Certificate verification is currently disabled for compatibility.
 */

require_once 'apps/emails/php/socket.php';

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

    /** @var int Timeout in seconds used when connecting and when reading. */
    public $timeout = 30;

    /** @var socket_class|null The connection with the server. */
    private $socket = null;

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
            $this->port = (int)$this->ssl === 1 ? 995 : 110;
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

        // Greeting of the server
        $error = $this->response('Greeting', $line);
        if ($error !== '') {
            $this->socket->Close();
            $this->socket = null;
            return $error;
        }

        // STARTTLS via STLS when requested (and not using implicit TLS)
        if ((int)$this->ssl !== 1 && (int)$this->tls === 1) {
            $error = $this->command('STLS', $line);
            if ($error === '') {
                $error = $this->socket->StartTls();
            }
            if ($error !== '') {
                $this->socket->Close();
                $this->socket = null;
                return $error;
            }
        }

        // Login
        $error = $this->command('USER ' . $this->user, $line);
        if ($error === '') {
            $error = $this->command('PASS ' . $this->pass, $line, 'PASS');
        }
        if ($error !== '') {
            $this->socket->Close();
            $this->socket = null;
            return $error;
        }
        return '';
    }

    /**
     * Retrieve the server's UIDL listing and return a map of index => uidl.
     *
     * @return array<int,string>|string Array of [msgIndex => uidl] on success; error string on failure.
     */
    public function ListMessages()
    {
        $error = $this->multiline('UIDL', $lines);
        if ($error !== '') {
            return $error;
        }

        $result = [];
        foreach ($lines as $line) {
            if (preg_match('~^(\d+)\s+(\S+)~', trim($line), $m)) {
                $result[(int)$m[1]] = (string)$m[2];
            }
        }
        ksort($result, SORT_NUMERIC);
        return $result;
    }

    /**
     * Download a full message by server index using "RETR <index>".
     *
     * @param     int         $index  1-based server message index.
     * @param     string|null $out    Filled with the raw RFC 5322 message on success.
     * @param-out string    $out
     * @return    string              Empty string on success; non-empty error message on failure.
     */
    public function GetMessage($index, &$out)
    {
        $out = '';
        $index = (int)$index;
        if ($index < 1) {
            return 'OpenMessage failed: bad index';
        }

        $error = $this->multiline("RETR {$index}", $lines);
        if ($error !== '') {
            return $error;
        }
        $out = implode("\r\n", $lines) . "\r\n";
        return '';
    }

    /**
     * Issue "DELE <index>" to mark a message for deletion, the server removes the marked
     * messages when the session is closed, so the indexes do not change during the session.
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
        return $this->command("DELE {$index}", $line);
    }

    /**
     * Close the session with QUIT, applying the deletions, and close the connection.
     *
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function Close()
    {
        if ($this->socket === null) {
            return '';
        }
        $error = $this->command('QUIT', $line);
        $this->socket->Close();
        $this->socket = null;
        return $error;
    }

    /**
     * Send a command and read its single-line response.
     *
     * @param     string      $cmd  POP3 command (verbatim), e.g., "STAT", "DELE 3".
     * @param     string|null $line Filled with the response line.
     * @param     string      $name Name of the command used in the error messages, to hide
     *                           the arguments of the commands with passwords.
     * @param-out string   $line
     * @return    string            Empty string on success; non-empty error message on failure.
     */
    private function command(string $cmd, ?string &$line, string $name = '')
    {
        $line = '';
        if ($this->socket === null) {
            return 'Command failed: not connected';
        }
        $error = $this->socket->WriteLine($cmd);
        if ($error !== '') {
            // @codeCoverageIgnoreStart
            // Not testable, the writes only fail when the connection is broken
            return $error;
            // @codeCoverageIgnoreEnd
        }
        if ($name === '') {
            $name = $cmd;
        }
        return $this->response($name, $line);
    }

    /**
     * Read a single-line response, only the status of the first line is checked.
     *
     * @param     string      $name Name of the command used in the error messages.
     * @param     string|null $line Filled with the response line.
     * @param-out string   $line
     * @return    string            Empty string on success; non-empty error message on failure.
     */
    private function response(string $name, ?string &$line)
    {
        $error = $this->socket->ReadLine($line);
        if ($error !== '') {
            return $error;
        }
        if (substr($line, 0, 3) !== '+OK') {
            return "$name failed: $line";
        }
        return '';
    }

    /**
     * Send a command and read its multi-line response, ended by a line with a single dot,
     * removing the dot-stuffing of the lines that begin with a dot.
     *
     * @param     string     $cmd   POP3 command (verbatim), e.g., "UIDL", "RETR 1".
     * @param     array|null $lines Filled with the lines of the response, without the status.
     * @param-out array   $lines
     * @return    string            Empty string on success; non-empty error message on failure.
     */
    private function multiline(string $cmd, ?array &$lines)
    {
        $lines = [];
        $error = $this->command($cmd, $line);
        if ($error !== '') {
            return $error;
        }
        while (true) {
            $error = $this->socket->ReadLine($line);
            if ($error !== '') {
                // @codeCoverageIgnoreStart
                // Not testable, the server must break the connection in the middle of a response
                return $error;
                // @codeCoverageIgnoreEnd
            }
            if ($line === '.') {
                break;
            }
            if (substr($line, 0, 1) === '.') {
                $line = substr($line, 1);
            }
            $lines[] = $line;
        }
        return '';
    }
}
