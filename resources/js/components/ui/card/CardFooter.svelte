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

<div data-slot="card-footer" class={cn('flex min-w-0 items-center px-3 [.border-t]:pt-4 sm:px-5 md:px-6 md:[.border-t]:pt-6 md:[html[data-density=compact]_&]:px-5 md:[html[data-density=compact]_&]:[.border-t]:pt-4', className)}>
    {@render children?.()}
</div>
