
/**
 * @jest-environment node
 *
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2025 by Josep Sanz Campderrós
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
 * Tester unit tests
 *
 * This file contains the tester unit tests
 */

/**
 * Puppeteer setup
 *
 * This lines contain the needed setup for run puppeteer and take screenshots
 */
const puppeteer = require('puppeteer');
const pti = require('puppeteer-to-istanbul');
const toMatchImageSnapshot = require('jest-image-snapshot').toMatchImageSnapshot;
expect.extend({toMatchImageSnapshot});
const timeout = {timeout: 3000};

/**
 * Global variables
 *
 * This variables contains the browser and page links
 */
let browser;
let page;

/**
 * Before All
 *
 * This function contains all code executed before all tests, in this case the
 * features provided by this function includes the launch of the browser, set
 * the screen size and start the javascript coverage
 */
beforeAll(async () => {
    browser = await puppeteer.launch({
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
        await page.goto('https://127.0.0.1/saltos/code4/#/app/tester');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#campo26d', timeout);
        await mypause(page, 1000);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * Action Disabled
     *
     * This part of the test tries to disable all widgets
     */
    test('Action Disabled', async () => {
        await page.evaluate(() => { saltos.app.form_disabled(true); });
        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
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

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * List of bs_themes
     */
    const bs_themes = ['light', 'dark', 'auto'];

    /**
     * Action Bs Theme
     *
     * This part of the test tries to set the differents bs_themes
     */
    test.each(bs_themes)('Action Bs Theme %s', async theme => {
        await page.evaluate(theme => { saltos.bootstrap.set_bs_theme(theme); }, theme);
        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * List of css_themes
     */
    const css_themes = ['default',
        'black', 'blue', 'cyan', 'gray', 'green', 'indigo',
        'orange', 'pink', 'purple', 'red', 'teal', 'yellow',
    ];

    /**
     * Action Css Theme
     *
     * This part of the test tries to set the differents css_themes
     */
    test.each(css_themes)('Action Css Theme %s', async theme => {
        await page.evaluate(theme => { saltos.bootstrap.set_css_theme(theme); }, theme);
        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });
});
