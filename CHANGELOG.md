# Changelog

All notable changes to SaltOS 4 are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Each release is identified by its version and by its revision (`rN`, the
git commit count), as shown in the About dialog (`SaltOS v4.1 rN`).

## [Unreleased]

### Upgrade notes
- The web server protection no longer depends on `.htaccess` files, and the layout of the `web` directory has changed. After updating: run `make web` (or `bash ../scripts/migrate_instance.sh` inside each instance created with `make_instance.sh`), publish only the `web` directory using the recipe of your web server (`scripts/server.nginx.conf`, `scripts/server.apache.conf` or `scripts/server.htaccess`), disable the directory listing, restart PHP-FPM or Apache (the opcode cache keeps the old `web/api` path) and validate the result with `php api/index.php setup/server <api url>`, that must return a `count` of 0.
- The database schema is regenerated once after updating, because the changes are now detected using hashes and the existing tables have no hash yet. Run the setup right after updating (`make setuponly` or `php api/index.php setup`); it can take a while with big tables.
- The company has two new fields, the tax mode and the withholding mode, and both are set to "not used" after updating. Set the tax mode to "by line" in the company form to continue working with the taxes in invoices and quotes as before.
- The emails are organized in folders and the spam is the Junk folder, the spam state of the emails is removed. Inside `code` and inside each instance: run `php ../scripts/migrate_emails_folders.php export spam.json` before the setup to save the spam emails, run the setup, and run `php ../scripts/migrate_emails_folders.php import spam.json` to create the folders of the existing accounts and to move the existing emails to them: the sent emails to Sent, the spam to Junk and the others to Inbox. The import can be executed more than once.

### Added
- `setup/server` action (the old `setup/apache`) that validates any web server: exposed headers, `Authorization` support, private paths, directory listings, content types and the full code published by mistake.
- Web server recipes for nginx, Apache and shared hostings in `scripts/server.nginx.conf`, `scripts/server.apache.conf` and `scripts/server.htaccess`.
- `scripts/makeapps.php` to generate `web/apps`, `scripts/migrate_instance.sh` to migrate old instances and `scripts/router.php` for the PHP built-in web server.
- Withholdings in invoices and quotes (like the Spanish IRPF): the taxes app has a `type` field to define taxes and withholdings, each line stores its tax and its withholding, the taxes breakdown shows both in the form and in the PDF, and the total is the subtotal plus the taxes minus the withholdings. The products have a withholding field too, prepared to be used as default.
- Company fields `tax_mode` and `withholding_mode` to define how the taxes and the withholdings are requested in invoices and quotes: by line, one by document or not used. The default tax and the default withholding are applied to the new lines, and the columns and boxes that are not used are hidden in the form and in the PDF.
- Quick apps defined in yaml support the `options` widget, a select with a fixed list of options, and an optional where in the `select` spec.
- The titles of the columns of the jspreadsheet widgets are translated.
- Folders in the emails app: each POP3 account has the Inbox, Sent, Trash and Junk folders, created with the account, and the user can create, rename, move and delete other folders. The folders tree is shown in its own panel, opened with the folders button next to the search form button, with the enabled accounts first and opened and the disabled accounts at the end and closed, selecting an account (or the All button, for all the accounts together) shows all its emails except the ones of the trash and the junk, that are only shown selecting these folders, and selecting a folder shows only the emails of the folder. The emails can be moved to another folder of the same account, and the trash and the junk can be emptied.
- IMAP accounts in the emails app, with its own socket client (implicit TLS or STARTTLS, LOGIN or AUTHENTICATE PLAIN), using the extensions of the server when they are available (SPECIAL-USE, CONDSTORE, UIDPLUS, MOVE, LITERAL+, OBJECTID and the Gmail ids) with fallbacks for the servers without them. The protocol is chosen when the account is created and can not be changed. The folders of the IMAP accounts are the folders of the server (the Sent, Trash and Junk folders are created in the server if they do not exist): the folders tree shows the subscribed ones, the Inbox, Sent, Trash, Junk and Archive folders are always subscribed, and the other folders are subscribed in the new "Server folders" dialog. The synchronization downloads the new emails in batches (the newest first), detects the emails moved and deleted by other clients, and updates the states of the emails with its flags; the changes done in SaltOS (read, wait, reply, forward, move, delete and the copy of the sent emails in the Sent folder, an option of the account for the servers that do not save it themselves) are applied to the server by a queue of pending operations, and the emails with pending operations are labeled as pending. The folder actions (create, rename, move and delete) are done in the server at once. The `config.xml` of the emails app defines the batch size and the retries of the queue.
- Buttons to test the connection with the incoming server (POP3 or IMAP) and the outgoing server (SMTP) in the form of the email accounts, using the data of the form without saving it.
- Dovecot server in the `test` Docker profile (`scripts/test.dovecot.conf`), with POP3 and IMAP on the ports 10110, 10995, 10143 and 10993, used to test STARTTLS and the IMAP extensions; GreenMail has IMAP on the ports 143 and 993 too.

### Changed
- The dbschema detects the changes using two hashes for each table, one for the fields and another for the indexes, stored in the config table. Changing the `null` or the `default` attributes of a field now regenerates the table, and the changes done to the database from outside of the dbschema are no longer repaired.
- The setup skips the db_schema and db_static steps when their files have not changed.
- The copy of rows done when a table is regenerated uses the `default` attribute for the new fields, and converts the nulls to this value in the fields defined with `null="false"`.
- All integer fields used as booleans (`active`, `default`, `allow`, `deny`, `is_closed`, `is_paid` and the flags of apps, emails and email accounts) are defined as `NOT NULL DEFAULT 0`, and the queries no longer need to use `IFNULL` with them.
- The matrices used by the invoices and quotes forms have changed: the lines have 7 columns (the withholding is added before the total), and the taxes and the totals have 4 columns (the type is added to the taxes and the withholding to the totals).
- The codes of the proformas, invoices and quotes are numbered by year: the next code is computed using the numeric value of the codes of the current year, so the serie continues after 999 and is restarted when the year changes.
- Only the `web` directory is published: `web/api` is now a directory with an `index.php` that jumps to the API, and `web/apps` only contains links to the public files of the apps, generated by `make web`.
- The `.htaccess` files with access rules have been removed; `api`, `apps` and `data` keep a minimal one that denies everything, as a safety net for Apache.
- Docker `server` and `demos` images migrated from Apache with mod_php to nginx with PHP-FPM.
- Docker `demos` image: the `instances` volume contains a `private` directory with the instances and a `public` directory, the document root, with one link per visitor to the `web` directory of its instance, synchronized by `scripts/demos_sync.php`; the url of each demo is now `/<hash>/`.
- Minimum PHP version raised from 7.1 to 8.2, the lowest version supported by tcpdf 7 and tc-lib-pdf.
- phpspreadsheet and symfony/yaml dependencies pinned to PHP 8.2 (zipstream-php 3.1.2, symfony/yaml 7.4 LTS).
- Setup checks the requirements of the apps libraries too, and reports as warnings the libraries only used when a PHP extension is missing (yaml, mailmimeparser).
- Third-party libraries updated to their latest releases (joditeditor, bootstrap icons, tcpdf, tc-lib-pdf, fpdi, pdfjs, jsuites, symfony/yaml, mailmimeparser, semver, mroonga, venobox, phpspreadsheet, gridstack, phpmailer, tomselect).
- Faster API requests: the permissions check no longer resolves the name of every app and permission in each request, and the cache of the xml files is read without acquiring a semaphore.
- The caches of the yaml app files, the commands output and the inline images of the emails are written using a temporary file and a rename, so a partial file is never read.
- `get_unique_id_md5()` uses `random_bytes()`, so the names of the temporary files are no longer predictable.
- Developer documentation and `README.md`, `CONTRIBUTING.md` and `AGENTS.md` reviewed and synchronized with the current code.
- Deleting emails moves them to the Trash folder, and only the emails that are already in the trash are deleted permanently. The spam is the Junk folder: the spam detected when receiving goes to the junk, and to mark an email as spam it is moved to the junk.
- The POP3 client of the emails app uses its own socket client based on the PHP streams instead of cURL, with one connection and one login for all the commands of each account, supporting implicit TLS and STARTTLS; the deleted messages are removed by the server when the session is closed.
- The GreenMail server of the `test` Docker profile uses the standard ports: 110 (POP3), 995 (POP3S), 587 (SMTP) and 465 (SMTPS).
- The demo data has two email accounts that use the GreenMail server of the `test` Docker profile: `pop3@example.com` (POP3, with the sample emails) and `imap@example.com` (IMAP), with the GreenMail users `pop3` and `imap` instead of `admin`.
- `saltos.filter.load()` sends the `saltos.filter.load` signal to the current tab, so the apps can react when a filter is loaded.
- The block "POP3 server configuration" of the email accounts is now "Incoming server configuration", with the protocol, and the delete and days options are only used by the POP3 accounts.
- The deleted emails of the POP3 accounts are the only ones stored to not download them again; the folders tree is reloaded when the synchronization changes the folders.

### Removed
- Compatibility wrappers and polyfills for PHP versions older than 8.2.
- `init_random()` and the `random.php` autoload module, PHP seeds the random number generator by default.
- The `php-imap` extension is no longer checked by the setup nor installed in the Docker images; it is not used, the IMAP support of the emails app uses its own socket client.
- The spam state of the emails, with its switch, its options in the mark menu and its label in the lists, replaced by the Junk folder, and the "Only SPAM", "Hide SPAM", "Only inbox" and "Only outbox" filters of the emails, replaced by the folders.

### Fixed
- Editing an invoice or a quote regenerated their code and their date, and the due date of the closed invoices and the valid until date of the quotes were replaced by the current date.
- Saving an invoice or a quote no longer rewrites the fields of the lines, taxes and totals that have not changed, so the history only shows the real changes.
- open_basedir error when generating PDFs with TCPDF on restricted hostings, and TCPDF errors reported as exceptions again instead of stopping the execution.
- JS error when the network comes back in browsers without service worker, like the http instances.
- The pdfjs widget failed in browsers without the most recent JavaScript features (like Safari 17), because the modern build of pdfjs requires them; now it uses the legacy build, that includes the needed polyfills.
- Waits in the web client that never ended when the awaited thing failed (loading a library, a cache or the pdfjs module, closing a modal, reading a file, mapping the error backtrace), now all of them use `saltos.core.wait_for()` with a time limit, and a failed library or cache load can be retried.
- The search filter of the emails list validates the account and the dates sent by the client before using them in the query.
- The widgets that load a library (jstree, codemirror, joditeditor, tags, onetag and jspreadsheet) could apply an old value over a newer one, like the folders tree of the emails app that sometimes was shown empty; now they store only the last value and the last disabled state until the library is ready, and apply them at once.

### Security
- Removed the posix_getuid() polyfill, which made the CLI `user=` owner check always pass when the posix extension was missing; posix is now a required extension.

## [4.1] - 2026-09-23

### Added
- Self-service password renewal for expired passwords, instead of a permanent lockout.
- New dashboard layout manager, with layouts calculated for every breakpoint.
- jspreadsheet widget, with events, fitWidth and rowHeaderWidth support.
- dbstats app to browse per-query usage statistics collected via `debug/slowquerystats`.
- About feature exposing SaltOS information and third-party libraries as JSON, integrated into the web interface, with path filter support.
- Full dark mode support for the joditeditor, codemirror, tomselect and jstree widgets, and a color inversion switch for the pdfjs, iframe and image widgets.
- Brand settings configurable in `config.xml`.
- Pure-PHP polyfill for the mailparse extension, for hosts without it.
- Onclick support in the navbar and image widgets.
- Row-level permission checks batched with `check_app_perm_ids`.
- Docker images with MariaDB compiled with the Mroonga engine, a demos deployment, cron support, tini as init process, persistent volumes and a wait page during the first boot.
- `SECURITY.md` with the security policy.

### Changed
- License changed from GPL-3.0 to MIT, with `SPDX-License-Identifier: MIT` in all source headers.
- App manifests are now defined in `manifest.yaml` instead of `manifest.xml`.
- Charts use ECharts instead of Chart.js.
- Default PDF font is now Atkinson Hyperlegible, and generated PDFs are compressed with ghostscript.
- HTML to text conversion uses soundasleep/html2text instead of league/html-to-markdown.
- MD5 in the web client uses js-md5 instead of blueimp md5.
- Revision numbers are taken from git instead of subversion.
- Strict comparisons (`===`/`!==`) and strict calls are used across the codebase.
- Slow query threshold raised from 5 to 15 seconds to reduce cold-cache false positives.
- `htm` directories and file extensions renamed to `html`.
- Third-party libraries updated to their latest releases (joditeditor 4.15.13, gridstack 14.0.0, tcpdf 7.0.11, tc-lib-pdf 8.76.1, among others).

### Removed
- Handsontable widget, replaced by jspreadsheet in all apps.
- Subversion support.
- Unused browser, geoip and security helpers, together with the browscap and phpgeoip libraries.
- Unused locutus library and bundled core PDF fonts.

### Fixed
- Race condition between `indexing_apps` and the creation flow on `app_*_index` inserts.
- Permission check broken by a strict `in_array` comparison after a type mismatch.
- Cache race condition between concurrent `prefetch_cache` calls.
- Client IP address resolution behind a reverse proxy (`HTTP_X_REAL_IP`).
- POP3 error detection misfiring when a message body contained `-ERR`.
- White flash on initial load in dark mode.
- gettext bug in the files feature.
- Tax lines order in quotes and invoices PDFs.
- Security alerts in guzzlehttp dependencies.

### Testing
- PHPUnit line coverage raised to over 96%.
- Jest screenshot tests for the dbstats app, and fixes for puppeteer ESM-only releases.

## [4.0] - 2026-02-17

First series of SaltOS 4, a full rewrite of SaltOS 3, licensed under GPL-3.0.

### Added
- Backend 100% PHP exposing a REST/JSON API, usable over HTTP and from the CLI.
- Frontend 100% vanilla JavaScript (Bootstrap 5), served as static files, with a service worker for offline support and request queueing.
- Declarative apps defined in YAML/XML (UI, `dbschema.xml` and manifest), with automatic schema synchronization.
- Per-record versioning with a hash chain to detect tampering, and access logging.
- SQLite and MySQL/MariaDB as deployment targets; PostgreSQL and MSSQL drivers for integrations.
- Built-in apps: CRM, sales, purchases, HR, emails, company, certificates, dashboard and users.
- PDF generation from XML templates, full-text search indexing and Excel import/export.
- Multi-language support (English, Spanish and Catalan) with a gettext-style API.
- PHPUnit and Jest test suites, and documentation generated as PDFs.
- Docker profiles: `devel` (SQLite + PHP built-in server), `server` (Apache + MariaDB) and `test` (MSSQL + PostgreSQL + GreenMail).

[4.1]: https://github.com/josepsanzcamp/SaltOS4/releases/tag/v4.1.2674
[4.0]: https://github.com/josepsanzcamp/SaltOS4/releases/tag/v4.0.2302
