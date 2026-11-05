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

// phpcs:disable PSR1.Files.SideEffects

/**
 * STARTTLS mock server
 *
 * This script is a minimal POP3 server with STARTTLS (STLS) used by the unit tests,
 * because GreenMail not supports STARTTLS: it listens in a random local port, prints
 * the port to the standard output, accepts one connection and responds to the commands
 * STLS, USER, PASS, UIDL and QUIT, using a self-signed certificate created on the fly
 */

// Self-signed certificate
$key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
$csr = openssl_csr_new(['commonName' => '127.0.0.1'], $key);
$cert = openssl_csr_sign($csr, null, $key, 1);
openssl_x509_export($cert, $pem1);
openssl_pkey_export($key, $pem2);
$file = tempnam(sys_get_temp_dir(), 'starttls');
file_put_contents($file, $pem1 . $pem2);

// Listening socket
$server = stream_socket_server('tcp://127.0.0.1:0');
echo explode(':', stream_socket_get_name($server, false))[1] . "\n";
fflush(STDOUT);

// Session
$conn = stream_socket_accept($server, 10);
if ($conn !== false) {
    stream_set_timeout($conn, 10);
    fwrite($conn, "+OK mock ready\r\n");
    while (($line = fgets($conn)) !== false) {
        $line = rtrim($line, "\r\n");
        $cmd = strtoupper(strtok($line, ' '));
        if ($cmd === 'STLS') {
            fwrite($conn, "+OK begin TLS\r\n");
            stream_context_set_option($conn, 'ssl', 'local_cert', $file);
            stream_context_set_option($conn, 'ssl', 'verify_peer', false);
            if (!stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_SERVER)) {
                break;
            }
        } elseif (in_array($cmd, ['USER', 'PASS'], true)) {
            fwrite($conn, "+OK\r\n");
        } elseif ($cmd === 'UIDL') {
            fwrite($conn, "+OK\r\n1 uidl1\r\n.\r\n");
        } elseif ($cmd === 'QUIT') {
            fwrite($conn, "+OK bye\r\n");
            break;
        } else {
            fwrite($conn, "-ERR unknown command\r\n");
        }
    }
    fclose($conn);
}

fclose($server);
unlink($file);
