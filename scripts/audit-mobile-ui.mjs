import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { parse } from 'svelte/compiler';

export function svelteFiles(directory) {
    return fs
        .readdirSync(directory, { withFileTypes: true })
        .flatMap((entry) =>
            entry.isDirectory()
                ? svelteFiles(path.join(directory, entry.name))
                : entry.name.endsWith('.svelte')
                  ? [path.join(directory, entry.name)]
                  : [],
        );
}
export const attribute = (node, name) =>
    node.attributes?.find(
        (item) => item.type === 'Attribute' && item.name === name,
    );
export const staticValue = (node, name) => {
    const value = attribute(node, name)?.value;

    return Array.isArray(value) && value.every((item) => item.type === 'Text')
        ? value.map((item) => item.data).join('')
        : undefined;
};
export function inspectFile(file) {
    const source = fs
        .readFileSync(file, 'utf8')
        .replace(/^\uFEFF/, '')
        .replace(/\r\n/g, '\n');
    const ast = parse(source);
    const labels = new Set();
    const fields = [];
    function walk(node, ancestors = [], siblings = []) {
        if (!node || typeof node !== 'object') {
return;
}

        if (Array.isArray(node)) {
            node.forEach((item) => walk(item, ancestors, node));

            return;
        }

        if (['label', 'Label'].includes(node.name) && staticValue(node, 'for')) {
labels.add(staticValue(node, 'for'));
}

        if (
            ['Input', 'input', 'select', 'textarea'].includes(node.name) &&
            !['hidden', 'radio', 'checkbox', 'file', 'submit'].includes(
                staticValue(node, 'type'),
            ) &&
            !ancestors.some((parent) =>
                ['label', 'Label'].includes(parent.name),
            )
        ) {
            fields.push({ node, ancestors, siblings });
        }

        for (const [key, value] of Object.entries(node)) {
            if (
                ![
                    'attributes',
                    'expression',
                    'instance',
                    'module',
                    'css',
                ].includes(key)
            ) {
walk(value, [...ancestors, node], siblings);
}
        }
    }
    walk(ast.html);

    return {
        file,
        source,
        ast,
        fields: fields.filter(
            ({ node }) =>
                !labels.has(staticValue(node, 'id')) &&
                !attribute(node, 'aria-labelledby'),
        ),
    };
}
export function mobileSourceFiles() {
    return [
        ...svelteFiles('resources/js/pages'),
        ...svelteFiles('resources/js/components/admin-ops'),
        ...svelteFiles('resources/js/components/admin-ops-flows'),
        ...svelteFiles('resources/js/components/bookings'),
    ];
}

if (
    process.argv[1] &&
    import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href
) {
    const findings = mobileSourceFiles().flatMap((file) => {
        const { source, fields } = inspectFile(file);

        return fields.map(({ node }) => ({
            file,
            line: source.slice(0, node.start).split('\n').length,
            control: node.name,
            placeholder: staticValue(node, 'placeholder'),
            accessibleLabel: staticValue(node, 'aria-label'),
        }));
    });
    console.log(
        JSON.stringify(
            { fieldsWithoutVisibleLabels: findings.length, findings },
            null,
            2,
        ),
    );

    if (findings.length) {
process.exitCode = 1;
}
}
