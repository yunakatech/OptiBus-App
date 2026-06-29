<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { SidebarProvider } from '@/components/ui/sidebar';
    import type { AppVariant } from '@/types';
    import { onMount, onDestroy } from 'svelte';

    let {
        variant = 'sidebar',
        class: className = '',
        children,
    }: {
        variant?: AppVariant;
        class?: string;
        children?: Snippet;
    } = $props();

    const publicUiScalePathnames = [
        '/',
        '/pricing',
        '/login',
        '/register',
        '/onboarding',
        '/forgot-password',
        '/reset-password',
        '/user/confirm-password',
        '/user/confirmed-password-status',
        '/email/verify',
        '/email/verification-notification',
        '/two-factor-challenge',
    ];

    function shouldUsePublicUiScale(pathname: string): boolean {
        return publicUiScalePathnames.some(
            (publicPath) =>
                pathname === publicPath ||
                pathname.startsWith(`${publicPath}/`),
        );
    }

    const isOpen = $derived(page.props.sidebarOpen);
    let previousDensity: string | null = null;

    const themeHandler = (e: Event) => {
        try {
            const ce = e as CustomEvent;
            const theme = ce?.detail?.theme ?? null;

            if (theme) {
                document.documentElement.setAttribute('data-theme', theme);
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        } catch {
            // ignore
        }
    };

    onMount(() => {
        if (typeof window !== 'undefined') {
            previousDensity =
                document.documentElement.getAttribute('data-density');
            document.documentElement.setAttribute(
                'data-density',
                'comfortable',
            );
            document.documentElement.setAttribute('data-ui-scale', 'app');
            window.addEventListener('set-theme', themeHandler as EventListener);
        }
    });

    onDestroy(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener(
                'set-theme',
                themeHandler as EventListener,
            );
            document.documentElement.setAttribute(
                'data-ui-scale',
                shouldUsePublicUiScale(window.location.pathname)
                    ? 'public'
                    : 'app',
            );
            if (previousDensity === null) {
                document.documentElement.removeAttribute('data-density');
            } else {
                document.documentElement.setAttribute(
                    'data-density',
                    previousDensity,
                );
            }
        }
    });
</script>

{#if variant === 'header'}
    <div class="flex min-h-screen w-full flex-col {className}">
        {@render children?.()}
    </div>
{:else}
    <SidebarProvider defaultOpen={isOpen}>
        {@render children?.()}
    </SidebarProvider>
{/if}
