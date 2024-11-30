
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

'use strict';

/**
 * Push & favicon helper module
 *
 * This fie contains useful functions related to the push and favicon features
 */

/**
 * TODO
 *
 * TODO
 */
saltos.push = {
    executing: false,
    count: 60,
};

/**
 * TODO
 *
 * TODO
 */
saltos.push.fn = () => {
    if (saltos.push.executing) {
        return;
    }
    if (!saltos.token.get()) {
        return;
    }
    if (!navigator.onLine) {
        return;
    }
    saltos.push.count--;
    if (saltos.push.count >= 0) {
        return;
    }
    saltos.push.executing = true;
    saltos.core.ajax({
        url: 'api/?/push/get/' + saltos.push.timestamp,
        success: response => {
            if (!saltos.app.check_response(response)) {
                return;
            }
            for (const key in response) {
                const val = response[key];
                if (['success', 'danger'].includes(val.type)) {
                    saltos.app.toast('Notification', val.message, {color: val.type});
                } else if (['event'].includes(val.type)) {
                    saltos.window.send(val.message);
                    saltos.favicon.run();
                } else {
                    throw new Error(`Unknown response type ${val.type}`);
                }
                saltos.push.timestamp = Math.max(saltos.push.timestamp, val.timestamp);
            }
            saltos.push.count = 60;
            saltos.push.executing = false;
        },
        error: error => {
            saltos.push.count = 60;
            saltos.push.executing = false;
        },
        abort: error => {
            saltos.push.count = 60;
            saltos.push.executing = false;
        },
        token: saltos.token.get(),
        lang: saltos.gettext.get(),
        proxy: 'no',
    });
};

/**
 * TODO
 *
 * TODO
 */
window.addEventListener('load', async event => {
    if (saltos.push.hasOwnProperty('timestamp')) {
        throw new Error('saltos.push.timestamp found');
    }
    saltos.push.timestamp = saltos.core.timestamp();
    if (saltos.push.hasOwnProperty('interval')) {
        throw new Error('saltos.push.interval found');
    }
    saltos.push.interval = setInterval(saltos.push.fn, 1000);
});

/**
 * Online sync
 *
 * This function send a push request when navigator detects an online change
 */
window.addEventListener('online', event => {
    saltos.push.count = 5;
});

/**
 * TODO
 *
 * TODO
 */
saltos.favicon = {
    executing: false,
    count: 0,
};

/**
 * TODO
 *
 * TODO
 */
saltos.favicon.fn = bool => {
    if (bool && !saltos.favicon.executing) {
        const icons = ['img/logo_revers.svg', 'img/logo_black.svg'];
        saltos.favicon.interval = setInterval(() => {
            document.querySelector('link[rel=icon]').href = icons[saltos.favicon.count];
            saltos.favicon.count = (saltos.favicon.count + 1) % icons.length;
        }, 1000);
        saltos.favicon.executing = true;
    }
    if (!bool && saltos.favicon.executing) {
        clearInterval(saltos.favicon.interval);
        document.querySelector('link[rel=icon]').href = 'img/logo_red.svg';
        saltos.favicon.count = 0;
        saltos.favicon.executing = false;
    }
};

/**
 * TODO
 *
 * TODO
 */
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        saltos.favicon.fn(false);
    }
});

/**
 * TODO
 *
 * TODO
 */
saltos.favicon.run = () => {
    if (document.visibilityState !== 'visible') {
        saltos.favicon.fn(true);
    }
};
