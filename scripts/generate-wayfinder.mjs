import { spawnSync } from 'node:child_process';
import { existsSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(scriptDir, '..');

const resolvePhpBinary = () => {
    const phpBinary = process.env.PHP_BINARY?.trim();
    if (phpBinary) {
        return phpBinary;
    }

    if (process.platform !== 'win32') {
        return 'php';
    }

    const laragonPhpRoot = 'D:/laragon/bin/php';
    if (!existsSync(laragonPhpRoot)) {
        return 'php';
    }

    const candidates = readdirSync(laragonPhpRoot, { withFileTypes: true })
        .filter((entry) => entry.isDirectory())
        .map((entry) => path.join(laragonPhpRoot, entry.name, 'php.exe'))
        .filter((binaryPath) => existsSync(binaryPath))
        .sort((a, b) => b.localeCompare(a, undefined, { numeric: true, sensitivity: 'base' }));

    return candidates[0] ?? 'php';
};

const patchBookingControllerActions = () => {
    const target = path.join(repoRoot, 'resources/js/actions/App/Http/Controllers/BookingController.ts');
    if (!existsSync(target)) {
        return;
    }

    const source = readFileSync(target, 'utf8');
    const replacement = `const BookingController: Record<string, unknown> & {
    printManifest?: typeof printManifest;
    downloadManifestPdf?: typeof downloadManifestPdf;
    printTicket?: typeof printTicket;
    downloadTicketPdf?: typeof downloadTicketPdf;
} = {`;

    if (source.includes(replacement)) {
        return;
    }

    const next = source.replace('const BookingController = {', replacement);
    if (next !== source) {
        writeFileSync(target, next, 'utf8');
    }
};

const phpBinary = resolvePhpBinary();
const result = spawnSync(phpBinary, ['artisan', 'wayfinder:generate', '--with-form'], {
    cwd: repoRoot,
    stdio: 'inherit',
});

if (result.error) {
    console.error(`[Wayfinder] Failed to run PHP binary "${phpBinary}": ${result.error.message}`);
    process.exit(1);
}

if (result.status !== 0) {
    console.error(`[Wayfinder] PHP command exited with status ${result.status ?? 'unknown'}.`);
    process.exit(result.status ?? 1);
}

patchBookingControllerActions();
