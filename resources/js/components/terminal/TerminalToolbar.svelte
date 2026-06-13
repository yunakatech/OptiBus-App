<script lang="ts">
    import { createEventDispatcher } from 'svelte';
    import type { Snippet } from 'svelte';
    import { Input } from '@/components/ui/input';
    import { Button } from '@/components/ui/button';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        query = $bindable(''),
        placeholder = 'Cari berdasarkan kode, nama, atau parameter...',
        actions,
    }: {
        class?: string;
        query?: string;
        placeholder?: string;
        actions?: Snippet;
    } = $props();

    const dispatch = createEventDispatcher();

    const doSearch = () => dispatch('search', { query });
</script>

<div class={cn('flex items-center gap-3 p-3 bg-card border border-border rounded-md', className)}>
    <div class="relative flex-1">
        <Input bind:value={query} placeholder={placeholder} on:keydown={(e) => { if (e.key === 'Enter') doSearch(); }} />
    </div>

    <div class="flex items-center gap-2">
        <Button onclick={doSearch} size="sm" class="uppercase tracking-wide">Cari</Button>
        {@render actions?.()}
    </div>
</div>
