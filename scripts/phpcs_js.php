<?php

if ($argc != 4) {
    echo "Error en los argumentos, debe ser fichero js, fichero de cache y accion (a/b)\n";
    die();
}

$file1 = $argv[1];
$file2 = $argv[2];
$accion = $argv[3];

$buffer = file_get_contents($file1);
$buffer = explode("`", $buffer);

$pos = 1;
$count = count($buffer);
if ($count % 2 == 0) {
    $pos++;
}

if ($accion == "a") {
    $cache = array();
    for ($i = $pos; $i < $count; $i += 2) {
        $hash = md5($buffer[$i]);
        $key = "__HASH_${hash}__";
        $cache[$hash] = $buffer[$i];
        $buffer[$i] = $key;
    }
    file_put_contents($file2, serialize($cache));
}

if ($accion == "b") {
    $cache = unserialize(file_get_contents($file2));
    for ($i = $pos; $i < $count; $i += 2) {
        $key = $buffer[$i];
        $hash = str_replace(array("__HASH_","__"), "", $key);
        $buffer[$i] = $cache[$hash];
    }
    unlink($file2);
}

$buffer = implode("`", $buffer);
file_put_contents($file1, $buffer);
