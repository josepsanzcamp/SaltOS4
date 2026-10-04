<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

/**
 * Synchronize the public directory of the demos with the private directory
 *
 * The private directory contains the full instances, one directory for each
 * hash, and the public directory, that is the document root, contains one
 * link for each hash that points to the web directory of the instance, the
 * private directory is the source: this function creates the links of the
 * new instances and removes the links of the instances that not exist
 *
 * Usage: php scripts/demos_sync.php, executed from the directory that contains
 * the private and public directories, too is used by demos_index.php and by
 * demos_trash.php when an instance is created or removed
 *
 * @private => the directory that contains the instances
 * @public  => the directory that contains the links
 */
function demos_sync($private, $public)
{
    foreach (glob("$private/*/web", GLOB_ONLYDIR) as $web) {
        $hash = basename(dirname($web));
        if (strlen($hash) === 32 && !is_link("$public/$hash")) {
            symlink("../private/$hash/web", "$public/$hash");
        }
    }
    foreach (glob("$public/*") as $link) {
        if (strlen(basename($link)) === 32 && is_link($link) && !file_exists($link)) {
            unlink($link);
        }
    }
}

if (isset($argv) && realpath($argv[0]) === __FILE__) {
    demos_sync('private', 'public');
}
