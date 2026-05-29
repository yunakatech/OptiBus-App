import { derived, get, writable } from 'svelte/store';

export type LoadingScope = 'global' | 'navigation' | 'form' | 'action';

export type LoadingEntry = {
    id: string;
    message: string;
    scope: LoadingScope;
    blocking: boolean;
    startedAt: number;
};

export type StartLoadingOptions = {
    id?: string;
    message?: string;
    scope?: LoadingScope;
    blocking?: boolean;
};

type LoadingState = {
    active: boolean;
    blocking: boolean;
    message: string;
    entries: LoadingEntry[];
};

const loadingEntries = writable<LoadingEntry[]>([]);

const createLoadingId = () => {
    return `loading-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
};

export const globalLoading = derived(loadingEntries, ($entries): LoadingState => {
    const latestEntry = $entries[$entries.length - 1];

    return {
        active: $entries.length > 0,
        blocking: $entries.some((entry) => entry.blocking),
        message: latestEntry?.message ?? '',
        entries: $entries,
    };
});

export function startLoading(options: StartLoadingOptions = {}): string {
    const id = options.id ?? createLoadingId();
    const entry: LoadingEntry = {
        id,
        message: options.message ?? 'Memproses data...',
        scope: options.scope ?? 'global',
        blocking: options.blocking ?? true,
        startedAt: Date.now(),
    };

    loadingEntries.update((entries) => {
        const nextEntries = entries.filter((item) => item.id !== id);
        nextEntries.push(entry);

        return nextEntries;
    });

    return id;
}

export function updateLoading(id: string, patch: Partial<Omit<LoadingEntry, 'id' | 'startedAt'>>): void {
    if (!id) {
        return;
    }

    loadingEntries.update((entries) =>
        entries.map((entry) => (entry.id === id ? { ...entry, ...patch } : entry)),
    );
}

export function stopLoading(id: string): void {
    if (!id) {
        return;
    }

    loadingEntries.update((entries) => entries.filter((entry) => entry.id !== id));
}

export function clearLoading(): void {
    loadingEntries.set([]);
}

export async function withLoading<T>(
    action: () => Promise<T>,
    options: StartLoadingOptions = {},
): Promise<T> {
    const token = startLoading(options);

    try {
        return await action();
    } finally {
        stopLoading(token);
    }
}

export function isLoadingActive(): boolean {
    return get(globalLoading).active;
}

export function useLoading() {
    return {
        loading: globalLoading,
        startLoading,
        updateLoading,
        stopLoading,
        clearLoading,
        withLoading,
        isActive: isLoadingActive,
    };
}
