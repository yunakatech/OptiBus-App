<script lang="ts">
    import type { Snippet } from 'svelte';
    import { SidebarInset } from '@/components/ui/sidebar';
    import { cn } from '@/lib/utils';
    import type { AppVariant } from '@/types';

    let {
        variant = 'sidebar',
        class: className = '',
        children,
    }: {
        variant?: AppVariant;
        class?: string;
        children?: Snippet;
    } = $props();
</script>

{#if variant === 'sidebar'}
    <SidebarInset class={className}>
        {@render children?.()}
    </SidebarInset>
{:else}
    <main
        class={cn(
            'mx-auto flex h-full min-w-0 w-full max-w-6xl flex-1 flex-col gap-3 overflow-x-hidden px-3 py-4 sm:px-4 md:px-6 md:py-5 md:[html[data-density=compact]_&]:gap-2.5',
            className,
        )}
    >
        {@render children?.()}
    </main>
{/if}
