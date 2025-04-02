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

// HTML Section
$file = str_replace('.t2t', '', $outfile);
ob_passthru("txt2tags --toc -t html -i ${file}.t2t -o ${file}.html");
$buffer = file_get_contents("${file}.html");
$buffer = explode("\n", $buffer);
$buffer0 = array_slice($buffer, 0, 20);
$buffer1 = [
    'pre{background-color:#e6e6e6;padding:5px 3px}',
];
$buffer2 = array_slice($buffer, 20);
$buffer = array_merge($buffer0, $buffer1, $buffer2);
$buffer = implode("\n", $buffer);
file_put_contents("${file}.html", $buffer);
//~ die();
