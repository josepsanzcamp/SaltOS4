
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
 * Emails unit tests
 *
 * This file contains the emails unit tests
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
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/emails'});
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
 * App Emails
 *
 * This test is intended to validate the correctness of the emails application by
 * execute the list, control+f, profile, help, filter, create and view features and
 * validate with the expected screenshot
 */
describe('App Emails', () => {
    /**
     * Action List
     *
     * This action tries to sets the emails as new and not new of all emails that
     * appear in the screen
     */
    test('Action List', async () => {
        await page.goto('https://127.0.0.1/saltos/code4/#/app/emails');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#list button', timeout);

        await page.evaluate(() => {
            saltos.app.ajax({
                url: 'app/emails/view/setter/90,91,92,93,94,95',
                proxy: 'network',
                data: {
                    'what': 'new=0',
                },
                success: response => {
                    saltos.window.send('saltos.emails.update');
                },
            });
        });
        await page.evaluate(() => {
            saltos.app.ajax({
                url: 'app/emails/view/setter/96,97,98,99,100',
                proxy: 'network',
                data: {
                    'what': 'new=1',
                },
                success: response => {
                    saltos.window.send('saltos.emails.update');
                },
            });
        });

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#list button', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * Action Control F
     *
     * This part of the test tries to validate the control+f focus
     */
    test('Action Control F', async () => {
        await page.waitForSelector('#top input', timeout);
        await page.$$eval('#top input', inputs => inputs[1].blur()); // this blur the focus
        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.keyboard.down('Control');
        await page.keyboard.down('Shift');
        await page.keyboard.press('F');
        await page.keyboard.up('Shift');
        await page.keyboard.up('Control');

        await mypause(page, 500);

        const screenshot2 = await page.screenshot({encoding: 'base64'});
        expect(screenshot2).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * Action Profile
     *
     * This part of the test tries to load the profile screen
     */
    test('Action Profile', async () => {
        await page.waitForSelector('#username', timeout);
        await page.$eval('#username', element => element.click()); // this open the dropdown
        await page.$$eval('#username ~ ul button', buttons => buttons[0].click()); // this trigger the profile

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#oldpass', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.profile(); });
        await page.waitForFunction(() => !document.querySelector('#oldpass'), timeout);

        testFinish = true;
    });

    /**
     * Action Help
     *
     * This part of the test tries to load the help screen
     */
    test('Action Help', async () => {
        await page.waitForSelector('#username', timeout);
        await page.$eval('#username', element => element.click()); // this open the dropdown
        await page.$$eval('#username ~ ul button', buttons => buttons[1].click()); // this trigger the help

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#pdfjs', timeout);

        // Special case to allow the pdf load
        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.help(); });
        await page.waitForFunction(() => !document.querySelector('#pdfjs'), timeout);

        testFinish = true;
    });

    /**
     * Action Filter
     *
     * This part of the test tries to load the filter screen
     */
    test('Action Filter', async () => {
        await page.waitForSelector('#top button', timeout);
        await page.$$eval('#top button', buttons => buttons[0].click()); // this trigger the filter action

        await page.waitForSelector('.offcanvas', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.filter(); });
        await page.waitForFunction(() => !document.querySelector('.offcanvas'), timeout);

        testFinish = true;
    });

    /**
     * Action Create
     *
     * This part of the test tries to load the create screen
     */
    test('Action Create', async () => {
        await page.waitForSelector('#top button', timeout);
        await page.$$eval('#top button', buttons => buttons[1].click()); // this trigger the creste action

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#from', timeout);

        // Special case to allow the ckeditor render
        await page.waitForFunction(() => document.getElementById('body').ckeditor, timeout);

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
        await page.$$eval('#list button', buttons => buttons[0].click()); // this trigger the view action

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#from', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});
