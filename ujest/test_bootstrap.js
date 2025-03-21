
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
 *
 * TODO
 */
describe('Bootstrap', () => {
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
        await page.coverage.startJSCoverage();
        await page.goto('https://127.0.0.1/saltos/code4/#/app/emails');
        await page.waitForFunction(() => document.getElementById('user'), timeout);
    });

    /**
     * TODO
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
     * TODO
     *
     * TODO
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
        } else if (field.type == 'codemirror') {
            await page.waitForFunction(id => document.getElementById(id).codemirror, timeout, id);
        } else if (['tags', 'onetag'].includes(field.type)) {
            await page.waitForFunction(id => document.getElementById(id).tomselect, timeout, id);
        } else if (field.type == 'jstree') {
            await page.waitForFunction(id => document.getElementById(id).instance, timeout, id);
        } else if (field.type == 'excel') {
            await page.waitForFunction(id => document.getElementById(id).excel, timeout, id);
        } else if (field.type == 'chartjs') {
            await mypause(page, 1000);
        } else if (field.type == 'gallery') {
            await mypause(page, 100);
        } else if (field.type == 'pdfjs') {
            await page.waitForFunction(() => { return typeof pdfjsLib == 'object'; }, timeout);
            await mypause(page, 200);
        }

        const widget = await page.$('#widget');
        const screenshot = await widget.screenshot({encoding: 'base64'});
        expect(screenshot).toMatchImageSnapshot({
            failureThreshold: 0.005,
            failureThresholdType: 'percent',
            customSnapshotsDir: `${__dirname}/snaps`,
        });
    });
});
