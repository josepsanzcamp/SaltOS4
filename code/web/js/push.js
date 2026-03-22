
/**
 *  ____        _ _    ___  ____    _  _    _
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / |
 * \___ \ / _` | | __| | | \___ \  | || |_ | |
 *  ___) | (_| | | |_| |_| |___) | |__   _|| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Push & favicon helper module
 *
 * This fie contains useful functions related to the push and favicon features
 */

/**
 * Push constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each
 * helper
 */
saltos.push = {
    executing: false,
    count: 60,
};

/**
 * Push helper
 *
 * This function tries to send the push request to the server with the latest
 * timestamp, and process the response depending the kind of response, too it
 * includes features to prevent concurrence and malfunctions
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
            saltos.push.count = 60;
            saltos.push.executing = false;
            if (!saltos.app.check_response(response)) {
                return;
            }
            for (const key in response) {
                const val = response[key];
                if (['success', 'danger'].includes(val.type)) {
                    saltos.app.toast('Notification', val.message, {color: val.type});
                } else if (['event'].includes(val.type)) {
                    saltos.window.send(val.message);
                } else {
                    throw new Error(`Unknown response type ${val.type}`);
                }
                saltos.push.timestamp = Math.max(saltos.push.timestamp, val.timestamp);
            }
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
 * Load setup
 *
 * This function is executed when window is loaded and defines the current
 * timestamp and launch the timer that executes periodically the function
 * each second
 */
window.addEventListener('load', async event => {
    if ('timestamp' in saltos.push) {
        throw new Error('saltos.push.timestamp found');
    }
    saltos.push.timestamp = saltos.core.timestamp();
    if ('interval' in saltos.push) {
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
 * Favicon constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each
 * helper
 */
saltos.favicon = {
    executing: false,
    count: 0,
};

/**
 * Favicon helper
 *
 * This function is intended to launch and stop the favicon animation depending
 * the bool argument
 *
 * @bool => true to run the animation and false to stop the animation
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
 * Visibility Change
 *
 * This function is executed where the vilibilitychange event is triggered and
 * the main task is to cancel the favicon animation
 */
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        saltos.favicon.fn(false);
    }
});

/**
 * Run
 *
 * This function run the favicon animation of the app is not visible
 */
saltos.favicon.run = () => {
    if (document.visibilityState !== 'visible') {
        saltos.favicon.fn(true);
    }
};
