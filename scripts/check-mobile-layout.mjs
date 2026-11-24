// Uses synthetic payloads exported by MobilePagesTest, never a production login.
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import http from 'node:http';
import os from 'node:os';
import path from 'node:path';

const root = process.cwd();
const output = path.join(root, 'storage/framework/testing/mobile-preview');
const fixtures = JSON.parse(
    fs.readFileSync(path.join(output, 'pages.json'), 'utf8'),
);
const manifest = JSON.parse(
    fs.readFileSync('public/build/manifest.json', 'utf8'),
);
const css = new Set();
let failingRoute = null;
let latency = 0;
function collectCss(key) {
    const entry = manifest[key];

    if (!entry) {
return;
}

    if (entry.file.endsWith('.css')) {
css.add(entry.file);
}

    entry.css?.forEach((file) => css.add(file));
    entry.imports?.forEach(collectCss);
}
collectCss('resources/js/app.ts');
collectCss('resources/css/app.css');
const server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');

    if (url.pathname.startsWith('/api/')) {
        res.setHeader('Content-Type', 'application/json');
        res.end(
            JSON.stringify({
                success: true,
                pending_count: 0,
                routes: [],
                schedules: [],
                assignments: [],
                drivers: [],
                armadas: [],
                riturs: [],
                mapped: [],
                available: [],
            }),
        );

        return;
    }

    const fixture = fixtures[url.pathname];

    if (fixture) {
        const page = { ...fixture, url: url.pathname + url.search };

        if (req.headers['x-inertia']) {
            if (failingRoute === url.pathname) {
                res.writeHead(503, { 'Content-Type': 'application/json' });
                res.end(
                    JSON.stringify({ message: 'Synthetic retry scenario' }),
                );

                return;
            }

            res.setHeader('Content-Type', 'application/json');
            res.setHeader('X-Inertia', 'true');
            setTimeout(() => res.end(JSON.stringify(page)), latency);
        } else {
            res.setHeader('Content-Type', 'text/html; charset=utf-8');
            const data = JSON.stringify(page).replaceAll('<', '\\u003c');
            res.end(
                `<!doctype html><html lang="id"><head><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="csrf-token" content="preview">${[...css].map((file) => `<link rel="stylesheet" href="/build/${file}">`).join('')}<script type="module" src="/build/${manifest['resources/js/app.ts'].file}"></script></head><body><script data-page="app" type="application/json">${data}</script><div id="app"></div></body></html>`,
            );
        }

        return;
    }

    const file = path.resolve(root, 'public', '.' + url.pathname);
    const publicRoot = path.resolve(root, 'public') + path.sep;

    if (
        !file.startsWith(publicRoot) ||
        !fs.existsSync(file) ||
        !fs.statSync(file).isFile()
    ) {
        res.writeHead(404);
        res.end();

        return;
    }

    const mime = {
        '.js': 'text/javascript',
        '.css': 'text/css',
        '.png': 'image/png',
        '.svg': 'image/svg+xml',
        '.woff2': 'font/woff2',
        '.webp': 'image/webp',
        '.json': 'application/json',
    };
    res.setHeader(
        'Content-Type',
        mime[path.extname(file)] || 'application/octet-stream',
    );
    fs.createReadStream(file).pipe(res);
});
await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve));
const origin = `http://127.0.0.1:${server.address().port}`;
const chrome =
    process.env.CHROME_PATH ||
    'C:/Program Files/Google/Chrome/Application/chrome.exe';
const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'optibus-mobile-'));
const child = spawn(
    chrome,
    [
        '--headless=new',
        '--remote-debugging-port=0',
        '--no-first-run',
        '--no-default-browser-check',
        '--disable-background-networking',
        '--disable-extensions',
        `--user-data-dir=${profile}`,
        'about:blank',
    ],
    { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] },
);
let socket;

try {
    const endpoint = await new Promise((resolve, reject) => {
        let log = '';
        const timer = setTimeout(
            () => reject(new Error('Chrome DevTools startup timed out')),
            15000,
        );
        child.on('error', reject);
        child.stderr.on('data', (chunk) => {
            log += chunk;
            const match = log.match(/DevTools listening on (ws:\/\/\S+)/);

            if (match) {
                clearTimeout(timer);
                resolve(match[1]);
            }
        });
    });
    socket = new WebSocket(endpoint);
    await new Promise((resolve, reject) => {
        socket.onopen = resolve;
        socket.onerror = reject;
    });
    let nextId = 0;
    const pending = new Map();
    const errors = [];
    let current = '';
    socket.onmessage = (event) => {
        const message = JSON.parse(event.data);

        if (message.id) {
            const task = pending.get(message.id);
            pending.delete(message.id);

            if (message.error) {
task?.reject(new Error(message.error.message));
} else {
task?.resolve(message.result);
}
        } else if (message.method === 'Runtime.exceptionThrown') {
            errors.push({
                route: current,
                error:
                    message.params.exceptionDetails.exception?.description ||
                    message.params.exceptionDetails.text,
            });
        }
    };
    function call(method, params = {}, sessionId) {
        const id = ++nextId;

        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => {
                pending.delete(id);
                reject(new Error(`CDP timeout: ${method}`));
            }, 20000);
            pending.set(id, {
                resolve: (result) => {
                    clearTimeout(timer);
                    resolve(result);
                },
                reject,
            });
            socket.send(
                JSON.stringify({
                    id,
                    method,
                    params,
                    ...(sessionId ? { sessionId } : {}),
                }),
            );
        });
    }
    const target = await call('Target.createTarget', { url: 'about:blank' });
    const { sessionId } = await call('Target.attachToTarget', {
        targetId: target.targetId,
        flatten: true,
    });
    const cdp = (method, params) => call(method, params, sessionId);
    await cdp('Page.enable');
    await cdp('Runtime.enable');
    await cdp('Network.enable');
    await cdp('Page.addScriptToEvaluateOnNewDocument', {
        source: "sessionStorage.setItem('optibus.navigationMetrics','1')",
    });
    const evaluate = async (expression) => {
        const result = await cdp('Runtime.evaluate', {
            expression,
            returnByValue: true,
            awaitPromise: true,
        });

        if (result.exceptionDetails) {
throw new Error(result.exceptionDetails.text);
}

        return result.result.value;
    };
    const pause = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    async function navigate(route) {
        current = route;
        await cdp('Page.navigate', { url: origin + route });

        for (let attempt = 0; attempt < 70; attempt++) {
            await pause(100);

            if (
                await evaluate(
                    "document.readyState === 'complete' && document.querySelector('#app')?.children.length > 0",
                )
            ) {
                await pause(200);

                return;
            }
        }

        console.error(
            JSON.stringify({
                route,
                errors,
                document: await evaluate(
                    "({state:document.readyState, app:document.querySelector('#app')?.childElementCount, title:document.title})",
                ),
            }),
        );

        throw new Error(`Page did not mount: ${route}`);
    }
    const measurements = [];
    const screenshots = [
        '/login',
        '/register',
        '/dashboard',
        '/bookings',
        '/payments',
        '/charters',
        '/luggages',
        '/settings/customer-bagasi',
        '/platform/saas',
    ];

    for (const width of [320, 360, 390, 430, 1440]) {
        await cdp('Emulation.setDeviceMetricsOverride', {
            width,
            height: 844,
            deviceScaleFactor: 1,
            mobile: width < 768,
        });
        await cdp('Emulation.setTouchEmulationEnabled', {
            enabled: width < 768,
        });

        for (const route of Object.keys(fixtures)) {
            await navigate(route);
            const result =
                await evaluate(`(() => ({ width: innerWidth, scrollWidth: document.documentElement.scrollWidth,
                bodyWidth: document.body.scrollWidth, controls: document.querySelectorAll('input,select,textarea').length,
                mounted: document.querySelector('#app').children.length }))()`);
            measurements.push({
                route,
                viewport: width,
                ...result,
                pass: result.scrollWidth <= width + 1 && result.width === width,
            });

            if (width === 360 && screenshots.includes(route)) {
                const shot = await cdp('Page.captureScreenshot', {
                    format: 'png',
                    captureBeyondViewport: false,
                });
                fs.writeFileSync(
                    path.join(
                        output,
                        route.slice(1).replaceAll('/', '-') + '-360.png',
                    ),
                    Buffer.from(shot.data, 'base64'),
                );
            }
        }

        console.log(
            `Checked ${Object.keys(fixtures).length} pages at ${width}px`,
        );
    }

    // Check the actual filter dialog footer against a shrunken visual viewport.
    await cdp('Emulation.setDeviceMetricsOverride', {
        width: 430,
        height: 844,
        deviceScaleFactor: 1,
        mobile: true,
    });
    await cdp('Emulation.setTouchEmulationEnabled', { enabled: true });
    await navigate('/payments');
    await evaluate(
        "[...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Filter')?.click()",
    );
    await pause(300);
    await evaluate("document.querySelector('[role=dialog] input')?.focus()");
    await cdp('Emulation.setDeviceMetricsOverride', {
        width: 430,
        height: 430,
        deviceScaleFactor: 1,
        mobile: true,
    });
    await pause(300);
    const filter =
        await evaluate(`(() => { const dialog = document.querySelector('[role=dialog]');
        const button = [...(dialog?.querySelectorAll('button') || [])].find(b => b.textContent.trim() === 'Terapkan');
        const rect = button?.getBoundingClientRect(); return { dialog: !!dialog, applyVisible: !!rect && rect.top >= 0 && rect.bottom <= innerHeight }; })()`);
    await cdp('Emulation.setDeviceMetricsOverride', {
        width: 390,
        height: 844,
        deviceScaleFactor: 1,
        mobile: true,
    });
    async function waitFor(expression) {
        for (let i = 0; i < 80; i++) {
            if (await evaluate(expression)) {
return true;
}

            await pause(100);
        }

        return false;
    }
    await navigate('/bookings');
    await evaluate(
        "[...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Buka Detail')?.click()",
    );
    const detail = await waitFor(
        "location.pathname.startsWith('/bookings/detail/') && document.body.textContent.includes('Penumpang Demo')",
    );
    await evaluate(
        "[...document.querySelectorAll('button')].find(b => b.textContent.includes('Kembali ke Data Keberangkatan'))?.click()",
    );
    const back = await waitFor(
        "location.pathname === '/bookings' && !document.body.textContent.includes('Kembali ke Data Keberangkatan')",
    );
    // Fail a filter request, then retry with a delayed successful response.
    failingRoute = '/bookings';
    await evaluate(
        "[...document.querySelectorAll('button')].find(b => b.textContent.trim() === 'Filter')?.click()",
    );
    await pause(200);
    const changedDate =
        await evaluate(`(() => { const input = document.querySelector('[role=dialog] input[type=date]'); if (!input) return false;
        input.value = '2026-11-05'; input.dispatchEvent(new Event('change', { bubbles: true }));
        [...document.querySelectorAll('[role=dialog] button')].find(b => b.textContent.trim() === 'Terapkan')?.click(); return true; })()`);
    const failed =
        changedDate &&
        (await waitFor(
            "document.querySelector('[role=alert]')?.textContent.includes('Gagal memuat keberangkatan')",
        ));
    failingRoute = null;
    latency = 700;
    await evaluate(
        "[...document.querySelectorAll('[role=alert] button')].find(b => b.textContent.trim() === 'Coba lagi')?.click()",
    );
    const retry =
        failed &&
        (await waitFor(
            "!document.querySelector('[role=alert]') && document.body.textContent.includes('MAKASSAR - PINRANG')",
        ));
    latency = 0;
    const navigationMetrics = await evaluate(
        'window.optibusNavigationMetrics || []',
    );
    // A failed performance panel must not hide the operational cards.
    const savedDashboard = structuredClone(fixtures['/dashboard']);
    fixtures['/dashboard'].rescuedProps = ['topDrivers'];
    delete fixtures['/dashboard'].props.topDrivers;
    await navigate('/dashboard');
    const isolatedFailure = await waitFor(
        "document.body.textContent.includes('Performa driver gagal dimuat') && document.body.textContent.includes('Keberangkatan Hari Ini')",
    );
    fixtures['/dashboard'] = savedDashboard;
    await evaluate(
        "[...document.querySelectorAll('[role=alert] button')].find(b => b.textContent.trim() === 'Coba lagi')?.click()",
    );
    const panelRetry = await waitFor(
        "!document.body.textContent.includes('Performa driver gagal dimuat')",
    );
    const interactions = {
        detail,
        back,
        failed,
        retry,
        isolatedFailure,
        panelRetry,
    };
    const report = {
        fixtureOnly: true,
        measurements,
        failures: measurements.filter((row) => !row.pass),
        errors,
        filter,
        interactions,
        navigationMetrics,
        keyboard:
            'Viewport shrink simulation; physical keyboard not exercised.',
    };
    fs.writeFileSync(
        path.join(output, 'layout-report.json'),
        JSON.stringify(report, null, 2),
    );
    console.log(
        JSON.stringify({
            checks: measurements.length,
            failures: report.failures,
            errors,
            filter,
            interactions,
        }),
    );

    if (
        report.failures.length ||
        errors.length ||
        !filter.applyVisible ||
        Object.values(interactions).some((passed) => !passed)
    ) {
process.exitCode = 1;
}
} finally {
    socket?.close();
    child.kill();
    server.close();
}
