<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function ob_passthru($cmd)
{
    ob_start();
    passthru("$cmd 2>&1");
    return ob_get_clean();
}

$files = glob('code/apps/*/locale/*/*.t2t');
$array = [];
foreach ($files as $file) {
    $temp = explode('/', $file);
    $lang = $temp[4];
    $group = $temp[2];
    $app = $temp[5];
    if (!isset($array[$lang])) {
        $array[$lang] = [];
    }
    if (!isset($array[$lang][$group])) {
        $array[$lang][$group] = [];
    }
    $array[$lang][$group][] = $app;
}

foreach ($array as $lang => $groups) {
    $output = strtolower("docs/user_$lang.t2t");
    $escape = str_replace('_', '\_', $lang);
    $title = "User Manual ($escape)";
    $rev = intval(ob_passthru('svnversion'));
    $date = date('F Y');
    $header = implode("\n", [
        $title,
        "SaltOS 4.0 r$rev",
        $date,
        '',
        '',
        '',
    ]);
    file_put_contents($output, $header);
    foreach ($groups as $group => $apps) {
        foreach ($apps as $app) {
            $file = "code/apps/$group/locale/$lang/$app";
            $buffer = file_get_contents($file);
            $buffer = explode("\n", $buffer);
            foreach ($buffer as $key => $val) {
                if (substr($val, 0, 1) == '=' && substr($val, -1, 1) == '=') {
                    $buffer[$key] = str_replace('=', '+', $val);
                }
                if (substr($val, 0, 1) == '[' && substr($val, -1, 1) == ']') {
                    $buffer[$key] = str_replace('../../../../../ujest', '../ujest', $val);
                }
            }
            $buffer = implode("\n", $buffer);
            file_put_contents($output, $buffer, FILE_APPEND);
        }
    }
    ob_passthru("php scripts/makepdf.php $output");
    ob_passthru("php scripts/makehtml.php $output");
}
