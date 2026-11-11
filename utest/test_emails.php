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
require_once 'apps/emails/php/folders.php';
require_once 'apps/emails/php/socket.php';
require_once 'apps/emails/php/imap.php';
require_once 'apps/emails/php/accounts.php';
require_once 'apps/emails/php/pop3.php';
require_once 'apps/emails/php/getmail_imap.php';
require_once 'php/lib/actions.php';
require_once 'php/lib/control.php';

/**
 * Main class of this unit test
 */
final class test_emails extends TestCase
{
    /**
     * IMAP cleanup
     *
     * This function removes the IMAP accounts left by a previous execution of the IMAP
     * tests that not finished, with its emails and its operations, to not disturb the
     * other tests; it is called at the beginning of the first test and of the IMAP tests
     */
    private function imap_cleanup(): void
    {
        $user = get_data('server/user');
        set_data('server/user', 'admin');
        $query = "SELECT id FROM app_emails_accounts WHERE protocol = 'imap' AND email_from = ?";
        foreach (execute_query_array($query, ['test@example.com']) as $id) {
            $ids = execute_query_array('SELECT id FROM app_emails WHERE account_id = ?', [$id]);
            if (count($ids)) {
                __getmail_delete(implode(',', $ids));
            }
            db_query('DELETE FROM app_emails_queue WHERE account_id = ?', [$id]);
            $this->assertSame('ok', delete('emails_accounts', $id)['status']);
        }
        set_data('server/user', $user);
    }

    /**
     * Mock server start
     *
     * This function starts the scripted mock server (utest/php/scripted.php) as a child
     * process, used to reproduce the responses that the real servers never send, and
     * returns an array with the process, the pipes and the port used by the server
     *
     * @script => the script of the mock server
     */
    private function mock_start(array $script): array
    {
        $pipes = [];
        $cmd = ['php', '../../utest/php/scripted.php', json_encode($script)];
        $proc = proc_open($cmd, [1 => ['pipe', 'w']], $pipes);
        $port = intval(fgets($pipes[1]));
        return [$proc, $pipes, $port];
    }

    /**
     * Mock server stop
     *
     * This function waits the end of the mock server and releases its resources
     *
     * @server => the array returned by mock_start
     */
    private function mock_stop(array $server): void
    {
        fclose($server[1][1]);
        proc_close($server[0]);
    }

    #[testdox('emails functions')]
    /**
     * emails test
     *
     * This test performs some tests to validate the correctness
     * of the emails functions
     */
    public function test_emails(): void
    {
        $this->imap_cleanup();

        // Wrong settings, ssl on the plain ports of GreenMail, used to check the errors
        $query = 'UPDATE app_emails_accounts
            SET pop3_port = ?, pop3_extra = ?, smtp_user = ?, smtp_pass = ?, smtp_port = ?, smtp_extra = ?
            WHERE id = ?';
        db_query($query, [110, 'ssl', 'pop3', 'admin', 587, 'ssl', 1]);

        $json = test_cli_helper('app/emails', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/filter', [], '', '', 'admin');
        $json = test_cli_helper('app/emails/list/data', [
            'account_id' => 1,
            'fields' => 'body',
            'onlynew' => 1,
            'onlywait' => 1,
            'folder_id' => 1,
            'withfiles' => 1,
            'withoutfiles' => 1,
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

        $cache = get_cache_file('which chromium', '.out');
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
        db_query($query, ['pop3@example.com', 1]);

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

        $result = sendmail(1, 'pop3@example.com', '', '');
        $this->assertStringContainsString('Message body empty', $result);

        $result = sendmail(1, ['to:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['cc:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['bcc:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['to:pop3@example.com', 'crt:1'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, ['to:pop3@example.com', 'priority:1'], '', 'nada');
        $this->assertIsInt($result);

        $result = sendmail(1, ['to:pop3@example.com', 'sensitivity:Personal'], '', 'nada');
        $this->assertIsInt($result);

        $result = sendmail(1, ['to:pop3@example.com', 'replyto:'], '', 'nada');
        $this->assertStringContainsString('Invalid address', $result);

        $result = sendmail(1, [
            'to:Admin <pop3@example.com>',
            'cc:pop3@example.com',
            'bcc:pop3@example.com',
            'crt:pop3@example.com',
            'priority:1',
            'sensitivity:1',
            'replyto:pop3@example.com',
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

        $result = sendmail(1, 'pop3@example.com', 'nada', 'nada', [], false);
        $this->assertStringContainsString('Failed to connect to server', $result);

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
            'to' => 'pop3@example.com',
            'subject' => 'test email',
            'body' => '<p>hello world</p><img src="' . __GIF_IMAGE__ . '"/>',
            'files' => [$file1],
        ], '', '');
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'pop3@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'reply', 50);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'pop3@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'replyall', 50);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = sendmail_action([
            'from' => 1,
            'to' => 'pop3@example.com',
            'subject' => 'test email',
            'body' => 'hello world',
        ], 'forward', 50);
        $this->assertIsArray($result);
        $this->assertSame($result['status'], 'ok');

        $result = test_cli_helper('app/emails/action/server', [], '', '', 'admin');
        $this->assertIsArray($result);
        $this->assertStringContainsString('Connect failed', $result[0]);
        $this->assertStringContainsString('email(s) received', $result[1]);
        $this->assertStringContainsString('Failed to connect to server', $result[2]);
        $this->assertStringContainsString('email(s) sended', $result[count($result) - 1]);

        // Right settings, ssl on the ssl ports of GreenMail
        $query = 'UPDATE app_emails_accounts
            SET pop3_port = ?, pop3_extra = ?, smtp_port = ?, smtp_extra = ?
            WHERE id = ?';
        db_query($query, [995, 'ssl', 465, 'ssl', 1]);

        // This trick is for execute the internal error part
        $files = glob('data/outbox/1/*.obj');
        unlink($files[1]);
        file_put_contents($files[2], '');

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

        $query = 'UPDATE app_emails_accounts SET pop3_extra = ?, smtp_extra = ? WHERE id = ?';
        db_query($query, ['ssl', 'ssl', 1]);

        $query = 'UPDATE app_emails_accounts SET email_addmetocc = ? WHERE id = ?';
        db_query($query, [1, 1]);

        $result = sendmail_signature([
            'old' => 1,
            'new' => 1,
            'body' => '<section></section>',
            'cc' => 'pop3@example.com;',
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

        // Only the emails of the account used by this test, to not touch other accounts
        $query = 'SELECT id FROM app_emails WHERE id > 100 AND account_id = 1';
        $ids = execute_query_array($query);

        $result = getmail_delete($ids);
        $this->assertSame(sprintf(T('%d email(s) moved to trash'), count($ids)), $result);

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

        $mime = new mime_parser_class();
        $decoded = [];
        $raw = "From: a@example.com\r\nTo: b@example.com\r\nSubject: test\r\n\r\nHello world\r\n";
        $this->assertTrue($mime->Decode(['Data' => $raw], $decoded));
        $this->assertNotEmpty($decoded);

        $decoded = [];
        $raw = "From: a@example.com\r\nTo: b@example.com\r\nSubject: test\r\nMIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary=\"XYZ\"\r\n\r\n" .
            "--XYZ\r\nContent-Type: text/plain\r\n\r\nbody\r\n" .
            "--XYZ\r\nContent-Type: application/octet-stream; name=\"file.bin\"\r\n\r\nsomedata\r\n" .
            "--XYZ--\r\n";
        $mime->Decode(['Data' => $raw], $decoded);
        $this->assertSame('file.bin', $decoded[0]['Parts'][1]['FileName']);

        $decoded = [];
        set_error_handler(fn() => true);
        try {
            $mime->Decode(['File' => 'data/temp/does_not_exist_' . uniqid()], $decoded);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('No se pudo leer', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    #[Depends('test_emails')]
    #[testdox('folders functions')]
    /**
     * folders test
     *
     * This test performs some tests to validate the correctness
     * of the emails folders functions
     */
    public function test_folders(): void
    {
        set_data('server/user', 'admin');
        set_data('rest/0', 'app');
        set_data('rest/1', 'emails');

        // The xml endpoints
        $json = test_cli_helper('app/emails/list/folders', [], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);
        $json = test_cli_helper('app/emails/action/folder_empty_summary', [
            'folder' => 'trash',
        ], '', '', 'admin');
        $this->assertSame(['status' => 'ko', 'text' => T('Permission denied')], $json);
        $json = test_cli_helper('app/emails/action/folder_summary', ['folder_id' => -1], '', '', 'admin');
        $this->assertSame(['status' => 'ko', 'text' => T('Permission denied')], $json);
        $json = test_cli_helper('app/emails/action/folder', ['action' => 'unknown'], '', '', 'admin');
        $this->assertIsArray($json);
        $this->assertSame(T('Unknown action'), $json['text']);

        // The mandatory folders
        $ids = array_map('intval', emails_folders_check(1));
        $this->assertSame(['\Inbox', '\Sent', '\Trash', '\Junk'], array_keys($ids));
        $this->assertSame($ids, array_map('intval', emails_folders_check(1)));
        $maxid = execute_query('SELECT MAX(id) FROM app_emails_accounts');
        $this->assertSame([], emails_folders_check($maxid + 1));

        // The tree
        $tree = emails_folders_tree();
        $this->assertIsArray($tree);
        $node = array_values(array_filter($tree, fn($x) => $x['id'] === '1/0'))[0];
        $this->assertSame('1/' . $ids['\Inbox'], $node['children'][0]['id']);
        $this->assertSame(T('Inbox'), $node['children'][0]['text']);
        $this->assertSame(['opened' => true], $node['children'][0]['state']);
        $disabled = intval(execute_query('SELECT email_disabled FROM app_emails_accounts WHERE id = 1'));
        $this->assertSame(['opened' => !$disabled], $node['state']);
        $this->assertCount(1, emails_folders_tree(42));
        $maxid = execute_query('SELECT MAX(id) FROM app_emails');
        $this->assertSame([], emails_folders_tree($maxid + 1));

        // Remove the folders of previous executions that not finished
        $query = "SELECT id FROM app_emails_folders WHERE account_id = 1 AND parent_id = 0
            AND label IN ('Test', 'Test2', 'Child', 'Other')";
        foreach (execute_query_array($query) as $id) {
            emails_folders_action(['action' => 'delete', 'folder_id' => $id]);
        }

        // Create
        $this->assertSame(T('Unknown action'), emails_folders_action(['action' => 'unknown']));
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'create', 'account_id' => 0, 'parent_id' => 0, 'label' => 'Test',
        ]));
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'create', 'account_id' => 1, 'parent_id' => -1, 'label' => 'Test',
        ]));
        $this->assertSame(T('The name of the folder is required'), emails_folders_action([
            'action' => 'create', 'account_id' => 1, 'parent_id' => 0, 'label' => ' ',
        ]));
        $this->assertSame(T('Folder created'), emails_folders_action([
            'action' => 'create', 'account_id' => 1, 'parent_id' => 0, 'label' => 'Test',
        ]));
        $this->assertSame(T('A folder with the same name already exists'), emails_folders_action([
            'action' => 'create', 'account_id' => 1, 'parent_id' => 0, 'label' => 'Test',
        ]));
        $query = "SELECT id FROM app_emails_folders
            WHERE account_id = 1 AND parent_id = 0 AND label = 'Test'";
        $test_id = intval(execute_query($query));
        $this->assertSame(T('Folder created'), emails_folders_action([
            'action' => 'create', 'account_id' => 1, 'parent_id' => $test_id, 'label' => 'Child',
        ]));
        $query = "SELECT id FROM app_emails_folders
            WHERE account_id = 1 AND parent_id = ? AND label = 'Child'";
        $child_id = intval(execute_query($query, [$test_id]));

        // Rename
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'rename', 'folder_id' => -1, 'label' => 'Test2',
        ]));
        $this->assertSame(T('The special folders can not be modified'), emails_folders_action([
            'action' => 'rename', 'folder_id' => $ids['\Inbox'], 'label' => 'Test2',
        ]));
        $this->assertSame(T('The name of the folder is required'), emails_folders_action([
            'action' => 'rename', 'folder_id' => $test_id, 'label' => '',
        ]));
        $this->assertSame(T('Folder created'), emails_folders_action([
            'action' => 'create', 'account_id' => 1, 'parent_id' => 0, 'label' => 'Other',
        ]));
        $this->assertSame(T('A folder with the same name already exists'), emails_folders_action([
            'action' => 'rename', 'folder_id' => $test_id, 'label' => 'Other',
        ]));
        $this->assertSame(T('Folder renamed'), emails_folders_action([
            'action' => 'rename', 'folder_id' => $test_id, 'label' => 'Test2',
        ]));

        // Move
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'move', 'folder_id' => -1, 'parent_id' => 0,
        ]));
        $this->assertSame(T('The special folders can not be modified'), emails_folders_action([
            'action' => 'move', 'folder_id' => $ids['\Inbox'], 'parent_id' => $test_id,
        ]));
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'move', 'folder_id' => $test_id, 'parent_id' => -1,
        ]));
        $this->assertSame(T('A folder can not be moved inside itself'), emails_folders_action([
            'action' => 'move', 'folder_id' => $test_id, 'parent_id' => $child_id,
        ]));
        $this->assertSame(T('Folder renamed'), emails_folders_action([
            'action' => 'rename', 'folder_id' => $child_id, 'label' => 'Other',
        ]));
        $this->assertSame(T('A folder with the same name already exists'), emails_folders_action([
            'action' => 'move', 'folder_id' => $child_id, 'parent_id' => 0,
        ]));
        $this->assertSame(T('Folder renamed'), emails_folders_action([
            'action' => 'rename', 'folder_id' => $child_id, 'label' => 'Child',
        ]));
        $this->assertSame(T('Folder moved'), emails_folders_action([
            'action' => 'move', 'folder_id' => $child_id, 'parent_id' => 0,
        ]));
        $this->assertSame(T('Folder moved'), emails_folders_action([
            'action' => 'move', 'folder_id' => $child_id, 'parent_id' => $test_id,
        ]));

        // Move emails
        $folder_id = intval(execute_query('SELECT folder_id FROM app_emails WHERE id = 42'));
        $maxid = execute_query('SELECT MAX(id) FROM app_emails');
        $this->assertSame(T('Permission denied'), emails_folders_move_to($maxid + 1, $child_id));
        $this->assertSame(T('Permission denied'), emails_folders_move_to(42, -1));
        $this->assertSame(sprintf(T('%d email(s) moved'), 1), emails_folders_move_to(42, $child_id));
        $this->assertSame($child_id, intval(execute_query('SELECT folder_id FROM app_emails WHERE id = 42')));

        // Delete
        $this->assertSame(
            ['status' => 'ko', 'text' => T('Permission denied')],
            emails_folders_summary(['folder_id' => -1])
        );
        $this->assertSame(
            ['status' => 'ko', 'text' => T('The special folders can not be modified')],
            emails_folders_summary(['folder_id' => $ids['\Inbox']])
        );
        $text = sprintf(T('The folder and its %d subfolder(s) will be deleted ' .
            'and its %d email(s) moved to the trash'), 1, 1);
        $result = emails_folders_summary(['folder_id' => $test_id]);
        $this->assertSame(['status' => 'ok', 'text' => $text], $result);
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'delete', 'folder_id' => -1,
        ]));
        $this->assertSame(T('The special folders can not be modified'), emails_folders_action([
            'action' => 'delete', 'folder_id' => $ids['\Inbox'],
        ]));
        $this->assertSame(T('Folder deleted'), emails_folders_action([
            'action' => 'delete', 'folder_id' => $test_id,
        ]));
        $query = 'SELECT folder_id FROM app_emails WHERE id = 42';
        $this->assertSame($ids['\Trash'], intval(execute_query($query)));
        $query = 'SELECT COUNT(*) FROM app_emails_folders WHERE id IN (?, ?)';
        $this->assertSame(0, intval(execute_query($query, [$test_id, $child_id])));
        $query = "SELECT id FROM app_emails_folders
            WHERE account_id = 1 AND parent_id = 0 AND label = 'Other'";
        $this->assertSame(T('Folder deleted'), emails_folders_action([
            'action' => 'delete', 'folder_id' => intval(execute_query($query)),
        ]));

        // Restore the email used in the test
        $this->assertSame(sprintf(T('%d email(s) moved'), 1), emails_folders_move_to(42, $folder_id));

        // Empty the trash and the junk of a new account
        $result = emails_accounts_insert([
            'user_id' => current_user(),
            'email_name' => 'Test',
            'email_from' => 'test@example.com',
        ]);
        $this->assertSame('ok', $result['status']);
        $account_id = intval($result['created_id']);
        $ids = array_map('intval', emails_folders_check($account_id));
        $this->assertCount(4, $ids);
        $this->assertSame(
            ['status' => 'ko', 'text' => T('Permission denied')],
            emails_folders_empty_summary(['account_id' => 0, 'folder' => 'trash'])
        );
        $this->assertSame(
            ['status' => 'ko', 'text' => T('Unknown action')],
            emails_folders_empty_summary(['account_id' => $account_id, 'folder' => 'inbox'])
        );
        $this->assertSame(
            ['status' => 'ko', 'text' => T('The folder is empty')],
            emails_folders_empty_summary(['account_id' => $account_id, 'folder' => 'trash'])
        );
        $this->assertSame(T('Permission denied'), emails_folders_action([
            'action' => 'empty', 'account_id' => 0, 'folder' => 'trash',
        ]));
        $this->assertSame(T('Unknown action'), emails_folders_action([
            'action' => 'empty', 'account_id' => $account_id, 'folder' => 'inbox',
        ]));
        $this->assertSame(T('The folder is empty'), emails_folders_action([
            'action' => 'empty', 'account_id' => $account_id, 'folder' => 'trash',
        ]));
        foreach (['\Trash', '\Junk'] as $special_use) {
            $query = prepare_insert_query('app_emails', [
                'account_id' => $account_id,
                'folder_id' => $ids[$special_use],
                'uidl' => 'utest_folders',
                'datetime' => current_datetime(),
                'subject' => 'Test',
            ]);
            db_query(...$query);
            make_control('emails', db_last_insert_id());
        }
        $text = sprintf(T('%d email(s) will be deleted permanently'), 1);
        $this->assertSame(
            ['status' => 'ok', 'text' => $text],
            emails_folders_empty_summary(['account_id' => $account_id, 'folder' => 'junk'])
        );
        $this->assertSame(sprintf(T('%d email(s) deleted'), 1), emails_folders_action([
            'action' => 'empty', 'account_id' => $account_id, 'folder' => 'junk',
        ]));
        $this->assertSame(sprintf(T('%d email(s) deleted'), 1), emails_folders_action([
            'action' => 'empty', 'account_id' => $account_id, 'folder' => 'trash',
        ]));
        $query = 'SELECT COUNT(*) FROM app_emails WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));

        // Remove the new account and its orphan folders
        $query = 'DELETE FROM app_emails_deletes WHERE account_id = ?';
        db_query($query, [$account_id]);
        $result = delete('emails_accounts', $account_id);
        $this->assertSame('ok', $result['status']);
        emails_folders_check(1);
        $query = 'SELECT COUNT(*) FROM app_emails_folders WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));

        set_data('server/user', null);
        set_data('rest/0', null);
        set_data('rest/1', null);
    }

    #[testdox('socket functions')]
    /**
     * socket test
     *
     * This test performs some tests to validate the correctness
     * of the socket class used by the mail protocol clients
     */
    public function test_socket(): void
    {
        // Errors without server
        $socket = new socket_class();
        $this->assertSame('Connect failed: empty host', $socket->Connect());
        $socket->hostname = '127.0.0.1';
        $this->assertSame('Connect failed: empty port', $socket->Connect());
        $line = null;
        $data = null;
        $this->assertSame('StartTls failed: not connected', $socket->StartTls());
        $this->assertSame('Write failed: not connected', $socket->WriteLine('test'));
        $this->assertSame('Read failed: not connected', $socket->ReadLine($line));
        $this->assertSame('Read failed: not connected', $socket->ReadBytes(1, $data));
        $this->assertSame('', $socket->Close());

        // A local server used to send and receive data
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int)explode(':', stream_socket_get_name($server, false))[1];

        $socket = new socket_class();
        $socket->hostname = '127.0.0.1';
        $socket->port = $port;
        $socket->timeout = 1;
        $this->assertSame('', $socket->Connect());
        $conn = stream_socket_accept($server);
        fwrite($conn, "220 hello\r\n");
        $this->assertSame('', $socket->ReadBytes(3, $data));
        $this->assertSame('220', $data);
        $this->assertSame('', $socket->ReadLine($line));
        $this->assertSame(' hello', $line);
        $this->assertSame('', $socket->WriteLine('PING'));
        $this->assertSame("PING\r\n", fgets($conn));
        $this->assertSame('Read failed: timeout', $socket->ReadLine($line));
        fclose($conn);
        $this->assertSame('Read failed: connection closed by the server', $socket->ReadLine($line));
        $this->assertSame('Read failed: connection closed by the server', $socket->ReadBytes(1, $data));
        $this->assertSame('', $socket->Close());

        // The TLS negotiation fails when the server closes the connection
        $socket = new socket_class();
        $socket->hostname = '127.0.0.1';
        $socket->port = $port;
        $socket->timeout = 1;
        $this->assertSame('', $socket->Connect());
        $conn = stream_socket_accept($server);
        fclose($conn);
        $this->assertSame('StartTls failed: TLS negotiation error', $socket->StartTls());
        $this->assertSame('', $socket->Close());

        // The TLS negotiation works with the POP3 of the Dovecot server (make teststart)
        $socket = new socket_class();
        $socket->hostname = '127.0.0.1';
        $socket->port = 10110;
        $socket->timeout = 10;
        $this->assertSame('', $socket->Connect());
        $this->assertSame('', $socket->ReadLine($line));
        $this->assertStringStartsWith('+OK', $line);
        $this->assertSame('', $socket->WriteLine('STLS'));
        $this->assertSame('', $socket->ReadLine($line));
        $this->assertStringStartsWith('+OK', $line);
        $this->assertSame('', $socket->StartTls());
        $this->assertSame('', $socket->WriteLine('QUIT'));
        $this->assertSame('', $socket->ReadLine($line));
        $this->assertStringStartsWith('+OK', $line);
        $this->assertSame('', $socket->Close());

        // The connection fails when the server is closed
        fclose($server);
        $socket = new socket_class();
        $socket->hostname = '127.0.0.1';
        $socket->port = $port;
        $socket->timeout = 1;
        $this->assertStringContainsString('Connect failed', $socket->Connect());
    }

    #[Depends('test_emails')]
    #[testdox('pop3 functions')]
    /**
     * pop3 test
     *
     * This test performs some tests to validate the correctness
     * of the pop3 class using the GreenMail server (make teststart)
     */
    public function test_pop3(): void
    {
        // Errors without server
        $pop3 = new pop3_class();
        $this->assertSame('Open failed: empty host', $pop3->Open());
        $out = null;
        $this->assertSame('OpenMessage failed: bad index', $pop3->GetMessage(0, $out));
        $this->assertSame('DeleteMessage failed: bad index', $pop3->DeleteMessage(0));
        $this->assertSame('Command failed: not connected', $pop3->ListMessages());
        $this->assertSame('', $pop3->Close());

        // Real GreenMail server (make teststart), default ports: 995 with ssl and 110 without ssl
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->ssl = 1;
        $pop3->port = 0;
        $pop3->user = 'pop3';
        $pop3->pass = 'admin';
        $this->assertSame('', $pop3->Open());
        $this->assertSame(995, $pop3->port);
        $this->assertSame('', $pop3->Close());

        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->port = 0;
        $pop3->user = 'pop3';
        $pop3->pass = 'admin';
        $this->assertSame('', $pop3->Open());
        $this->assertSame(110, $pop3->port);
        $this->assertSame('', $pop3->Close());

        // Real GreenMail server, ssl on the plain port fails in the TLS negotiation
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->ssl = 1;
        $pop3->port = 110;
        $this->assertStringContainsString('Connect failed', $pop3->Open());

        // Timeout reading the greeting, using a local server that accepts but not responds
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int)explode(':', stream_socket_get_name($server, false))[1];
        $pop3 = new pop3_class();
        $pop3->hostname = '127.0.0.1';
        $pop3->port = $port;
        $pop3->timeout = 1;
        $this->assertSame('Read failed: timeout', $pop3->Open());
        fclose($server);

        // Real GreenMail server, using the SMTP port that responds with a wrong greeting
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->port = 587;
        $this->assertStringContainsString('Greeting failed: 220', $pop3->Open());

        // STLS with the Dovecot server (make teststart), because GreenMail not supports it
        $pop3 = new pop3_class();
        $pop3->hostname = '127.0.0.1';
        $pop3->port = 10110;
        $pop3->tls = 1;
        $pop3->timeout = 10;
        $pop3->user = 'pop3';
        $pop3->pass = 'admin';
        $this->assertSame('', $pop3->Open());
        $this->assertIsArray($pop3->ListMessages());
        $this->assertSame('', $pop3->Close());

        // Real GreenMail server, STLS is not supported by GreenMail
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->port = 110;
        $pop3->tls = 1;
        $pop3->user = 'pop3';
        $pop3->pass = 'admin';
        $this->assertStringContainsString('STLS failed', $pop3->Open());

        // Real GreenMail server, using a wrong password that must not appear in the error
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->port = 995;
        $pop3->ssl = 1;
        $pop3->user = 'pop3';
        $pop3->pass = 'wrong_password';
        $error = $pop3->Open();
        $this->assertStringContainsString('PASS failed', $error);
        $this->assertStringNotContainsString('wrong_password', $error);

        // Send an email with a line that begins with a dot using SMTP, to check the dot-stuffing
        $smtp = new socket_class();
        $smtp->hostname = 'example.com';
        $smtp->port = 587;
        $this->assertSame('', $smtp->Connect());
        $lines = [
            'EHLO example.com',
            'MAIL FROM:<pop3@example.com>',
            'RCPT TO:<pop3@example.com>',
            'DATA',
            "Subject: Dot test\r\n\r\nFirst line\r\n..dot line\r\nLast line\r\n.",
            'QUIT',
        ];
        $line = null;
        $this->assertSame('', $smtp->ReadLine($line));
        foreach ($lines as $cmd) {
            $this->assertSame('', $smtp->WriteLine($cmd));
            do {
                $this->assertSame('', $smtp->ReadLine($line));
            } while (substr($line, 3, 1) === '-');
            $this->assertMatchesRegularExpression('/^[23]/', $line);
        }
        $this->assertSame('', $smtp->Close());

        // Real GreenMail server, a full session using ssl
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->port = 995;
        $pop3->ssl = 1;
        $pop3->user = 'pop3';
        $pop3->pass = 'admin';
        $this->assertSame('', $pop3->Open());

        $uidls = $pop3->ListMessages();
        $this->assertIsArray($uidls);
        $this->assertNotEmpty($uidls);
        $lastIndex = max(array_keys($uidls));

        $out = null;
        $this->assertStringContainsString('RETR 999999 failed', $pop3->GetMessage(999999, $out));
        $this->assertSame('', $pop3->GetMessage($lastIndex, $out));
        $this->assertStringContainsString("Subject: Dot test\r\n", $out);
        $this->assertStringContainsString("\r\n.dot line\r\n", $out);

        // DELE only marks the message, the indexes of the others do not change in the session
        $this->assertSame('', $pop3->DeleteMessage($lastIndex));
        $uidls2 = $pop3->ListMessages();
        $this->assertIsArray($uidls2);
        unset($uidls[$lastIndex]);
        $this->assertSame($uidls, $uidls2);
        $this->assertSame('', $pop3->Close());

        // The deletion is applied by the QUIT of the Close
        $pop3 = new pop3_class();
        $pop3->hostname = 'example.com';
        $pop3->port = 995;
        $pop3->ssl = 1;
        $pop3->user = 'pop3';
        $pop3->pass = 'admin';
        $this->assertSame('', $pop3->Open());
        $uidls3 = $pop3->ListMessages();
        $this->assertIsArray($uidls3);
        $this->assertSame(array_values($uidls), array_values($uidls3));
        $this->assertSame('', $pop3->Close());
    }

    #[testdox('imap functions')]
    /**
     * imap test
     *
     * This test performs some tests to validate the correctness of the imap class using
     * the GreenMail and the Dovecot servers (make teststart) and the scripted mock server
     */
    public function test_imap(): void
    {
        // Errors without server
        $imap = new imap_class();
        $this->assertSame('Open failed: empty host', $imap->Open());
        $this->assertSame('', $imap->Close());
        $this->assertFalse($imap->HasCapability('IMAP4rev1'));
        $this->assertSame('"a\\"b\\\\c"', imap_class::Quote('a"b\\c'));

        // Parser
        $this->assertSame(
            ['LIST', ['\\HasNoChildren', '\\Sent'], '/', 'Sent Items'],
            imap_class::Parse('LIST (\\HasNoChildren \\Sent) "/" "Sent Items"')
        );
        $data = "3 FETCH (UID 10 BODY[HEADER.FIELDS (MESSAGE-ID)] {17}\r\nMessage-ID: <x>\r\n X NIL)";
        $this->assertSame(
            [
                '3',
                'FETCH',
                ['UID', '10', 'BODY[HEADER.FIELDS (MESSAGE-ID)]', "Message-ID: <x>\r\n", 'X', null],
            ],
            imap_class::Parse($data)
        );
        $this->assertSame(['a', 'b'], imap_class::Parse('a) b'));
        $this->assertSame(['a"b', 'c'], imap_class::Parse("\"a\\\"b\" {1+}\r\nc"));
        $this->assertSame(['a'], imap_class::Parse('"a\\'));

        // Real GreenMail server (make teststart), default ports: 993 with ssl and 143 without ssl
        $imap = new imap_class();
        $imap->hostname = 'example.com';
        $imap->ssl = 1;
        $imap->user = 'pop3';
        $imap->pass = 'admin';
        $this->assertSame('', $imap->Open());
        $this->assertSame(993, $imap->port);
        $this->assertTrue($imap->HasCapability('uidplus'));
        $this->assertFalse($imap->HasCapability('SPECIAL-USE'));
        $this->assertSame('', $imap->Close());

        $imap = new imap_class();
        $imap->hostname = 'example.com';
        $imap->user = 'pop3';
        $imap->pass = 'admin';
        $imap->ignore = ['uidplus'];
        $this->assertSame('', $imap->Open());
        $this->assertSame(143, $imap->port);
        $this->assertFalse($imap->HasCapability('UIDPLUS'));
        $this->assertTrue($imap->HasCapability('MOVE'));
        $this->assertSame('', $imap->Close());

        // Real GreenMail server, errors: ssl on the plain port, STARTTLS not supported, wrong
        // password (hidden in the error) and greeting of other protocol
        $imap = new imap_class();
        $imap->hostname = 'example.com';
        $imap->ssl = 1;
        $imap->port = 143;
        $this->assertStringContainsString('Connect failed', $imap->Open());

        $imap = new imap_class();
        $imap->hostname = 'example.com';
        $imap->tls = 1;
        $this->assertSame('STARTTLS failed: BAD Invalid command.', $imap->Open());

        $imap = new imap_class();
        $imap->hostname = 'example.com';
        $imap->user = 'pop3';
        $imap->pass = 'secret';
        $error = $imap->Open();
        $this->assertStringStartsWith('LOGIN failed: NO', $error);
        $this->assertStringNotContainsString('secret', $error);

        $imap = new imap_class();
        $imap->hostname = 'example.com';
        $imap->port = 110;
        $this->assertStringStartsWith('Greeting failed: +OK', $imap->Open());

        // Real Dovecot server (make teststart): STARTTLS and AUTHENTICATE PLAIN, with the
        // initial response (SASL-IR), with the continuation and LOGIN
        foreach ([[], ['SASL-IR'], ['AUTH=PLAIN']] as $ignore) {
            $imap = new imap_class();
            $imap->hostname = '127.0.0.1';
            $imap->port = 10143;
            $imap->tls = 1;
            $imap->user = 'pop3';
            $imap->pass = 'admin';
            $imap->ignore = $ignore;
            $this->assertSame('', $imap->Open());
            $this->assertTrue($imap->HasCapability('SPECIAL-USE'));
            $this->assertSame('', $imap->Close());
        }

        // Real Dovecot server, errors: login without TLS and wrong password
        $imap = new imap_class();
        $imap->hostname = '127.0.0.1';
        $imap->port = 10143;
        $this->assertSame('Login failed: the server requires TLS', $imap->Open());

        $imap = new imap_class();
        $imap->hostname = '127.0.0.1';
        $imap->port = 10993;
        $imap->ssl = 1;
        $imap->user = 'pop3';
        $imap->pass = 'secret';
        $error = $imap->Open();
        $this->assertStringStartsWith('AUTHENTICATE failed: NO', $error);
        $this->assertStringNotContainsString('secret', $error);

        // Folders in the real servers: GreenMail (no SPECIAL-USE, the special folders are
        // detected by name, LSUB), Dovecot (SPECIAL-USE and LIST-EXTENDED) and Dovecot
        // ignoring these capabilities to use the same fallbacks than GreenMail
        $servers = [
            ['example.com', 993, []],
            ['127.0.0.1', 10993, []],
            ['127.0.0.1', 10993, ['SPECIAL-USE', 'LIST-EXTENDED']],
        ];
        foreach ($servers as [$hostname, $port, $ignore]) {
            $imap = new imap_class();
            $imap->hostname = $hostname;
            $imap->port = $port;
            $imap->ssl = 1;
            $imap->user = 'pop3';
            $imap->pass = 'admin';
            $imap->ignore = $ignore;
            $this->assertSame('', $imap->Open());
            $folders = $imap->ListFolders();
            $this->assertIsArray($folders);
            $delimiter = $folders[0]['delimiter'];
            // Remove the folders of previous executions that not finished
            foreach (['SaltOS Año', 'SaltOS Ñu'] as $name) {
                $imap->DeleteFolder($name . $delimiter . 'Niño');
                $imap->DeleteFolder($name);
            }
            $imap->CreateFolder('Sent');
            $this->assertSame('', $imap->CreateFolder('SaltOS Año'));
            $this->assertSame('', $imap->CreateFolder('SaltOS Año' . $delimiter . 'Niño'));
            $error = $imap->CreateFolder('SaltOS Año');
            $this->assertStringStartsWith('CREATE "SaltOS A&APE-o" failed: NO', $error);
            $this->assertSame('', $imap->SubscribeFolder('SaltOS Año'));
            $folders = array_column($imap->ListFolders(), null, 'name');
            $this->assertSame('\\Inbox', $folders['INBOX']['special_use']);
            $this->assertSame('\\Sent', $folders['Sent']['special_use']);
            $this->assertSame([
                'name' => 'SaltOS A&APE-o',
                'label' => 'SaltOS Año',
                'parent' => '',
                'delimiter' => $delimiter,
                'special_use' => '',
                'subscribed' => 1,
                'selectable' => 1,
            ], $folders['SaltOS A&APE-o']);
            $child = $folders['SaltOS A&APE-o' . $delimiter . 'Ni&APE-o'];
            $this->assertSame('Niño', $child['label']);
            $this->assertSame('SaltOS A&APE-o', $child['parent']);
            $this->assertSame(0, $child['subscribed']);
            $this->assertSame('', $imap->RenameFolder('SaltOS Año', 'SaltOS Ñu'));
            $this->assertSame('', $imap->DeleteFolder('SaltOS Ñu' . $delimiter . 'Niño'));
            $this->assertSame('', $imap->DeleteFolder('SaltOS Ñu'));
            $error = $imap->DeleteFolder('SaltOS Ñu');
            $this->assertStringStartsWith('DELETE "SaltOS &ANE-u" failed: NO', $error);
            $this->assertSame('', $imap->Close());
        }

        // Emails in the real servers, using two folders to not modify the INBOX used by the
        // POP3 tests: GreenMail with and without MOVE, UIDPLUS and LITERAL+, Dovecot with and
        // without MOVE and CONDSTORE; the names are unique because GreenMail breaks the
        // connection when deleting a folder selected in a previous session, like the folders
        // left by an execution that not finished
        $servers = [
            ['example.com', 993, []],
            ['example.com', 993, ['MOVE', 'UIDPLUS', 'LITERAL+']],
            ['127.0.0.1', 10993, []],
            ['127.0.0.1', 10993, ['MOVE', 'CONDSTORE']],
        ];
        $message = "From: a@example.com\nTo: b@example.com\nSubject: Test\n" .
            "Message-ID: <test1@saltos>\n\nHello\n.dot\n";
        foreach ($servers as [$hostname, $port, $ignore]) {
            $imap = new imap_class();
            $imap->hostname = $hostname;
            $imap->port = $port;
            $imap->ssl = 1;
            $imap->user = 'pop3';
            $imap->pass = 'admin';
            $imap->ignore = $ignore;
            $this->assertSame('', $imap->Open());
            $folder1 = 'SaltOS A ' . uniqid();
            $folder2 = 'SaltOS B ' . uniqid();
            $this->assertSame('', $imap->CreateFolder($folder1));
            $this->assertSame('', $imap->CreateFolder($folder2));
            $this->assertSame('', $imap->Append($folder1, $message, ['\\Seen'], $uid1));
            $message2 = str_replace('test1', 'test2', $message);
            $this->assertSame('', $imap->Append($folder1, $message2, [], $uid2));
            $this->assertSame([1, 2], [$uid1, $uid2]);
            $state = $imap->SelectFolder($folder1);
            $this->assertSame([2, 3], [$state['exists'], $state['uidnext']]);
            $this->assertGreaterThan(0, $state['uidvalidity']);
            $this->assertSame($imap->HasCapability('CONDSTORE'), $state['highestmodseq'] !== '');
            $this->assertSame([1, 2], $imap->Search('ALL'));
            $this->assertSame([2], $imap->Search('HEADER Message-ID "<test2@saltos>"'));
            $items = 'FLAGS RFC822.SIZE INTERNALDATE BODY.PEEK[HEADER.FIELDS (MESSAGE-ID)]';
            $data = $imap->Fetch('1:*', $items);
            $this->assertSame([1, 2], array_keys($data));
            $this->assertContains('\\Seen', $data[1]['FLAGS']);
            $this->assertNotContains('\\Seen', $data[2]['FLAGS']);
            $size = strlen(str_replace("\n", "\r\n", $message));
            $this->assertSame((string)$size, $data[1]['RFC822.SIZE']);
            $this->assertNotEmpty($data[1]['INTERNALDATE']);
            $header = trim($data[2]['BODY[HEADER.FIELDS (MESSAGE-ID)]']);
            $this->assertSame('Message-ID: <test2@saltos>', $header);
            $data = $imap->Fetch('1', 'BODY.PEEK[]');
            $this->assertSame(str_replace("\n", "\r\n", $message), $data[1]['BODY[]']);
            $this->assertSame('', $imap->Store('1', '-', ['\\Seen']));
            $this->assertSame('', $imap->Store('2', '+', ['\\Flagged', '\\Seen']));
            $data = $imap->Fetch('1:*', 'FLAGS');
            $this->assertNotContains('\\Seen', $data[1]['FLAGS']);
            $this->assertContains('\\Flagged', $data[2]['FLAGS']);
            if ($state['highestmodseq'] !== '') {
                $data = $imap->Fetch('1:*', 'FLAGS', $state['highestmodseq']);
                $this->assertSame([1, 2], array_keys($data));
            }
            $this->assertSame('', $imap->Move('1', $folder2, $map));
            $this->assertSame([1 => 1], $map);
            $this->assertSame([2], $imap->Search('ALL'));
            $this->assertSame('', $imap->Expunge('2'));
            $this->assertSame([], $imap->Search('ALL'));
            $this->assertSame(1, $imap->SelectFolder($folder2)['exists']);
            $this->assertStringStartsWith('SELECT "SaltOS Missing"', $imap->SelectFolder('SaltOS Missing'));
            // After a failed SELECT there is no folder selected
            $this->assertStringStartsWith('UID FETCH 1:* (FLAGS) failed:', $imap->Fetch('1:*', 'FLAGS'));
            $imap->SelectFolder('INBOX');
            $this->assertSame('', $imap->DeleteFolder($folder1));
            $this->assertSame('', $imap->DeleteFolder($folder2));
            $this->assertSame('', $imap->Close());
        }

        // Emails without connection
        $imap = new imap_class();
        $error = 'Command failed: not connected';
        $this->assertSame($error, $imap->SelectFolder('INBOX'));
        $this->assertSame($error, $imap->Search('ALL'));
        $this->assertSame($error, $imap->Fetch('1:*', 'FLAGS'));
        $this->assertSame($error, $imap->Store('1', '+', ['\\Seen']));
        $this->assertSame($error, $imap->Move('1', 'INBOX', $map));
        $this->assertSame($error, $imap->Expunge('1'));
        $this->assertSame($error, $imap->Append('INBOX', $message, [], $uid1));

        // Folders without connection
        $imap = new imap_class();
        $this->assertSame('Command failed: not connected', $imap->ListFolders());
        $this->assertSame('Command failed: not connected', $imap->CreateFolder('Test'));
        $this->assertSame('Niño/Ç', imap_class::Decode(imap_class::Encode('Niño/Ç')));

        // Timeout reading the greeting, using a local server that accepts but not responds
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int)explode(':', stream_socket_get_name($server, false))[1];
        $imap = new imap_class();
        $imap->hostname = '127.0.0.1';
        $imap->port = $port;
        $imap->timeout = 1;
        $this->assertSame('Read failed: timeout', $imap->Open());
        fclose($server);

        // Folders like the ones of Gmail (special uses without LIST-EXTENDED, \\Noselect) and a
        // server without hierarchy, using the scripted mock server
        $login = [
            '* OK [CAPABILITY IMAP4rev1 SPECIAL-USE] ready', '<', 'A0001 OK logged', '<',
            '* CAPABILITY IMAP4rev1 SPECIAL-USE', 'A0002 OK done', '<',
        ];
        $mock = $this->mock_start(array_merge($login, [
            '* LIST (\\Noselect) "/" "[Gmail]"',
            '* LIST (\\HasNoChildren \\Sent) "/" "[Gmail]/Sent Mail"',
            '* LIST () NIL Flat',
            '* OK note',
            'A0003 OK done', '<',
            '* LSUB () "/" "[Gmail]/Sent Mail"',
            'A0004 OK done',
        ]));
        $imap = new imap_class();
        $imap->hostname = '127.0.0.1';
        $imap->port = $mock[2];
        $imap->timeout = 10;
        $this->assertSame('', $imap->Open());
        $this->assertSame([
            [
                'name' => '[Gmail]', 'label' => '[Gmail]', 'parent' => '', 'delimiter' => '/',
                'special_use' => '', 'subscribed' => 0, 'selectable' => 0,
            ],
            [
                'name' => '[Gmail]/Sent Mail', 'label' => 'Sent Mail', 'parent' => '[Gmail]',
                'delimiter' => '/', 'special_use' => '\\Sent', 'subscribed' => 1, 'selectable' => 1,
            ],
            [
                'name' => 'Flat', 'label' => 'Flat', 'parent' => '', 'delimiter' => '',
                'special_use' => '', 'subscribed' => 0, 'selectable' => 1,
            ],
        ], $imap->ListFolders());
        $this->mock_stop($mock);

        foreach ([['A0003 NO denied'], ['A0003 OK done', '<', 'A0004 NO denied']] as $index => $script) {
            $mock = $this->mock_start(array_merge($login, $script));
            $imap = new imap_class();
            $imap->hostname = '127.0.0.1';
            $imap->port = $mock[2];
            $imap->timeout = 10;
            $this->assertSame('', $imap->Open());
            $expected = $index ? 'LSUB "" "*" failed: NO denied' : 'LIST "" "*" failed: NO denied';
            $this->assertSame($expected, $imap->ListFolders());
            $this->mock_stop($mock);
        }

        // Unsolicited FETCH responses without UID and COPYUID with ranges, using the scripted
        // mock server
        $mock = $this->mock_start([
            '* OK [CAPABILITY IMAP4rev1 MOVE] ready', '<', 'A0001 OK logged', '<',
            '* CAPABILITY IMAP4rev1 MOVE', 'A0002 OK done', '<',
            '* 1 FETCH (FLAGS (\\Seen))', '* 2 FETCH (UID 7 FLAGS ())', '* 3 EXISTS', 'A0003 OK done', '<',
            '* OK [COPYUID 9 4:6,9 10:13] moved', 'A0004 OK done',
        ]);
        $imap = new imap_class();
        $imap->hostname = '127.0.0.1';
        $imap->port = $mock[2];
        $imap->timeout = 10;
        $this->assertSame('', $imap->Open());
        $this->assertSame([7 => ['UID' => '7', 'FLAGS' => []]], $imap->Fetch('7', 'FLAGS'));
        $this->assertSame('', $imap->Move('4:6,9', 'Other', $map));
        $this->assertSame([4 => 10, 5 => 11, 6 => 12, 9 => 13], $map);
        $this->mock_stop($mock);

        // Responses that the real servers never send, using the scripted mock server
        $scripts = [
            ['CAPABILITY failed: NO denied', ['* OK ready', '<', 'A0001 NO denied']],
            ['CAPABILITY failed: unexpected continuation', ['* OK ready', '<', '+ go']],
            ['Read failed: connection closed by the server', ['* OK ready', '<']],
            ['Read failed: connection closed by the server', ['* OK ready', '<', '* CAPABILITY {10}']],
            [
                'Login failed: the server requires TLS',
                ['* OK ready', '<', "* CAPABILITY IMAP4rev1 {3}\r\nabc LOGINDISABLED", 'A0001 OK done'],
            ],
            ['StartTls failed: TLS negotiation error', ['* OK ready', '<', 'A0001 OK begin TLS']],
        ];
        foreach ($scripts as [$expected, $script]) {
            $mock = $this->mock_start($script);
            $imap = new imap_class();
            $imap->hostname = '127.0.0.1';
            $imap->port = $mock[2];
            $imap->tls = str_starts_with($expected, 'StartTls') ? 1 : 0;
            $imap->timeout = 10;
            $this->assertSame($expected, $imap->Open());
            $this->mock_stop($mock);
        }
    }

    #[testdox('accounts functions')]
    /**
     * accounts test
     *
     * This test performs some tests to validate the correctness of the test of the
     * connection of the accounts using the GreenMail and the Dovecot servers (make teststart)
     */
    public function test_accounts(): void
    {
        // POP3 and SMTP of GreenMail
        $json = [
            'protocol' => 'pop3',
            'pop3_host' => 'example.com',
            'pop3_port' => '995',
            'pop3_extra' => 'ssl',
            'pop3_user' => 'pop3',
            'pop3_pass' => 'admin',
            'smtp_host' => 'example.com',
            'smtp_port' => '465',
            'smtp_extra' => 'ssl',
            'smtp_user' => 'pop3',
            'smtp_pass' => 'admin',
        ];
        $result = emails_accounts_test($json + ['server' => 'incoming']);
        $this->assertSame('ok', $result['status']);
        $prefix = explode('%d', T('Connection successful, %d emails in the server'))[0];
        $this->assertStringStartsWith($prefix, $result['text']);
        $result = emails_accounts_test($json + ['server' => 'outgoing']);
        $this->assertSame(['status' => 'ok', 'text' => T('Connection successful')], $result);

        // IMAP of Dovecot with STARTTLS, the SMTP is sent using the local server
        $json = [
            'protocol' => 'imap',
            'pop3_host' => '127.0.0.1',
            'pop3_port' => '10143',
            'pop3_extra' => 'tls',
            'pop3_user' => 'pop3',
            'pop3_pass' => 'admin',
            'smtp_host' => '',
        ];
        $result = emails_accounts_test($json + ['server' => 'incoming']);
        $this->assertSame('ok', $result['status']);
        $prefix = explode('%d', T('Connection successful, %d folders in the server'))[0];
        $this->assertStringStartsWith($prefix, $result['text']);
        $result = emails_accounts_test($json + ['server' => 'outgoing']);
        $text = T('Not tested, the emails are sent using the local server');
        $this->assertSame(['status' => 'ok', 'text' => $text], $result);

        // Errors: incoming and outgoing servers that not exist
        $json = [
            'protocol' => 'imap',
            'pop3_host' => '127.0.0.1',
            'pop3_port' => '1',
            'smtp_host' => '127.0.0.1',
            'smtp_port' => '1',
        ];
        $result = emails_accounts_test($json + ['server' => 'incoming']);
        $this->assertSame('error', $result['status']);
        $this->assertStringStartsWith('Connect failed', $result['text']);
        $result = emails_accounts_test($json + ['server' => 'outgoing']);
        $this->assertSame('error', $result['status']);
        $this->assertNotSame('', $result['text']);

        // Errors: wrong passwords, the IMAP login fails and the POP3 login fails
        foreach (['imap' => '10993', 'pop3' => '10995'] as $protocol => $port) {
            $json = [
                'server' => 'incoming',
                'protocol' => $protocol,
                'pop3_host' => '127.0.0.1',
                'pop3_port' => $port,
                'pop3_extra' => 'ssl',
                'pop3_user' => 'pop3',
                'pop3_pass' => 'secret',
            ];
            $result = emails_accounts_test($json);
            $this->assertSame('error', $result['status']);
            $this->assertStringNotContainsString('secret', $result['text']);
        }

        // Errors: unknown server
        test_external_exec('php/emails12.php', 'phperror.log', 'unknown server nada');

        // The action of the app
        $json = test_cli_helper('app/emails_accounts/action/test', [
            'server' => 'incoming',
            'protocol' => 'pop3',
            'pop3_host' => 'example.com',
            'pop3_port' => '110',
            'pop3_user' => 'pop3',
            'pop3_pass' => 'admin',
        ], '', '', 'admin');
        $this->assertSame('ok', $json['status']);
        $this->assertArrayHasKey('text', $json);
    }

    #[Depends('test_emails')]
    #[testdox('getmail imap functions')]
    /**
     * getmail imap test
     *
     * This test performs some tests to validate the correctness of the synchronization of
     * the IMAP accounts using the Dovecot server (make teststart) with a new user, that
     * starts with an empty mailbox
     */
    public function test_getmail_imap(): void
    {
        set_data('server/user', 'admin');
        set_data('server/lang', 'en');
        set_data('rest/0', 'app');
        set_data('rest/1', 'emails');

        $this->imap_cleanup();

        // A new IMAP account without folders until the first synchronization, Dovecot
        // creates the trash and the junk of the new users
        $user = uniqid('saltos');
        $result = emails_accounts_insert([
            'user_id' => current_user(),
            'email_name' => 'Test',
            'email_from' => 'test@example.com',
            'protocol' => 'imap',
            'pop3_host' => '127.0.0.1',
            'pop3_port' => '10993',
            'pop3_extra' => 'ssl',
            'pop3_user' => $user,
            'pop3_pass' => 'admin',
        ]);
        $this->assertSame('ok', $result['status']);
        $account_id = intval($result['created_id']);
        $account = execute_query('SELECT * FROM app_emails_accounts WHERE id = ?', [$account_id]);
        $this->assertSame([], emails_folders_check($account_id));

        // Folders in the server before the first synchronization
        $imap = new imap_class();
        $imap->hostname = '127.0.0.1';
        $imap->port = 10993;
        $imap->ssl = 1;
        $imap->user = $user;
        $imap->pass = 'admin';
        $this->assertSame('', $imap->Open());
        foreach (['Sent', 'Drafts', 'Work', 'Work/Child', 'Other'] as $name) {
            $this->assertSame('', $imap->CreateFolder($name));
        }
        $this->assertSame('', $imap->SubscribeFolder('Work'));
        $this->assertSame('', $imap->SubscribeFolder('Drafts'));

        // The first synchronization subscribes the generic folders and the subscribed ones
        // except the drafts
        $newemail = 0;
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT name, id, parent_id, label, special_use, is_subscribed
            FROM app_emails_folders WHERE account_id = ?';
        $folders = array_column(execute_query_array($query, [$account_id]), null, 'name');
        $names = array_keys($folders);
        sort($names);
        $expected = ['Drafts', 'INBOX', 'Junk', 'Other', 'Sent', 'Trash', 'Work', 'Work/Child'];
        $this->assertSame($expected, $names);
        $this->assertSame('\Inbox', $folders['INBOX']['special_use']);
        $this->assertSame('\Sent', $folders['Sent']['special_use']);
        $subscribed = array_map('intval', array_column($folders, 'is_subscribed', 'name'));
        $this->assertSame(1, $subscribed['INBOX']);
        $this->assertSame(1, $subscribed['Sent']);
        $this->assertSame(1, $subscribed['Trash']);
        $this->assertSame(1, $subscribed['Junk']);
        $this->assertSame(0, $subscribed['Drafts']);
        $this->assertSame(1, $subscribed['Work']);
        $this->assertSame(0, $subscribed['Work/Child']);
        $this->assertSame(0, $subscribed['Other']);
        $this->assertSame(intval($folders['Work']['id']), intval($folders['Work/Child']['parent_id']));
        $this->assertSame('Child', $folders['Work/Child']['label']);
        $ids = array_map('intval', emails_folders_check($account_id));
        ksort($ids);
        $this->assertSame([
            '\\Inbox' => intval($folders['INBOX']['id']),
            '\\Junk' => intval($folders['Junk']['id']),
            '\\Sent' => intval($folders['Sent']['id']),
            '\\Trash' => intval($folders['Trash']['id']),
        ], $ids);

        // Changes in the server: the new folders are not subscribed, the removed folders are
        // removed, the renamed folders are a removed folder and a new folder, and the local
        // subscriptions are kept
        $this->assertSame('', $imap->CreateFolder('New'));
        $this->assertSame('', $imap->SubscribeFolder('New'));
        $this->assertSame('', $imap->DeleteFolder('Other'));
        $this->assertSame('', $imap->RenameFolder('Work/Child', 'Work/Renamed'));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $folders2 = array_column(execute_query_array($query, [$account_id]), null, 'name');
        $this->assertArrayNotHasKey('Other', $folders2);
        $this->assertArrayNotHasKey('Work/Child', $folders2);
        $this->assertSame(0, intval($folders2['New']['is_subscribed']));
        $this->assertSame(0, intval($folders2['Work/Renamed']['is_subscribed']));
        $this->assertSame(intval($folders['Work']['id']), intval($folders2['Work/Renamed']['parent_id']));
        foreach (['INBOX', 'Sent', 'Trash', 'Junk', 'Drafts', 'Work'] as $name) {
            $this->assertSame($folders[$name], $folders2[$name]);
        }
        $this->assertSame(0, $newemail);

        // The generic special folders are always subscribed, also if they appear later (like
        // the trash created by another client), simulated unsubscribing the trash
        $query2 = 'UPDATE app_emails_folders SET is_subscribed = 0 WHERE id = ?';
        db_query($query2, [$folders['Trash']['id']]);
        $this->assertSame('', getmail_imap($account, $newemail));
        $query2 = 'SELECT is_subscribed FROM app_emails_folders WHERE id = ?';
        $this->assertSame(1, intval(execute_query($query2, [$folders['Trash']['id']])));

        // The changes of the folders found by the synchronization are notified to the
        // folders tree with a push event, only if the folders change
        $query2 = 'SELECT COUNT(*) FROM tbl_push WHERE message = ?';
        $count = intval(execute_query($query2, ['saltos.emails.folders']));
        $this->assertSame('', $imap->Open());
        $this->assertSame('', $imap->CreateFolder('Push'));
        $this->assertSame('', $imap->Close());
        $cli = get_data('server/cli');
        set_data('server/cli', true);
        $this->assertIsArray(getmail_server());
        $this->assertSame($count + 1, intval(execute_query($query2, ['saltos.emails.folders'])));
        $this->assertIsArray(getmail_server());
        $this->assertSame($count + 1, intval(execute_query($query2, ['saltos.emails.folders'])));
        set_data('server/cli', $cli);
        db_query('DELETE FROM tbl_push WHERE message = ?', ['saltos.emails.folders']);
        $this->assertSame('', $imap->Open());
        $this->assertSame('', $imap->DeleteFolder('Push'));
        $this->assertSame('', $imap->Close());

        // Emails in the server: three in the INBOX with different flags and one in a not
        // subscribed folder, that is not downloaded
        $this->assertSame('', $imap->Open());
        $emails = [
            ['INBOX', 1, []],
            ['INBOX', 2, ['\\Seen', '\\Flagged']],
            ['INBOX', 3, ['\\Seen', '\\Answered', '$Forwarded']],
            ['New', 4, []],
        ];
        $uids = [];
        foreach ($emails as [$name, $number, $flags]) {
            $message = "From: a@example.com\nTo: b@example.com\nSubject: Test $number\n" .
                "Message-ID:\n <test$number@saltos>\n\nHello $number\n";
            $this->assertSame('', $imap->Append($name, $message, $flags, $uid));
            $uids[$number] = $uid;
        }
        $this->assertSame('', $imap->Close());

        // The batch limits the emails downloaded in each synchronization, the newest first
        set_config('emails/imapbatch', 2);
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(2, $newemail);
        $query = 'SELECT imap_uid FROM app_emails WHERE account_id = ? ORDER BY imap_uid';
        $downloaded = array_map('intval', execute_query_array($query, [$account_id]));
        $this->assertSame([$uids[2], $uids[3]], $downloaded);
        set_config('emails/imapbatch', 100);
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(3, $newemail);

        // The emails with its folder, its IMAP fields, its states and its file
        $query = 'SELECT id, folder_id, uidl, imap_uid, imap_messageid, imap_emailid, subject,
            state_new, state_reply, state_forward, state_wait
            FROM app_emails WHERE account_id = ? ORDER BY imap_uid';
        $rows = execute_query_array($query, [$account_id]);
        $this->assertCount(3, $rows);
        $states = [[1, 0, 0, 0], [0, 0, 0, 1], [0, 1, 1, 0]];
        foreach ($rows as $index => $row) {
            $number = $index + 1;
            $this->assertSame(intval($folders['INBOX']['id']), intval($row['folder_id']));
            $this->assertSame($uids[$number], intval($row['imap_uid']));
            $this->assertSame("<test$number@saltos>", $row['imap_messageid']);
            $this->assertSame('', $row['imap_emailid']);
            $this->assertSame("Test $number", $row['subject']);
            $this->assertSame($states[$index], array_map('intval', [
                $row['state_new'], $row['state_reply'], $row['state_forward'], $row['state_wait'],
            ]));
            $file = get_directory('dirs/inboxdir') . $account_id . '/' . $row['uidl'] . '.eml.gz';
            $this->assertFileExists($file);
        }

        // Without changes in the server, the folders are skipped
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(3, $newemail);

        // A new UIDVALIDITY: the emails are matched by Message-ID and size, not downloaded
        $query = 'UPDATE app_emails_folders SET uidvalidity = uidvalidity + 1 WHERE id = ?';
        db_query($query, [$folders['INBOX']['id']]);
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(3, $newemail);
        $query = 'SELECT id, folder_id, uidl, imap_uid, imap_messageid, imap_emailid, subject,
            state_new, state_reply, state_forward, state_wait
            FROM app_emails WHERE account_id = ? ORDER BY imap_uid';
        $this->assertSame($rows, execute_query_array($query, [$account_id]));

        // Flags changed by other clients, the server wins: with CONDSTORE (changes since the
        // last HIGHESTMODSEQ) and without it (all the flags, simulated without the saved
        // HIGHESTMODSEQ)
        $query = 'SELECT state_new, state_reply, state_forward, state_wait
            FROM app_emails WHERE account_id = ? ORDER BY imap_uid';
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('INBOX'));
        $this->assertSame('', $imap->Store(strval($uids[1]), '+', ['\\Seen', '\\Flagged']));
        $this->assertSame('', $imap->Store(strval($uids[2]), '-', ['\\Seen', '\\Flagged']));
        $this->assertSame('', $imap->Store(strval($uids[3]), '-', ['\\Answered']));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame([[0, 0, 0, 1], [1, 0, 0, 0], [0, 0, 1, 0]], array_map(
            fn ($row) => array_map('intval', array_values($row)),
            execute_query_array($query, [$account_id])
        ));
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('INBOX'));
        $this->assertSame('', $imap->Store(strval($uids[3]), '+', ['\\Answered', '\\Flagged']));
        $this->assertSame('', $imap->Close());
        $query2 = 'UPDATE app_emails_folders SET highestmodseq = NULL WHERE id = ?';
        db_query($query2, [$folders['INBOX']['id']]);
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame([[0, 0, 0, 1], [1, 0, 0, 0], [0, 1, 1, 1]], array_map(
            fn ($row) => array_map('intval', array_values($row)),
            execute_query_array($query, [$account_id])
        ));
        $this->assertSame(3, $newemail);

        // Changes done by other clients: the first email moved to another folder keeps its
        // register with the new folder and the new UID, the second email deleted is deleted
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('INBOX'));
        $this->assertSame('', $imap->Move(strval($uids[1]), 'Work', $map));
        $this->assertSame('', $imap->Expunge(strval($uids[2])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(3, $newemail);
        $query = 'SELECT folder_id, imap_uid, uidl FROM app_emails WHERE id = ?';
        $row = execute_query($query, [$rows[0]['id']]);
        $this->assertSame(intval($folders['Work']['id']), intval($row['folder_id']));
        $this->assertSame($map[$uids[1]], intval($row['imap_uid']));
        $this->assertSame($rows[0]['uidl'], $row['uidl']);
        $query = 'SELECT COUNT(*) FROM app_emails WHERE id = ?';
        $this->assertSame(0, intval(execute_query($query, [$rows[1]['id']])));
        $file = get_directory('dirs/inboxdir') . $account_id . '/' . $rows[1]['uidl'] . '.eml.gz';
        $this->assertFileDoesNotExist($file);

        // Ambiguous case: the third email moved to the sent folder and a copy appended in
        // other folder, the register is deleted and both emails are downloaded
        $message = "From: a@example.com\nTo: b@example.com\nSubject: Test 3\n" .
            "Message-ID:\n <test3@saltos>\n\nHello 3\n";
        $this->assertSame('', $imap->Append('Work', $message, [], $uid));
        $this->assertSame('', $imap->Move(strval($uids[3]), 'Sent', $map));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(5, $newemail);
        $query = 'SELECT COUNT(*) FROM app_emails WHERE id = ?';
        $this->assertSame(0, intval(execute_query($query, [$rows[2]['id']])));
        $query = 'SELECT folder_id FROM app_emails
            WHERE account_id = ? AND imap_messageid = ? ORDER BY folder_id';
        $expected = [intval($folders['Sent']['id']), intval($folders['Work']['id'])];
        sort($expected);
        $this->assertSame(
            $expected,
            array_map('intval', execute_query_array($query, [$account_id, '<test3@saltos>']))
        );

        // A folder removed in the server: its emails are deleted
        $this->assertSame('', $imap->DeleteFolder('Work/Renamed'));
        $this->assertSame('', $imap->DeleteFolder('Work'));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(5, $newemail);
        $query = 'SELECT folder_id FROM app_emails WHERE account_id = ?';
        $this->assertSame([intval($folders['Sent']['id'])], array_map(
            'intval',
            execute_query_array($query, [$account_id])
        ));

        // The queue: a new email in the INBOX
        $message = "From: a@example.com\nTo: b@example.com\nSubject: Test 5\n" .
            "Message-ID: <test5@saltos>\n\nHello 5\n";
        $this->assertSame('', $imap->Open());
        $this->assertSame('', $imap->Append('INBOX', $message, [], $uid5));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(6, $newemail);
        $query = 'SELECT id FROM app_emails WHERE account_id = ? AND imap_messageid = ?';
        $email_id = intval(execute_query($query, [$account_id, '<test5@saltos>']));
        $trash_id = intval($folders['Trash']['id']);

        // A flag, a move to the trash (done in SaltOS at once) and a flag that depends on the
        // move: the UID learned by the move is used by the next operation and by the email
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => '\Seen']));
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'move', ['to_folder_id' => $trash_id]));
        $query = 'UPDATE app_emails SET folder_id = ?, imap_uid = NULL WHERE id = ?';
        db_query($query, [$trash_id, $email_id]);
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => '\Flagged']));
        $query = 'SELECT imap_uid FROM app_emails_queue WHERE email_id = ? ORDER BY id';
        $this->assertSame([$uid5, $uid5, 0], array_map('intval', execute_query_array($query, [$email_id])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(6, $newemail);
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));
        $query = 'SELECT folder_id, imap_uid, state_new, state_wait FROM app_emails WHERE id = ?';
        $row = execute_query($query, [$email_id]);
        $this->assertSame($trash_id, intval($row['folder_id']));
        $this->assertSame([0, 1], [intval($row['state_new']), intval($row['state_wait'])]);
        $uid = intval($row['imap_uid']);
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('Trash'));
        $fetch = $imap->Fetch(strval($uid), 'FLAGS');
        $this->assertIsArray($fetch);
        $this->assertContains('\Seen', $fetch[$uid]['FLAGS']);
        $this->assertContains('\Flagged', $fetch[$uid]['FLAGS']);
        $this->assertSame('', $imap->Close());

        // An operation of an email that not exists in the server is removed, and an
        // operation without UID that not depends on a move waits
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => '\Seen']));
        db_query('UPDATE app_emails_queue SET imap_uid = 999999 WHERE email_id = ?', [$email_id]);
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => '\Seen']));
        $query = 'UPDATE app_emails_queue SET imap_uid = NULL, folder_id = ? WHERE email_id = ?';
        db_query($query, [$folders['INBOX']['id'], $email_id]);
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(1, intval(execute_query($query, [$account_id])));
        db_query('DELETE FROM app_emails_queue WHERE email_id = ?', [$email_id]);

        // An error returned by the server: the operation is retried up to the maximum
        // retries and the next operations of the email wait
        set_config('emails/imapretries', 2);
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => 'bad(flag']));
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'flag_remove', ['flag' => '\Flagged']));
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT retries, error FROM app_emails_queue WHERE email_id = ? ORDER BY id';
        $rows2 = execute_query_array($query, [$email_id]);
        $this->assertCount(2, $rows2);
        $this->assertSame(1, intval($rows2[0]['retries']));
        $this->assertStringContainsString('failed: BAD', $rows2[0]['error']);
        $this->assertSame(0, intval($rows2[1]['retries']));
        $file = 'data/logs/saltos.log';
        $this->assertFileDoesNotExist($file);
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));
        $query = 'SELECT state_wait FROM app_emails WHERE id = ?';
        $this->assertSame(0, intval(execute_query($query, [$email_id])));
        set_config('emails/imapretries', 10);
        $this->assertFileExists($file);
        $words = "IMAP operation flag_add of the email $email_id removed";
        $this->assertTrue(words_exists($words, file_get_contents($file)));
        unlink($file);

        // An email deleted in SaltOS is expunged in the server
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'expunge'));
        __getmail_delete(strval($email_id));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('Trash'));
        $this->assertSame([], $imap->Search("UID $uid"));
        $this->assertSame('', $imap->Close());

        // The operations of the emails that never were in the server are not enqueued
        $this->assertFalse(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => '\Seen']));
        $query = 'SELECT id FROM app_emails WHERE account_id = ?';
        $email_id = intval(execute_query($query, [$account_id]));
        db_query('UPDATE app_emails SET imap_uid = NULL WHERE id = ?', [$email_id]);
        $this->assertFalse(__getmail_imap_enqueue($email_id, 'flag_add', ['flag' => '\Seen']));
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));

        // An email without UID (the last one) adopts the appeared email of its folder with
        // the same Message-ID and size, and the UID is written in its waiting operations
        $sent_id = intval($folders['Sent']['id']);
        $query = prepare_insert_query('app_emails_queue', [
            'account_id' => $account_id,
            'email_id' => $email_id,
            'action' => 'flag_add',
            'folder_id' => $sent_id,
            'flag' => '\Flagged',
            'datetime' => current_datetime(),
        ]);
        db_query(...$query);
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(6, $newemail);
        $uid = intval(execute_query('SELECT imap_uid FROM app_emails WHERE id = ?', [$email_id]));
        $this->assertGreaterThan(0, $uid);
        $query = 'SELECT imap_uid FROM app_emails_queue WHERE email_id = ?';
        $this->assertSame($uid, intval(execute_query($query, [$email_id])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));
        $query = 'SELECT state_wait FROM app_emails WHERE id = ?';
        $this->assertSame(1, intval(execute_query($query, [$email_id])));

        // A move pending in the queue (the destination folder not exists in the server): the
        // email is frozen and its UID is known, so it is not downloaded again; when the move
        // is removed, the email of the removed folder is matched with the email of the server
        $query = prepare_insert_query('app_emails_folders', [
            'account_id' => $account_id,
            'parent_id' => 0,
            'name' => 'Missing',
            'label' => 'Missing',
            'delimiter' => '/',
        ]);
        db_query(...$query);
        $missing_id = db_last_insert_id();
        $this->assertTrue(__getmail_imap_enqueue($email_id, 'move', ['to_folder_id' => $missing_id]));
        $query = prepare_update_query('app_emails', [
            'folder_id' => $missing_id,
            'imap_uid' => 0,
        ], [
            'id' => $email_id,
        ]);
        db_query(...$query);
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(6, $newemail);
        $query = 'SELECT COUNT(*) FROM app_emails WHERE id = ?';
        $this->assertSame(1, intval(execute_query($query, [$email_id])));
        $query = 'SELECT retries FROM app_emails_queue WHERE email_id = ?';
        $this->assertSame(1, intval(execute_query($query, [$email_id])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(6, $newemail);
        $query = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));
        $query = 'SELECT folder_id, imap_uid FROM app_emails WHERE id = ?';
        $row = execute_query($query, [$email_id]);
        $this->assertSame([$sent_id, $uid], [intval($row['folder_id']), intval($row['imap_uid'])]);

        // A frozen email (with a waiting operation) is not deleted when it vanishes from the
        // server, and it is deleted when the operation is removed
        $query = prepare_insert_query('app_emails_queue', [
            'account_id' => $account_id,
            'email_id' => $email_id,
            'action' => 'flag_add',
            'folder_id' => $folders['INBOX']['id'],
            'flag' => '\Seen',
            'datetime' => current_datetime(),
        ]);
        db_query(...$query);
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('Sent'));
        $this->assertSame('', $imap->Expunge(strval($uid)));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT COUNT(*) FROM app_emails WHERE id = ?';
        $this->assertSame(1, intval(execute_query($query, [$email_id])));
        db_query('DELETE FROM app_emails_queue WHERE email_id = ?', [$email_id]);
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT COUNT(*) FROM app_emails WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));

        // The actions of SaltOS on a new email are applied in the server: open (\Seen),
        // wait (\Flagged), new (without \Seen), move to the junk, delete (move to the
        // trash) and delete from the trash (expunge)
        $message = "From: a@example.com\nTo: b@example.com\nSubject: Test 6\n" .
            "Message-ID: <test6@saltos>\n\nHello 6\n";
        $this->assertSame('', $imap->Open());
        $this->assertSame('', $imap->Append('INBOX', $message, [], $uid));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(7, $newemail);
        $query = 'SELECT id FROM app_emails WHERE account_id = ?';
        $email_id = intval(execute_query($query, [$account_id]));
        $server = function ($name) use ($imap, $email_id) {
            $uid = intval(execute_query('SELECT imap_uid FROM app_emails WHERE id = ?', [$email_id]));
            $this->assertSame('', $imap->Open());
            $this->assertIsArray($imap->SelectFolder($name));
            $fetch = $imap->Fetch(strval($uid), 'FLAGS');
            $this->assertSame('', $imap->Close());
            $this->assertIsArray($fetch);
            $this->assertArrayHasKey($uid, $fetch);
            return array_values(array_intersect($fetch[$uid]['FLAGS'], ['\Seen', '\Flagged']));
        };
        $queue = 'SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ?';
        $this->assertIsString(getmail_body($email_id));
        $this->assertSame(1, intval(execute_query($queue, [$account_id])));
        $pending = function () use ($account_id, $email_id) {
            $json = test_cli_helper('app/emails/list/data', [
                'account_id' => $account_id,
            ], '', '', 'admin');
            $data = array_column($json['data'], 'body_text', 'id');
            return str_contains($data[$email_id], 'Pending');
        };
        $this->assertTrue($pending());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertFalse($pending());
        $this->assertSame(['\Seen'], $server('INBOX'));
        getmail_setter($email_id, 'wait=1');
        getmail_setter($email_id, 'new=1');
        $this->assertSame(2, intval(execute_query($queue, [$account_id])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(['\Flagged'], $server('INBOX'));
        $junk_id = intval($folders['Junk']['id']);
        $this->assertSame(sprintf(T('%d email(s) moved'), 1), emails_folders_move_to($email_id, $junk_id));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(['\Flagged'], $server('Junk'));
        $this->assertSame(sprintf(T('%d email(s) moved to trash'), 1), getmail_delete($email_id));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(['\Flagged'], $server('Trash'));
        $uid = intval(execute_query('SELECT imap_uid FROM app_emails WHERE id = ?', [$email_id]));
        $this->assertSame(sprintf(T('%d email(s) deleted'), 1), getmail_delete($email_id));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(0, intval(execute_query($queue, [$account_id])));
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('Trash'));
        $this->assertSame([], $imap->Search("UID $uid"));
        $this->assertSame('', $imap->Close());
        $this->assertSame(7, $newemail);

        // The folder actions of the IMAP accounts are done in the server: create (and
        // subscribe), rename and move, the subfolders get its new full names, and the
        // next synchronization keeps the same folders
        $action = fn ($json) => emails_folders_action($json + ['account_id' => $account_id]);
        $this->assertSame(T('Folder created'), $action(['action' => 'create', 'label' => 'Projects']));
        $query = 'SELECT id FROM app_emails_folders WHERE account_id = ? AND name = ?';
        $projects = intval(execute_query($query, [$account_id, 'Projects']));
        $this->assertSame(T('Folder created'), $action([
            'action' => 'create', 'parent_id' => $projects, 'label' => 'Año',
        ]));
        $child = intval(execute_query($query, [$account_id, 'Projects/A&APE-o']));
        $this->assertGreaterThan(0, $child);
        $this->assertSame('', $imap->Open());
        $folders2 = array_column($imap->ListFolders(), null, 'name');
        $this->assertSame(1, $folders2['Projects/A&APE-o']['subscribed']);
        $this->assertSame('', $imap->Close());
        $this->assertSame(
            sprintf(T('The name of the folder can not contain the character %s'), '/'),
            $action(['action' => 'create', 'label' => 'a/b'])
        );
        $this->assertSame('', $imap->Open());
        $this->assertSame('', $imap->CreateFolder('Ghost'));
        $this->assertSame('', $imap->Close());
        $error = $action(['action' => 'create', 'label' => 'Ghost']);
        $this->assertStringStartsWith(explode('%s', T('The server returned an error: %s'))[0], $error);
        $this->assertSame(T('Folder renamed'), $action([
            'action' => 'rename', 'folder_id' => $projects, 'label' => 'Projects2',
        ]));
        $this->assertSame('Projects2/A&APE-o', execute_query(
            'SELECT name FROM app_emails_folders WHERE id = ?',
            [$child]
        ));
        $this->assertSame(T('Folder moved'), $action([
            'action' => 'move', 'folder_id' => $child, 'parent_id' => 0,
        ]));
        $row = execute_query('SELECT name, parent_id FROM app_emails_folders WHERE id = ?', [$child]);
        $this->assertSame(['A&APE-o', 0], [$row['name'], intval($row['parent_id'])]);
        $this->assertSame('', $imap->Open());
        $folders2 = array_column($imap->ListFolders(), null, 'name');
        $this->assertArrayHasKey('Projects2', $folders2);
        $this->assertArrayHasKey('A&APE-o', $folders2);
        $this->assertArrayNotHasKey('Projects', $folders2);
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $query = 'SELECT id FROM app_emails_folders WHERE account_id = ? AND name = ?';
        $this->assertSame($projects, intval(execute_query($query, [$account_id, 'Projects2'])));
        $this->assertSame($child, intval(execute_query($query, [$account_id, 'A&APE-o'])));
        $this->assertSame('', $imap->Open());
        foreach (['Año', 'Projects2', 'Ghost'] as $name) {
            $this->assertSame('', $imap->DeleteFolder($name));
        }
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));

        // Delete a folder of an IMAP account: its emails and the emails of its subfolders
        // are moved to the trash in the server and in SaltOS (with its new UIDs), and the
        // folders are deleted in the server and in SaltOS
        $this->assertSame(T('Folder created'), $action(['action' => 'create', 'label' => 'Old']));
        $query = 'SELECT id FROM app_emails_folders WHERE account_id = ? AND name = ?';
        $old = intval(execute_query($query, [$account_id, 'Old']));
        $this->assertSame(T('Folder created'), $action([
            'action' => 'create', 'parent_id' => $old, 'label' => 'Sub',
        ]));
        $this->assertSame('', $imap->Open());
        foreach (['Old', 'Old/Sub'] as $index => $name) {
            $message = "From: a@example.com\nTo: b@example.com\nSubject: Test old $index\n" .
                "Message-ID: <old$index@saltos>\n\nHello\n";
            $this->assertSame('', $imap->Append($name, $message, [], $uid));
        }
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(9, $newemail);
        db_query('UPDATE app_emails_folders SET special_use = ? WHERE id = ?', ['', $trash_id]);
        $this->assertSame(T('The account has no trash folder'), $action([
            'action' => 'delete', 'folder_id' => $old,
        ]));
        db_query('UPDATE app_emails_folders SET special_use = ? WHERE id = ?', ['\Trash', $trash_id]);
        $this->assertSame(T('Folder deleted'), $action(['action' => 'delete', 'folder_id' => $old]));
        $query = 'SELECT COUNT(*) FROM app_emails_folders WHERE account_id = ? AND name LIKE ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id, 'Old%'])));
        $query = 'SELECT id, folder_id, imap_uid FROM app_emails WHERE account_id = ?';
        $rows2 = execute_query_array($query, [$account_id]);
        $this->assertCount(2, $rows2);
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('Trash'));
        $trash = $imap->Search('ALL');
        $folders2 = array_column($imap->ListFolders(), null, 'name');
        $this->assertSame('', $imap->Close());
        $this->assertArrayNotHasKey('Old', $folders2);
        foreach ($rows2 as $row) {
            $this->assertSame($trash_id, intval($row['folder_id']));
            $this->assertContains(intval($row['imap_uid']), $trash);
        }
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(9, $newemail);
        $this->assertSame($rows2, execute_query_array($query, [$account_id]));
        $ids = implode(',', array_column($rows2, 'id'));
        $this->assertSame(sprintf(T('%d email(s) deleted'), 2), getmail_delete($ids));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(0, intval(execute_query($queue, [$account_id])));

        // Send a reply from an IMAP account: the sent email is in the sent folder with its
        // Message-ID and without UID, and the original email gets the \Answered flag
        $message = "From: a@example.com\nTo: b@example.com\nSubject: Test 7\n" .
            "Message-ID: <test7@saltos>\n\nHello 7\n";
        $this->assertSame('', $imap->Open());
        $this->assertSame('', $imap->Append('INBOX', $message, ['\Seen'], $uid));
        $this->assertSame('', $imap->Close());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(10, $newemail);
        $query = 'SELECT id FROM app_emails WHERE account_id = ? AND imap_messageid = ?';
        $email_id = intval(execute_query($query, [$account_id, '<test7@saltos>']));
        $result = sendmail_action([
            'from' => $account_id,
            'to' => 'a@example.com',
            'subject' => 'Re: Test 7',
            'body' => 'Hello',
        ], 'reply', $email_id);
        $this->assertSame('ok', $result['status'], strval($result['text'] ?? ''));
        $query = 'SELECT id, folder_id, imap_uid, imap_messageid FROM app_emails
            WHERE account_id = ? AND is_outbox = 1';
        $row = execute_query($query, [$account_id]);
        $this->assertSame(intval($folders['Sent']['id']), intval($row['folder_id']));
        $this->assertSame(0, intval($row['imap_uid']));
        $this->assertStringStartsWith('<', $row['imap_messageid']);
        $this->assertSame(1, intval(execute_query($queue, [$account_id])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(0, intval(execute_query($queue, [$account_id])));
        $this->assertSame('', $imap->Open());
        $this->assertIsArray($imap->SelectFolder('INBOX'));
        $fetch = $imap->Fetch(strval($uid), 'FLAGS');
        $this->assertSame('', $imap->Close());
        $this->assertIsArray($fetch);
        $this->assertContains('\Answered', $fetch[$uid]['FLAGS']);
        $query = 'SELECT state_reply FROM app_emails WHERE id = ?';
        $this->assertSame(1, intval(execute_query($query, [$email_id])));

        // The sent email is appended in the sent folder of the server by the next
        // synchronization (the copy in the sent folder is enabled by default), and gets the
        // UID returned by the server, so it is not downloaded again
        $query = 'UPDATE app_emails_accounts SET smtp_host = ?, smtp_port = ?, smtp_extra = ?,
            smtp_user = ?, smtp_pass = ? WHERE id = ?';
        db_query($query, ['example.com', 465, 'ssl', 'pop3', 'admin', $account_id]);
        $result = sendmail_server();
        $this->assertStringContainsString('email(s) sended', strval(end($result)));
        $this->assertSame(1, intval(execute_query($queue, [$account_id])));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(0, intval(execute_query($queue, [$account_id])));
        $query = 'SELECT imap_uid, state_sent FROM app_emails WHERE id = ?';
        $row2 = execute_query($query, [$row['id']]);
        $this->assertSame(1, intval($row2['state_sent']));
        $this->assertGreaterThan(0, intval($row2['imap_uid']));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(10, $newemail);

        // A copy saved by the server before the append: the append finds it by the
        // Message-ID and learns its UID without appending another copy (enabled), or the
        // synchronization adopts it (disabled, for the servers that save the copy itself)
        $sent = [];
        foreach ([1, 0] as $append) {
            $query = 'UPDATE app_emails_accounts SET imap_append_sent = ? WHERE id = ?';
            db_query($query, [$append, $account_id]);
            $result = sendmail_action([
                'from' => $account_id,
                'to' => 'a@example.com',
                'subject' => "Fwd: Test 7 ($append)",
                'body' => 'Hello',
            ], 'forward', $email_id);
            $this->assertSame('ok', $result['status'], strval($result['text'] ?? ''));
            $result = sendmail_server();
            $this->assertStringContainsString('email(s) sended', strval(end($result)));
            $query = "SELECT COUNT(*) FROM app_emails_queue WHERE account_id = ? AND action = 'append'";
            $this->assertSame($append, intval(execute_query($query, [$account_id])));
            $query = 'SELECT id, uidl, imap_messageid FROM app_emails WHERE account_id = ? AND is_outbox = 1
                AND IFNULL(imap_uid, 0) = 0';
            $row3 = execute_query($query, [$account_id]);
            $file = get_directory('dirs/outboxdir') . $account_id . '/' . $row3['uidl'] . '.eml.gz';
            $message = "X-Copy: server\r\n" . file_get_contents('compress.zlib://' . $file);
            $this->assertSame('', $imap->Open());
            $this->assertSame('', $imap->Append('Sent', $message, ['\Seen'], $uid));
            $this->assertSame('', $imap->Close());
            $this->assertSame('', getmail_imap($account, $newemail));
            $this->assertSame(10, $newemail);
            $this->assertSame(0, intval(execute_query($queue, [$account_id])));
            $query = 'SELECT imap_uid FROM app_emails WHERE id = ?';
            $this->assertSame($uid, intval(execute_query($query, [$row3['id']])));
            $this->assertSame('', $imap->Open());
            $this->assertIsArray($imap->SelectFolder('Sent'));
            $found = $imap->Search('HEADER Message-ID ' . imap_class::Quote($row3['imap_messageid']));
            $this->assertSame([$uid], $found);
            $this->assertSame('', $imap->Close());
            $sent[] = $row3['id'];
        }
        db_query('UPDATE app_emails_accounts SET imap_append_sent = 1 WHERE id = ?', [$account_id]);

        $ids = implode(',', [$email_id, $row['id'], ...$sent]);
        $this->assertSame(sprintf(T('%d email(s) moved to trash'), 4), getmail_delete($ids));
        $this->assertSame(sprintf(T('%d email(s) deleted'), 4), getmail_delete($ids));
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(0, intval(execute_query($queue, [$account_id])));
        $query = 'SELECT COUNT(*) FROM app_emails WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));

        // The folders of the server: the special folders are locked, the subscribed folders
        // are synchronized and shown in the tree, and the unsubscribed folders lose its
        // emails in SaltOS, that stay in the server
        $result = emails_folders_server(['account_id' => $account_id]);
        $this->assertSame('ok', $result['status']);
        $list = array_column($result['folders'], null, 'name');
        $this->assertSame(1, $list['INBOX']['locked']);
        $this->assertSame(['subscribed' => 0, 'locked' => 0], [
            'subscribed' => $list['New']['subscribed'],
            'locked' => $list['New']['locked'],
        ]);
        $this->assertSame(
            ['status' => 'ko', 'text' => T('Only the IMAP accounts have folders in the server')],
            emails_folders_server(['account_id' => 1])
        );
        $this->assertSame(['status' => 'ko', 'text' => T('Permission denied')], emails_folders_server([]));
        $new_id = $list['New']['id'];
        $tree = function () use ($account_id) {
            $nodes = array_column(emails_folders_tree(), 'children', 'id');
            return array_column($nodes["$account_id/0"], 'id');
        };
        $this->assertNotContains("$account_id/$new_id", $tree());
        $result = $action(['action' => 'subscribe', 'ids' => strval($new_id)]);
        $this->assertSame(T('Folders updated'), $result);
        $this->assertContains("$account_id/$new_id", $tree());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(11, $newemail);
        $query = 'SELECT COUNT(*) FROM app_emails WHERE folder_id = ?';
        $this->assertSame(1, intval(execute_query($query, [$new_id])));
        $this->assertSame(T('Folders updated'), $action(['action' => 'subscribe', 'ids' => '']));
        $this->assertSame(0, intval(execute_query($query, [$new_id])));
        $this->assertNotContains("$account_id/$new_id", $tree());
        $this->assertSame('', getmail_imap($account, $newemail));
        $this->assertSame(11, $newemail);
        $this->assertSame(T('Only the IMAP accounts have folders in the server'), emails_folders_action([
            'action' => 'subscribe', 'account_id' => 1,
        ]));
        $this->assertSame(T('Permission denied'), emails_folders_action(['action' => 'subscribe']));

        // The special folders that not exist are created in the server, using the scripted
        // mock server: the root folder named Trash without special use is used as the trash,
        // the sent is created with its special use (CREATE-SPECIAL-USE) and subscribed, and
        // the creation is retried in the next synchronization if the server rejects it
        $fake = intval(execute_query('SELECT MAX(id) FROM app_emails_accounts')) + 1000;
        $login = [
            '* OK [CAPABILITY IMAP4rev1 SPECIAL-USE CREATE-SPECIAL-USE] ready', '<',
            'A0001 OK logged', '<',
            '* CAPABILITY IMAP4rev1 SPECIAL-USE CREATE-SPECIAL-USE', 'A0002 OK done', '<',
            '* LIST () "/" INBOX', '* LIST () "/" Trash', '* LIST (\\Junk) "/" Junk', 'A0003 OK done', '<',
            'A0004 OK done', '<',
        ];
        $scripts = [
            [['A0005 NO denied'], ''],
            [[], 'Read failed: connection closed by the server'],
            [['A0005 OK created', '<', 'A0006 OK subscribed'], ''],
        ];
        $query = 'SELECT name, special_use, is_subscribed FROM app_emails_folders
            WHERE account_id = ? ORDER BY name';
        foreach ($scripts as [$script, $error]) {
            $mock = $this->mock_start(array_merge($login, $script));
            $imap2 = new imap_class();
            $imap2->hostname = '127.0.0.1';
            $imap2->port = $mock[2];
            $imap2->timeout = 10;
            $this->assertSame('', $imap2->Open());
            $this->assertSame($error, __getmail_imap_folders($imap2, $fake));
            $imap2->Close();
            $this->mock_stop($mock);
        }
        $this->assertSame([
            ['INBOX', '\\Inbox', 1],
            ['Junk', '\\Junk', 1],
            ['Sent', '\\Sent', 1],
            ['Trash', '\\Trash', 1],
        ], array_map(
            fn ($row) => [$row['name'], $row['special_use'], intval($row['is_subscribed'])],
            execute_query_array($query, [$fake])
        ));
        db_query('DELETE FROM app_emails_folders WHERE account_id = ?', [$fake]);

        // Errors: wrong password
        $error = getmail_imap(['pop3_pass' => 'secret'] + $account, $newemail);
        $this->assertStringStartsWith('AUTHENTICATE failed', $error);

        // Remove the new account and its orphan folders, the deleted emails of the IMAP
        // accounts are not stored in the deletes table
        $query = 'SELECT COUNT(*) FROM app_emails_deletes WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));
        $result = delete('emails_accounts', $account_id);
        $this->assertSame('ok', $result['status']);
        emails_folders_check(1);
        $query = 'SELECT COUNT(*) FROM app_emails_folders WHERE account_id = ?';
        $this->assertSame(0, intval(execute_query($query, [$account_id])));

        set_data('server/lang', null);
        set_data('server/user', null);
        set_data('rest/0', null);
        set_data('rest/1', null);
    }
}
