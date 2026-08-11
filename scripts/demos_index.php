<?php

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

// Send waiting page
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generating demo...</title>
  <meta http-equiv="refresh" content="10;url=app/">
  <style>
    body { font-family: sans-serif; text-align: center; padding-top: 100px; background: #f8f9fa; }
    .loader { font-size: 1.5em; animation: blink 1s steps(1, start) infinite; }
    @keyframes blink { 50% { opacity: 0; } }
  </style>
</head>
<body>
  <h1>SaltOS4 - Preparing the demo</h1>
  <p class="loader">The demo is being generated, please wait a few seconds...</p>
  <p>You will be redirected automatically in a few seconds.</p>
HTML;

// Flush output immediately
@ob_end_flush(); @flush();

function ob_passthru($cmd)
{
    ob_start();
    passthru("$cmd 2>&1");
    return ob_get_clean();
}

if (!file_exists('scripts')) {
    echo 'scripts not found';
    die();
}
if (!file_exists('code')) {
    echo 'code not found';
    die();
}

$hash = md5($_SERVER['REMOTE_ADDR']);
if (!file_exists($hash)) {
    // Create the hash directory
    if (!mkdir($hash)) {
        echo 'mkdir error';
        die();
    }
    // Set permissions to the hash directory
    if (!chmod($hash, 0777)) {
        echo 'chmod error';
        die();
    }
    // Create the instance inside the hash directory
    if (!chdir($hash)) {
        echo 'chdir error';
        die();
    }
    ob_passthru('bash ../scripts/make_instance.sh');
    // The api/index.php will be replaced by a new file owned by apache
    rename('api/index.php', 'api/index.old.php');
    file_put_contents('api/index.php', "<?php include('index.old.php');");
    // Setting the sqlite configuration
    file_put_contents('data/files/config.xml', '<root><db><type>pdo_sqlite</type></db></root>');
    chmod('data/files/config.xml', 0777);
    // Execcute all setups
    ob_passthru('php api/index.php setup');
    ob_passthru('user=admin php api/index.php setup/certs');
    ob_passthru('user=admin php api/index.php setup/company');
    ob_passthru('user=admin php api/index.php setup/emails');
    ob_passthru('user=admin php api/index.php setup/crm');
    ob_passthru('user=admin php api/index.php setup/hr');
    ob_passthru('user=admin php api/index.php setup/purchases');
    ob_passthru('user=admin php api/index.php setup/sales');
    // Restore the old api/index.php
    unlink('api/index.php');
    rename('api/index.old.php', 'api/index.php');
}

// Redirects to the real access url
echo <<<HTML
  <script>
    window.location.href = "$hash/web/?user=admin&pass=admin";
  </script>
</body>
</html>
HTML;
die();
