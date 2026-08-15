<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        id,
        children,
    }: {
        class?: string;
        id?: string;
        children?: Snippet;
    } = $props();
</script>

<div id={id} data-slot="card" class={cn('bg-card text-card-foreground flex min-w-0 max-w-full flex-col gap-3 overflow-hidden rounded-lg border border-border/80 py-3 shadow-xs sm:gap-4 sm:py-4 md:[html[data-density=compact]_&]:gap-3 md:[html[data-density=compact]_&]:py-3.5', className)}>
    {@render children?.()}
</div>
