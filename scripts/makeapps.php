<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

/**
 * Make the apps directory of the web
 *
 * This script creates the apps directory of the web using symbolic links to
 * the public contents of the real apps directory, the public contents are
 * all the js, mjs, js.map, css and pdf files and all the files of the files
 * directory of the tester app, they are linked one by one, in this way the
 * result only contains regular directories and links to files, a js file
 * is not published when its min.js equivalent exists, and the files
 * of the vendor directories are never published because they belong to the
 * php libraries installed by composer
 *
 * Usage: php scripts/makeapps.php code/apps code/web/apps
 */

function relative_path($from, $to)
{
    $from = explode('/', $from);
    $to = explode('/', $to);
    while (count($from) && count($to) && $from[0] === $to[0]) {
        array_shift($from);
        array_shift($to);
    }
    return str_repeat('../', count($from)) . implode('/', $to);
}

function remove_links($dir)
{
    foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
        $file = "$dir/$file";
        if (is_link($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            remove_links($file);
        } else {
            die("Error: $file is not a symbolic link\n");
        }
    }
    rmdir($dir);
}

if (!isset($argv) || !isset($argv[2])) {
    die("Usage: php scripts/makeapps.php <apps directory> <web apps directory>\n");
}
$source = realpath($argv[1]);
if ($source === false || !is_dir($source)) {
    die("Error: {$argv[1]} not found\n");
}
$target = rtrim($argv[2], '/');
if (is_link($target)) {
    unlink($target);
} elseif (is_dir($target)) {
    remove_links($target);
}
mkdir($target);
$target = realpath($target);

$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
    $source,
    FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
));
foreach ($iterator as $file) {
    $file = strval($file);
    if (str_starts_with($file, "$source/tester/files/")) {
        $files[] = $file;
        continue;
    }
    if (str_contains($file, '/vendor/')) {
        continue;
    }
    if (!preg_match('~\.(js|mjs|js\.map|css|pdf)$~', $file)) {
        continue;
    }
    if (str_ends_with($file, '.js') && file_exists(substr($file, 0, -3) . '.min.js')) {
        continue;
    }
    $files[] = $file;
}
sort($files);
foreach ($files as $file) {
    $link = $target . substr($file, strlen($source));
    if (!is_dir(dirname($link))) {
        mkdir(dirname($link), 0777, true);
    }
    symlink(relative_path(dirname($link), $file), $link);
    echo substr($link, strlen($target) + 1) . "\n";
}
