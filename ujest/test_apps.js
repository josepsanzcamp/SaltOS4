
/**
 * @jest-environment node
 */

/**
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
 * TODO
 */
const puppeteer = require('puppeteer');
const pti = require('puppeteer-to-istanbul');
const toMatchImageSnapshot = require('jest-image-snapshot').toMatchImageSnapshot;
expect.extend({toMatchImageSnapshot});

/**
 * TODO
 */
const timeout = {timeout: 3000};

/**
 * TODO
 */
let browser;
let page;

/**
 * TODO
 */
beforeAll(async () => {
    browser = await puppeteer.launch({
        args: ['--ignore-certificate-errors'],
    });
    page = await browser.newPage();
    await page.setViewport({width: 1920, height: 1080});
    await page.coverage.startJSCoverage();
});

/**
 * TODO
 */
afterAll(async () => {
    const jsCoverage = await page.coverage.stopJSCoverage();
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/apps'});
    await browser.close();
});

/**
 * TODO
 */
let testFailed = false;
let testFinish = false;

/**
 * TODO
 */
beforeEach(() => {
    if (testFailed) {
        throw new Error('A previous test failed, skipping execution');
    }
    testFinish = false;
});

/**
 * TODO
 */
afterEach(() => {
    if (!testFinish) {
        testFailed = true;
    }
});

/**
 * TODO
 *
 * TODO
 */
describe('App Login', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action Login', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4');

        await page.waitForSelector('#user', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Login Ko Red', async () => {
        await page.waitForSelector('#user', timeout);
        await page.$$eval('button', buttons => buttons[1].click());

        // Special case to allow the login ko
        await page.waitForSelector('#user', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        // Special case to allow the toast render
        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Login Ko Green', async () => {
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin2');
        await page.$eval('#pass', el => el.value = 'admin2');
        await page.$$eval('button', buttons => buttons[1].click());

        // Special case to allow the login ko
        await page.waitForSelector('#user', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        // Special case to allow the toast render
        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.$eval('.toast', el => el.remove());
        await page.waitForFunction(() => !document.querySelector('.toast'), timeout);

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Dashboard', async () => {
        await page.waitForSelector('#user', timeout);
        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForSelector('#one', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        // Special case to allow the chartjs render
        await mypause(page, 1000);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('App Customers', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/customers');

        await page.waitForSelector('#list table', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        await page.waitForSelector('#one button', timeout);
        await page.$$eval('#one button', buttons => buttons[1].click()); // this trigger the create action
        await page.$$eval('#one button', buttons => buttons[1].blur()); // this lost the focus

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this open the dropdown
        await page.$$eval('#list button', buttons => buttons[1].click()); // this trigger the view action

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Edit', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this open the dropdown
        await page.$$eval('#list button', buttons => buttons[2].click()); // this trigger the edit action

        await page.waitForSelector('#nombre', timeout);
        // The follow lines disable the spell checker, the blur and focus
        // are executed in separate statements to allow the excetution
        await page.$eval('#nombre', element => element.spellcheck = false);
        await page.$eval('#nombre', element => element.blur());
        await page.$eval('#nombre', element => element.focus());
        // Continue
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('App Invoices', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/invoices');

        await page.waitForSelector('#list table', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        await page.waitForSelector('#one button', timeout);
        await page.$$eval('#one button', buttons => buttons[1].click()); // this trigger the create action
        await page.$$eval('#one button', buttons => buttons[1].blur()); // this lost the focus

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this open the dropdown
        await page.$$eval('#list button', buttons => buttons[1].click()); // this trigger the view action

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Edit', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this open the dropdown
        await page.$$eval('#list button', buttons => buttons[2].click()); // this trigger the edit action

        await page.waitForSelector('#nombre', timeout);
        // The follow lines disable the spell checker, the blur and focus
        // are executed in separate statements to allow the excetution
        await page.$eval('#nombre', element => element.spellcheck = false);
        await page.$eval('#nombre', element => element.blur());
        await page.$eval('#nombre', element => element.focus());
        // Continue
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('App Emails', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/emails');

        await page.waitForSelector('#list button', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Profile', async () => {
        await page.waitForSelector('#username', timeout);
        await page.$eval('#username', element => element.click()); // this open the dropdown
        await page.$$eval('#username ~ ul button', buttons => buttons[0].click()); // this trigger the profile

        await page.waitForSelector('#oldpass', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.profile(); });
        await page.waitForFunction(() => !document.querySelector('#oldpass'), timeout);

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Help', async () => {
        await page.waitForSelector('#username', timeout);
        await page.$eval('#username', element => element.click()); // this open the dropdown
        await page.$$eval('#username ~ ul button', buttons => buttons[1].click()); // this trigger the help

        await page.waitForSelector('#pdfjs', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        // Special case to allow the pdf load
        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.help(); });
        await page.waitForFunction(() => !document.querySelector('#pdfjs'), timeout);

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Filter', async () => {
        await page.waitForSelector('#top button', timeout);
        await page.$$eval('#top button', buttons => buttons[0].click()); // this trigger the filter action

        await page.waitForSelector('.offcanvas', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.filter(); });
        await page.waitForFunction(() => !document.querySelector('.offcanvas'), timeout);

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        await page.waitForSelector('#top button', timeout);
        await page.$$eval('#top button', buttons => buttons[1].click()); // this trigger the creste action
        await page.$$eval('#top button', buttons => buttons[1].blur()); // this lost the focus

        await page.waitForSelector('#from', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        // Special case to allow the ckeditor render
        await page.waitForFunction(() => document.getElementById('body').ckeditor, timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        await page.$$eval('#list button', buttons => buttons[0].click()); // this trigger the view action
        await page.$$eval('#list button', buttons => buttons[0].blur()); // this lost the focus

        await page.waitForSelector('#from', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('App Logout', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action Logout', async () => {
        await page.waitForSelector('#username', timeout);
        await page.$eval('#username', element => element.click()); // this open the dropdown
        await page.$$eval('#username ~ ul button', buttons => buttons[2].click()); // this trigger the logout

        await page.waitForSelector('#user', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005, // this is for the shadow
            failureThresholdType: 'percent', // this is for the shadow
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});
