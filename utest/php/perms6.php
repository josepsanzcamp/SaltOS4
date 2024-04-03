<?php

/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
 * More information in https://www.saltos.org or info@saltos.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

foreach (glob("php/autoload/*.php") as $file) {
    if (basename($file) == "zindex.php") {
        continue;
    }
    require $file;
}

pcov_start();
program_handlers();
init_timer();
init_random();
check_system();

global $_CONFIG;
$_CONFIG = eval_attr(xmlfiles2array(detect_config_files("xml/config.xml")));
db_connect();

set_data("server/token", file_get_contents("/tmp/phpunit.token"));
set_data("server/remote_addr", file_get_contents("/tmp/phpunit.remote_addr"));
set_data("server/user_agent", file_get_contents("/tmp/phpunit.user_agent"));
check_sql("customers", "view");
pcov_stop();
