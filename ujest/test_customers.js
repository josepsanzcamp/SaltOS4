
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
    await page.coverage.startJSCoverage({
        resetOnNavigation: false,
        reportAnonymousScripts: false,
        includeRawScriptCoverage: false,
        useBlockCoverage: true,
    });
});

/**
 * TODO
 */
afterAll(async () => {
    const jsCoverage = await page.coverage.stopJSCoverage();
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/customers'});
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
describe('App Customers', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/customers');

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
     * TODO
     *
     * TODO
     */
    test('Action More', async () => {
        await page.waitForFunction(() => document.querySelectorAll('#list tbody tr').length == 25, timeout);
        await page.$$eval('#one button', buttons => buttons[buttons.length - 1].click());
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => document.querySelectorAll('#list tbody tr').length == 50, timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Reset', async () => {
        await page.$$eval('#one button', buttons => buttons[3].click());
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => document.querySelectorAll('#list tbody tr').length == 25, timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
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
     * TODO
     *
     * TODO
     */
    test('Action Cancel', async () => {
        await page.$$eval('#two button', buttons => buttons[buttons.length - 1].click()); // cancel button
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => !document.querySelector('#nombre'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
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
     * TODO
     *
     * TODO
     */
    test('Action Close', async () => {
        await page.$$eval('#two button', buttons => buttons[buttons.length - 2].click());
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => !document.querySelector('#nombre'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
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
     * TODO
     *
     * TODO
     */
    test('Action Go Back', async () => {
        await page.goBack();
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => !document.querySelector('#nombre'), timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Insert', async () => {
        await page.goto('https://127.0.0.1/saltos/code4/#/app/customers/create');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#nombre', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.$eval('#nombre', el => el.value = 'Josep Sanz');
        await page.$eval('#cif', el => el.value = '12345678X');
        await page.$eval('#nombre_poblacion', el => el.value = 'Barcelona');
        await page.$eval('#nombre_codpostal', el => el.value = '08001');
        await page.$$eval('#two button', buttons => buttons[buttons.length - 2].click()); // create button

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => !document.querySelector('#nombre'), timeout);

        const screenshot2 = await page.screenshot({encoding: 'base64'});
        expect(screenshot2).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Update', async () => {
        const id = await page.$eval('#list tbody tr', el => el.id.split('/')[1]);
        await page.goto('https://127.0.0.1/saltos/code4/#/app/customers/edit/' + id);

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#nombre', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.$eval('#nombre', el => el.value = 'Josep Sanz');
        await page.$eval('#cif', el => el.value = '12345678Y');
        await page.$eval('#nombre_poblacion', el => el.value = 'Barcelona');
        await page.$eval('#nombre_codpostal', el => el.value = '08002');
        await page.$$eval('#two button', buttons => buttons[buttons.length - 2].click()); // update button

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => !document.querySelector('#nombre'), timeout);

        const screenshot2 = await page.screenshot({encoding: 'base64'});
        expect(screenshot2).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Delete', async () => {
        const id = await page.$eval('#list tbody tr', el => el.id.split('/')[1]);
        await page.evaluate(id => { saltos.driver.delete(`app/customers/delete/${id}`); }, id);

        await page.waitForSelector('.modal', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.$eval('.modal-footer button', button => button.click()); // yes button

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForFunction(() => !document.querySelector('.modal'), timeout);

        const screenshot2 = await page.screenshot({encoding: 'base64'});
        expect(screenshot2).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});
