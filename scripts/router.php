<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

/**
 * Router for the PHP built-in web server (php -S)
 *
 * php -S ignores the .htaccess files, this router reads them walking from the
 * document root to the requested file and applies the subset of directives used
 * by SaltOS (Options Indexes, DirectoryIndex, Files, FilesMatch and Require all)
 * like apache does, the rest of directives (AddType, SetEnvIf, ...) are ignored
 *
 * Usage: php -S 0.0.0.0:8080 -t code/web/ scripts/router.php
 */

function htaccess_parse($file)
{
    $config = ['require' => null, 'indexes' => null, 'index' => null, 'files' => []];
    $section = null;
    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        // Empty lines and comments, for example:
        // # These lines fix some issues in servers that do not send the content-type
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        // Opening of a Files or FilesMatch section, for example:
        // <Files "*.js">
        // <FilesMatch "^(index\.php)?$">
        if (preg_match('~^<(Files|FilesMatch)\s+"?([^">]*)"?\s*>$~i', $line, $matches)) {
            $section = ['match' => strtolower($matches[1]), 'pattern' => $matches[2], 'require' => null];
            continue;
        }
        // Closing of a Files or FilesMatch section, for example:
        // </Files>
        // </FilesMatch>
        if (preg_match('~^</Files(Match)?>$~i', $line)) {
            $config['files'][] = $section;
            $section = null;
            continue;
        }
        $words = preg_split('~\s+~', $line);
        $directive = strtolower($words[0]);
        // Require all, inside or outside a section, for example:
        // Require all denied
        // Require all granted
        if ($directive === 'require' && strtolower($words[1] ?? '') === 'all') {
            $require = strtolower($words[2] ?? '') === 'granted';
            if ($section !== null) {
                $section['require'] = $require;
            } else {
                $config['require'] = $require;
            }
        } elseif ($directive === 'options' && $section === null) {
            // Options outside a section, only Indexes is used, for example:
            // Options -Indexes
            foreach (array_slice($words, 1) as $option) {
                if (strcasecmp(ltrim($option, '+-'), 'Indexes') === 0) {
                    $config['indexes'] = $option[0] !== '-';
                }
            }
        } elseif ($directive === 'directoryindex' && $section === null) {
            // DirectoryIndex outside a section, for example:
            // DirectoryIndex index.php
            $config['index'] = array_slice($words, 1);
        }
    }
    return $config;
}

function htaccess_granted($configs, $basename)
{
    // First the Require outside sections, from the outer to the inner directory
    $granted = true;
    foreach ($configs as $config) {
        if ($config['require'] !== null) {
            $granted = $config['require'];
        }
    }
    // Next the Files and FilesMatch sections in order of appearance, the last match wins
    foreach ($configs as $config) {
        foreach ($config['files'] as $section) {
            if ($section['require'] === null) {
                continue;
            }
            if ($section['match'] === 'files') {
                $match = fnmatch($section['pattern'], $basename);
            } else {
                $match = preg_match('~' . str_replace('~', '\~', $section['pattern']) . '~', $basename);
            }
            if ($match) {
                $granted = $section['require'];
            }
        }
    }
    return $granted;
}

function htaccess_response($code, $title)
{
    http_response_code($code);
    echo "<!doctype html><html><head><title>$code $title</title></head>";
    echo "<body><h1>$title</h1></body></html>";
    return true;
}

$path = rawurldecode(strval(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
$parts = array_values(array_filter(explode('/', $path), fn($part) => $part !== ''));
if (in_array('..', $parts, true)) {
    return htaccess_response(400, 'Bad Request');
}

// Walk the directories collecting the .htaccess files like apache does
$dir = $_SERVER['DOCUMENT_ROOT'];
$file = $dir;
$configs = [];
$index = ['index.php', 'index.html'];
$indexes = true;
$exists = true;
foreach (array_merge($parts, ['']) as $key => $part) {
    if (file_exists("$dir/.htaccess")) {
        $config = htaccess_parse("$dir/.htaccess");
        $configs[] = $config;
        $index = $config['index'] ?? $index;
        $indexes = $config['indexes'] ?? $indexes;
    }
    if ($part === '') {
        break;
    }
    $file = "$dir/$part";
    if (!is_dir($file)) {
        // Avoid the php -S fallback to the index of the parent directories
        $exists = file_exists($file) && $key === count($parts) - 1;
        break;
    }
    $dir = $file;
}

if (is_dir($file)) {
    // The directory itself is checked with an empty basename, like apache
    if (!htaccess_granted($configs, '')) {
        return htaccess_response(403, 'Forbidden');
    }
    foreach ($index as $temp) {
        if (file_exists("$file/$temp")) {
            if (!htaccess_granted($configs, $temp)) {
                return htaccess_response(403, 'Forbidden');
            }
            return false;
        }
    }
    if (!$indexes) {
        return htaccess_response(403, 'Forbidden');
    }
    return htaccess_response(404, 'Not Found');
}

if (!htaccess_granted($configs, basename($file))) {
    return htaccess_response(403, 'Forbidden');
}
if (!$exists) {
    return htaccess_response(404, 'Not Found');
}
return false;
