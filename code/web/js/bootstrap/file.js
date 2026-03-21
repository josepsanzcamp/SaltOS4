
/**
 *  ____        _ _    ___  ____    _  _    ___
 * / ___|  __ _| | |_ / _ \/ ___|  | || |  / _ \
 * \___ \ / _` | | __| | | \___ \  | || |_| | | |
 *  ___) | (_| | | |_| |_| |___) | |__   _| |_| |
 * |____/ \__,_|_|\__|\___/|____/     |_|(_)___/
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
 * @file        => id, class, DS, RQ, AF, AK, multiple, tooltip, label, color, OC
 */

/**
 * File constructor helper
 *
 * This function returns an input object of type password, you can pass some arguments as:
 *
 * @id        => the id used by the object
 * @class     => allow to add more classes to the default form-control
 * @disabled  => this parameter raise the disabled flag
 * @required  => this parameter raise the required flag
 * @autofocus => this parameter raise the autofocus flag
 * @multiple  => this parameter raise the multiple flag, intended to select more files at time
 * @tooltip   => this parameter raise the title flag
 * @accesskey => the key used as accesskey parameter
 * @label     => this parameter is used as text for the label
 * @color     => the color of the widget (primary, secondary, success, danger, warning, info, none)
 * @onchange  => the function executed when onchange event is detected
 *
 * Notes:
 *
 * This control allow to select file from the tradicional input, and automatically, send it
 * to the server using the addfiles action, add a row in the widget's table to show information
 * about the new file and allow too to delete it using the trash button.
 *
 * To get the data, the controls store each file information in each added row of the table and
 * in addition, too join all information in a data structure of the input of type file.
 *
 * The difference between this control and the older controls is that they send the files to
 * the server and store the information related to the file on the server to be processed after
 * the real upload action.
 */
saltos.bootstrap.__field.file = field => {
    saltos.core.check_params(field, ['class', 'id', 'value', 'data', 'disabled',
                                     'required', 'onchange', 'autofocus', 'multiple',
                                     'tooltip', 'accesskey', 'color', 'shadow', 'rounded']);
    let disabled = '';
    if (saltos.core.eval_bool(field.disabled)) {
        disabled = 'disabled';
    }
    let required = '';
    if (saltos.core.eval_bool(field.required)) {
        required = 'required';
    }
    let autofocus = '';
    if (saltos.core.eval_bool(field.autofocus)) {
        autofocus = 'autofocus';
    }
    let multiple = '';
    if (saltos.core.eval_bool(field.multiple)) {
        multiple = 'multiple';
    }
    let color = 'primary';
    if (field.color !== '') {
        color = field.color;
    }
    let border1 = `border border-${color}-subtle`;
    let border2 = `border-${color}-subtle`;
    if (field.color === 'none') {
        border1 = 'border-0';
        border2 = '';
    }
    let shadow = 'shadow';
    if (field.shadow !== '') {
        shadow = field.shadow;
    }
    let rounded = 'rounded';
    if (field.rounded !== '') {
        rounded = field.rounded;
    }
    const obj = saltos.core.html(`
        <div>
            <div class="${shadow} ${rounded}">
                <input type="file" class="form-control ${rounded} ${border1} ${field.class}" id="${field.id}"
                    ${disabled} ${required} ${autofocus} ${multiple}
                    data-bs-accesskey="${field.accesskey}" data-bs-title="${field.tooltip}" />
            </div>
            <div class="form-control ${rounded} p-0 border-0 ${shadow} table-responsive mt-3 d-none">
                <table class="table table-striped table-hover ${border2} mb-0">
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    `);
    if (field.tooltip !== '') {
        obj.querySelectorAll('input').forEach(item => {
            saltos.bootstrap.__tooltip_helper(item);
        });
    }
    // This helper programs the input file data update
    const __update_data_input_file = input => {
        const data = [];
        const tabla = input.parentElement.nextElementSibling.querySelector('table');
        tabla.querySelectorAll('tr').forEach(item => {
            data.push(item.data);
        });
        input.data = data;
    };
    __update_data_input_file(obj.querySelector('input'));
    // This helper programs the delete file button
    const __button_remove_file = event => {
        const row = event.target.parentElement.parentElement;
        const table = row.parentElement.parentElement;
        const input = table.parentElement.previousElementSibling.querySelector('input');
        saltos.core.ajax({
            url: 'api/?/upload/delfile',
            data: JSON.stringify(row.data),
            method: 'post',
            content_type: 'application/json',
            success: response => {
                if (!saltos.app.check_response(response)) {
                    return;
                }
                row.data = response;
                // If server removes the file, i remove the row
                if (response.file === '') {
                    row.remove();
                }
                // If not there are files, hide the table
                if (table.querySelectorAll('tr').length === 0) {
                    table.parentElement.classList.add('d-none');
                }
                __update_data_input_file(input);
            },
            token: saltos.token.get(),
            lang: saltos.gettext.get(),
            abortable: true,
        });
    };
    // This helper paints each row of the table
    const __add_row_file = (input, table, file) => {
        // Show the table
        table.parentElement.classList.remove('d-none');
        // Add the row for the new file
        const row = saltos.core.html('tbody', `
            <tr id="${file.id}" class="align-middle">
                <td class="text-break">${file.name}</td>
                <td class="w-25">
                    <div class="progress" role="progressbar" aria-label="Upload percent"
                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                </td>
                <td class="p-0" style="width: 1%"><button
                    class="btn bi-trash border-0" type="button"></button></td>
            </tr>
        `);
        // Store the data in the row
        row.data = file;
        // Program de remove button
        row.querySelector('button').addEventListener('click', __button_remove_file);
        // Add the row
        table.querySelector('tbody').append(row);
        __update_data_input_file(input);
        return row;
    };
    // Program the automatic upload
    obj.querySelector('input').addEventListener('change', async event => {
        const input = event.target;
        const files = event.target.files;
        const table = event.target.parentElement.nextElementSibling.querySelector('table');
        for (let i = 0; i < files.length; i++) {
            // Prepare the data to send
            const data = {
                id: saltos.core.uniqid(),
                app: saltos.hash.get(),
                name: files[i].name,
                size: files[i].size,
                type: files[i].type,
                data: '',
                error: '',
                file: '',
                hash: '',
            };
            // Add the row to the table
            const row = __add_row_file(input, table, data);
            // Get the local file using syncronous techniques
            const reader = new FileReader();
            reader.readAsDataURL(files[i]);
            while (!reader.result && !reader.error) {
                await new Promise(resolve => setTimeout(resolve, 1));
            }
            // If there is a file
            if (reader.result) {
                data.data = reader.result;
                // Check for void type
                if (!data.type) {
                    data.type = data.data.split(';')[0].split(':')[1];
                }
                // This allow multiple uploads in parallel
                ((data, row) => {
                    const ajax = new XMLHttpRequest();
                    ajax.open('POST', 'api/?/upload/addfile');
                    ajax.setRequestHeader('Content-Type', 'application/json');
                    ajax.setRequestHeader('Authorization', 'Bearer ' + saltos.token.get());
                    ajax.setRequestHeader('Accept-Language', saltos.gettext.get());
                    ajax.setRequestHeader('X-Proxy-Order', 'no');
                    ajax.onload = event => {
                        if (ajax.status < 200 || ajax.status >= 300) {
                            throw new Error(`HTTP ${ajax.status}: ${ajax.statusText} - ${ajax.responseText}`);
                        }
                        let data = ajax.response;
                        let type = ajax.getResponseHeader('content-type');
                        type = saltos.core.toString(type).toUpperCase();
                        if (type.includes('JSON')) {
                            data = JSON.parse(ajax.responseText);
                        } else if (type.includes('XML')) {
                            data = ajax.responseXML;
                        }
                        if (!saltos.app.check_response(data)) {
                            return;
                        }
                        row.data = data;
                        __update_data_input_file(input);
                    };
                    ajax.onerror = event => {
                        throw new Error(`Network error (readyState: ${ajax.readyState})`);
                    };
                    ajax.onprogress = event => {
                        if (event.lengthComputable) {
                            const percent = Math.round((event.loaded / event.total) * 100);
                            row.querySelector('.progress-bar').style.width = percent + '%';
                            row.querySelector('.progress').setAttribute('aria-valuenow', percent);
                        }
                    };
                    ajax.upload.onprogress = ajax.onprogress;
                    ajax.send(JSON.stringify(data));
                })(data, row);
            }
            // If there is an error
            if (reader.error) {
                data.error = reader.error.message;
                throw reader.error;
            }
        }
        input.value = '';
    });
    // Program the set function
    obj.querySelector('input').set = data => {
        const input = obj.querySelector('input');
        const tabla = input.parentElement.nextElementSibling.querySelector('table');
        tabla.querySelectorAll('tr').forEach(item => {
            item.remove();
        });
        __update_data_input_file(input);
        for (const i in data) {
            const input = obj.querySelector('input');
            const table = input.parentElement.nextElementSibling.querySelector('table');
            const row = __add_row_file(input, table, data[i]);
            const percent = 100;
            row.querySelector('.progress-bar').style.width = percent + '%';
            row.querySelector('.progress').setAttribute('aria-valuenow', percent);
        }
    };
    // Initialize the input with the previous function
    obj.querySelector('input').set(field.data);
    // Added the onchange event
    if (field.onchange !== '') {
        obj.querySelectorAll('input[type=file]').forEach(item => {
            saltos.bootstrap.__onchange_helper(item, field.onchange);
        });
    }
    // Continue
    obj.prepend(saltos.bootstrap.__label_helper(field));
    return obj;
};
