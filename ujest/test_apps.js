
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
 *
 * TODO
 */
describe('App Dashboard', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action Login', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/dashboard');

        await page.waitForSelector('#user', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Dashboard', async () => {
        await page.waitForSelector('#user', timeout);
        await page.type('#user', 'admin');
        await page.type('#pass', 'admin');
        const buttons = await page.$$('button');
        await buttons[1].click();

        await page.waitForSelector('#one', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 1000);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
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

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        await page.waitForSelector('#one button', timeout);
        const buttons = await page.$$('#one button');
        await buttons[1].click(); // This trigger the create action

        await page.waitForSelector('#codigo', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[1].click(); // This trigger the view action

        await page.waitForSelector('#codigo', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Edit', async () => {
        await page.waitForSelector('#list button', timeout);
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[2].click(); // This trigger the edit action

        await page.waitForSelector('#codigo', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
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

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        await page.waitForSelector('#one button', timeout);
        const buttons = await page.$$('#one button');
        await buttons[1].click(); // This trigger the create action

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[1].click(); // This trigger the view action

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Edit', async () => {
        await page.waitForSelector('#list button', timeout);
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[2].click(); // This trigger the edit action

        await page.waitForSelector('#nombre', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
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

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        await page.waitForSelector('#top button', timeout);
        const buttons = await page.$$('#top button');
        await buttons[1].click(); // This trigger the create action

        await page.waitForSelector('#from', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        await page.waitForSelector('#list button', timeout);
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This trigger the view action

        await page.waitForSelector('#from', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Filter', async () => {
        await page.waitForSelector('#list button', timeout);
        const buttons = await page.$$('#top button');
        await buttons[0].click(); // This trigger the filter action

        await page.waitForSelector('.offcanvas', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.filter(); });
        await page.waitForFunction(() => !document.querySelector('.offcanvas'), timeout);
    });
});

/**
 * TODO
 *
 * TODO
 */
describe('App Dropdown', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action Profile', async () => {
        await page.waitForSelector('#username', timeout);
        const username = await page.$('#username');
        await username.click(); // This open the dropdown
        const buttons = await page.$$('#username ~ ul button');
        await buttons[0].click(); // This trigger the profile action

        await page.waitForSelector('#oldpass', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.profile(); });
        await page.waitForFunction(() => !document.querySelector('#oldpass'), timeout);
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Help', async () => {
        await page.waitForSelector('#username', timeout);
        const username = await page.$('#username');
        await username.click(); // This open the dropdown
        const buttons = await page.$$('#username ~ ul button');
        await buttons[1].click(); // This trigger the help action

        await page.waitForSelector('#pdfjs', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 500);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.evaluate(() => { saltos.common.help(); });
        await page.waitForFunction(() => !document.querySelector('#pdfjs'), timeout);
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Logout', async () => {
        await page.waitForSelector('#username', timeout);
        const username = await page.$('#username');
        await username.click(); // This open the dropdown
        const buttons = await page.$$('#username ~ ul button');
        await buttons[2].click(); // This trigger the logout action

        await page.waitForSelector('#user', timeout);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        await mypause(page, 100);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });
});
