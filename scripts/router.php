<?php

/**
 * Router for the PHP built-in web server (php -S)
 *
 * When the requested file does not exist, php -S returns the index of the
 * nearest parent directory instead of a 404 error, and when the requested
 * directory does not have an index, php -S returns a 404 error instead of
 * a 403 error, this router returns the same errors that apache or nginx,
 * the rest is served by php -S as usual
 *
 * Usage: php -S 0.0.0.0:8080 -t code/web/ scripts/router.php
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

function router_error($code, $title)
{
    http_response_code($code);
    echo "<!doctype html><html><head><title>$code $title</title></head>";
    echo "<body><h1>$title</h1></body></html>";
    return true;
}

$path = rawurldecode(strval(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
$file = $_SERVER['DOCUMENT_ROOT'] . $path;
if (in_array('..', explode('/', $path), true) || !file_exists($file)) {
    return router_error(404, 'Not Found');
}
if (is_dir($file) && !file_exists("$file/index.php") && !file_exists("$file/index.html")) {
    return router_error(403, 'Forbidden');
}
return false;
