<?php

declare(strict_types=1);

$buffer = file_get_contents("php://stdin");
$buffer = explode("\n", $buffer);
array_shift($argv);
$orig = array_shift($argv);
foreach ($buffer as $key => $val) {
    if (strpos($val, $orig) === false) {
        continue;
    }
    $buffer[$key] = [];
    while ($temp = array_shift($argv)) {
        $buffer[$key][] = str_replace($orig, $temp, $val);
    }
    $buffer[$key] = implode("\n", $buffer[$key]);
}
$buffer = implode("\n", $buffer);
echo $buffer;
