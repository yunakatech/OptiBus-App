<?php

namespace App\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

class HeadlessPdf
{
    /**
     * @param  array{title?: string}  $options
     */
    public static function renderHtmlToPdf(string $html, string $outputPath, array $options = []): void
    {
        $browser = self::resolveBrowserBinary();
        $tempHtml = tempnam(sys_get_temp_dir(), 'qbus-pdf-');

        if ($tempHtml === false) {
            throw new RuntimeException('Gagal menyiapkan file sementara untuk PDF.');
        }

        $htmlPath = $tempHtml.'.html';
        @rename($tempHtml, $htmlPath);

        $wrappedHtml = self::normalizeHtml($html, $options['title'] ?? 'Qbus PDF');
        file_put_contents($htmlPath, $wrappedHtml);

        $fileUrl = 'file:///'.str_replace('\\', '/', ltrim($htmlPath, '\\/'));
        $command = [
            $browser,
            '--headless=new',
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--allow-file-access-from-files',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=2500',
            '--no-pdf-header-footer',
            '--print-to-pdf='.$outputPath,
            $fileUrl,
        ];

        $process = new Process($command);
        $process->setTimeout(30);
        $process->run();

        @unlink($htmlPath);

        if (! $process->isSuccessful() || ! is_file($outputPath) || filesize($outputPath) <= 0) {
            throw new RuntimeException('Gagal membuat file PDF server-side.');
        }
    }

    private static function resolveBrowserBinary(): string
    {
        $configuredBrowser = env('BROWSER_BINARY') ?: env('HEADLESS_BROWSER_BINARY');

        if (is_string($configuredBrowser) && $configuredBrowser !== '' && is_file($configuredBrowser)) {
            return $configuredBrowser;
        }

        $candidates = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/snap/bin/chromium',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Browser headless untuk export PDF tidak ditemukan. Set env BROWSER_BINARY jika perlu.');
    }

    private static function normalizeHtml(string $html, string $title): string
    {
        if (str_contains($html, '<html')) {
            return $html;
        }

        return '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>'
            .e($title)
            .'</title></head><body>'
            .$html
            .'</body></html>';
    }
}
