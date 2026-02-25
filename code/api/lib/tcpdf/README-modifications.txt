TCPDF – Local LGPL Modifications
=================================

This project includes the TCPDF library (LGPL-3.0-only)
distributed via Composer (tecnickcom/tcpdf).

The original TCPDF license and copyright notices
are preserved without modification.

Local Modifications
-------------------

In order to ensure compatibility with PHP 8.5 and to suppress
deprecation warnings introduced in newer PHP versions, minor
non-functional changes were applied to the following files:

- vendor/tecnickcom/tcpdf/tcpdf.php
- vendor/tecnickcom/tcpdf/tcpdf_barcodes_1d.php
- vendor/tecnickcom/tcpdf/tcpdf_barcodes_2d.php

Description of Changes
----------------------

Certain deprecated PHP functions were replaced with project-local
wrapper functions:

- imagedestroy() → imagedestroy_deprecated()

The wrapper functions conditionally call the original PHP functions
only for PHP versions prior to 8.0, where manual resource cleanup
is still relevant.

No functional behavior of TCPDF was modified.
These changes only prevent deprecation warnings under PHP 8.5.

License Compliance
------------------

TCPDF remains licensed under the GNU Lesser General Public License
(LGPL-3.0-only).

All local modifications to TCPDF are distributed under the same
LGPL-3.0-only license terms as the original library.

The complete, corresponding source code of the modified TCPDF
version is available within this repository.
