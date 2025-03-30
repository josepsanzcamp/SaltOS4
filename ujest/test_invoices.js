
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
 * Bootstrap unit tests
 *
 * This file contains the bootstrap unit tests
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
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/invoices'});
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
 * App Invoices
 *
 * This test is intended to validate the correctness of the invoices application
 * by execute the list, checkbox, create, view and edit features and validate with
 * the expected screenshot
 */
describe('App Invoices', () => {
    /**
     * Action List
     *
     * This part of the test tries to load the list screen
     */
    test('Action List', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/invoices');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#list table', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * Action List
     *
     * This part of the test tries to validate the correctness of the checkbox
     * feature
     */
    test('Action Checkbox', async () => {
        let count;

        // This checks that no checkboxes are enabled
        count = await page.$$eval('#list input[type=checkbox]:checked', inputs => inputs.length);
        expect(count).toBe(0);

        // This enable all checkboxes
        await page.$$eval('#list input[type=checkbox]', inputs => inputs[0].click());
        count = await page.$$eval('#list input[type=checkbox]:checked', inputs => inputs.length);
        expect(count).toBe(26);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        // This disable all checkboxes
        await page.$$eval('#list input[type=checkbox]', inputs => inputs[0].click());
        count = await page.$$eval('#list input[type=checkbox]:checked', inputs => inputs.length);
        expect(count).toBe(0);

        // This enable the first row, note that the click is triggered to the first row
        await page.$$eval('#list tbody tr', rows => rows[0].click());
        // This event is triggered to the checkbox number six to get only 5 enabled checkboxes
        await page.evaluate(() => {
            const checkbox = document.querySelectorAll('#list input[type=checkbox]')[5];
            const event = new MouseEvent('click', {
                bubbles: true,
                ctrlKey: true,
            });
            checkbox.dispatchEvent(event);
        });
        count = await page.$$eval('#list input[type=checkbox]:checked', inputs => inputs.length);
        expect(count).toBe(5);

        const screenshot2 = await page.screenshot({encoding: 'base64'});
        expect(screenshot2).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        // This reset all checkboxes to disabled all selections
        await page.$$eval('#list input[type=checkbox]', inputs => inputs[0].click());
        await page.$$eval('#list input[type=checkbox]', inputs => inputs[0].click());
        count = await page.$$eval('#list input[type=checkbox]:checked', inputs => inputs.length);
        expect(count).toBe(0);

        testFinish = true;
    });

    /**
     * Action Create
     *
     * This part of the test tries to load the create screen
     */
    test('Action Create', async () => {
        await page.waitForSelector('#one button', timeout);
        await page.$$eval('#one button', buttons => buttons[1].click()); // this trigger the create action

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#nombre', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * Action View
     *
     * This part of the test tries to load the view screen
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this open the dropdown
        await page.$$eval('#list button', buttons => buttons[1].click()); // this trigger the view action

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#nombre', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * Action Edit
     *
     * This part of the test tries to load the edit screen
     */
    test('Action Edit', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this open the dropdown
        await page.$$eval('#list button', buttons => buttons[2].click()); // this trigger the edit action

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#nombre', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});
