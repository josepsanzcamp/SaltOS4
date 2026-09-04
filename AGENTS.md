# AGENTS.md

## Overview

SaltOS 4 is a declarative framework for building enterprise business applications (CRM, ERP, workflows) using YAML/XML definitions.

The system auto-generates:

- REST API (HTTP + CLI compatible)
- Responsive Web SPA (vanilla JS)
- Automatic schema synchronization
- Version tracking with blockchain-style integrity
- Audit logging
- Multi-database support
- Offline-first behavior via Service Worker

SaltOS is structured as a layered architecture with strict separation between frontend, backend, declarative definitions, and persistence.

This document is intended for AI agents assisting humans in:

- Installing SaltOS
- Configuring environments
- Creating new apps
- Extending existing apps
- Debugging runtime issues
- Understanding architectural responsibilities
- Running tests and builds correctly

**Scope of this document.** This file covers agent-specific behavior and
known pitfalls only. It intentionally does not restate everything —
[CONTRIBUTING.md](CONTRIBUTING.md) is the canonical, complete reference for
setup steps, the full command reference (including less common variants),
coding standards, and project structure; [README.md](README.md) is the
product overview. When this file and CONTRIBUTING.md disagree, trust
CONTRIBUTING.md and flag the discrepancy — don't silently pick one.

---

## Project Structure

code/
    api/        PHP backend (REST + CLI engine)
    apps/       Declarative app modules (YAML/XML + JS + PHP)
    data/       Runtime storage (files, logs, cache, uploads)
    web/        Frontend SPA (vanilla JS, Bootstrap)

docs/           .t2t documentation sources
scripts/        Automation tools (doc generation, helpers)
utest/          PHPUnit backend tests
ujest/          Jest frontend tests
makefile        Main automation entrypoint

---

## Architecture Model

SaltOS operates in 4 layers:

1. Client SPA (web/)
2. PHP Execution Engine (api/)
3. Declarative App Definitions (apps/*)
4. Data & Persistence Layer (data/, DB engines)

### Key Principle

SaltOS is declarative-first.

Apps are defined by:

- manifest.yaml (registration: id, code, name, table, features)
- dbschema.xml (table/field definitions, auto-migrated)
- dbstatic.xml (optional; bulk-loads master-data rows into a table and
  re-syncs them whenever the file changes — used by the core, not by the
  example apps)
- *.yaml or *.xml (UI definition: list/form/select; YAML is compiled to XML
  and cached — see `detect_app_file()` in
  code/api/php/autoload/apps.php)

AI agents must prefer editing declarative definitions instead of writing imperative controller logic.

---

## Backend (api/)

Entry point:

php api/index.php

Supports both:

- HTTP REST
- CLI execution

All requests follow:

1. Token validation
2. Permission validation
3. Action dispatch
4. JSON serialization

Do NOT:

- Bypass autoload modules
- Hardcode database logic outside drivers
- Skip permission checks

---

## Frontend (web/)

Vanilla JavaScript SPA.

Core files:

- core.js → low-level utilities (AJAX, DOM, error handling)
- app.js → view logic, layout, routing
- proxy.js → service worker (offline, caching, queueing)

Important:

- No server-side rendering
- UI generated dynamically from backend definitions
- Production builds require `make web`

Agents must NOT:

- Modify index.html manually for logic
- Break service worker registration
- Introduce server dependencies in frontend

---

## App Development Workflow

To create or modify an app:

1. Edit YAML or XML definition
2. Update dbschema.xml if schema changes
3. Update manifest.yaml if features change
4. Run setup/sync
5. Test via UI and API

Schema migrations are automatic.

Never write manual SQL migrations.

---

## Common Pitfalls

Concrete traps in this codebase, verified against the current code (not
generic advice):

- **Multi-database SQL.** MySQL/MariaDB and SQLite are the supported
  deployment targets; PostgreSQL and MSSQL drivers also ship
  (`code/api/php/database/pdo_{mysql,sqlite,pgsql,mssql}.php`) but for
  targeted integration work, not general deployment. Regardless, never
  write engine-specific SQL inline. `parse_query()`
  (`code/api/php/autoload/sql.php:46`) strips or keeps fragments wrapped in
  `/*MYSQL*/ ... /*SQLITE*/ ... /*PGSQL*/ ... /*MSSQL*/ ...` comment blocks
  depending on the active driver — put engine-specific SQL there, not in
  plain string concatenation.
- **`code/web/index.html` is a static shell**, not a template — all view
  logic lives in `code/web/js/`. Don't add logic to it.
- **Autoload modules** (`code/api/php/autoload/*.php`, ~30 files: `sql.php`,
  `tokens.php`, `user.php`, `apps.php`, `perms.php`, ...) are all loaded
  automatically by `zindex.php` on every request. Don't require them
  manually or duplicate their logic elsewhere.
- **Token binding** is checked against `remote_addr` + `user_agent` on every
  request (`code/api/php/autoload/user.php:38-52`) — don't relax or bypass
  this check.
- **Version/audit chain** lives in `code/api/php/lib/version.php`
  (`make_version()`) — it stores deltas plus a hash of the previous version
  per register. Never write to version rows directly; always go through
  this function.
- **Vendored libraries** under `code/api/lib/*/` each carry their own
  `composer.lock`. `check_composer()` (`code/api/php/autoload/system.php:154`)
  validates the running PHP against each lib's `require.php` constraint at
  setup time. If you update a vendored lib, its constraint may raise the
  effective PHP floor above the hard-coded minimum of 7.1
  (`code/api/index.php:25`) — check `make setuponly` / `check_composer()`
  output after doing so.

---

## Setup & Installation

SQLite setup:

make setupsqlite
make setupall

MySQL setup:

make setupmysql
make setupall

Manual setup:

php api/index.php setup

Demo data example:

user=admin php api/index.php setup/crm

---

## Development Commands

Build frontend (production):

make web

Development mode:

make devel

Lint + static analysis:

make test

PHPUnit:

make utest

Jest:

make ujest

Docker (development):

make develbuild
make develstart

Docker (production profile):

make serverbuild
make serverstart

Container management (status/logs/shell for devel and server profiles) and
the `test` Docker profile (MSSQL, PostgreSQL, GreenMail for integration
tests) are documented in CONTRIBUTING.md — not repeated here.

---

## Testing Model

Backend:

- PHPUnit, located in `utest/`
- `make utest` runs modified tests only; `make utest file=all` runs
  everything; `make utest file=core` runs one file by name

Frontend:

- Jest, located in `ujest/`
- `make ujest` runs modified tests only; `make ujest file=all` runs
  everything; `make ujest file=<filter>` runs a subset

AI agents should:

- Prefer the scoped `file=` form while iterating, and run `file=all` before
  considering a change done
- Always run the relevant test suite after modifying core behavior
- Prefer writing new tests when introducing new features
- Avoid changing snapshots unless behavior intentionally changed

---

## Authentication Model

Token-based authentication:

HTTP:
- auth/login
- Bearer token in header

CLI:
- token=... php index.php ...
- user=admin (owner-only shortcut)

Tokens are bound to:

- IP
- User agent

Agents must not suggest disabling token validation.

---

## Offline & Service Worker

Service worker (proxy.js) handles:

- Caching
- Offline queue
- Sync operations

Agents must:

- Preserve fetch interception logic
- Avoid breaking POST queueing
- Not disable caching unless debugging

---

## Data Directory Requirements

code/data/ must be writable.

Contains:

- cache
- logs
- files
- upload
- temp
- trash
- cron

Agents must not:

- Store runtime files outside this directory
- Assume DB-only storage

---

## Documentation System

Docs are written in .t2t format.

Generate all:

make docs

Generate specific:

make docs file=api
make docs file=web

User manuals are auto-generated from app helps.

Agents must not manually edit generated PDFs or HTML.

---

## Security Model

SaltOS enforces:

- Token validation per request
- Permission validation
- Version tracking (immutable chain)
- Access logging

Agents must:

- Preserve integrity mechanisms
- Avoid bypassing version logging
- Never recommend disabling permission checks

---

## Design Philosophy

- Declarative > imperative
- Convention > configuration
- Unified HTTP + CLI engine
- Automatic schema sync
- Minimal boilerplate

When assisting users, agents should:

- Guide through declarative definitions first
- Prefer configuration changes over code hacks
- Respect separation of frontend and backend
- Avoid introducing external frameworks

---

## Non-Goals

SaltOS is NOT:

- A traditional MVC framework
- A server-side rendering system
- A no-code visual builder
- A monolithic hardcoded ERP

It is a declarative application engine.
