
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
 * Bootstrap
 *
 * This test contains the code needed to create all widgets and validate the
 * correctness of them
 */
describe('Bootstrap', () => {
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
     * This function contains all code executed before all tests
     */
    beforeAll(async () => {
        browser = await puppeteer.launch({
            args: ['--ignore-certificate-errors'],
        });
        page = await browser.newPage();
        await page.coverage.startJSCoverage({
            resetOnNavigation: false,
            reportAnonymousScripts: false,
            includeRawScriptCoverage: false,
            useBlockCoverage: true,
        });
        await page.goto('https://127.0.0.1/saltos/code4/#/app/emails');
        await page.waitForFunction(() => document.getElementById('user'), timeout);
    });

    /**
     * After All
     *
     * This function contains all code executed after all tests
     */
    afterAll(async () => {
        const jsCoverage = await page.coverage.stopJSCoverage();
        pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/bootstrap'});
        await browser.close();
    });

    /**
     * Prepare the test.each iterator
     */
    const fs = require('fs');
    const json = JSON.parse(fs.readFileSync('/tmp/tester.json', 'utf-8'));

    /**
     * Real test
     *
     * This function executes the real test for each json item, its able to
     * create a widget and validate the correctness of the widget comparing
     * the new widget screenshot to the backup widget screenshot
     */
    test.each(json)('$label', async field => {
        await page.evaluate(field => {
            const obj = saltos.bootstrap.field(field);
            const div = saltos.core.html('<div id="widget" style="width:600px;" />');
            div.append(obj);
            document.body.innerHTML = '';
            document.body.append(div);
        }, field);

        const id = field.id;
        if (field.type == 'ckeditor') {
            await page.waitForFunction(id => document.getElementById(id).ckeditor, timeout, id);
            await mypause(page, 100);
        } else if (field.type == 'codemirror') {
            await page.waitForFunction(id => document.getElementById(id).codemirror, timeout, id);
            await mypause(page, 100);
        } else if (['tags', 'onetag'].includes(field.type)) {
            await page.waitForFunction(id => document.getElementById(id).tomselect, timeout, id);
        } else if (field.type == 'jstree') {
            await page.waitForFunction(id => document.getElementById(id).instance, timeout, id);
        } else if (field.type == 'excel') {
            await mypause(page, 500);
            await page.waitForFunction(id => document.getElementById(id).excel, timeout, id);
        } else if (field.type == 'chartjs') {
            await mypause(page, 1000);
        } else if (['gallery', 'image'].includes(field.type)) {
            await mypause(page, 100);
        } else if (field.type == 'pdfjs') {
            await page.waitForFunction(() => { return typeof pdfjsLib == 'object'; }, timeout);
            await mypause(page, 500);
        } else {
            await mypause(page, 1);
        }

        const widget = await page.$('#widget');
        const screenshot = await widget.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0,
            failureThresholdType: 'pixel',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });
});
