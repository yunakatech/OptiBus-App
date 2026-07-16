<script lang="ts">
    import { createEventDispatcher } from 'svelte';
    import type { Snippet } from 'svelte';
    import { Input } from '@/components/ui/input';
    import { Button } from '@/components/ui/button';
    import { cn } from '@/lib/utils';

    let {
        query = $bindable(''),
        placeholder = 'Cari...',
        class: className = '',
        extras,
    }: {
        query?: string;
        placeholder?: string;
        class?: string;
        extras?: Snippet;
    } = $props();

    const dispatch = createEventDispatcher();

    const doSearch = () => dispatch('search', { query });
</script>

<div
    class={cn(
        'flex items-center gap-2 rounded-lg border border-border/70 bg-card p-2 shadow-xs',
        className,
    )}
>
    <div class="flex-1">
        <Input
            bind:value={query}
            {placeholder}
            on:keydown={(e) => {
                if (e.key === 'Enter') doSearch();
            }}
        />
    </div>

    <div class="flex items-center gap-2">
        <Button size="sm" onclick={doSearch}>Cari</Button>
        {@render extras?.()}
    </div>
</div>
