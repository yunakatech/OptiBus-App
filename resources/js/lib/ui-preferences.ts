import { page } from '@inertiajs/svelte';

export type UiViewMode = 'sheet' | 'cards';

export type UiPreferences = {
    defaultViewMode?: UiViewMode;
    defaultDateRange?: string;
    itemsPerPage?: number;
};

const UI_PREFERENCES_ENDPOINT = '/api/user/ui-preferences';

function normalizeItemsPerPage(value: unknown): number | undefined {
    if (typeof value === 'number' && Number.isFinite(value)) {
        return value;
    }

    if (typeof value === 'string' && value.trim() !== '') {
        const parsed = Number(value);

        return Number.isFinite(parsed) ? parsed : undefined;
    }

    return undefined;
}

function csrfToken(): string {
    if (typeof document === 'undefined') {
        return '';
    }

    return (
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)
            ?.content ?? ''
    );
}

function rawPreferences(): Record<string, unknown> {
    return (page.props.auth?.user?.ui_preferences ?? {}) as Record<string, unknown>;
}

export function readUiPreferences(): UiPreferences {
    const prefs = rawPreferences();
    const defaultViewMode = prefs.defaultViewMode;
    const defaultDateRange = prefs.defaultDateRange;
    const itemsPerPage = prefs.itemsPerPage;

    return {
        defaultViewMode:
            defaultViewMode === 'sheet' || defaultViewMode === 'cards'
                ? defaultViewMode
                : undefined,
        defaultDateRange:
            typeof defaultDateRange === 'string' && defaultDateRange !== ''
                ? defaultDateRange
                : undefined,
        itemsPerPage: normalizeItemsPerPage(itemsPerPage),
    };
}

export function getUiPreference<K extends keyof UiPreferences>(
    key: K,
    fallback?: UiPreferences[K],
): UiPreferences[K] {
    const preferences = readUiPreferences();
    const value = preferences[key];

    return (value ?? fallback) as UiPreferences[K];
}

export async function saveUiPreferences(
    preferences: Partial<UiPreferences>,
): Promise<UiPreferences> {
    const response = await fetch(UI_PREFERENCES_ENDPOINT, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ preferences }),
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload?.success === false) {
        throw new Error(
            (payload?.message as string | undefined) ??
                'Gagal menyimpan preferensi tampilan.',
        );
    }

    const saved = (payload?.ui_preferences ?? preferences) as Partial<UiPreferences>;

    return {
        defaultViewMode:
            saved.defaultViewMode === 'sheet' || saved.defaultViewMode === 'cards'
                ? saved.defaultViewMode
                : undefined,
        defaultDateRange:
            typeof saved.defaultDateRange === 'string' && saved.defaultDateRange !== ''
                ? saved.defaultDateRange
                : undefined,
        itemsPerPage: normalizeItemsPerPage(saved.itemsPerPage),
    };
}
