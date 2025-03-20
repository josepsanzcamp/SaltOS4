
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

/**
 * TODO
 *
 * TODO
 */
describe.only('App Customers', () => {
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
        pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/2'});
        await browser.close();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Login', async () => {
        await page.goto('https://127.0.0.1/saltos/code4/#/app/customers');
        await page.waitForFunction(() => document.getElementById('user'));
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 100);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.type('#user', 'admin');
        await page.type('#pass', 'admin');
        const buttons = await page.$$('button');
        await buttons[1].click();

        await page.waitForSelector('#list table');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        const buttons = await page.$$('#one button');
        await buttons[1].click(); // This trigger the create action

        await page.waitForSelector('#codigo');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[1].click(); // This trigger the view action

        await page.waitForSelector('#codigo');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Edit', async () => {
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[2].click(); // This trigger the edit action

        await page.waitForSelector('#codigo');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });
});

/**
 * TODO
 *
 * TODO
 */
describe.only('App Invoices', () => {
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
        pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/3'});
        await browser.close();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Login', async () => {
        await page.goto('https://127.0.0.1/saltos/code4/#/app/invoices');
        await page.waitForFunction(() => document.getElementById('user'));
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 100);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.type('#user', 'admin');
        await page.type('#pass', 'admin');
        const buttons = await page.$$('button');
        await buttons[1].click();

        await page.waitForSelector('#list table');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        const buttons = await page.$$('#one button');
        await buttons[1].click(); // This trigger the create action

        await page.waitForSelector('#nombre');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[1].click(); // This trigger the view action

        await page.waitForSelector('#nombre');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Edit', async () => {
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This open the dropdown
        await buttons[2].click(); // This trigger the edit action

        await page.waitForSelector('#nombre');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });
});

/**
 * TODO
 *
 * TODO
 */
describe.only('App Emails', () => {
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
        pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/4'});
        await browser.close();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Login', async () => {
        await page.goto('https://127.0.0.1/saltos/code4/#/app/emails');
        await page.waitForFunction(() => document.getElementById('user'));
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 100);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action List', async () => {
        await page.type('#user', 'admin');
        await page.type('#pass', 'admin');
        const buttons = await page.$$('button');
        await buttons[1].click();

        await page.waitForSelector('#list button');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action Create', async () => {
        const buttons = await page.$$('#top button');
        await buttons[1].click(); // This trigger the create action

        await page.waitForSelector('#from');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('Action View', async () => {
        const buttons = await page.$$('#list button');
        await buttons[0].click(); // This trigger the view action

        await page.waitForSelector('#from');
        await page.waitForFunction(() => !saltos.form.screen('isloading'));

        await myblur(page);
        await mypause(page, 200);

        const screenshot = await page.screenshot();
        mysave(expect, screenshot);
        expect(screenshot).toMatchSnapshot();
    });
});
