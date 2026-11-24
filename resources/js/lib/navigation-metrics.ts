import { router } from '@inertiajs/svelte';

type Sample = {
    kind: 'document' | 'inertia';
    component: string;
    visit: 'first' | 'repeat';
    requestTtfbMs: number | null;
    serverAppMs: number | null;
    responseMs: number | null;
    moduleResolveMs: number;
    jsTransferBytes: number;
    jsResourceCount: number;
    jsDownloadMs: number;
    renderAfterModuleMs: number | null;
    nextPaintMs: number | null;
    dataReadyMs: number | null;
};
type Active = {
    start: number;
    url: string;
    sample: Sample;
    completed: boolean;
    moduleReadyAt?: number;
};
declare global {
    interface Window {
        optibusNavigationMetrics?: Sample[];
    }
}
let enabled = false;
let active: Active | null = null;
let sequence = 0;
const visited = new Set<string>();
const rounded = (value: number) => Math.round(value * 10) / 10;

export function initializeNavigationMetrics() {
    try {
        enabled =
            import.meta.env.DEV ||
            import.meta.env.VITE_NAVIGATION_METRICS === 'true' ||
            sessionStorage.getItem('optibus.navigationMetrics') === '1';
    } catch {
        enabled = false;
    }

    if (!enabled) {
return;
}

    window.optibusNavigationMetrics = [];
    start('document', window.location.href, 0);
    router.on('start', (event) => {
        const visit = event.detail.visit;

        // Deferred/background reloads belong to the existing navigation.
        if (visit.async) {
return;
}

        start('inertia', String(visit.url), performance.now());
    });
    const finishPage = (name: string) => {
        if (active && !active.completed && !active.sample.component) {
            active.sample.component = name;
            active.sample.visit = visited.has(name) ? 'repeat' : 'first';
            visited.add(name);
        }

        void finish();
    };
    router.on('navigate', (event) => finishPage(event.detail.page.component));
    // Same-URL/replace visits emit success without a navigate event.
    router.on('success', (event) => finishPage(event.detail.page.component));
    router.on('cancel', () => {
        active = null;
        sequence++;
    });
}

function start(kind: Sample['kind'], url: string, time: number) {
    sequence++;
    active = {
        start: time,
        url,
        completed: false,
        sample: {
            kind,
            component: '',
            visit: 'first',
            requestTtfbMs: null,
            serverAppMs: null,
            responseMs: null,
            moduleResolveMs: 0,
            jsTransferBytes: 0,
            jsResourceCount: 0,
            jsDownloadMs: 0,
            renderAfterModuleMs: null,
            nextPaintMs: null,
            dataReadyMs: null,
        },
    };
}

export async function measurePageModule<T>(
    name: string,
    load: () => Promise<T>,
): Promise<T> {
    if (!enabled) {
return load();
}

    const current = active;
    const time = performance.now();
    const result = await load();

    if (current && current === active && !current.completed) {
        if (!current.sample.component) {
            current.sample.component = name;
            current.sample.visit = visited.has(name) ? 'repeat' : 'first';
            visited.add(name);
        }

        current.sample.moduleResolveMs = Math.max(
            current.sample.moduleResolveMs,
            rounded(performance.now() - time),
        );
        current.moduleReadyAt = performance.now();
    }

    return result;
}

// Called after the main page props have reached Svelte; not an estimate of CPU time.
export function markNavigationDataReady() {
    if (!enabled || !active || active.sample.dataReadyMs !== null) {
return;
}

    const current = active;
    requestAnimationFrame(() => {
        if (current === active) {
current.sample.dataReadyMs = rounded(
                performance.now() - current.start,
            );
}
    });
}

async function finish() {
    if (!active || active.completed) {
return;
}

    const current = active;
    const token = sequence;
    await new Promise<void>((resolve) =>
        requestAnimationFrame(() => requestAnimationFrame(() => resolve())),
    );

    if (token !== sequence || current !== active || current.completed) {
return;
}

    const end = performance.now();
    current.sample.nextPaintMs = rounded(end - current.start);

    if (current.moduleReadyAt !== undefined) {
current.sample.renderAfterModuleMs = rounded(
            end - current.moduleReadyAt,
        );
}

    const request =
        current.sample.kind === 'document'
            ? (performance.getEntriesByType('navigation')[0] as
                  | PerformanceNavigationTiming
                  | undefined)
            : (
                  performance.getEntriesByType(
                      'resource',
                  ) as PerformanceResourceTiming[]
              )
                  .filter(
                      (entry) =>
                          entry.name === current.url &&
                          entry.startTime >= current.start &&
                          ['fetch', 'xmlhttprequest'].includes(
                              entry.initiatorType,
                          ),
                  )
                  .at(-1);

    if (request?.responseStart) {
        current.sample.requestTtfbMs = rounded(
            request.responseStart - request.startTime,
        );
        current.sample.responseMs = rounded(
            request.responseEnd - request.responseStart,
        );
        current.sample.serverAppMs =
            request.serverTiming?.find((timing) => timing.name === 'app')
                ?.duration ?? null;
    }

    const scripts = (
        performance.getEntriesByType('resource') as PerformanceResourceTiming[]
    ).filter(
        (entry) =>
            /\.js(?:\?|$)/.test(entry.name) &&
            entry.startTime >= current.start &&
            entry.startTime <= end,
    );
    current.sample.jsResourceCount = scripts.length;
    current.sample.jsTransferBytes = scripts.reduce(
        (total, entry) => total + entry.transferSize,
        0,
    );
    current.sample.jsDownloadMs = rounded(
        scripts.reduce(
            (total, entry) =>
                total + Math.max(0, entry.responseEnd - entry.responseStart),
            0,
        ),
    );
    // Keep this object alive so later deferred data can complete dataReadyMs.
    current.completed = true;
    const samples = window.optibusNavigationMetrics!;
    samples.push(current.sample);

    if (samples.length > 40) {
samples.shift();
}
}
