
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
 * Workflow variables
 *
 * This variables allow to control the workflow of the test, the main idea is to
 * skip all tests when one test fails
 */
let testFailed = false;
let testFinish = false;

/**
 * Before Each
 *
 * This function contains all code executed before each test, in this case the
 * features provided by this function includes the control of the workflow
 */
beforeEach(() => {
    if (testFailed) {
        throw new Error('A previous test failed, skipping execution');
    }
    testFinish = false;
});

/**
 * After Each
 *
 * This function contains all code executed after each test, in this case the
 * features provided by this function includes the control of the workflow
 */
afterEach(() => {
    if (!testFinish) {
        testFailed = true;
    }
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

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
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

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
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

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.$eval('.toast', el => el.remove());
        await page.waitForFunction(() => !document.querySelector('.toast'), timeout);

        testFinish = true;
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

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
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

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
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
        await page.$$eval('#username ~ ul button', buttons => buttons[2].click()); // this trigger the logout

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});
