import { router } from '@inertiajs/svelte';
import { showToast } from '@/lib/toast';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) {
            return;
        }

        showToast(data.type, data.message);
    });
}
