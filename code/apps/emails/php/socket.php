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
 * Socket client
 *
 * This file contains the transport used by the mail protocol clients (POP3 and IMAP): a
 * persistent connection using the PHP streams, with implicit TLS, STARTTLS and timeouts,
 * and the functions to read and write lines and blocks of bytes
 */

/**
 * Socket class
 *
 * All the methods return an empty string on success and a non-empty error message on
 * failure, following the style of the other clients of the emails app, the warnings of
 * the stream functions are ignored using overload_error_handler because the SaltOS error
 * handler stops the execution even when the @ operator is used
 */
final class socket_class
{
    /** @var string Server hostname or IP (required). */
    public $hostname = '';

    /** @var int Server TCP port (required). */
    public $port = 0;

    /** @var int 1 to use implicit TLS when connecting; 0 for a plain connection. */
    public $ssl = 0;

    /** @var int 1 to verify the certificate of the server; 0 to skip the checks. */
    public $verify = 0;

    /** @var int Timeout in seconds used when connecting and when reading. */
    public $timeout = 30;

    /** @var resource|null The stream of the connection. */
    private $stream = null;

    /**
     * Open the connection with the server
     *
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function Connect()
    {
        if (!$this->hostname) {
            return 'Connect failed: empty host';
        }
        if (!$this->port) {
            return 'Connect failed: empty port';
        }
        $scheme = (int)$this->ssl === 1 ? 'ssl' : 'tcp';
        $address = sprintf('%s://%s:%d', $scheme, $this->hostname, (int)$this->port);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => (int)$this->verify === 1,
                'verify_peer_name' => (int)$this->verify === 1,
                'allow_self_signed' => (int)$this->verify !== 1,
                'SNI_enabled' => true,
                'peer_name' => $this->hostname,
            ],
        ]);
        $errno = 0;
        $errstr = '';
        overload_error_handler('stream_socket_client');
        $stream = stream_socket_client(
            $address,
            $errno,
            $errstr,
            (int)$this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        restore_error_handler();
        if ($stream === false) {
            return "Connect failed: $errstr ($errno)";
        }
        stream_set_timeout($stream, (int)$this->timeout);
        $this->stream = $stream;
        return '';
    }

    /**
     * Upgrade the plain connection to TLS, used after the STARTTLS command of the protocol
     *
     * @return string Empty string on success; non-empty error message on failure.
     */
    public function StartTls()
    {
        if ($this->stream === null) {
            return 'StartTls failed: not connected';
        }
        overload_error_handler('stream_socket_enable_crypto');
        $result = stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        restore_error_handler();
        if ($result !== true) {
            return 'StartTls failed: TLS negotiation error';
        }
        return '';
    }

    /**
     * Write a line to the server, adding the CRLF line terminator
     *
     * @param  string $line The line without the line terminator.
     * @return string       Empty string on success; non-empty error message on failure.
     */
    public function WriteLine(string $line)
    {
        return $this->WriteBytes($line . "\r\n");
    }

    /**
     * Write a block of bytes to the server
     *
     * @param  string $data The bytes to write.
     * @return string       Empty string on success; non-empty error message on failure.
     */
    public function WriteBytes(string $data)
    {
        if ($this->stream === null) {
            return 'Write failed: not connected';
        }
        $length = strlen($data);
        $written = 0;
        while ($written < $length) {
            overload_error_handler('fwrite');
            $result = fwrite($this->stream, substr($data, $written));
            restore_error_handler();
            if ($result === false || $result === 0) {
                // @codeCoverageIgnoreStart
                // Not testable, the writes only fail when the connection is broken
                return $this->ReadError('Write failed');
                // @codeCoverageIgnoreEnd
            }
            $written += $result;
        }
        return '';
    }

    /**
     * Read a line from the server, removing the CRLF line terminator
     *
     * @param     string|null $line Filled with the line without the line terminator.
     * @param-out string    $line
     * @return    string            Empty string on success; non-empty error message on failure.
     */
    public function ReadLine(?string &$line)
    {
        $line = '';
        if ($this->stream === null) {
            return 'Read failed: not connected';
        }
        overload_error_handler('fgets');
        $result = fgets($this->stream);
        restore_error_handler();
        if ($result === false) {
            return $this->ReadError('Read failed');
        }
        $line = rtrim($result, "\r\n");
        return '';
    }

    /**
     * Read a block of bytes from the server, used by the literals of IMAP
     *
     * @param     int         $length The number of bytes to read.
     * @param     string|null $data   Filled with the bytes read.
     * @param-out string    $data
     * @return    string              Empty string on success; non-empty error message on failure.
     */
    public function ReadBytes(int $length, ?string &$data)
    {
        $data = '';
        if ($this->stream === null) {
            return 'Read failed: not connected';
        }
        while (strlen($data) < $length) {
            overload_error_handler('fread');
            $result = fread($this->stream, $length - strlen($data));
            restore_error_handler();
            if ($result === false || $result === '') {
                return $this->ReadError('Read failed');
            }
            $data .= $result;
        }
        return '';
    }

    /**
     * Close the connection with the server
     *
     * @return string Always returns empty string.
     */
    public function Close()
    {
        if ($this->stream !== null) {
            overload_error_handler('fclose');
            fclose($this->stream);
            restore_error_handler();
            $this->stream = null;
        }
        return '';
    }

    /**
     * Read error helper
     *
     * This function returns the error message of a failed read or write, detecting the
     * timeouts and the connections closed by the server
     *
     * @param  string $prefix The prefix of the error message.
     * @return string         The error message.
     */
    private function ReadError(string $prefix)
    {
        $meta = stream_get_meta_data($this->stream);
        if ($meta['timed_out']) {
            return "$prefix: timeout";
        }
        if ($meta['eof']) {
            return "$prefix: connection closed by the server";
        }
        // @codeCoverageIgnoreStart
        // Not testable, the reads and writes only fail by timeout or by end of connection
        return "$prefix: unknown error";
        // @codeCoverageIgnoreEnd
    }
}
