# Contributing to SaltOS 4

Thank you for your interest in contributing to SaltOS 4! This document provides guidelines and instructions for contributing to the project.

This is the canonical reference for setup, commands, coding standards and
project structure. If you are an AI coding agent, also read
[AGENTS.md](AGENTS.md) for agent-specific behavior rules and known
pitfalls — it defers to this file for anything not agent-specific.

## 🌟 Ways to Contribute

- **Report bugs** via [GitHub Issues](https://github.com/josepsanzcamp/SaltOS4/issues)
- **Suggest features** via [GitHub Discussions](https://github.com/josepsanzcamp/SaltOS4/discussions)
- **Submit pull requests** with bug fixes or new features
- **Improve documentation** (code comments, PDFs, README)
- **Translate** to new languages (currently: EN/ES/CA)
- **Write tutorials** or blog posts about SaltOS 4

---

## 🔧 Development Setup

### Prerequisites

- PHP 8.2-8.5 (8.2 is the hard floor enforced in `api/index.php`; every
  vendored library must work on it, so a library whose latest release
  needs a newer PHP is pinned with `composer config platform.php 8.2.0`
  in its `sanz.txt` - `setup` runs `check_composer()` to catch any
  mismatch)
- Node.js (for frontend testing)
- MySQL/MariaDB or SQLite
- Git

### Installation

```bash
# 1. Fork and clone the repository
git clone https://github.com/YOUR_USERNAME/SaltOS4.git
cd SaltOS4

# 2. Create a development instance
mkdir dev-instance
cd dev-instance
bash ../scripts/make_instance.sh

# 3. Setup database (uses SQLite by default)
php api/index.php setup

# 4. Start development server
php -S localhost:8080 -t web ../scripts/router.php
```

### Verify Installation

```bash
# Check directories and available commands
make check
```

It lists the instance directories and the development and production
commands, each one marked as `OK` or `KO`. A `KO` only matters for the
features that need that command.

```bash
# Check the web server configuration (from the instance directory)
php api/index.php setup/server http://localhost:8080/api
```

It requests the API through the web server and returns the errors and
warnings found: API not reachable, `Authorization` header not forwarded,
`X-Powered-By` or a detailed `Server` header exposed, private paths
(`config.xml`, `saltos.sqlite`, libraries, ...) that can be downloaded or
executed, directories that show their contents, wrong content types and
the full code published instead of only the `web` directory. A `count` of
0 means that the installation is correct, with any web server.

### Web server

Only the `web` directory must be published. The `api`, `apps` and `data`
directories live outside of the document root, so the web server does not
need any rule to protect them:

- `web/api/index.php` is the only entry to the API, it jumps to the real
  `api/index.php`.
- `web/apps` only contains links to the public files of the apps (js, css
  and pdf). `make web` generates it with `scripts/makeapps.php`, and
  `make devel` replaces it by a link to the full `apps` directory.
- `scripts/router.php` makes `php -S` return the same 403 and 404 errors
  that Apache or nginx.

`scripts/` contains one recipe for each web server: `server.nginx.conf`,
`server.apache.conf` and `server.htaccess` (for shared hostings where all
the code is published). The `.htaccess` files of `api`, `apps` and `data`
only deny everything: they are a minimum security for Apache when the full
code is published by mistake, and nginx ignores them.

Instances created with an old `make_instance.sh` publish the full `api`
and `apps` directories, migrate them with `scripts/migrate_instance.sh`.

---

## 🐳 Docker

Two runtime profiles (see README.md for the quick "build and start" steps):

```bash
# devel: PHP built-in server + SQLite
make develbuild && make develstart
make develstatus   # Show container status
make devellogs     # Show logs
make develbash     # Open shell inside container
make develstop     # Stop and remove container

# server: nginx + PHP-FPM + MariaDB
make serverbuild && make serverstart
make serverstatus  # Show container status
make serverlogs    # Show logs
make serverbash    # Open shell inside container
make serverstop    # Stop and remove containers
```

A third profile, `test`, doesn't build SaltOS — it only starts the external
services the test suite needs for integration testing: Microsoft SQL Server
2022, PostgreSQL 17, and GreenMail (SMTP/POP3 simulation).

```bash
make teststart     # Start test dependencies
make teststatus    # Show running containers
make testlogs      # View service logs
make teststop      # Stop and cleanup
```

### The `demos` profile (maintainer-only)

`Dockerfile.demos` builds the image behind
[demos.saltos.org](https://demos.saltos.org/) — an intermediate between
`devel` and `server`: nginx + PHP-FPM + the full toolchain (chromium,
LibreOffice, tesseract, xlsxio) like `server`, but SQLite like `devel`.
Unlike the other profiles it doesn't serve a single instance: its entry point
(`scripts/demos_index.php`) hashes each visitor's IP and provisions a fresh,
fully set-up instance per hash on first visit, and a cron job
(`scripts/demos_trash.php`) retires instances untouched for 7 days. The
`instances` volume contains two directories: `private`, with the full
instances, and `public`, the document root, that only contains one link per
hash to the `web` directory of its instance, so the `api`, `apps` and `data`
of each visitor are never published. `scripts/demos_sync.php` keeps `public`
in sync with `private` each time that an instance is created or retired. Most
contributors will never need this profile — it exists to run the public
demo site, not for local development.

```bash
make demosbuild && make demosstart
make demosstatus   # Show container status
make demoslogs     # Show logs
make demosbash     # Open shell inside container
make demosstop     # Stop and remove container
```

---

## 🧪 Testing

SaltOS 4 has comprehensive test coverage (over 96% of lines in PHPUnit) using PHPUnit and Jest.

### Running Tests

```bash
# Lint code (PHP + JavaScript)
make test

# Backend tests (PHPUnit)
make utest              # Run modified tests only
make utest file=all     # Run all tests
make utest file=core    # Run specific test file

# Frontend tests (Jest)
make ujest              # Run modified tests only
make ujest file=all     # Run all tests
make ujest file=filter  # Run specific test file
```

### Code Quality

```bash
# PHP linting
make test               # Runs phpcs + phpstan + jscs

# Individual checks
phpcs --standard=scripts/phpcs.xml path/to/file.php
phpstan -c scripts/phpstan.neon analyse path/to/file.php
jscs --config=scripts/jscs.json path/to/file.js
```

### Writing Tests

**Backend (PHPUnit):**
```php
// utest/test_myfeature.php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

final class test_myfeature extends TestCase
{
    #[testdox('myfeature functions')]
    public function test_myfeature(): void
    {
        $this->assertSame(my_function('input'), 'expected');
    }
}
```

**Frontend (Jest):**
```javascript
// ujest/test_myfeature.js
describe('MyFeature', () => {
    test('should work correctly', () => {
        const result = my_function('input');
        expect(result).toBe('expected');
    });
});
```

---

## 📝 Coding Standards

### PHP

- **PSR-12** coding standard (enforced by phpcs)
- Use **strict types**: `declare(strict_types=1);`
- Document functions with PHPDoc
- Prefer explicit variable names (except standard: `$i`, `$j`, `$e`)

```php
<?php
declare(strict_types=1);

/**
 * Calculate customer total invoices
 *
 * This function returns the total amount of the invoices of a customer
 *
 * @customer_id => the id of the customer
 */
function calculate_customer_total(int $customer_id): float
{
    // Implementation
}
```

### JavaScript

- **JSCS** standard (see `scripts/jscs.json`)
- Use semicolons
- Single quotes for strings
- snake_case for functions and variables

```javascript
function calculate_total(items) {
    let total = 0;
    for (let i = 0; i < items.length; i++) {
        total += items[i].price;
    }
    return total;
}
```

### YAML/XML

- **4 spaces** indentation (YAML and XML)
- Lowercase field names with underscores

```yaml
# Good
app: customers
list:
    - [customer_name, text, Name]

# Bad
app: Customers
list:
  - [CustomerName, text, Name]
```

---

## 🏗️ Project Structure
```
SaltOS4/
├── code/
│   ├── api/                    # Backend PHP
│   │   ├── index.php           # Entry point
│   │   ├── php/
│   │   │   ├── action/         # Controllers (app.php, auth.php, etc.)
│   │   │   ├── autoload/       # Core functions (autoloaded)
│   │   │   ├── database/       # Multi-database drivers
│   │   │   └── lib/            # Utilities (actions.php, version.php, etc.)
│   │   ├── xml/                # Core schemas and config
│   │   ├── locale/             # Translations (ca_ES, en_US, es_ES)
│   │   └── lib/                # External PHP libraries
│   │       ├── tcpdf/          # PDF generation
│   │       ├── phpspreadsheet/ # Excel support
│   │       ├── yaml/           # YAML parser
│   │       └── ...
│   ├── apps/                   # Modular applications
│   │   ├── crm/                # CRM (customers, leads, quotes)
│   │   │   ├── xml/            # YAML/XML definitions
│   │   │   ├── php/            # Custom logic
│   │   │   ├── js/             # Custom frontend
│   │   │   ├── locale/         # Translations
│   │   │   ├── sample/         # Sample data
│   │   │   └── lib/            # Optional: app-specific vendored deps
│   │   │                       # (e.g. emails/lib/mailmimeparser,
│   │   │                       #  certs/lib/fpdi) — same idea as
│   │   │                       #  code/api/lib/, scoped to this app
│   │   ├── sales/              # Sales (products, invoices)
│   │   ├── purchases/          # Purchases (suppliers)
│   │   ├── hr/                 # HR (employees, departments)
│   │   ├── emails/             # Email integration (POP3/SMTP)
│   │   ├── company/            # Company settings
│   │   ├── users/              # User & permission management
│   │   ├── dashboard/          # Main dashboard
│   │   ├── certs/              # Digital certificates
│   │   ├── common/             # Shared functionality
│   │   └── tester/             # Internal testing tools
│   ├── web/                    # Frontend (PWA)
│   │   ├── index.html          # Entry point
│   │   ├── js/                 # JavaScript core
│   │   ├── lib/                # External JS libraries
│   │   │   ├── bootstrap/
│   │   │   ├── joditeditor/
│   │   │   ├── echarts/
│   │   │   ├── codemirror/
│   │   │   ├── pdfjs/
│   │   │   └── ...
│   │   ├── html/               # HTML templates
│   │   ├── img/                # Images/icons
│   │   ├── api/                # index.php that jumps to the real API
│   │   └── apps/               # Links to the public files of the apps
│   └── data/                   # Runtime data (gitignored)
│       ├── files/              # User uploads
│       ├── cache/              # Cache files
│       ├── logs/               # Application logs
│       ├── temp/               # Temporary files
│       ├── inbox/              # Email inbox
│       ├── outbox/             # Email outbox
│       ├── upload/             # Upload staging
│       ├── trash/              # Deleted files
│       └── cron/               # Cron job data
├── docs/                       # Documentation (9 PDFs)
├── scripts/                    # Build and utility scripts
│   ├── make_instance.sh        # Create new instance
│   ├── migrate_instance.sh     # Migrate an old instance
│   ├── makeapps.php            # Generate web/apps
│   ├── router.php              # Router for php -S
│   ├── server.nginx.conf       # nginx recipe
│   ├── server.apache.conf      # Apache recipe
│   ├── server.htaccess         # Apache recipe for shared hostings
│   ├── phpcs.xml               # PHP CodeSniffer config
│   ├── phpstan.neon            # PHPStan config
│   ├── jest.config.js          # Jest config
│   └── checklibs.txt           # Dependency versions
├── utest/                      # PHPUnit backend tests
│   ├── test_*.php              # Test files
│   ├── files/                  # Test fixtures
│   └── lib/                    # Test utilities
├── ujest/                      # Jest frontend tests
│   ├── test_*.js               # Test files
│   ├── snaps/                  # Jest snapshots
│   └── lib/                    # Test utilities
├── makefile                    # Build automation
└── README.md
```

---

## 🔄 Pull Request Process

### Before Submitting

1. **Create a feature branch**
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. **Make your changes**
   - Follow coding standards
   - Add/update tests
   - Update documentation if needed

3. **Test thoroughly**
   ```bash
   make test        # Linting
   make utest       # Backend tests
   make ujest       # Frontend tests
   ```

4. **Commit with clear messages**
   ```bash
   git commit -m "Add customer export to Excel feature"
   ```

### Submitting

1. Push to your fork
   ```bash
   git push origin feature/my-new-feature
   ```

2. Open a Pull Request on GitHub
   - Describe what the PR does
   - Reference any related issues (#123)
   - Include screenshots for UI changes

3. Wait for review
   - Maintainers will review your code
   - Address any feedback
   - CI tests must pass

### PR Guidelines

- ✅ One feature/fix per PR
- ✅ Tests included for new code
- ✅ Documentation updated
- ✅ Clean commit history
- ❌ Don't include unrelated changes
- ❌ Don't break existing functionality

---

## 📚 Creating New Apps

To create a new app, you need 3 files:

### 1. Database Schema (`apps/myapp/xml/dbschema.xml`)

```xml
<root>
    <tables>
        <table name="app_myapp">
            <fields>
                <field name="id" type="INTEGER" pkey="true"/>
                <field name="name" type="VARCHAR(255)"/>
            </fields>
        </table>
    </tables>
</root>
```

### 2. App Definition (`apps/myapp/xml/myapp.yaml`)

```yaml
app: myapp
template: apps/common/xml/default.xml

list:
    - [name, text, Name]

form:
    - [name, text, Name]
```

### 3. Manifest (`apps/myapp/xml/manifest.yaml`)

```yaml
apps:
    - id: 100
      active: 1
      code: myapp
      name: My App
      table: app_myapp
      field: name
      has_version: 1
      has_files: 1
      has_notes: 1
```

[📖 Read the full Apps Guide](https://raw.githubusercontent.com/josepsanzcamp/SaltOS4/master/docs/apps.pdf)

---

## 🌍 Translations

SaltOS 4 uses **YAML-based translations** (not GNU gettext, despite function names).

### Translation Files

Translations are stored in YAML format:
```
code/api/locale/{lang}/messages.yaml
code/apps/{app}/locale/{lang}/messages.yaml
```

Supported languages:
- `en_US` — English (US)
- `es_ES` — Spanish (Spain)
- `ca_ES` — Catalan

### Adding Translations

1. **Edit YAML files**
```yaml
   # code/api/locale/es_ES/messages.yaml
   Hello: "Hola"
   Goodbye: "Adiós"
```

2. **Check translation status**
```bash
   make langs

   # Check missing translations for specific language
   python3 scripts/checklangs.py --lang en_US --group crm --filter missing
```

### Translation System

Despite using YAML instead of `.po`/`.mo` files, translation is a single
short function call, `T()` (`code/api/php/autoload/gettext.php`):
```php
echo T("Hello");        // Returns the translated string
```

The same `T()` call is available in JS and in YAML/XML app definitions.

---

## 📖 Documentation

### Generating PDFs

```bash
# Generate all documentation
make docs

# Generate specific docs
make docs file=api      # API reference
make docs file=web      # Web client
make docs file=devel    # Developer guide
make docs file=user     # User manual
make docs file=locale   # Per-app help PDFs
```

### Documentation Sources

- **API/Web/Apps docs**: Auto-generated from code comments
- **Developer/User guides**: `docs/*.t2t` (txt2tags format)
- **Per-app help**: `code/apps/*/locale/*/*.t2t`, one PDF per app and language

---

## 🐛 Reporting Bugs

When reporting bugs, please include:

- **SaltOS version**: Run `git describe --tags`, or check the About dialog
- **PHP version**: `php -v`
- **Database**: MySQL/PostgreSQL/SQLite + version
- **Browser**: Chrome/Firefox/Safari + version
- **Steps to reproduce**
- **Expected vs actual behavior**
- **Error messages** (from browser console + PHP logs)

---

## 💬 Getting Help

- **GitHub Discussions**: General questions and ideas
- **GitHub Issues**: Bug reports and feature requests
- **Email**: josep.sanz@saltos.org
- **Documentation**: [9 PDFs (the user manual in 3 languages)](https://github.com/josepsanzcamp/SaltOS4/tree/master/docs)

---

## 📄 License

By contributing to SaltOS 4, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).

---

## 🙏 Thank You!

Every contribution, no matter how small, helps make SaltOS 4 better. We appreciate your time and effort!

**Happy coding! 🚀**
