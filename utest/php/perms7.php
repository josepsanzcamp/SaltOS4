<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 *
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects

foreach (glob('php/autoload/*.php') as $file) {
    if (basename($file) === 'zindex.php') {
        continue;
    }
    require $file;
}

pcov_start();
program_handlers();
init_timer();
init_random();

global $_CONFIG;
$_CONFIG = eval_attr(prepare_config_files(xmlfiles2array(detect_config_files('xml/config.xml'))));
db_connect();

set_data('server/token', file_get_contents('/tmp/phpunit.token'));
set_data('server/remote_addr', file_get_contents('/tmp/phpunit.remote_addr'));
set_data('server/user_agent', file_get_contents('/tmp/phpunit.user_agent'));
check_app_perm_id('customers', 'view', -1);
pcov_stop();
