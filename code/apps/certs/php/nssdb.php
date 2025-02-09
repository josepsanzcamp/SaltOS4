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
function __nssdb_dir_helper()
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
function __nssdb_passthru_helper($cmd)
{
    $output = ob_passthru($cmd);
    $output = str_replace("\r", '', $output);
    $output = explode("\n", $output);
    $output = array_values(array_diff($output, ['']));
    return $output;
}

/**
 * Grep helper
 *
 * This function emulates the grep command, is able to invert the pattern
 * selection and returns the same array with the grep applied
 *
 * @input   => the input array
 * @pattern => the search pattern
 * @invert  => default to false to search, true to invert the selection
 */
function __nssdb_grep_helper($input, $pattern, $invert = false)
{
    $pattern = iconv('UTF-8', 'ASCII//TRANSLIT', $pattern);
    foreach ($input as $key => $val) {
        $val = iconv('UTF-8', 'ASCII//TRANSLIT', $val);
        $pos = stripos($val, $pattern);
        if (!$invert && $pos === false) {
            unset($input[$key]);
        } elseif ($invert && $pos !== false) {
            unset($input[$key]);
        }
    }
    $input = array_values($input);
    return $input;
}

/**
 * Init nssdb repo
 *
 * This function tries to initialize the nssdb component with an empty repo
 */
function __nssdb_init()
{
    if (!check_commands('certutil')) {
        return ['certutil not found'];
    }
    $dir = __nssdb_dir_helper();
    if (!file_exists($dir)) {
        mkdir($dir);
        chmod_protected($dir, 0777);
    }
    $files = glob($dir . '/*');
    if (count($files)) {
        return ["files found in $dir"];
    }
    $output = __nssdb_passthru_helper("certutil -N -d sql:$dir --empty-password 2>&1");
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        chmod_protected($file, 0666);
    }
    return $output;
}

/**
 * Create certificate
 *
 * This function create a dummy self signed certificate intended to be used in
 * test cases
 *
 * $outfile => the p12 file
 * $outpass => the password used by the p12 file
 */
function __nssdb_create($outfile, $outpass, $subject = '', $name = '')
{
    if (!check_commands('openssl')) {
        return ['openssl not found'];
    }
    if (!$subject) {
        $subject = '/C=ES/serialNumber=ABCDE-12345678X/O=12345678X/CN=THE SALTOS PROJECT';
    }
    if (!$name) {
        $name = 'THE SALTOS PROJECT - 12345678X';
    }
    $privfile = dirname($outfile) . '/private.key';
    $privpass = $outpass;
    $certfile = dirname($outfile) . '/certificate.crt';
    // phpcs:disable Generic.Files.LineLength
    $output1 = __nssdb_passthru_helper("openssl genpkey -algorithm RSA -out $privfile -aes256 -pass pass:$privpass 2>&1");
    $output2 = __nssdb_passthru_helper("openssl req -new -x509 -key $privfile -out $certfile -days 365 -utf8 -subj \"$subject\" -passin pass:$privpass 2>&1");
    $output3 = __nssdb_passthru_helper("openssl pkcs12 -export -out $outfile -inkey $privfile -in $certfile -name \"$name\" -passin pass:$privpass -passout pass:$outpass 2>&1");
    // phpcs:enable Generic.Files.LineLength
    unlink($privfile);
    unlink($certfile);
    return array_merge($output1, $output2, $output3);
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
        return ['pk12util not found'];
    }
    $dir = __nssdb_dir_helper();
    file_put_contents($dir . '/pass.txt', $pass);
    $output = __nssdb_passthru_helper("pk12util -i $file -d sql:$dir -w $dir/pass.txt 2>&1");
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
        return ['pdfsig not found'];
    }
    $dir = __nssdb_dir_helper();
    $output = __nssdb_passthru_helper("pdfsig -nssdir $dir -list-nicks 2>&1");
    $output = __nssdb_grep_helper($output, 'NSS_Shutdown failed', true);
    $output = __nssdb_grep_helper($output, 'There are no certificates available.', true);
    $output = __nssdb_grep_helper($output, 'Certificate nicknames available:', true);
    return $output;
}

/**
 * Info of a certificate
 *
 * This function returns the info of the nick certificate of the nssdb repo
 *
 * @nick => the desired nick used to retrieve info
 */
function __nssdb_info($nick, $shortnames = false)
{
    if (!check_commands('certutil')) {
        return ['certutil not found'];
    }
    $dir = __nssdb_dir_helper();
    $cert = __nssdb_passthru_helper("certutil -L -d sql:$dir -n \"$nick\" -a 2>&1");
    $cert = implode("\n", $cert);
    $array = openssl_x509_parse($cert, $shortnames);
    if (!is_array($array)) {
        return ['openssl_x509_parse output error'];
    }
    $pubkey = openssl_pkey_get_public($cert);
    if (!$pubkey) {
        return ['openssl_pkey_get_public output error'];
    }
    $details = openssl_pkey_get_details($pubkey);
    if (!is_array($details) || !isset($details['key'])) {
        return ['openssl_pkey_get_details output error'];
    }
    $remove = ['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'];
    $binkey = base64_decode(str_replace($remove, '', $details['key']));
    return [
        'subject' => $array['subject'],
        'info' => [
            'serialNumber' => strtoupper(implode(':', str_split($array['serialNumberHex'], 2))),
            'validFrom' => date('Y-m-d H:i:s', $array['validFrom_time_t']),
            'validTo' => date('Y-m-d H:i:s', $array['validTo_time_t']),
            'signatureType' => $array['signatureTypeSN'],
            'md5' => strtoupper(implode(':', str_split(hash('md5', $binkey), 2))),
            'sha1' => strtoupper(implode(':', str_split(hash('sha1', $binkey), 2))),
            'sha256' => strtoupper(implode(':', str_split(hash('sha256', $binkey), 2))),
        ],
    ];
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
        return ['pdfsig not found'];
    }
    $dir = __nssdb_dir_helper();
    // phpcs:disable Generic.Files.LineLength
    $output1 = __nssdb_passthru_helper("pdfsig -nssdir $dir -add-signature -nick \"$nick\" $input $output 2>&1");
    // phpcs:enable Generic.Files.LineLength
    if (file_exists($output)) {
        chmod_protected($output, 0666);
    }
    $output1 = __nssdb_grep_helper($output1, 'NSS_Shutdown failed', true);
    $output2 = __nssdb_passthru_helper("pdfsig $output 2>&1");
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
    if (!check_commands('certutil')) {
        return ['certutil not found'];
    }
    $dir = __nssdb_dir_helper();
    $output = __nssdb_passthru_helper("certutil -D -d sql:$dir -n \"$nick\" 2>&1");
    return $output;
}

/**
 * Reset nssdb repo
 *
 * This function removes the nssdb repo, is the inverted of the init function
 */
function __nssdb_reset()
{
    $dir = __nssdb_dir_helper();
    if (!file_exists($dir)) {
        return ["$dir not found"];
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
    require_once 'lib/tcpdf/vendor/autoload.php';
    require_once 'lib/fpdi/vendor/autoload.php';
    require_once 'php/lib/color.php';

    $info = __nssdb_info($nick, true);

    $info0 = [
        'nickName' => $nick,
        'validFrom' => $info['info']['validFrom'],
        'validTo' => $info['info']['validTo'],
    ];
    $info0 = array_map(fn($k, $v) => "$k = $v", array_keys($info0), $info0);
    $info0 = implode(' | ', $info0);

    $info1 = $info['subject'];
    $info1 = array_map(fn($k, $v) => "$k = $v", array_keys($info1), $info1);
    $info1 = implode(' | ', $info1);

    $info2 = [
        'sha256' => $info['info']['sha256'],
        'signedBy' => get_name_version_revision(),
    ];
    $info2 = array_map(fn($k, $v) => "$k = $v", array_keys($info2), $info2);
    $info2 = implode(' | ', $info2);

    $image_file = 'img/logo_grey.png';
    $image_size = getimagesize($image_file);
    $image_width = 9;
    $image_height = 9 * $image_size[1] / $image_size[0];
    $image_sep = $image_height / 9;

    $pdf = new setasign\Fpdi\Tcpdf\Fpdi();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->setMargins(0, 0, 0);
    $pdf->SetFont('atkinsonhyperlegible', '', 6);
    $pdf->SetTextColor(0, 0, 0);

    $color = '#e5dbda';
    $color = [
        'R' => color2dec($color, 'R'),
        'G' => color2dec($color, 'G'),
        'B' => color2dec($color, 'B'),
    ];
    $pdf->SetDrawColor($color['R'], $color['G'], $color['B']);
    $pdf->SetFillColor($color['R'], $color['G'], $color['B']);

    $total_pages = $pdf->setSourceFile($input);
    for ($page_num = 1; $page_num <= $total_pages; $page_num++) {
        $page_id = $pdf->importPage($page_num);
        $page_size = $pdf->getTemplateSize($page_id);
        $pdf->AddPage($page_size['orientation'], [$page_size['width'], $page_size['height']]);
        $pdf->useTemplate($page_id);

        $page_height = $page_size['height'];
        $pdf->Rect(0, 0, $image_width, $page_height, 'DF');

        $total_images = intval(($page_height - $image_sep) / ($image_height + $image_sep));
        $image_incr = $page_height - $image_sep - $total_images * ($image_height + $image_sep);
        $image_incr /= $total_images;
        $pos_y = $image_sep;
        for ($i = 0; $i < $total_images; $i++) {
            $pdf->Image($image_file, 0, $pos_y, $image_width, $image_height);
            $pos_y += $image_height + $image_sep + $image_incr;
        }

        $pdf->StartTransform();
        $pdf->Rotate(90, 0, $page_height);
        $pdf->SetXY(0, $page_height);
        $pdf->Cell(0, 0, $info0, 0, 1);
        $pdf->Cell(0, 0, $info1, 0, 1);
        $pdf->Cell(0, 0, $info2, 0, 1);
        $pdf->StopTransform();
    }

    $output = get_cache_file($input, '.pdf');
    $pdf->Output($output, 'F');
    chmod_protected($output, 0666);
    return $output;
}
