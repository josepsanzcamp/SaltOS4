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
 * Test control
 *
 * This test performs some tests to validate the correctness
 * of the control functions
 */

/**
 * Importing namespaces
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Loading helper function
 *
 * This file contains the needed function used by the unit tests
 */
require_once 'php/lib/control.php';
require_once 'php/lib/log.php';
require_once 'php/lib/version.php';
require_once 'php/lib/indexing.php';

/**
 * Main class of this unit test
 */
final class test_control extends TestCase
{
    #[testdox('control functions')]
    /**
     * control test
     *
     * This test performs some tests to validate the correctness
     * of the control functions
     */
    public function test_control(): void
    {
        $this->assertSame(make_control('dashboard', -1), -1);
        $this->assertSame(make_control('configlog', -1), -2);
        $this->assertSame(make_control('customers', -1), -3);
        $this->assertSame(make_control('customers', 1), -4);

        // This is only to force that the index exists
        make_control('customers', 1);

        $query = 'UPDATE app_customers SET id=-1 WHERE id=1';
        db_query($query);

        $this->assertSame(make_control('customers', 1), 2);

        $query = 'UPDATE app_customers SET id=1 WHERE id=-1';
        db_query($query);

        $this->assertSame(make_control('customers', 1), 1);
        $this->assertSame(make_control('customers', 1), -4);
    }

    #[testdox('log functions')]
    /**
     * log test
     *
     * This test performs some tests to validate the correctness
     * of the log functions
     */
    public function test_log(): void
    {
        $this->assertSame(make_log('dashboard', 'utest', -1), -1);
        $this->assertSame(make_log('emails', 'utest', -1), -2);
        $this->assertSame(make_log('customers', 'utest', ''), 1);
        $this->assertSame(make_log('customers', 'utest', '1'), 1);
        $this->assertSame(make_log('customers', 'utest', '1,2,3'), 1);
        $this->assertSame(make_log('customers', 'utest', '1', ''), 1);
        $this->assertSame(make_log('customers', 'utest', '1', '1'), 1);
        $this->assertSame(make_log('customers', 'utest', '1', '1,2,3'), 1);

        $this->assertSame(make_log_bypass('customers', 'utest', ['id' => 1]), ['id' => 1]);
        $this->assertSame(make_log_bypass('customers', 'utest', [['id' => 1]]), [['id' => 1]]);

        $this->assertSame(get_logs('dashboard', -1), -1);
        $this->assertSame(get_logs('emails', -1), -2);
        $this->assertSame(get_logs('customers', -1), []);

        $this->assertSame(del_log('dashboard', -1), -1);
        $this->assertSame(del_log('emails', -1), -2);
        $this->assertSame(del_log('customers', -1), -3);
        $this->assertSame(del_log('customers', 1), 1);
    }

    #[testdox('version functions')]
    /**
     * version test
     *
     * This test performs some tests to validate the correctness
     * of the version functions
     */
    public function test_version(): void
    {
        $this->assertSame(make_version('dashboard', -1), -1);
        $this->assertSame(make_version('emails', -1), -2);
        $this->assertSame(make_version('customers', -1), -3);
        $this->assertSame(make_version('customers', 1), 1);

        $this->assertSame(get_version('dashboard', -1), -1);
        $this->assertSame(get_version('emails', -1), -2);
        $this->assertSame(get_version('customers', -1, -1), -3);
        $this->assertSame(get_version('customers', 1, -1), -3);
        $this->assertSame(get_version('customers', -1), []);

        $this->assertSame(del_version('dashboard', -1), -1);
        $this->assertSame(del_version('emails', -1), -2);
        $this->assertSame(del_version('customers', -1), -3);
        $this->assertSame(del_version('customers', 1), 1);
    }

    #[testdox('indexing functions')]
    /**
     * indexing test
     *
     * This test performs some tests to validate the correctness
     * of the indexing functions
     */
    public function test_indexing(): void
    {
        $this->assertSame(make_index('dashboard', -1), -1);
        $this->assertSame(make_index('configlog', -1), -2);
        $this->assertSame(make_index('customers', -1), -3);

        // This is only to force that the index exists
        make_index('customers', 1);

        $query = 'UPDATE app_customers SET id=-1 WHERE id=1';
        db_query($query);

        $this->assertSame(make_index('customers', 1), 3);

        $query = 'UPDATE app_customers SET id=1 WHERE id=-1';
        db_query($query);

        $this->assertSame(make_index('customers', 1), 1);
        $this->assertSame(make_index('customers', 1), 2);

        // This is for get coverage in the subtables part
        $this->assertSame(make_index('invoices', 1), 2);
    }

    #[testdox('integrity functions')]
    /**
     * integrity test
     *
     * This test performs some tests to validate the correctness of the
     * integrity function, that periodically scans every app table
     * looking for drift against its control table and repairs it via
     * make_control()
     */
    public function test_integrity(): void
    {
        // Ensure a known-synced starting point
        make_control('customers', 1);

        // Desync: the main row moves to id=-1, leaving a stale control
        // row at id=1 (main table orphan, second loop) and a main row
        // without a control row at id=-1 (control table orphan, first loop)
        $query = 'UPDATE app_customers SET id=-1 WHERE id=1';
        db_query($query);

        $total = integrity();
        $this->assertGreaterThanOrEqual(2, $total);

        // id=1's stale control row must have been removed
        $this->assertSame(make_control('customers', 1), -3);
        // id=-1's control row must have been created
        $this->assertSame(make_control('customers', -1), -4);

        // Restore: move the row back to id=1, drop the now-stale id=-1
        // control row, and recreate id=1's control row so the DB is
        // left exactly as synced as it was found
        $query = 'UPDATE app_customers SET id=1 WHERE id=-1';
        db_query($query);
        $query = 'DELETE FROM app_customers_control WHERE id=-1';
        db_query($query);
        $this->assertSame(make_control('customers', 1), 1);
        $this->assertSame(make_control('customers', 1), -4);
    }
}
