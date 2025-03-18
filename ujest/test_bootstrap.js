
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
describe('Widget rendering', () => {
    let browser;
    let page;
    let __iter = 0;

    /**
     * TODO
     */
    beforeEach(async () => {
        browser = await puppeteer.launch({
            args: ['--ignore-certificate-errors'],
        });
        page = await browser.newPage();
        await page.coverage.startJSCoverage();
    });

    /**
     * TODO
     */
    afterEach(async () => {
        const jsCoverage = await page.coverage.stopJSCoverage();
        __iter++;
        pti.write(jsCoverage, {
            storagePath: `/tmp/nyc_output/${__iter}`
        });
        await browser.close();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('renders table', async () => {
        await page.addScriptTag({path: path.resolve(
            __dirname, '../code/web/lib/bootstrap/bootstrap.bundle.min.js')});
        await page.addStyleTag({path: path.resolve(
            __dirname, '../code/web/lib/bootswatch/cosmo.min.css')});

        const files = `object,core,bootstrap,storage,hash,token,auth,window,
            gettext,driver,filter,backup,form,push,common,app`.split(',');
        for (const i in files) {
            const file = files[i].trim();
            await page.addScriptTag({path: path.resolve(__dirname, `../code/web/js/${file}.js`)});
        }

        await page.evaluate(() => {
            const widget = saltos.bootstrap.field({
                type: 'table',
                id: 'table',
                label: 'Campo 28 (table)',
                tooltip: 'Tooltip 28 (table)',
                header: ['Name','Surname',{'label': 'Phone', 'type': 'html'}],
                data: [
                    ['Josep','Sanz',`<a href='#'>654 123 789</a>`],
                    ['Jordi','Company','654 123 789'],
                    ['Andres','Diaz','654 123 789']
                ],
                footer: ['','Total','3'],
                checkbox: true,
            });
            const div = saltos.core.html('<div id="widget" style="width:600px;" />');
            div.append(widget);
            document.body.innerHTML = '';
            document.body.append(div);
        });

        const widget = await page.$('#widget');
        const screenshot = await widget.screenshot();
        const testName = expect.getState().currentTestName; // "should render correctly"
        const testPath = expect.getState().testPath; // Ruta del archivo de prueba
        const dir = path.dirname(testPath); // Obtiene el directorio
        const file = saltos.core.encode_bad_chars(testName) + '.png';
        fs.writeFileSync(`${dir}/snaps/${file}`, screenshot);
        expect(screenshot).toMatchSnapshot();
    });

    /**
     * TODO
     *
     * TODO
     */
    test('renders ckeditor', async () => {
        await page.goto('https://127.0.0.1/saltos/code4/#/app/emails');
        await page.waitForFunction(() => document.querySelector('#user'));

        await page.evaluate(() => {
            const widget = saltos.bootstrap.field({
                type: 'ckeditor',
                id: 'ckeditor',
                label: 'Campo 13 (ckeditor)',
                placeholder: 'Escriba aqui',
                tooltip: 'Tooltip 13 (ckeditor)',
                required: 'true',
                value: 'Texto de prueba<br/><br/>Adios<br/>',
            });
            const div = saltos.core.html('<div id="widget" style="width:600px;" />');
            div.append(widget);
            document.body.innerHTML = '';
            document.body.append(div);
        });
        await page.waitForFunction(() => document.querySelector('#ckeditor').ckeditor);

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
