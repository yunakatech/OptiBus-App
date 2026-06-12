<script lang="ts">
    import { createEventDispatcher } from 'svelte';
    import { Input } from '@/components/ui/input';
    import { Button } from '@/components/ui/button';
    import { cn } from '@/lib/utils';

    let { query = '', placeholder = 'Cari...', class: className = '' }: { query?: string; placeholder?: string; class?: string } = $props();

    const dispatch = createEventDispatcher();

    const doSearch = () => dispatch('search', { query });
</script>

<div class={cn('flex items-center gap-2', className)}>
    <div class="flex-1">
        <Input bind:value={query} placeholder={placeholder} on:keydown={(e) => { if (e.key === 'Enter') doSearch(); }} />
    </div>

    <div class="flex items-center gap-2">
        <Button size="sm" on:click={doSearch}>Cari</Button>
        <slot name="extras" />
    </div>
</div>
