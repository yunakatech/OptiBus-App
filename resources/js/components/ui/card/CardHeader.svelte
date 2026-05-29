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
        '@container/card-header grid auto-rows-min grid-rows-[auto_auto] items-start gap-1.5 px-6 has-data-[slot=card-action]:grid-cols-[1fr_auto] [.border-b]:pb-6 md:[html[data-density=compact]_&]:gap-1 md:[html[data-density=compact]_&]:px-5 md:[html[data-density=compact]_&]:[.border-b]:pb-4',
        className,
    )}
>
    {@render children?.()}
</div>
