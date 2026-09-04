<div align="center">

# SaltOS 4

### Build Enterprise Business Apps Declaratively in YAML/XML
**Structured Business Systems — 10x Faster Than Traditional Frameworks**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)
[![PHP](https://img.shields.io/badge/PHP-7.1%20to%208.5-777BB4.svg)](https://www.php.net/)
[![Demo](https://img.shields.io/badge/Demo-Live-success)](https://demos.saltos.org/)
[![Docs](https://img.shields.io/badge/Docs-9%20PDFs-orange)](https://github.com/josepsanzcamp/SaltOS4/tree/master/docs)

[**🚀 Try Demo**](https://demos.saltos.org/) • [**📖 Documentation**](https://github.com/josepsanzcamp/SaltOS4/tree/master/docs) • [**💬 Discussions**](https://github.com/josepsanzcamp/SaltOS4/discussions)

</div>

---

## 🎯 The Problem

Building custom business applications is expensive and slow:
- Traditional development: **6-12 months** for a basic business application
- Proprietary enterprise platforms: High recurring licensing costs + vendor lock-in
- Customizing platforms: Expensive and complex
- No-code tools: Limited power for complex business logic

## ✨ The Solution

**Define your business app declaratively in YAML/XML.** SaltOS 4 automatically generates:
- ✅ Full REST API with authentication
- ✅ Responsive web UI (desktop + mobile)
- ✅ Complete audit trail with blockchain integrity
- ✅ Multi-language support (EN/ES/CA)
- ✅ Offline-first Progressive Web App
- ✅ PDF generation from templates
- ✅ Full-text search indexing

### Example: CRM App Definition

```yaml
# apps/crm/xml/customers.yaml
app: customers
template: apps/common/xml/default.xml

list:
    - [name, text, Name]
    - [code, text, Tax ID]
    - [city, text, City]
    - [active, boolean, Active]

form:
    - [name, text, Name]
    - [code, text, Tax ID]
    - [email, text, Email]
    - [phone, text, Phone]
    - [notes, textarea, Notes]
    - [type_id, select, Type]

select:
    - [type_id, app_customers_types]

attr:
    name:
        required: true
    notes:
        height: 5em
```

Plus **database schema** (dbschema.xml) and **app manifest** (manifest.yaml).
👉 [See complete CRM example](https://github.com/josepsanzcamp/SaltOS4/tree/master/code/apps/crm/xml)

**You automatically get:**
- ✅ Full REST API (`GET/POST/PUT/DELETE /api/app/customers`)
- ✅ Responsive web UI with search/filter/pagination
- ✅ Create/Edit/Delete forms with validation
- ✅ Blockchain-verified version history
- ✅ File attachments & notes system
- ✅ User/group permission system
- ✅ Full-text search indexing

---

## 📐 How Apps Are Structured

Every SaltOS app is defined by **3 declarative files**:

<table>
<tr>
<td width="33%">

**1. UI Definition**
`customers.yaml`

```yaml
app: customers

list:
  - [name, text, Name]
  - [email, text, Email]

form:
  - [name, text, Name]
  - [email, text, Email]
```

Defines list views, forms, and field types.

</td>
<td width="33%">

**2. Database Schema**
`dbschema.xml`

```xml
<table name="app_customers">
  <fields>
    <field name="id"
           type="INTEGER"
           pkey="true"/>
    <field name="name"
           type="VARCHAR(255)"/>
    <field name="email"
           type="VARCHAR(255)"/>
  </fields>
</table>
```

Defines tables, fields, and relationships.

</td>
<td width="33%">

**3. App Manifest**
`manifest.yaml`

```yaml
apps:
    - id: 50
      code: customers
      name: Customers
      table: app_customers
      has_version: 1
      has_files: 1
      has_notes: 1
```

Registers the app with metadata and features.

</td>
</tr>
</table>

### What Gets Auto-Generated

From these definitions, SaltOS automatically creates:

| Component | Generated From | Example |
|-----------|----------------|---------|
| **REST API** | YAML + Schema | `GET /api/app/customers/list` |
| **Web UI** | YAML fields | Responsive list + modal forms |
| **SQL Migrations** | Schema changes | `ALTER TABLE app_customers ADD COLUMN...` |
| **Search Index** | Text fields | Full-text search on name, email, notes |
| **Version Tracking** | manifest `has_version="1"` | Blockchain-verified history |
| **File Uploads** | manifest `has_files="1"` | Attachment management |
| **Permissions** | manifest `perms` | User/group access control |

[📖 Learn more in the Developer Guide](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/devel.pdf)

---

## 🔥 Key Features

### For Developers
- **🚀 10x Faster Development**: Define apps declaratively, not imperatively
- **🏗️ Automatic Schema Migrations**: Edit XML → Database updates automatically
- **🔐 Blockchain-Verified Versioning**: Every change tracked with cryptographic integrity
- **📱 PWA-Ready**: Works offline with service workers
- **🧪 Fully Tested**: PHPUnit + Jest with comprehensive coverage
- **🌍 Multi-Database**: MySQL/MariaDB and SQLite are the supported deployment targets; PostgreSQL and MSSQL drivers also ship, for targeted integration work rather than general deployment

### For Businesses
- **💰 Zero Licensing Costs**: MIT open source
- **🔒 Self-Hosted**: Your data stays on your servers
- **📊 Audit Compliance**: Every action logged with user/timestamp
- **🌐 Multilingual**: Built-in i18n (YAML-based translations)
- **📄 PDF Generation**: Custom templates for invoices/reports
- **🔄 Import/Export**: CSV, Excel, SQL

---

## 📸 Screenshots

<table>
  <tr>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-users-login-1-snap.png" alt="Login"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-dashboard-dashboard-en-us-light-1-snap.png" alt="Dashboard"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-sales-invoices-view-100-en-us-light-1-snap.png" alt="Invoices"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-sales-invoices-create-en-us-light-1-snap.png" alt="Invoices"/></td>
  </tr>
  <tr>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-emails-emails-view-100-en-us-light-1-snap.png" alt="Emails"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-emails-emails-create-en-us-light-1-snap.png" alt="Emails"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-emails-js-app-emails-action-help-1-snap.png" alt="Help"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-emails-js-app-emails-action-about-1-snap.png" alt="About"/></td>
  </tr>
  <tr>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-emails-emails-view-100-en-us-dark-1-snap.png" alt="Emails"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-emails-emails-view-viewpdf-100-en-us-dark-1-snap.png" alt="PDF"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-crm-customers-edit-100-en-us-dark-1-snap.png" alt="Customers"/></td>
    <td><img src="https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/ujest/snaps/test-screenshots-js-screenshots-crm-meetings-view-viewpdf-100-en-us-dark-1-snap.png" alt="PDF"/></td>
  </tr>
</table>

---

## 🚀 Quick Start

### Try the Demo (No Installation)

👉 **[https://demos.saltos.org/](https://demos.saltos.org/)**
- Username: `admin`
- Password: `admin`

Each visitor gets an isolated SQLite-based instance, similar to the `devel` Docker profile.

### Local Installation
```bash
# 1. Clone repository
git clone https://github.com/josepsanzcamp/SaltOS4.git
cd SaltOS4

# 2. Create instance (symlinks code/ to instance directory)
mkdir instance
cd instance
bash ../scripts/make_instance.sh

# 3. Configure database (SQLite or MySQL)
# Edit code/api/data/config.xml

# 4. Run setup (creates tables + sample data)
php api/index.php setup
user=admin php api/index.php setup/certs
user=admin php api/index.php setup/company
user=admin php api/index.php setup/emails
user=admin php api/index.php setup/crm
user=admin php api/index.php setup/hr
user=admin php api/index.php setup/purchases
user=admin php api/index.php setup/sales

# 5. Start web server
php -S 0.0.0.0:8080 -t web

# 6. Open browser
open http://localhost:8080
```

### Docker Profiles Overview

SaltOS 4 provides two main runtime Docker profiles:

- `devel`: lightweight development environment (SQLite + PHP built-in server)
- `server`: production-ready stack (Apache + PHP + MariaDB)

The server profile installs and initializes SaltOS automatically during the image build.

### Development with Docker (SQLite + PHP Built-in Server)

```bash
make develbuild
make develstart
```

- http://localhost:8080
- Username: `admin`
- Password: `admin`

### Production Server with Docker (Apache + MariaDB)

```bash
make serverbuild
make serverstart
```

- HTTP: http://localhost:8080
- HTTPS: https://localhost:8443 (self-signed certificate, generated automatically)
- Username: `admin`
- Password: `admin`

Container management (status/logs/shell), the `test` Docker profile (MSSQL +
PostgreSQL + GreenMail for integration tests), and the full command
reference are in [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────┐
│                  Web Browser (PWA)                  │
│  Vanilla JavaScript · Bootstrap 5 · Service Worker  │
└─────────────────┬───────────────────────────────────┘
                  │ REST/JSON
┌─────────────────▼───────────────────────────────────┐
│                   PHP API Layer                     │
│  Router · Auth · Permissions · Versioning           │
└─────────────────┬───────────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────────┐
│            YAML/XML App Definitions                 │
│  Declarative schemas → Auto-generated CRUD          │
└─────────────────┬───────────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────────┐
│               MySQL/MariaDB · SQLite                │
└─────────────────────────────────────────────────────┘
```

**Docker profiles:**
Development uses SQLite + PHP built-in server.
Production uses Apache + MariaDB.

MySQL/MariaDB and SQLite are the supported deployment targets. PostgreSQL
and MSSQL drivers also ship, but for targeted integration work rather than
general deployment.

### Core Technologies
- **Backend**: PHP 7.1-8.5 (strict types, tested)
- **Frontend**: Vanilla JavaScript, Bootstrap 5, TomSelect, Jodit Editor, Chart.js
- **Storage**: Multi-database abstraction layer (PDO)
- **Testing**: PHPUnit (backend) + Jest (frontend)
- **i18n**: YAML-based translations
- **PDFs**: TCPDF with XML templates
- **Excel**: PHPSpreadsheet (import/export)

---

## 📚 Built-In Apps

SaltOS 4 ships with production-ready apps:

| App | Description |
|-----|-------------|
| **CRM** | Customers, Leads, Quotes, Meetings |
| **Sales** | Products, Invoices, Orders, Taxes |
| **Purchases** | Suppliers, Purchase Orders |
| **HR** | Employees, Departments |
| **Emails** | POP3/SMTP integration, inbox management |
| **Company** | Company profile, settings |
| **Users** | User & group management, permissions |

---

## 🔐 Blockchain-Verified Versioning

Every change is stored with **cryptographic chain-of-custody**:
```php
// Version 1 (created)
{
  "ver_id": 1,
  "user_id": 1,
  "datetime": "2025-01-01 10:00:00",
  "data": {"app_customers": {"123": {"name": "Acme Corp", ...}}},
  "hash": ""  // First version
}

// Version 2 (updated - only deltas)
{
  "ver_id": 2,
  "user_id": 2,
  "datetime": "2025-01-05 14:30:00",
  "data": {"app_customers": {"123": {"email": "new@acme.com"}}},
  "hash": "abc123..."  // Hash of version 1
}
```

**Tamper-proof**: Any modification to historical data breaks the chain.

---

## 📖 Documentation

Comprehensive docs in 3 languages (English, Spanish, Catalan):

- 📘 **User Manual** — End-user guide [English](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/user_en_us.pdf) [Spanish](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/user_es_es.pdf) [Catalan](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/user_ca_es.pdf)
- 🔧 [**Developer Guide**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/devel.pdf) — Architecture & customization
- 🌐 [**API Reference**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/api.pdf) — REST endpoints
- 💻 [**Web Client**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/web.pdf) — Frontend architecture
- 📦 [**Apps Guide**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/apps.pdf) — Building custom apps
- 🧪 **Testing** — [PHPUnit](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/utest.pdf) & [Jest](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/ujest.pdf) guides

---

## 🤝 Contributing

We welcome contributions! See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines. Using an AI coding agent on this repo? See [AGENTS.md](AGENTS.md).

### Development Setup
```bash
# Run code quality and tests
make test        # Linting (phpcs + phpstan + jscs)
make utest       # PHPUnit (backend tests)
make ujest       # Jest (frontend tests)
```

---

## 📄 License

SaltOS is licensed under the [MIT License](LICENSE.md)

```
Copyright (c) 2007-2026 Josep Sanz Campderrós
```

SaltOS4 versions prior to 4.1 were licensed under GPL-3.0.
Starting from version 4.1, the project is licensed under MIT.

---

## 💬 Community & Support

- 🌐 **Website**: [saltos.org](https://www.saltos.org)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/josepsanzcamp/SaltOS4/discussions)
- 🐛 **Issues**: [GitHub Issues](https://github.com/josepsanzcamp/SaltOS4/issues)
- 📧 **Email**: info@saltos.org

---

## 🙏 Acknowledgments

SaltOS 4 is built on top of excellent open source projects:

**Backend:**
- [TCPDF](https://tcpdf.org/) — PDF generation
- [PHPSpreadsheet](https://phpspreadsheet.readthedocs.io/) — Excel import/export
- [PHP EDIFACT](https://github.com/php-edifact/edifact) — EDI message parsing
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) — Email sending
- [Symfony YAML](https://symfony.com/components/Yaml) — YAML parser
- [FPDI](https://www.setasign.com/fpdi) — PDF manipulation

**Frontend:**
- [Bootstrap](https://getbootstrap.com/) — UI framework
- [Jodit Editor](https://xdsoft.net/jodit/) — Rich text editor
- [Chart.js](https://www.chartjs.org/) — Data visualization
- [PDF.js](https://mozilla.github.io/pdf.js/) — PDF viewer (Mozilla)
- [CodeMirror](https://codemirror.net/) — Code editor
- [TomSelect](https://tom-select.js.org/) — Enhanced select boxes
- [Interact.js](https://interactjs.io/) — Drag and drop

**Testing:**
- [PHPUnit](https://phpunit.de/) — PHP testing framework
- [Jest](https://jestjs.io/) — JavaScript testing

**Full list:** [View all 30+ dependencies in checklibs.txt](https://github.com/josepsanzcamp/SaltOS4/blob/master/scripts/checklibs.txt)

---

<div align="center">

**Built with ❤️ by [Josep Sanz Campderrós](https://github.com/josepsanzcamp)**

[⭐ Star this repo](https://github.com/josepsanzcamp/SaltOS4) if you find it useful!

</div>
