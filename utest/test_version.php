<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
// phpcs:disable PSR1.Files.SideEffects

/**
 * Test version
 *
 * This test performs some tests to validate the correctness
 * of the version related functions
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
final class test_version extends TestCase
{
    #[testdox('version functions')]
    /**
     * version test
     *
     * This function performs some tests to validate the correctness
     * of the version related functions
     */
    public function test_version(): void
    {
        $this->assertStringContainsString('SaltOS', get_name_version_revision(false));
        $this->assertStringContainsString('Copyright', get_name_version_revision(true));

        $this->assertSame(svnversion() > 0, true);
        file_put_contents('/tmp/svnversion', '123');
        $this->assertSame(svnversion('/tmp/'), 123);
        unlink('/tmp/svnversion');

        $old = get_config('commands/svnversion');
        set_config('commands/svnversion', 'nada');
        $this->assertSame(svnversion('/tmp/'), 0);
        set_config('commands/svnversion', $old);

        $old = get_config('commands/__gitversion__');
        set_config('commands/__gitversion__', 'ls');
        $this->assertSame(gitversion(), 0);
        set_config('commands/__gitversion__', $old);

        file_put_contents('/tmp/gitversion', '123');
        $this->assertSame(gitversion('/tmp/'), 123);
        unlink('/tmp/gitversion');

        $old = get_config('commands/gitversion');
        set_config('commands/gitversion', 'nada');
        $this->assertSame(gitversion('/tmp/'), 0);
        set_config('commands/gitversion', $old);

        $this->assertSame(isphp(0), true);
        $this->assertSame(isphp(99), false);
    }
}
