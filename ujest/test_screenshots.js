
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
 * Screenshots unit tests
 *
 * This file contains the screenshots unit tests
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
    pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/screenshots'});
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
 * App Customers
 *
 * This test is intended to validate the correctness of the customers application
 * by execute the list, more, reset, create, cancel, view, close, edit, back,
 * insert, update and delete features and validate with the expected screenshot
 */
describe('Screenshots', () => {
    /**
     * Action List
     *
     * This part of the test tries to load the list screen
     */
    test('users login', async () => {
        await page.evaluate(() => { document.body.innerHTML = ''; });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/login');

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#user', timeout);

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('#catalog', timeout);

        testFinish = true;
    });

    const apps = {
        'crm': {
            'customers': ['list', 'create', 'view/100', 'edit/100'],
            'leads': ['list', 'create', 'view/100', 'edit/100'],
            'meetings': ['list', 'create', 'view/100', 'edit/100', 'view/viewpdf/100'],
            'quotes': ['list', 'create', 'view/100', 'edit/100', 'view/viewpdf/100'],
            'customers_types': ['list', 'create', 'view/10', 'edit/10'],
            'leads_status': ['list', 'create', 'view/10', 'edit/10'],
            'quotes_status': ['list', 'create', 'view/10', 'edit/10']
        },
        'company': {
            'company': ['list', 'create', 'view/1', 'edit/1']
        },
        'sales': {
            'invoices': ['list', 'create', 'view/100', 'edit/100', 'view/viewpdf/100'],
            'products': ['list', 'create', 'view/100', 'edit/100'],
            'taxes': ['list', 'create', 'view/1', 'edit/1'],
            'workorders': ['list', 'create', 'view/100', 'edit/100', 'view/viewpdf/100'],
            'payment_methods': ['list', 'create', 'view/10', 'edit/10'],
            'invoices_status': ['list', 'create', 'view/10', 'edit/10'],
            'products_types': ['list', 'create', 'view/10', 'edit/10'],
            'products_categories': ['list', 'create', 'view/10', 'edit/10']
        },
        'dashboard': {
            'dashboard': [''],
            'dashboard_widgets': ['']
        },
        'emails': {
            'emails': ['list', 'create', 'view/100', 'view/viewpdf/100'],
            'emails_accounts': ['list', 'create', 'edit/1', 'view/1']
        },
        'hr': {
            'employees': ['list', 'create', 'view/100', 'edit/100'],
            'departments': ['list', 'create', 'view/100', 'edit/100'],
            'employees_types': ['list', 'create', 'view/10', 'edit/10']
        },
        'users': {
            'users': ['list', 'create', 'view/1', 'edit/1'],
            'groups': ['list', 'create', 'view/1', 'edit/1']
        },
        'common': {
            'pushlog': ['list'],
            'cronlog': ['list'],
            'uploadlog': ['list'],
            'configlog': ['list', 'create', 'view/10', 'edit/10'],
            'trashlog': ['list'],
            'tokenslog': ['list', 'view/1'],
            'fileslog': ['list']
        },
        'purchases': {
            'suppliers': ['list', 'create', 'view/100', 'edit/100'],
            'purchase': ['list', 'create', 'view/100', 'edit/100'],
            'suppliers_types': ['list', 'create', 'view/10', 'edit/10'],
            'purchase_status': ['list', 'create', 'view/10', 'edit/10']
        },
        'certs': {
            'certs': ['list', 'create', 'view/ab877d2027f7c71d9935999cce1b802b']
        }
    };

    const allApps = [];
    const langs = ['en_US', 'ca_ES', 'es_ES'];
    for (const lang in langs) {
        for (const group in apps) {
            for (const app in apps[group]) {
                for (const action in apps[group][app]) {
                    allApps.push({
                        group: group,
                        app: app,
                        action: apps[group][app][action],
                        lang: langs[lang],
                    });
                }
            }
        }
    }
    //~ console.log(allApps);

    const fs = require('fs');
    const path = require('path');
    const dir = path.join(__dirname, 'snaps');
    fs.readdirSync(dir).forEach(file => {
        if (file.includes('tokenslog') || file.includes('configlog')) {
            const fullPath = path.join(dir, file);
            fs.unlinkSync(fullPath);
            //~ console.log('Deleted:', fullPath);
        }
    });

    test.each(allApps)('$group $app $action $lang', async (info) => {
        if (['list', ''].includes(info.action)) {
            await page.evaluate(() => { saltos.bootstrap.modal('close'); });
            await page.evaluate(() => { document.body.innerHTML = ''; });
            await page.evaluate(lang => { saltos.gettext.set(lang); }, info.lang);
            await page.evaluate(() => { saltos.app.__cache = {}; });
        }
        if (['create', 'edit/100', 'edit/10', 'edit/1'].includes(info.action)) {
            await page.evaluate(() => {
                for (const i in localStorage) {
                    if (i.includes('saltos.autosave')) {
                        localStorage.removeItem(i);
                    }
                }
            });
        }

        await page.goto(`https://127.0.0.1/saltos/code4/#/app/${info.app}/${info.action}`);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        if (info.app == 'emails' && info.action == 'create') {
            await page.waitForFunction(id => document.getElementById(id).ckeditor, timeout, 'body');
            await mypause(page, 1);
        } else if (info.action.includes('viewpdf')) {
            await mypause(page, 500);
        } else {
            await mypause(page, 1);
        }

        const screenshot = await page.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });

        testFinish = true;
    });
});
