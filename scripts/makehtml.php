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
if (!isset($argv)) {
    die();
}
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

// /Add some pre style
$line = array_search('</style>', $buffer, true);
if ($line === false) {
    echo "Internal error!!!\n";
    die();
}
$buffer0 = array_slice($buffer, 0, $line);
$buffer1 = [
    'pre{background-color:#e6e6e6;padding:5px 3px}',
];
$buffer2 = array_slice($buffer, $line);
$buffer = array_merge($buffer0, $buffer1, $buffer2);

// Embed images
foreach ($buffer as $key => $val) {
    if (substr($val, 0, 5) !== '<img ') {
        continue;
    }
    $val = explode(' ', $val);
    foreach ($val as $key2 => $val2) {
        $val2 = explode('=', $val2);
        if ($val2[0] !== 'src') {
            continue;
        }
        if (substr($val2[1], 0, 1) === '"' && substr($val2[1], -1, 1) === '"') {
            $image = substr($val2[1], 1, -1);
            $type = 'image/png';
            $data = base64_encode(file_get_contents($image));
            $inline = "data:$type;base64,$data";
            $buffer[$key] = str_replace($image, $inline, $buffer[$key]);
        }
    }
}

// Finish
$buffer = implode("\n", $buffer);
file_put_contents("${file}.html", $buffer);
