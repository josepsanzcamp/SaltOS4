# Changelog

All notable changes to SaltOS 4 are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Each release is identified by its version and by its revision (`rN`, the
git commit count), as shown in the About dialog (`SaltOS v4.1 rN`).

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
- PHPUnit coverage raised to about 89% of classes and 96% of methods.
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
