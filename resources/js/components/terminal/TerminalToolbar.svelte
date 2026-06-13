<script lang="ts">
    import { createEventDispatcher } from 'svelte';
    import { Input } from '@/components/ui/input';
    import { Button } from '@/components/ui/button';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        query = $bindable(''),
        placeholder = 'Cari berdasarkan kode, nama, atau parameter...',
    }: {
        class?: string;
        query?: string;
        placeholder?: string;
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
        <slot name="actions" />
    </div>
</div>
