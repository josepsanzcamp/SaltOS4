# Security Policy

## Supported Versions

We actively provide security updates **only for the latest release** of SaltOS 4. Older minor versions are considered End-of-Life (EOL) immediately upon the release of a new version.

| Version | Supported          | Notes |
| ------- | ------------------ | ----- |
| >= 4.1  | :white_check_mark: Yes | Current stable release (MIT License). Active security support. |
| 4.0     | :x: No             | End of Life. Users must upgrade to 4.1+ for security fixes. |
| < 4.0   | :x: No             | Legacy versions (GPL-3.0). Unsupported. |

## Reporting a Vulnerability

**Please do not open a public GitHub Issue to report a security vulnerability.**

If you discover a security flaw in SaltOS 4, we ask that you report it securely and privately so we can address it before it becomes public knowledge:

1. **Via GitHub Private Report (Recommended):** Go to the **Security** tab of this repository, click on **Advisories**, and then click **Report a vulnerability**.
2. **Via Email:** Alternatively, you can send a detailed encrypted or plain email to: **info@saltos.org**

### What to include in your report:
* **Description:** A clear and concise overview of the vulnerability.
* **Steps to reproduce:** Detailed steps, commands, or a minimal Proof of Concept (PoC).
* **Environment details:** PHP version, Database driver (SQLite/MySQL/PostgreSQL/MSSQL), and SaltOS version (`git describe --tags`).
* **Impact:** What could an attacker achieve if this vulnerability is exploited?

## Our Commitment

If you report a vulnerability, we commit to:
* Acknowledge receipt of your report within **48 hours**.
* Provide an estimated timeline for a fix and keep you updated on progress.
* Coordinate a public disclosure date once the fix is released and instances have had time to update.
* Credit you in the release notes (if desired) for helping keep SaltOS secure.
