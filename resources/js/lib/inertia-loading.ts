import { router } from '@inertiajs/svelte';
import { startLoading, stopLoading } from '@/lib/loading';

let initialized = false;
let navigationToken: string | null = null;
let navigationTimer: ReturnType<typeof setTimeout> | null = null;

const finishNavigationLoading = () => {
    if (navigationTimer) {
        clearTimeout(navigationTimer);
        navigationTimer = null;
    }

    if (!navigationToken) {
        return;
    }

    stopLoading(navigationToken);
    navigationToken = null;
};

export function initializeInertiaLoading(): void {
    if (initialized) {
        return;
    }

    initialized = true;

    router.on('start', (event) => {
        finishNavigationLoading();
        const visit = (event as unknown as { detail?: { visit?: { url?: unknown } } })?.detail?.visit;
        const targetUrl = String(visit?.url ?? '');
        const cleanUrl = targetUrl
            .replace(/^https?:\/\/[^/]+/i, '')
            .split('?')[0]
            .replace(/^\/+/, '');
        const pageName = cleanUrl === '' ? 'Dashboard' : cleanUrl
            .split('/')
            .filter(Boolean)
            .slice(0, 2)
            .map((segment) => segment.replace(/-/g, ' '))
            .join(' / ');

        navigationTimer = setTimeout(() => {
            navigationToken = startLoading({
                id: 'inertia-navigation',
                message: pageName ? `Memuat ${pageName}...` : 'Memuat halaman...',
                scope: 'navigation',
                blocking: false,
            });
        }, 80);
    });

    router.on('finish', finishNavigationLoading);
    router.on('cancel', finishNavigationLoading);
    router.on('error', finishNavigationLoading);
    router.on('success', finishNavigationLoading);
}
