<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
$file = "vendor/browscap/browscap-php/resources/cache.sqlite";
touch($file);
$db = new PDO("sqlite:$file");
$adapter = new MatthiasMullie\Scrapbook\Adapters\SQLite($db);
$cache = new MatthiasMullie\Scrapbook\Psr16\SimpleCache($adapter);
$logger = new \Monolog\Logger('name');
$bc = new \BrowscapPHP\BrowscapUpdater($cache, $logger);
$bc->update(BrowscapPHP\Helper\IniLoaderInterface::PHP_INI_LITE);
