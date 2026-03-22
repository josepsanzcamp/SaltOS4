<?php

/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
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
 * Test memory
 *
 * This test performs some tests to validate the correctness
 * of the memory functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Main class of this unit test
 */
final class test_memory extends TestCase
{
    #[testdox('memory functions')]
    /**
     * memory test
     *
     * This test performs some tests to validate the correctness
     * of the memory functions
     */
    public function test_memory(): void
    {
        $this->assertSame(memory_get_free(false), 0);
        $this->assertSame(memory_get_free(true), INF);

        $this->assertSame(init_timer(), null);

        $this->assertSame(time_get_usage(false) < 1, true);
        $this->assertSame(time_get_usage(true) < 1, true);

        $this->assertSame(time_get_free(false) > 99, true);
        $this->assertSame(time_get_free(true) > 500, true);

        $this->assertSame(set_max_memory_limit(), null);
        $this->assertSame(set_max_execution_time(), null);

        $old = get_config('iniset/max_execution_time');
        set_config('iniset/max_execution_time', 0);
        time_get_usage();
        set_config('iniset/max_execution_time', $old);
    }
}
