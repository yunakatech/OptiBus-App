<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        label = '',
        error = '',
        required = false,
        class: className = '',
        children,
    }: {
        label?: string;
        error?: string | null;
        required?: boolean;
        class?: string;
        children?: Snippet;
    } = $props();
</script>

<label class={cn('grid min-w-0 gap-1.5 text-sm font-medium', className)}>
    {#if label}
        <span class="text-foreground">
            {label}{#if required}<span class="text-destructive"> *</span>{/if}
        </span>
    {/if}
    {@render children?.()}
    {#if error}
        <span class="text-xs font-medium text-destructive">{error}</span>
    {/if}
</label>
