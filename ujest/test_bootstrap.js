
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
const fs = require('fs');
const path = require('path');
const pti = require('puppeteer-to-istanbul');

/**
 * TODO
 *
 * TODO
 */
describe('Bootstrap', () => {
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
        await page.waitForFunction(() => document.getElementById('user'));
    });

    /**
     * TODO
     */
    afterAll(async () => {
        const jsCoverage = await page.coverage.stopJSCoverage();
        pti.write(jsCoverage, {storagePath: '/tmp/nyc_output/1'});
        await browser.close();
    });

    /**
     * Prepare the test.each iterator
     */
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

        if (field.type == 'ckeditor') {
            await page.waitForFunction(id => document.getElementById(id).ckeditor, {}, field.id);
        } else if (field.type == 'codemirror') {
            await page.waitForFunction(id => document.getElementById(id).codemirror, {}, field.id);
        } else if (['tags', 'onetag'].includes(field.type)) {
            await page.waitForFunction(id => document.getElementById(id).tomselect, {}, field.id);
        } else if (field.type == 'jstree') {
            await page.waitForFunction(id => document.getElementById(id).instance, {}, field.id);
        } else if (field.type == 'excel') {
            await page.waitForFunction(id => document.getElementById(id).excel, {}, field.id);
        } else if (field.type == 'chartjs') {
            await page.evaluate(() => { return new Promise((resolve) => { setTimeout(resolve, 1000); }); });
        } else if (field.type == 'gallery') {
            await page.evaluate(() => { return new Promise((resolve) => { setTimeout(resolve, 100); }); });
        } else if (field.type == 'pdfjs') {
            await page.waitForFunction(() => { return typeof pdfjsLib == 'object'; });
            await page.evaluate(() => { return new Promise((resolve) => { setTimeout(resolve, 200); }); });
        }

        const widget = await page.$('#widget');
        const screenshot = await widget.screenshot();
        const testName = expect.getState().currentTestName; // "should render correctly"
        const testPath = expect.getState().testPath; // Ruta del archivo de prueba
        const dir = path.dirname(testPath); // Obtiene el directorio
        const file = saltos.core.encode_bad_chars(testName) + '.png';
        fs.writeFileSync(`${dir}/snaps/${file}`, screenshot);
        expect(screenshot).toMatchSnapshot();
    });
});
