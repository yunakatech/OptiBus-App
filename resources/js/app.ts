import { createInertiaApp } from '@inertiajs/svelte';
import type { ResolvedComponent } from '@inertiajs/svelte';
import AppLayout from '@/layouts/AppLayout.svelte';
import AuthLayout from '@/layouts/AuthLayout.svelte';
import SettingsLayout from '@/layouts/settings/Layout.svelte';
import { initializeFlashToast } from '@/lib/flash-toast';
import { initializeInertiaLoading } from '@/lib/inertia-loading';
import { initializeTheme } from '@/lib/theme.svelte';

const appName = import.meta.env.VITE_APP_NAME || 'OptiBus';
const pages = import.meta.glob<ResolvedComponent>('./pages/**/*.svelte');

createInertiaApp({
    resolve: async (name) => {
        const pageModule = pages[`./pages/${name}.svelte`];

        if (!pageModule) {
            throw new Error(`Inertia page not found: ${name}`);
        }

        return await pageModule();
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name === 'Pricing':
                return null;
            case name === 'Onboarding':
                return AuthLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: false,
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

// This gives every Inertia page transition a visible loading response.
initializeInertiaLoading();
