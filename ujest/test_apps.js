
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
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
 */

'use strict';

/**
 * Apps unit tests
 *
 * This file contains the apps unit tests
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
const sharp = require('sharp');

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
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/apps'});
    await browser.close();
});

/**
 * App Login
 *
 * This test is intended to validate the correctness of the login screen and their
 * particularities, too tries to check the dashboard and the logout to close the
 * loop of tests
 */
describe('App Login', () => {
    /**
     * Action Login
     *
     * This function tries to do a test to validate that the login screen appear
     * correctly without issues
     */
    test('Action Login', async () => {
        await page.goto('https://127.0.0.1/saltos/code4');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);

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
     * Action Login Ko Red
     *
     * This function tries to validate the form when no data is found
     */
    test('Action Login Ko Red', async () => {
        await page.waitForSelector('#user', timeout);
        await page.$$eval('button', buttons => buttons[1].click());

        await mypause(page, 100);
        await page.waitForSelector('.is-invalid', timeout);

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
     * Action Login Ko Green
     *
     * This function tries to validate the form when invalid data is found
     */
    test('Action Login Ko Green', async () => {
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin2');
        await page.$eval('#pass', el => el.value = 'admin2');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('.is-valid', timeout);
        await page.waitForSelector('.toast', timeout);
        // Special case because the previous toast detection not works as expected
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

        await page.$eval('.toast', el => el.remove());
        await page.waitForFunction(() => !document.querySelector('.toast'), timeout);
    });

    /**
     * Action Dashboard
     *
     * This function tries to execute the login with valid credentials, to validate
     * the correctness, the dashboard will appear
     */
    test('Action Dashboard', async () => {
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#one', timeout);
        // Special case to allow the chartjs render
        await mypause(page, 1000);
        await page.waitForFunction(() => !document.querySelector('.toast'), timeout);

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
     * Action Reload
     *
     * This function tries to do a reload of the previous page, the validation of
     * this test must accomplish when a valid dashbord appear
     */
    test('Action Reload', async () => {
        await page.reload();

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#one', timeout);
        // Special case to allow the chartjs render
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
    });

    /**
     * Action Logout
     *
     * This test is intended to check the correctness of the logout feature, to
     * to it the test tries to locate the logout button placed in the dropdown
     * menu of the nabvar and click to the third element
     */
    test('Action Logout', async () => {
        await page.waitForSelector('#username', timeout);
        await page.$eval('#username', element => element.click()); // this open the dropdown
        await page.$$eval('#username ~ ul button', buttons => buttons[3].click()); // this trigger the logout

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);

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
