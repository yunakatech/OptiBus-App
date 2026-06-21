<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'Pool',
                href: '/admin-ops/pool',
            },
        ],
    };
</script>

<script lang="ts">
    import AdminOps from '@/pages/AdminOps.svelte';

    const props = $props();
</script>

<AdminOps {...props} />
