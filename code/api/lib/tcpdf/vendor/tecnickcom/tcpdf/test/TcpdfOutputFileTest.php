<?php

declare(strict_types=1);

/**
 * File output tests for the TCPDF compatibility facade.
 *
 * Cover the 'F', 'FI' and 'FD' destinations, which take a full file path and
 * are split into the directory and file name the engine expects.
 *
 * @package com.tecnick.tcpdf
 */

use PHPUnit\Framework\TestCase;

class TcpdfOutputFileTest extends TestCase
{
    /**
     * Scratch directory, inside the system temp dir so that it is covered by
     * the default allowed paths.
     */
    private string $dir = '';

    /** @throws \Random\RandomException */
    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/tcpdf-out-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        if ($this->dir === '' || !is_dir($this->dir)) {
            return;
        }

        $files = glob($this->dir . '/*');
        foreach ($files === false ? [] : $files as $file) {
            unlink($file);
        }

        rmdir($this->dir);
    }

    private function buildDocument(): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setFont('helvetica', '', 12);
        $pdf->AddPage();
        $pdf->Cell(0, 0, 'Hello facade', 0, 1, 'L');
        return $pdf;
    }

    /**
     * Names present in the scratch directory.
     *
     * @return array<int, string>
     */
    private function written(): array
    {
        $entries = scandir($this->dir);

        return array_values(array_diff($entries === false ? [] : $entries, ['.', '..']));
    }

    public function testWritesTheRequestedFile(): void
    {
        $path = $this->dir . '/target.pdf';

        $this->assertSame('', $this->buildDocument()->Output($path, 'F'));

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF-', (string) file_get_contents($path));
    }

    public function testWrittenFileHoldsTheWholeDocument(): void
    {
        $path = $this->dir . '/same.pdf';
        $pdf = $this->buildDocument();
        $pdf->Output($path, 'F');

        // Same instance: the buffer is built once, so the two must agree.
        $this->assertSame($pdf->Output('same.pdf', 'S'), (string) file_get_contents($path));
    }

    public function testWritesARelativeNameInTheWorkingDirectory(): void
    {
        $cwd = (string) getcwd();
        chdir($this->dir);

        try {
            $this->buildDocument()->Output('out.pdf', 'F');
        } finally {
            chdir($cwd);
        }

        $this->assertFileExists($this->dir . '/out.pdf');
    }

    public function testSanitizesTheFileNameInsteadOfUsingTheDocumentId(): void
    {
        $expected = [
            'my_report.final.pdf' => 'my_report_final.pdf',
            'report v1.2.pdf' => 'report v1_2.pdf',
            'facture_#42 (copie).pdf' => 'facture__42 _copie_.pdf',
        ];

        foreach ($expected as $given => $want) {
            $this->buildDocument()->Output($this->dir . '/' . $given, 'F');
            $this->assertFileExists($this->dir . '/' . $want);
        }

        foreach ($this->written() as $file) {
            $this->assertDoesNotMatchRegularExpression('/^[0-9a-f]{32}\.pdf$/', $file);
        }
    }

    public function testGetPDFFilenameReportsTheNameWritten(): void
    {
        $pdf = $this->buildDocument();
        $pdf->Output($this->dir . '/my_report.final.pdf', 'F');

        $this->assertSame('my_report_final.pdf', $pdf->getPDFFilename());
        $this->assertFileExists($this->dir . '/' . $pdf->getPDFFilename());
    }

    public function testGetPDFFilenameReportsAnUnchangedName(): void
    {
        $pdf = $this->buildDocument();
        $pdf->Output($this->dir . '/plain.pdf', 'F');

        $this->assertSame('plain.pdf', $pdf->getPDFFilename());
    }

    public function testKeepsDistinctNamesDistinct(): void
    {
        $batch = ['inv-2024.01.15.pdf', 'inv-2024.02.20.pdf', 'inv-2024.03.05.pdf'];
        foreach ($batch as $name) {
            $this->buildDocument()->Output($this->dir . '/' . $name, 'F');
        }

        $this->assertCount(3, $this->written());
    }

    public function testMissingDirectoryRaisesAnError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Unable to create output file/');

        try {
            $this->buildDocument()->Output($this->dir . '/nope/target.pdf', 'F');
        } finally {
            $this->assertSame([], $this->written());
        }
    }

    public function testErrorMessageNamesTheAllowedPathsConstant(): void
    {
        $this->expectExceptionMessageMatches('/K_ALLOWED_PATHS/');

        $this->buildDocument()->Output($this->dir . '/nope/target.pdf', 'F');
    }

    public function testRejectsStreamWrapperDestinations(): void
    {
        $paths = [
            'phar://archive.phar/x.pdf',
            'php://output',
            'php://filter/write=convert.base64-decode/resource=/tmp/evil.pdf',
            'compress.zlib:///tmp/evil.pdf',
            'data://text/plain,evil',
            $this->dir . "/di\0r/x.pdf",
        ];

        foreach ($paths as $path) {
            $thrown = false;
            try {
                $this->buildDocument()->Output($path, 'F');
            } catch (Exception $e) {
                $thrown = str_contains($e->getMessage(), 'Unable to create output file');
            }

            $this->assertTrue($thrown, 'not rejected: ' . addcslashes($path, "\0..\37"));
        }
    }

    public function testNulByteInTheFileNameIsSanitizedAway(): void
    {
        $this->buildDocument()->Output($this->dir . "/ev\0il.pdf", 'F');

        $written = $this->written();
        $this->assertSame(['ev_il.pdf'], $written);
        $this->assertStringNotContainsString("\0", $written[0]);
    }

    public function testPhpFilterDestinationWritesNothing(): void
    {
        $target = $this->dir . '/evil.pdf';

        try {
            $this->buildDocument()->Output('php://filter/write=convert.base64-decode/resource=' . $target, 'F');
        } catch (Exception) {
            // The destination is rejected; the assertion below is the point.
        }

        $this->assertFileDoesNotExist($target);
    }

    public function testBooleanFalseDestinationSavesToFile(): void
    {
        $path = $this->dir . '/legacy-bool.pdf';

        ob_start();
        $returned = $this->buildDocument()->Output($path, false);
        $echoed = (string) ob_get_clean();

        $this->assertSame('', $returned);
        $this->assertSame('', $echoed);
        $this->assertFileExists($path);
    }

    public function testFileAndInlineDestinationWritesTheFile(): void
    {
        $path = $this->dir . '/inline.pdf';

        ob_start();
        $this->buildDocument()->Output($path, 'FI');
        $echoed = (string) ob_get_clean();

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF-', $echoed);
    }

    public function testFileAndDownloadDestinationWritesTheFile(): void
    {
        $path = $this->dir . '/download.pdf';

        ob_start();
        $this->buildDocument()->Output($path, 'FD');
        $echoed = (string) ob_get_clean();

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF-', $echoed);
    }

    public function testAttachmentDestinationSendsASanitizedName(): void
    {
        $mime = $this->buildDocument()->Output('report v1.2.pdf', 'E');

        // The header carries the rawurlencode()d form of the sanitized name.
        $this->assertStringContainsString('name="report%20v1_2.pdf"', $mime);
        $this->assertDoesNotMatchRegularExpression('/name="[0-9a-f]{32}\.pdf"/', $mime);
    }

    public function testStringDestinationIsUnchanged(): void
    {
        $raw = $this->buildDocument()->Output('doc.pdf', 'S');

        $this->assertStringStartsWith('%PDF-', $raw);
        $this->assertSame([], $this->written());
    }
}
