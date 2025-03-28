
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
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/tester'});
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
describe('App Tester', () => {
    /**
     * TODO
     *
     * TODO
     */
    test('Action Init', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
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

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
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

        testFinish = true;
    });

    /**
     * TODO
     *
     * TODO
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

        testFinish = true;
    });

    /**
     * TODO
     */
    const bs_themes = ['light', 'dark', 'auto'];

    /**
     * TODO
     *
     * TODO
     */
    test.each(bs_themes)('Action Bs Theme %s', async theme => {
        await page.evaluate(theme => { saltos.bootstrap.set_bs_theme(theme); }, theme);
        await mypause(page, 100);

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
     */
    const css_themes = ['default', 'cerulean', 'cyborg', 'darkly', 'flatly', 'journal', 'litera', 'lumen',
        'lux', 'materia', 'minty', 'morph', 'pulse', 'quartz', 'sandstone', 'simplex', 'sketchy',
        'slate', 'solar', 'spacelab', 'superhero', 'united', 'vapor', 'yeti', 'zephyr', 'cosmo'];

    /**
     * TODO
     *
     * TODO
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

        testFinish = true;
    });
});
