<?php

if ($argc != 3) {
    echo "Error en los argumentos, debe ser fichero antiguo js y fichero nuevo js\n";
    die();
}

$file1 = $argv[1];
$file2 = $argv[2];

$buffer = file_get_contents($file1);
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
file_put_contents($file2, $buffer);
