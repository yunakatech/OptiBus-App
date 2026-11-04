<script lang="ts">
    import type { Snippet } from 'svelte';
    import { MediaQuery } from 'svelte/reactivity';

    let {
        minWidth = 0,
        maxWidth,
        children,
    }: {
        minWidth?: number;
        maxWidth?: number;
        children: Snippet;
    } = $props();
    const query = $derived(
        `(min-width: ${minWidth}px)${maxWidth === undefined ? '' : ` and (max-width: ${maxWidth}px)`}`,
    );
    const viewport = $derived(new MediaQuery(query));
</script>

{#if viewport.current}{@render children()}{/if}
