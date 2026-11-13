# Changelog

All notable changes to SaltOS 4 are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Each release is identified by its version and by its revision (`rN`, the
git commit count), as shown in the About dialog (`SaltOS v4.1 rN`).

## [4.2] - 2026-11-13

### Upgrade notes
- Only the `web` directory is published, without `.htaccess` rules. After updating: run `make web` (or `bash ../scripts/migrate_instance.sh` in each instance), publish `web` with the recipe of your server (`scripts/server.*`), disable the directory listing, restart PHP-FPM or Apache, and check that `php api/index.php setup/server <api url>` returns a `count` of 0.
- The first setup after updating regenerates every table once (new hash detection). Run `make setuponly` or `php api/index.php setup`; it can be slow with big tables.
- The new tax and withholding modes of the company start as "not used". Set the tax mode to "by line" to keep the taxes as before.
- Emails folders: inside `code` and each instance, run `php ../scripts/migrate_emails_folders.php export spam.json`, then the setup, then `php ../scripts/migrate_emails_folders.php import spam.json` (it can be repeated). Sent emails go to Sent, spam to Junk and the rest to Inbox.

### Added
- `setup/server` action (formerly `setup/apache`) to validate any web server.
- Web server recipes for nginx, Apache and shared hostings (`scripts/server.*`).
- `scripts/makeapps.php`, `scripts/migrate_instance.sh` and `scripts/router.php` (PHP built-in server).
- Withholdings in invoices and quotes (like the Spanish IRPF): taxes `type` field, tax and withholding per line, both in the breakdown and the PDF. Products have a withholding field.
- Company `tax_mode` and `withholding_mode`: by line, one per document or not used. Unused columns are hidden in the form and the PDF.
- Yaml quick apps: `options` widget and optional where in `select`.
- Translated column titles in the jspreadsheet widgets.
- Email folders: Inbox, Sent, Trash and Junk per account, plus user folders (create, rename, move, delete) in a tree panel. Emails can be moved within the account, and Trash and Junk can be emptied.
- IMAP accounts with its own socket client (TLS, STARTTLS, LOGIN, PLAIN) and the server extensions when available (SPECIAL-USE, CONDSTORE, UIDPLUS, MOVE, LITERAL+, OBJECTID, Gmail ids). Server folders with subscriptions, sync in batches, and local changes sent through a queue of pending operations.
- Buttons to test the incoming and outgoing servers from the account form, without saving.
- Dovecot in the `test` Docker profile (ports 10110, 10995, 10143, 10993) for STARTTLS and the IMAP extensions. GreenMail adds IMAP on 143 and 993.

### Changed
- dbschema detects changes with two hashes per table (fields and indexes). External changes are no longer repaired.
- Setup skips db_schema and db_static when their files have not changed.
- Regenerating a table fills new fields and nulls in `null="false"` fields with their `default`.
- Boolean integer fields are `NOT NULL DEFAULT 0`, without `IFNULL` in the queries.
- Invoices and quotes matrices: 7 columns in lines, 4 in taxes and totals (withholding and type added).
- Proforma, invoice and quote codes are numbered per year, beyond 999.
- Only `web` is published: `web/api` jumps to the API and `web/apps` links the public app files (`make web`).
- `.htaccess` access rules removed. `api`, `apps` and `data` keep a deny-all one as a safety net.
- Docker `server` and `demos` images: nginx and PHP-FPM instead of Apache and mod_php.
- Docker `demos`: `private` instances and a `public` document root with one link per visitor (`scripts/demos_sync.php`). Demo urls are `/<hash>/`.
- Minimum PHP raised from 7.1 to 8.2 (required by tcpdf 7 and tc-lib-pdf).
- phpspreadsheet and symfony/yaml pinned to PHP 8.2.
- Setup checks the app libraries too; optional ones (yaml, mailmimeparser) as warnings.
- Updated libraries: joditeditor 4.20.1, bootstrap icons 1.13.2, tcpdf 7.0.22, tc-lib-pdf 8.84.0, fpdi 2.6.10, pdfjs 6.5.438, jsuites 6.6.1, symfony/yaml 7.4.23, mailmimeparser 4.0.9, semver 3.6.0, mroonga 16.15, venobox 2.2.0, phpspreadsheet 5.11.0, gridstack 14.1.0, phpmailer 7.2.0, tomselect 2.7.0, bootstrap 5.3.9.
- Faster API requests: lighter permissions check, xml cache read without semaphore.
- Yaml cache, command output and email inline images are written with a temporary file and a rename.
- `get_unique_id_md5()` uses `random_bytes()`: temporary file names are unpredictable.
- `user=` from the command line no longer checks the owner of the files; the CLI is protected by the file system permissions.
- Developer docs, `README.md`, `CONTRIBUTING.md` and `AGENTS.md` synchronized with the code.
- Deleting an email moves it to Trash; only Trash emails are deleted permanently. Spam is moved to Junk.
- POP3 uses its own socket client (PHP streams, no cURL), with one session per account, TLS and STARTTLS.
- GreenMail in the `test` profile uses the standard ports (110, 995, 587, 465).
- Demo data: two GreenMail accounts, `pop3@example.com` (with the sample emails) and `imap@example.com`.
- `saltos.filter.load()` sends the `saltos.filter.load` signal to the current tab.
- "POP3 server configuration" is now "Incoming server configuration", with the protocol. Delete and days are POP3 only.
- POP3 accounts only store the deleted emails, to not download them again. The folders tree reloads when the sync changes the folders.

### Removed
- Wrappers and polyfills for PHP older than 8.2.
- `init_random()` and `random.php`; PHP seeds the generator itself.
- `php-imap` extension (setup check and Docker images); IMAP uses its own client.
- Spam state of the emails (switch, mark menu, list label) and the "Only SPAM", "Hide SPAM", "Only inbox" and "Only outbox" filters, replaced by folders.

### Fixed
- Editing an invoice or a quote regenerated its code and dates.
- Saving an invoice or a quote rewrote unchanged lines, taxes and totals, adding noise to the history.
- open_basedir error in TCPDF on restricted hostings; TCPDF errors are exceptions again.
- JS error when the network came back without service worker (http instances).
- pdfjs widget failed in older browsers (like Safari 17); it now uses the legacy build.
- Web client waits that never ended on failure. They now use `saltos.core.wait_for()` with a time limit, and failed library or cache loads can be retried.
- The emails list filter validates the account and the dates sent by the client.
- Lazy-loaded widgets (jstree, codemirror, joditeditor, tags, onetag, jspreadsheet) could apply an old value over a newer one, like an empty folders tree.
- Null values in insert and update queries are stored as null if the field allows it, otherwise as the dbschema default. Nulls in where queries trigger an error.

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

[4.2]: https://github.com/josepsanzcamp/SaltOS4/releases/tag/v4.2.2790
[4.1]: https://github.com/josepsanzcamp/SaltOS4/releases/tag/v4.1.2674
[4.0]: https://github.com/josepsanzcamp/SaltOS4/releases/tag/v4.0.2302
