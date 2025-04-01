
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
 * Driver emails object
 *
 * This object stores the functions and properties used by the email driver,
 * providing the necessary methods for managing email operations.
 */
saltos.driver.__types.emails = {};

/**
 * Create email template
 *
 * This function generates a template for the email driver, configuring specific
 * attributes and layout modifications. It sets the 'type' attribute to 'emails'
 * and adjusts the layout of the corresponding element for proper display.
 */
saltos.driver.__types.emails.template = arg => {
    const obj = saltos.driver.__types.type5.template(); // Reuses the template from type5
    obj.setAttribute('type', 'emails'); // Set the type attribute to 'emails'
    obj.querySelector('#one').classList.replace('col-xl', 'col-xl-4'); // Adjust layout classes
    return obj;
};

/**
 * Initialization, open, and close handlers for emails
 *
 * These methods inherit their implementations from the 'type5' driver, allowing
 * reuse of core functionality for initializing, opening, and closing email resources.
 */
saltos.driver.__types.emails.init = saltos.driver.__types.type5.init; // Inherits initialization from type5
saltos.driver.__types.emails.open = saltos.driver.__types.type5.open; // Inherits opening logic from type5
saltos.driver.__types.emails.close = saltos.driver.__types.type5.close; // Inherits closing logic from type5
