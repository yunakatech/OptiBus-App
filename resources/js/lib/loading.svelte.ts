import { derived, get, readonly, writable } from 'svelte/store';

export type GlobalLoadingOptions = {
    message?: string;
    source?: string;
};

export type GlobalLoadingState = {
    active: boolean;
    count: number;
    message: string;
    source: string | null;
    startedAt: number | null;
};

type LoadingEntry = {
    id: number;
    message: string;
    source: string | null;
    startedAt: number;
};

const DEFAULT_LOADING_MESSAGE = 'Memproses data...';

let nextLoadingId = 1;

const loadingEntries = writable<LoadingEntry[]>([]);

const loadingState = derived<typeof loadingEntries, GlobalLoadingState>(loadingEntries, ($entries) => {
    const latest = $entries.at(-1) ?? null;

    return {
        active: $entries.length > 0,
        count: $entries.length,
        message: latest?.message ?? DEFAULT_LOADING_MESSAGE,
        source: latest?.source ?? null,
        startedAt: latest?.startedAt ?? null,
    };
});

export const globalLoading = readonly(loadingState);

export function startLoading(options: GlobalLoadingOptions = {}): number {
    const entry: LoadingEntry = {
        id: nextLoadingId++,
        message: options.message?.trim() || DEFAULT_LOADING_MESSAGE,
        source: options.source?.trim() || null,
        startedAt: Date.now(),
    };

    loadingEntries.update((entries) => [...entries, entry]);

    return entry.id;
}

export function stopLoading(id?: number): void {
    if (typeof id !== 'number') {
        loadingEntries.set([]);

        return;
    }

    loadingEntries.update((entries) => entries.filter((entry) => entry.id !== id));
}

export function clearLoading(): void {
    stopLoading();
}

export function setLoadingMessage(message: string, id?: number): void {
    const nextMessage = message.trim() || DEFAULT_LOADING_MESSAGE;

    loadingEntries.update((entries) => {
        if (entries.length === 0) {
            return entries;
        }

        const targetId = id ?? entries.at(-1)?.id;

        return entries.map((entry) => (entry.id === targetId ? { ...entry, message: nextMessage } : entry));
    });
}

export function isLoadingActive(): boolean {
    return get(loadingState).active;
}

export async function withLoading<T>(
    action: () => Promise<T>,
    options: GlobalLoadingOptions = {},
): Promise<T> {
    const loadingId = startLoading(options);

    try {
        return await action();
    } finally {
        stopLoading(loadingId);
    }
}

export function useLoading() {
    return {
        state: globalLoading,
        subscribe: globalLoading.subscribe,
        start: startLoading,
        stop: stopLoading,
        clear: clearLoading,
        setMessage: setLoadingMessage,
        withLoading,
        isActive: isLoadingActive,
    };
}
