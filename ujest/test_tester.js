
/**
 * @jest-environment node
 *
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
 * Tester unit tests
 *
 * This file contains the tester unit tests
 */

/**
 * Puppeteer setup
 *
 * This lines contain the needed setup for run puppeteer and take screenshots
 */
const pti = require('puppeteer-to-istanbul');
const toMatchImageSnapshot = require('jest-image-snapshot').toMatchImageSnapshot;
expect.extend({toMatchImageSnapshot});
const timeout = {timeout: 3000};
const sharp = require('sharp');

/**
 * Global variables
 *
 * This variables contains the browser and page links
 */
let puppeteer;
let browser;
let page;

/**
 * Before All
 *
 * This function contains all code executed before all tests, in this case the
 * features provided by this function includes the launch of the browser, set
 * the screen size and start the javascript coverage
 *
 * puppeteer is loaded here via dynamic import because since v25 it ships
 * as an ESM-only package and require() can no longer load it
 */
beforeAll(async () => {
    puppeteer = (await import('puppeteer')).default;
    browser = await puppeteer.launch({
        executablePath: '/usr/bin/chromium',
        args: ['--ignore-certificate-errors'],
    });
    page = await browser.newPage();
    await page.setViewport({width: 1920, height: 1080});
    await page.coverage.startJSCoverage({
        resetOnNavigation: false,
        reportAnonymousScripts: false,
        includeRawScriptCoverage: false,
        useBlockCoverage: true,
    });
});

/**
 * After All
 *
 * This function contains all code executed after all tests, in this case the
 * features provided by this function include the stop of the javsacript coverage
 * recording, the save feature to the desired storage path and the browser close
 */
afterAll(async () => {
    const jsCoverage = await page.coverage.stopJSCoverage();
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/tester'});
    await browser.close();
});

/**
 * App Tester
 *
 * This test is intended to validate the correctness of the tester application
 * by execute the init, disabled, enabled, all bs_themes and all css_themes and
 * validate with the expected screenshot
 */
describe('App Tester', () => {
    /**
     * Action Init
     *
     * This part of the test tries to initialize the tester screen by provide a
     * valid credentials and loads the tester application
     */
    test('Action Init', async () => {
        await page.goto('http://127.0.0.1:8092/#/app/tester');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), {timeout: 10000});
        await page.waitForSelector('#campo26d', timeout);
        await mypause(page, 1000);

        const screenshot = await sharp(await page.screenshot()).png({
            compressionLevel: 9,
            palette: true,
        }).toBuffer();
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    }, 20000);

    /**
     * Action Disabled
     *
     * This part of the test tries to disable all widgets
     */
    test('Action Disabled', async () => {
        await page.evaluate(() => { saltos.app.form_disabled(true); });
        await mypause(page, 100);

        const screenshot = await sharp(await page.screenshot()).png({
            compressionLevel: 9,
            palette: true,
        }).toBuffer();
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * Action Enabled
     *
     * This part of the test tries to enable all widgets
     */
    test('Action Enabled', async () => {
        await page.evaluate(() => { saltos.app.form_disabled(false); });
        await mypause(page, 100);

        const screenshot = await sharp(await page.screenshot()).png({
            compressionLevel: 9,
            palette: true,
        }).toBuffer();
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * Prepare the test.each iterator
     */
    const themes = ['default',
        'black', 'blue', 'cyan', 'gray', 'green', 'indigo',
        'orange', 'pink', 'purple', 'red', 'teal', 'yellow',
    ];
    const modes = ['light', 'dark'];
    const cases = modes.flatMap(mode => themes.map(theme => ({
        mode: mode,
        theme: theme,
        label: `${theme} [${mode}]`,
    })));

    /**
     * Visual Theme Combinations
     *
     * This test iterates all css themes combined with both light and dark
     * bootstrap modes. For each permutation the bootstrap theme and the
     * css theme are applied and the resulting screen is validated against
     * the expected visual snapshot.
     *
     * This ensures visual consistency across all supported theme
     * combinations without duplicating structural tests like
     * enabled/disabled state.
     */
    test.each(cases)('$label', async ({mode, theme, label}) => {
        await page.evaluate(({mode, theme}) => {
            saltos.bootstrap.set_bs_theme(mode);
            saltos.bootstrap.set_css_theme(theme);
        }, {mode, theme});

        await mypause(page, 500);

        const screenshot = await sharp(await page.screenshot()).png({
            compressionLevel: 9,
            palette: true,
        }).toBuffer();
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

    });
});
