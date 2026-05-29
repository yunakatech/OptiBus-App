<script lang="ts">
    import type { Snippet } from 'svelte';
    import { Button } from '@/components/ui/button';
    import { Spinner } from '@/components/ui/spinner';
    import { cn } from '@/lib/utils';

    let {
        loading = false,
        loadingText = 'Memproses...',
        spinnerClass = 'h-4 w-4',
        class: className = '',
        children,
        disabled = false,
        ...rest
    }: {
        loading?: boolean;
        loadingText?: string;
        spinnerClass?: string;
        class?: string;
        children?: Snippet;
        disabled?: boolean;
        [key: string]: unknown;
    } = $props();
</script>

<Button
    class={cn(loading ? 'cursor-wait' : '', className)}
    disabled={disabled || loading}
    {...rest}
>
    {#if loading}
        <Spinner class={spinnerClass} />
        <span>{loadingText}</span>
    {:else}
        {@render children?.()}
    {/if}
</Button>
