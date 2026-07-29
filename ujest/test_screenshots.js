
/**
 * @jest-environment node
 *
 *  ____        _ _    ___  ____  _  _
 * / ___|  __ _| | |_ / _ \/ ___|| || |
 * \___ \ / _` | | __| | | \___ \| || |_
 *  ___) | (_| | | |_| |_| |___) |__   _|
 * |____/ \__,_|_|\__|\___/|____/   |_|
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (c) 2007-2026 Josep Sanz Campderrós
 * SPDX-License-Identifier: MIT
 * Licensed under the MIT License.
 * See the LICENSE file in the project root for full license information.
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
const pti = require('puppeteer-to-istanbul');
const toMatchImageSnapshot = require('jest-image-snapshot').toMatchImageSnapshot;
expect.extend({toMatchImageSnapshot});
const timeout = {timeout: 3000};
const sharp = require('sharp');
const load_puppeteer = require('./lib/puppeteer.setup.js');

/**
 * Global variables
 *
 * This variables contains the browser and page links
 */
let puppeteer;
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
    puppeteer = await load_puppeteer();
    browser = await puppeteer.launch({
        executablePath: '/usr/bin/chromium',
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
        await page.goto('http://127.0.0.1:8092/#/app/login');

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

        await page.$eval('#user', el => el.value = 'admin');
        await page.$eval('#pass', el => el.value = 'admin');
        await page.$$eval('button', buttons => buttons[1].click());

        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);
        await page.waitForSelector('.grid-stack', timeout);
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
    const modes = ['light', 'dark'];
    for (const lang in langs) {
        for (const mode in modes) {
            for (const group in apps) {
                for (const app in apps[group]) {
                    for (const action in apps[group][app]) {
                        allApps.push({
                            group: group,
                            app: app,
                            action: apps[group][app][action],
                            lang: langs[lang],
                            mode: modes[mode],
                        });
                    }
                }
            }
        }
    }
    //~ console.log(allApps);

    test.each(allApps)('$group $app $action $lang $mode', async (info) => {
        if (['list', ''].includes(info.action)) {
            await page.evaluate(lang => { saltos.gettext.set(lang); }, info.lang);
            await page.evaluate(mode => { saltos.bootstrap.set_bs_theme(mode); }, info.mode);
            await page.goto('about:blank');
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

        await page.goto(`http://127.0.0.1:8092/#/app/${info.app}/${info.action}`);
        await page.waitForFunction(() => !saltos.form.screen('isloading'), timeout);

        if (info.app === 'emails' && info.action === 'create') {
            await page.waitForFunction(id => document.getElementById(id).joditeditor, timeout, 'body');
            await mypause(page, 1);
        } else if (info.action.includes('viewpdf')) {
            await mypause(page, 500);
        } else if (info.app.includes('dashboard')) {
            await mypause(page, 500);
        } else if (info.app.includes('emails')) {
            await mypause(page, 500);
        } else {
            await mypause(page, 1);
        }

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
