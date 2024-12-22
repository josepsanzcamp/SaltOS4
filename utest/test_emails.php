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
 * Test emails
 *
 * This test performs some tests to validate the correctness
 * of the emails functions
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
require_once __ROOT__ . 'apps/emails/php/getmail.php';

/**
 * Main class of this unit test
 */
final class test_emails extends TestCase
{
    #[testdox('emails functions')]
    /**
     * emails test
     *
     * This test performs some tests to validate the correctness
     * of the emails functions
     */
    public function test_emails(): void
    {
        $json = test_cli_helper('app/emails', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/filter', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/list', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/99', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/files/99', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/body/99', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/body/99/true', [], '', '', 'admin');

        $files = glob("data/cache/*.eml");
        foreach ($files as $file) {
            unlink($file);
        }

        $decoded = __getmail_getmime(101);
        $this->assertSame($decoded, '');

        $decoded = __getmail_getmime(99);
        $this->assertIsArray($decoded);

        $decoded = __getmail_getmime(99);
        $this->assertIsArray($decoded);

        $result = __getmail_removebody(__getmail_getnode('0', $decoded));
        $this->assertIsArray($result);
        $this->assertStringContainsString('BODY REMOVED FOR DEBUG PURPOSES', sprintr($result));

        $result = __getmail_getinfo(__getmail_getnode('0', $decoded));
        $this->assertIsArray($result);

        $result = __getmail_gettextbody(__getmail_getnode('0', $decoded));
        $this->assertIsString($result);

        $result = __getmail_getfullbody(__getmail_getnode('0', $decoded));
        $this->assertIsArray($result);

        $result = __getmail_getfiles(__getmail_getnode('0', $decoded));
        $this->assertIsArray($result);
        $key = key($result);
        $hash = $result[$key]['chash'];

        $result = __getmail_getcid(__getmail_getnode('0', $decoded), $hash);
        $this->assertIsArray($result);
        $this->assertSame($result['chash'], $hash);

        set_data('server/user', 'admin');

        $result = getmail_body(99);
        $this->assertIsString($result);

        $result = getmail_body(99, true);
        $this->assertIsString($result);

        $result = getmail_source(99);
        $this->assertIsString($result);

        $result = getmail_files(99);
        $this->assertIsArray($result);

        $result = getmail_cid(99, $hash);
        $this->assertIsArray($result);

        $result = getmail_field('is_outbox', 99);
        $this->assertIsInt($result);

        $files = glob("data/cache/*.pdf");
        foreach ($files as $file) {
            unlink($file);
        }

        $result = getmail_viewpdf(99, $hash);
        $this->assertIsString($result);

        $result = getmail_download(99, $hash);
        $this->assertIsArray($result);

        set_data('rest/0', 'app');
        set_data('rest/1', 'emails');

        $result = getmail_setter('101', 'new=0');
        $this->assertSame($result, T('Permission denied'));

        $result = getmail_setter('99', 'new=1');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('99', 'new=0');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('99', 'wait=1');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('99', 'wait=0');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('99', 'spam=1');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('99', 'spam=0');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $files = glob("data/cache/*.html");
        foreach ($files as $file) {
            unlink($file);
        }

        $result = getmail_pdf(99);
        $this->assertIsArray($result);

        $result = getmail_pdf('99,100');
        $this->assertIsArray($result);

        set_data('server/user', null);
    }
}
