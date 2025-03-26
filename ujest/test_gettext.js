
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
 * Core unit tests
 *
 * This file contains the core unit tests
 */

/**
 * Load all needed files of the project
 */
const files = `bootstrap,core,gettext,storage`.split(',');
for (const i in files) {
    const file = files[i].trim();
    require(`../code/web/js/${file}.js`);
}

/**
 * TODO
 */
beforeEach(() => {
    jest.resetAllMocks();
    jest.spyOn(console, 'log').mockImplementation(() => {});
});

/**
 * TODO
 */
afterEach(() => {
    jest.restoreAllMocks();
});

/**
 * saltos.gettext.bootstrap.set/get/get_short/unset
 *
 * This function performs the tests of the set/get/get_short and unset functions
 */
test('saltos.gettext.set/get/get_short/unset', () => {
    // set should store language and short language
    saltos.gettext.set('en-US');
    expect(saltos.gettext.get()).toBe('en_US');
    expect(saltos.gettext.get_short()).toBe('en');

    // unset should remove stored language and short language
    saltos.gettext.unset();
    expect(saltos.gettext.get()).toBe(null);
    expect(saltos.gettext.get_short()).toBe(null);

    // T should return translated text if available
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            en_US: {
                test: 'translated_global'
            }
        }
    };
    expect(saltos.gettext.T('test')).toBe('translated_global');

    // T should return translated text if available
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            testApp: {
                en_US: {
                    test: 'translated'
                }
            },
            en_US: {
                test: 'translated_global'
            }
        }
    };
    expect(saltos.gettext.T('test')).toBe('translated');

    // T should return original text if no translation is found
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {}
    };
    expect(saltos.gettext.T('not_found')).toBe('not_found');

    // T should throws an error for unsupported obj type
    expect(() => { saltos.gettext.T(123); }).toThrow('Unknown gettext typeof number');
});

/**
 * saltos.gettext.bootstrap.field
 *
 * This function performs the tests of the field function
 */
test('saltos.gettext.bootstrap.field', () => {
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            en_US: {
                text1: 'translated1',
                text2: 'translated2',
                text3: 'translated3',
                text4: 'translated4',
                text5: 'translated5',
                text6: 'translated6',
                text7: 'translated7',
                text8: 'translated8',
                text9: 'translated9',
            }
        }
    };

    let html = saltos.gettext.bootstrap.field({
        type: 'text',
        label: 'text1',
        tooltip: 'text2',
        placeholder: 'text3'
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(html).toContain('translated3');

    html = saltos.gettext.bootstrap.field({
        type: 'table',
        header: {
            'field1': 'text1',
            'field2': {label: 'text2'},
        },
        actions: {
            'view': {label: 'text3', tooltip: 'text4', onclick: 'fn'},
            'edit': {label: 'text5', tooltip: 'text6', onclick: 'fn'},
        },
        data: [
            {id: 1, name: 'josep', surname: 'sanz', actions: {view: {arg: 'app/x/view/1'}}},
            {id: 2, name: 'josep', surname: 'sanz', actions: {view: {label: 'text7', tooltip: 'text8',
                arg: 'app/x/view/2'}}},
        ],
        footer: 'text9',
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).not.toContain('text4');
    expect(html).not.toContain('text5');
    expect(html).not.toContain('text6');
    expect(html).not.toContain('text7');
    expect(html).not.toContain('text8');
    expect(html).not.toContain('text9');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(html).toContain('translated3');
    expect(html).toContain('translated4');
    expect(html).toContain('translated5');
    expect(html).toContain('translated6');
    expect(html).toContain('translated7');
    expect(html).toContain('translated8');
    expect(html).toContain('translated9');

    html = saltos.gettext.bootstrap.field({
        type: 'table',
        header: {
            'field1': 'text1',
            'field2': {label: 'text2'},
        },
        footer: {
            'field1': 'text3',
            'field2': {value: 'text4'},
        },
        nodata: 'text5',
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).not.toContain('text4');
    expect(html).not.toContain('text5');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(html).toContain('translated3');
    expect(html).toContain('translated4');
    expect(html).toContain('translated5');

    html = saltos.gettext.bootstrap.field({
        type: 'select',
        rows: {
            'row1': {label: 'text1', value: 'text2'},
            'row3': {label: 'text3', value: 'text4'},
        },
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).toContain('text4');
    expect(html).toContain('translated1');
    expect(html).not.toContain('translated2');
    expect(html).toContain('translated3');
    expect(html).not.toContain('translated4');

    html = saltos.gettext.bootstrap.field({
        type: 'alert',
        title: 'text1',
        text: 'text2',
        body: 'text3',
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(html).toContain('translated3');

    html = saltos.gettext.bootstrap.field({
        type: 'dropdown',
        menu: [
            {label: 'text1', tooltip: 'text2'},
            {label: 'text3', tooltip: 'text4'},
        ],
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).not.toContain('text4');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(html).toContain('translated3');
    expect(html).toContain('translated4');
});

/**
 * saltos.gettext.bootstrap.modal
 *
 * This function performs the tests of the modal function
 */
test('saltos.gettext.bootstrap.modal', () => {
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            en_US: {
                text1: 'translated1',
                text2: 'translated2',
            }
        }
    };

    document.body.innerHTML = '';
    saltos.gettext.bootstrap.modal({
        id: 'modalTest',
        title: 'text1',
        body: 'text2',
        footer: null,
    });
    const html = document.body.innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(document.querySelector('#modalTest')).not.toBeNull();
});

/**
 * saltos.gettext.bootstrap.toast
 *
 * This function performs the tests of the toast function
 */
test('saltos.gettext.bootstrap.toast', () => {
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            en_US: {
                text1: 'translated1',
                text2: 'translated2',
            }
        }
    };

    document.body.innerHTML = '';
    saltos.gettext.bootstrap.toast({
        id: 'toastTest',
        title: 'text1',
        subtitle: 'text2',
        body: null,
    });
    const html = document.body.innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(document.querySelector('#toastTest')).not.toBeNull();
});

/**
 * saltos.gettext.bootstrap.menu
 *
 * This function performs the tests of the menu function
 */
test('saltos.gettext.bootstrap.menu', () => {
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            en_US: {
                text1: 'translated1',
                text2: 'translated2',
                text3: 'translated3',
                text4: 'translated4',
            }
        }
    };

    const html = saltos.gettext.bootstrap.menu({
        menu: [
            {label: 'text1'},
            {label: 'text2'},
            {},
            {menu: [
                {label: 'text3'},
                {label: 'text4'},
                {},
            ]}
        ],
    }).innerHTML;
    expect(html).not.toContain('text1');
    expect(html).not.toContain('text2');
    expect(html).not.toContain('text3');
    expect(html).not.toContain('text4');
    expect(html).toContain('translated1');
    expect(html).toContain('translated2');
    expect(html).toContain('translated3');
    expect(html).toContain('translated4');

    expect(saltos.gettext.bootstrap.menu({}).innerHTML).toBe('');
});

/**
 * saltos.gettext.bootstrap.offcanvas
 *
 * This function performs the tests of the offcanvas function
 */
test('saltos.gettext.bootstrap.offcanvas', () => {
    saltos.gettext.cache = {
        app: 'testApp',
        lang: 'en_US',
        locale: {
            en_US: {
                text1: 'translated1',
            }
        }
    };

    document.body.innerHTML = '';
    saltos.gettext.bootstrap.offcanvas({
        id: 'offcanvasTest',
        title: 'text1',
        body: null,
    });
    const html = document.body.innerHTML;
    expect(html).not.toContain('text1');
    expect(html).toContain('translated1');
    expect(document.querySelector('#offcanvasTest')).not.toBeNull();
});
