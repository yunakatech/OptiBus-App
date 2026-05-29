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

<div data-slot="card-footer" class={cn('flex items-center px-6 [.border-t]:pt-6 md:[html[data-density=compact]_&]:px-5 md:[html[data-density=compact]_&]:[.border-t]:pt-4', className)}>
    {@render children?.()}
</div>
