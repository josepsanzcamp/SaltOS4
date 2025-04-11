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

// Prepare the list of files to process
$files = [];
foreach ($argv as $path) {
    $temp = explode("\n", trim(ob_passthru("find $path -type f")));
    $files = array_merge($files, $temp);
}
sort($files);
$files = array_flip($files);
//~ $files = ["core/php/autoload/import.php" => ""];
//~ print_r($files);
//~ die();

// Process all files to prepare the data in a memory structure
$open = "/**\n";
$close = " */\n";
foreach ($files as $file => $temp) {
    $excludes = ['.htaccess', '.min.', 'utest/files/', 'utest/php/', 'ujest/snaps/'];
    $found = false;
    foreach ($excludes as $exclude) {
        if (strpos($file, $exclude) !== false) {
            $found = true;
            break;
        }
    }
    if ($found) {
        unset($files[$file]);
        continue;
    }
    $buffer = file_get_contents($file);
    $matches = [];
    $pos = strpos($buffer, $open);
    while ($pos !== false) {
        $pos2 = strpos($buffer, $close, $pos + strlen($open));
        $pos3 = strpos($buffer, "\n", $pos2 + strlen($close));
        if ($pos2 === false || $pos3 === false) {
            echo "Internal error processing $file\n";
            die();
        }
        $a = trim(substr($buffer, $pos2 + strlen($close), $pos3 - $pos2 - strlen($close)));
        if (substr($a, -5, 5) == ' => {') {
            $a = substr($a, 0, -5);
        }
        if ($a == '(()') {
            $a = '';
        }
        $b = trim(substr($buffer, $pos, $pos2 - $pos + strlen($close)));
        $b = explode("\n", $b);
        array_shift($b);
        array_pop($b);
        foreach ($b as $key => $val) {
            $val = trim($val);
            if ($val == '*') {
                $val = '';
            } elseif (substr($val, 0, 2) == '* ') {
                $val = substr($val, 2);
            }
            if (in_array(substr($val, 0, 1), ['@', '+', '-']) && !in_array(substr($val, 1, 1), ['', ' '])) {
                $val = "- $val";
            }
            $b[$key] = $val;
        }
        $c = array_shift($b);
        array_shift($b);
        // To add newlines to break the lists
        $is_inside_list = false;
        foreach ($b as $key => $val) {
            if (substr($val, 0, 1) == '-') {
                $is_inside_list = true;
            } elseif ($val == '' && $is_inside_list) {
                $b[$key] = "\n";
                $is_inside_list = false;
            }
        }
        // Continue
        $b = implode("\n", $b);
        $matches[] = [$c, $a, $b];
        $pos = strpos($buffer, $open, $pos2 + strlen($close));
    }
    array_shift($matches);
    if (!count($matches)) {
        echo "Internal error processing $file\n";
        die();
    }
    $files[$file] = $matches;
}
//~ print_r($files);
//~ print_r(array_keys($files));
//~ die();

// T2T section
ob_start();
$title = str_replace('.t2t', '', $outfile);
$map = [
    'apps' => 'applications',
    'web' => 'web client',
    'utest' => 'phpunit test',
    'ujest' => 'jest test',
];
if (isset($map[$title])) {
    $title = $map[$title];
}
$title .= ' documentation';
echo ucwords($title) . "\n";
$rev = intval(ob_passthru('svnversion'));
echo "SaltOS 4.0 r$rev\n";
$date = date('F Y');
echo "$date\n";
echo "\n";
echo "\n";
echo "\n";
$path = '';
$map = [
    'action' => 'actions',
    'js' => 'javascript',
    'lib' => 'libraries',
    'utest lib' => 'phpunit libraries',
    'utest code' => 'phpunit code',
    'ujest lib' => 'jest libraries',
    'ujest code' => 'jest code',
];
foreach ($files as $file => $contents) {
    if (substr($file, 0, 13) == 'code/api/php/') {
        $path2 = explode('/', $file)[3];
    } elseif (substr($file, 0, 12) == 'code/web/js/') {
        $path2 = explode('/', $file)[2];
    } elseif (substr($file, 0, 10) == 'code/apps/') {
        $path2 = explode('/', $file)[2];
    } elseif (substr($file, 0, 10) == 'utest/lib/') {
        $path2 = 'utest lib';
    } elseif (substr($file, 0, 6) == 'utest/') {
        $path2 = 'utest code';
    } elseif (substr($file, 0, 10) == 'ujest/lib/') {
        $path2 = 'ujest lib';
    } elseif (substr($file, 0, 6) == 'ujest/') {
        $path2 = 'ujest code';
    } else {
        ob_clean();
        echo "Internal error processing $file\n";
        die();
    }
    if (isset($map[$path2])) {
        $path2 = $map[$path2];
    }
    $path2 = ucwords($path2);
    if ($path != $path2) {
        echo "+$path2+\n";
        echo "\n";
        $path = $path2;
    }
    $first = true;
    foreach ($contents as $content) {
        if ($first) {
            echo "++{$content[0]}++\n";
            echo "\n";
            $content[1] = strrev(dirname(strrev($file)));
            $first = false;
        } else {
            echo "+++{$content[0]}+++\n";
            echo "\n";
        }
        if ($content[1] != '') {
            echo "```\n";
            echo "{$content[1]}\n";
            echo "```\n";
            echo "\n";
        }
        if ($content[2] != '') {
            echo "{$content[2]}\n";
            echo "\n";
        }
        echo "\n";
    }
}
$buffer = ob_get_clean();
//~ echo $buffer;
//~ die();

// Prepare the directory to work
if (!file_exists($outdir)) {
    mkdir($outdir);
}
chdir($outdir);

// Write the t2t file
file_put_contents($outfile, $buffer);
//~ die();
