<?php

declare(strict_types=1);

$buffer = file_get_contents('php://stdin');
array_shift($argv);
$orig = array_shift($argv);
$dest = array_shift($argv);
$buffer = str_replace($orig, $dest, $buffer);
echo $buffer;
