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
    class={cn(
        'mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end',
        className,
    )}
>
    {@render children?.()}
</div>
