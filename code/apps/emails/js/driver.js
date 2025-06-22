
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
 * Email application
 *
 * This application implements the typical features associated with email functionality,
 * including templates and initialization processes for handling email-related tasks.
 */

/**
 * Driver type2emails object
 *
 * This object stores the functions and properties used by the email driver,
 * providing the necessary methods for managing email operations.
 */
saltos.driver.__types.type2emails = {};

/**
 * Create type2emails template
 *
 * This function generates a template for the email driver, configuring specific
 * attributes and layout modifications. It sets the 'type' attribute to 'type2emails'
 * and adjusts the layout of the corresponding element for proper display.
 */
saltos.driver.__types.type2emails.template = arg => {
    const obj = saltos.driver.__types.type2modal.template(); // Reuses the template from type2modal
    obj.dataset.type = 'type2emails'; // Set the type attribute to 'type2emails'
    obj.querySelector('#one').classList.replace('col-xl', 'col-xl-4'); // Adjust layout classes
    return obj;
};

/**
 * Initialization, open, and close handlers for type2emails
 *
 * These methods inherit their implementations from the 'type2modal' driver, allowing
 * reuse of core functionality for initializing, opening, and closing email resources.
 */
saltos.driver.__types.type2emails.init = saltos.driver.__types.type2modal.init; // Inherits initialization
saltos.driver.__types.type2emails.open = saltos.driver.__types.type2modal.open; // Inherits opening logic
saltos.driver.__types.type2emails.close = saltos.driver.__types.type2modal.close; // Inherits closing logic
