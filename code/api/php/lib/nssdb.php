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
    $dir = __nssdb_dir();
    $output = __nssdb_passthru("certutil -L -d sql:$dir -n \"$nick\" -r |
        openssl x509 -noout -fingerprint -alias -serial -dates -subject -issuer 2>&1");
    return $output;
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
        return;
    }
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        unlink($file);
    }
    rmdir($dir);
}
