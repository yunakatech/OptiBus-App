<script lang="ts">
    import DataTable from '@/components/terminal/DataTable.svelte';

    let props: Record<string, unknown> = $props();
</script>

<DataTable {...props} tone="default" />
