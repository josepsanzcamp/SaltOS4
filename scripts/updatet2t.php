<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function ob_passthru($cmd)
{
    ob_start();
    passthru("$cmd 2>&1");
    return ob_get_clean();
}

// Prepare the files to use and output variables
array_shift($argv);
$temp = array_shift($argv);
$outdir = dirname($temp);
$outfile = basename($temp);

// Prepare the directory to work
if (!file_exists($outdir)) {
    mkdir($outdir);
}
chdir($outdir);

// Compute the svnversion and date
$rev = intval(ob_passthru('svnversion'));
$date = date('F Y');

// Get the source and process it
$buffer = file_get_contents($outfile);
$hash1 = md5($buffer);
$buffer = explode("\n", $buffer);
$buffer[1] = "SaltOS 4.0 r$rev";
$buffer[2] = $date;
$buffer = implode("\n", $buffer);
$hash2 = md5($buffer);
if ($hash1 != $hash2) {
    file_put_contents($outfile, $buffer);
}
