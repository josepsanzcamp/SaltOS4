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
 * Test dbstats
 *
 * This test performs some tests to validate the correctness of the dbstats
 * functions, it covers both the recording side (php/lib/dbstats.php, loaded
 * on demand by db_query() when debug/slowquerystats is enabled) and the app
 * side that exposes the recorded rows (apps/common/php/dbstats.php), since
 * both files work over the same isolated tbl_slowqueries sqlite table
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
require_once 'lib/utestlib.php';
require_once 'apps/common/php/dbstats.php';

/**
 * Main class of this unit test
 */
final class test_dbstats extends TestCase
{
    #[testdox('dbstats functions')]
    /**
     * dbstats test
     *
     * This test performs some tests to validate the correctness
     * of the dbstats functions
     */
    public function test_dbstats(): void
    {
        // Start from a clean stats file, to get deterministic ids and counters
        $file = get_directory('dirs/filesdir') . (get_config('debug/slowqueryfile') ?? 'dbstats.sqlite');
        if (file_exists($file)) {
            unlink($file);
        }

        // Without a stats file, all functions must degrade gracefully
        $this->assertSame([], __dbstats_list('', '', 0, INF));
        $this->assertFalse(__dbstats_check(1));

        // Populate the stats table using the real recording function
        set_config('debug/slowquerystats', true);
        $do_query = function () {
            db_query('SELECT * FROM tbl_users_tokens');
        };
        $do_query();
        $do_query();
        $do_query();
        set_config('debug/slowquerystats', false);

        $this->assertFileExists($file);

        // The recorded row must be visible through the app functions
        $list = __dbstats_list('', '', 0, INF);
        $this->assertCount(1, $list);
        $row = $list[0];
        $this->assertStringContainsString('closure', $row['source']);
        $this->assertSame(3, $row['count']);
        $this->assertGreaterThanOrEqual($row['min'], $row['avg']);
        $this->assertLessThanOrEqual($row['max'], $row['avg']);

        $id = $row['id'];
        $this->assertTrue(__dbstats_check($id));
        $this->assertFalse(__dbstats_check($id + 1));
        $this->assertSame($row, __dbstats_view($id));

        // Search filters: positive, negative, quoted empty term and no match
        $this->assertCount(1, __dbstats_list('closure', '', 0, INF));
        $this->assertCount(1, __dbstats_list('+closure', '', 0, INF));
        $this->assertCount(0, __dbstats_list('-closure', '', 0, INF));
        $this->assertCount(0, __dbstats_list('nomatch', '', 0, INF));
        $this->assertCount(1, __dbstats_list('""', '', 0, INF));

        // Pagination
        $this->assertCount(1, __dbstats_list('', '', 0, 25));
        $this->assertCount(0, __dbstats_list('', '', 1, 25));

        // Custom order
        $this->assertCount(1, __dbstats_list('', 'source ASC', 0, INF));

        // Not found row, unreachable through the app routing (the view action
        // already gates the id using __dbstats_check before evaluating this),
        // but kept as a safety net worth covering on its own
        test_external_exec('php/dbstats1.php', '', '');
    }
}
