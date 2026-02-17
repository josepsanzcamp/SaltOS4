<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

/**
 * Random helper module
 *
 * This fie contains useful functions related to random number generator, currently only initialize
 * the internal generator, but in the future we can add more features if it is needed
 */

/**
 * Init Random
 *
 * This function initialize the random number generator
 *
 * Notes:
 *
 * This function previously sets the seed using the microtime, but reading
 * the srand php documentation, I see that the seed is not needed because
 * if it is not provided, a randomly seed is used by default
 */
function init_random()
{
    srand();
}
