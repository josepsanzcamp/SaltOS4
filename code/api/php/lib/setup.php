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

/**
 * Setup helper module
 *
 * This file contains useful functions related to the setup process
 */

/**
 * TODO
 *
 * TODO
 */
function __setup()
{
    $array = [
        'tbl_users' => [
            [
                'id' => 1,
                'active' => 1,
                'group_id' => 1,
                'login' => 'admin',
                'name' => 'Admin',
                'description' => 'Admin user',
                'start' => '00:00:00',
                'end' => '23:59:59',
                'days' => '1111111',
            ],
        ],
        'tbl_users_passwords' => [
            [
                'id' => 1,
                'active' => 1,
                'user_id' => 1,
                'remote_addr' => '',
                'user_agent' => '',
                'password' => '21232f297a57a5a743894a0e4a801fc3',
                'expires_at' => '9999-99-99 99:99:99',
            ],
        ],
        'tbl_groups' => [
            [
                'id' => 1,
                'active' => 1,
                'code' => 'admin',
                'name' => 'Admin',
                'description' => 'Admin group',
            ],
        ],
    ];

    return [];
}
