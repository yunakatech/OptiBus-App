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

<div
    data-slot="card-header"
    class={cn(
        '@container/card-header grid min-w-0 auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-3 has-data-[slot=card-action]:grid-cols-[minmax(0,1fr)_auto] [.border-b]:pb-4 sm:px-5 sm:[.border-b]:pb-5 md:px-6 md:[.border-b]:pb-6 md:[html[data-density=compact]_&]:gap-1 md:[html[data-density=compact]_&]:px-5 md:[html[data-density=compact]_&]:[.border-b]:pb-4',
        className,
    )}
>
    {@render children?.()}
</div>
