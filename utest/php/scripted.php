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
 * Scripted mock server
 *
 * This script is a minimal server used by the unit tests to reproduce the responses that
 * the real servers never send, like errors or connections closed in the middle of a
 * response: it listens in a random local port, prints the port to the standard output,
 * accepts one connection and follows the script received as a JSON array in the first
 * argument, where each item is a line sent to the client, or "<" to wait a line of the
 * client, and the connection is closed when the script ends
 */

$script = json_decode($_SERVER['argv'][1] ?? '[]', true);

// Listening socket
$server = stream_socket_server('tcp://127.0.0.1:0');
echo explode(':', stream_socket_get_name($server, false))[1] . "\n";
fflush(STDOUT);

// Session
$conn = stream_socket_accept($server, 10);
if ($conn !== false) {
    stream_set_timeout($conn, 10);
    foreach ($script as $line) {
        if ($line === '<') {
            if (fgets($conn) === false) {
                break;
            }
            continue;
        }
        fwrite($conn, $line . "\r\n");
    }
    fclose($conn);
}

fclose($server);
