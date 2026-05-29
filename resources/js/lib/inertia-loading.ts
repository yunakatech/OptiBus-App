import { router } from '@inertiajs/svelte';
import { startLoading, stopLoading } from '@/lib/loading';

let initialized = false;
let navigationToken: string | null = null;

const finishNavigationLoading = () => {
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

    router.on('start', () => {
        finishNavigationLoading();
        navigationToken = startLoading({
            id: 'inertia-navigation',
            message: 'Memuat halaman...',
            scope: 'navigation',
            blocking: true,
        });
    });

    router.on('finish', finishNavigationLoading);
    router.on('cancel', finishNavigationLoading);
    router.on('error', finishNavigationLoading);
    router.on('success', finishNavigationLoading);
}
