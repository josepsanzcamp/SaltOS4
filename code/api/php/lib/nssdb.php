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

/**
 * Directory helper
 *
 * This function returns the nssdb directory used by all functions
 */
function __nssdb_dir()
{
    $dir = get_directory('dirs/filesdir') ?? getcwd_protected() . '/data/files/';
    return $dir . 'nssdb';
}

/**
 * Passthru helper
 *
 * This function uses the ob_passthru and improve the output
 *
 * @cmd => command line to be executed
 */
function __nssdb_passthru($cmd)
{
    $output = ob_passthru($cmd);
    $output = str_replace("\r", '', $output);
    $output = explode("\n", $output);
    $output = array_values(array_diff($output, ['']));
    return $output;
}

/**
 * Init nssdb repo
 *
 * This function tries to initialize the nssdb component with an empty repo
 */
function __nssdb_init()
{
    if (!check_commands('certutil')) {
        return [];
    }
    $dir = __nssdb_dir();
    if (!file_exists($dir)) {
        mkdir($dir);
        chmod_protected($dir, 0777);
    }
    $files = glob($dir . '/*');
    if (count($files)) {
        return;
    }
    $output = __nssdb_passthru("certutil -N -d sql:$dir --empty-password 2>&1");
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        chmod_protected($file, 0666);
    }
    return $output;
}

/**
 * Add certificate
 *
 * This function adds the p12 file that must contains a valid certificate to the
 * nssdb repo using the pass if needed
 *
 * @file => the p12 file
 * @pass => the p12 password
 */
function __nssdb_add($file, $pass)
{
    if (!check_commands('pk12util')) {
        return [];
    }
    $dir = __nssdb_dir();
    file_put_contents($dir . '/pass.txt', $pass);
    $output = __nssdb_passthru("pk12util -i $file -d sql:$dir -w $dir/pass.txt 2>&1");
    unlink($dir . '/pass.txt');
    return $output;
}

/**
 * List certificates
 *
 * This function returns the list of valid nicks that you can use with pdfsig
 */
function __nssdb_list()
{
    if (!check_commands('pdfsig')) {
        return [];
    }
    $dir = __nssdb_dir();
    //~ $output = __nssdb_passthru("certutil -L -d sql:$dir 2>&1");
    $output = __nssdb_passthru("pdfsig -nssdir $dir -list-nicks 2>&1");
    return $output;
}

/**
 * Info of a certificate
 *
 * This function returns the info of the nick certificate of the nssdb repo
 *
 * @nick => the desired nick used to retrieve info
 */
function __nssdb_info($nick)
{
    if (!check_commands('certutil')) {
        return [];
    }
    $dir = __nssdb_dir();
    $output = __nssdb_passthru("certutil -L -d sql:$dir -n \"$nick\" -a 2>&1");
    $output = implode("\n", $output);
    $output1 = openssl_x509_parse($output, false);
    if (!is_array($output1)) {
        return [];
    }
    $output2 = strtoupper(implode(':', str_split(openssl_x509_fingerprint($output, 'sha1'), 2)));
    $output3 = strtoupper(implode(':', str_split(openssl_x509_fingerprint($output, 'sha256'), 2)));
    return array_merge($output1['subject'], ['sha1' => $output2, 'sha256' => $output3]);
}

/**
 * Pdf signature
 *
 * This function uses the pdfsig to add the signature to the pdf using the nick
 * certificate of the nssdb repo
 *
 * @nick   => the desired nick used to sign de pdf
 * @input  => the desired input file that you want to sign
 * @output => the signed pdf file
 */
function __nssdb_pdfsig($nick, $input, $output)
{
    if (!check_commands('pdfsig')) {
        return [];
    }
    $dir = __nssdb_dir();
    $output1 = __nssdb_passthru("pdfsig -nssdir $dir -add-signature -nick \"$nick\" $input $output 2>&1 |
        grep -v 'NSS_Shutdown failed: NSS could not shutdown. Objects are still in use.'");
    if (file_exists($output)) {
        chmod_protected($output, 0666);
    }
    $output2 = __nssdb_passthru("pdfsig $output 2>&1");
    return array_merge($output1, $output2);
}

/**
 * Remove certificate
 *
 * This function remove the certificate from the nssdb repo indentified by nick
 *
 * @nick => the desired nick that you want to remove
 */
function __nssdb_remove($nick)
{
    if (!check_commands('certutil') || !check_commands('pdfsig')) {
        return [];
    }
    $dir = __nssdb_dir();
    $output1 = __nssdb_passthru("certutil -D -d sql:$dir -n \"$nick\" 2>&1");
    //~ $output2 = __nssdb_passthru("certutil -L -d sql:$dir 2>&1");
    $output2 = __nssdb_passthru("pdfsig -nssdir $dir -list-nicks 2>&1");
    return array_merge($output1, $output2);
}

/**
 * Reset nssdb repo
 *
 * This function removes the nssdb repo, is the inverted of the init function
 */
function __nssdb_reset()
{
    $dir = __nssdb_dir();
    if (!file_exists($dir)) {
        return [];
    }
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        unlink($file);
    }
    rmdir($dir);
    return [];
}

/**
 * Update pdf
 *
 * This function creates a new pdf document using input as source and adding to
 * each page a new box with the important information about the certificate used
 * in the signature process
 *
 * @nick  => the desired nick used to sign de pdf
 * @input => the desired input pdf file where you want to add the cert info
 */
function __nssdb_update($nick, $input)
{
    //~ require_once 'lib/tcpdf/vendor/autoload.php';
    require_once 'lib/fpdi/vendor/autoload.php';

    $info0 = [
        'signedBy' => get_name_version_revision(),
        'nickName' => $nick,
    ];
    $info0 = array_map(fn($k, $v) => "$k = $v", array_keys($info0), $info0);
    $info0 = implode(' | ', $info0);
    $info = __nssdb_info($nick);
    $info1 = array_diff_key($info, ['sha1' => '', 'sha256' => '']);
    $info1 = array_map(fn($k, $v) => "$k = $v", array_keys($info1), $info1);
    $info1 = implode(' | ', $info1);
    $info2 = array_intersect_key($info, ['sha1' => '', 'sha256' => '']);
    $info2 = array_map(fn($k, $v) => "$k = $v", array_keys($info2), $info2);
    $info2 = implode(' | ', $info2);

    $pdf = new setasign\Fpdi\Tcpdf\Fpdi();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->setMargins(0, 0, 0);
    $pdf->SetFont('atkinsonhyperlegible', '', 6);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(204, 204, 204);
    $pdf->SetFillColor(204, 204, 204);

    $total = $pdf->setSourceFile($input);

    for ($i = 1; $i <= $total; $i++) {
        $pageId = $pdf->importPage($i);
        $pdf->AddPage();
        $pdf->useTemplate($pageId);

        $pdf->Rect(0, 0, 8, 297, 'DF');

        $pdf->StartTransform();
        $pdf->Rotate(90, 0, 297);
        $pdf->SetXY(0, 297);
        $pdf->MultiCell(297, 0, $info0 . "\n" . $info1 . "\n" . $info2 . "\n");
        $pdf->StopTransform();
    }

    $output = get_cache_file($input, '.pdf');
    $pdf->Output($output, 'F');
    chmod_protected($output, 0666);
    return $output;
}
