import { fileURLToPath } from 'node:url';
import { readdirSync } from 'node:fs';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

const root = fileURLToPath(new URL('../../../', import.meta.url));
const fixture = fileURLToPath(new URL('./', import.meta.url));

// Isolated local UI fixture: the real component/styles, a fake Inertia page,
// and no backend requests or production entrypoints.
export default defineConfig({
    root: fixture,
    publicDir: false,
    resolve: {
        alias: {
            '@': `${root}resources/js`,
            '@inertiajs/svelte': `${fixture}inertia.svelte.ts`,
        },
    },
    plugins: [
        svelte(),
        tailwindcss(),
        {
            name: 'fixture-built-fonts',
            transformIndexHtml(html) {
                try {
                    const fonts = readdirSync(
                        `${root}public/build/assets`,
                    ).find((file) => /^fonts-.*\.css$/.test(file));
                    if (fonts) {
                        return html.replace(
                            '</head>',
                            `<link rel="stylesheet" href="/@fs/${root.replaceAll('\\', '/')}public/build/assets/${fonts}"></head>`,
                        );
                    }
                } catch {
                    /* Fonts are optional before the first build. */
                }
                return html;
            },
        },
    ],
    server: {
        host: '127.0.0.1',
        port: 4187,
        strictPort: true,
        fs: { allow: [root] },
    },
});
