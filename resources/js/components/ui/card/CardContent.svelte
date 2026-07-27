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

<div data-slot="card-content" class={cn('min-w-0 px-3 sm:px-5 md:px-6 md:[html[data-density=compact]_&]:px-5', className)}>
    {@render children?.()}
</div>
