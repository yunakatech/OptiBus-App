import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const rootDir = process.cwd();
const targetDir = path.join(rootDir, 'resources', 'js');
const ignoredDirNames = new Set(['actions', 'routes', 'wayfinder']);
const fileExtensions = new Set(['.js', '.ts', '.svelte', '.mjs', '.cjs']);
const forbiddenPatterns = [
    {
        name: 'window.location.href assignment',
        regex: /window\.location\.href\s*=/g,
    },
    {
        name: 'window.location.assign()',
        regex: /window\.location\.assign\s*\(/g,
    },
    {
        name: 'window.location.replace()',
        regex: /window\.location\.replace\s*\(/g,
    },
];

const violations = [];

function walk(dirPath) {
    const entries = fs.readdirSync(dirPath, { withFileTypes: true });

    for (const entry of entries) {
        const fullPath = path.join(dirPath, entry.name);

        if (entry.isDirectory()) {
            if (ignoredDirNames.has(entry.name)) {
                continue;
            }

            walk(fullPath);
            continue;
        }

        if (!entry.isFile() || !fileExtensions.has(path.extname(entry.name))) {
            continue;
        }

        inspectFile(fullPath);
    }
}

function inspectFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split(/\r?\n/);

    lines.forEach((line, index) => {
        for (const pattern of forbiddenPatterns) {
            if (!pattern.regex.test(line)) {
                pattern.regex.lastIndex = 0;
                continue;
            }

            violations.push({
                filePath,
                lineNumber: index + 1,
                pattern: pattern.name,
                source: line.trim(),
            });
            pattern.regex.lastIndex = 0;
        }
    });
}

if (!fs.existsSync(targetDir)) {
    console.error(`Target directory not found: ${targetDir}`);
    process.exit(1);
}

walk(targetDir);

if (violations.length > 0) {
    console.error('Forbidden internal navigation pattern detected. Use Inertia `router.visit()` or `Link` instead.\n');

    for (const violation of violations) {
        const relativePath = path.relative(rootDir, violation.filePath);
        console.error(`- ${relativePath}:${violation.lineNumber} [${violation.pattern}]`);
        console.error(`  ${violation.source}`);
    }

    process.exit(1);
}

console.log('Internal navigation guard passed.');
