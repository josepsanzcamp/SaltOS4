
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
 * Form helper module
 *
 * This module provides the needed tools to implement the form feature
 */

/**
 * Form constructor helper object
 *
 * This object allow to the constructor to use a rational structure for a quick access of each
 * helper
 */
saltos.form = {};

/**
 * Data helper object
 *
 * This object allow to the app to store the data of the fields map
 */
saltos.form.__form = {
    fields: [],
    templates: {},
    loading: 0,
    timer: null,
};

/**
 * Form data helper
 *
 * This function sets the values of the request to the objects placed in the document, too as bonus
 * extra, it tries to search the field spec in the array to update the value of the field spec to
 * allow that the get_data can differ between the original data and the modified data.
 */
saltos.form.data = (data, sync = true) => {
    // Check that data is found
    if (data === null) {
        return;
    }
    // Check for attr template_id
    if ('#attr' in data && 'template_id' in data['#attr']) {
        const template_id = data['#attr'].template_id;
        if (!Array.isArray(data.value)) {
            throw new Error(`Data for template ${template_id} is not an array of rows`);
        }
        for (const key in data.value) {
            const val = data.value[key];
            if (parseInt(key)) {
                const temp1 = saltos.form.__layout_template_helper(template_id, key);
                const temp2 = saltos.form.layout(temp1, 'div');
                const temp3 = document.getElementById(template_id + '.' + (key - 1));
                temp3.after(temp2);
            }
            saltos.form.data(saltos.form.__data_template_helper(template_id, val, key));
        }
        return;
    }
    // Check for the correctness of the data
    if (Array.isArray(data)) {
        throw new Error(`Data is an array instead of an object of key and val pairs`);
    }
    // Continue with the normal behaviour
    for (const key in data) {
        let val = data[key];
        if (val === null) {
            val = '';
        }
        // This updates the object
        const obj = document.getElementById(key);
        if (!obj) {
            continue;
        }
        // Check to prevent objects in value
        if (typeof val != 'object') {
            obj.value = val;
        }
        // Special case for iframes
        if (obj.hasAttribute('src')) {
            obj.src = val;
        }
        if (obj.hasAttribute('srcdoc')) {
            obj.srcdoc = val;
        }
        // Special case for widgets with set
        if ('set' in obj && typeof obj.set == 'function') {
            obj.set(val);
        }
        if (!sync) {
            continue;
        }
        // This updates the field spec searching in all backups
        for (const i in saltos.backup.__forms) {
            saltos.backup.__forms[i].fields.forEach(_this => {
                if (_this.id == key) {
                    _this.value = val;
                }
            });
        }
    }
};

/**
 * Data template helper
 *
 * This function allow to convert the data object that contains the values for the fields idenfidied
 * by the keys of the associative array into a data object with the keys ready to be used by the
 * fields of a template, this fields are of the follow structure: TEMPLATE_ID#ID#INDEX
 *
 * @template_id => the template identity used in the spec
 * @data        => the object that contains the data associated to the row
 * @index       => the index used in all fields of the template
 */
saltos.form.__data_template_helper = (template_id, data, index) => {
    for (const key in data) {
        const val = data[key];
        delete data[key];
        data[template_id + '.' + index + '.' + key] = val;
    }
    return data;
};

/**
 * Layout template helper
 *
 * This function returns the template identified by the template_id for the specified index, ready
 * to be used by the saltos.form.layout function.
 *
 * @template_id => the template identity used in the spec
 * @index       => the index used in all fields of the template
 */
saltos.form.__layout_template_helper = (template_id, index) => {
    const template = saltos.core.copy_object(saltos.form.__form.templates[template_id]);
    template['#attr'].id = template_id + '.' + index;
    for (const key in template.value) {
        const val = template.value[key];
        if ('id' in val['#attr']) {
            const id = val['#attr'].id;
            val['#attr'].id = template_id + '.' + index + '.' + id;
        }
    }
    return template;
};

/**
 * Form layout helper
 *
 * This function process the layout command, its able to process nodes as container, row, col and div
 * and all form_field defined in the bootstrap file.
 *
 * Each objects as container, rows and cols, have two modes of works:
 *
 * 1) normal mode => requires that the user specify all layout, container, row, col and fields.
 *
 * 2) auto mode => only requires set auto='true' to the container, row or col, and with this, all childrens
 * of the node are created inside the correct element, if the auto appear in the container then a container,
 * a row and one col for each field are created, if the auto appear in the row then a row with one col for
 * each field are created, if the auto appear in the col then one col for each field are created, this mode
 * allow to optimize and speedup the creation of each screen by allow to reduce the amount of xml needed
 * to define each screen but allowing to be a lot of specific and define from all to zero auto features.
 *
 * Notes:
 *
 * This function add the fields to the saltos.form.__form.fields, this allow to the saltos.app.get_data
 * can retrieve the desired information of the fields.
 */
saltos.form.layout = (layout, extra) => {
    if (extra === undefined) {
        saltos.form.__form.fields = [];
        saltos.form.__form.templates = {};
    }
    // This code fix a problem when layout contains the append element
    let append = '';
    if (saltos.core.is_attr_value(layout) && 'append' in layout['#attr']) {
        append = layout['#attr'].append;
        layout = layout.value;
        const temp = append.split(',');
        let obj = null;
        for (const i in temp) {
            obj = document.getElementById(temp[i]);
            if (obj) {
                append = temp[i];
                break;
            }
        }
        if (!obj) {
            throw new Error(`Layout append ${append} not found`);
        }
    }
    // This code fix a problem when layout contains the content of a template
    if (saltos.core.is_attr_value(layout)) {
        layout = {[layout['#attr'].type]: layout};
    }
    // Continue
    let arr = [];
    for (let key in layout) {
        let val = layout[key];
        key = saltos.core.fix_key(key);
        let attr = {};
        let value = val;
        if (saltos.core.is_attr_value(val)) {
            attr = val['#attr'];
            value = val.value;
        }
        if (!('type' in attr)) {
            attr.type = key;
        }
        // Check for template_id attr
        if ('template_id' in attr) {
            // Store it in the templates container
            const template_id = attr.template_id;
            delete val['#attr'].template_id;
            saltos.form.__form.templates[template_id] = val;
            // Modify the id of the first elements to convert it to the format TEMPLATE_ID#ID#0
            // Note: the follow line returns a copy of the object!!!
            val = saltos.form.__layout_template_helper(template_id, 0);
        }
        // Continue with original idea of use an entire specified layout
        if (
            ['container', 'col', 'row'].includes(key) &&
            'auto' in attr && saltos.core.eval_bool(attr.auto)
        ) {
            val = saltos.form.__layout_auto_helper[key](val);
            const temp = saltos.form.layout(val, 'arr');
            for (const i in temp) {
                arr.push(temp[i]);
            }
        } else if (['container', 'col', 'row', 'div'].includes(key)) {
            const obj = saltos.gettext.bootstrap.field(attr);
            const temp = saltos.form.layout(value, 'arr');
            for (const i in temp) {
                obj.append(temp[i]);
            }
            arr.push(obj);
        } else {
            if (typeof value == 'object') {
                for (const key2 in value) {
                    if (!(key2 in attr)) {
                        attr[key2] = value[key2];
                    }
                }
            } else if (!('value' in attr)) {
                attr.value = value;
            }
            saltos.core.check_params(attr, ['id', 'source']);
            if (attr.id == '') {
                attr.id = saltos.core.uniqid();
            }
            saltos.form.__form.fields.push(attr);
            let obj = null;
            if (attr.source != '') {
                obj = saltos.app.__source_helper(attr);
            } else {
                obj = saltos.gettext.bootstrap.field(attr);
            }
            arr.push(obj);
        }
    }
    // Some extra features to allow that returns only the array
    if (extra == 'arr') {
        return arr;
    }
    let div = saltos.core.html('<div></div>');
    for (const i in arr) {
        div.append(arr[i]);
    }
    div = saltos.core.optimize(div);
    // Some extra features to allow that returns only the div
    if (extra == 'div') {
        return div;
    }
    // Defaut feature that add the div to the body's document
    let obj = null;
    if (append != '') {
        // Do a backup of the fields and templates using the append key
        saltos.backup.save(append);
        // Continue
        obj = document.getElementById(append);
        obj.replaceChildren(div);
    } else {
        obj = document.body;
        document.body.append(div);
    }
    obj.querySelectorAll('[autofocus]').forEach(_this => {
        _this.focus();
    });
};

/**
 * Form layout auto helper object
 *
 * This object stores the functions used as layout_auto_helper for containers, rows and cols
 */
saltos.form.__layout_auto_helper = {};

/**
 * Form layout auto helper for containers
 *
 * This functions implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @id              => defines the id used by the container element
 * @container_class => defines the class used by the container element
 * @container_style => defines the style used by the container element
 * @row_class       => defines the class used by the row element
 * @row_style       => defines the style used by the row element
 * @col_class       => defines the class used by the col element
 * @col_style       => defines the style used by the col element
 */
saltos.form.__layout_auto_helper.container = layout => {
    const attr = layout['#attr'];
    saltos.core.check_params(attr, ['id', 'container_class', 'container_style',
        'row_class', 'row_style', 'col_class', 'col_style']);
    // Store and delete to prevent the id propagation to the next childrens
    const id = layout['#attr'].id;
    delete layout['#attr'].id;
    const temp = saltos.form.__layout_auto_helper.row(layout);
    // This is the new layout object created with one container and the row inside
    layout = {
        container: {
            'value': {},
            '#attr': {
                id: id,
                class: attr.container_class,
                style: attr.container_style,
            }
        }
    };
    for (const i in temp) {
        layout.container.value[i] = temp[i];
    }
    return layout;
};

/**
 * Form layout auto helper for rows
 *
 * This functions implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @id              => defines the id used by the container element
 * @row_class       => defines the class used by the row element
 * @row_style       => defines the style used by the row element
 * @col_class       => defines the class used by the col element
 * @col_style       => defines the style used by the col element
 */
saltos.form.__layout_auto_helper.row = layout => {
    const attr = layout['#attr'];
    saltos.core.check_params(attr, ['id', 'row_class', 'row_style', 'col_class', 'col_style']);
    // Store and delete to prevent the id propagation to the next childrens
    const id = layout['#attr'].id;
    delete layout['#attr'].id;
    const temp = saltos.form.__layout_auto_helper.col(layout);
    // This is the new layout object created with one row and the cols inside
    layout = {
        row: {
            'value': {},
            '#attr': {
                id: id,
                class: attr.row_class,
                style: attr.row_style,
            }
        }
    };
    for (const i in temp) {
        layout.row.value[i] = temp[i];
    }
    return layout;
};

/**
 * Form layout auto helper for cols
 *
 * This functions implements the auto feature used by the layout function, allow to specify the
 * follow arguments:
 *
 * @col_class       => defines the class used by the col element
 * @col_style       => defines the style used by the col element
 *
 * Notes:
 *
 * This function not allow to specify the id because in this functions, each object of the
 * layout is embedded inside of a col object and is not suitable to put an id attribute
 * to each col without repetitions.
 */
saltos.form.__layout_auto_helper.col = layout => {
    const attr = layout['#attr'];
    const value = layout.value;
    saltos.core.check_params(attr, ['col_class', 'col_style']);
    // This trick convert all entries of the object in an array with the keys and values
    const temp = [];
    for (const key in value) {
        temp.push([key, value[key]]);
    }
    // This is the new layout object created with one cols by each original field
    layout = {};
    let numcol = 0;
    while (temp.length) {
        const item = temp.shift();
        let col_class = attr.col_class;
        let col_style = attr.col_style;
        if (saltos.core.is_attr_value(item[1])) {
            if ('col_class' in item[1]['#attr']) {
                col_class = item[1]['#attr'].col_class;
            }
            if ('col_style' in item[1]['#attr']) {
                col_style = item[1]['#attr'].col_style;
            }
        }
        // This trick allow to hide the hidden fields
        if (saltos.core.fix_key(item[0]) == 'hidden') {
            col_class = 'col d-none';
            col_style = '';
        }
        layout['col#' + numcol] = {
            'value': {},
            '#attr': {
                class: col_class,
                style: col_style,
            }
        };
        layout['col#' + numcol].value[item[0]] = item[1];
        numcol++;
    }
    return layout;
};

/**
 * Form style helper
 *
 * This function allow to specify styles, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 *
 * @data => the object that contains the styles requirements (can be file or inline)
 */
saltos.form.style = async data => {
    for (let key in data) {
        const val = data[key];
        key = saltos.core.fix_key(key);
        if (key == 'inline') {
            const style = document.createElement('style');
            style.innerHTML = val;
            document.head.append(style);
        }
        if (key == 'file') {
            try {
                const response = await fetch(val, {credentials: 'omit'});
                if (!response.ok) {
                    throw new Error(`${response.status} ${response.statusText} loading ${val}`);
                }
                const data = await response.text();
                const style = document.createElement('style');
                style.innerHTML = data;
                document.head.append(style);
            } catch (error) {
                throw new Error(`${error.name} ${error.message} loading ${val}`);
            }
        }
    }
};

/**
 * Form javascript helper
 *
 * This function allow to specify scripts, you can use the inline of file key to specify
 * what kind of usage do you want to do.
 *
 * @data => the object that contains the javascript requirements (can be file or inline)
 */
saltos.form.javascript = async data => {
    for (let key in data) {
        const val = data[key];
        key = saltos.core.fix_key(key);
        if (key == 'inline') {
            const script = document.createElement('script');
            script.innerHTML = val;
            document.head.append(script);
        }
        if (key == 'file') {
            try {
                const response = await fetch(val, {credentials: 'omit'});
                if (!response.ok) {
                    throw new Error(`${response.status} ${response.statusText} loading ${val}`);
                }
                const data = await response.text();
                const script = document.createElement('script');
                script.innerHTML = data;
                document.head.append(script);
            } catch (error) {
                throw new Error(`${error.name} ${error.message} loading ${val}`);
            }
        }
    }
};

/**
 * Form title helper
 *
 * This function sets the document title, too it checks the existence of the About
 * header received in the ajax calls and stored in the saltos object to be used in the
 * last part of the title.
 *
 * @title => The title that you want to set in the page
 */
saltos.form.title = title => {
    if (saltos.core.is_attr_value(title)) {
        const attr = title['#attr'];
        if ('append' in attr) {
            const append = attr.append.split(',');
            for (const i in append) {
                if (append[i] == 'modal') {
                    // Try for modal
                    const obj = document.querySelector('.modal-title');
                    if (obj) {
                        obj.innerHTML = T(title.value);
                        return;
                    }
                } else if (append[i] == 'offcanvas') {
                    // Try for offcanvas
                    const obj = document.querySelector('.offcanvas-title');
                    if (obj) {
                        obj.innerHTML = T(title.value);
                        return;
                    }
                } else {
                    // Try for element id
                    const obj = document.getElementById(append[i]);
                    if (obj) {
                        obj.innerHTML = T(title.value);
                        return;
                    }
                }
            }
            setTimeout(() => {
                saltos.form.title(title);
            }, 1);
            return;
        }
        throw new Error(`Unknown attr`);
    }
    // Continue with default behaviour
    if ('about' in saltos.core) {
        document.title = T(title) + ' - ' + saltos.core.about;
        return;
    }
    document.title = T(title);
};

/**
 * Form screen helper
 *
 * This function adds and removes the spinner to emulate the loading effect screen, too is able
 * to clear the screen by removing all contents of the body
 *
 * @action => use loading, unloading or clear to execute the desired action
 */
saltos.form.screen = action => {
    switch (action) {
        case 'loading': {
            clearTimeout(saltos.form.__form.timer);
            saltos.form.__form.loading++;
            const obj = document.getElementById('loading');
            if (obj) {
                return false;
            }
            document.body.append(saltos.core.html(`
                <div id="loading">
                    <div class="modal-backdrop show" style="z-index:202"></div>
                    <div class="position-fixed top-50 start-50 translate-middle" style="z-index:203">
                        <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;">
                        </div>
                    </div>
                </div>
            `));
            return true;
        }
        case 'unloading': {
            clearTimeout(saltos.form.__form.timer);
            saltos.form.__form.loading--;
            if (saltos.form.__form.loading < 0) {
                saltos.form.__form.loading = 0;
            }
            if (saltos.form.__form.loading > 0) {
                return false;
            }
            const obj = document.getElementById('loading');
            if (!obj) {
                return false;
            }
            // This setTimeout allow to prevent the blinking effect caused by
            // consecutives loadings and unloadings by adding a delay in the
            // real unloading code
            saltos.form.__form.timer = setTimeout(() => {
                obj.remove();
            }, 100);
            return true;
        }
        case 'isloading': {
            const obj = document.getElementById('loading');
            if (obj) {
                return true;
            }
            return false;
        }
        case 'clear':
            const obj = document.getElementById('screen');
            if (!obj) {
                return false;
            }
            obj.remove();
            return true;
    }
    if ('__types' in saltos.driver) {
        const only = saltos.hash.get().split('/').at(-1);
        if (only == 'only') {
            action = 'type1';
        }
        if (action in saltos.driver.__types) {
            const obj = document.getElementById('screen');
            if (obj) {
                return false;
            }
            document.body.append(saltos.driver.__types[action].template());
            return true;
        }
    }
    throw new Error(`Screen action ${action} not found`);
};

/**
 * Form navbar helper
 *
 * This function create a navbar with their menus on the top of the page, more
 * info about this feature in the bootstrap documentation
 *
 * @navbar
 */
saltos.form.navbar = navbar => {
    navbar['#attr'] = saltos.app.__get_data_parser_helper(navbar['#attr']);
    navbar = saltos.core.join_attr_value(navbar);
    if (document.getElementById(navbar.id)) {
        return;
    }
    if ('items' in navbar) {
        for (const key in navbar.items) {
            let val = navbar.items[key];
            if (saltos.core.fix_key(key) == 'menu') {
                let _class = '';
                const menu = [];
                if (saltos.core.is_attr_value(val)) {
                    if ('class' in val['#attr']) {
                        _class = val['#attr'].class;
                    }
                    val = val.value;
                }
                for (const key2 in val) {
                    const val2 = val[key2];
                    // Trick to allow to put attr in the value node intended to use the eval=true
                    { // This curly brackets allow to create a block for the temp const
                        const temp = ['label', 'id', 'icon', 'disabled',
                                      'active', 'onclick', 'dropdown_menu_end'];
                        for (const i in temp) {
                            const j = temp[i];
                            if (typeof val2.value == 'object' &&
                                j in val2.value && !(j in val2['#attr'])) {
                                val2['#attr'][j] = val2.value[j];
                                delete val2.value[j];
                            }
                        }
                    } // Here the temp const disapear!!!
                    // Continue
                    if (typeof val2.value == 'string') {
                        menu.push(val2['#attr']);
                    } else if ('menu' in val2.value) {
                        const menu2 = [];
                        for (const key3 in val2.value.menu) {
                            const val3 = val2.value.menu[key3];
                            // Trick to allow to put attr in the value node intended to use the eval=true
                            const temp = ['label', 'id', 'icon', 'disabled', 'active', 'onclick', 'divider'];
                            for (const i in temp) {
                                const j = temp[i];
                                if (typeof val3.value == 'object' &&
                                    j in val3.value && !(j in val3['#attr'])) {
                                    val3['#attr'][j] = val3.value[j];
                                    delete val3.value[j];
                                }
                            }
                            // Continue
                            menu2.push(val3['#attr']);
                        }
                        menu.push({
                            ...val2['#attr'],
                            menu: menu2,
                        });
                    }
                }
                navbar.items[key] = saltos.gettext.bootstrap.menu({
                    class: _class,
                    menu: menu,
                });
            } else if (saltos.core.fix_key(key) == 'form') {
                let _class = '';
                if (saltos.core.is_attr_value(val)) {
                    if ('class' in val['#attr']) {
                        _class = val['#attr'].class;
                    }
                    val = val.value;
                }
                const obj = saltos.core.html(`<form class='${_class}' onsubmit='return false'></form>`);
                for (const key2 in val) {
                    const val2 = val[key2];
                    val2['#attr'].type = saltos.core.fix_key(key2);
                    obj.append(saltos.gettext.bootstrap.field(val2['#attr']));
                }
                navbar.items[key] = obj;
            }
        }
    }
    const obj = saltos.bootstrap.navbar(navbar);
    if ('append' in navbar) {
        const obj2 = document.getElementById(navbar.append);
        if (!obj2) {
            throw new Error(`Navbar append ${navbar.append} not found`);
        }
        obj2.replaceChildren(obj);
    } else {
        document.body.append(obj);
    }
};

/**
 * Load gettext helper
 *
 * This function load the gettext data from the api to the cli sapi
 */
saltos.form.gettext = array => {
    saltos.gettext.cache = array;
};
