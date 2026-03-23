
/**
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
 * Bootstrap helper module
 *
 * This fie contains useful functions related to the bootstrap widgets, allow to create widgets and
 * other plugins suck as plots or rich editors
 *
 * @gallery     => id, class, label, images, color
 */

/**
 * Gallery constructor helper
 *
 * This function returns a gallery object, you can pass some arguments as:
 *
 * @id     => the id used to set the reference for to the object
 * @class  => allow to add more classes to the default img-fluid
 * @label  => this parameter is used as text for the label
 * @images => the array with images, each image can be a string or object
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 *
 * This widget requires venobox, masonry and imagesloaded
 *
 * This widget requires the venobox, masonry and imagesloaded libraries and can be loaded
 * automatically using the require feature:
 *
 * @lib/venobox/venobox.min.css
 * @lib/venobox/venobox.min.js
 * @lib/masonry/masonry.pkgd.min.js
 * @lib/imagesloaded/imagesloaded.pkgd.min.js
 */
saltos.bootstrap.__field.gallery = field => {
    saltos.core.check_params(field, ['id', 'class', 'images', 'color', 'shadow', 'rounded']);
    let _class = 'col';
    if (field.class !== '') {
        _class = field.class;
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border = `border border-${color}-subtle`;
    if (field.color === 'none') {
        border = 'border-0';
    }
    let obj = saltos.core.html(`
        <div id="${field.id}" class="container-fluid">
            <div class="row">
            </div>
        </div>
    `);
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    if (typeof field.images === 'object') {
        for (const key in field.images) {
            let val = field.images[key];
            if (typeof val === 'string') {
                val = {image: val};
            }
            saltos.core.check_params(val, ['image', 'title']);
            const img = saltos.core.html(`
                <div class="${_class} p-1">
                    <a href="${val.image}" class="venobox" data-gall="${field.id}" title="${val.title}">
                        <img src="${val.image}"
                            class="img-fluid img-thumbnail ${border} p-0 ${rounded} ${shadow}"/>
                    </a>
                </div>
            `);
            obj.querySelector('.row').append(img);
        }
    }
    const element = obj.querySelector('.row');
    saltos.core.require([
        'lib/venobox/venobox.min.css',
        'lib/venobox/venobox.min.js',
        'lib/masonry/masonry.pkgd.min.js',
        'lib/imagesloaded/imagesloaded.pkgd.min.js',
    ], () => {
        const msnry = new Masonry(element, {
            percentPosition: true,
        });
        imagesLoaded(element).on('progress', () => {
            msnry.layout();
        });
        new VenoBox();
    });
    obj = saltos.bootstrap.__label_combine(field, obj);
    return obj;
};
