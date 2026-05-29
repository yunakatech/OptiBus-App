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

const appearance = $state<{ value: Appearance }>({ value: 'system' });
const density = $state<{ value: Density }>({ value: 'compact' });

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
        return 'system';
    }

    const stored = localStorage.getItem('appearance');

    return stored === 'light' || stored === 'dark' || stored === 'system'
        ? stored
        : 'system';
};

const getStoredDensity = (): Density => {
    if (typeof window === 'undefined') {
        return 'compact';
    }

    const stored = localStorage.getItem('density');

    return stored === 'compact' || stored === 'comfortable'
        ? stored
        : 'compact';
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

    if (!localStorage.getItem('appearance')) {
        localStorage.setItem('appearance', 'system');
        setCookie('appearance', 'system');
    }

    if (!localStorage.getItem('density')) {
        localStorage.setItem('density', 'compact');
        setCookie('density', 'compact');
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
