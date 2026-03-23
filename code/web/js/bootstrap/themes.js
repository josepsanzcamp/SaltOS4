
/**
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Bootstrap helper module
 *
 * This fie contains useful functions related to the bootstrap widgets, allow to create widgets and
 * other plugins suck as plots or rich editors
 */

/**
 * Window match media
 *
 * This function returns an object intended to monitorize the bs_theme
 */
saltos.bootstrap.window_match_media = window.matchMedia('(prefers-color-scheme: dark)');

/**
 * Set data_bs_theme
 *
 * This function sets the data_bs_theme attribute to enable or disable the dark bs theme
 */
saltos.bootstrap.set_data_bs_theme = e => {
    document.documentElement.dataset.bsTheme = e.matches ? 'dark' : '';
};

/**
 * Check bs theme
 *
 * This function checks the bs theme
 *
 * @theme => Can be auto, light or dark
 */
saltos.bootstrap.check_bs_theme = theme => {
    const themes = ['auto', 'light', 'dark'];
    return themes.includes(theme);
};

/**
 * Set bs theme
 *
 * This function sets the bs theme
 *
 * @theme => Can be auto, light or dark
 */
saltos.bootstrap.set_bs_theme = theme => {
    if (!saltos.bootstrap.check_bs_theme(theme)) {
        throw new Error(`bs_theme ${theme} not found`);
    }
    saltos.bootstrap.window_match_media.removeEventListener(
        'change', saltos.bootstrap.set_data_bs_theme);
    switch (theme) {
        case 'auto':
            saltos.bootstrap.set_data_bs_theme(saltos.bootstrap.window_match_media);
            saltos.bootstrap.window_match_media.addEventListener(
                'change', saltos.bootstrap.set_data_bs_theme);
            break;
        case 'light':
            saltos.bootstrap.set_data_bs_theme({matches: false});
            break;
        case 'dark':
            saltos.bootstrap.set_data_bs_theme({matches: true});
            break;
    }
    saltos.storage.setItem('saltos.bootstrap.bs_theme', theme);
};

/**
 * Get bs theme
 *
 * Retrieve the bs_theme stored in the localStorage
 */
saltos.bootstrap.get_bs_theme = () => {
    return saltos.storage.getItem('saltos.bootstrap.bs_theme');
};

/**
 * TODO
 */
saltos.bootstrap.css_themes = {
    default: 'lib/bootstrap/bootstrap.min.css',
    black:   'lib/themes/dist/bootstrap.black.min.css',
    blue:    'lib/themes/dist/bootstrap.blue.min.css',
    cyan:    'lib/themes/dist/bootstrap.cyan.min.css',
    gray:    'lib/themes/dist/bootstrap.gray.min.css',
    green:   'lib/themes/dist/bootstrap.green.min.css',
    indigo:  'lib/themes/dist/bootstrap.indigo.min.css',
    orange:  'lib/themes/dist/bootstrap.orange.min.css',
    pink:    'lib/themes/dist/bootstrap.pink.min.css',
    purple:  'lib/themes/dist/bootstrap.purple.min.css',
    red:     'lib/themes/dist/bootstrap.red.min.css',
    teal:    'lib/themes/dist/bootstrap.teal.min.css',
    yellow:  'lib/themes/dist/bootstrap.yellow.min.css',
};

/**
 * Check css theme
 *
 * This function checks the css theme
 *
 * @theme => Can be default or one of the themes
 */
saltos.bootstrap.check_css_theme = theme => {
    if (theme in saltos.bootstrap.css_themes) {
        return saltos.bootstrap.css_themes[theme];
    }
    return '';
};

/**
 * Set css theme
 *
 * This function sets the css theme
 *
 * @theme => Can be default or one of the themes
 */
saltos.bootstrap.set_css_theme = theme => {
    const file = saltos.bootstrap.check_css_theme(theme);
    if (file === '') {
        throw new Error(`css_theme ${theme} not found`);
    }
    document.querySelectorAll('link[rel=preload][as=style]').forEach(item => {
        item.remove();
    });
    document.querySelectorAll('link[rel=stylesheet]').forEach(item => {
        const found1 = item.href.includes('bootstrap/bootstrap.min.css');
        const found2 = item.href.includes('themes/dist/bootstrap.') && item.href.includes('.min.css');
        if (found1 || found2) {
            const link = document.createElement('link');
            //~ link.rel = 'stylesheet';
            link.rel = 'preload';
            link.as = 'style';
            link.href = file;
            link.onload = () => {
                link.rel = 'stylesheet';
                link.removeAttribute('as');
                item.remove();
                saltos.storage.setItem('saltos.bootstrap.css_theme', theme);
            };
            document.head.appendChild(link);
        }
    });
};

/**
 * Get css theme
 *
 * Retrieve the css_theme stored in the localStorage
 */
saltos.bootstrap.get_css_theme = () => {
    return saltos.storage.getItem('saltos.bootstrap.css_theme');
};
