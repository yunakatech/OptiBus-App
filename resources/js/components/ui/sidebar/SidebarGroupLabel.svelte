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
    data-slot="sidebar-group-label"
    data-sidebar="group-label"
    class={cn(
        'text-sidebar-foreground/70 ring-sidebar-ring flex h-8 shrink-0 items-center rounded-md px-2 text-xs font-medium outline-hidden transition-[margin,opacity] duration-200 ease-linear focus-visible:ring-2 md:[html[data-density=compact]_&]:h-7 md:[html[data-density=compact]_&]:px-1.5 md:[html[data-density=compact]_&]:text-[11px] [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:-mt-8 group-data-[collapsible=icon]:opacity-0',
        className,
    )}
>
    {@render children?.()}
</div>
