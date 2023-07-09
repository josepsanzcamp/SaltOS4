<?php

$buffer = file_get_contents("php://stdin");
$buffer = explode("\n", $buffer);
foreach ($buffer as $key => $val) {
    if (strpos($val, "js/index.min.js") === false) {
        continue;
    }
    array_shift($argv);
    $buffer[$key] = array();
    while ($temp = array_shift($argv)) {
        $buffer[$key][] = str_replace("js/index.min.js", $temp, $val);
    }
    $buffer[$key] = implode("\n", $buffer[$key]);
}
$buffer = implode("\n", $buffer);
echo $buffer;
