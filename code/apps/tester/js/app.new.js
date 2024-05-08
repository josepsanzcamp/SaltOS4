
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2024 by Josep Sanz Campderrós
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
 * Tester application
 *
 * This application implements the tipical features associated to tester
 */

/**
 * Tester object
 *
 * This object stores all function used by this app
 */
saltos.tester = {};

/**
 * Campo 8
 *
 * TODO
 */
saltos.tester.campo8 = () => {
    alert('button onclick');
};

/**
 * Campo 9
 *
 * TODO
 */
saltos.tester.campo9 = () => {
    saltos.bootstrap.modal({
        static: false,
        title: 'Titulo',
        close: 'Cerrar',
        body: `
            <div>
                Some text as placeholder. In real life you can have the elements you have
                chosen. Like, text, images, lists, etc.
            </div>
            <div class='dropdown mt-3'>
                <button class='btn btn-secondary dropdown-toggle' type='button'
                data-bs-toggle='dropdown'>
                    Dropdown button
                </button>
                <ul class='dropdown-menu'>
                    <li><a class='dropdown-item' href='#'>Action</a></li>
                    <li><a class='dropdown-item' href='#'>Another action</a></li>
                    <li><a class='dropdown-item' href='#'>Something else here</a></li>
                </ul>
            </div>
        `,
        footer: function() {
            var obj = saltos.core.html('<div></div>');
            obj.append(saltos.bootstrap.field({
                type: 'button',
                value: 'Aceptar',
                onclick: function() {
                    console.log('OK');
                    saltos.bootstrap.modal('close');
                }
            }));
            obj.append(saltos.bootstrap.field({
                type: 'button',
                value: 'Cancelar',
                class: 'ms-1',
                onclick: function() {
                    console.log('KO');
                    saltos.bootstrap.modal('close');
                },
            }));
            return obj;
        }()
    });
};

/**
 * Campo 10
 *
 * TODO
 */
saltos.tester.campo10 = () => {
    saltos.bootstrap.offcanvas({
        static: false,
        //class: 'offcanvas-start',
        title: 'Titulo',
        close: 'Cerrar',
        body: `
            <div>
                Some text as placeholder. In real life you can have the elements you have
                chosen. Like, text, images, lists, etc.
            </div>
            <div class='dropdown mt-3'>
                <button class='btn btn-secondary dropdown-toggle' type='button'
                data-bs-toggle='dropdown'>
                    Dropdown button
                </button>
                <ul class='dropdown-menu'>
                    <li><a class='dropdown-item' href='#'>Action</a></li>
                    <li><a class='dropdown-item' href='#'>Another action</a></li>
                    <li><a class='dropdown-item' href='#'>Something else here</a></li>
                </ul>
            </div>
        `,
    });
};

/**
 * Campo 11
 *
 * TODO
 */
saltos.tester.campo11 = () => {
    saltos.bootstrap.toast({
        //class: 'text-bg-primary',
        close: 'Cerrar',
        title: 'Hola mundo',
        subtitle: 'pues nada',
        body: 'Pues eso, hola mundo ' + new Date(),
    });
};

/**
 * Campo 22
 *
 * TODO
 */
saltos.tester.campo22 = () => {
    window.open('https://www.saltos.org/portal/es/estadisticas');
};
