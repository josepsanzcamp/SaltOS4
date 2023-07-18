<?php

declare(strict_types=1);

if ($argc != 3) {
    echo "Arguments error, mut to provide the standard argument and the file to process\n";
    die();
}

$standard = $argv[1];
$file = $argv[2];

// First part => prepare the file to phpcs and execute it
$buffer = file_get_contents($file);
$buffer = explode("`", $buffer);

$pos = 1;
$count = count($buffer);
if ($count % 2 == 0) {
    $pos++;
}

for ($i = $pos; $i < $count; $i += 2) {
    $hash = md5($buffer[$i]);
    $key = "__HASH_${hash}__";
    $buffer[$i] = $key;
}

$buffer = implode("`", $buffer);
file_put_contents("$file.tmp.js", $buffer);
passthru("phpcs $standard $file.tmp.js");
unlink("$file.tmp.js");

// Second part => check the space after comma
$buffer = file_get_contents($file);
$buffer = explode("\n", $buffer);

foreach ($buffer as $key => $val) {
    $val = trim($val);
    // Exceptions
    if ($val == "") {
        continue;
    }
    $exceptions = [
        '|____/ \__,_|_|\__|\___/|____/     |_|(_)___/',
        'join(",")',
        'split(",")',
        'mapToRadix: [","]',
        'https://www.saltos.org',
        'https://www.gnu.org/licenses',
        '*::before,',
        '*::after {',
        ' + ":" + ',
    ];
    $found = false;
    foreach ($exceptions as $exception) {
        if (strpos($val, $exception) !== false) {
            $found = true;
            break;
        }
    }
    if ($found) {
        continue;
    }
    // Normal operation
    $patterns = [",", ":"];
    foreach ($patterns as $pattern) {
        $val2 = explode($pattern, $val);
        unset($val2[0]);
        $error = 0;
        foreach ($val2 as $val3) {
            if (!strlen($val3)) {
                continue;
            }
            if (substr($val3, 0, 1) != " ") {
                $error = 1;
                break;
            }
        }
        if ($error) {
            $key++;
            echo "$file:$key:$val\n";
        }
    }
}
