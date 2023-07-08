<?php

$buffer = file_get_contents("php://stdin");
// Remove comments
for (;;) {
    $pos1 = strpos($buffer, "<!--");
    if ($pos1 === false) {
        break;
    }
    $pos2 = strpos($buffer, "-->", $pos1);
    if ($pos2 === false) {
        break;
    }
    $buffer = substr($buffer, 0, $pos1) . substr($buffer, $pos2 + 3);
}
// Remove unnecessary doble quotes
$pos2 = -1;
for (;;) {
    $pos1 = strpos($buffer, '"', $pos2 + 1);
    if ($pos1 === false) {
        break;
    }
    $pos2 = strpos($buffer, '"', $pos1 + 1);
    if ($pos2 === false) {
        break;
    }
    $temp = substr($buffer, $pos1 + 1, $pos2 - $pos1 - 1);
    if ($temp == "" && substr($buffer, $pos1 - 1, 1) == "=") {
        $buffer = substr($buffer, 0, $pos1 - 1) . substr($buffer, $pos2 + 1);
    } elseif (strpos($temp, " ") === false) {
        $buffer = substr($buffer, 0, $pos1) . $temp . substr($buffer, $pos2 + 1);
    }
}
// Remove spaces between tags
$pos2 = -1;
for (;;) {
    $pos1 = strpos($buffer, '>', $pos2 + 1);
    if ($pos1 === false) {
        break;
    }
    $pos2 = strpos($buffer, '<', $pos1 + 1);
    if ($pos2 === false) {
        break;
    }
    $temp = substr($buffer, $pos1 + 1, $pos2 - $pos1 - 1);
    if (str_replace(array(" ","\n"), "", $temp) == "") {
        $buffer = substr($buffer, 0, $pos1 + 1) . substr($buffer, $pos2);
        $pos2 -= strlen($temp);
    }
}
// End
$buffer = trim($buffer);
echo $buffer;
