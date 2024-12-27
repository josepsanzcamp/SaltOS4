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
require_once 'apps/emails/php/getmail.php';
require_once 'apps/emails/php/sendmail.php';
require_once 'php/lib/html.php';

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
        $json = test_cli_helper('app/emails/list/list', [
            'account_id' => 1,
            'fields' => 'body',
            'onlynew' => 1,
            'onlywait' => 1,
            'onlyspam' => 1,
            'hidespam' => 1,
            'withfiles' => 1,
            'withoutfiles' => 1,
            'onlyinbox' => 1,
            'onlyoutbox' => 1,
            'date1' => current_date(),
            'date2' => current_date(),
            'date3' => 'today',
        ], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/list', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/99', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/files/99', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/body/99', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/body/99/true', [], '', '', 'admin');

        $files = glob('data/cache/*.eml');
        foreach ($files as $file) {
            unlink($file);
        }

        $decoded = __getmail_getmime(-1);
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

        $files = glob('data/cache/*.pdf');
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

        $files = glob('data/cache/*.html');
        foreach ($files as $file) {
            unlink($file);
        }

        $result = getmail_pdf(99);
        $this->assertIsArray($result);

        $result = getmail_pdf('99,100');
        $this->assertIsArray($result);

        $query = 'UPDATE app_emails_files SET indexed=0, retries = 0';
        db_query($query);

        $query = 'SELECT COUNT(*) FROM app_emails_files';
        $total = execute_query($query);

        $json = test_cli_helper('app/emails/action/indexing', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame($json['indexing']['total'], $total);

        $this->assertFileDoesNotExist('data/logs/warning.log');

        // !$decoded case
        $query = prepare_insert_query('app_emails_files', [
            'user_id' => current_user(),
            'datetime' => current_datetime(),
            'reg_id' => -1,
            'uniqid' => 'nada',
            'hash' => md5('nada'),
        ]);
        db_query(...$query);

        $json = test_cli_helper('app/emails/action/indexing', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame($json['indexing']['total'], 0);

        $this->assertFileExists('data/logs/warning.log');
        unlink('data/logs/warning.log');

        $this->assertFileDoesNotExist('data/logs/warning.log');

        // !$file case
        $query = prepare_update_query('app_emails_files', [
            'reg_id' => 100,
            'retries' => 0,
        ], [
            'uniqid' => 'nada',
        ]);
        db_query(...$query);

        $json = test_cli_helper('app/emails/action/indexing', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame($json['indexing']['total'], 0);

        $this->assertFileExists('data/logs/warning.log');
        unlink('data/logs/warning.log');

        $query = 'DELETE FROM app_emails_files WHERE reg_id = ? AND uniqid = ?';
        db_query($query, [100, 'nada']);

        set_data('server/lang', 'en');

        $result = sendmail(-1, '', '', '');
        $this->assertStringContainsString('Id not found', $result);

        $result = sendmail(1, '', '', '');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, 'test@example.com', '', '');
        $this->assertStringContainsString('Message body empty', $result);

        $result = sendmail(1, ['to:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['cc:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['bcc:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['to:test@example.com', 'crt:1'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['to:test@example.com', 'priority:100'], '', 'nada');
        $this->assertSame($result, '');

        $result = sendmail(1, ['to:test@example.com', 'sensitivity:100'], '', 'nada');
        $this->assertSame($result, '');

        $result = sendmail(1, ['to:test@example.com', 'replyto:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, [
            'to:test@example.com',
            'cc:test@example.com',
            'bcc:test@example.com',
            'crt:test@example.com',
            'priority:1',
            'sensitivity:1',
            'replyto:test@example.com',
        ], 'test email', 'body for the test email', [
            [
                'data' => 'hola mundo',
                'name' => 'file1.txt',
                'mime' => 'text/plain',
            ], [
                'file' => '../../utest/files/lorem.txt',
                'name' => 'lorem.txt',
                'mime' => 'text/plain',
            ], [
                'data' => 'hola mundo',
                'name' => 'file1.txt',
                'mime' => 'text/plain',
                'cid' => 'file1.txt',
            ], [
                'file' => '../../utest/files/lorem.txt',
                'name' => 'lorem.txt',
                'mime' => 'text/plain',
                'cid' => 'lorem.txt',
            ],
        ]);
        $this->assertSame($result, '');

        $result = sendmail(1, 'test@example.com', 'nada', 'nada', [], false);
        $this->assertStringContainsString('Connection refused', $result);

        set_server('QUERY_STRING', 'app/emails/create');

        $result = sendmail_prepare('', '');
        $this->assertIsArray($result);

        $result = sendmail_prepare('reply', 100);
        $this->assertIsArray($result);

        $result = sendmail_prepare('replyall', 100);
        $this->assertIsArray($result);

        $result = sendmail_prepare('forward', 100);
        $this->assertIsArray($result);

        // forward add an upload file that must to be removed
        $id = execute_query('SELECT MAX(id) FROM tbl_uploads');
        $query = 'SELECT uniqid id, app, name, size, type, file, hash
            FROM tbl_uploads WHERE id = ?';
        $file = execute_query($query, [$id]);
        $result = del_file($file);
        $file['file'] = '';
        $file['hash'] = '';
        $this->assertSame($result, $file);

        $result = sendmail_action([
            'from' => 1,
        ], '', '');
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ko');

        // Add a file to the tbl_uploads
        $id = get_unique_id_md5();
        $file = '../../utest/files/lorem.html';
        $app = 'app/emails/create';
        $name = basename($file);
        $size = filesize($file);
        $type = saltos_content_type($file);
        $data = mime_inline($type, file_get_contents($file));
        $file1 = [
            'id' => $id,
            'app' => $app,
            'name' => $name,
            'size' => $size,
            'type' => $type,
            'data' => $data,
            'error' => '',
            'file' => '',
            'hash' => '',
        ];
        $json = test_cli_helper('upload/addfile', $file1, '', '', 'admin');
        $file1['data'] = '';
        $file1['file'] = execute_query("SELECT file FROM tbl_uploads WHERE uniqid='$id'");
        $file1['hash'] = md5_file($file);
        $this->assertSame($json, $file1);

        $result = sendmail_action([
            'from' => 1,
            'to' => 'test@example.com',
            'subject' => 'test email',
            'body' => '<p>hello world</p><img src="' . __GIF_IMAGE__ . '"/>',
            'files' => [$file1],
        ], '', '');
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'test@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'reply', 100);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'test@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'replyall', 100);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'test@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'forward', 100);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = test_cli_helper('app/emails/action/server', [], '', '', 'admin');
        $this->assertIsArray($result);
        $this->assertStringContainsString('Connection refused', $result[0]);

        $query = 'UPDATE app_emails_accounts SET email_addmetocc = ? WHERE id = ?';
        db_query($query, [1, 1]);

        $result = sendmail_signature([
            'old' => 1,
            'new' => 1,
            'body' => '<section></section>',
            'cc' => 'test@example.com;',
        ]);
        $this->assertIsArray($result);
        $this->assertStringContainsString('<section>', sprintr($result));
        $this->assertStringContainsString('</section>', sprintr($result));

        $query = 'UPDATE app_emails_accounts SET email_addmetocc = ? WHERE id = ?';
        db_query($query, [0, 1]);

        copy('data/inbox/1/email_0100.eml.gz', 'data/inbox/1/email_0101.eml.gz');
        $file = get_temp_file('json');
        file_put_contents($file, json_encode([
            'getmailmsgid' => '1/email_0101',
        ]));

        test_pcov_start();
        $json = ob_passthru("cat $file | user=admin php index.php app/emails/server");
        test_pcov_stop(1);
        $this->assertIsString($json);
        $json = json_decode($json, true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('emails', $json);
        $this->assertArrayHasKey('getmailmsgid', $json['emails']);
        $this->assertArrayHasKey('file', $json['emails']);
        $this->assertArrayHasKey('last_id', $json['emails']);
        $this->assertSame($json['emails']['getmailmsgid'], '1/email_0101');

        $query = 'SELECT id FROM app_emails WHERE id > 100';
        $ids = execute_query_array($query);

        $result = getmail_delete($ids);
        $this->assertSame(sprintf(T('%d email(s) deleted'), count($ids)), $result);

        set_data('server/user', null);
        set_data('server/lang', null);
        set_server('QUERY_STRING', null);
    }
}
