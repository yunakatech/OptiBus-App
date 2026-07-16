<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        children,
    }: {
        class?: string;
        children?: Snippet;
    } = $props();
</script>

<div data-slot="card" class={cn('bg-card text-card-foreground flex flex-col gap-4 rounded-lg border border-border/80 py-4 shadow-xs md:[html[data-density=compact]_&]:gap-3 md:[html[data-density=compact]_&]:py-3.5', className)}>
    {@render children?.()}
</div>
