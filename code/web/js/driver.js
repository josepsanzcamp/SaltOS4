
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
 *
 * SaltOS: Framework to develop Rich Internet Applications
 * Copyright (C) 2007-2026 by Josep Sanz Campderrós
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
 * Driver module
 *
 * Intended to be used as an abstraction layer to allow multiple screens configurations
 */

/**
 * Driver object
 *
 * This object stores the functions used by the layouts widgets and must work with all screens
 */
saltos.driver = {};

/**
 * Driver init
 *
 * This function initialize the driver screen, detects and replaces the styles, compute
 * the paddings checking the contents of the top and bottom layouts, initialize the filter
 * and the autosave features, and executes the specific init function.
 *
 * @arg => the desired action to do
 */
saltos.driver.init = async arg => {
    if (document.getElementById('saltos-driver-styles')) {
        document.getElementById('saltos-driver-styles').remove();
    }
    document.getElementById('screen').append(saltos.driver.styles());
    // Detect needed padding
    const has_top = document.getElementById('top').innerHTML.length;
    const has_bottom = document.getElementById('bottom').innerHTML.length;
    let new_class;
    if (!has_top && !has_bottom) {
        new_class = 'py-3';
    } else if (!has_top) {
        new_class = 'pt-3';
    } else if (!has_bottom) {
        new_class = 'pb-3';
    } else {
        new_class = '';
    }
    // Remove and apply the old and new paddings
    document.querySelectorAll('#one, #two, #three').forEach(item => {
        ['py-3', 'pt-3', 'pb-3'].forEach(item2 => {
            item.classList.remove(item2);
        });
        if (new_class != '') {
            item.classList.add(new_class);
        }
    });
    // To check the list preferences
    if (arg == 'list') {
        await saltos.filter.init();
        saltos.filter.select();
        saltos.filter.load('last');
    }
    // To check the autosave feature
    if (['create', 'edit'].includes(arg)) {
        if (saltos.autosave.restore('two,one')) {
            saltos.app.modal('Attention', 'Data restored from the previous session', {color: 'danger'});
        }
        saltos.autosave.init('two,one');
    }
    // Old feature
    const type = document.getElementById('screen').dataset.type;
    saltos.driver.__types[type].init(arg);
};

/**
 * Driver open
 *
 * This function launch the specific open action for the screen type
 *
 * @arg => this argument is bypassed to the destination
 */
saltos.driver.open = arg => {
    const type = document.getElementById('screen').dataset.type;
    saltos.driver.__types[type].open(arg);
};

/**
 * Driver close
 *
 * This function close the current app using the history back if it is available,
 * otherwise use the specific close action for the screen type
 *
 * @arg => this argument forces to execute the specific driver close
 */
saltos.driver.close = arg => {
    if (arg !== undefined && saltos.core.eval_bool(arg)) {
        // Old feature
        const type = document.getElementById('screen').dataset.type;
        saltos.driver.__types[type].close(arg);
        return;
    }
    // Disable all autoclose
    document.querySelectorAll('[autoclose]').forEach(item => {
        item.removeAttribute('autoclose');
    });
    // Continue
    const url1 = window.location.href;
    window.history.back();
    setTimeout(() => {
        const url2 = window.location.href;
        if (url1 == url2) {
            // Old feature
            const type = document.getElementById('screen').dataset.type;
            saltos.driver.__types[type].close(arg);
        }
    }, 100);
};

/**
 * Driver cancel
 *
 * This function works in conjuntion with the autosave module, and checks if the
 * current screen contains new data, in this case ask to the user if they want
 * continue.
 *
 * This function checks that modal is close, otherwise an old confirm is used
 * to ask to the user.
 *
 * If the user decides continue to close, then the saltos.driver.close is executed
 * bypassing the arg argument.
 *
 * @arg => this argument is bypassed to the destination
 */
saltos.driver.cancel = arg => {
    saltos.backup.restore('two,one');
    const data = saltos.app.get_data();
    if (Object.keys(data).length) {
        if (saltos.bootstrap.modal('isopen')) {
            const bool = confirm('Do you want to close this screen???');
            if (bool) {
                saltos.autosave.clear('two,one');
                saltos.driver.close(arg);
            }
            return;
        }
        saltos.app.modal('Close this screen???', 'Do you want to close this screen???', {
            buttons: [{
                label: 'Yes',
                color: 'success',
                icon: 'check-lg',
                onclick: () => {
                    saltos.autosave.clear('two,one');
                    saltos.driver.close(arg);
                },
            },{
                label: 'No',
                color: 'danger',
                icon: 'x-lg',
                autofocus: true,
                onclick: () => {},
            }],
            color: 'danger',
        });
        return;
    }
    saltos.autosave.clear('two,one');
    saltos.driver.close(arg);
};

/**
 * Driver search
 *
 * This function implement the search feature associated to tables and lists
 * using the filters fields
 *
 * @arg => unused at this scope
 */
saltos.driver.search = arg => {
    document.getElementById('page').value = '0';
    saltos.backup.restore('top+one');
    const data = saltos.app.get_data(true);
    saltos.filter.update('last', data);
    const app = saltos.hash.get().split('/').at(1);
    // Restore the more button
    const obj = document.getElementById('more');
    if (obj && 'set_disabled' in obj && typeof obj.set_disabled == 'function') {
        obj.set_disabled(false);
    }
    // Continue
    saltos.app.ajax({
        url: `app/${app}/list/data`,
        data: data,
        success: response => {
            document.getElementById('list').set(response);
            document.getElementById('one').scrollTop = 0;
        },
    });
};

/**
 * Driver search
 *
 * This function implement the reset feature associated to the filters fields
 *
 * @arg => unused at this scope
 */
saltos.driver.reset = arg => {
    saltos.backup.restore('top+one');
    const types = ['text', 'hidden', 'integer', 'float', 'color', 'date', 'time',
        'datetime', 'textarea', 'ckeditor', 'codemirror', 'select', 'multiselect',
        'checkbox', 'switch', 'password', 'file', 'excel', 'tags', 'onetag'];
    for (const i in saltos.form.__form.fields) {
        const field = saltos.form.__form.fields[i];
        if (!types.includes(field.type)) {
            continue;
        }
        const obj = document.getElementById(field.id);
        if (!obj) {
            continue;
        }
        // Check to prevent objects in value
        if (typeof field.value != 'object') {
            obj.value = field.value;
        }
        // Special case for widgets with set
        if ('set' in obj && typeof obj.set == 'function') {
            obj.set(field.value);
        }
    }
    saltos.driver.search();
};

/**
 * Driver more
 *
 * This function implement the more feature associated to tables and lists
 * using the filters fields
 *
 * @arg => unused at this scope
 */
saltos.driver.more = arg => {
    document.getElementById('page').value = parseInt(document.getElementById('page').value, 10) + 1;
    saltos.backup.restore('top+one');
    const data = saltos.app.get_data(true);
    const app = saltos.hash.get().split('/').at(1);
    saltos.app.ajax({
        url: `app/${app}/list/data`,
        data: data,
        success: response => {
            if (!response.data.length) {
                saltos.app.toast('Response', 'There is no more data', {color: 'warning'});
                // Disable the more button
                const obj = document.getElementById('more');
                if (obj && 'set_disabled' in obj && typeof obj.set_disabled == 'function') {
                    obj.set_disabled(true);
                }
                // Continue
                return;
            }
            document.getElementById('list').add(response);
        },
    });
};

/**
 * Driver insert
 *
 * This function implement the insert feature associated to the current app fields
 *
 * @arg => unused at this scope
 */
saltos.driver.insert = arg => {
    saltos.backup.restore('two,one');
    if (!saltos.app.check_required()) {
        saltos.app.toast('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    const data = saltos.app.get_data();
    if (!Object.keys(data).length) {
        saltos.app.toast('Warning', 'No data found', {color: 'danger'});
        return;
    }
    const app = saltos.hash.get().split('/').at(1);
    saltos.app.ajax({
        url: `app/${app}/insert`,
        data: data,
        proxy: 'network,queue',
        success: response => {
            if (response.status == 'ok') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text);
                }
                saltos.window.send(`saltos.${app}.update`);
                saltos.autosave.clear('two,one');
                saltos.driver.close();
                return;
            }
            if (response.status == 'ko') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text, {color: 'danger'});
                }
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * Driver update
 *
 * This function implement the update feature associated to the current app fields
 *
 * @arg => unused at this scope
 */
saltos.driver.update = arg => {
    saltos.backup.restore('two,one');
    if (!saltos.app.check_required()) {
        saltos.app.toast('Warning', 'Required fields not found', {color: 'danger'});
        return;
    }
    const data = saltos.app.get_data();
    if (!Object.keys(data).length) {
        saltos.app.toast('Warning', 'No changes detected', {color: 'danger'});
        return;
    }
    const app = saltos.hash.get().split('/').at(1);
    const id = saltos.hash.get().split('/').at(-1);
    saltos.app.ajax({
        url: `app/${app}/update/${id}`,
        data: data,
        proxy: 'network,queue',
        success: response => {
            if (response.status == 'ok') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text);
                }
                saltos.window.send(`saltos.${app}.update`);
                saltos.autosave.clear('two,one');
                saltos.driver.close();
                return;
            }
            if (response.status == 'ko') {
                if ('text' in response) {
                    saltos.app.toast('Response', response.text, {color: 'danger'});
                }
                return;
            }
            saltos.app.show_error(response);
        },
    });
};

/**
 * Driver delete
 *
 * This function implement the delete feature associated to the current app register
 *
 * @arg => this field can contain the hash of the deletion
 */
saltos.driver.delete = async arg => {
    if (saltos.bootstrap.modal('isopen')) {
        // Disable all autoclose
        document.querySelectorAll('[autoclose]').forEach(item => {
            item.removeAttribute('autoclose');
        });
        // Continue
        saltos.bootstrap.modal('close');
        while (saltos.bootstrap.modal('isopen')) {
            await new Promise(resolve => setTimeout(resolve, 1));
        }
    }
    saltos.app.modal('Delete???', 'Do you want to delete this register???', {
        buttons: [{
            label: 'Yes',
            color: 'success',
            icon: 'check-lg',
            autofocus: true,
            onclick: () => {
                let app = saltos.hash.get().split('/').at(1);
                let id = saltos.hash.get().split('/').at(-1);
                if (typeof arg == 'string') {
                    app = arg.split('/').at(1);
                    id = arg.split('/').at(-1);
                }
                saltos.app.ajax({
                    url: `app/${app}/delete/${id}`,
                    proxy: 'network',
                    success: response => {
                        if (response.status == 'ok') {
                            if ('text' in response) {
                                saltos.app.toast('Response', response.text);
                            }
                            saltos.window.send(`saltos.${app}.update`);
                            // arg has valid data when is called from the list, and in
                            // this case, it is improtant to don't close the current view
                            if (arg === undefined) {
                                saltos.driver.close();
                            }
                            return;
                        }
                        if (response.status == 'ko') {
                            if ('text' in response) {
                                saltos.app.toast('Response', response.text, {color: 'danger'});
                            }
                            return;
                        }
                        saltos.app.show_error(response);
                    },
                });
            },
        },{
            label: 'No',
            color: 'danger',
            icon: 'x-lg',
            onclick: () => {},
        }],
        color: 'danger',
    });
};

/**
 * Driver placeholder
 *
 * This function sets a placeholder object in the element identified by the arg
 *
 * @id     => the id of the element where do you want to put the placeholder
 * @color  => the color of the widget (primary, secondary, success, danger, warning, info, none)
 */
saltos.driver.placeholder = arg => {
    saltos.core.check_params(arg, ['id', 'color', 'shadow', 'rounded']);
    if (!arg.color) {
        arg.color = 'primary';
    }
    let shadow = 'shadow';
    if (arg.shadow) {
        shadow = arg.shadow;
    }
    let rounded = 'rounded';
    if (arg.rounded) {
        rounded = arg.rounded;
    }
    const obj = saltos.core.html(`
        <div class="form-control ${rounded} ${shadow} border-0
            bg-${arg.color}-subtle opacity-50 h-100 driver-placeholder"></div>
    `);
    obj.append(saltos.core.html(`
        <style>
            .driver-placeholder {
                background-image: url(img/logo_white.svg);
                background-repeat: no-repeat;
                background-position: center;
                background-size: 75% 75%;
            }
            html[data-bs-theme=dark] .driver-placeholder {
                background-image: url(img/logo_black.svg);
            }
        </style>
    `));
    document.getElementById(arg.id).replaceChildren(obj);
};

/**
 * Driver styles
 *
 * This function returns the style object that contains the tricks to do
 * that the screen with verticals scrolls runs as expected
 *
 * @arg => the break-size used in the driver screen, xl as default
 */
saltos.driver.styles = (arg = 'xl') => {
    const sizes = {
        xs: 0,
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200,
        xxl: 1400,
    };
    const size = sizes[arg];
    const height = document.getElementById('header').offsetHeight +
        document.getElementById('top').offsetHeight +
        document.getElementById('bottom').offsetHeight +
        document.getElementById('footer').offsetHeight;
    return saltos.core.html(`
        <style id="saltos-driver-styles">
            @media (min-width: ${size}px) {
                .overflow-auto-${arg} {
                    height: calc(100vh - ${height}px - 0.5px);
                    overflow: auto;
                }
            }
        </style>
    `);
};

/**
 * Initializes the column resizing feature for screen layouts with two or three columns.
 *
 * - Skips initialization if the resizable styles are already added to the DOM.
 * - Only applies to layouts of type `type2` or `type3` (multi-column).
 * - Injects a `<style>` element that defines the appearance and behavior of the resizable handles.
 * - Delegates the actual resizing logic to internal helper functions:
 *   - `saltos.driver.__resizable_2cols()` for two-column layouts
 *   - `saltos.driver.__resizable_3cols()` for three-column layouts
 *
 * Notes:
 * - The resizable handle is 16px wide and visually centered on the column edge.
 * - On hover, the handle shows a dashed border and background highlight.
 */
saltos.driver.resizable = () => {
    if (document.getElementById('saltos-driver-resizable')) {
        return;
    }
    const type = document.getElementById('screen').dataset.type;
    if (type.includes('type1')) {
        return;
    }
    document.getElementById('screen').append(saltos.core.html(`
        <style id="saltos-driver-resizable">
            .saltos-resizable {
                position: absolute;
                top: 0;
                bottom: 0;
                padding: 0;
                width: 16px; /* Área más grande para facilitar el clic */
                margin-left: -8px; /* Centrar el handle sobre el borde */
            }

            .saltos-noselect {
                user-select: none !important;
            }
        </style>
    `));
    if (saltos.core.get_browser() == 'chrome') {
        // The follow fix tries to hide the scrollbar
        // but allowing to scroll at the same time
        document.getElementById('screen').append(saltos.core.html(`
            <style>
                ::-webkit-scrollbar {
                    display: none;
                }
            </style>
        `));
    }
    if (type.includes('type2')) {
        saltos.driver.__resizable_2cols();
    }
    if (type.includes('type3')) {
        saltos.driver.__resizable_3cols();
    }
};

/**
 * Enables dynamic resizing of two-column layouts (type2).
 *
 * - Targets elements with IDs `one` and `two`, assumed to be columns inside a shared parent row.
 * - Loads previous column sizes from `saltos.storage`, if available, or infers them from current CSS classes.
 * - Applies Bootstrap `col-xl-*` classes to adjust the column widths.
 * - Injects a draggable handle between the two columns using the `interact` library.
 *
 * Behavior:
 * - When dragging the handle:
 *   - Calculates new column sizes based on pointer position (1–11 for col1, rest for col2).
 *   - Enforces a minimum size of 1 column for each.
 *   - Updates classes dynamically and saves the result per app hash.
 * - Respects window resizes and updates the handle position accordingly.
 *
 * Storage key format: `saltos.driver.resizable/{app}`, where `{app}` is extracted from the hash.
 */
saltos.driver.__resizable_2cols = () => {
    const row = document.getElementById('one').parentElement;
    const one = document.getElementById('one');
    const two = document.getElementById('two');

    const save_handle_cols = values => {
        const app = saltos.hash.get().split('/').at(1);
        const key = `saltos.driver.resizable/${app}`;
        saltos.storage.setItem(key, JSON.stringify(values));
    };

    const load_handle_cols = () => {
        const app = saltos.hash.get().split('/').at(1);
        const key = `saltos.driver.resizable/${app}`;
        const data = saltos.storage.getItem(key);
        try {
            const parsed = JSON.parse(data);
            if (Array.isArray(parsed) && parsed.length == 2) {
                return parsed;
            }
        } catch (error) {
            // Nothing to do
        }
        let col1 = 0;
        let col2 = 0;
        for (let i = 1; i <= 12; i++) {
            if (!col1 && one.classList.contains(`col-xl-${i}`)) {
                col1 = i;
            }
            if (!col2 && two.classList.contains(`col-xl-${i}`)) {
                col2 = i;
            }
        }
        if (!col1 || !col2) {
            throw new Error('Unknown cols first case');
        }
        return [col1, col2];
    };

    let [col1, col2] = load_handle_cols();

    const apply_handle_cols = () => {
        for (let i = 1; i <= 12; i++) {
            one.classList.remove(`col-xl-${i}`);
            two.classList.remove(`col-xl-${i}`);
        }
        one.classList.add(`col-xl-${col1}`);
        two.classList.add(`col-xl-${col2}`);
    };

    apply_handle_cols();

    const create_handle = id => {
        const handle = document.createElement('div');
        handle.classList.add('saltos-resizable');
        handle.id = id;
        row.appendChild(handle);
        return handle;
    };

    const handle = create_handle('handle');

    const updateHandlePosition = () => {
        handle.style.left = `${one.offsetLeft + one.offsetWidth}px`;
    };

    window.addEventListener('resize', updateHandlePosition);
    updateHandlePosition();

    interact(handle).draggable({
        cursorChecker: () => 'ew-resize',
        listeners: {
            start() {
                document.body.classList.add('saltos-noselect');
            },
            move(event) {
                const rowRect = row.getBoundingClientRect();
                const rowWidth = row.offsetWidth;

                let pointerX = event.client.x - rowRect.left;
                pointerX = Math.max(0, Math.min(rowWidth, pointerX));
                handle.style.left = `${pointerX}px`;

                let newCol = Math.round((pointerX / rowWidth) * 12);
                newCol = Math.max(1, Math.min(11, newCol));

                col1 = newCol;
                col2 = 12 - col1;
                col2 = Math.max(1, col2);
                apply_handle_cols();
            },
            end() {
                document.body.classList.remove('saltos-noselect');
                updateHandlePosition();
                save_handle_cols([col1, col2]);
            }
        }
    });
};

/**
 * Enables dynamic resizing of three-column layouts (type3).
 *
 * - Targets elements with IDs `one`, `two`, and `three`, assumed to be columns inside a shared parent row.
 * - Loads saved column sizes from `saltos.storage` or infers them from current Bootstrap `col-xl-*` classes.
 * - Applies calculated column widths via Bootstrap classes.
 *
 * Behavior:
 * - Creates two draggable handles (`handle1` between columns 1 and 2, and `handle2` between columns 2 and 3).
 * - When dragging:
 *   - `handle1`: adjusts the size of column 1 (`col1`), recalculates `col2` keeping `col3` fixed.
 *   - `handle2`: adjusts the size of column 2 (`col2`), recalculates `col3` keeping `col1` fixed.
 * - Enforces constraints to ensure each column has at least 1 unit (out of 12 total).
 * - Updates handle positions on window resize and after drag end.
 * - Saves current sizes per app hash to persistent storage.
 *
 * Storage key format: `saltos.driver.resizable/{app}`, where `{app}` is extracted from the hash.
 */
saltos.driver.__resizable_3cols = () => {
    const row = document.getElementById('one').parentElement;
    const one = document.getElementById('one');
    const two = document.getElementById('two');
    const three = document.getElementById('three');

    const save_handle_cols = values => {
        const app = saltos.hash.get().split('/').at(1);
        const key = `saltos.driver.resizable/${app}`;
        saltos.storage.setItem(key, JSON.stringify(values));
    };

    const load_handle_cols = () => {
        const app = saltos.hash.get().split('/').at(1);
        const key = `saltos.driver.resizable/${app}`;
        const data = saltos.storage.getItem(key);
        try {
            const parsed = JSON.parse(data);
            if (Array.isArray(parsed) && parsed.length == 3) {
                return parsed;
            }
        } catch (error) {
            // Nothing to do
        }
        let col1 = 0;
        let col2 = 0;
        let col3 = 0;
        for (let i = 1; i <= 12; i++) {
            if (!col1 && one.classList.contains(`col-xl-${i}`)) {
                col1 = i;
            }
            if (!col2 && two.classList.contains(`col-xl-${i}`)) {
                col2 = i;
            }
            if (!col3 && three.classList.contains(`col-xl-${i}`)) {
                col3 = i;
            }
        }
        if (!col1 || !col2 || !col3) {
            throw new Error('Unknown cols first case');
        }
        return [col1, col2, col3];
    };

    let [col1, col2, col3] = load_handle_cols();

    const apply_handle_cols = () => {
        for (let i = 1; i <= 12; i++) {
            one.classList.remove(`col-xl-${i}`);
            two.classList.remove(`col-xl-${i}`);
            three.classList.remove(`col-xl-${i}`);
        }
        one.classList.add(`col-xl-${col1}`);
        two.classList.add(`col-xl-${col2}`);
        three.classList.add(`col-xl-${col3}`);
    };

    apply_handle_cols();

    const create_handle = id => {
        const handle = document.createElement('div');
        handle.classList.add('saltos-resizable');
        handle.id = id;
        row.appendChild(handle);
        return handle;
    };

    const handle1 = create_handle('handle1');
    const handle2 = create_handle('handle2');

    const update_handle_positions = () => {
        handle1.style.left = `${one.offsetLeft + one.offsetWidth}px`;
        handle2.style.left = `${two.offsetLeft + two.offsetWidth}px`;
    };

    window.addEventListener('resize', update_handle_positions);
    update_handle_positions();

    interact(handle1).draggable({
        cursorChecker: () => 'ew-resize',
        listeners: {
            start() {
                document.body.classList.add('saltos-noselect');
            },
            move(event) {
                const rowRect = row.getBoundingClientRect();
                const rowWidth = row.offsetWidth;

                let pointerX = event.client.x - rowRect.left;
                pointerX = Math.max(0, Math.min(rowWidth, pointerX));
                handle1.style.left = `${pointerX}px`;

                let newCol = Math.round((pointerX / rowWidth) * 12);
                newCol = Math.max(1, Math.min(11 - col3, newCol));

                col1 = newCol;
                col2 = 12 - col1 - col3;
                col2 = Math.max(1, col2);
                apply_handle_cols();
            },
            end() {
                document.body.classList.remove('saltos-noselect');
                update_handle_positions();
                save_handle_cols([col1, col2, col3]);
            }
        }
    });

    interact(handle2).draggable({
        cursorChecker: () => 'ew-resize',
        listeners: {
            start() {
                document.body.classList.add('saltos-noselect');
            },
            move(event) {
                const rowRect = row.getBoundingClientRect();
                const rowWidth = row.offsetWidth;

                let pointerX = event.client.x - rowRect.left;
                pointerX = Math.max(0, Math.min(rowWidth, pointerX));
                handle2.style.left = `${pointerX}px`;

                let newCol = Math.round((pointerX / rowWidth) * 12);
                newCol = Math.max(col1, Math.min(11, newCol));

                col2 = newCol - col1;
                col2 = Math.max(1, col2);
                col3 = 12 - col1 - col2;
                apply_handle_cols();
            },
            end() {
                document.body.classList.remove('saltos-noselect');
                update_handle_positions();
                save_handle_cols([col1, col2, col3]);
            }
        }
    });
};

/**
 * Driver search if needed
 *
 * This function launch the saltos.driver.search action if the action is the
 * same before and after the setTimeout, too is launched if action source and
 * the action destination complains with some pair of actions defined in the
 * arg argument.
 *
 * @arg => an array with pairs of actions
 */
saltos.driver.search_if_needed = arg => {
    const action1 = saltos.hash.get().split('/').at(2);
    setTimeout(() => {
        const action2 = saltos.hash.get().split('/').at(2);
        //~ console.log(action1 + ' => ' + action2);
        if (action1 == action2) {
            // Old feature
            saltos.driver.search();
            saltos.favicon.run();
            return;
        }
        for (const key in arg) {
            const val = arg[key];
            if (action1 == val[0] && action2 == val[1]) {
                // Old feature
                saltos.driver.search();
                saltos.favicon.run();
                return;
            }
        }
    }, 100);
};

/**
 * Driver internal object
 *
 * This object stores the functions used by the main driver
 */
saltos.driver.__types = {};

/********************************************************************************
 * DRIVER TYPE 1
 ********************************************************************************/

/**
 * Driver type1 object
 *
 * This object stores the functions used by the type1 driver
 */
saltos.driver.__types.type1 = {};

/**
 * Driver type1 template
 *
 * This function returns the type1 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type1.template = arg => {
    const obj = saltos.core.html(`
        <div id="screen" data-type="type1">
            <div id="header" class="sticky-top"></div>
            <div class="container-xl">
                <div class="row">
                    <div id="top" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="one" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="bottom" class="col-12"></div>
                </div>
            </div>
            <div id="footer" class="sticky-bottom"></div>
        </div>
    `);
    return obj;
};

/**
 * Driver type1 init
 *
 * This function initialize the type1 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type1.init = arg => {
    if (arg == 'list') {
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search();
            saltos.favicon.run();
        });
    }
    if (arg == 'view') {
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.hash.trigger();
            saltos.favicon.run();
        });
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type1 open
 *
 * This function open a new window
 *
 * @arg => the desired url
 */
saltos.driver.__types.type1.open = arg => {
    saltos.window.open(arg);
};

/**
 * Driver type1 close
 *
 * This function close the window
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type1.close = arg => {
    saltos.window.close();
};

/********************************************************************************
 * DRIVER TYPE 2
 ********************************************************************************/

/**
 * Driver type2 object
 *
 * This object stores the functions used by the type2 driver
 */
saltos.driver.__types.type2 = {};

/**
 * Driver type2 template
 *
 * This function returns the type2 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type2.template = arg => {
    const obj = saltos.core.html(`
        <div id="screen" data-type="type2">
            <div id="header"></div>
            <div class="container-fluid">
                <div class="row">
                    <div id="top" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="one" class="col-xl-6 overflow-auto-xl"></div>
                    <div id="two" class="col-xl-6 overflow-auto-xl"></div>
                </div>
                <div class="row">
                    <div id="bottom" class="col-12"></div>
                </div>
            </div>
            <div id="footer"></div>
        </div>
    `);
    return obj;
};

/**
 * Driver type2 init
 *
 * This function initialize the type2 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type2.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.driver.placeholder({
                id: 'two',
                color: 'secondary',
            });
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['create', 'view'],
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
    if (['view', 'edit'].includes(arg)) {
        const obj = document.getElementById('list');
        if (obj) {
            obj.querySelectorAll('td').forEach(item => {
                item.classList.remove('table-active');
            });
            const id = saltos.hash.get().split('/').at(-1);
            const row = document.getElementById(`list_${id}`);
            if (row) {
                row.querySelectorAll('td').forEach(item => {
                    item.classList.add('table-active');
                });
            }
        }
    }
};

/**
 * Driver type2 open
 *
 * This function open a new content
 *
 * @arg => the desired url
 */
saltos.driver.__types.type2.open = arg => {
    saltos.autosave.save('two,one');
    saltos.autosave.purge('two,one');
    saltos.hash.add(arg);
    saltos.app.send_request(arg);
};

/**
 * Driver type2 close
 *
 * This function close the two zone of the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type2.close = arg => {
    saltos.driver.placeholder({
        id: 'two',
        color: 'secondary',
    });
    // Hash part
    const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
    saltos.hash.add(temp);
};

/********************************************************************************
 * DRIVER TYPE 3
 ********************************************************************************/

/**
 * Driver type3 object
 *
 * This object stores the functions used by the type3 driver
 */
saltos.driver.__types.type3 = {};

/**
 * Driver type3 template
 *
 * This function returns the type3 template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type3.template = arg => {
    const obj = saltos.core.html(`
        <div id="screen" data-type="type3">
            <div id="header"></div>
            <div class="container-fluid">
                <div class="row">
                    <div id="top" class="col-12"></div>
                </div>
                <div class="row">
                    <div id="one" class="col-xl-4 overflow-auto-xl"></div>
                    <div id="two" class="col-xl-4 overflow-auto-xl"></div>
                    <div id="three" class="col-xl-4 overflow-auto-xl"></div>
                </div>
                <div class="row">
                    <div id="bottom" class="col-12"></div>
                </div>
            </div>
            <div id="footer"></div>
        </div>
    `);
    return obj;
};

/**
 * Driver type3 init
 *
 * This function initialize the type3 driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type3.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.driver.placeholder({
                id: 'two',
                color: 'secondary',
            });
            saltos.driver.placeholder({
                id: 'three',
                color: 'secondary',
            });
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['create', 'view'],
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
        if (!document.getElementById('two').textContent.trim().length) {
            let temp = saltos.hash.get().split('/');
            temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
            saltos.app.send_request(temp);
        }
        const arr = saltos.hash.get().split('/');
        if (arr.length < 5) {
            saltos.driver.placeholder({
                id: 'three',
                color: 'secondary',
            });
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
    if (['view', 'edit'].includes(arg)) {
        const obj = document.getElementById('list');
        if (obj) {
            obj.querySelectorAll('td').forEach(item => {
                item.classList.remove('table-active');
            });
            const id = saltos.hash.get().split('/').at(-1);
            const row = document.getElementById(`list_${id}`);
            if (row) {
                row.querySelectorAll('td').forEach(item => {
                    item.classList.add('table-active');
                });
            }
        }
    }
};

/**
 * Driver type3 open
 *
 * This function bypass to the type2 driver
 *
 * @arg => the desired url
 */
saltos.driver.__types.type3.open = saltos.driver.__types.type2.open;

/**
 * Driver type3 close
 *
 * This function close the three and/or two zone of the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type3.close = arg => {
    const arr = saltos.hash.get().split('/');
    const action = saltos.hash.get().split('/').at(2);
    if (arr.length >= 5 && action == 'view') {
        saltos.driver.placeholder({
            id: 'three',
            color: 'secondary',
        });
        // Hash part
        let temp = saltos.hash.get().split('/');
        temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
        saltos.hash.add(temp);
    } else {
        saltos.driver.placeholder({
            id: 'two',
            color: 'secondary',
        });
        // Hash part
        const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
        saltos.hash.add(temp);
    }
};

/********************************************************************************
 * DRIVER TYPE 1 MODAL
 ********************************************************************************/

/**
 * Driver type1modal object
 *
 * This object stores the functions used by the type1modal driver
 */
saltos.driver.__types.type1modal = {};

/**
 * Driver type1modal template
 *
 * This function returns the type1modal template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type1modal.template = arg => {
    const obj = saltos.driver.__types.type1.template();
    obj.dataset.type = 'type1modal';
    const div = saltos.core.html(`<div id="two" class="d-none"></div>`);
    obj.querySelector('#one').after(div);
    return obj;
};

/**
 * Driver type1modal init
 *
 * This function initialize the type1modal driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type1modal.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.bootstrap.modal('close');
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
        if (document.getElementById('two').textContent.trim().length) {
            const obj = document.getElementById('two').firstElementChild;
            if (!saltos.bootstrap.modal('isopen')) {
                saltos.gettext.bootstrap.modal({
                    close: 'Close',
                    body: obj,
                    class: 'modal-xl',
                });
            } else {
                document.querySelector('.modal-body').replaceChildren(obj);
            }
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
};

/**
 * Driver type1modal open
 *
 * This function bypass to the type2 driver
 *
 * @arg => the desired url
 */
saltos.driver.__types.type1modal.open = saltos.driver.__types.type2.open;

/**
 * Driver type1modal close
 *
 * This function close the modal
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type1modal.close = arg => {
    saltos.bootstrap.modal('close');
    // Hash part
    const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
    saltos.hash.add(temp);
};

/********************************************************************************
 * DRIVER TYPE 1 FLUID
 ********************************************************************************/

/**
 * Driver type1fluid object
 *
 * This object stores the functions used by the type1modal driver
 */
saltos.driver.__types.type1fluid = {};

/**
 * Create type1fluid template
 *
 * This function generates a template for the type1fluid driver, configuring specific
 * attributes and layout modifications. It sets the 'type' attribute to 'type1fluid'
 * and adjusts the layout of the corresponding element for proper display.
 */
saltos.driver.__types.type1fluid.template = arg => {
    const obj = saltos.driver.__types.type1.template(); // Reuses the template from type1
    obj.dataset.type = 'type1fluid'; // Set the type attribute to 'type1fluid'
    obj.querySelector('.container-xl').classList.replace('container-xl', 'container-fluid');
    return obj;
};

/**
 * Initialization, open, and close handlers for type1fluid
 *
 * These methods inherit their implementations from the 'type1' driver, allowing
 * reuse of core functionality for initializing, opening, and closing type1 resources.
 */
saltos.driver.__types.type1fluid.init = saltos.driver.__types.type1.init; // Inherits initialization
saltos.driver.__types.type1fluid.open = saltos.driver.__types.type1.open; // Inherits opening logic
saltos.driver.__types.type1fluid.close = saltos.driver.__types.type1.close; // Inherits closing logic

/********************************************************************************
 * DRIVER TYPE 1 FULL
 ********************************************************************************/

/**
 * Driver type1full object
 *
 * This object stores the functions used by the type1full driver
 */
saltos.driver.__types.type1full = {};

/**
 * Create type1full template
 *
 * This function generates a template for the type1full driver, configuring specific
 * attributes and layout modifications. It sets the 'type' attribute to 'type1full'
 * and adjusts the layout of the corresponding element for proper display.
 */
saltos.driver.__types.type1full.template = arg => {
    const obj = saltos.driver.__types.type1modal.template(); // Reuses the template from type1modal
    obj.dataset.type = 'type1full'; // Set the type attribute to 'type1full'
    obj.querySelector('.container-xl').classList.replace('container-xl', 'container-fluid');
    return obj;
};

/**
 * Initialization, open, and close handlers for type1full
 *
 * These methods inherit their implementations from the 'type1modal' driver, allowing
 * reuse of core functionality for initializing, opening, and closing type1modal resources.
 */
saltos.driver.__types.type1full.init = saltos.driver.__types.type1modal.init; // Inherits initialization
saltos.driver.__types.type1full.open = saltos.driver.__types.type1modal.open; // Inherits opening logic
saltos.driver.__types.type1full.close = saltos.driver.__types.type1modal.close; // Inherits closing logic

/********************************************************************************
 * DRIVER TYPE 2 MODAL
 ********************************************************************************/

/**
 * Driver type2modal object
 *
 * This object stores the functions used by the type2modal driver
 */
saltos.driver.__types.type2modal = {};

/**
 * Driver type2modal template
 *
 * This function returns the type2modal template to mount the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type2modal.template = arg => {
    const obj = saltos.driver.__types.type2.template();
    obj.dataset.type = 'type2modal';
    const div = saltos.core.html(`<div id="three" class="d-none"></div>`);
    obj.querySelector('#two').after(div);
    return obj;
};

/**
 * Driver type2modal init
 *
 * This function initialize the type2modal driver screen for the arg requested
 *
 * @arg => the desired action to do
 */
saltos.driver.__types.type2modal.init = arg => {
    if (arg == 'list') {
        const action = saltos.hash.get().split('/').at(2);
        if (!['create', 'view', 'edit'].includes(action)) {
            saltos.driver.placeholder({
                id: 'two',
                color: 'secondary',
            });
            saltos.bootstrap.modal('close');
        }
        // Program the update event
        const app = saltos.hash.get().split('/').at(1);
        saltos.window.set_listener(`saltos.${app}.update`, event => {
            saltos.driver.search_if_needed([
                ['create', 'view'],
                ['edit', 'view'],
            ]);
        });
    }
    if (['create', 'view', 'edit'].includes(arg)) {
        if (!document.getElementById('one').textContent.trim().length) {
            const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
            saltos.app.send_request(temp);
        }
        if (!document.getElementById('two').textContent.trim().length) {
            let temp = saltos.hash.get().split('/');
            temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
            saltos.app.send_request(temp);
        }
        if (document.getElementById('three').textContent.trim().length) {
            const obj = document.getElementById('three').firstElementChild;
            if (!saltos.bootstrap.modal('isopen')) {
                saltos.gettext.bootstrap.modal({
                    close: 'Close',
                    body: obj,
                    class: 'modal-xl',
                });
            } else {
                document.querySelector('.modal-body').replaceChildren(obj);
            }
        }
    }
    if (arg == 'view') {
        // This disable the fields to use as readonly
        saltos.backup.restore('two,one');
        saltos.app.form_disabled(true);
    }
    if (['view', 'edit'].includes(arg)) {
        const obj = document.getElementById('list');
        if (obj) {
            obj.querySelectorAll('td').forEach(item => {
                item.classList.remove('table-active');
            });
            const id = saltos.hash.get().split('/').at(-1);
            const row = document.getElementById(`list_${id}`);
            if (row) {
                row.querySelectorAll('td').forEach(item => {
                    item.classList.add('table-active');
                });
            }
        }
    }
};

/**
 * Driver type2modal open
 *
 * This function bypass to the type2 driver
 *
 * @arg => the desired url
 */
saltos.driver.__types.type2modal.open = saltos.driver.__types.type2.open;

/**
 * Driver type2modal close
 *
 * This function close the modal and/or two zone of the screen
 *
 * @arg => unused at this scope
 */
saltos.driver.__types.type2modal.close = arg => {
    const arr = saltos.hash.get().split('/');
    const action = saltos.hash.get().split('/').at(2);
    if (arr.length >= 5 && action == 'view') {
        saltos.bootstrap.modal('close');
        // Hash part
        let temp = saltos.hash.get().split('/');
        temp = [...temp.slice(0, 3), ...temp.slice(4, 5)].join('/');
        saltos.hash.add(temp);
    } else {
        saltos.driver.placeholder({
            id: 'two',
            color: 'secondary',
        });
        // Hash part
        const temp = saltos.hash.get().split('/').slice(0, 2).join('/');
        saltos.hash.add(temp);
    }
};
