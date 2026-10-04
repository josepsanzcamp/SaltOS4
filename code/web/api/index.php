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
 * This script is the only thing of the api published in the web directory,
 * it jumps to the real api that lives outside of the web directory, in this
 * way, the rest of the contents of the api are not accessible from the web
 *
 * By default the api is located next to the web directory, if your api
 * lives in another place, change this path to point to its index.php
 */
$_SERVER['SCRIPT_FILENAME'] = dirname($_SERVER['SCRIPT_FILENAME']) . '/../../api/index.php';
require $_SERVER['SCRIPT_FILENAME'];
