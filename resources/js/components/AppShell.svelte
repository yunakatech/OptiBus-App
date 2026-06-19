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
            previousDensity = document.documentElement.getAttribute('data-density');
            document.documentElement.setAttribute('data-density', 'compact');
            window.addEventListener('set-theme', themeHandler as EventListener);
        }
    });

    onDestroy(() => {
        if (typeof window !== 'undefined') {
            window.removeEventListener('set-theme', themeHandler as EventListener);
            if (previousDensity === null) {
                document.documentElement.removeAttribute('data-density');
            } else {
                document.documentElement.setAttribute('data-density', previousDensity);
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
