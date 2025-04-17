<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
require_once 'apps/emails/php/html.php';

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
        $query = 'UPDATE app_emails_accounts
            SET smtp_user = ?, smtp_pass = ?, smtp_port = ?, smtp_extra = ?
            WHERE id = ?';
        db_query($query, ['admin', 'admin', 587, 'tls', 1]);

        $json = test_cli_helper('app/emails', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/filter', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/data', [
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
        $json = test_cli_helper('app/emails/list/data', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/42', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/files/42', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/body/42', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/view/body/42/true', [], '', '', 'admin');

        $files = glob('data/cache/*.eml');
        foreach ($files as $file) {
            unlink($file);
        }

        $decoded = __getmail_getmime(-1);
        $this->assertSame($decoded, '');

        $decoded = __getmail_getmime(42);
        $this->assertIsArray($decoded);

        $decoded = __getmail_getmime(42);
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
//~ print_r($result);
//~ die();
        $this->assertIsArray($result);
        $this->assertSame($result['chash'], $hash);

        $temp = $result['body'];
        $cid = $result['cid'];
        $cname = $result['cname'];
        $ctype = $result['ctype'];
        $csize = $result['csize'];
        $hashes = [
            md5(md5($temp) . md5($cid) . md5($cname) . md5($ctype) . md5(strval($csize))),
            md5(serialize([$temp, $cid, $cname, $ctype, $csize])),
            md5(serialize([md5($temp), $cid, $cname, $ctype, $csize])),
            md5(serialize([md5($temp), null, $cname, $ctype, $csize])),
            md5(json_encode([md5($temp), $cid, $cname, $ctype, $csize])),
        ];
        foreach ($hashes as $hash) {
            $result2 = __getmail_getcid(__getmail_getnode('0', $decoded), $hash);
            $this->assertIsArray($result2);
            $this->assertSame($result2['chash'], $result['chash']);
        }

        set_data('server/user', 'admin');

        $result = getmail_body(42);
        $this->assertIsString($result);

        $result = getmail_body(42, true);
        $this->assertIsString($result);

        $result = getmail_source(42);
        $this->assertIsString($result);

        $result = getmail_files(42);
        $this->assertIsArray($result);

        $result = getmail_cid(42, $hash);
        $this->assertIsArray($result);

        $result = getmail_field('is_outbox', 42);
        $this->assertIsInt($result);

        $files = glob('data/cache/*.pdf');
        foreach ($files as $file) {
            unlink($file);
        }

        $result = getmail_viewpdf(42, $hash);
        $this->assertIsString($result);

        // This trick is for execute the __pdf_all2pdf inside getmail_viewpdf
        $cache1 = get_cache_file([42, $hash], 'jpg');
        unlink($cache1);
        $output = get_cache_file($cache1, '.pdf');
        file_put_contents($output, '');
        $cache2 = get_cache_file([42, $hash], 'pdf');
        unlink($cache2);
        $result = getmail_viewpdf(42, $hash);
        $this->assertIsString($result);
        unlink($cache1);
        unlink($output);
        unlink($cache2);

        $result = getmail_download(42, $hash);
        $this->assertIsArray($result);

        set_data('rest/0', 'app');
        set_data('rest/1', 'emails');

        $maxid = execute_query('SELECT MAX(id) FROM app_emails');
        $result = getmail_setter($maxid + 1, 'new=0');
        $this->assertSame($result, T('Permission denied'));

        $result = getmail_setter('42', 'new=1');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('42', 'new=0');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('42', 'wait=1');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('42', 'wait=0');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('42', 'spam=1');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $result = getmail_setter('42', 'spam=0');
        $this->assertSame($result, sprintf(T('%d email(s) modified successfully'), 1));

        $files = glob('data/cache/*.html');
        foreach ($files as $file) {
            unlink($file);
        }

        $result = getmail_pdf(42);
        $this->assertIsArray($result);

        $result = getmail_pdf(42);
        $this->assertIsArray($result);

        $result = getmail_pdf('42,43');
        $this->assertIsArray($result);

        $cache = get_cache_file('which wkhtmltopdf', '.out');
        $this->assertNotFalse(file_put_contents($cache, ''));
        $result = getmail_pdf('42,43');
        $this->assertIsArray($result);
        $this->assertTrue(unlink($cache));

        $query = 'UPDATE app_emails_files SET indexed=0, retries = 0';
        db_query($query);

        $query = 'SELECT COUNT(*) FROM app_emails_files';
        $total = execute_query($query);

        $this->assertFileDoesNotExist('data/logs/phperror.log');
        $json = test_web_helper('app/emails/action/indexing', [], '', '');
        $this->assertFileExists('data/logs/phperror.log');
        unlink('data/logs/phperror.log');

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
            'reg_id' => 50,
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
        db_query($query, [50, 'nada']);

        set_data('server/lang', 'en');

        if (file_exists('data/outbox/1')) {
            rmdir('data/outbox/1');
        }
        $this->assertDirectoryDoesNotExist('data/outbox/1');

        $result = sendmail(-1, '', '', '');
        $this->assertStringContainsString('Id not found', $result);

        $query = 'UPDATE app_emails_accounts SET email_from = ? WHERE id = ?';
        db_query($query, ['nada', 1]);

        $result = sendmail(1, '', '', '');
        $this->assertStringContainsString('Invalid address', $result);

        $query = 'UPDATE app_emails_accounts SET email_from = ? WHERE id = ?';
        db_query($query, ['admin@example.com', 1]);

        $query = 'UPDATE app_emails_accounts SET email_disabled = ? WHERE id = ?';
        db_query($query, [1, 1]);

        $result = sendmail(1, '', '', '');
        $this->assertStringContainsString('Email disabled', $result);

        $query = 'UPDATE app_emails_accounts SET email_disabled = ? WHERE id = ?';
        db_query($query, [0, 1]);

        set_data('server/lang', 'nada');

        $result = sendmail(1, '', '', '');
        $this->assertStringContainsString('Lang nada not found', $result);

        set_data('server/lang', 'en');

        $result = sendmail(1, '', '', '');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, 'admin@example.com', '', '');
        $this->assertStringContainsString('Message body empty', $result);

        $result = sendmail(1, ['to:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['cc:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['bcc:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['to:admin@example.com', 'crt:1'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['to:admin@example.com', 'priority:1'], '', 'nada');
        $this->assertIsInt($result);

        $result = sendmail(1, ['to:admin@example.com', 'sensitivity:Personal'], '', 'nada');
        $this->assertIsInt($result);

        $result = sendmail(1, ['to:admin@example.com', 'replyto:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, [
            'to:Admin <admin@example.com>',
            'cc:admin@example.com',
            'bcc:admin@example.com',
            'crt:admin@example.com',
            'priority:1',
            'sensitivity:1',
            'replyto:admin@example.com',
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
        $this->assertIsInt($result);

        $result = sendmail(1, 'admin@example.com', 'nada', 'nada', [], false);
        $this->assertStringContainsString('Connection refused', $result);

        set_server('QUERY_STRING', 'app/emails/create');

        $result = sendmail_prepare('', '');
        $this->assertIsArray($result);

        $result = sendmail_prepare('reply', 50);
        $this->assertIsArray($result);

        $result = sendmail_prepare('replyall', 50);
        $this->assertIsArray($result);

        $result = sendmail_prepare('forward', 50);
        $this->assertIsArray($result);

        // forward add an upload file that must to be removed
        $id = execute_query('SELECT MAX(id) FROM tbl_uploads');
        $query = 'SELECT uniqid id, app, name, size, type, file, hash
            FROM tbl_uploads WHERE id = ?';
        $file = execute_query($query, [$id]);
        $result = del_upload_file($file);
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
        $file1['file'] = execute_query('SELECT file FROM tbl_uploads WHERE uniqid = ?', [$id]);
        $file1['hash'] = md5_file($file);
        $this->assertSame($json, $file1);

        $result = sendmail_action([
            'from' => 1,
            'to' => 'admin@example.com',
            'subject' => 'test email',
            'body' => '<p>hello world</p><img src="' . __GIF_IMAGE__ . '"/>',
            'files' => [$file1],
        ], '', '');
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'admin@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'reply', 50);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'admin@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'replyall', 50);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'admin@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'forward', 50);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = test_cli_helper('app/emails/action/server', [], '', '', 'admin');
        $this->assertIsArray($result);
        $this->assertStringContainsString('email(s) received', $result[0]);
        $this->assertStringContainsString('Connection refused', $result[1]);
        $this->assertStringContainsString('email(s) sended', $result[count($result) - 1]);

        $query = 'UPDATE app_emails_accounts SET smtp_port = ?, smtp_extra = ? WHERE id = ?';
        db_query($query, [25, '', 1]);

        $result = test_cli_helper('app/emails/action/server', [], '', '', 'admin');
        $this->assertIsArray($result);
        $this->assertStringContainsString('email(s) received', $result[0]);
        $this->assertStringContainsString('Could not authenticate', $result[1]);
        $this->assertStringContainsString('email(s) sended', $result[count($result) - 1]);

        // This trick is for execute the internal error part
        $files = glob('data/outbox/1/*.obj');
        unlink($files[1]);
        file_put_contents($files[2], '');

        $query = 'UPDATE app_emails_accounts SET smtp_user = ?, smtp_pass = ? WHERE id = ?';
        db_query($query, ['', '', 1]);

        $result = test_cli_helper('app/emails/action/server', [], '', '', 'admin');
        $this->assertIsArray($result);
        $this->assertStringContainsString('email(s) received', $result[0]);
        $this->assertStringContainsString('internal error', $result[1]);
        $this->assertStringContainsString('internal error', $result[2]);
        $this->assertStringContainsString('email(s) sended', $result[3]);

        $result = test_cli_helper('app/emails/action/server', [], '', '', 'admin');
        $this->assertIsArray($result);
        $this->assertStringContainsString('email(s) received', $result[0]);
        $this->assertStringContainsString('email(s) sended', $result[1]);

        $query = 'UPDATE app_emails_accounts
            SET smtp_user = ?, smtp_pass = ?, smtp_port = ?, smtp_extra = ?
            WHERE id = ?';
        db_query($query, ['admin', 'admin', 587, 'tls', 1]);

        $query = 'UPDATE app_emails_accounts SET email_addmetocc = ? WHERE id = ?';
        db_query($query, [1, 1]);

        $result = sendmail_signature([
            'old' => 1,
            'new' => 1,
            'body' => '<section></section>',
            'cc' => 'admin@example.com;',
        ]);
        $this->assertIsArray($result);
        $this->assertStringContainsString('<section>', sprintr($result));
        $this->assertStringContainsString('</section>', sprintr($result));

        $query = 'UPDATE app_emails_accounts SET email_addmetocc = ? WHERE id = ?';
        db_query($query, [0, 1]);

        copy('data/inbox/1/email_0050.eml.gz', 'data/inbox/1/email_0101.eml.gz');
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

        $result = getmail_delete($ids);
        $this->assertSame(T('Permission denied'), $result);

        set_data('server/user', null);
        set_data('server/lang', null);
        set_server('QUERY_STRING', null);

        $this->assertSame(__getmail_getsource(101), '');
        $expected = gzfilesize('data/inbox/1/email_0050.eml.gz');
        $actual = strlen(__getmail_getsource(50));
        $this->assertLessThan($expected, $actual);
        $this->assertSame(__getmail_fixstring([1, 2, 3]), 1);
        $this->assertSame(gzfilesize('../../utest/files/lorem.txt'), 751);

        $query = 'SELECT account_id,uidl,is_outbox,datetime,size FROM app_emails WHERE id = ?';
        $row = execute_query($query, [48]);
        $cache = get_cache_file($row, '.eml');
        file_put_contents($cache, '');

        test_external_exec('php/emails01.php', 'phperror.log', 'permission denied');
        test_external_exec('php/emails02.php', 'phperror.log', 'could not decode de message');
        test_external_exec('php/emails03.php', 'phperror.log', 'permission denied');
        test_external_exec('php/emails04.php', 'phperror.log', 'permission denied');
        test_external_exec('php/emails05.php', 'phperror.log', 'could not decode de message');
        test_external_exec('php/emails06.php', 'phperror.log', 'permission denied');
        test_external_exec('php/emails07.php', 'phperror.log', 'could not decode de message');
        test_external_exec('php/emails08.php', 'phperror.log', 'cid not found in message');
        test_external_exec('php/emails09.php', 'phperror.log', 'permission denied');
        test_external_exec('php/emails10.php', 'phperror.log', 'permission denied');
        test_external_exec('php/emails11.php', 'phperror.log', 'could not decode de message');

        unlink($cache);
    }
}
