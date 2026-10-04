<div align="center">

# SaltOS 4

### A Modular ERP/CRM Suite, Extensible Declaratively in YAML/XML
**Ready-to-use business apps - and the framework to build your own**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)
[![PHP](https://img.shields.io/badge/PHP-8.2%20to%208.5-777BB4.svg)](https://www.php.net/)
[![Release](https://img.shields.io/github/v/release/josepsanzcamp/SaltOS4)](https://github.com/josepsanzcamp/SaltOS4/releases/latest)
[![Demo](https://img.shields.io/badge/Demo-Live-success)](https://demos.saltos.org/)
[![Docs](https://img.shields.io/badge/Docs-9%20PDFs-orange)](https://github.com/josepsanzcamp/SaltOS4/tree/master/docs)

[**🚀 Try Demo**](https://demos.saltos.org/) • [**📖 Documentation**](https://github.com/josepsanzcamp/SaltOS4/tree/master/docs) • [**💬 Discussions**](https://github.com/josepsanzcamp/SaltOS4/discussions) • [**📝 Changelog**](CHANGELOG.md)

</div>

---

## 🎯 The Problem

Building custom business applications is expensive and slow:
- Traditional development: months of work for a basic business application
- Proprietary enterprise platforms: High recurring licensing costs + vendor lock-in
- Customizing platforms: Expensive and complex
- No-code tools: Limited power for complex business logic

## ✨ The Solution

**Start with the built-in apps (CRM, sales, purchases, HR, emails) and extend them, or define new ones, declaratively in YAML/XML.** SaltOS 4 automatically generates:
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
- ✅ Full REST/JSON API (`app/customers/list`, `app/customers/view/123`, ...)
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
| **REST API** | YAML + Schema | `app/customers/list` |
| **Web UI** | YAML fields | Responsive list + modal forms |
| **SQL Migrations** | Schema changes | `ALTER TABLE app_customers ADD COLUMN...` |
| **Search Index** | manifest `has_index: 1` | Full-text search on name, email, notes |
| **Version Tracking** | manifest `has_version: 1` | Blockchain-verified history |
| **File Uploads** | manifest `has_files: 1` | Attachment management |
| **Permissions** | manifest `perms` | User/group access control |

[📖 Learn more in the Developer Guide](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/devel.pdf)

---

## 🔥 Key Features

### For Developers
- **🚀 Declarative Development**: Define apps declaratively, not imperatively
- **🏗️ Automatic Schema Migrations**: Edit XML → Database updates automatically
- **🔐 Blockchain-Verified Versioning**: Every change tracked in a hash chain
- **📱 PWA-Ready**: Works offline with service workers
- **🧪 Fully Tested**: PHPUnit + Jest with comprehensive coverage
- **🌍 Multi-Database**: MySQL/MariaDB and SQLite are the supported deployment targets; PostgreSQL and MSSQL drivers also ship, for targeted integration work rather than general deployment

### For Businesses
- **💰 Zero Licensing Costs**: MIT open source
- **🔒 Self-Hosted**: Your data stays on your servers
- **📊 Audit Compliance**: Every action logged with user/timestamp
- **🌐 Multilingual**: Built-in i18n (gettext-style API with YAML catalogs)
- **📄 PDF Generation**: Custom templates for invoices/reports
- **🔄 Import/Export**: CSV, Excel (XLS/XLSX/ODS), XML, JSON, EDI

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
# Create data/files/config.xml to override the defaults in code/api/xml/config.xml

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
php -S 0.0.0.0:8080 -t web ../scripts/router.php

# 6. Check the web server configuration (from another terminal)
php api/index.php setup/server http://localhost:8080/api

# 7. Open browser
open http://localhost:8080
```

### Web Server

Only the `web` directory must be published by the web server. The `api`,
`apps` and `data` directories must stay outside of the document root: the
web reaches the API through `web/api/index.php` and only the public files
of the apps are linked inside `web/apps`.

`scripts/` provides one recipe for each web server, ready to adapt:

- `scripts/server.nginx.conf`: nginx with PHP-FPM
- `scripts/server.apache.conf`: Apache with access to the global configuration
- `scripts/server.htaccess`: Apache on shared hostings, where all the code is
  published and only a `.htaccess` file can be used

After any change in the web server, validate it with `setup/server` (step 6):
a `count` of 0 means that nothing private is exposed.

### Docker Profiles Overview

SaltOS 4 provides two main runtime Docker profiles:

- `devel`: lightweight development environment (SQLite + PHP built-in server)
- `server`: production-ready stack (nginx + PHP-FPM + MariaDB)

The server profile installs and initializes SaltOS automatically during the image build.

### Development with Docker (SQLite + PHP Built-in Server)

```bash
make develbuild
make develstart
```

- http://localhost:8080
- Username: `admin`
- Password: `admin`

### Production Server with Docker (nginx + MariaDB)

```bash
make serverbuild
make serverstart
```

- http://localhost:8080
- Username: `admin`
- Password: `admin`

The containers only publish plain HTTP on their port 80, mapped to the port
8080 of the host. HTTPS must be provided by an external layer, for example a
reverse proxy like Traefik.

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
Production uses nginx + PHP-FPM + MariaDB.

MySQL/MariaDB and SQLite are the supported deployment targets. PostgreSQL
and MSSQL drivers also ship, but for targeted integration work rather than
general deployment.

### Core Technologies
- **Backend**: PHP 8.2-8.5 (strict types, tested)
- **Frontend**: Vanilla JavaScript, Bootstrap 5, TomSelect, Jodit Editor, ECharts
- **Storage**: Multi-database abstraction layer (PDO)
- **Testing**: PHPUnit (backend) + Jest (frontend)
- **i18n**: gettext-style API with YAML catalogs
- **PDFs**: TCPDF with XML templates
- **Excel**: PHPSpreadsheet (import/export)

---

## 📚 Built-In Apps

SaltOS 4 ships with production-ready apps:

| App | Description |
|-----|-------------|
| **CRM** | Customers, Leads, Quotes, Meetings |
| **Sales** | Products, Invoices, Work Orders, Taxes, Payment Methods |
| **Purchases** | Suppliers, Purchase Orders |
| **HR** | Employees, Departments |
| **Emails** | POP3/SMTP integration, inbox management |
| **Company** | Company profile, settings |
| **Certificates** | Digital certificate management |
| **Dashboard** | Configurable widgets home screen |
| **Users** | User & group management, permissions |

---

## 🔐 Blockchain-Verified Versioning

Every change is stored as a new version in a **hash chain**:
```php
// Version 1 (created)
{
  "reg_id": 123,
  "ver_id": 1,
  "user_id": 1,
  "datetime": "2025-01-01 10:00:00",
  "data": {"app_customers": {"123": {"name": "Acme Corp", ...}}},
  "hash": "9f2c..."  // md5 of this version, computed with an empty previous hash
}

// Version 2 (updated - only deltas)
{
  "reg_id": 123,
  "ver_id": 2,
  "user_id": 2,
  "datetime": "2025-01-05 14:30:00",
  "data": {"app_customers": {"123": {"email": "new@acme.com"}}},
  "hash": "4b7e..."  // md5 of this version, including the hash of version 1
}
```

`data` is stored serialized and base64-encoded; it is shown decoded here.

**Tamper-evident**: changing any stored version breaks the hashes of every
later version, and SaltOS reports a blockchain integrity break when that
history is read.

---

## 📖 Documentation

9 PDFs; the user manual is available in English, Spanish and Catalan:

- 📘 **User Manual** - End-user guide [English](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/user_en_us.pdf) [Spanish](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/user_es_es.pdf) [Catalan](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/user_ca_es.pdf)
- 🔧 [**Developer Guide**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/devel.pdf) - Architecture & customization
- 🌐 [**API Reference**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/api.pdf) - REST endpoints
- 💻 [**Web Client**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/web.pdf) - Frontend architecture
- 📦 [**Apps Guide**](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/apps.pdf) - Building custom apps
- 🧪 **Testing** - [PHPUnit](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/utest.pdf) & [Jest](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/ujest.pdf) guides

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
- 📧 **Email**: josep.sanz@saltos.org

---

## 🙏 Acknowledgments

SaltOS 4 is built on top of excellent open source projects:

**Backend:**
- [TCPDF](https://tcpdf.org/) and [tc-lib-pdf](https://github.com/tecnickcom/tc-lib-pdf) - PDF generation
- [PHPSpreadsheet](https://phpspreadsheet.readthedocs.io/) - Excel import/export
- [PHP EDIFACT](https://github.com/php-edifact/edifact) - EDI message parsing
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Email sending
- [Symfony YAML](https://symfony.com/components/Yaml) - YAML parser
- [FPDI](https://www.setasign.com/fpdi) - PDF manipulation
- [MailMimeParser](https://github.com/zbateson/mail-mime-parser) - Email parsing
- [zxcvbn-php](https://github.com/bjeavons/zxcvbn-php) - Password strength
- [html2text](https://github.com/soundasleep/html2text) - HTML to text conversion

**Frontend:**
- [Bootstrap](https://getbootstrap.com/) - UI framework
- [Jodit Editor](https://xdsoft.net/jodit/) - Rich text editor
- [Apache ECharts](https://echarts.apache.org/) - Data visualization
- [PDF.js](https://mozilla.github.io/pdf.js/) - PDF viewer (Mozilla)
- [CodeMirror](https://codemirror.net/) - Code editor
- [TomSelect](https://tom-select.js.org/) - Enhanced select boxes
- [Interact.js](https://interactjs.io/) - Drag and drop
- [Jspreadsheet CE](https://github.com/jspreadsheet/ce) - Spreadsheet widget
- [jsTree](https://www.jstree.com/) - Tree widget
- [Gridstack](https://gridstackjs.com/) - Dashboard layout

**Testing:**
- [PHPUnit](https://phpunit.de/) - PHP testing framework
- [Jest](https://jestjs.io/) - JavaScript testing

**Full list:** [View all 30+ dependencies in checklibs.txt](https://github.com/josepsanzcamp/SaltOS4/blob/master/scripts/checklibs.txt)

---

<div align="center">

**Built with ❤️ by [Josep Sanz Campderrós](https://github.com/josepsanzcamp)**

[⭐ Star this repo](https://github.com/josepsanzcamp/SaltOS4) if you find it useful!

</div>
