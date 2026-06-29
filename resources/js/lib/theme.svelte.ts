import type { Appearance, Density, ResolvedAppearance } from '@/types';

export type { Appearance, Density, ResolvedAppearance };

export type ThemeState = {
    appearance: {
        value: Appearance;
    };
    density: {
        value: Density;
    };
    resolvedAppearance: () => ResolvedAppearance;
    updateAppearance: (value: Appearance) => void;
    updateDensity: (value: Density) => void;
};

const DEFAULT_APPEARANCE: Appearance = 'light';

const appearance = $state<{ value: Appearance }>({
    value: DEFAULT_APPEARANCE,
});
const density = $state<{ value: Density }>({ value: 'comfortable' });

let themeChangeMediaQuery: MediaQueryList | null = null;

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const isDarkMode = (value: Appearance): boolean => {
    return value === 'dark' || (value === 'system' && prefersDark());
};

const getResolvedAppearance = (): ResolvedAppearance => {
    return isDarkMode(appearance.value) ? 'dark' : 'light';
};

const setCookie = (name: string, value: string, days = 365): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const applyTheme = (value: Appearance): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = isDarkMode(value);
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

const applyDensity = (value: Density): void => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.density = value;
};

const getStoredAppearance = (): Appearance => {
    if (typeof window === 'undefined') {
        return DEFAULT_APPEARANCE;
    }

    const stored = localStorage.getItem('appearance');

    return stored === 'light' || stored === 'dark' || stored === 'system'
        ? stored
        : DEFAULT_APPEARANCE;
};

const getStoredDensity = (): Density => {
    if (typeof window === 'undefined') {
        return 'comfortable';
    }

    const stored = localStorage.getItem('density');

    return stored === 'compact' || stored === 'comfortable'
        ? stored
        : 'comfortable';
};

const handleSystemThemeChange = (): void => {
    applyTheme(appearance.value);
};

const detachThemeChangeListener = (): void => {
    if (!themeChangeMediaQuery) {
        return;
    }

    themeChangeMediaQuery.removeEventListener(
        'change',
        handleSystemThemeChange,
    );
    themeChangeMediaQuery = null;
};

export function initializeTheme(): () => void {
    if (typeof window === 'undefined') {
        return () => {};
    }

    const storedAppearance = localStorage.getItem('appearance');
    const hasUserSelectedAppearance =
        localStorage.getItem('appearance-user-set') === 'true';

    if (
        !storedAppearance ||
        (storedAppearance === 'system' && !hasUserSelectedAppearance)
    ) {
        localStorage.setItem('appearance', DEFAULT_APPEARANCE);
        setCookie('appearance', DEFAULT_APPEARANCE);
    }

    if (!localStorage.getItem('density')) {
        localStorage.setItem('density', 'comfortable');
        setCookie('density', 'comfortable');
    }

    appearance.value = getStoredAppearance();
    density.value = getStoredDensity();
    applyTheme(appearance.value);
    applyDensity(density.value);

    detachThemeChangeListener();
    themeChangeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    themeChangeMediaQuery.addEventListener('change', handleSystemThemeChange);

    return detachThemeChangeListener;
}

export function updateAppearance(value: Appearance): void {
    appearance.value = value;

    if (typeof window !== 'undefined') {
        localStorage.setItem('appearance', value);
        localStorage.setItem('appearance-user-set', 'true');
    }

    setCookie('appearance', value);
    applyTheme(value);
}

export function updateDensity(value: Density): void {
    density.value = value;

    if (typeof window !== 'undefined') {
        localStorage.setItem('density', value);
    }

    setCookie('density', value);
    applyDensity(value);
}

export function themeState(): ThemeState {
    return {
        appearance,
        density,
        resolvedAppearance: getResolvedAppearance,
        updateAppearance,
        updateDensity,
    };
}
